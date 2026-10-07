<?php
function enqueue_firebase_sdk() {

    wp_enqueue_script('firebase-app', 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js', [], null, true);
    wp_enqueue_script('firebase-db', 'https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js', ['firebase-app'], null, true);
    
    wp_enqueue_script(
        'firebase-chat',
        get_stylesheet_directory_uri() . '/sals3/assets/js/firebase-chat.js',
        [],
        date("YmdHis"),
        true
    );
    
}

add_action('wp_enqueue_scripts', 'enqueue_firebase_sdk');

function add_type_attribute($tag, $handle, $src) {
    // if not your script, do nothing and return original $tag
    if ( 'firebase-app' !== $handle && 'firebase-db' !== $handle && 'firebase-chat' !== $handle) {
        return $tag;
    }
    // change the script tag by adding type="module" and return it.
    $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
    return $tag;
}

add_filter('script_loader_tag', 'add_type_attribute' , 10, 3);

add_action( 'woocommerce_loaded', function() {
    require_once get_stylesheet_directory() . '/sals3/includes/class-wc-partner-invoice.php';
});
/*
 * Register the email class

add_filter( 'woocommerce_email_classes', function( $emails ) {
    $emails['WC_Partner_Invoice'] = new \WC_Partner_Invoice();
    return $emails;
});
 */
add_action("wp_ajax_view_inquiry_action", "view_inquiry_action");

function view_inquiry_action(){

    $product_id = $_REQUEST['product_id'];
    $inquiry = getWholesaleInquiriesChatByCustomer($_REQUEST['inquiry_id']);
    $product = wc_get_product($product_id);

    if(!$product){
        echo "Product with id of <strong>{$product_id}</strong> is not available";
        die();
    }

    $product_image_id = $product->get_image_id();
    $product_image_url = wp_get_attachment_image_url($product_image_id, 'full');
    $product_title = $product->get_name();
    $product_short_description = $product->get_short_description();

    $agent_data = get_userdata($inquiry['agent_id']);
    $agent_image = get_avatar_url($inquiry['agent_id']);
    $default_avatar_url = get_avatar_url( 'some-email@example.com', array( 'default' => 'mystery' ) );
    $agent_display_name = $agent_data->first_name . ' ' . $agent_data->last_name;
    $agent_email = $agent_data->data->user_email;

    $messages = getUserChatsByInquiry($_REQUEST['inquiry_id']);

    ob_start();

    require_once get_stylesheet_directory() . '/sals3/shortcodes/inquiry-chat-container.php';

    echo ob_get_clean();
    die();

}

add_action("wp_ajax_inquiry_send_chat", "inquiry_send_chat");

function inquiry_send_chat(){

    global $wpdb;

    $table_name = $wpdb->prefix . 'inquiry_chat';
    $inquiry_id = $_REQUEST['inquiry_id'];
    $user_id = $_REQUEST['sender_id'];
    $message = $_REQUEST['chat_input'];

    $args = [

        'inquiry_id' => $inquiry_id,
        'user_id' => $user_id,
        'message' => $message,

    ];

    $chat = $wpdb->insert($table_name, $args);

    if($chat){

        echo json_encode(['message' => 'insert chat success', 'status' => true]);
        die();

    }

    echo json_encode(['message' => 'insert chat failed', 'status' => false]);
    die();

}

add_action("wp_ajax_sls_inquiry_update_status", "sls_inquiry_update_status");

function sls_inquiry_update_status(){

    global $wpdb;

    $table_name = $wpdb->prefix . "inquiries";
    $inquiry_id = $_REQUEST['inquiry_id'];
    $status = $_REQUEST['inquiry_status'];

    $update = $wpdb->update($table_name, ['status' => $status], ['id' => $inquiry_id]);

    if($update){

        echo json_encode(['message' => "Update inquiry status to {$status}", "status" => true]);
        die();

    }

    echo json_encode(['message' => "Update inquiry status failed", "status" => false]);
    die();

}

function getUserChatsByInquiry($inquiry_id){

    global $wpdb;
    $table_name = $wpdb->prefix . 'inquiry_chat';

    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE inquiry_id = %d", $inquiry_id), ARRAY_A);

    return $results ?? [];

}
// 1. Force session start (Keep this as is)
add_action( 'init', 'maybe_set_cart_cookie', 5 );
function maybe_set_cart_cookie() {
    if ( ! is_admin() && isset(WC()->session) && ! WC()->session->has_session() ) {
        WC()->session->set_customer_session_cookie( true );
    }
}

// 2. Optimized AJAX Handler
add_action('wp_ajax_add_to_inquiry', 'handle_inquiry_addition');
add_action('wp_ajax_nopriv_add_to_inquiry', 'handle_inquiry_addition');

function handle_inquiry_addition() {
    if ( ! function_exists( 'WC' ) ) { wp_send_json_error( 'WC not found' ); }

    // FORCE Session Initialization for Logged-in Users
    if ( is_user_logged_in() || ! WC()->session->has_session() ) {
        WC()->session->set_customer_session_cookie( true );
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ( $product_id > 0 ) {
        // Get the list
        $inquiry_list = WC()->session->get('custom_inquiry_list', array());

        if ( ! in_array( $product_id, $inquiry_list ) ) {
            $inquiry_list[] = $product_id;
            
            // Save to session
            WC()->session->set('custom_inquiry_list', $inquiry_list);
            
            // CRITICAL: For logged-in users, we must force a data save 
            // to ensure it writes to the database session table.
            if ( is_user_logged_in() ) {
                $customer_id = get_current_user_id();
                // This ensures the data is tied to the User ID, not just a cookie
                WC()->session->save_data(); 
            } else {
                WC()->session->save_data();
            }
            
            wp_send_json_success( array( 'count' => count( $inquiry_list ) ) );
        } else {
            wp_send_json_success( 'already_exists' );
        }
    }
    wp_send_json_error( 'Invalid Product ID' );
}

// Handle AJAX removal inquiry item
add_action('wp_ajax_remove_inquiry_item', 'handle_remove_inquiry_item');
add_action('wp_ajax_nopriv_remove_inquiry_item', 'handle_remove_inquiry_item');

function handle_remove_inquiry_item() {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $list = WC()->session->get('custom_inquiry_list', array());

    if (($key = array_search($product_id, $list)) !== false) {
        unset($list[$key]);
        WC()->session->set('custom_inquiry_list', array_values($list));
        WC()->session->save_data();
        wp_send_json_success();
    }
    wp_send_json_error();
}

// Handle Clear All basket enquiry items
add_action('wp_ajax_clear_inquiry_list', 'handle_clear_inquiry_list');
add_action('wp_ajax_nopriv_clear_inquiry_list', 'handle_clear_inquiry_list');

function handle_clear_inquiry_list() {
    WC()->session->set('custom_inquiry_list', array());
    WC()->session->save_data();
    wp_send_json_success();
}

function should_load_inquiry_script() {
    return (
        is_product() ||
        is_shop() ||
        is_search() ||
        is_product_category() ||
        is_page('inquiry-basket') ||
        is_page('wishlist') ||
        is_page('analysts-choice') ||
        is_page('low-moq') ||
        is_page('oem-products') ||
        is_front_page()
    );
}

// 3. Footer Script (Keep this, it's correct)
add_action('wp_footer', 'add_inquiry_script_to_footer');
function add_inquiry_script_to_footer() {
    // Only load on single products or the shop to save resources
    //if ( is_page('inquiry-basket') ) {
    if ( ! should_load_inquiry_script() ) return;
    // Kurl - Store inquiry list contents for the JS
    $inquiry_items = WC()->session->get('custom_inquiry_list', array());
    ?>
    <script type="text/javascript">
    (function($) { // <--- This '($)' at the start is the fix
        $(document).ready(function() {
            // Kurl - Check if wishlist product is already in the inquiry list
            const inquiryItems = <?php echo json_encode($inquiry_items) ?> || [];
            const inquirySet = new Set(inquiryItems);
            console.log('inquiry basket ids:',inquiryItems);
            $('.inquiry-trigger').each(function() {
                const btn = $(this);
                const productId = parseInt(btn.data('product-id'));

                if (inquirySet.has(productId)) {

                btn.prop('disabled', true)
                .addClass('is-added-inquiry');

                // Optional per-button UI logic
                if (btn.is('#wishlist-inquiry-btn') || btn.is('.single-prod')) {
                    btn.text('Added');
                } else {
                    btn.html('<i class="icon-check"></i>');
                }
            }
        });

      
        // Handle Clear All Button
        $(document).on('click', '#clear-inquiry-list', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to clear your entire inquiry list?')) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: { action: 'clear_inquiry_list' },
                    success: function(response) {
                        if (response.success) {
                            // Refresh the basket content immediately
                            refreshInquiryBasket();
                            
                            // Optional: Reset all "Added to List" buttons on the page back to default
                            $('.inquiry-btn').text('Add to Inquiry List').prop('disabled', false).removeClass('added');
                        }
                    }
                });
            }
        });

        // Handle removing items from the sliding basket
        $(document).on('click', '.remove-from-basket', function(e) {
            e.preventDefault();
            const btn = $(this);
            const productId = btn.data('id');

            // Optional: add a loading state to the specific item
            btn.text('...').prop('disabled', true);

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'remove_inquiry_item', // This matches the PHP function we created earlier
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        // Refresh the basket content "on-time"
                        <?php
                            if (is_page('inquiry-basket')) {
                                ?>
                                // If on the inquiry basket page, also remove the item row
                                $('#basket-item-' + productId).fadeOut(function() {
                                    $(this).remove();
                                });
                                <?php
                            }
                        ?>
                        refreshInquiryBasket();
                    } else {
                        alert('Could not remove item.');
                        btn.html('&times;').prop('disabled', false);
                    }
                }
            });
        });

        // Function to refresh the basket content
        function refreshInquiryBasket() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: { action: 'get_inquiry_basket_html' },
                success: function(response) {
                    if (response.success) {
                        $('#basket-content').html(response.data);
                    }
                }
            });
        }


        // Kurl - Added #wishlist-inquiry-btn
        $(document).on('click', '.inquiry-btn, #wishlist-inquiry-btn', function(e) {

            e.preventDefault();
            const button = $(this);
            const productId = button.data('product-id');
            <?php if ( is_product()) : ?>
            button.text('Adding...').prop('disabled', true); 
            <?php else: ?>
            button.addClass('is-added-inquiry');
            button.html('<i class="icon-check extra-icon" style="color:#ff4c00;"></i>').prop('disabled', true);

            <?php endif; ?>
            console.log('Adding product ID ' + productId + ' to inquiry list.');
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'add_to_inquiry',
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        if(button.hasClass('single-prod')) {
                            // On single product page, change button to "Added to List"
                            button.text('Added to List').addClass('added');
                        } else {
                            button.text('').addClass('added');
                            button.html('<i class="icon-check extra-icon" style="color:#ff4c00;"></i>').prop('disabled', true);
                        }
                        
                        // 1. Refresh the basket HTML "On-Time"
                        refreshInquiryBasket();
                        var $counter = $( '#icon-basket-contents .mini-item-counter' );
                        var currentCount = parseInt( $counter.text().trim() ) || 0;
                        var newCount = currentCount + 1;
                        $counter.text( newCount );
                        // 2. Slide the basket into view
                        $('#inquiry-slide-basket').removeClass('basket-hidden');
                    } else {
                        alert('Error: ' + response.data);
                        button.text('Add to Inquiry List').prop('disabled', false);
                    }
                }
            });
        });

        // Close basket event
        $(document).on('click', '#close-basket', function() {
            $('#inquiry-slide-basket').addClass('basket-hidden');
        });

        // Initial load: refresh basket once when page loads to show existing items
        refreshInquiryBasket();

    });
    })(jQuery); // <--- Pass jQuery into the function here
    </script>
    <?php
    $list = WC()->session->get('custom_inquiry_list', array());
        echo '<div id="inquiry-slide-basket" class="basket-hidden">';
        echo '<div class="basket-header">';
        echo '<h4>Your Inquiry Basket</h4>';
        echo '<button id="close-basket" style="text-align:right; background:none; border:none; font-size:24px; cursor:pointer; color:#fff" aria-label="Close Basket"><i class="icon-cross"></i></button>';
        echo '</div>';
        echo '<div id="basket-content" style="padding:15px; overflow-y:auto; flex-grow:1;">';
    if(!empty($list)) {


            foreach($list as $id) {
                $p = wc_get_product($id);
                echo '<div style="display:flex;gap:10px;margin-bottom:10px;font-size:14px;font-weight:500; border-bottom:1px solid #f0f0f0; padding-bottom:8px;">';
                echo $p->get_image(array(40, 40));
                echo '<span style="font-weight:500;">' . $p->get_name() . '</span>';
                echo '</div>';
            }



    } else {
        echo '<p>Your list is empty</p>';
    }
      
        echo '</div>'; // Close basket-content

        echo '<div class="basket-footer sticky-footer"> 
        <div style="display:flex; justify-content:space-between;">
         <div id="clear-inquiry-list"><button id="clear-inquiry-list" style="text-align:left; display: inline-block; background:none; border:none; font-size:15px; cursor:pointer;" aria-label="Clear Inquiry List" ><i class="icon-cart-empty"></i></button> Clear Basket</div>
    <div style="text-align:right;">
        <a href="'.site_url('/inquiry-basket/').'" class="btn-bluegreen">View Full List</a>
    </div></div></div>';

        echo '</div>';
        echo '</div>';  
    ?>
    <style>
        #inquiry-slide-basket {
            position: fixed;
            bottom: 0px;
            right: 20px;
            width: 450px;
            max-height: 550px; /* Limits the total height */
            background: #fff;
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
            border-radius: 12px 12px 0 0;
            z-index: 99999;
            display: flex;
            flex-direction: column; /* Stacks header, content, and footer */
            overflow: hidden; /* Prevents rounded corners from being clipped */
        }

        /* Hidden state: slides down and out of view */
        #inquiry-slide-basket.basket-hidden {
            transform: translateY(120%);
        }

        .basket-header {
            padding: 15px;
            background: #333;
            color: #fff;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .basket-header h4{
            margin: 0;
            font-size: 18px; color:#fff;
        }

        #close-basket {
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }

        #basket-content {
            padding: 15px;
            overflow-y: auto; /* This makes the middle section scrollable */
            flex-grow: 1;     /* Allows content to take up available space */
        }

            .basket-footer {
                padding: 15px;
                border-top: 1px solid #eee;
            }

        .basket-footer.sticky-footer {
            padding: 15px;
            background: #ffffff;
            border-top: 1px solid #f0f0f0;
            margin-top: auto; /* Pushes it to the very bottom */
            box-shadow: 0 -5px 10px rgba(0,0,0,0.02); /* Subtle shadow to separate from content */
        }

        .basket-footer .button.full-width {
            display: block;
            text-align: center;
            background: #2ecc71;
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }

        .basket-footer .button.full-width:hover {
            background: #27ae60;
        }

            .basket-footer .button.full-width {
                display: block;
                text-align: center;
                width: 100%;
                background: #2ecc71;
                color: #fff;
                padding: 10px;
                border-radius: 6px;
                text-decoration: none;
            }
        #basket-content {
            scrollbar-gutter: stable;
            scrollbar-width: thin; /* For Firefox */
        }

        /* For Chrome/Safari */
        #basket-content::-webkit-scrollbar {
            width: 6px;
        }
        #basket-content::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 10px;
        }
        #clear-inquiry-list{
            font-size:14px;
            font-weight:600;
        }
        #clear-inquiry-list:hover{
            color:red; cursor: pointer;
        }

        @media(max-width: 600px) {
            #inquiry-slide-basket {
                width: 98%;
                right: 0px;
                left: 50%;
                transform: translate(-50%, 0%);
                padding-bottom: 60px;
                max-height: 600px;
            }
        }
    </style>
    <?php
}

// AJAX to fetch the current list HTML
add_action('wp_ajax_get_inquiry_basket_html', 'handle_get_inquiry_basket_html');
add_action('wp_ajax_nopriv_get_inquiry_basket_html', 'handle_get_inquiry_basket_html');

function handle_get_inquiry_basket_html() {
    $list = WC()->session->get('custom_inquiry_list', array());
    
    ob_start();
    if ( empty( $list ) ) {
        echo '<p class="empty-msg">Your list is empty</p>';
    } else {
        foreach ( $list as $product_id ) {
            $product = wc_get_product( $product_id );
            if ( ! $product ) continue;
            ?>
            <div class="basket-item" id="basket-item-<?php echo $product_id; ?>" style="display:flex; align-items:center; gap:10px; margin-bottom:12px; border-bottom:1px solid #f0f0f0; padding-bottom:8px;">
                <div class="basket-img" style="width:40px; height:40px; flex-shrink:0;">
                    <?php echo $product->get_image(array(40, 40)); ?>
                </div>
                <div class="basket-info" style="flex-grow:1; font-size:14px;">
                    <span style="display:block;"><?php echo $product->get_name(); ?></span>
                </div>
                <div class="basket-remove">
                    <button class="remove-from-basket" data-id="<?php echo $product_id; ?>" title="Remove item" style="background:none; border:none; color:#ff4d4d; font-size:20px; cursor:pointer; padding:0 5px;">&times;</button>
                </div>
            </div>
            <?php
        }
    }
    $html = ob_get_clean();
    wp_send_json_success( $html );
}

function display_inquiry_list() {

    $inquiry_list = WC()->session->get('custom_inquiry_list', array());

    ob_start();
    echo '<div id="inquiry-list-wrapper">';
    if (empty($inquiry_list)) {
        echo '<p>Your inquiry list is empty.</p>';
    } else {
        foreach ($inquiry_list as $product_id) {
            $product = wc_get_product($product_id);
            echo '<div class="inquiry-item" style="display:flex; align-items:center; gap:15px; margin-bottom:10px; background:#fff; padding:10px; border-radius:8px;">';
            echo $product->get_image('thumbnail');
            echo '<span>' . $product->get_name() . '</span>';
            echo '</div>';
        }
        // The Popup Trigger Button
        echo '<button id="open-inquiry-form" class="button alt" style="margin-top:20px;">Send Inquiry for all Items</button>';
    }
    echo '</div>';

    // The Popup Modal (Hidden by default)
    ?>
    <div id="inquiry-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999;">
        <div style="background:#fff; width:90%; max-width:500px; margin:100px auto; padding:30px; border-radius:12px; position:relative;">
            <h3>Request a Quote</h3>
            <p>Fill out the form below to receive pricing for your selected items.</p>
            <?php echo do_shortcode('[contact-form-7 id="YOUR_FORM_ID" title="Inquiry Form"]'); ?>
            <button id="close-modal" style="position:absolute; top:10px; right:10px; border:none; background:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#open-inquiry-form').click(function() {
            $('#inquiry-modal').fadeIn();
        });
        $('#close-modal').click(function() {
            $('#inquiry-modal').fadeOut();
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

add_action('wp_footer', 'add_sticky_inquiry_script');
function add_sticky_inquiry_script() {
    ?>
    <script>
        (function() {
            const el = document.querySelector('.inquiry-summary-content');
            console.log(el);
            if (!el) return;

            window.addEventListener('scroll', function() {
                if (window.scrollY >= 178) {
                    console.log('Adding sticky class');
                    el.classList.add('is-sticky');
                } else {
                    el.classList.remove('is-sticky');
                }
            });
        })();
    </script>
    <?php
}