<?php
/**
 * Consolidated Auto-Generated Schema for Sals3.com
 * Includes Organization, WebSite, Article, WebPage, and WooCommerce Products.
 */
function sals3_auto_generate_schema() {
    $schema = array();
    $product_schema = array();

    // 1. SITEWIDE ORGANIZATION DATA
    $organization = array(
        "@context" => "https://schema.org",
        "@type"    => "Organization",
        "name"     => "SALS3",
        "url"      => home_url(),
        "logo"     => "https://sals3.com/wp-content/uploads/2025/11/sals3-logo-1.png", // Update with your actual logo path
        "sameAs"   => array(
            "https://facebook.com/sals3.global",
            "https://instagram.com/sals3_global/",
            "https://www.tiktok.com/@sals3.global"
        )
    );

    // 2. HOMEPAGE SPECIFIC
    if ( is_front_page() ) {
        $schema[] = $organization;
        $schema[] = array(
            "@context" => "https://schema.org",
            "@type"    => "WebSite",
            "url"      => home_url(),
            "potentialAction" => array(
                "@type"       => "SearchAction",
                "target"      => home_url( '/?s={search_term_string}' ),
                "query-input" => "required name=search_term_string"
            )
        );
    }

    // 3. WOOCOMMERCE PRODUCT PAGES
    elseif ( function_exists( 'is_product' ) && is_product() ) {
        global $product;
        if ( is_object( $product ) ) {
            $schema[] = array(
                "@context" => "https://schema.org/",
                "@type"    => "Product",
                "name"     => $product->get_name(),
                "image"    => array( wp_get_attachment_image_url( $product->get_image_id(), 'full' ) ),
                "description" => wp_strip_all_tags( $product->get_short_description() ? $product->get_short_description() : get_the_excerpt() ),
                "sku"      => $product->get_sku(),
                "brand"    => array(
                    "@type" => "Brand",
                    "name"  => "SALS3"
                ),
                "offers"   => array(
                    "@type"         => "Offer",
                    "url"           => get_permalink( $product->get_id() ),
                    "priceCurrency" => get_woocommerce_currency(),
                    "price"         => $product->get_price(),
                    "availability"  => $product->is_in_stock() ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
                    "itemCondition" => "https://schema.org/NewCondition",
                    "seller"        => $organization
                )
            );

            // ADD AGGREGATE RATING (If reviews exist)
            $rating_count = $product->get_review_count();
            if ( $rating_count > 0 ) {
                $product_schema["aggregateRating"] = array(
                    "@type"       => "AggregateRating",
                    "ratingValue" => $product->get_average_rating(),
                    "reviewCount" => $rating_count,
                    "bestRating"  => "5",
                    "worstRating" => "0"
                );
               
                // ADD INDIVIDUAL REVIEWS (List the 5 most recent)
                $comments = get_comments( array( 'post_id' => $product->ID, 'status' => 'approve', 'type'    => 'review') );
               // print_r($comments);
                $reviews_list = array();
                $count = 0;

                foreach ( $comments as $comment ) {
                    if ( $count >= 5 ) break; // Limit to 5 for speed/cleanliness
                    $rating = get_comment_meta( $comment->comment_ID, 'rating', true );
                    
                    $reviews_list[] = array(
                        "@type" => "Review",
                        "author" => array(
                            "@type" => "Person",
                            "name"  => $comment->comment_author
                        ),
                        "datePublished" => date( 'c', strtotime( $comment->comment_date ) ),
                        "reviewBody"    => wp_strip_all_tags( $comment->comment_content ),
                        "reviewRating"  => array(
                            "@type"       => "Rating",
                            "ratingValue" => $rating ? $rating : "5",
                            "bestRating"  => "5"
                        )
                    );
                    $count++;
                }
                $product_schema["review"] = $reviews_list;
            }

            $schema[] = $product_schema;

        }
    }

    // 4. SINGLE BLOG POSTS
    elseif ( is_single() ) {
        $schema[] = array(
            "@context" => "https://schema.org",
            "@type"    => "Article",
            "headline" => get_the_title(),
            "image"    => get_the_post_thumbnail_url(),
            "author"   => array(
                "@type" => "Person",
                "name"  => get_the_author()
            ),
            "publisher" => $organization,
            "datePublished" => get_the_date('c'),
            "dateModified"  => get_the_modified_date('c')
        );
    }

    // 5. STANDARD PAGES
    elseif ( is_page() ) {
        $schema[] = array(
            "@context" => "https://schema.org",
            "@type"    => "WebPage",
            "name"     => get_the_title(),
            "description" => get_the_excerpt(),
            "publisher"   => $organization
        );
    }

    // Output the script
    if ( ! empty( $schema ) ) {
        echo "\n\n";
        echo '<script type="application/ld+json">' . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>';
        echo "\n";
    }
}
add_action( 'wp_head', 'sals3_auto_generate_schema' );

/**
 * Prevent duplicate Product schema from WooCommerce core
 */
add_filter( 'woocommerce_structured_data_product', '__return_empty_array' );


/**
 * Send a custom HTML order email to rodel@sals3.com
 */
function send_custom_order_email() {
    $to = 'rodel@sals3.com, rodelmojicaednalan@gmail.com, admin@sals3.com';
    $subject = 'Thank you for your order!';
    
    // 1. Define the HTML Body
    $message = '
    <html>
    <head>
        <title>Order Confirmation</title>
    </head>
    <body>
        <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <h2 style="color: #2c3e50;">Thank you for your order!</h2>
            <p>Hi Rodel, we have received your order and it is now being processed.</p>
            
            <hr style="border: 0; border-top: 1px solid #eee;" />
            
            <h3>Order Details:</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8f8f8;">
                        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Product</th>
                        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Quantity</th>
                        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">Wireless Headphones</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">1</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">$120.00</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">USB-C Charging Cable</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">2</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">$30.00</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="padding: 10px; border: 1px solid #ddd; text-align: right;"><strong>Total:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>$150.00</strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <p style="margin-top: 20px;">If you have any questions, please reply to this email.</p>
        </div>
    </body>
    </html>';

    // 2. Set Headers for HTML
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // 3. Send the Email
    wp_mail($to, $subject, $message, $headers);
}

//send_custom_order_email();


//add_action( 'phpmailer_init', 'setup_ms365_smtp' );
function setup_ms365_smtp( $phpmailer ) {
    $phpmailer->isSMTP();     
    $phpmailer->Host       = 'smtp.office365.com';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 587;
    $phpmailer->SMTPSecure = 'tls';

    // Authentication
    $phpmailer->Username   = 'rodel@sals3.com'; 
    $phpmailer->Password   = 'QazWsx04-21';

    // Sets the "From" address and name
    $phpmailer->From       = 'rodel@sals3.com';
    $phpmailer->FromName   = 'Sals3 Website';
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_meta_box', __FILE__, true );
	}
} );
/** 
 * Add a custom Meta Box to the Order Edit page
 */
add_action( 'add_meta_boxes_shop_order', 'as_add_custom_order_notes_meta_box' );
function as_add_custom_order_notes_meta_box() {
    add_meta_box(
        'as_custom_order_admin_notes',
        'Order Notes (Internal)',
        'as_custom_notes_field_html',
        'shop_order',
        'normal', 
        'default'
    );
}

/**
 * The HTML for the textarea field
 */
function as_custom_notes_field_html( $post ) {
    // Works for both HPOS and Traditional post meta
    $order = wc_get_order( $post->ID );
    $value = $order->get_meta( 'as_admin_notes' );

    echo '<textarea name="as_admin_notes" style="width:100%; height:100px; border-radius:4px;" placeholder="Add private notes here...">' . esc_textarea( $value ) . '</textarea>';
    echo '<p class="description">Notes saved here are internal and not shared with customers.</p>';
}
 
/**
 * Save the custom field data when order is updated
 */
add_action( 'woocommerce_process_shop_order_meta', 'as_save_custom_order_notes' );
function as_save_custom_order_notes( $order_id ) {
    $order = wc_get_order( $order_id );
    
    if ( isset( $_POST['as_admin_notes'] ) ) {
        $order->update_meta_data( 'as_admin_notes', sanitize_textarea_field( $_POST['as_admin_notes'] ) );
        $order->save();
    }
}
