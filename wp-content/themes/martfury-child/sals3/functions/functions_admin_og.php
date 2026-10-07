<?php

function add_woocommerce_open_graph_tags() {
  global $product;
  global $post;

  $product_id = get_the_ID();
  $product = wc_get_product( $product_id );
 
    if ( is_product() ) {
        
    $title = get_post_meta($product_id, 'seo_title', true);
    $desc  = get_post_meta($product_id, 'seo_description', true);
    $keys  = get_post_meta($product_id, 'target_keywords', true);
    
        // Get product details
        $title = ($title ? $title : get_the_title());
        $permalink = get_permalink();
        $description = ($desc ? $desc : wp_trim_words( $product->get_description(), 55 ));
        $image_id = $product->get_image_id();
        $image_url = $image_id ? wp_get_attachment_url( $image_id ) : '';
        $price = $product->get_price();
        $currency = get_woocommerce_currency_symbol();
        $availability = $product->is_in_stock() ? 'instock' : 'outofstock';

        // Clean up description for OG tag
        $description = esc_attr( strip_tags( $description ) );

    
        
        // Required Open Graph Tags
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />'. "\n"; 
        echo '<meta property="og:type" content="product" />'. "\n"; 
        echo '<meta property="og:url" content="' . esc_url( $permalink ) . '" />'. "\n"; 
        echo '<meta property="og:description" content="' . $description . '" />'. "\n"; 
        
        if ( $image_url ) {
            // Main image tag
            echo '<meta property="og:image" content="' . esc_url( $image_url ) . '" />'. "\n"; 
            
            // Image dimensions (Highly Recommended)
            $image_data = wp_get_attachment_image_src( $image_id, 'full' );
            if ( $image_data ) {
                echo '<meta property="og:image:width" content="' . $image_data[1] . '" />'. "\n"; 
                echo '<meta property="og:image:height" content="' . $image_data[2] . '" />'. "\n"; 
            }
        }
        
        // Recommended Product-Specific Open Graph Tags
        echo '<meta property="product:price:amount" content="' . esc_attr( $product->get_price() ) . '" />'. "\n"; 
        echo '<meta property="product:price:currency" content="' . esc_attr( $currency ) . '" />'. "\n"; 
        echo '<meta property="og:availability" content="' . esc_attr( $availability ) . '" />'. "\n"; 
        
        // General Recommended Tags
        // echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />'. "\n"; 
        // echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '" />'. "\n"; 
        // echo '<meta property="og:logo" content="https://anythingsupplies.com/wp-content/uploads/2026/02/cctv.webp">' . "\n"; 
   
    }else if (is_page() || is_single() || is_singular() || is_home() || is_front_page() || is_archive() || is_category() || is_tag() || is_author() || is_search() ) {
 
        
        //print_r($post);
        if ( isset( $post ) && $post instanceof WP_Post ) {
            $post_id = $post->ID;
        } else {
            return; // Exit if not a valid post
        }
        // Fetch values from WordPress Custom Fields
        $seo_title = get_post_meta($post_id, 'custom_seo_title', true);
        $seo_desc  = get_post_meta($post_id, 'custom_seo_description', true);
        $seo_keys  = get_post_meta($post_id, 'custom_seo_keywords', true);

        // Use Post Thumbnail for OpenGraph, fallback to a default if empty
        $og_image = get_the_post_thumbnail_url($post_id, 'full');
        if (!$og_image) {
            $og_image = 'https://anythingsupplies.com/wp-content/uploads/2026/02/cctv.webp';
        }

        // Fallback to Post Title/Excerpt if custom fields are empty
        $display_title = !empty($seo_title) ? $seo_title : get_the_title();
        $display_desc  = !empty($seo_desc) ? $seo_desc : wp_trim_words($post->post_content, 25);

        // Output Tags
        echo "\n\n";
        echo '<meta name="description" content="' . esc_attr($display_desc) . '">' . "\n";
        if (!empty($seo_keys)) {
            echo '<meta name="keywords" content="' . esc_attr($seo_keys) . '">' . "\n";
        }
        
        // OpenGraph
        // echo '<meta property="og:title" content="' . esc_attr($display_title) . '">' . "\n";
        // echo '<meta property="og:description" content="' . esc_attr($display_desc) . '">' . "\n";
        // echo '<meta property="og:type" content="article">' . "\n";
        // echo '<meta property="og:url" content="' . get_permalink() . '">' . "\n";
        // echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        // echo '<meta property="og:logo" content="https://anythingsupplies.com/wp-content/uploads/2026/02/cctv.webp">' . "\n"; 
        // echo "\n";
    
    }
   
}

add_action( 'wp_head', 'add_woocommerce_open_graph_tags', 1 );

// Define the Meta Key once
$sals3_meta_key = 'sals3_free_shipping';

/**
 * 1. Register the custom meta box for the 'product' post type.
 */
function sals3_add_custom_metabox() {
    add_meta_box(
        'sals3_shipping_metabox',           // Unique ID
        __( 'SALS3 Shipping Options', 'your-text-domain' ), // Box Title
        'sals3_render_metabox_content',     // Callback function to render content
        'product',                          // Post type (only applies to products)
        'side',                             // Context (where to show: 'normal', 'side', or 'advanced')
        'default'                           // Priority
    );
}
add_action( 'add_meta_boxes', 'sals3_add_custom_metabox' );

/**
 * 2. Render the content of the custom meta box.
 * * @param WP_Post $post The current post object.
 */
function sals3_render_metabox_content( $post ) {
    global $sals3_meta_key;
    
    // Add a nonce field for security checking when saving
    wp_nonce_field( 'sals3_save_metabox_data', 'sals3_metabox_nonce' );
    
    // Get the current value
    $value = get_post_meta( $post->ID, $sals3_meta_key, true );
    
    // Default to 'no' if no value is set
    $selected = empty( $value ) ? 'no' : $value;
    
    ?>
    <p>
        <label for="<?php echo esc_attr( $sals3_meta_key ); ?>">
            <strong><?php esc_html_e( 'Enable SALS3 Free Shipping:', 'your-text-domain' ); ?></strong>
        </label>
    </p>
    <select name="<?php echo esc_attr( $sals3_meta_key ); ?>" id="<?php echo esc_attr( $sals3_meta_key ); ?>">
        <option value="no" <?php selected( $selected, 'no' ); ?>>
            <?php esc_html_e( 'No', 'your-text-domain' ); ?>
        </option>
        <option value="yes" <?php selected( $selected, 'yes' ); ?>>
            <?php esc_html_e( 'Yes', 'your-text-domain' ); ?>
        </option>
    </select>
    <p class="description">
        <?php esc_html_e( 'Setting to "Yes" enables special free shipping for this product.', 'your-text-domain' ); ?>
    </p>
    <?php
}

/**
 * 3. Save the custom field data ONLY if the post type is 'product'.
 *
 * @param int $post_id The current post ID.
 */
function sals3_save_free_shipping_select_field( $post_id ) {
    global $sals3_meta_key;

    // CRITICAL SECURITY CHECKS:
    // Check if our nonce is set.
    if ( ! isset( $_POST['sals3_metabox_nonce'] ) ) {
        return $post_id;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['sals3_metabox_nonce'], 'sals3_save_metabox_data' ) ) {
        return $post_id;
    }

    // Check if the user has permissions to save the post.
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }
    
    // CRITICAL POST TYPE CHECK: Ensure we are only saving data for the 'product' post type
    if ( get_post_type( $post_id ) !== 'product' ) {
        return $post_id;
    }
    
    // Check if the value was submitted
    if ( isset( $_POST[ $sals3_meta_key ] ) ) {
        
        $selected_value = sanitize_text_field( $_POST[ $sals3_meta_key ] );
        
        // Ensure the value is one of the allowed options ('yes' or 'no')
        if ( in_array( $selected_value, array( 'yes', 'no' ) ) ) {
            
            // Update the post meta
            update_post_meta( $post_id, $sals3_meta_key, $selected_value );
            
        } else {
            // Default to 'no' if an invalid value is received
            update_post_meta( $post_id, $sals3_meta_key, 'no' );
        }
    } else {
        // If the field wasn't in the POST data, ensure a default exists
        update_post_meta( $post_id, $sals3_meta_key, 'no' );
    }
}
add_action( 'save_post', 'sals3_save_free_shipping_select_field' );


/**
 * 1. Add the custom maximum quantity input field to the Product Data Inventory tab.
 */
function custom_add_max_quantity_field() {
    global $post;
    
    // Get the current saved value for this product
    $max_limit = get_post_meta( $post->ID, '_custom_max_quantity', true );

    woocommerce_wp_text_input( array(
        'id'            => '_custom_max_quantity',
        'value'         => $max_limit,
        'label'         => __( 'Max Purchase Quantity', 'woocommerce' ),
        'placeholder'   => __( 'Leave blank for no limit', 'woocommerce' ),
        'description'   => __( 'Enter the maximum number of this product a customer can purchase in one order.', 'woocommerce' ),
        'data_type'     => 'decimal', // Ensures only numbers are accepted
        'wrapper_class' => 'form-field',
    ) );
}
add_action( 'woocommerce_product_options_inventory_product_data', 'custom_add_max_quantity_field' );

/**
 * 2. Save the data from the custom maximum quantity input field.
 *
 * @param int $post_id The ID of the post (product) being saved.
 */
function custom_save_max_quantity_field( $post_id ) {
    $max_quantity = sanitize_text_field( $_POST['_custom_max_quantity'] ?? '' );
    
    // Update the post meta field
    if ( ! empty( $max_quantity ) ) {
        update_post_meta( $post_id, '_custom_max_quantity', absint( $max_quantity ) );
    } else {
        delete_post_meta( $post_id, '_custom_max_quantity' );
    }
}
add_action( 'woocommerce_process_product_meta', 'custom_save_max_quantity_field' );

/**
 * 3a. Apply the maximum quantity limit to the quantity input field on the product page.
 *
 * @param array $args The quantity input arguments.
 * @param WC_Product $product The product object.
 * @return array The modified quantity input arguments.
 */
function custom_apply_max_quantity_to_input( $args, $product ) {
    $max_limit = get_post_meta( $product->get_id(), '_custom_max_quantity', true );

    if ( ! empty( $max_limit ) ) {
        // Only set max_value if it's not already lower (e.g., set by stock)
        if ( ! isset( $args['max_value'] ) || $args['max_value'] > $max_limit ) {
            $args['max_value'] = absint( $max_limit );
        }
    }
    return $args;
}
add_filter( 'woocommerce_quantity_input_args', 'custom_apply_max_quantity_to_input', 10, 2 );

/**
 * 3b. Validate the maximum quantity when adding to cart (Server-side check).
 * This prevents users from bypassing the limit via direct URL manipulation.
 *
 * @param bool $passed Whether the validation has passed.
 * @param int $product_id The product ID being added.
 * @param int $quantity The quantity being added.
 * @return bool
 */
function custom_validate_max_quantity_add_to_cart( $passed, $product_id, $quantity ) {
    $max_limit = get_post_meta( $product_id, '_custom_max_quantity', true );
    
    if ( ! empty( $max_limit ) && $quantity > absint( $max_limit ) ) {
        // Add a visible error notice
        wc_add_notice( 
            sprintf( 
                'You can only purchase a maximum of %d units of this item.', 
                absint( $max_limit )
            ), 
            'error' 
        );
        $passed = false; // Block the add-to-cart action
    }

    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'custom_validate_max_quantity_add_to_cart', 10, 3 );


/**
 * Register the SEO Meta Box
 */
function custom_seo_meta_box() {
    $screens = ['post', 'page']; // Add custom post types here if needed
    foreach ($screens as $screen) {
        add_meta_box(
            'seo_metadata_box',           // Unique ID
            'SEO & OpenGraph Settings',    // Box title
            'display_seo_meta_box',        // Content callback
            $screen,                      // Post type
            'normal',                     // Context (normal, side, advanced)
            'high'                        // Priority
        );
    }
}
add_action('add_meta_boxes', 'custom_seo_meta_box');

/**
 * Display the Meta Box Fields
 */
function display_seo_meta_box($post) {
    // Add a nonce field for security
    wp_nonce_field('save_seo_meta_box_data', 'seo_meta_box_nonce');

    // Retrieve existing values from the database
    $title = get_post_meta($post->ID, 'custom_seo_title', true);
    $desc  = get_post_meta($post->ID, 'custom_seo_description', true);
    $keys  = get_post_meta($post->ID, 'custom_seo_keywords', true);

    echo '<div style="padding: 10px 0;">';
    
    echo '<p><label for="custom_seo_title"><strong>SEO Title</strong> (Recommended: < 60 chars)</label><br />';
    echo '<input type="text" id="custom_seo_title" name="custom_seo_title" value="' . esc_attr($title) . '" style="width:100%; height:40px;" /></p>';

    echo '<p><label for="custom_seo_description"><strong>Meta Description</strong> (Recommended: 150-160 chars)</label><br />';
    echo '<textarea id="custom_seo_description" name="custom_seo_description" style="width:100%; height:80px;">' . esc_textarea($desc) . '</textarea></p>';

    echo '<p><label for="custom_seo_keywords"><strong>SEO Keywords</strong> (Comma separated)</label><br />';
    echo '<input type="text" id="custom_seo_keywords" name="custom_seo_keywords" value="' . esc_attr($keys) . '" style="width:100%;" /></p>';

    echo '</div>';
}

/**
 * Save the Meta Box Data
 */
function save_seo_meta_box_data($post_id) {
    // Security checks
    if (!isset($_POST['seo_meta_box_nonce']) || !wp_verify_nonce($_POST['seo_meta_box_nonce'], 'save_seo_meta_box_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Sanitize and save the inputs
    if (isset($_POST['custom_seo_title'])) {
        update_post_meta($post_id, 'custom_seo_title', sanitize_text_field($_POST['custom_seo_title']));
    }
    if (isset($_POST['custom_seo_description'])) {
        update_post_meta($post_id, 'custom_seo_description', sanitize_textarea_field($_POST['custom_seo_description']));
    }
    if (isset($_POST['custom_seo_keywords'])) {
        update_post_meta($post_id, 'custom_seo_keywords', sanitize_text_field($_POST['custom_seo_keywords']));
    }
}
add_action('save_post', 'save_seo_meta_box_data');

add_filter('pre_get_document_title', 'override_seo_title', 10);
function override_seo_title($title) {
    if (is_singular()) {
        $custom_title = get_post_meta(get_the_ID(), 'custom_seo_title', true);
        if (!empty($custom_title)) {
            return $custom_title;
        }
    }
    return $title;
}

function add_custom_seo_metadata() {
    // Only run on single posts or pages
    if (is_singular()) {
        global $post;

        // Fetch values from WordPress Custom Fields
        $seo_title = get_post_meta($post->ID, 'custom_seo_title', true);
        $seo_desc  = get_post_meta($post->ID, 'custom_seo_description', true);
        $seo_keys  = get_post_meta($post->ID, 'custom_seo_keywords', true);
        
        // Use Post Thumbnail for OpenGraph, fallback to a default if empty
        $og_image = get_the_post_thumbnail_url($post->ID, 'full');
        if (!$og_image) {
            $og_image = 'https://anythingsupplies.com/wp-content/uploads/2026/02/cctv.webp';
        }

        // Fallback to Post Title/Excerpt if custom fields are empty
        $display_title = !empty($seo_title) ? $seo_title : get_the_title();
        $display_desc  = !empty($seo_desc) ? $seo_desc : wp_trim_words($post->post_content, 25);

        // Output Tags
        echo "\n\n";
        echo '<meta name="description" content="' . esc_attr($display_desc) . '">' . "\n";
        if (!empty($seo_keys)) {
            echo '<meta name="keywords" content="' . esc_attr($seo_keys) . '">' . "\n";
        }
        
        // OpenGraph
        // echo '<meta property="og:title" content="' . esc_attr($display_title) . '">' . "\n";
        // echo '<meta property="og:description" content="' . esc_attr($display_desc) . '">' . "\n";
        // echo '<meta property="og:type" content="article">' . "\n";
        // echo '<meta property="og:url" content="' . get_permalink() . '">' . "\n";
        // echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        // echo '<meta property="og:logo" content="https://anythingsupplies.com/wp-content/uploads/2026/02/cctv.webp">' . "\n"; 
        // echo "\n";
    }
}
//add_action('wp_head', 'add_custom_seo_metadata', 1);