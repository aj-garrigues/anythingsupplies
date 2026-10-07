<?php
add_action('post_submitbox_misc_actions', function() {
    global $post;
    if ($post->post_type !== 'product') return;

    $sku = get_post_meta($post->ID, '_sku', true);
    if (empty($sku)) return;

    $cjds_sync_product = admin_url('admin-post.php?action=cjds_sync_product&sku=' . urlencode($sku) . '&product_id=' . $post->ID);
    $cj_sync_product = admin_url('admin-post.php?action=cj_sync_product&sku=' . urlencode($sku) . '&product_id=' . $post->ID);
    $shopify_sync_product = admin_url('admin-post.php?action=shopify_sync_product&sku=' . urlencode($sku) . '&product_id=' . $post->ID);
    
    echo '<div class="misc-pub-section">
        <div>
            <a href="' . esc_url($cj_sync_product) . '" class="button button-primary" style="margin-top:6px; width:80%"> Update from CJDS </a>
        </div>
    </div>';
});

add_action('admin_post_cj_sync_product', function() {

    if (!current_user_can('edit_products')) {
        wp_die('Unauthorized');
    }

    $sku = sanitize_text_field($_GET['sku'] ?? '');
    $product_id = intval($_GET['product_id'] ?? 0);

    if (empty($sku) || !$product_id) {
        wp_die('Missing SKU or product ID.'); 
    }

    $product = wc_get_product($product_id);

    if (!$product) {
        wp_die('Product not found.');
    }

	$result_handle = cjds_sync_product_handle($product_id, $sku);
	
	if (isset($result_handle['redirect']) && $result_handle['redirect'] !==""){
		wp_safe_redirect(admin_url("post.php?post={$product_id}&action=edit&status=".$result_handle['stat']['status']."&des=".$result_handle['stat']['des']));
	}	
	
	
});

add_action('wp_ajax_cjds_sync_product_ajax', 'cjds_sync_product_ajax_handler');

function cjds_sync_product_ajax_handler() {
    // Security check
    if (!check_ajax_referer('product_featured_nonce', 'security', false)) {
        wp_send_json_error('Security check failed.');
        wp_die();
    }

    // Permission check
    if (!current_user_can('edit_products')) {
        wp_send_json_error('Unauthorized access.');
        wp_die();
    }

    // Input validation
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $current_status = isset($_POST['current_status']) ? sanitize_text_field($_POST['current_status']) : 'no';
    $force_sync = isset($_POST['force_sync']) ? filter_var($_POST['force_sync'], FILTER_VALIDATE_BOOLEAN) : false;

    if ($product_id === 0) {
        wp_send_json_error('Invalid Product ID.');
        wp_die();
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error('Product not found.');
        wp_die();
    }

    $sku = $product->get_sku();
    if (empty($sku)) {
        update_post_meta($product_id, '_CJ_link', 'failed');
        wp_send_json_error('Product has no SKU.');
        wp_die();
    }

    // Sync logic
    $new_status = ($current_status === 'yes') ? false : true;
    try {
        $result = function_exists('cjds_sync_product_handle_forced')
            ? cjds_sync_product_handle_forced($product_id, $sku, $new_status, $current_status, $force_sync)
            : cjds_sync_product_handle($product_id, $sku, $new_status, $current_status);

        // Always update last processed date
        update_post_meta($product_id, '_CJ_last_processed_date', current_time('mysql'));

        if (isset($result['status']) && $result['status'] === 'success') {
            update_post_meta($product_id, '_CJ_link', 'success');
            wp_send_json_success([
                'status' => 'success',
                'message' => $result['des'] ?? 'Product synced successfully',
                'new_status' => 'yes',
                'last_processed' => current_time('m/d/Y H:i:s'),
                'product_id' => $product_id
            ]);
        } elseif (isset($result['status'])) {
            update_post_meta($product_id, '_CJ_link', 'failed');
            wp_send_json_success([
                'status' => 'failed',
                'message' => $result['des'] ?? 'Sync failed',
                'new_status' => 'no',
                'last_processed' => current_time('m/d/Y H:i:s'),
                'product_id' => $product_id
            ]);
        } else {
            update_post_meta($product_id, '_CJ_link', 'failed');
            wp_send_json_error('Unexpected sync result format.');
        }
    } catch (Exception $e) {
        update_post_meta($product_id, '_CJ_link', 'failed');
        error_log("[CJDS AJAX Sync] Error syncing product {$product_id}: " . $e->getMessage() . "\n", 3, CJSYNC . '/wccj_error.log');
        wp_send_json_error('An error occurred during sync: ' . $e->getMessage());
    }

    wp_die();
}

/**
 * Optional: Add a column to show sync status more clearly
 */
function cjds_add_sync_status_css() {
    global $typenow;
    if ('product' === $typenow) {
        echo '<style>
            .toggle-synccj-btn {
                display: inline-block;
                text-decoration: none;
            }
            .toggle-synccj-btn:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }
            .column-cjds_sync > div {
                margin: 3px 0;
                font-size: 11px;
            }
        </style>';
    }
}
add_action('admin_head', 'cjds_add_sync_status_css');

add_action('post_submitbox_misc_actions', function() {
    global $post;
    if ($post->post_type !== 'product') return;

    $cjds_pid = get_post_meta($post->ID, 'cjds_pid', true);
    if (empty($cjds_pid)) return;

    $load_video_url = admin_url('admin-post.php?action=cjds_load_video&product_id=' . $post->ID);

    echo '<div class="misc-pub-section">
        <div>
            <a href="' . esc_url($load_video_url) . '" class="button button-secondary" style="margin-top: 6px; width: 80%">
                Load Video URL
            </a>
        </div>        
    </div>';
});

add_action('admin_post_cjds_load_video', function() {
    if (!current_user_can('edit_products')) {
        wp_die('Unauthorized');
    }

    $product_id = intval($_GET['product_id'] ?? 0 );

    if (!$product_id) {
        wp_die('Missing product ID.');
    }

    $pid = get_post_meta($product_id, 'cjds_pid', true);

    if (empty($pid)) {
        wp_redirect(admin_url("post.php?post={$product_id}&action=edit&status=failed&des=No+CJ+PID+found"));
        exit;
    }

    $json_file = ABSPATH . "fetchcj/products/$pid.json";

    if (!file_exists($json_file)) {
        wp_redirect(admin_url("post.php?post={$product_id}&action=edit&status=failed&des=JSON+file+not+found"));
        exit;
    }

    $product_data = json_decode(file_get_contents($json_file), true);

    if (empty($product_data)) {
        wp_redirect(admin_url("post.php?post={$product_id}&action=edit&status=failed&des=Invalid+JSON_data"));
        exit;
    }

    $video_urls = [];

    if (!empty($product_data['productVideo']) && is_array($product_data['productVideo'])) {
        $video_urls = $product_data['productVideo'];
    } elseif (isset($product_data['details']['productVideo']) && is_array($product_data['details']['productVideo'])) {
        $video_urls = $product_data['details']['productVideo'];
    }

    if (empty($video_urls)) {
        wp_redirect(admin_url("post.php?post{$product_id}&action=edit&status=failed&des=No+videos+found"));
        exit;
    }

    update_post_meta($product_id, 'wc_cjds_product_video', implode(', ', $video_urls));
    update_post_meta($product_id, 'video_url', sanitize_url($video_urls[0]));

    wp_redirect(admin_url("post.php?post={$product_id}&action=edit&status=success&des=Loaded+" . count($video_urls). "+video(s)"));
    exit;
});