<?php
/**
 * Create and write new json cj reference file
 */
function cjds_create_json_singleproduct($result){
    $details = $result['details'];
    $pid = $details['pid'];

    // Create in SHARED location (accessible by both domains)
    $shared_path = CJDS_SHARED_JSON_PATH . $pid . '.json';

    // Convert video ids to URLs
    $video_urls = [];
    if (!empty($details['productVideo']) && is_array($details['productVideo'])) {
        foreach ($details['productVideo'] as $vid) {
            $video_urls[] = "https://video-cf.cjdropshipping.com/{$vid}.mp4";
        }
    }
    
    // Transform to batch format
    $normalized = [
        'productSku' => $details['productSku'],
        'nameEn' => isset($details['productNameEn']) ? $details['productNameEn'] : $details['nameEn'],
        'sku' => $details['productSku'],
        'sellPrice' => $details['sellPrice'],
        'bigImage' => !empty($details['productImageSet']) ? $details['productImageSet'][0] : '',
        'productVideo' => $video_urls,
        'defaultArea' => '',
        'areaCountryCode' => '',
        'categoryName' => $details['categoryName'] ?? '',
        'details' => $details
    ];
    
    // Ensure directory exists
    if (!file_exists(CJDS_SHARED_JSON_PATH)) {
        mkdir(CJDS_SHARED_JSON_PATH, 0755, true);
    }
    
    // Save to shared location
    $result = file_put_contents($shared_path, json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        error_log("[CJDS] Created shared JSON: {$shared_path}\n", 3, CJSYNC . '/wccj_error.log');
        return $normalized;
    } else {
        error_log("[CJDS] Failed to create shared JSON: {$shared_path}\n", 3, CJSYNC . '/wccj_error.log');
        return false;
    }
}

function cjds_isupdated($post_date){

	// --- Configuration ---
	$given_date_string = $post_date; // The original start date (MM/DD/YYYY)
	$expiration_period = 'P7D';        // Period of 7 Days

	// 1. Setup Date Objects
	// The given date
	$start_date = DateTime::createFromFormat('m/d/Y', $given_date_string);
	if ($start_date === false) {
		return;
	}

	// The calculated future expiration date (start date + 7 days)
	$expiration_date = clone $start_date;
	$expiration_date->add(new DateInterval($expiration_period));

	// The current date/time for comparison
	$current_date = new DateTime();

	// 3. Conditional Check
	if ($expiration_date < $current_date) {
		// The calculated expiration date is in the past
	   return 'expired';
	} else {
		// The calculated expiration date is today or in the future
	   return 'updated';
	}
}

function cjds_sync_product_handle($product_id, $sku, $new_status = null, $current_status = null) {

    if (empty($sku) || !$product_id) {
        return [
            'status' => 'failed',
            'des' => 'Missing SKU or product ID.'
        ];
    }

    $product = wc_get_product($product_id);
    $is_updated = cjds_isupdated(get_post_meta($product_id, '_CJ_last_processed_date', true));

    if ($is_updated === 'updated') {
        return [
            'status' => 'failed',
            'des' => 'This is updated product.',
            'stat' => ['status' => 'skipped', 'des' => 'Recently updated (within 7 days)'],
            'redirect' => admin_url("post.php?post={$product_id}&action=edit&status=skipped&des=Recently+updated"),
            'new_status' => $new_status ? 'yes' : 'no',
            'html_content' => $new_status
                ? '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>'
                : '<span class="dashicons dashicons-star-empty" style="color:#aaa;"></span>'
        ];
    }

    // Determine PID
    $pid = get_post_meta($product_id, 'cjds_pid', true);

    if (strncmp($sku, 'CJ', 2) !== 0) {
        // Non-CJ SKU: extract base SKU from variations
        $base_sku = cjds_extract_parent_sku_from_variants($product_id);

        if (empty($pid) && !empty($base_sku)) {
            $cj_data = get_cg_product_by_sku($base_sku);
            $pid = isset($cj_data['list'][0]['pid']) ? $cj_data['list'][0]['pid'] : '';
        }

    } else {
        // SKU starts with 'CJ'
        $cj_data = get_cg_product_by_sku($sku);
        if (empty($pid)) {
            $pid = isset($cj_data['list'][0]['pid']) ? $cj_data['list'][0]['pid'] : '';
        }
    }

    if (empty($pid)) {
        error_log("[CJDS Sync] ❌ No PID found for SKU: {$sku} (Product ID: {$product_id})\n", 3, CJSYNC . '/wccj_error.log');
        return [
            'status' => 'failed',
            'des' => 'No PID found',
            'stat' => ['status' => 'failed', 'des' => 'No PID in response'],
            'redirect' => admin_url("post.php?post={$product_id}&action=edit&status=failed&des=No+PID"),
            'new_status' => 'no',
            'html_content' => '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
        ];
    }

    update_post_meta($product_id, 'cjds_pid', $pid);
    update_post_meta($product_id, '_CJ_last_processed_date', current_time('mysql'));

    // Load JSON from shared or local path using helper
    $create_data = cjds_get_json_data($pid);

    // If JSON does not exist, fetch from API and create JSON
    if (empty($create_data)) {
        error_log("[CJDS Sync] 📥 JSON not found, fetching from API for PID: {$pid}\n", 3, CJSYNC . '/wccj_error.log');

        $create_data = get_cg_product_by_pid($pid);

        if (empty($create_data)) {
            $product->set_status('draft');
            $product->save();
            update_post_meta($product_id, '_CJ_link', 'failed');
            update_post_meta($product_id, '_draft_reason', 'Product not found in CJ Dropshipping API');

            error_log("[CJDS Sync] Set product {$product_id} to DRAFT - API fetch failed\n", 3, CJSYNC . '/wccj_error.log');

            return [
                'status' => 'failed',
                'des' => 'Could not fetch product from CJ API',
                'stat' => ['status' => 'failed', 'des' => 'API fetch failed'],
                'redirect' => admin_url("post.php?post={$product_id}&action=edit&status=failed&des=API+fetch+failed"),
                'new_status' => 'no',
                'html_content' => '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
            ];
        }

        // Create JSON file
        $data['details'] = $create_data;
        cjds_create_json_singleproduct($data);

        // Reload the JSON
        $create_data = cjds_get_json_data($pid);
    }

    // Update WooCommerce product
    $stat = cjds_update_single_product($product_id, $create_data);
    $new_status_flag = ($current_status === 'yes') ? false : true;

    error_log("[CJDS Sync {$stat['status']}] {$stat['des']} (Product ID: {$product_id})\n", 3, CJSYNC . '/wccj_error.log');
    
    update_post_meta($product_id, 'CJIMPORT', 'TRUE');

    return [
        'status' => 'success',
        'des' => 'Updated product from CJ json file or API',
        'stat' => $stat,
        'redirect' => admin_url("post.php?post={$product_id}&action=edit&status={$stat['status']}&des={$stat['des']}"),
        'new_status' => $new_status_flag ? 'yes' : 'no',
        'html_content' => $new_status_flag
            ? '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>'
            : '<span class="dashicons dashicons-star-empty" style="color:#aaa;"></span>'
    ];
}

/**
 * Single product update from the JSON
 */
function cjds_update_single_product($product_id, $product_data) {
    
    if (!class_exists('WC_Product')) {
        error_log("[CJDS Sync] WC_Product class not found for product {$product_id}\n", 3, CJSYNC . '/wccj_error.log');
        return array('status'=>'failed','des'=>'WC_Product class not found');
    }

    try {
        $wc_product = wc_get_product($product_id);
        if (!$wc_product) {
            error_log("[CJDS Sync] Product ID {$product_id} not found.\n", 3, CJSYNC . '/wccj_error.log');
            return array('status'=>'failed','des'=>'Missing Post Id');
        }

        $product_data['nameEn'] = isset($product_data['productNameEn']) ? $product_data['productNameEn'] : $product_data['nameEn'];
        $details = $product_data['details'] ?? [];
        $image_urls = $details['productImageSet'] ?? [];
        $variants = $details['variants'] ?? [];
        $desc = !empty($details['description']) ? wp_kses_post($details['description']) : '';
        $image_url = $product_data['bigImage'] ?? '';

        // Handle video URLs
        $video_urls = [];
        if (!empty($product_data['productVideo']) && is_array($product_data['productVideo'])) {
            $video_urls = $product_data['productVideo'];
        } elseif (!empty($details['productVideo']) && is_array($details['productVideo'])) {
            $video_urls = $details['productVideo'];
        }

        if (!empty($video_urls)) {
            $video_result = cjds_store_product_videos($product_id, $video_urls);
            if ($video_result) {
                error_log("[CJDS Sync] Stored " . count($video_urls) . " videos for product {$product_id}\n", 3, CJSYNC . '/wccj_error.log');
            }
        }

        // Price handling
        $price_raw = $product_data['sellPrice'] ?? 0;
        $price = (strpos($price_raw, '-') !== false)
            ? floatval(explode('-', $price_raw)[1])
            : floatval($price_raw);

        if ($price < 0) $price = 0;

        // SKU conflict check
        $new_sku = sanitize_text_field($product_data['sku'] ?? $product_data['productSku'] ?? '');
        $current_sku = $wc_product->get_sku();

        if (!empty($new_sku) && $new_sku !== $current_sku) {
            $existing_product_id = wc_get_product_id_by_sku($new_sku);
            if ($existing_product_id && $existing_product_id !== $product_id) {
                error_log("[CJDS Sync] SKU CONFLICT: New SKU '{$new_sku}' already exists on product ID {$existing_product_id}. Setting product {$product_id} to draft.\n", 3, CJSYNC . '/wccj_error.log');
                $wc_product->set_status('draft');
                $wc_product->update_meta_data('_sku_conflict_reason', "SKU '{$new_sku}' conflicts with product ID {$existing_product_id}");
                $wc_product->save();
                
                return array('status'=>'failed','des'=>'Duplicated Parent SKU');
            }
        }

        // Republish draft if SKU changed
        $current_status = $wc_product->get_status();
        if (!empty($new_sku) && $new_sku !== $current_sku && $current_status === 'draft') {
            $wc_product->set_sku($new_sku);
            $wc_product->set_status('publish');
            $wc_product->save();
        }

        // Update basic info
        $wc_product->set_sku(sanitize_text_field($product_data['productSku']));
        $wc_product->set_name(sanitize_text_field($product_data['nameEn'] ?? 'Untitled'));

        // Update categories
        $category_path = $details['categoryName'] ?? ($product_data['categoryName'] ?? '');
        if (!empty($category_path)) {
            $category_ids = cjds_get_or_create_category($category_path);
            if (!empty($category_ids)) $wc_product->set_category_ids($category_ids);
        }

        // Update prices
        $prices = cjds_calculate_prices($price);
        $wc_product->set_sale_price($prices['sale_price']);
        $wc_product->set_regular_price($prices['regular_price']);

        // Update meta fields
        $meta_map = [
            'wc_cj_price'   => $price,
            'wc_cj_default_area'    => $product_data['defaultArea'] ?? '',
            'wc_cj_country_code'    => $product_data['areaCountryCode'] ?? '',
            'wc_cj_product_type'    => $details['productType'] ?? '',
            'cjds_pid'  => $details['pid'] ?? '',
            'shopify_pid'   => $current_sku ?? '',
        ];

        // Store image URLs instead of uploading
        if (!empty($image_urls)) {
            cjds_store_image_urls($product_id, $image_urls);
        } elseif (!empty($image_url)) {
            cjds_store_image_urls($product_id, [$image_url]);
        }

        // Materials
        if (!empty($details['materialNameEnSet']) && is_array($details['materialNameEnSet'])) {
            $meta_map['wc_cj_materials'] = implode(', ', array_map('sanitize_text_field', $details['materialNameEnSet']));
        }

        // Packing
        if (!empty($details['packingKeySet']) && is_array($details['packingKeySet'])) {
            $meta_map['wc_cj_packing_key'] = implode(', ', array_map('sanitize_text_field', $details['packingKeySet']));
        }

        foreach ($meta_map as $meta_key => $meta_value) {
            if ($meta_value !== '' && $meta_value !== null) {
                $wc_product->update_meta_data($meta_key, sanitize_text_field($meta_value));
            }
        }

        // Check for variants
        $has_variants = is_array($variants) && count($variants) > 0;

        // SIMPLE PRODUCT
        if (!$has_variants) {
            $product_id = $wc_product->save();
            cjds_set_local_featured_image_if_missing($product_id);
            update_post_meta($product_id, 'CJIMPORT', 'TRUE');
            error_log("[CJDS Sync] Simple product {$product_id} updated successfully\n", 3, CJSYNC . '/wccj_error.log');
            return array('status'=>'success','des'=>'Single Product updated');
        }

        // VARIABLE PRODUCT - Build attributes
        $attributes = [];
        foreach ($variants as $v) {
            if (empty($v['variantKey'])) continue;
            $keys = explode('-', $v['variantKey']);
            foreach ($keys as $index => $val) {
                $attr_name = 'attribute_' . ($index == 0 ? 'color' : 'size');
                $attributes[$attr_name][] = trim($val);
            }
        }

        $product_attributes = [];
        foreach ($attributes as $attr_key => $terms) {
            $attr = new WC_Product_Attribute();
            $attr->set_name(str_replace('attribute_', '', $attr_key));
            $attr->set_options(array_unique($terms));
            $attr->set_visible(true);
            $attr->set_variation(true);
            $product_attributes[$attr_key] = $attr;
        }

        $wc_product->set_attributes($product_attributes);
        $product_id = $wc_product->save();
        cjds_set_local_featured_image_if_missing($product_id);

        // Remove old variations
        foreach ($wc_product->get_children() as $child_id) wp_delete_post($child_id, true);

        // Create variations
        foreach ($variants as $v) {
            $vsku = sanitize_text_field($v['variantSku'] ?? '');
            $vkey = $v['variantKey'] ?? '';
            
            if (empty($vkey) || empty($vsku)) {
                error_log("[CJDS Sync] Skipping variant with empty key or SKU for product {$product_id}\n", 3, CJSYNC . '/wccj_error.log');
                continue;
            }

            // Check variant SKU conflicts
            $conflicting_product_id = cjds_sku_is_conflicting($vsku, $product_id);

            if ($conflicting_product_id !== false) {
                error_log(
                    "[CJDS Sync] CRITICAL VARIANT SKU CONFLICT: Variant SKU '{$vsku}' " .
                    "from CJ data conflicts with existing product ID {$conflicting_product_id}. " .
                    "Aborting entire sync for Product ID: {$product_id}\n", 
                    3, CJSYNC . '/wccj_error.log'
                );
                
                return array('status'=>'failed','des'=>"CRITICAL: Variant SKU '{$vsku}' conflicts with another product ID {$conflicting_product_id}. Aborting sync.");
            }

            // Create variation
            $variation = new WC_Product_Variation();
            $variation->set_parent_id($product_id);
            $variation->set_sku($vsku);

            // Prices
            $base_price = floatval($v['variantSellPrice'] ?? 0);
            if ($base_price < 0) $base_price = 0;
            $prices = cjds_calculate_prices($base_price);
            $variation->set_sale_price($prices['sale_price']);
            $variation->set_regular_price($prices['regular_price']);

            // Stock
            $inventory = intval($v['inventoryNum'] ?? 0);
            if ($inventory > 0) {
                $variation->set_manage_stock(true);
                $variation->set_stock_quantity($inventory);
            } else {
                $variation->set_manage_stock(false);
            }

            // Weight and dimensions
            $weight_g = floatval($v['variantWeight'] ?? 0);
            if ($weight_g > 0) $variation->set_weight(round($weight_g / 1000, 3));

            $length_mm = floatval($v['variantLength'] ?? 0);
            $width_mm  = floatval($v['variantWidth'] ?? 0);
            $height_mm = floatval($v['variantHeight'] ?? 0);
            if ($length_mm > 0) $variation->set_length(round($length_mm / 10, 2));
            if ($width_mm > 0)  $variation->set_width(round($width_mm / 10, 2));
            if ($height_mm > 0) $variation->set_height(round($height_mm / 10, 2));

            // Variant attributes
            $keys = explode('-', $vkey);
            $attributes_set = [];
            $index = 0;
            foreach ($product_attributes as $attr_key => $attr_obj) {
                $base_name = str_replace('attribute_', '', $attr_key);
                if(isset($keys[$index])) {
                    $attributes_set[$base_name] = trim($keys[$index]);
                }
                $index++;
            }
            $variation->set_attributes($attributes_set);

            // Suggested price
            if (isset($v['variantSugSellPrice'])) {
                $variation->update_meta_data('wc_cj_suggested_sell_price', floatval($v['variantSugSellPrice']));
            }

            // VID
            if (!empty($v['vid'])) {
                $variation->update_meta_data('wc_cj_vid', sanitize_text_field((string)$v['vid']));
            }

            $variation_id = $variation->save();

            // Store variant image URL instead of uploading
            $vimage = $v['variantImage'] ?? '';
            if (!empty($vimage)) {
                cjds_store_variant_image_url($variation_id, $vimage);
            }
            
            error_log("[CJDS Sync] Successfully created variant SKU {$vsku} (ID: {$variation_id}) for product {$product_id}\n", 3, CJSYNC . '/wccj_error.log');
        }

        $wc_product->save();
        update_post_meta($product_id, 'CJIMPORT', 'TRUE');
        error_log("[CJDS Sync] Variable product {$product_id} updated successfully with " . count($variants) . " variants\n", 3, CJSYNC . '/wccj_error.log');
        return array('status'=>'success','des'=>'Product updated');

    } catch (Exception $e) {
        error_log("[CJDS Sync] Exception in cjds_update_single_product for product {$product_id}: " . $e->getMessage() . " | Line: " . $e->getLine() . "\n", 3, CJSYNC . '/wccj_error.log');
        return array('status'=>'failed','des'=>'Exception: ' . $e->getMessage());
    }
}

/**
 * Standard price calculation
 */
function cjds_calculate_prices($base_price){
    $first_markup = $base_price * 1.25;

    $second_markup = $first_markup * 1.5;

    if ($base_price < 10) {
        $final_price = $second_markup * 1.15; // +15%
    } elseif ($base_price < 20) {
        $final_price = $second_markup * 1.20; // +20%
    } else {
        $final_price = $second_markup * 1.25; // +25%
    }

    return [
        'sale_price'    => round($second_markup, 2),
        'regular_price' => round($final_price, 2),
    ];
}

function cjds_get_or_create_category($category_path) {
    if (empty($category_path)) {
        return [];
    }

    // Clean up the category path - handle various separators
    $category_path = str_replace(['/', ' / ', ' > ', '，'], '>', $category_path); // Added Chinese comma
    $category_path = preg_replace('/\s*>\s*/', '>', $category_path); // Clean spacing around >
    $categories = array_map('trim', explode('>', $category_path));
    
    $category_ids = [];
    
    foreach ($categories as $cat_name) {
        if (empty($cat_name)) continue;
        
        $cat_name = trim($cat_name);
        
        // Try to find matching category by normalized name
        $matched_category = cjds_find_matching_category($cat_name);
        
        if ($matched_category) {
            $category_ids[] = $matched_category->term_id;
            error_log("[CJDS] Matched category: '{$cat_name}' to existing '{$matched_category->name}' (ID: {$matched_category->term_id})");
        } else {
            // Check if a category with similar slug already exists
            $cat_slug = sanitize_title($cat_name);
            $existing_by_slug = get_term_by('slug', $cat_slug, 'product_cat');
            
            if ($existing_by_slug) {
                // Found existing category by slug
                $category_ids[] = $existing_by_slug->term_id;
                error_log("[CJDS] Found existing category by slug: {$cat_name} (ID: {$existing_by_slug->term_id})");
            } else {
                // Create new category
                $new_term = wp_insert_term($cat_name, 'product_cat', [
                    'slug' => $cat_slug
                ]);
                
                if (!is_wp_error($new_term)) {
                    $category_ids[] = $new_term['term_id'];
                    error_log("[CJDS] Created new category: {$cat_name} (ID: {$new_term['term_id']})");
                } else {
                    error_log("[CJDS] Failed to create category: {$cat_name} | " . $new_term->get_error_message());
                }
            }
        }
    }
    
    return array_unique($category_ids);
}

function cjds_find_matching_category($search_name) {
    // Get all product categories
    $all_categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ]);
    
    if (is_wp_error($all_categories) || empty($all_categories)) {
        return false;
    }
    
    // Normalize the search name
    $normalized_search = cjds_normalize_category_name($search_name);
    
    // Look for exact match first
    foreach ($all_categories as $category) {
        $normalized_category = cjds_normalize_category_name($category->name);
        
        if ($normalized_category === $normalized_search) {
            return $category;
        }
    }
    
    // If no exact match, look for fuzzy match with multiple variations
    $search_variations = cjds_generate_category_variations($search_name);
    
    foreach ($all_categories as $category) {
        $category_variations = cjds_generate_category_variations($category->name);
        
        // Check if any variation matches
        foreach ($search_variations as $search_var) {
            foreach ($category_variations as $cat_var) {
                if ($search_var === $cat_var) {
                    return $category;
                }
            }
        }
    }
    
    return false;
}

function cjds_generate_category_variations($name) {
    $variations = [];
    
    // Base normalized version
    $normalized = cjds_normalize_category_name($name);
    $variations[] = $normalized;
    
    // Version with 'and' replaced by '&'
    $with_ampersand = str_replace(' and ', ' & ', $normalized);
    if ($with_ampersand !== $normalized) {
        $variations[] = $with_ampersand;
    }
    
    // Version with '&' replaced by 'and'
    $with_and = str_replace(' & ', ' and ', $normalized);
    if ($with_and !== $normalized) {
        $variations[] = $with_and;
    }
    
    // Version without 'and' or '&' at all
    $without_connector = preg_replace('/\s+(and|&)\s+/', ' ', $normalized);
    if ($without_connector !== $normalized) {
        $variations[] = $without_connector;
    }
    
    return array_unique($variations);
}

function cjds_normalize_category_name($name) {
    $name = str_replace('，', ',', $name);
    
    $name = str_replace(['、', '。', '；'], ' ', $name);
    
    $name = strtolower($name);
    $name = preg_replace('/[\'"`\x{2018}\x{2019}\x{201C}\x{201D}]/u', '', $name);
    
    $name = preg_replace('/\s*&\s*/', ' & ', $name);
    $name = preg_replace('/\s+and\s+/', ' and ', $name);
    
    $name = preg_replace('/[^a-z0-9\s&,]/u', '', $name);
    
    $name = preg_replace('/\s*,\s*/', ', ', $name);
    
    $name = preg_replace('/\s+/', ' ', $name);
    
    return trim($name);
}

/**
 * Upload image product for both parent and variants
 */
function cjds_upload_image($image_url, $parent_id = 0) {
	
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	if (empty($image_url)) return null;

	$existing = get_posts([
		'post_type'  => 'attachment',
		'meta_query' => [
			[
				'key'     => '_cjds_image_source',
				'value'   => $image_url,
				'compare' => '='
			]
		],
		'posts_per_page' => 1,
		'fields' => 'ids'
	]);

	if (!empty($existing)) {
		error_log("[CJDS] Reusing existing image for {$image_url}\n", 3, CJSYNC . '/wccj_error.log');
		return $existing[0]; 
	}

	$temp_file = wp_tempnam($image_url);
	if (!$temp_file) {
		error_log("[CJDS] Failed to create temp file for: {$image_url}\n", 3, CJSYNC . '/wccj_error.log');
		return null;
	}

	$response = wp_remote_get($image_url, ['timeout' => 60]);
	if (is_wp_error($response)) {
		@unlink($temp_file);
		error_log("[CJDS] Failed to download image: {$image_url} | " . $response->get_error_message()."\n", 3, CJSYNC . '/wccj_error.log');
		return null;
	}

	$file_content = wp_remote_retrieve_body($response);
	if (empty($file_content)) {
		@unlink($temp_file);
		error_log("[CJDS] Empty image content for: {$image_url}\n", 3, CJSYNC . '/wccj_error.log');
		return null;
	}

	file_put_contents($temp_file, $file_content);

	$filename  = basename(parse_url($image_url, PHP_URL_PATH));
	$file_type = wp_check_filetype($filename, null);

	$file_array = [
		'name'     => $filename,
		'tmp_name' => $temp_file,
		'type'     => $file_type['type'] ?? '',
		'error'    => 0,
		'size'     => filesize($temp_file)
	];

	$attachment_id = media_handle_sideload($file_array, $parent_id);

	if (is_wp_error($attachment_id)) {
		@unlink($temp_file);
		error_log("[CJDS] Upload failed for {$image_url}: " . $attachment_id->get_error_message()."\n", 3, CJSYNC . '/wccj_error.log');
		return null;
	}

	update_post_meta($attachment_id, '_cjds_image_source', $image_url);

	error_log("[CJDS] Uploaded new image (ID: {$attachment_id}) from {$image_url}\n", 3, CJSYNC . '/wccj_error.log');
	return $attachment_id;
}

/**
 * set the image to the product
 */
function cjds_set_product_image($product_id, $image_url) {
    $attachment_id = cjds_upload_image($image_url, $product_id);
    if ($attachment_id) {
        set_post_thumbnail($product_id, $attachment_id);
        return $attachment_id;
    }
    return null;
}

function cjds_sync_product_handle_forced($product_id, $sku, $new_status=null, $current_status=null, $force_update=false){
    
    if (empty($sku) || !$product_id) {
        $return = array('status'=>'failed','des'=>'Missing SKU or product ID.');
        return $return;
    }

    $product = wc_get_product($product_id);
    
    // Only check if updated when NOT forcing update
    if (!$force_update) {
        $is_updated = cjds_isupdated(get_post_meta($product_id,'_CJ_last_processed_date',true));
        
        if ($is_updated=='updated'){
            $return = array(
                'status'=>'failed',
                'des'=>'This is an updated product (synced within last 7 days).',
                'stat'=>array('status'=>'skipped', 'des'=>'Recently updated'),
                'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=skipped&des=Recently+updated"),
                'new_status' => $new_status ? 'yes' : 'no',
                'html_content' => $new_status ? '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>' : '<span class="dashicons dashicons-star-empty" style="color:#aaa;"></span>'
            );
            return $return;
        }
    }
    
    // Handle products where parent SKU doesn't start with 'CJ'
    if (strncmp($sku, 'CJ', 2) !== 0) {
        
        error_log("[CJDS Sync] Parent SKU doesn't start with CJ: {$sku} (Product ID: {$product_id})\n", 3, CJSYNC . '/wccj_error.log');
        
        $variations = $product->get_available_variations();

        $base_sku = cjds_extract_parent_sku_from_variants($product_id);
        
        if (empty($base_sku)) {
            error_log("[CJDS Bulk Sync] No variant SKU for product ID {$product_id}\n", 3, CJSYNC . '/wccj_error.log');
            $return = array(
                'status'=>'failed',
                'des'=>'No variant SKU for product ID.',
                'stat'=>array('status'=>'failed', 'des'=>'No variant SKU'),
                'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=failed&des=No+variant+SKU"),
                'new_status' => $new_status ? 'yes' : 'no',
                'html_content' => $new_status ? '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>' : '<span class="dashicons dashicons-star-empty" style="color:#aaa;"></span>'
            );
            return $return;
        }

        // Get the data from CJ api by sku
        $pid = get_post_meta($product_id,'cjds_pid',true);
        
        if (empty($pid)){
            $cj_data = get_cg_product_by_sku($base_sku);
            
            // Check if API response is valid and has data
            if (empty($cj_data) || !isset($cj_data['list']) || empty($cj_data['list'])) {
                error_log("[CJDS Sync] Failed to get product data from CJ API for SKU: {$base_sku}\n", 3, CJSYNC . '/wccj_error.log');
                $return = array(
                    'status'=>'failed',
                    'des'=>'Could not find product in CJ Dropshipping API',
                    'stat'=>array('status'=>'failed', 'des'=>'API returned no data'),
                    'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=failed&des=API+no+data"),
                    'new_status' => 'no',
                    'html_content' => '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
                );
                return $return;
            }
            
            // Safely get PID
            $pid = isset($cj_data['list'][0]['pid']) ? $cj_data['list'][0]['pid'] : '';
            
            if (empty($pid)) {
                error_log("[CJDS Sync] No PID found in API response for SKU: {$base_sku}\n", 3, CJSYNC . '/wccj_error.log');
                $return = array(
                    'status'=>'failed',
                    'des'=>'No PID found in CJ API response',
                    'stat'=>array('status'=>'failed', 'des'=>'No PID in response'),
                    'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=failed&des=No+PID"),
                    'new_status' => 'no',
                    'html_content' => '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
                );
                return $return;
            }
            
            // Update PID meta
            update_post_meta($product_id,'cjds_pid', $pid);
            update_post_meta($product_id, '_CJ_last_processed_date', current_time('mysql'));
            update_post_meta($product_id, '_CJ_link', 'success');
        }
        
        $cjds_prodduct_json = ABSPATH . "fetchcj/products/$pid.json";

        // Check if JSON file exists
        if (file_exists($cjds_prodduct_json)) {
            $create_data = json_decode(file_get_contents($cjds_prodduct_json), true);
            
            // Validate JSON data
            if (empty($create_data)) {
                error_log("[CJDS Sync] Invalid JSON file for PID: {$pid}, regenerating...\n", 3, CJSYNC . '/wccj_error.log');
                // Delete corrupted file
                @unlink($cjds_prodduct_json);
            } else {
                // JSON is valid, update product
                $stat = cjds_update_single_product($product_id, $create_data);
                
                $new_status = ($current_status === 'yes') ? false : true;
                
                $return = array(
                    'status'=>'success',
                    'des'=>'Updated product from CJ json file',
                    'stat'=>$stat,
                    'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=".$stat['status']."&des=".$stat['des']),
                    'new_status' => $new_status ? 'yes' : 'no',
                    'html_content' => $new_status ? '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>' : '<span class="dashicons dashicons-star-empty" style="color:#aaa;"></span>'
                );
                return $return;
            }
        }
        
        // JSON doesn't exist or was corrupted, fetch from API
        error_log("[CJDS Sync] JSON not found for PID {$pid}, fetching from API...\n", 3, CJSYNC . '/wccj_error.log');
        $create_data = get_cg_product_by_pid($pid);
        
        if (empty($create_data)) {
            error_log("[CJDS Sync] Failed to fetch product data from API for PID: {$pid}\n", 3, CJSYNC . '/wccj_error.log');
            $return = array(
                'status'=>'failed',
                'des'=>'Could not fetch product details from CJ API',
                'stat'=>array('status'=>'failed', 'des'=>'API fetch failed'),
                'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=failed&des=API+fetch+failed"),
                'new_status' => 'no',
                'html_content' => '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
            );
            return $return;
        }

        // Create json file 
        $data['details'] = $create_data;
        $result_cjdata = cjds_create_json_singleproduct($data); 

        if ($result_cjdata){
            // Update the single product
            $create_data = json_decode(file_get_contents($cjds_prodduct_json), true);
            $stat = cjds_update_single_product($product_id, $create_data);
            error_log("[CJDS Sync ".$stat['status']."] ".$stat['des']." {$product_id}\n", 3, CJSYNC . '/wccj_error.log');
            $new_status = ($current_status === 'yes') ? false : true;
            
            $return = array(
                'status'=>'success',
                'des'=>'Created Json and Updated product from CJ json file',
                'stat'=>$stat,
                'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=".$stat['status']."&des=".$stat['des']),
                'new_status' => $new_status ? 'yes' : 'no',
                'html_content' => $new_status ? '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>' : '<span class="dashicons dashicons-star-empty" style="color:#aaa;"></span>'
            );

            return $return;            
        }
        
        // If we get here, something went wrong
        $return = array(
            'status'=>'failed',
            'des'=>'Failed to create JSON or update product',
            'stat'=>array('status'=>'failed', 'des'=>'JSON creation failed'),
            'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=failed&des=JSON+creation+failed"),
            'new_status' => 'no',
            'html_content' => '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
        );
        return $return;
        
    } else {
        
        $cj_data = get_cg_product_by_sku($sku);
        
        // Check if API response is valid
        if (empty($cj_data) || !isset($cj_data['list']) || empty($cj_data['list'])) {
            error_log("[CJDS Sync] Failed to get product data from CJ API for SKU: {$sku}\n", 3, CJSYNC . '/wccj_error.log');
            $return = array(
                'status'=>'failed',
                'des'=>'Could not find product in CJ Dropshipping API',
                'stat'=>array('status'=>'failed', 'des'=>'API returned no data'),
                'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=failed&des=API+no+data"),
                'new_status' => 'no',
                'html_content' => '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
            );
            return $return;
        }
        
        // Safely get PID
        $pid = isset($cj_data['list'][0]['pid']) ? $cj_data['list'][0]['pid'] : '';
        
        if (empty($pid)) {
            error_log("[CJDS Sync] No PID found in API response for SKU: {$sku}\n", 3, CJSYNC . '/wccj_error.log');
            $return = array(
                'status'=>'failed',
                'des'=>'No PID found in CJ API response',
                'stat'=>array('status'=>'failed', 'des'=>'No PID in response'),
                'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=failed&des=No+PID"),
                'new_status' => 'no',
                'html_content' => '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
            );
            return $return;
        }
        
        update_post_meta($product_id,'cjds_pid', $pid);

        $cjds_prodduct_json = ABSPATH . "fetchcj/products/$pid.json";

        // Check if JSON file exists
        if (file_exists($cjds_prodduct_json)) {
            $create_data = json_decode(file_get_contents($cjds_prodduct_json), true);
            
            // Validate JSON data
            if (empty($create_data)) {
                error_log("[CJDS Sync] Invalid JSON file for PID: {$pid}, regenerating...\n", 3, CJSYNC . '/wccj_error.log');
                // Delete corrupted file
                @unlink($cjds_prodduct_json);
            } else {
                // JSON is valid, update product
                $stat = cjds_update_single_product($product_id, $create_data);
                error_log("[CJDS Sync ".$stat['status']."] ".$stat['des']." {$product_id}\n", 3, CJSYNC . '/wccj_error.log');
                $new_status = ($current_status === 'yes') ? false : true;
                
                $return = array(
                    'status'=>'success',
                    'des'=>'Updated product from CJ json file',
                    'stat'=>$stat,
                    'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=".$stat['status']."&des=".$stat['des']),
                    'new_status' => $new_status ? 'yes' : 'no',
                    'html_content' => $new_status ? '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>' : '<span class="dashicons dashicons-star-empty" style="color:#aaa;"></span>'
                );
                return $return;
            }
        }
        
        // JSON doesn't exist or was corrupted, fetch from API
        error_log("[CJDS Sync] JSON not found for PID {$pid}, fetching from API...\n", 3, CJSYNC . '/wccj_error.log');
        $create_data = get_cg_product_by_pid($pid);
        
        if (empty($create_data)) {
            error_log("[CJDS Sync] Failed to fetch product data from API for PID: {$pid}\n", 3, CJSYNC . '/wccj_error.log');
            $return = array(
                'status'=>'failed',
                'des'=>'Could not fetch product details from CJ API',
                'stat'=>array('status'=>'failed', 'des'=>'API fetch failed'),
                'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=failed&des=API+fetch+failed"),
                'new_status' => 'no',
                'html_content' => '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
            );
            return $return;
        }

        // Create json file 
        $data['details'] = $create_data;
        $result_cjdata = cjds_create_json_singleproduct($data); 

        if ($result_cjdata){ 
            // Update the single product
            $create_data = json_decode(file_get_contents($cjds_prodduct_json), true);
            $stat = cjds_update_single_product($product_id, $create_data);
            error_log("[CJDS Sync ".$stat['status']."] ".$stat['des']." {$product_id}\n", 3, CJSYNC . '/wccj_error.log');
            $new_status = ($current_status === 'yes') ? false : true;
            
            $return = array(
                'status'=>'success',
                'des'=>'Created Json and Updated product from CJ json file',
                'stat'=>$stat,
                'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=".$stat['status']."&des=".$stat['des']),
                'new_status' => $new_status ? 'yes' : 'no',
                'html_content' => $new_status ? '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>' : '<span class="dashicons dashicons-star-empty" style="color:#aaa;"></span>'
            );
            return $return;                    
        }
        
        // If we get here, something went wrong
        $return = array(
            'status'=>'failed',
            'des'=>'Failed to create JSON or update product',
            'stat'=>array('status'=>'failed', 'des'=>'JSON creation failed'),
            'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=failed&des=JSON+creation+failed"),
            'new_status' => 'no',
            'html_content' => '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
        );
        return $return;
    }
    
}

/**
 * Check if SKU belongs to another product or variation.
 */
function cjds_sku_is_conflicting($sku, $current_product_id = 0) {
    if (empty($sku)) return false;

    $found_id = wc_get_product_id_by_sku($sku);
    if (!$found_id) return false;

    if ((int)$found_id === (int)$current_product_id) return false;

    return $found_id;
}

function cjds_store_product_videos($product_id, $video_urls) {
    if (empty($video_urls) || !is_array($video_urls)) {
        return false;
    }

    update_post_meta($product_id, 'wc_cjds_product_video', implode(', ', $video_urls));

    if (!empty($video_urls[0])) {
        update_post_meta($product_id, 'video_url', sanitize_url($video_urls[0]));
        error_log("[CJDS] Stored first video URL for product {$product_id}: {$video_urls[0]}\n", 3, CJSYNC . '/wccj_error.log');
    }
    return true;
}

function cjds_get_product_videos($product_id) {
    $video_json = get_post_meta($product_id, 'wc_cjds_product_videos_array', true);
    if (!empty($video_json)) {
        $videos = json_decode($video_json, true);
        if (is_array($videos)) {
            return $videos;
        }
    }
    return [];
}

function cjds_get_primary_video_url($product_id) {
    $video_url = get_post_meta($product_id, 'video_url', true);
    if (!empty($video_url)) {
        return $video_url;
    }

    $videos = cjds_get_product_videos($product_id);
    if (!empty($videos[0])) {
        return $videos[0];
    }

    return '';
}


// ============= URL Image Handler =============

/**
 * Store product image URLs in custom fields
 */
function cjds_store_image_urls($product_id, $image_urls) {
    if (empty($image_urls) || !is_array($image_urls)) {
        return false;
    }

    // Store all image URLs as JSON
    update_post_meta($product_id, '_cjds_image_urls', wp_json_encode($image_urls));

    // Store first image as featured image URL
    if (!empty($image_urls[0])) {
        update_post_meta($product_id, '_cjds_featured_image_url', sanitize_url($image_urls[0]));
        update_post_meta($product_id, '_thumbnail_id', 'external');
    }

    // Store gallery URL (excluding first image)
    if (count($image_urls) > 1) {
        $gallery_urls = array_slice($image_urls, 1);
        update_post_meta($product_id, '_cjds_gallery_urls', wp_json_encode($gallery_urls));
    }

    error_log("[CJDS] Stored " . count($image_urls) . " image URLs for product {$product_id}\n", 3, CJSYNC . '/wccj_error.log');
    return true;
}

/**
 * Store variant image URL
 */
function cjds_store_variant_image_url($variation_id, $image_url) {
    if (empty($image_url)) {
        return false;
    }
    
    update_post_meta($variation_id, '_cjds_variant_image_url', sanitize_url($image_url));
    update_post_meta($variation_id, '_thumbnail_id', 'external'); // Mark as external
    
    error_log("[CJDS] Stored variant image URL for variation {$variation_id}\n", 3, CJSYNC . '/wccj_error.log');
    return true;
}

/**
 * Get stored image URLs for a product
 */
function cjds_get_product_image_urls($product_id) {
    $image_urls_json = get_post_meta($product_id, '_cjds_image_urls', true);
    if (!empty($image_urls_json)) {
        return json_decode($image_urls_json, true);
    }
    return [];
}

/**
 * Get featured image URL
 */
function cjds_get_featured_image_url($product_id) {
    return get_post_meta($product_id, '_cjds_featured_image_url', true);
}

/**
 * Get gallery image URLs
 */
function cjds_get_gallery_image_urls($product_id) {
    $gallery_urls_json = get_post_meta($product_id, '_cjds_gallery_urls', true);
    if (!empty($gallery_urls_json)) {
        return json_decode($gallery_urls_json, true);
    }
    return [];
}

/**
 * Get variant image URL
 */
function cjds_get_variant_image_url($variation_id) {
    return get_post_meta($variation_id, '_cjds_variant_image_url', true);
}

function cjds_set_local_featured_image_if_missing($product_id) {
    // If already has real featured image → do nothing
    if (get_post_thumbnail_id($product_id)) {
        return;
    }

    // Pull from stored external image field
    $url = get_post_meta($product_id, '_cjds_featured_image_url', true);
    if (empty($url)) return;

    // Upload image
    $attachment_id = cjds_upload_image($url, $product_id);

    if ($attachment_id) {
        set_post_thumbnail($product_id, $attachment_id);
        update_post_meta($product_id, '_cjds_has_local_featured', 1);
        update_post_meta($product_id, 'video_thumbnail', $attachment_id);
    }
}

function cjds_get_video_thumbnail($product_id) {
    return get_post_meta($product_id, 'video_thumbnail', true);
}

// ========= External JSON Loader ==========
define('CJDS_SHARED_JSON_PATH', '/home/customer/www/sals3.com/public_html/fetchcj/products/');

function cjds_get_json_data($pid) {
    if (empty($pid)) {
        return null;
    }
    
    // Path to shared JSON file on same server
    $shared_json_file = CJDS_SHARED_JSON_PATH . $pid . '.json';
    
    // Check shared location first
    if (file_exists($shared_json_file)) {
        error_log("[CJDS] Loading from shared location: {$shared_json_file}\n", 3, CJSYNC . '/wccj_error.log');
        
        $data = json_decode(file_get_contents($shared_json_file), true);
        
        if (!empty($data)) {
            return $data;
        }
    }
    
    // Fallback to local subdomain location if exists
    $local_json_file = ABSPATH . "fetchcj/products/{$pid}.json";
    
    if (file_exists($local_json_file)) {
        error_log("[CJDS] Loading from local subdomain: {$local_json_file}\n", 3, CJSYNC . '/wccj_error.log');
        return json_decode(file_get_contents($local_json_file), true);
    }
    
    error_log("[CJDS] JSON file not found for PID: {$pid}\n", 3, CJSYNC . '/wccj_error.log');
    return null;
}

add_action('init', function() {
    if (isset($_GET['test_shared_json']) && current_user_can('manage_options')) {
        echo "<h2>Testing Shared JSON Access</h2>";
        
        echo "<p><strong>Shared Path:</strong> " . CJDS_SHARED_JSON_PATH . "</p>";
        
        // Check if directory exists
        if (file_exists(CJDS_SHARED_JSON_PATH)) {
            echo "<p style='color:green;'>✅ Directory exists!</p>";
            
            // Check if readable
            if (is_readable(CJDS_SHARED_JSON_PATH)) {
                echo "<p style='color:green;'>✅ Directory is readable!</p>";
                
                // List some files
                $files = glob(CJDS_SHARED_JSON_PATH . '*.json');
                echo "<p><strong>Found " . count($files) . " JSON files</strong></p>";
                
                if (!empty($files)) {
                    echo "<p>Sample files:</p><ul>";
                    foreach (array_slice($files, 0, 5) as $file) {
                        echo "<li>" . basename($file) . "</li>";
                    }
                    echo "</ul>";
                    
                    // Test reading a file
                    $test_file = $files[0];
                    $data = json_decode(file_get_contents($test_file), true);
                    
                    if ($data) {
                        echo "<p style='color:green;'>✅ Successfully read and parsed: " . basename($test_file) . "</p>";
                    } else {
                        echo "<p style='color:red;'>❌ Failed to parse JSON from: " . basename($test_file) . "</p>";
                    }
                }
            } else {
                echo "<p style='color:red;'>❌ Directory is NOT readable! Check permissions.</p>";
            }
        } else {
            echo "<p style='color:red;'>❌ Directory does NOT exist!</p>";
            echo "<p>Please create it or update the path in your configuration.</p>";
        }
        
        wp_die();
    }
});

add_action('init', function() {
    if (isset($_GET['test_email'])) {
        
        // 1. Hook in BEFORE sending to catch the failure details
        add_action('wp_mail_failed', function($error) {
            echo '<h1>Email sending failed!</h1>';
            echo '<pre>';
            // Print the error message and the details passed to wp_mail
            print_r($error->get_error_messages());
            echo '<br><strong>Error Data:</strong><br>';
            print_r($error->get_error_data());
            echo '</pre>';
            exit; // Stop execution so we can read the dump
        });

        $to      = 'yijolif681@luckfeed.com'; // Change to your email
        $subject = 'WP Mail Debug Test';
        $message = 'Testing with error tracing enabled.';
        
        wp_mail($to, $subject, $message);
    }
});