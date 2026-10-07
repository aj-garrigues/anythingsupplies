<?php
add_action('post_submitbox_misc_actions', function() {
    global $post;
    if ($post->post_type !== 'product') return;

    $sku = get_post_meta($post->ID, '_sku', true);

    if (empty($sku)) return;
    $pid = get_post_meta($post->ID, 'cjds_pid', true);

    $cjds_sync_product = admin_url('admin-post.php?action=cjds_sync_product&sku=' . urlencode($sku) . '&product_id=' . $post->ID);
    $cj_sync_product = admin_url('admin-post.php?action=cj_sync_product&sku=' . urlencode($sku) . '&product_id=' . $post->ID);
    $shopify_sync_product = admin_url('admin-post.php?action=shopify_sync_product&sku=' . urlencode($sku) . '&product_id=' . $post->ID);
    $cj_sync_product_review = admin_url('admin-post.php?action=cj_sync_product_review&sku=' . urlencode($sku) . '&product_id=' . $post->ID.'&pid=' . urlencode($pid));
    
    echo '<div class="misc-pub-section">
        <div>
            <a href="' . esc_url($cj_sync_product_review) . '" class="button button-primary" style="margin-top:6px; width:80%"> Update Review from CJDS </a>
        </div>
    </div>';
});

add_action('admin_post_cj_sync_product_review', function() {

    if (!current_user_can('edit_products')) {
        wp_die('Unauthorized');
    }

    $sku = sanitize_text_field($_GET['sku'] ?? '');
    $product_id = intval($_GET['product_id'] ?? 0);
    $pid = $_GET['pid'];

    if (empty($pid)) {
        wp_die('Missing PID or product ID.'); 
    }

    $data = get_cg_product_reviews($pid);
   

    $cj_reviews = $data['list'];
    $reviews_inserted_count = 0;

    foreach ( $cj_reviews as $review_data ) {
        
        // Skip if a required field is missing
        if ( empty( $review_data['comment'] ) || empty( $review_data['score'] ) ) {
            continue;
        }
        
        $author_name = sanitize_text_field( $review_data['commentUser'] ?? 'Sals3 Customer' );
        $comment_content = sanitize_textarea_field( $review_data['comment'] );
        
        if ( cj_review_exists( $product_id, $comment_content, $author_name ) ) {
            continue;
        }
        
        $comment_data = array(
            'comment_post_ID'      => $product_id, 
            'comment_author'       => $author_name,
            'comment_author_email' => 'product-review@sals3.com',
            'comment_content'      => $comment_content,
            'comment_type'         => 'review', 
            'comment_approved'     => 1,
            'comment_date'         => sanitize_text_field( $review_data['commentDate'] ) ?? current_time( 'mysql' ),
        );
        
        // Insert the comment
        $review_id = wp_insert_comment( $comment_data );

        if ( $review_id ) {
            update_comment_meta( $review_id, 'rating', intval( $review_data['score'] ) );
            $reviews_inserted_count++;
        }
    }

    if ( $reviews_inserted_count > 0 ) {
        wc_delete_product_transients( $product_id );

        $product = wc_get_product( $product_id );
        if ( $product ) {
            WC_Comments::clear_transients( $product_id );
        }
        
        $result_handle['redirect'] = "yes";
        $result_handle['stat']['status'] = "success";
        $result_handle['stat']['des'] = "Reviews updated successfully.";
    }else{
        $result_handle['redirect'] = "yes";
        $result_handle['stat']['status'] = "failed";
        $result_handle['stat']['des'] = "No new reviews to update.";
    }
    
    error_log( 'CJ Review Import Complete. Total reviews inserted: ' . $reviews_inserted_count );

	if (isset($result_handle['redirect']) && $result_handle['redirect'] !==""){
		wp_safe_redirect(admin_url("post.php?post={$product_id}&action=edit&status=".$result_handle['stat']['status']."&des=".$result_handle['stat']['des']));
	}	
	
});

function cj_review_exists($product_id, $comment_content, $comment_author) {
    global $wpdb;

    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT comment_ID FROM {$wpdb->comments} 
        WHERE comment_post_ID = %d 
        AND comment_content = %s 
        AND comment_author = %s 
        AND comment_type = 'review'",
        $product_id,
        $comment_content,
        $comment_author
    ));
    
    return !empty($existing);
}

/**
 * Conditionally loads scripts for PayPal, Stripe, and other gateways
 * ONLY on the WooCommerce Cart and Checkout pages.
 */
function conditionally_load_payment_gateway_scripts() {

    // 1. Bail early if WooCommerce is not active or if we ARE on the Cart OR Checkout page
    // The conditional check is now: if NOT (is_cart() OR is_checkout())
    if ( ! class_exists( 'WooCommerce' ) || is_cart() || is_checkout() ) {
        // If we are on the Cart or Checkout page, we let the scripts load normally.
        return; 
    }

    // --- 2. Define the script/style handles to be removed on other pages ---
    
    // **PayPal Handles** (likely for WooCommerce PayPal Payments)
    $paypal_handles = array(
        'ppc-loader-script', 
        'wc-payment-method-paypal', 
    );

    // **Stripe Handles** (likely for WooCommerce Stripe Payment Gateway)
    $stripe_handles = array(
        'stripe',
        'wc-stripe',
        'wc-stripe-payment-form',
        'wc-stripe-apple-pay',
        'wc-stripe-google-pay',
        'wc-stripe-elements',
        'wc-stripe-blocks-checkout-style',
        'contact-form-7'
    );

    // *** IMPORTANT: Add Antom Gateway Handles Here ***
    $antom_handles = array(
        'antom-main-script', 
        'antom-style',
        'antom-payment', 
        'antom-payment-gateway',
        'crypto',
        'jsencrypt',    
        'credit-card-type'
        // Replace with the actual handles you found using Query Monitor or plugin files.
    );
    
    // Combine all handles for processing
    $all_handles = array_merge( $paypal_handles, $stripe_handles, $antom_handles );
    
    // --- 3. Deregister and Dequeue for all other pages ---
    // This code runs only if the conditional check at the start returned FALSE (i.e., we are NOT on Cart/Checkout)

    foreach ( $all_handles as $handle ) {
        // Attempt to deregister scripts and styles
        wp_deregister_script( $handle );
        wp_dequeue_script( $handle );
        wp_deregister_style( $handle );
        wp_dequeue_style( $handle );
    }
}
// Use a high priority (9999) to run this function after the gateways have enqueued their scripts.
add_action( 'wp_enqueue_scripts', 'conditionally_load_payment_gateway_scripts', 9999 );