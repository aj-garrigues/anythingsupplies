<?php
/**
 * Limit WooCommerce product titles to 100 characters in listings only
 */
add_filter( 'the_title', 'limit_product_title_in_listings', 10, 2 );

function limit_product_title_in_listings( $title, $id ) {
    // 1. Don't change titles in the WordPress admin dashboard
    if ( is_admin() ) {
        return $title;
    }

    // 2. Check if it's a WooCommerce product
    if ( get_post_type( $id ) === 'product' ) {
        
        // 3. Only apply if we are NOT on a single product page
        // This targets the Shop, Archives, and Related/Cross-sell loops
        if ( ! is_product() ) {
            $limit = 42;

            if ( mb_strlen( $title ) > $limit ) {
                // mb_substr is safer for special characters/languages
                // $title = mb_substr( $title, 0, $limit ) . '...';
            }
        }
    }
    return $title;
}

/**
 * 1. Add MOQ field to the Product Inventory tab
 */
add_action( 'woocommerce_product_options_inventory_product_data', 'add_moq_product_field' );
function add_moq_product_field() {
    woocommerce_wp_text_input( array(
        'id'          => '_min_qty_moq',
        'label'       => __( 'Minimum Order Qty (MOQ)', 'woocommerce' ),
        'placeholder' => '1',
        'desc_tip'    => 'true',
        'description' => __( 'The minimum number of items a customer must buy.', 'woocommerce' ),
        'type'        => 'number',
        'custom_attributes' => array(
            'step' => '1',
            'min'  => '1'
        )
    ) );
}

/**
 * 2. Save the MOQ value when the product is updated
 */
add_action( 'woocommerce_process_product_meta', 'save_moq_product_field' );
function save_moq_product_field( $post_id ) {
    $moq_value = isset( $_POST['_min_qty_moq'] ) ? sanitize_text_field( $_POST['_min_qty_moq'] ) : '';
    update_post_meta( $post_id, '_min_qty_moq', $moq_value );
}

add_filter( 'woocommerce_quantity_input_args', 'enforce_moq_frontend', 10, 2 );
function enforce_moq_frontend( $args, $product ) {
    $moq = get_post_meta( $product->get_id(), '_min_qty_moq', true );
    if ( ! empty( $moq ) && $moq > 1 ) {
        $args['min_value'] = $moq;
        
        if ( is_product() ) {
            $args['input_value'] = $moq;
        }
    }
    return $args;
}

add_filter( 'woocommerce_add_to_cart_validation', 'validate_moq_on_add_to_cart', 10, 3 );
function validate_moq_on_add_to_cart( $passed, $product_id, $quantity ) {
    $moq = get_post_meta( $product_id, '_min_qty_moq', true );
    
    if ( ! empty( $moq ) && $quantity < $moq ) {
        wc_add_notice( sprintf( __( 'You must order a minimum of %d for this product.', 'woocommerce' ), $moq ), 'error' );
        return false;
    }
    return $passed;
}

add_action( 'woocommerce_product_options_pricing', 'add_six_tiered_pricing_fields' );
function add_six_tiered_pricing_fields() {
    echo '<div class="options_group">';
    echo '<h4>' . __( 'Bulk Savings Tiers (Up to 6)', 'woocommerce' ) . '</h4>';
    
    for ( $i = 1; $i <= 6; $i++ ) {
        echo '<div style="background: #f9f9f9; padding: 10px; border-bottom: 1px solid #eee;">';
        woocommerce_wp_text_input( array(
            'id'    => "_tier_{$i}_qty",
            'label' => "Tier $i Min Qty",
            'type'  => 'number',
        ) );
        woocommerce_wp_text_input( array(
            'id'    => "_tier_{$i}_price",
            'label' => "Tier $i Unit Price ($)",
            'type'  => 'text',
        ) );
        echo '</div>';
    }
    echo '</div>';
}

/**
 * 2. Save all 6 Tiers
 */
add_action( 'woocommerce_process_product_meta', 'save_six_tiered_pricing_fields' );
function save_six_tiered_pricing_fields( $post_id ) {
    for ( $i = 1; $i <= 6; $i++ ) {
        update_post_meta( $post_id, "_tier_{$i}_qty", sanitize_text_field( $_POST["_tier_{$i}_qty"] ) );
        update_post_meta( $post_id, "_tier_{$i}_price", sanitize_text_field( $_POST["_tier_{$i}_price"] ) );
    }
}

add_action( 'woocommerce_before_calculate_totals', 'apply_six_tier_discount', 10, 1 );
function apply_six_tier_discount( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    foreach ( $cart->get_cart() as $cart_item ) {
        $product_id = $cart_item['product_id'];
        $quantity   = $cart_item['quantity'];
        $best_price = null;

        for ( $i = 6; $i >= 1; $i-- ) {
            $t_qty   = get_post_meta( $product_id, "_tier_{$i}_qty", true );
            $t_price = get_post_meta( $product_id, "_tier_{$i}_price", true );

            if ( ! empty( $t_qty ) && $quantity >= $t_qty ) {
                $best_price = $t_price;
                break; 
            }
        }

        if ( $best_price !== null ) {
            $cart_item['data']->set_price( $best_price );
        }
    }
}

add_action( 'woocommerce_before_add_to_cart_form', 'display_six_tier_pricing_table' );
function display_six_tier_pricing_table() {
    global $product;
    $product_id = $product->get_id();
    $tiers = [];

    for ( $i = 1; $i <= 6; $i++ ) {
        $qty   = get_post_meta( $product_id, "_tier_{$i}_qty", true );
        $price = get_post_meta( $product_id, "_tier_{$i}_price", true );
        if ( ! empty( $qty ) ) {
            $tiers[] = ['qty' => $qty, 'price' => $price];
        }
    }

    if ( empty( $tiers ) ) return;

    echo '<div class="bulk-pricing-table" style="margin: 0px 0 20px; background: #fff;">';
    echo '<h4 style="margin-bottom:10px;">' . __( 'Wholesale Tiered Pricing', 'woocommerce' ) . '</h4>';
    echo '<table style="width: 100%; font-size: 0.9em; text-align: left;">';
    echo '<tr style="border-bottom: 1px solid #eee;  font-size:18px"><th>Qty</th><th>Unit Price</th></tr>';

    foreach ( $tiers as $index => $tier ) {
        $next_qty = isset( $tiers[$index + 1] ) ? ( intval( $tiers[$index + 1]['qty'] ) - 1 ) : '';
        $range = $next_qty ? $tier['qty'] . ' - ' . $next_qty : $tier['qty'] . '+';
        
        echo '<tr>';
        echo '<td style="padding: 5px 5; width:30%;  font-size:18px">' . $range . ' units</td>';
        echo '<td style="padding: 5px 5; color: #ff3300; font-weight: bold;  font-size:20px">' . wc_price( $tier['price'] ) . '</td>';
        echo '</tr>';
    }

    echo '</table></div>';
}


/**
 * Replace Add to Cart with Inquire Now for Out of Stock Products
 */
//add_action( 'woocommerce_single_product_summary', 'replace_out_of_stock_button', 1, 0 );

function replace_out_of_stock_button() {
    global $product;

   // if ( ! $product->is_in_stock() ) {
        // Remove Add to Cart
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
        
        // Add Inquire Button
        echo '<button type="button" id="inquire-now-btn" class="button alt">Inquire Now</button>';
        
        // Add the Modal HTML to the footer
        add_action( 'wp_footer', 'render_inquiry_popup_form' );
    //}
}

function render_inquiry_popup_form() {
    ?>
    <div id="inquiry-modal" class="inquiry-modal">
        <div class="inquiry-modal-content">
            <span class="close-inquiry">&times;</span>
            <h3>Product Inquiry</h3>
            <form id="product-inquiry-form">
                <input type="text" name="user_name" placeholder="Name" required>
                <input type="text" name="contact_number" placeholder="Contact Number" required>
                <input type="email" name="user_email" placeholder="Email" required>
                <input type="number" name="qty_request" placeholder="Quantity Request" min="1" required>
                <textarea name="quick_message" placeholder="Quick Message"></textarea>
                
                <div class="checkbox-group">
                    <label><input type="checkbox" name="agree_related" required> Agree to receive product inquiries related to this product</label>
                    <label><input type="checkbox" name="get_quotations">aaa Get quotation from others about this product</label>
                </div>
                
                <button type="submit" class="button">Submit Inquiry</button>
            </form>
        </div>
    </div>
    <?php
}

//add_action( 'wp_footer', 'inquiry_form_js' );
function inquiry_form_js() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            // Inside this function, $ works just like normal
            const $modal = $('#inquiry-modal');
            const $btn = $('#inquire-now-btn');
            const $span = $('.close-inquiry');

            // Open modal
            $btn.on('click', function(e) {
                e.preventDefault(); // Prevents page jump if the button is an <a> tag
                $modal.fadeIn(200); // Using fadeIn for a smoother WP feel
            });

            // Close modal via 'X' button
            $span.on('click', function() {
                $modal.fadeOut(200);
            });

            // Close modal when clicking outside
            $(window).on('click', function(event) {
                if ($(event.target).is($modal)) {
                    $modal.fadeOut(200);
                }
            });
        });
    </script>
    <?php
}

/**
 * 1. Render the Modal with AJAX Script
 */
add_action( 'wp_footer', 'render_inquiry_ajax_system' );
function render_inquiry_ajax_system() {
    // Only load if on a single product page and it's out of stock
    if ( ! is_product() ) return;
    global $product;
   // if ( $product->is_in_stock() ) return;
   $image_id = $product->get_image_id();

   add_image_size( 'compact-thumb', 50, 50, true );

    if ( $image_id ) {
        $product_img =  wp_get_attachment_image( $image_id, 'compact-thumb' );
    } else {
        // Fallback if no image exists
        $product_img = wc_placeholder_img( 'compact-thumb' );
    }

    //get user info
    $current_user = wp_get_current_user();
    
    ?>
    <div id="inquiry-modal" class="inquiry-modal">
        <div class="inquiry-modal-content">
            <span class="close-inquiry">&times;</span>
            <h3 style="margin-top: 0; font-weight: 500;">Product Inquiry</h3>
            
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:15px;">
            <div class=""><?php echo $product_img; ?></div>
            <div class="inquery-product-title" style="font-size:17px;"><?php echo get_the_title(); ?></div>
            </div>

            <form id="product-inquiry-form">
                <?php wp_nonce_field( 'inquiry_nonce_action', 'inquiry_nonce' ); ?>
                <input type="hidden" name="product_name" value="<?php echo esc_attr( get_the_title() ); ?>">
                <input type="hidden" name="product_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
                <?php if ( is_user_logged_in() ): ?>
                <input type="hidden" name="user_id" value="<?php echo esc_attr( $current_user->ID ); ?>">    
                <input type="hidden" name="name" placeholder="Name" value="<?php echo esc_attr( $current_user->display_name ); ?>" required readonly> 
                <input type="hidden" name="contact_number" placeholder="Contact Number" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'billing_phone', true ) ); ?>" required readonly>    
                <input type="hidden" name="user_email" placeholder="Email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required readonly>
                <?php else: ?>
                <input type="text" name="name" placeholder="Name" required>
                <input type="text" name="contact_number" placeholder="Contact Number" required>
                <input type="email" name="user_email" placeholder="Email" required>
                <?php endif; ?> 
                <input type="number" name="qty_request" placeholder="Quantity Request" min="1" required>
                <textarea name="quick_message" placeholder="Quick Message"></textarea>
                
                <div class="checkbox-group">
                    <label><input type="checkbox" name="agree_related" value="Yes" required> Agree to received product inquiries related to this product</label>
                    <label><input type="checkbox" name="get_quotations" value="Yes"> Get quotation from other about this products</label>
                </div>
                 <div id="inquiry-response" style=""></div>
                <button type="submit" id="submit-inquiry" class="button">Send Inquiry</button>
               
            </form>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
    const $modal = $('#inquiry-modal');
    const $btn = $('#inquire-now-btn');
    const $span = $('.close-inquiry');
    const $form = $('#product-inquiry-form');
    const $responseDiv = $('#inquiry-response');
    const $submitBtn = $('#submit-inquiry');

    console.log('Inquiry form script loaded.');

    // Toggle Modal
    $btn.on('click', function() {
        console.log('Inquire Now button clicked.');
		$modal.addClass("modal-open");
        $modal.fadeIn(200); // Using fadeIn for a smoother effect
    });

    $span.on('click', function() {
		$modal.removeClass("modal-open");
        $modal.fadeOut(200);
    });

    // Close on outside click
    $(window).on('click', function(e) {
	
        if ($(e.target).is($modal)){
			 $modal.removeClass("modal-open");
			 $modal.fadeOut(200);
		}
    });

    // AJAX Submission
    $form.on('submit', function(e) {
        e.preventDefault();

        $submitBtn.prop('disabled', true);
        $responseDiv.html("Sending...");

        // Serialize data and add action for WordPress
        const formData = $(this).serialize() + '&action=process_product_inquiry';

        $.ajax({
            url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('AJAX response:', response);
                if (response.success) {
                    $responseDiv.html('<span style="color:green;">' + response.data.message + '</span>');

                    // Reset the form
                    if ($form.length) $form[0].reset();
                    
                    // Clean up UI after a delay
                    setTimeout(function() {
                        $modal.fadeOut(200, function() {
                            $(this).removeClass("modal-open");
                            $responseDiv.empty();
                            // Reset button text
                            $submitBtn.text('Submit Inquiry');
                        });
                    }, 2500);

                } else {
                    // Extract error message correctly
                    
                    $responseDiv.html('<span style="color:red;">' + response.data.message + '</span>');
                }
            },
            error: function() {
                $responseDiv.html('<span style="color:red;">An error occurred. Please try again.</span>');
            },
            complete: function() {
                $submitBtn.prop('disabled', false);
            }
        });
        
    });
});
    </script>
    <?php
}
/**
 * 2. Handle the AJAX Post Request
 */
add_action( 'wp_ajax_process_product_inquiry', 'process_product_inquiry_callback' );
add_action( 'wp_ajax_nopriv_process_product_inquiry', 'process_product_inquiry_callback' );

function process_product_inquiry_callback(){
    // Security check
    global $wpdb;
    if ( ! isset( $_POST['inquiry_nonce'] ) || ! wp_verify_nonce( $_POST['inquiry_nonce'], 'inquiry_nonce_action' ) ) {
        wp_send_json_error( 'Security check failed.' );
    }

    // Sanitize Inputs
    $customer_name    = sanitize_text_field( $_POST['name'] );
    $customer_email   = sanitize_email( $_POST['user_email'] );
    $customer_contact   = sanitize_text_field( $_POST['contact_number'] );
    $qty     = sanitize_text_field( $_POST['qty_request'] );
    $msg     = sanitize_textarea_field( $_POST['quick_message'] );
    $product_name = sanitize_text_field( $_POST['product_name'] );
    $product_id = sanitize_text_field( $_POST['product_id'] );
    $agree1  = isset($_POST['agree_related']) ? 'Yes' : 'No';
    $agree2  = isset($_POST['get_quotations']) ? 'Yes' : 'No';

    // 1. Get or assign the dedicated agent for this user
    $agent_data = as_get_or_assign_agent( get_current_user_id() );

    $to      = get_option( 'admin_email' ).','.$agent_data['email']; // Send to admin and assigned agent
    
    // Assuming these variables are defined from your $_POST and $assigned data

$agent_name = $agent_data['name']; // From our previous userdata fetch

$subject = "🚨 URGENT: High-Volume Inquiry ( $qty units) - " . $product_name;

$databody = array(
    'customer_name'   => $customer_name,
    'customer_email'   => $customer_email,
    'customer_contact' => $customer_contact,
    'qty' => $qty,
    'product_name' => $product_name,
    'product_id' => $product_id,
    'msg' => $msg,
    'agent_name' => $agent_name,
    'agent_email' => $agent_data['email'],
    'agent_id' => $agent_data['id'],
    'show_byline' => true
);

$email_body = "
<html>
<body style='font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;'>
    <div style='max-width: 600px; margin: 20px auto; border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
        
        <div style='background-color: #2c3e50; color: #ffffff; padding: 25px; text-align: center;'>
            <h1 style='margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 1px;'>New Order Inquiry</h1>
            <p style='margin: 5px 0 0; opacity: 0.8;'>Priority: <span style='color: #ff1900; font-weight: bold;'>High</span></p>
        </div>

        <div style='background-color: #fff3cd; color: #856404; padding: 15px; text-align: center; font-weight: bold; border-bottom: 1px solid #ffeeba;'>
            ACTION REQUIRED: Please respond to this inquiry immediately.
        </div>

        <div style='padding: 30px; background-color: #ffffff;'>
            <p>Hello <strong>{$agent_name}</strong>,</p>
            <p>A new high-volume inquiry has been assigned to you. Please review the details below and provide a quotation as soon as possible.</p>

            <div style='background-color: #f8f9fa; border-left: 4px solid #3498db; padding: 20px; margin-bottom: 25px;'>
                <h3 style='margin-top: 0; color: #2c3e50;'>Product Information</h3>
                <p style='margin: 5px 0;'><strong>Product:</strong> {$product_name}</p>
                <p style='margin: 5px 0;'><strong>Product ID:</strong> {$product_id}</p>
                <p style='margin: 5px 0; font-size: 18px; color: #27ae60;'><strong>Quantity Requested:</strong> {$qty} units</p>
            </div>

            <h3 style='color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px;'>Customer Details</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 8px 0; color: #7f8c8d; width: 40%;'>Customer Name:</td>
                    <td style='padding: 8px 0; font-weight: bold;'>{$customer_name}</td>
                </tr>
                <tr>
                    <td style='padding: 8px 0; color: #7f8c8d;'>Email:</td>
                    <td style='padding: 8px 0;'><a href='mailto:{$customer_email}' style='color: #3498db;'>{$customer_email}</a></td>
                </tr>
                
                <tr>
                    <td style='padding: 8px 0; color: #7f8c8d;'>Contact:</td>
                    <td style='padding: 8px 0;'>{$customer_contact}</td>
                </tr>
                <tr>
                    <td style='padding: 8px 0; color: #7f8c8d;'>Preferences:</td>
                    <td style='padding: 8px 0;'>
                        <span style='font-size: 12px; background: #e8f5e9; color: #2e7d32; padding: 2px 8px; border-radius: 10px;'>Agreed to Related</span>
                        <span style='font-size: 12px; background: #e3f2fd; color: #1565c0; padding: 2px 8px; border-radius: 10px;'>Requested Quotations</span>
                    </td>
                </tr>
            </table>

            <div style='margin-top: 25px;'>
                <p style='color: #7f8c8d; margin-bottom: 5px;'>Customer Message:</p>
                <div style='padding: 15px; background: #ffffff; border: 1px solid #eee; font-style: italic; color: #555;'>
                    \"{$msg}\"
                </div>
            </div>

            <div style='text-align: center; margin-top: 30px;'>
                <a href='mailto:{$customer_email}?subject=Re: Inquiry for {$product_name}' 
                   style='background-color: #27ae60; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                   Reply to Customer Now
                </a>
            </div>
        </div>

        <div style='background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #95a5a6;'>
            This is an automated priority alert from the <strong>Anythingsupplies Portal</strong>.<br>
            Agent Assignment ID: {$agent_data['id']}
        </div>
    </div>
</body>
</html>";


    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $customer_name . ' <' . $customer_email . '>'
    );

    if ( wp_mail( $to, $subject, $email_body, $headers ) ) {
        $mailsend = 'Your inquiry has been sent successfully!' ;

        //now send email to the customer as confirmation
        // 1. Prepare Customer-specific variables
        $subject_customer = "We've received your inquiry: " . $product_name;

        // 2. Set Headers for HTML
        $headers_customer = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Anythingsupplies <support@anythingsupplies.com>' 
        );

        // 3. The HTML Message Body
        $message_customer = "
        <html>
        <body style='font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #eee;'>
                
                <div style='background-color: #2eb82e; color: #ffffff; padding: 30px; text-align: center;'>
                    <h2 style='margin: 0;'>Inquiry Confirmed</h2>
                    <p style='margin: 5px 0 0;'>Thank you for choosing Anythingsupplies</p>
                </div>

                <div style='padding: 30px;'>
                    <p>Hi <strong>{$customer_name}</strong>,</p>
                    <p>We've successfully received your inquiry for the following product. One of our agents has been assigned to your request and will contact you shortly with a formal quotation.</p>

                    <table style='width: 100%; margin: 20px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 15px 0;'>
                        <tr>
                            <td style='color: #777; padding: 5px 0;'>Product:</td>
                            <td style='font-weight: bold; text-align: left;'>{$product_name}</td>
                        </tr>
                        <tr>
                            <td style='color: #777; padding: 5px 0;'>Quantity:</td>
                            <td style='font-weight: bold; text-align: left;'>{$qty} units</td>
                        </tr>
                        <tr>
                            <td style='color: #777; padding: 5px 0;'>Inquiry Status:</td>
                            <td style='font-weight: bold; text-align: left; color: #2eb82e;'>Processing</td>
                        </tr>
                    </table>

                    <div style='background: #fff3cd; padding: 15px; border-radius: 5px; font-size: 14px; color: #856404;'>
                        <strong>Next Step:</strong> Your assigned agent will review your requirements and reach out via email within 24-48 business hours.
                    </div>

                    <p style='margin-top: 25px;'>If you have any questions in the meantime, please feel free to reply to this email.</p>
                </div>

                <div style='background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #999;'>
                    &copy; 2026 Anythingsupplies.com | All Rights Reserved.<br>
                    You received this email because you submitted an inquiry on our portal.
                </div>
            </div> 
        </body>
        </html>";

        // 4. Send the mail to the customer
        wp_mail($customer_email, $subject_customer, $message_customer, $headers_customer);

    } else {
        $mailsend = 'Server error. Please try again later.';
    }

    //now add to the inquiry list custom post type
  
    $table_name = $wpdb->prefix . 'inquiries';

    $customer_id = get_current_user_id();
    $inquiry_qty = $qty;
    $agent_id = $agent_data['id']; //default no agent assigned
    $type_id = 3; //product inquiry type
    $parent_inquiry = 0; //default no parent inquiry
    $message = $msg;



// 2. Prepare the data (removing quotes from numbers where appropriate)
$data = [
    'customer_id'    => $customer_id, // Integer
    'product_id'     => $product_id, // Integer
    'inquired_qty'   => $inquiry_qty, // Integer
   // 'inquired_key'   => '', // String key to identify the type of inquiry
    'inquiry_type'   => 'pieces',
    'agent_id'       => $agent_id, // Ensure it's not null
    'type_id'        => $type_id, // Integer
    'parent_inquiries' => 0,
    'message'        => $msg,
    'subject'        => sanitize_text_field('Bulk Order Inquiry'),
    'created_at'     => current_time('mysql'), // Best practice: track when it happened
];

// 3. Define the Table Name
$table_name = $wpdb->prefix . 'inquiries';

// 4. Insert the data with Format Specifiers (Security)
// %d = integer, %s = string
$formats = ['%d', '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%s', '%s'];

$inserted = $wpdb->insert($table_name, $data, $formats);

// 5. Respond to the JavaScript caller
    if ( $inserted ) {

        // The data you want to update
        $inquired_key = $wpdb->insert_id."-".rand(1000,9999);

        $data = array(
            'inquired_key' => $inquired_key // Replace with the actual value
        );

        // The WHERE clause (which row should be updated?)
        $where = array(
            'id' => $wpdb->insert_id // Replace with the specific ID or unique identifier for the row
        );

        // Optional: define the format of the data (%s = string, %d = integer)
        $format = array('%s'); 
        $where_format = array('%d');

        $updated = $wpdb->update($table_name, $data, $where, $format, $where_format);

        $table_inquiry_products = $wpdb->prefix . 'inquiry_products';

        $result = $wpdb->insert(
            $table_inquiry_products,
            array(
                'inquired_key'  => $inquired_key,
                'product_id'    => $product_id,
                'requested_qty' => $qty,
                'requested_msg' => $msg,
            ),
            array(
                '%s', // format for inquired_key
                '%d', // format for product_id
                '%d', // format for requested_qty
                '%s'  // format for requested_msg
            )
        );
        

        // send email notification to agent here if needed
        wp_send_json_success([
            'message' => $mailsend,
            'inquiry_id' => $wpdb->insert_id
        ]);


    } else {
        // Log the error for your debugging, but send a generic message to the user
        error_log('Inquiry Insert Error: ' . $wpdb->last_error);
        wp_send_json_error(['message' => $mailsend]);

    }

    //update user meta for future use
    $agree_related = isset($_POST['agree_related']) ? 'yes' : 'no';
    $get_quotations = isset($_POST['get_quotations']) ? 'yes' : 'no';

    update_user_meta($customer_id, 'agree_related', $agree_related); //this is to store if user agree to receive related inquiries - function not done yet
    update_user_meta($customer_id, 'get_quotations', $get_quotations); //this is to store if user want to get quotations from others - function not done yet

    wp_die();
}
 
/**
 * Add Inquire Now button BEFORE the Add to Cart button for out-of-stock items
 */

function get_random_agent_data() {
    $agent_query = new WP_User_Query( array(
        'role'   => 'user-agent',
        'fields' => array( 'ID', 'user_email', 'display_name' ), // Optimization: Only fetch ID, Email, and Display Name
    ) );

    $agents = $agent_query->get_results();

    if ( ! empty( $agents ) ) {
        // Pick one random agent object from the results
        $random_agent = $agents[ array_rand( $agents ) ];
        
        return array(
            'id'    => $random_agent->ID,
            'name'  => $random_agent->display_name,
            'email' => $random_agent->user_email
        );
    }

    // Fallback: If no agents found, use ID 28
    $fallback_user = get_userdata( 28 );
    
    return array(
        'id'    => 28,
        'name'  => ( $fallback_user ) ? $fallback_user->display_name : 'Admin',
        'email' => ( $fallback_user ) ? $fallback_user->user_email : 'admin@yourdomain.com'
    );
}

add_action( 'woocommerce_single_product_summary', 'add_inquiry_button_before_cart', 30 );

function add_inquiry_button_before_cart() {
    global $product;
    $is_ready = get_post_meta( $product->get_id(), '_ready_to_buy', true );

    // Check if product is out of stock
    if ( $is_ready !== 'yes' ) {
        // Show the Inquire button
        echo '<style>.martfury-product-price{display:none}</style><div class="product-class-inquiry">
            <button type="button" id="inquire-now-btn" class="button alt inquire-now-btn" > Inquire Now</button>';
        echo '<button class="button inquiry-btn inquiry-trigger single-prod" data-product-id="' . esc_attr( $product->get_id() ) . '">
            + Inquiry List 
          </button>'; 

        echo get_product_sample_url($product->get_id()).'</div>';
        echo '<div class="payment-icons" style="margin-top: 15px;">
                <div class="payment-svg-icons  ">Payment:</div>
                <div class="payment-svg-icons  "><img src="' . get_stylesheet_directory_uri() . '/images/icons/icon_paypal.svg" alt="Paypal payment"></div>
                <div class="payment-svg-icons  "><img src="' . get_stylesheet_directory_uri() . '/images/icons/icon_visa.svg" alt="Paypal payment"></div>
                <div class="payment-svg-icons  "><img src="' . get_stylesheet_directory_uri() . '/images/icons/icon_mastercard.svg" alt="Paypal payment"></div>
                <div class="payment-svg-icons  "><img src="' . get_stylesheet_directory_uri() . '/images/icons/icon_discover.svg" alt="Paypal payment"></div>
                <div class="payment-svg-icons  "><img src="' . get_stylesheet_directory_uri() . '/images/icons/icon_AmericanExpress.svg" alt="Paypal payment"></div>
            </div>';
        // Load the modal logic

       

        add_action( 'wp_footer', 'render_inquiry_ajax_system' );
   }
}

add_filter( 'woocommerce_variation_is_visible', 'hide_variation_if_not_ready', 10, 4 );
function hide_variation_if_not_ready( $visible, $variation_id, $variable_product, $variation ) {
    
    // 1. Safety Check: Ensure $variable_product is an object and has the method
    if ( is_object( $variable_product ) && method_exists( $variable_product, 'get_id' ) ) {
        $product_id = $variable_product->get_id();
    } else {
        // Fallback: If it's not an object, it's likely already the ID
        $product_id = $variable_product;
    }

    // 2. Only proceed if we actually have a numeric ID
    if ( ! $product_id ) {
        return $visible;
    }

    $is_ready = get_post_meta( $product_id, '_ready_to_buy', true );

    // 3. Return visibility based on the meta value
    return ( $is_ready === 'yes' ) ? $visible : false;
}

/**
 * 1. Hide the Add to Cart button if "Ready to Buy" is not checked.
 */
add_filter( 'woocommerce_is_purchasable', 'toggle_product_purchasable', 10, 2 ); 
function toggle_product_purchasable( $is_purchasable, $product ) {
    $is_ready = get_post_meta( $product->get_id(), '_ready_to_buy', true );
    
    // If NOT ready to buy, make the product unpurchasable
    if ( $is_ready !== 'yes' ) {
        return false;
    }
    
    return $is_purchasable;
}

add_filter( 'woocommerce_get_price_html', 'handle_ready_to_buy_price_display', 10, 2 );
function handle_ready_to_buy_price_display( $price, $product ) {
    $is_ready = get_post_meta( $product->get_id(), '_ready_to_buy', true );

    // If NOT ready to buy, return empty string (hides Regular + Sale price)
    if ( $is_ready !== 'yes' ) {
        return ''; 
    }

    // If READY, return the normal price (Sale price will show automatically if set)
    return $price;
}

add_filter( 'woocommerce_sale_flash', 'hide_sale_percentage_if_not_ready', 10, 3 );
function hide_sale_percentage_if_not_ready( $html, $post, $product ) {
    $is_ready = get_post_meta( $product->get_id(), '_ready_to_buy', true );

    // If NOT ready to buy, return an empty string to remove the (-10%) badge
    if ( $is_ready !== 'yes' ) {
        return '';
    }

    return $html;
}

add_filter( 'body_class', 'add_not_ready_body_class' );
function add_not_ready_body_class( $classes ) {
    if ( is_product() ) {
        global $product;
        $is_ready = get_post_meta( $product->get_id(), '_ready_to_buy', true );
        if ( $is_ready !== 'yes' ) {
            $classes[] = 'is-not-ready-to-buy';
        }
    }
    return $classes;
}

function get_product_sample_url( $product_id ) {
    
    $buy_sample_product = get_post_meta( $product_id, '_buy_sample_product', true );
    if ( empty( $buy_sample_product ) ) {
        return '';  
    }
    $product_id = url_to_postid($buy_sample_product);

    $product = wc_get_product($product_id);

// 2. If the product exists, get its name and price
    if ( $product ) {
        $product_name  = $product->get_name();
        $product_price = $product->get_price_html(); // Gets formatted price like "$10.00"
        
        // 3. Create the Direct-to-Checkout link
        $checkout_url = wc_get_checkout_url() . '?add-to-cart=' . $product_id;
        
        // Return the button with the price included
        return '<a href="'.$checkout_url.'" id="buy-sample-btn" class="button alt buy-sample-btn">Buy Sample : '.$product_price.'/Piece</a>';
             // Using kses_post because price HTML can include <span> tags
       
    }

    return '';

    return '<a href="' . home_url('/order-product/?p_ref=') . base64_encode( $buy_sample_product) . '" id="buy-sample-btn" class="button alt buy-sample-btn" > Buy Sample : $30/Piece </a></div>';
}

add_shortcode('Order_sample_product', 'create_url_product_button');

function create_url_product_button() {
    // 1. Look for 'p_ref' instead of 'product'
    if ( !isset($_GET['p_ref']) || empty($_GET['p_ref']) ) {
        return '';
    }

    // 2. Decode the Base64 string (which contains your full product URL)
     $decoded_url = base64_decode($_GET['p_ref']);

    // 3. Extract the ID from that URL
     $product_id = url_to_postid($decoded_url);

    // 4. If a valid ID is found, build the button
    if ( $product_id > 0 ) {
        $product = wc_get_product($product_id);
        
        if ( $product ) {
            // Standard WooCommerce checkout link
            $checkout_url = wc_get_checkout_url() . '?add-to-cart=' . $product_id;
            
            return sprintf(
                '<a href="%s" class="button alt" style="background-color:#111; color:#fff; padding:12px 24px; text-decoration:none; border-radius:4px; font-weight:bold;">Buy %s Now</a>',
                esc_url($checkout_url),
                esc_html($product->get_name())
            );
        }
    }

    return '';
}

/**
 * Proper way to enqueue scripts and styles.
 */
function wpdocs_theme_name_scripts() {
	
	wp_enqueue_style( 'sals3-themes-sigle-product-style', get_stylesheet_directory_uri() . '/sals3/assets/css/sigle-product-style.css');
	
}
add_action( 'wp_enqueue_scripts', 'wpdocs_theme_name_scripts' );



/* sample products functions */
// 1. Add Custom Checkbox to Product Inventory Tab
add_action( 'woocommerce_product_options_inventory_product_data', 'add_sample_product_checkbox' );
function add_sample_product_checkbox() {
    woocommerce_wp_checkbox( array(
        'id'          => '_is_sample_product',
        'label'       => __( 'Sample Product?', 'woocommerce' ),
        'description' => __( 'Check this if this is a sample product that requires a minimum purchase.', 'woocommerce' )
    ) );
}

// 2. Save the Checkbox value
add_action( 'woocommerce_process_product_meta', 'save_sample_product_checkbox' );
function save_sample_product_checkbox( $post_id ) {
    $is_sample = isset( $_POST['_is_sample_product'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_is_sample_product', $is_sample );
}

// 3. Set Minimum Quantity for Sample Products
add_filter( 'woocommerce_quantity_input_args', 'set_min_qty_for_samples', 10, 2 );
function set_min_qty_for_samples( $args, $product ) {
    $is_sample = get_post_meta( $product->get_id(), '_is_sample_product', true );
    
    if ( $is_sample === 'yes' ) {
        $args['min_value'] = 5; // Change this to your desired minimum
        $args['input_value'] = max( $args['input_value'], 5 ); 
    }
    return $args;
}

// 4. Validate Cart to ensure minimum is met
add_action( 'woocommerce_check_cart_items', 'validate_sample_min_qty' );
function validate_sample_min_qty() {
    $min_qty = 5; // Change this to match the value above
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $product_id = $cart_item['product_id'];
        $is_sample = get_post_meta( $product_id, '_is_sample_product', true );
        
        if ( $is_sample === 'yes' && $cart_item['quantity'] < $min_qty ) {
            wc_add_notice( 
                sprintf( 'The minimum quantity for %s is %d.', get_the_title($product_id), $min_qty ), 
                'error' 
            );
        }
    }
}

/**
 * Add a custom Meta Box to WooCommerce Products
 */
add_action( 'add_meta_boxes', 'add_buy_sample_url_meta_box' );
function add_buy_sample_url_meta_box() {
    add_meta_box(
        'buy_sample_product_box',       // ID
        'Sample Product Link',          // Title
        'render_buy_sample_meta_box',   // Callback function
        'product',                      // Post type
        'side',                         // Context (side or normal)
        'default'                       // Priority
    );
}

/**
 * Render the Meta Box HTML
 */
function render_buy_sample_meta_box( $post ) {
    // Retrieve existing value from the database
    $value = get_post_meta( $post->ID, '_buy_sample_product', true );
    ?>
    <label for="buy_sample_product">URL for Sample Product:</label>
    <input type="url" id="buy_sample_product" name="buy_sample_product" value="<?php echo esc_attr( $value ); ?>" style="width:100%;" placeholder="https://..." />
    <p class="description">Enter the full URL for the sample version of this product.</p>
    <?php
}

/**
 * Save the Meta Box Data
 */
add_action( 'save_post_product', 'save_buy_sample_url_meta_data' );
function save_buy_sample_url_meta_data( $post_id ) {
    if ( array_key_exists( 'buy_sample_product', $_POST ) ) {
        update_post_meta(
            $post_id,
            '_buy_sample_product',
            esc_url_raw( $_POST['buy_sample_product'] )
        );
    }
}

/**
 * 1. Register Custom Order Status: To Receive
 */
function register_to_receive_order_status() {
    register_post_status( 'wc-to-receive', array(
        'label'                     => 'To Receive',
        'public'                    => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'To Receive <span class="count">(%s)</span>', 'To Receive <span class="count">(%s)</span>' )
    ) );
}
add_action( 'init', 'register_to_receive_order_status' );

/**
 * 2. Add to WooCommerce Order Statuses
 */
function add_to_receive_to_order_statuses( $order_statuses ) {
    $new_order_statuses = array();

    // Add the new status after "Processing"
    foreach ( $order_statuses as $key => $status ) {
        $new_order_statuses[ $key ] = $status;
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-to-receive'] = 'To Receive';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_to_receive_to_order_statuses' );

/**
 * 3. Add Custom Icon/Color for the Admin Order List
 */
add_action('admin_head', 'style_to_receive_status_icon');
function style_to_receive_status_icon() {
   echo '<style>
      .status-wc-to-receive {
          background: #e5e5e5 !important;
          color: #333 !important;
      }
      /* If you want a specific color like Orange for "To Receive" */
      mark.wc-to-receive {
          background-color: #ffba00;
          color: #fff;
      }
   </style>';
}


// 1. Display the Checkbox in the Inventory Tab
add_action( 'woocommerce_product_options_inventory_product_data', 'add_ready_to_buy_inventory_checkbox' );
function add_ready_to_buy_inventory_checkbox() {
    echo '<div class="options_group">'; // Wraps it in a clean section
    woocommerce_wp_checkbox( array(
        'id'            => '_ready_to_buy',
        'label'         => __( 'Ready to Buy', 'woocommerce' ),
        'description'   => __( 'Check this if the product is fully ready for purchase.', 'woocommerce' ),
        'desc_tip'      => true,
    ) );
    echo '</div>';
}

// 2. Save the Checkbox value
add_action( 'woocommerce_process_product_meta', 'save_ready_to_buy_inventory_checkbox' );
function save_ready_to_buy_inventory_checkbox( $post_id ) {
    $ready_to_buy = isset( $_POST['_ready_to_buy'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_ready_to_buy', $ready_to_buy );
}

//add_action( 'woocommerce_after_shop_loop_item', 'add_inquiry_button_to_listings', 15 );
function add_inquiry_button_to_listings() {
    global $product;
    
    // Check our custom meta toggle
    $is_ready = get_post_meta( $product->get_id(), '_ready_to_buy', true );

    // If NOT ready to buy (Inquiry Only), show the button
    if ( 'yes' !== $is_ready ) {
        echo '<button class="button inquiry-btn listing-inquiry-btn" data-product-id="' . esc_attr( $product->get_id() ) . '">
                + Inquiry
              </button>';
    }
}

add_action( 'woocommerce_after_shop_loop_item', 'add_custom_button_to_shop', 15 );

function add_custom_button_to_shop() {
    global $product;
    
    // Get the product URL
    $link = $product->get_permalink();
    // Output the button
    echo '<div class="product-info-footer">' . add_inquiry_list_button($product->get_id()) . '</div>';
}
