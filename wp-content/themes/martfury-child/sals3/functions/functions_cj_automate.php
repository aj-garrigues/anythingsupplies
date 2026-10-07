<?php

// Define constants for the AJAX action and batch size
define( 'CJ_BATCH_ACTION', 'process_product_batch' );
define( 'CJ_BATCH_SIZE', 10 ); 
 
/**
 * 1. Define the Custom Menu Page
 */
function cj_automation_add_admin_menu() {
    add_menu_page(
        'CJ Automation Settings',
        'CJ Automation',
        'manage_options',
        'cj-automation-main',
        'cj_automation_page_content',
        'dashicons-code-standards',
        30
    );
}
add_action( 'admin_menu', 'cj_automation_add_admin_menu' );


/**
 * 2. Enqueue Script ONLY on the CJ Automation Page
 */
function cj_automation_enqueue_admin_script( $hook ) {
    // Target the specific admin page hook: 'toplevel_page_MENU_SLUG'
    if ( 'toplevel_page_cj-automation-main' !== $hook ) {
        return;
    }
    
    // Enqueue script
    wp_enqueue_script( 
        'cj-dashboard-ajax', 
        get_stylesheet_directory_uri() . '/import_cj/assets/js/cj-dashboard-ajax.js',
        array('jquery'), 
        '1.0', 
        true 
    ); 

    // Pass AJAX URL and security nonce
    wp_localize_script( 'cj-dashboard-ajax', 'CjAjax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'cj_product_fetch_nonce' )
    ));
}
add_action( 'admin_enqueue_scripts', 'cj_automation_enqueue_admin_script' );

/**
 * 3. Display the Content (Button & Result Area) on the Automation Page
 */

add_action('admin_init', function() {
    // Only run on admin pages
    if (!is_admin()) {
        return;
    }
    
    // POST handler: Process form submission
    if (isset($_POST['cjds_file_name']) && isset($_POST['getCGproduct'])) {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'cjds_form_nonce')) {
            return;
        }
        wp_redirect("admin.php?page=cj-automation-main&cjproduct=" . base64_encode($_POST['cjds_file_name']));
        exit;
    }

    // GET handler: Process the product creation
    if (isset($_GET['cjproduct']) && $_GET['cjproduct'] != "") {
        
        $url = base64_decode($_GET['cjproduct']);
        $pid = getLastUrlSegment($url, "-");
        
        if (empty($pid)) {
            wp_die('Invalid PID extracted from URL. Please check the URL format.');
        }
        
        echo "<h2>Processing CJ Product: PID {$pid}</h2>";
        
        // Define the JSON file path
        $cjds_product_json = CJDS_SHARED_JSON_PATH . "$pid.json";
        
        // Step 1: Check if JSON file already exists
        if (file_exists($cjds_product_json)) {
            echo "<p style='color:blue;'>✓ Using cached JSON file for PID {$pid}</p>";
            $create_data = json_decode(file_get_contents($cjds_product_json), true);
            
            if (empty($create_data) || !isset($create_data['details'])) {
                wp_die('<p style="color:red;">Cached JSON file is invalid or corrupted.</p>');
            }
            
            // Extract details from cached JSON
            $create_data = $create_data['details'];
            echo "<p style='color:green;'>Product data loaded from cache</p>";
        } else {
            // Step 1: Fetch product data from CJ API
            echo "<p>Fetching product data from CJ API...</p>";
            $create_data = get_cg_product_by_pid($pid);
            
            if (empty($create_data)) {
                wp_die('<p style="color:red;"> Failed to fetch product data from CJ API for PID: ' . esc_html($pid) . '</p>');
            }
            
            echo "<p style='color:green;'>Product data fetched successfully from API</p>";
            
            // Step 2: Generate JSON file
            echo "<p>Generating JSON file...</p>";
            $data['details'] = $create_data;
            $result_cjdata = cjds_create_json_singleproduct($data);
            
            if (!$result_cjdata) {
                wp_die('<p style="color:red;"> Failed to create JSON file</p>');
            }
            
            echo "<p style='color:green;'>JSON file created: " . esc_html($cjds_product_json) . "</p>";
        }
        
        // Step 3: Check if product already exists
        $product_sku = $create_data['productSku'] ?? '';
        if (empty($product_sku)) {
            wp_die('<p style="color:red;"> No SKU found in product data</p>');
        }
        
        $existing_product_id = wc_get_product_id_by_sku($product_sku);
        
        if ($existing_product_id) {
            echo "<p style='color:orange;'>Product with SKU '{$product_sku}' already exists (ID: {$existing_product_id})</p>";
            echo "<p><a href='" . admin_url("post.php?post={$existing_product_id}&action=edit") . "' class='button button-primary'>View Existing Product</a></p>";
            wp_die();
        }
        
        // Step 4: Create new product
        echo "<p>Creating new WooCommerce product...</p>";
        $product_id = cjds_create_product_from_json($pid, $create_data);

        if (is_wp_error($product_id)) {
            wp_die('<p style="color:red;"> Failed to create product: ' . $product_id->get_error_message() . '</p>');
        }

        if (!$product_id) {
            wp_die('<p style="color:red;"> Failed to create product</p>');
        }
        
        echo "<p style='color:green; font-weight:bold;'>Successfully created Product ID: {$product_id}</p>";
        
        // Step 5: Update product with full details from JSON
        echo "<p>Updating product with complete details...</p>";

        echo "<p>Verifying product in database...</p>";
        sleep(1); // Give database 1 second to commit
        $verify_product = wc_get_product($product_id);

        if (!$verify_product) {
            wp_die('<p style="color:red;"> Product was created but not found in database. Database write failed.</p>');
        }
        echo "<p style='color:blue;'>✓ Product verified in database</p>";

        // Clear all caches after verification
        wp_cache_flush();
        clean_post_cache($product_id);
        
        if (file_exists($cjds_product_json)) {
            $json_data = json_decode(file_get_contents($cjds_product_json), true);
            
            if (empty($json_data)) {
                echo "<p style='color:orange;'>Invalid JSON data, but product was created</p>";
            } else {
                // Pass product ID as integer explicitly
                $stat = cjds_update_single_product((int)$product_id, $json_data);
                
                if (isset($stat['status'])) {
                    if ($stat['status'] === 'success' || $stat['status'] === 'Update') {
                        echo "<p style='color:green;'>Product updated: " . esc_html($stat['des']) . "</p>";
                    } else {
                        echo "<p style='color:orange;'>Update status: " . esc_html($stat['des']) . "</p>";
                        error_log("Update failed for Product ID: {$product_id}. Response: " . print_r($stat, true), 3, CJSYNC . '/wccj_error.log');
                    }
                }
            }
        }
        
        // Step 7: Final cache clear and product refresh 
        clean_post_cache($product_id);
        
        // Step 8: Show final summary
        $product_edit_url = admin_url("post.php?post={$product_id}&action=edit");
        wp_redirect($product_edit_url);
        exit;
    }
}, 20);

function cjds_create_product_from_json($pid, $product_data) {
    if (!class_exists('WC_Product')) {
        error_log('WooCommerce is not active. Cannot create product.');
        return new WP_Error('wc_not_active', 'WooCommerce is not active');
    }
    
    // Extract product details
    $product_name = isset($product_data['productNameEn']) ? $product_data['productNameEn'] : ($product_data['nameEn'] ?? 'Untitled Product');
    $product_sku = $product_data['productSku'] ?? '';
    $description = !empty($product_data['description']) ? wp_kses_post($product_data['description']) : '';
    $variants = $product_data['variants'] ?? [];
    
    // Determine product type based on variants
    $has_variants = is_array($variants) && count($variants) >= 1;
    $product_type = $has_variants ? 'variable' : 'simple';
    
    error_log("[CJDS Create] Creating {$product_type} product. Variants count: " . count($variants));
    
    // Create the appropriate product object
    if ($product_type === 'variable') {
        $product = new WC_Product_Variable();
    } else {
        $product = new WC_Product_Simple();
    }
    
    // Set basic product properties
    $product->set_name(sanitize_text_field($product_name));
    $product->set_description($description);
    $product->set_status('draft');
    
    // Set SKU
    if (!empty($product_sku)) {
        $product->set_sku($product_sku);
    }
    
    // Set visibility and stock status
    $product->set_catalog_visibility('visible');
    $product->set_stock_status('instock');
    
    // Set pricing
    $base_price = floatval($product_data['sellPrice'] ?? 0);
    if ($base_price > 0) {
        $prices = cjds_calculate_prices($base_price);
        $product->set_regular_price($prices['regular_price']);
        $product->set_sale_price($prices['sale_price']);
        $product->set_price($prices['sale_price']);
    }
    
    // Stock management for simple products
    if (!$has_variants && !empty($variants)) {
        $inventory = intval($variants[0]['inventoryNum'] ?? 0);
        if ($inventory > 0) {
            $product->set_manage_stock(true);
            $product->set_stock_quantity($inventory);
        } else {
            $product->set_manage_stock(false);
        }
    }
    
    // Save the product to get an ID
    $product_id = $product->save();
    
    if (!$product_id) {
        error_log('[CJDS Create] Failed to save product');
        return new WP_Error('product_save_failed', 'Failed to save product');
    }
    
    // CRITICAL: Force database commit and cache flush
    wp_cache_flush();
    wp_cache_delete($product_id, 'posts');
    clean_post_cache($product_id);
    wc_delete_product_transients($product_id);
    
    // VERIFY: Check if product was actually saved to database
    global $wpdb;
    $db_check = $wpdb->get_var(
        $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_type = 'product'", $product_id)
    );
    
    if (!$db_check) {
        error_log("[CJDS Create] Product ID {$product_id} NOT found in database after save!");
        return new WP_Error('product_db_failed', 'Product not persisted to database');
    }
    
    error_log("[CJDS Create] ✓ Product ID {$product_id} verified in database");
    
    // Store CJ metadata
    update_post_meta($product_id, 'cjds_pid', $pid);
    update_post_meta($product_id, '_CJ_last_processed_date', current_time('mysql'));
    update_post_meta($product_id, '_CJ_link', 'success');
    update_post_meta($product_id, 'wc_cj_price', $base_price);

    update_post_meta($product_id, 'CJIMPORT', 'TRUE');
    
    // Clear caches again after metadata
    clean_post_cache($product_id);
    wc_delete_product_transients($product_id);
    
    error_log("[CJDS Create] Successfully created {$product_type} product ID {$product_id} with SKU {$product_sku}");
    
    return $product_id;
}

function getLastUrlSegment(string $inputUrl, string $delimiter = '-'): string {
    $inputUrl = preg_replace('#^https?://[^/]+/#', '', $inputUrl);
    
    $parts = explode('-p-', $inputUrl);

    // 2. Get the last element of the array.
    $target_with_html = end($parts);

    // 3. Remove the '.html' extension from the end of the string.
    $product_id = str_replace('.html', '', $target_with_html);
    
    return trim($product_id);
}

function cj_automation_page_content() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>CJ Automation Dashboard</h1>
        <p>This page contains tools for CJ Dropshipping fecthing:</p>
        
        <div class="card" style="max-width: 400px; padding: 20px;">
            <h2>Single Product Fetcher Tool</h2>
            <p>Generate source file from cjDropshipping API. Please enter PID source.</p>
            <form method="post" name="getCGproduct">
                <?php wp_nonce_field('cjds_form_nonce'); ?>
                <p>
                    <label for="cjds_file_name">File Name:</label>
                    <input type="hidden" id="pgname" name="page" value="cj-automation-main" />
                    <input type="hidden" name="getCGproduct" value="1" />
                    <input type="text" id="cjds_file_name" name="cjds_file_name" value="<?php echo esc_attr($file_name ?? ''); ?>" style="width: 100%;" />
                </p>
                <p>
                    <button type="submit" id="cjds_generate_file_button" class="button button-primary" style="width: 100%;">Generate File</button>
                </p>
            </form>
        </div>
    </div>
	
    <?php

}

/**
 * 4. Server-Side AJAX Handler (No Change Needed)
 * This function remains the same as it is independent of where the button is placed.
 */
function cj_automation_fetch_product() {

	
    if ( ! check_ajax_referer( 'cj_product_fetch_nonce', 'security' ) ) {
        wp_send_json_error( 'Security check failed.' );
        wp_die();
    }
    
	//loop all product every 50 product per page
	// B. Get Input (Page number)
	
    $current_page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
    
    // C. Fetch the Batch of Products
    $args = array(
        'post_type'      => 'product', 
        'post_status'    => 'publish',
        'fields'         => 'ids', // Optimization: fetch only IDs
    );
    
    $product_query = new WP_Query( $args );
    $product_ids   = $product_query->posts;

    // D. Process the Batch
    $products_processed = 0; 
    foreach ( $product_ids as $product_id ) {
        // --- YOUR BATCH PROCESSING LOGIC GOES HERE ---
        
        // Example: Get product object and perform a simple update/check
        $product = wc_get_product( $product_id );
        if ( $product ) {
			sleep(40);
            // Log the action for demonstration
            error_log("Processing Product ID: {$product_id} | SKU: {$product->get_sku()}");
            $fetch_result[] = cjds_sync_product_handle($product_id, $product->get_sku());
			
            // Example: Update a product meta field
            update_post_meta( $product_id, '_CJ_last_processed_date', current_time('mysql') );
			
            $products_processed++;
			
        }
    }
    
    // E. Determine Next Step
    $max_pages = $product_query->max_num_pages;
    $next_page = $current_page + 1;
    $is_finished = ( $next_page > $max_pages );
    
    // F. Send Response
    $data = array(
        'finished'      => $is_finished,
        'next_page'     => $is_finished ? 0 : $next_page,
        'total_pages'   => $max_pages,
        'current_page'  => $current_page,
        'processed_count' => $products_processed,
        'fetch_result' => $fetch_result
    );
	
    
    wp_send_json_success( $data );
	
    wp_die();
}
add_action( 'wp_ajax_fetch_jc_product', 'cj_automation_fetch_product' );



//===========================================================================================================


// --- CONSTANTS ---
define('JC_CRON_HOOK', 'jc_product_batch_update_hook');
define('JC_OFFSET_OPTION', 'jc_batch_update_offset');
define('JC_BATCH_SIZE', 20);
// New constant to store the dynamic total count retrieved from the database
define('JC_TOTAL_COUNT_OPTION', 'jc_total_records_count');


// ----------------------------------------------------
// 1. CUSTOM CRON INTERVAL
// ----------------------------------------------------

/**
 * Add a custom cron schedule for 'Every Minute' (60 seconds).
 */
function jc_add_custom_cron_intervals( $schedules ) {
    $schedules['every_minute'] = array(
        'interval' => 60,       // Interval in seconds
        'display'  => esc_html__( 'Every Minute' ),
    );
    return $schedules;
}
//add_filter( 'cron_schedules', 'jc_add_custom_cron_intervals' );


// ----------------------------------------------------
// 2. SCHEDULING AND EXTERNAL TRIGGER
// ----------------------------------------------------

/**
 * Schedules the recurring batch process ONLY if ?fetchingjc=yes is present in the URL.
 * Also calculates the total number of products and initializes the offset.
 */
 
function jc_schedule_batch_update_conditionally() {
    global $wpdb; // Required for running the SQL query
    wp_clear_scheduled_hook( JC_CRON_HOOK );
	        delete_option( JC_OFFSET_OPTION );
        delete_option( JC_TOTAL_COUNT_OPTION );
	    // --- STOP CRON CHECK ---
    if ( isset( $_GET['stopjc'] ) && 'yes' === $_GET['stopjc'] ) {
        wp_clear_scheduled_hook( JC_CRON_HOOK );
        // Also clean up options to reset the process entirely
        delete_option( JC_OFFSET_OPTION );
        delete_option( JC_TOTAL_COUNT_OPTION );
        error_log("CRON: Batch Update event STOPPED by URL trigger. Scheduled hook and options cleared.");
        return; // Stop execution to prevent scheduling below
    }
	
    // Trigger check for the custom URL parameter
    if ($_GET['fetchingjc']=='yes' ) {
		
         error_log("CRON: Nothing 2\n", 3, CJSYNC . '/wccj_error.log');
        // DYNAMICALLY CALCULATE TOTAL COUNT based on the processing query criteria
        $total_count = (int) $wpdb->get_var(
            "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'"
        );

        // Store the dynamic total count in the options table
        update_option( JC_TOTAL_COUNT_OPTION, $total_count );

        // Ensure the offset option exists, initialize to 0 if not
        if ( get_option( JC_OFFSET_OPTION ) === false ) {
            add_option( JC_OFFSET_OPTION, 0 );
        }

        // Check if the event is already scheduled
        if ( ! wp_next_scheduled( JC_CRON_HOOK ) ) {
            
            // Schedule the event to run starting now, and repeat every minute
            wp_schedule_event( time(), 'every_minute', JC_CRON_HOOK );
            
            error_log("CRON: Batch Update event scheduled and total count (current: {$total_count}) stored.", 3, CJSYNC . '/wccj_error.log');
        } else {
             error_log("CRON: Batch Update event already scheduled. Total count refreshed to {$total_count}.", 3, CJSYNC . '/wccj_error.log');
        }
		
		 error_log("CRON: Nothing 1\n", 3, CJSYNC . '/wccj_error.log');
		 
    }
	 error_log("CRON: Nothing\n", 3, CJSYNC . '/wccj_error.log');
}

// Hook this function to run when WordPress initializes

//add_action( 'wp', 'jc_schedule_batch_update_conditionally' );


// ----------------------------------------------------
// 3. BATCH PROCESSING LOGIC
// ----------------------------------------------------

/**
 * The function executed by the cron job every minute.
 * Processes one batch of records and updates the offset.
 */
function jc_run_batch_update____() {
    global $wpdb;
    
    // 1. Retrieve the current processing offset and dynamic total count
    $offset = (int) get_option( JC_OFFSET_OPTION, 0 );
    $total_records = (int) get_option( JC_TOTAL_COUNT_OPTION, 0 ); // <-- Dynamic total

    // 2. Sanity check: If no records, abort gracefully
    if ( $total_records === 0 ) {
        error_log("Batch Update aborted: No records found in the database matching criteria.", 3, CJSYNC . '/wccj_error.log');
        return; 
    }
    
    // 3. Sanity check: If offset exceeds total records (e.g., records were deleted), reset to 0
    if ( $offset >= $total_records ) {
        $offset = 0;
    }

    error_log("--- Running Batch Update: Offset {$offset} / Total {$total_records} ---", 3, CJSYNC . '/wccj_error.log');

    // 4. Database Query to fetch the batch
    $sql = $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' LIMIT %d OFFSET %d",
        JC_BATCH_SIZE, 
        $offset
    );
    
    $post_ids = $wpdb->get_col( $sql );
    
    if ( ! empty( $post_ids ) ) {
        
        // 5. Process each item in the batch
        foreach ( $post_ids as $post_id ) {
            $product = wc_get_product( $post_id );
			

			$fetch_result = cjds_sync_product_handle($post_id, $product->get_sku());
			 
			/*
				$return = array(
							'status'=>'failed',
							'des'=>'This is updated product.',
							'stat'=>$stat,
							'redirect'=>admin_url("post.php?post={$product_id}&action=edit&status=".$stat['status']."&des=".$stat['des']),
							'new_status' => $new_status ? 'yes' : 'no', //ajax approach 
							'html_content' => $new_status ? '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>' : '<span class="dashicons dashicons-star-empty" style="color:#aaa;"></span>' //ajax approach 
							);
			*/
			
            // Example: Update a product meta field
            update_post_meta( $product_id, '_CJ_last_processed_date', current_time('mysql') );
			
            // Update the post meta key '_link'
            //update_post_meta( $post_id, '_link', $new_link_value );
            
            // Log the action (check your debug.log)
            error_log("Updated product ID: {$post_id} Status: ".$fetch_result['status']." Description: ".$fetch_result['des']." with link: {$new_link_value}", 3, CJSYNC . '/wccj_error.log');
        } 
		
        
        // 6. Calculate and save the next offset
        $next_offset = $offset + JC_BATCH_SIZE;
        
        if ( $next_offset >= $total_records ) { // Use dynamic total count for cycle check
            // Cycle back to 0 when the batch finishes the last record
            update_option( JC_OFFSET_OPTION, 0 );
            error_log("Batch cycle completed. Resetting offset to 0.", 3, CJSYNC . '/wccj_error.log');
        } else {
            // Save the new offset for the next minute's run
            update_option( JC_OFFSET_OPTION, $next_offset );
        }
        
    } else {
        // If we received no post IDs, it means the processing is done or the count was inaccurate
        error_log("Batch finished or no more records found (expected total: {$total_records}). Resetting offset to 0.", 3, CJSYNC . '/wccj_error.log');
        update_option( JC_OFFSET_OPTION, 0 );
        
        // Optional: clear the cron if the job is truly finished and shouldn't restart
        // wp_clear_scheduled_hook( JC_CRON_HOOK );
    }
}
// Attach the batch function to the custom hook
//add_action( JC_CRON_HOOK, 'jc_run_batch_update' );


// ----------------------------------------------------
// 4. DEACTIVATION CLEANUP (Optional, for plugins)
// ----------------------------------------------------

/**
 * Cleanup function to unschedule the event and remove the offset option on deactivation.
 */
function jc_cron_cleanup() {
    //wp_clear_scheduled_hook( JC_CRON_HOOK );
    //delete_option( JC_OFFSET_OPTION );
    //delete_option( JC_TOTAL_COUNT_OPTION ); // Also delete the total count option
}
// If this code is in a plugin, use register_deactivation_hook( __FILE__, 'jc_cron_cleanup' );
// If in functions.php, it will remain until manually removed.

//=================================================================================================================

// URL TRIGGER
add_action( 'init', function() {
    if ( isset($_GET['cjds_import']) && $_GET['cjds_import'] == 1 ) {
       cjds_import_all_products_from_json();
        wp_die('CJDS import completed. Check WooCommerce → Products and wp-content/debug.log for details.');
    }
});

/**
 * Loads the json and import the products
 */
function cjds_import_all_products_from_json() {
    if (!defined('CJDS_SHARED_JSON_PATH')) {
        error_log('[CJDS Import] CJDS_SHARED_JSON_PATH not defined');
        return;
    }

    $dir = CJDS_SHARED_JSON_PATH;

    if (!file_exists($dir)) {
        error_log('[CJDS Import] Shared directory not found: ' . $dir);
        return;
    }

    $files = array_filter(
        glob($dir . '/*.json'),
        function($file) {
            return stripos(basename($file), 'batch') === false;
        }
    );
    if (empty($files)) {
        error_log('[CJDS Import] No JSON files found in shared location.');
        return;
    }

    error_log('[CJDS Import] Starting import from SHARED location: ' . $dir);
    error_log('[CJDS Import] Found ' . count($files) . ' JSON files');

    $imported = 0;
    $skipped = 0;
    $failed = 0;


    foreach ($files as $file) {
        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if ($data === null) {
            error_log("[CJDS Import] JSON decode failed: $file");
            $failed++;
            continue;
        }

        // Check if it's a single product object
        if (isset($data['sku']) || isset($data['productSku']) || isset($data['productId'])) {
            try {
                $return_stat = cjds_import_product($data);

                $imported++;
                error_log("[CJDS Import] Imported: " . basename($file));
            } catch (Exception $e) {
                $failed++;
                error_log("[CJDS Import] Failed: " . basename($file) . " - " . $e->getMessage());
            }
        } 
        // Check if it's an array of products
        elseif (is_array($data) && isset($data[0])) {
            foreach ($data as $product) {
                try {
                    cjds_import_product($product);
                    $imported++;
                } catch (Exception $e) {
                    $failed++;
                    error_log("[CJDS Import] Failed product in " . basename($file) . " - " . $e->getMessage());
                }
            }
            error_log("[CJDS Import] Processed file: $file (" . count($data) . " products)");
        } 
        else {
            error_log("[CJDS Import] Invalid JSON format: $file");
            $failed++;
            continue;
        }

        // Small delay to avoid overwhelming server
        usleep(100000); // 0.1 second
    }
}

function cjds_import_product($product) {
    if (!class_exists('WC_Product')) return;

    set_time_limit(300);

    // DEBUG: Log the raw product data structure
    error_log("[CJDS Import] Processing product with keys: " . implode(', ', array_keys($product)));
 
    // Map fields from the new JSON structure
    $title      = sanitize_text_field($product['nameEn'] ?? 'Untitled');
    $sku        = sanitize_text_field($product['sku'] ?? ($product['productSku'] ?? ''));
    $price_raw  = $product['sellPrice'] ?? '0';
    
    // Check if product already exists
    $existing_id = wc_get_product_id_by_sku($sku);
    if ($existing_id) {
        $wc_product = wc_get_product($existing_id);
        error_log("[CJDS Import] Skip import if product exists ID: {$existing_id}");
        return 'f-sku'; // Skip import if product exists
    }

    // DEBUG: Log extracted values
    error_log("[CJDS Import] Title: {$title}, SKU: {$sku}, Price: {$price_raw}");
    
    $image_url  = $product['bigImage'] ?? '';
    $details    = $product['details'] ?? [];
    $variants   = $details['variants'] ?? [];
    $desc       = !empty($details['description']) ? wp_kses_post($details['description']) : '';
    $image_urls = $details['productImageSet'] ?? [];
    $pid        = $details['pid'] ?? ''; // Extract PID

    // Handle video URLs
    $video_urls = [];
    if (!empty($product['productVideo']) && is_array($product['productVideo'])) {
        $video_urls = $product['productVideo'];
    } elseif (!empty($details['productVideo']) && is_array($details['productVideo'])) {
        $video_urls = $details['productVideo'];
    }

    // Price range handling
    if (strpos($price_raw, '-') !== false) {
        [$min_price, $max_price] = array_map('floatval', explode('-', $price_raw));
        $price = $max_price;
    } else {
        $price = floatval($price_raw);
    }

    if (empty($sku) || $price <= 0) {
        error_log("[CJDS] Skipped product (missing SKU or invalid price): $title | SKU: '$sku' | Price: $price");
        return;
    }

    // Check for variants
    $has_variants = is_array($variants) && count($variants) > 0;

    // Check if product already exists
    $existing_id = wc_get_product_id_by_sku($sku);
    if ($existing_id) {
        $wc_product = wc_get_product($existing_id);
        error_log("[CJDS Import] Updating existing product ID: {$existing_id}");
    } else {
        $wc_product = $has_variants ? new WC_Product_Variable() : new WC_Product_Simple();
        error_log("[CJDS Import] Creating new product: {$title}");
    }
    
    // Basic Product Data
    $wc_product->set_name($title);
    if (!empty($desc)) {
        $wc_product->set_description(wp_kses_post($desc));
    }
    $wc_product->set_sku($sku);
    $wc_product->set_status('publish');

    // Category handling
    $category_path = $details['categoryName'] ?? ($product['categoryName'] ?? '');
    if (!empty($category_path)) {
        error_log("[CJDS] Found category path for {$title}: {$category_path}");
        $category_ids = cjds_get_or_create_category($category_path);
        if (!empty($category_ids)) {
            $wc_product->set_category_ids($category_ids);
        }
    }

    // Set product creation date (if available)
    // Note: The new format doesn't have createAt in the same location
    // You might need to adjust this based on your needs
    
    // ===== ENHANCED META FIELDS (matching sync function) =====
    $meta_map = [
        'wc_cj_price'          => $price,
        'wc_cj_default_area'   => $product['defaultArea'] ?? '',
        'wc_cj_country_code'   => $product['areaCountryCode'] ?? '',
        'wc_cj_product_type'   => $details['productType'] ?? '',
        'cjds_pid'             => $pid, // Store PID
        'shopify_pid'          => $sku, // Store original SKU as shopify_pid
        
        // === SYNC METADATA (NEW) ===
        '_CJ_last_processed_date' => current_time('mysql'), // Current timestamp
        '_CJ_link'                => 'success', // Mark as successfully synced
    ];

    // Materials
    if (!empty($details['materialNameEnSet']) && is_array($details['materialNameEnSet'])) {
        $meta_map['wc_cj_materials'] = implode(', ', array_map('sanitize_text_field', $details['materialNameEnSet']));
    }

    // Packing
    if (!empty($details['packingKeySet']) && is_array($details['packingKeySet'])) {
        $meta_map['wc_cj_packing_key'] = implode(', ', array_map('sanitize_text_field', $details['packingKeySet']));
    }

    // Apply meta fields
    foreach ($meta_map as $meta_key => $meta_value) {
        if ($meta_value !== '' && $meta_value !== null) {
            $safe_value = is_string($meta_value) ? sanitize_text_field($meta_value) : $meta_value;
            $wc_product->update_meta_data($meta_key, $safe_value);
        }
    }

    // SIMPLE PRODUCT
    if (!$has_variants) {
        $prices = cjds_calculate_prices($price);
        $wc_product->set_sale_price($prices['sale_price']);
        $wc_product->set_regular_price($prices['regular_price']);

        $product_id = $wc_product->save();

        // Store gallery + featured URL BEFORE thumbnail processing
        if (!empty($image_urls)) {
            cjds_store_image_urls($product_id, $image_urls);
        } elseif ($image_url) {
            cjds_store_image_urls($product_id, [$image_url]);
        }

        update_post_meta($product_id, 'CJIMPORT', 'TRUE');

        // Now upload featured image (because meta is now present)
        cjds_set_local_featured_image_if_missing($product_id);

        // Store video URLs (NEW)
        if (!empty($video_urls)) {
            cjds_store_product_videos($product_id, $video_urls);
        }
        
        error_log("[CJDS Import] ✅ Imported simple product: {$title} (ID: {$product_id}) with sync metadata");
        return;
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

    // Store gallery and featured metadata first
    if (!empty($image_urls)) {
        cjds_store_image_urls($product_id, $image_urls);
    } elseif ($image_url) {
        cjds_store_image_urls($product_id, [$image_url]);
    }

    update_post_meta($product_id, 'CJIMPORT', 'TRUE');

    // Now upload the local featured image
    cjds_set_local_featured_image_if_missing($product_id);

    error_log("[CJDS Import] Saved parent product {$title} (ID: {$product_id}).");

    // Store video URLs (NEW)
    if (!empty($video_urls)) {
        cjds_store_product_videos($product_id, $video_urls);
    }

    // Remove old variations (if updating existing product)
    if ($existing_id) {
        foreach ($wc_product->get_children() as $child_id) {
            wp_delete_post($child_id, true);
        }
    }

    // Create variations
    foreach ($variants as $v) {
        $vsku   = sanitize_text_field($v['variantSku']);
        $vimage = $v['variantImage'] ?? '';
        $vkey   = $v['variantKey'] ?? '';
        $base_variant_price = floatval($v['variantSellPrice'] ?? 0);

        if (empty($vkey)) {
            error_log("[CJDS Import] Skipping variant with empty key for SKU: {$vsku}");
            continue;
        }

        // SKU conflict check for variants (NEW - matching sync behavior)
        $conflicting_product_id = cjds_sku_is_conflicting($vsku, $product_id);
        if ($conflicting_product_id !== false) {
            error_log(
                "[CJDS Import] ⚠️ VARIANT SKU CONFLICT: SKU '{$vsku}' conflicts with product ID {$conflicting_product_id}. Skipping variant.",
            );
            continue;
        }

        // Calculate prices
        $prices = cjds_calculate_prices($base_variant_price);

        // Create variation
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($product_id);
        $variation->set_sku($vsku);

        // Inventory
        $inventory = intval($v['inventoryNum'] ?? 0);
        if ($inventory > 0) {
            $variation->set_manage_stock(true);
            $variation->set_stock_quantity($inventory);
        } else {
            $variation->set_manage_stock(false);
        }

        // Weight
        $weight_g = floatval($v['variantWeight'] ?? 0);
        if ($weight_g > 0) {
            $weight_kg = round($weight_g / 1000, 3);
            $variation->set_weight($weight_kg);
        }

        // Dimensions
        $length_mm = floatval($v['variantLength'] ?? 0);
        $width_mm  = floatval($v['variantWidth'] ?? 0);
        $height_mm = floatval($v['variantHeight'] ?? 0);

        if ($length_mm > 0) $variation->set_length(round($length_mm / 10, 2));
        if ($width_mm > 0)  $variation->set_width(round($width_mm / 10, 2));
        if ($height_mm > 0) $variation->set_height(round($height_mm / 10, 2));

        // Attributes
        $keys = explode('-', $vkey);
        $attributes_set = [];
        $index = 0;
        foreach ($product_attributes as $attr_key => $attr_obj) {
            $base_name = str_replace('attribute_', '', $attr_key);
            $attributes_set[$base_name] = $keys[$index] ?? '';
            $index++;
        }
        $variation->set_attributes($attributes_set);

        // Prices
        $variation->set_sale_price($prices['sale_price']);
        $variation->set_regular_price($prices['regular_price']);

        // Suggested price
        if (isset($v['variantSugSellPrice'])) {
            $variation->update_meta_data('wc_cj_suggested_sell_price', floatval($v['variantSugSellPrice']));
        }

        // Store VID (matching sync behavior)
        if (!empty($v['vid'])) {
            $variation->update_meta_data('wc_cj_vid', sanitize_text_field((string)$v['vid']));
        }

        $variation_id = $variation->save();

        // Store variant image URL
        if (!empty($vimage)) {
            cjds_store_variant_image_url($variation_id, $vimage);
        }

        error_log("[CJDS Import] Saved variation SKU {$vsku} (ID: {$variation_id}) | Sale: {$prices['sale_price']} | Regular: {$prices['regular_price']}");
    }

    $wc_product->save(); 
    error_log("[CJDS Import] ✅ SUCCESSFULLY imported variable product: {$title} (" . count($variants) . " variants) with full sync metadata");
}

function custom_product_admin_enqueue_scripts( $hook ) {
    // Check if we are on the WooCommerce Product listing page
    if ( 'edit.php' !== $hook || !isset($_GET['post_type']) || $_GET['post_type'] !== 'product' ) {
        return;
    }

    // Enqueue the custom admin script
    wp_enqueue_script( 
        'product-ajax-script', 
        get_stylesheet_directory_uri() . '/import_cj/assets/js/product-admin-ajax.js', // Adjust path
        array('jquery'), 
        '1.0', 
        true 
    );

    // Pass necessary data to the script
    wp_localize_script( 'product-ajax-script', 'ProductAjax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'product_featured_nonce' )
    ));
}
add_action( 'admin_enqueue_scripts', 'custom_product_admin_enqueue_scripts' );

// Register the AJAX handler function
add_action( 'wp_ajax_toggle_product_featured', 'handle_product_featured_toggle' );

function handle_product_featured_toggle() {
    // 1. Security Check
    if ( ! check_ajax_referer( 'product_featured_nonce', 'security' ) ) {
        wp_send_json_error( 'Security check failed.' );
        wp_die();
    }

    // 2. Validate and Sanitize Input
    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    $current_status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

    if ( $product_id === 0 ) {
        wp_send_json_error( 'Invalid Product ID.' );
        wp_die();
    }

    // 3. Perform the Update (The Core Logic)
    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        wp_send_json_error( 'Product not found.' );
        wp_die();
    }
    
    // Toggle the featured status
    $new_status = ( $current_status === 'yes' ) ? false : true; 
	$cjds_pid = get_post_meta($product_id, 'cjds_pid',true);
	$sku = $product->get_sku();
	
	// Handle products where parent SKU doesn't start with 'CJ'
	$result_handle = cjds_sync_product_handle($product_id, $sku, $new_status, $current_status);
	
	if (isset($result_handle['status']) && $result_handle['status'] =="success"){
		update_post_meta( $product_id, '_CJ_last_processed_date', current_time('mysql') );
		update_post_meta( $product_id, '_CJ_link', 'success');
		wp_send_json_success( $result_handle );
		wp_die();	
	
	}elseif(isset($result_handle['status']) && $result_handle['status'] =="failed"){
		update_post_meta( $product_id, '_CJ_last_processed_date', current_time('mysql') );
		update_post_meta( $product_id, '_CJ_link', 'failed');
		$response_data = array(
			'new_status' => 'no',
			'html_content' => '<span class="dashicons dashicons-dismiss-filled" style="color:#7ad03a; font-weight:600"></span>'
		);
		
		wp_send_json_success( $response_data );
		wp_die();
	}	
	
}

function remove_product_brand_column( $columns ) {
    
    // --- IMPORTANT: Find the correct brand column slug ---
    // You need to inspect your site's HTML or check your brand plugin's code 
    // to confirm the exact slug. Here are the most common slugs:
    
    // 1. Common slug for WooCommerce Brands (by WooCommerce):
    if ( isset( $columns['product_brand'] ) ) {
        unset( $columns['product_brand'] );
    }

    // 2. Common slug for taxonomy-based brands (like 'brand' taxonomy):
    if ( isset( $columns['taxonomy-brand'] ) ) {
        unset( $columns['taxonomy-brand'] );
    }

    // 3. Other possible slugs (Less common):
    if ( isset( $columns['brand'] ) ) {
        unset( $columns['brand'] );
    }
    
    // --- End of common slugs ---
    
    return $columns;
}
// Use a high priority (e.g., 99) to ensure this runs after the plugin has added the column.
add_filter( 'manage_edit-product_columns', 'remove_product_brand_column', 99 );



// --- FILE: functions.php ---


// --- FILE: functions.php ---

/**
 * Inject the custom button and input field above the product table.
 */
function cj_add_bulk_update_ui() {
    // Only display on the product list screen
    if ( !isset($_GET['post_type']) || $_GET['post_type'] !== 'product' ) {
        return;
    }
    
    // Check if the current user can manage options (security)
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    ?>
    <div class="cj-bulk-controls">
        <label for="cj-new-sku-tag">New CJ SKU Tag:</label>
        <input type="text" id="cj-new-sku-tag" placeholder="Enter tag value..." style="width: 200px;">
        
        <button id="run-cj-bulk-update" class="button button-primary">
            Run CJ Bulk Update on Selected
        </button>
        
        <span class="spinner" id="cj-bulk-spinner" style="float: none; margin: 0 5px 0 10px; visibility: hidden;"></span>
        <div id="cj-bulk-message" class="clear" style="padding-top: 10px;"></div>
    </div>
    <hr class="wp-header-end">
    <?php
}
// Hook this action to run right before the main table content
add_action( 'manage_posts_extra_tablenav', 'cj_add_bulk_update_ui' );
// 1. Add the Custom Bulk Action to the Products screen
function cj_register_custom_bulk_action( $bulk_actions ) {
    $bulk_actions['cj_update_sku'] = __( 'Set New CJ SKU Meta', 'your-text-domain' );
    return $bulk_actions;
}
add_filter( 'bulk_actions-edit-product', 'cj_register_custom_bulk_action' );

// 2. Enqueue the JavaScript file for the Admin Product List
function cj_enqueue_bulk_action_script( $hook ) {
    // Only load on the product list screen
    if ( 'edit.php' !== $hook || !isset($_GET['post_type']) || $_GET['post_type'] !== 'product' ) {
        return;
    }

    wp_enqueue_script( 
        'cj-bulk-ajax', 
        get_stylesheet_directory_uri() . '/import_cj/assets/js/cj-bulk-ajax.js', // Adjust path
        array('jquery'), 
        '1.0', 
        true 
    );

    // Pass AJAX URL and security nonce to the script
    wp_localize_script( 'cj-bulk-ajax', 'CjBulk', array(
        'ajax_url'    => admin_url( 'admin-ajax.php' ),
        'nonce'       => wp_create_nonce( 'cj_bulk_sku_nonce' ),
        'action_name' => 'cj_process_bulk_sku',
        'bulk_slug'   => 'cj_update_sku' // Pass the slug of the custom bulk action
    ));
}
//add_action( 'admin_enqueue_scripts', 'cj_enqueue_bulk_action_script' );


// 3. Server-Side AJAX Handler: Process the update
function cj_process_bulk_sku_handler() {
    // A. Security Check
    if ( ! check_ajax_referer( 'cj_bulk_sku_nonce', 'security' ) ) {
        wp_send_json_error( 'Security check failed.' );
        wp_die();
    }
    
    // B. Get Input and Validate
    $product_ids = isset( $_POST['product_ids'] ) ? array_map( 'absint', $_POST['product_ids'] ) : array();
    $new_sku_tag = isset( $_POST['new_sku_tag'] ) ? sanitize_text_field( $_POST['new_sku_tag'] ) : '';

    if ( empty( $product_ids ) || empty( $new_sku_tag ) ) {
        wp_send_json_error( 'Missing product IDs or new SKU tag.' );
        wp_die();
    }
    
    $updated_count = 0;
    
    // C. Perform the Batch Update
    foreach ( $product_ids as $product_id ) {
        // Update the custom meta field with the new tag
        // We use '_cjskutag' as the new meta key
        $updated = update_post_meta( $product_id, '_cjskutag', $new_sku_tag );
        
        // Check if the update succeeded (true) or if the value was new (meta_id)
        if ( $updated !== false ) {
            $updated_count++;
        }
    }

    // D. Send Response
    $message = sprintf( 
        _n( 'Successfully updated meta for %s product.', 
            'Successfully updated meta for %s products.', 
            $updated_count, 
            'your-text-domain' 
        ), 
        number_format_i18n( $updated_count ) 
    );

    wp_send_json_success( array(
        'message' => $message,
        'count'   => $updated_count
    ));
    
    wp_die();
}
add_action( 'wp_ajax_cj_process_bulk_sku', 'cj_process_bulk_sku_handler' );

// ========= Import Page ==========

function cj_batch_import_admin_menu() {
    add_menu_page(
        'CJ Batch Import',
        'CJ Batch Import',
        'manage_options',
        'cj-batch-import',
        'cj_batch_import_page_content',
        'dashicons-upload',
        31
    );
}
add_action('admin_menu', 'cj_batch_import_admin_menu');

function cj_batch_import_page_content() {
    ?>
    <div class="wrap">
        <h1>CJ Batch Import</h1>
        <p>Import products from JSON files in <code>/fetchcj/products/</code> directory.</p>
        
        <div style="margin: 20px 0;">
            <button id="cj-import-start" class="button button-primary">Start Import</button>
            <button id="cj-import-start-new" class="button button-secondary">Start New Import</button>
            <button id="cj-import-abort" class="button" disabled>Abort</button>
        </div>
        
        <div id="cj-import-stats" style="margin: 20px 0; padding: 15px; background: #f0f0f1; border-left: 4px solid #2271b1;">
            <strong>Status:</strong> <span id="cj-status-text">Ready to start</span><br>
            <strong>Progress:</strong> <span id="cj-progress-text">0/0 (0%)</span><br>
            <strong>Imported:</strong> <span id="cj-imported-count">0</span> | 
            <strong>Skipped:</strong> <span id="cj-skipped-count">0</span> | 
            <strong>Failed:</strong> <span id="cj-failed-count">0</span>
        </div>
        
        <div id="cj-import-progress" style="margin-top:20px;">
            <progress id="cj-progress-bar" value="0" max="100" style="width:100%; height:30px;"></progress>
        </div>
        
        <h3>Import Log:</h3>
        <div id="cj-import-log" style="margin-top:10px; max-height:400px; overflow:auto; background:#fff; border:1px solid #ddd; padding:10px; font-family: monospace; font-size: 12px;"></div>
        
    </div>
    <?php
    
    // Enqueue JS
    wp_enqueue_script('cj-batch-import-js', get_stylesheet_directory_uri() . '/import_cj/assets/js/cj-batch-import.js', array('jquery'), '1.1', true);
    wp_localize_script('cj-batch-import-js', 'CjBatchImport', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('cj_batch_import_nonce')
    ));
}

/**
 * AJAX Handler for Batch Import
 */
add_action('wp_ajax_cj_batch_import', 'cj_batch_import_ajax_handler');

function cj_batch_import_ajax_handler() {
    // Security check
    check_ajax_referer('cj_batch_import_nonce', 'security');
    
    // Increase time limit
    set_time_limit(300);
    
    $batch_size = 1;

    if (!empty($_POST['reset_total'])) {
        delete_transient('cj_import_total');
        delete_option('cj_import_progress');

        wp_send_json_success([
            'reset' => true,
            'message' => 'Total count reset'
        ]);
    }


    // Check-only status fetch
    if (!empty($_POST['check_only'])) {
        $progress = get_option('cj_import_progress', false);
        $total_products = get_transient('cj_import_total', 0);

        wp_send_json_success([
            'offset'      => $progress['offset'] ?? 0,
            'file_offset' => $progress['file_offset'] ?? 0,
            'file_index'  => $progress['file_index'] ?? 0,
            'total'       => $total_products,
            'progress'    => ($total_products > 0 && isset($progress['offset']))
                                ? round(($progress['offset'] / $total_products) * 100)
                                : 0
        ]);
    }

    $SKIP_PRODUCTS = 0;

    $offset      = isset($_POST['offset']) ? intval($_POST['offset']) : $SKIP_PRODUCTS;
    $file_offset = isset($_POST['file_offset']) ? intval($_POST['file_offset']) : 0;
    $file_index  = isset($_POST['file_index']) ? intval($_POST['file_index']) : 0;
    $aborted = isset($_POST['aborted']) ? filter_var($_POST['aborted'], FILTER_VALIDATE_BOOLEAN) : false;
    $progress = get_option('cj_import_progress', false);

    if ($progress && $offset === 0 && $file_index === 0) {
        $offset      = intval($progress['offset']);
        $file_offset = intval($progress['file_offset']);
        $file_index  = intval($progress['file_index']);

        $log = ["<span style='color:purple;'>Resuming import from last saved checkpoint…</span>"];
    }

    // Handle abort
    if ($aborted) {
        wp_send_json_success([
            'aborted' => true,
            'done' => true,
            'log' => ['<span style="color:red;"><strong>Import aborted by user</strong></span>']
        ]);
    }

    if (defined('CJDS_SHARED_JSON_PATH') && file_exists(CJDS_SHARED_JSON_PATH)) {
        $dir = CJDS_SHARED_JSON_PATH;
    } else {
        // Fallback to local directory if shared path fails
        $dir = ABSPATH . 'fetchcj/products';
    }
    
    if (!file_exists($dir)) {
        wp_send_json_error(['message' => 'Directory not found: ' . $dir]);
    }
    
    $files = array_filter(glob($dir . '/*.json'), function($file) {
        return stripos(basename($file), 'batch') === false;
    });
    
    if (empty($files)) {
        wp_send_json_error(['message' => 'No JSON files found']);
    }
    
    sort($files); 
    
    // Total count logic (run once at start)
    $total_products = get_transient('cj_import_total');

    if ($total_products === false) { // transient not set yet
        $total_products = 0;
        foreach ($files as $file) {
            $json = file_get_contents($file);
            $data = json_decode($json, true);
            if (!$data) continue;

            if (isset($data['sku']) || isset($data['productSku']) || isset($data['productId'])) {
                $total_products += 1;
            } elseif (is_array($data) && isset($data[0])) {
                $total_products += count($data);
            }
        }
        set_transient('cj_import_total', $total_products, HOUR_IN_SECONDS);
        $log[] = '<span style="color:blue;"><strong>Total products found: ' . $total_products . '</strong></span>';
    } else {
        $log = [];
    }

    // Load File
    if (!isset($files[$file_index])) {
        delete_option('cj_import_progress');
        wp_send_json_success([
            'done' => true, 'offset' => $offset, 'total' => $total_products, 
            'progress' => 100, 'log' => ['<span style="color:green;"><strong>All Done!</strong></span>']
        ]);
    }
    
    $current_file = $files[$file_index];
    $json = file_get_contents($current_file);
    $data = json_decode($json, true);
    
    // Handle bad JSON
    if ($data === null) {
        wp_send_json_success([
            'offset' => $offset, 'file_index' => $file_index + 1, 
            'total' => $total_products, 'progress' => $total_products > 0 ? round(($offset/$total_products)*100, 1) : 0,
            'log' => ['<span style="color:red;">JSON Decode Failed: ' . basename($current_file) . '</span>']
        ]);
    }
    
    // Normalize Data
    if (isset($data['sku']) || isset($data['productSku']) || isset($data['productId'])) {
        $products = [$data];
    } elseif (is_array($data) && isset($data[0])) {
        $products = $data;
    } else {
        // Skip invalid file
        wp_send_json_success([
            'offset' => $offset, 'file_index' => $file_index + 1, 
            'total' => $total_products, 'progress' => $total_products > 0 ? round(($offset/$total_products)*100, 1) : 0,
            'log' => ['<span style="color:red;">Invalid JSON structure: ' . basename($current_file) . '</span>']
        ]);
    }
        
    // Slicing
    $current_batch = array_slice($products, $file_offset, $batch_size);

    $imported = 0; $skipped = 0; $failed = 0;

    foreach ($current_batch as $product) {
        $sku = $product['sku'] ?? $product['productSku'] ?? '';
        $title = $product['nameEn'] ?? 'Untitled';

        if (empty($sku)) {
            $skipped++;
            $log[] = '<span style="color:orange;">Skipped (No SKU): ' . esc_html($title) . '</span>';
            continue;
        }

        $existing_id = wc_get_product_id_by_sku($sku);

        if ($existing_id) {
            $skipped++;
            $log[] = '<span style="color:orange;">Skipped (Exists): ' . esc_html($sku) . '</span>';
            continue;
        }

        try {
            cjds_import_product($product); 
            $imported++;
            $log[] = '<span style="color:green;">Imported: ' . esc_html($sku) . '</span>';
        } catch (Exception $e) {
            $failed++;
            $log[] = '<span style="color:red;">Failed ' . esc_html($sku) . ': ' . $e->getMessage() . '</span>';
        }
    }

    $file_index_offset = 0;

    for ($i = 0; $i < $file_index; $i++) {
        $temp_json = file_get_contents($files[$i]);
        $temp_data = json_decode($temp_json, true);

        if (!$temp_data) continue;

        if (isset($temp_data['sku']) || isset($temp_data['productSku']) || isset($temp_data['productId'])) {
            $file_index_offset += 1;
        } elseif (is_array($temp_data) && isset($temp_data[0])) {
            $file_index_offset += count($temp_data);
        }
    }



    $new_file_offset = $file_offset + count($current_batch);
    $new_offset = $file_index_offset + $new_file_offset;

    if ($new_offset >= $total_products) {

        delete_option('cj_import_progress');

        wp_send_json_success([
            'done'     => true,
            'offset'   => $total_products,
            'total'    => $total_products,
            'progress' => 100,
            'log'      => ['<span style="color:green;"><strong>Import completed!</strong></span>']
        ]);
    }

    $next_file_index = $file_index;
    $next_file_offset = $new_file_offset;

    if ($new_file_offset >= count($products)) {
        $next_file_index++;
        $next_file_offset = 0;
        if (isset($files[$next_file_index])) {
            $log[] = '<strong>Opening next file: ' . basename($files[$next_file_index]) . '</strong>';
        }
    }

    $done = false;
    if (!isset($files[$next_file_index])) {
        $done = true;
    }

    update_option('cj_import_progress', [
        'offset'      => $new_offset,        // Update the overall offset
        'file_offset' => $next_file_offset,  // Update the file offset for the current file
        'file_index'  => $next_file_index,   // Update to the next file index if needed
    ]);

    // Send the response back with updated values
    wp_send_json_success([
        'processed'    => count($current_batch),
        'imported'     => $imported,
        'skipped'      => $skipped,
        'failed'       => $failed,
        'offset'       => $new_offset,         // Send updated offset
        'file_offset'  => $next_file_offset,  // Send updated file offset
        'file_index'   => $next_file_index,   // Send updated file index
        'total'        => max($total_products, 1), // Avoid division by zero
        'done'         => $done,
        'log'          => $log,
        'progress'     => $total_products > 0 ? round(($new_offset / $total_products) * 100, 0) : 0,
        'current_file' => basename($current_file)
    ]);

}

function get_cg_product_by_pid($pid, $store=null, $access_token=null) {
    // Try to get from shared location first
    $json_data = cjds_get_json_data($pid);
    
    if (!empty($json_data) && isset($json_data['details'])) {
        error_log("[CJDS] Using existing JSON for PID: {$pid}\n", 3, CJSYNC . '/wccj_error.log');
        return $json_data['details'];
    }
    
    // If not found, fetch from API
    error_log("[CJDS] Fetching from API for PID: {$pid}\n", 3, CJSYNC . '/wccj_error.log');
    
    $tokenFile = CJSYNC . 'cj_token.json';

    if (!file_exists($tokenFile)) {
        error_log("[CJDS] Token file not found\n", 3, CJSYNC . '/wccj_error.log');
        return null;
    }

    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (!isset($tokenData['token'])) {
        error_log("[CJDS] Invalid token data\n", 3, CJSYNC . '/wccj_error.log');
        return null;
    }

    $accessToken = $tokenData['token'];
    $api_url = "https://developers.cjdropshipping.com/api2.0/v1/product/query?pid={$pid}&features=enable_video";

    $response = wp_remote_get($api_url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'CJ-Access-Token' => $accessToken,
        ],
        'timeout' => 60,
    ]);

    if (is_wp_error($response)) {
        error_log("[CJDS] API request failed: " . $response->get_error_message() . "\n", 3, CJSYNC . '/wccj_error.log');
        return null;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (!isset($body['data'])) {
        error_log("[CJDS] Invalid API response for PID: {$pid}\n", 3, CJSYNC . '/wccj_error.log');
        return null;
    }

    return $body['data'];
}