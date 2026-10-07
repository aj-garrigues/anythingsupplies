<?php

function account_enqueue_script(){

    $strtotime = strtotime("now");
    wp_enqueue_script("martfury-child-art-script", get_stylesheet_directory_uri() . '/sals3/assets/js/martfury-child-art-script.js', ['jquery'], $strtotime, true);

    wp_localize_script("martfury-child-art-script", "ajax_obj", ['ajax_url' => admin_url('admin-ajax.php')]);

    wp_enqueue_style("martfury-child-art-css", get_stylesheet_directory_uri() . '/sals3/assets/css/arth.css');


}

add_action("wp_enqueue_scripts", "account_enqueue_script");

function get_user_initial_and_name_shortcode() {
    $current_user = wp_get_current_user();

    if ( ! $current_user->exists() ) {
        return '';
    }

    // Get first name or fallback to display name
    $name = trim( $current_user->first_name );
    if ( empty( $name ) ) {
        $name = trim( $current_user->display_name );
    }

    // Get first letter only
    $initial = strtoupper( mb_substr( $name, 0, 1 ) );

    return '
        <span class="user-account-wrap">
            <span class="user-initial-circle">' . esc_html( $initial ) . '</span>
            <span class="user-nickname">' . esc_html( $current_user->display_name ) . '</span>
        </span>
    ';
}

add_shortcode( 'account_info', 'get_user_initial_and_name_shortcode' );

add_action( 'template_redirect', 'sals3_restrict_my_account' );

function sals3_restrict_my_account() {
    // 1. Check if the current page is 'my-account'
    // 2. Check if the user is NOT logged in
    if (isset($_GET['reset-link-sent']) && $_GET['reset-link-sent'] === 'true') {
        wp_redirect( site_url( '/account-lostpassword/?password-reset=sent' ) );
        exit; 
    }

    if ( is_page('my-account') && !is_user_logged_in() ) {
        wp_redirect( site_url( '/account-login/' ) );   
        exit;
    }

    if ( is_page('account-login') && is_user_logged_in() ) {
        wp_redirect( site_url( '/my-account/' ) );
        exit; 
    }

}

add_filter( 'lostpassword_url', 'sals3_custom_lost_password_url', 10, 2 );
function sals3_custom_lost_password_url( $lostpassword_url, $redirect ) {
    // Replace 'account-lostpassword' with your actual page slug
     $redirect = "account-lostpassword1/?password-reset=sent";
    $args = array( 'redirect_to' => $redirect );
    return add_query_arg( $args, site_url( '/account-lostpassword/' ) );
}


/**
 * Shortcode to show Reset Password Form ONLY if key is present
 * Usage: [sals3_reset_password_check]
 */
add_shortcode( 'sals3_reset_password_check', 'sals3_handle_reset_password_display' );

function sals3_handle_reset_password_display() {
    if ( ! class_exists( 'WooCommerce' ) ) return '';

    // Check if 'key' and 'login' are present in the URL
    if ( ! empty( $_GET['key'] ) && ! empty( $_GET['login'] ) ) {
        
        // Start buffering to get the form
        ob_start();
        
        wc_get_template( 'myaccount/form-reset-password.php', array(
            'key'   => $_GET['key'],
            'login' => $_GET['login']
        ) );

        return ob_get_clean();

    } else {
        // If 'key' IS NULL or missing, show a friendly error or redirect
        return '
        <div class="sals3-error-box">
            <div class="icon">⚠️</div>
            <h2>Invalid Access</h2>
            <p>This password reset link is invalid or has expired. Please request a new one to continue.</p>
            <a href="' . site_url('/account-lostpassword/') . '" class="btn-primary">Request New Link</a>
        </div>';
    }
}

/**
 * 1. Register the custom endpoint
 */
add_action( 'init', 'add_wholesale_endpoint' );
function add_wholesale_endpoint() {
    // This creates the URL slug: yoursite.com/my-account/wholesale
    add_rewrite_endpoint( 'wholesale-management', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'company-info', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'billing-info', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'shipping-info', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'order-invoice', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'my-invoice', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'edit-security', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'edit-privacy', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'edit-notification', EP_ROOT | EP_PAGES );
}

add_filter( 'woocommerce_account_menu_items', 'sals3_edit_account_menu', 99 );

function sals3_edit_account_menu( $items ) {
    // 1. Rename an item
    $items['dashboard'] = 'Dashboard';
    unset($items['payment-methods']);
    
    
   // $items['my-invoice']    = 'Payments';
    unset($items['edit-account']);
    $items['wholesale-management']    = 'Messages';

    $items['edit-account'] = 'My Profile';
    $items['payment-methods']    = 'Payment Methods';
    unset($items['edit-address']);

    unset($items['customer-logout']);

// 1. The Parent "Heading" (Acts as the dropdown trigger)
    // 2. The Children
    $items['edit-account']  = 'My Account';
    // $items['company-info']  = 'Company Details';

    // 2. Remove an item (e.g., remove "Downloads")
    unset( $items['downloads'] );
 
    $items['customer-logout'] = 'Log Out';
    
    return $items;
}

 
// 1. Company Information Content
// B. Show the content (Notice the hook name matches your endpoint)
add_action( 'woocommerce_account_edit-security_endpoint', 'render_security_content' );

function render_security_content() {
    wc_get_template('myaccount/my-security.php', array('company_info' => 'shipping'));
}

add_action('woocommerce_account_edit-notification_endpoint', 'render_notification_content');
function render_notification_content() {
    wc_get_template('myaccount/my-notification.php', array('company_info' => 'shipping'));
    // Add your form or logic here
}

add_action( 'woocommerce_account_edit-privacy_endpoint', 'render_privacy_content' );

function render_privacy_content() {
    wc_get_template('myaccount/my-privacy.php', array('company_info' => 'shipping'));
}
// 1. Company Information Content
add_action('woocommerce_account_company-info_endpoint', 'render_company_info_content');
function render_company_info_content() {
    $user_id = get_current_user_id();
    $company = get_user_meta($user_id, 'billing_company', true);
    $contact = get_user_meta($user_id, 'contact_person', true);
    
    wc_get_template('myaccount/my-company.php', array('company_info' => 'shipping'));

}

// 1. Company Information Content
add_action('woocommerce_account_order-invoice_endpoint', 'render_order_invoice_content');
function render_order_invoice_content() {
    $user_id = get_current_user_id();
    $company = get_user_meta($user_id, 'billing_company', true);
    $contact = get_user_meta($user_id, 'contact_person', true);
    
    echo '<h3>Company Information</h3>';
    echo '<p><strong>Company Name:</strong> ' . esc_html($company) . '</p>';
    echo '<p><strong>Contact Person:</strong> ' . esc_html($contact) . '</p>';
    echo '<a href="' . wc_get_endpoint_url('edit-address', 'billing') . '" class="button">Edit Company Info</a>';
}

// 1. My invoices
add_action('woocommerce_account_my-invoice_endpoint', 'render_my_invoice_content');
function render_my_invoice_content() {
    $user_id = get_current_user_id();
    
    wc_get_template('myaccount/my-invoices.php', array('load_address' => 'shipping'));

}


/**
 * 3. Add Content to the page
 * Note: The hook name must match your endpoint slug exactly
 */
add_action( 'woocommerce_account_wholesale-management_endpoint', 'wholesale_management_content' );
function wholesale_management_content() {
    global $wpdb;
    $current_user = wp_get_current_user();
    $partner_id = $current_user->ID;

    // 1. get inquiries for current user
    $results = $wpdb->get_results("
        SELECT i.* FROM sls_inquiries i
        LEFT JOIN sls_inquiry_types t ON i.type_id = t.id
        WHERE i.customer_id = $partner_id OR i.agent_id = $partner_id AND t.type_name = 'inqueries'
        ORDER BY i.created_at DESC
    ");

    $rfqs_results = $wpdb->get_results("
        SELECT i.* FROM sls_inquiries i
        LEFT JOIN sls_inquiry_types t ON i.type_id = t.id
        WHERE i.customer_id = $partner_id AND t.type_name = 'rfqs'
        ORDER BY i.created_at DESC
    ");

//print_r($results);

    wc_get_template('myaccount/my-wholesale.php', array('inquiries' => $results, 'rfqs' => $rfqs_results));
}
//my orders content
add_action( 'woocommerce_account_my-orders_endpoint', 'my_orders_content' );
function my_orders_content() {
    global $wpdb;
    $current_user = wp_get_current_user();
    $partner_id = $current_user->ID;

    // 1. get inquiries for current user
    $results = $wpdb->get_results("
        SELECT i.* FROM sls_inquiries i
        LEFT JOIN sls_inquiry_types t ON i.type_id = t.id
        WHERE i.customer_id = $partner_id AND t.type_name = 'inqueries'
        ORDER BY i.created_at DESC
    ");
//print_r($results);

    wc_get_template('myaccount/my-orders-new.php', array('inquiries' => $results));
}



// 2. Billing Address Content
add_action('woocommerce_account_billing-info_endpoint', 'render_billing_info_content');
function render_billing_info_content() {

    wc_get_template('myaccount/my-billinginfo.php', array('load_address' => 'billing'));
}

// 3. Shipping Address Content
add_action('woocommerce_account_shipping-info_endpoint', 'render_shipping_info_content');
function render_shipping_info_content() {
    echo '<h3>Your Shipping Address</h3>';
    wc_get_template('myaccount/my-address.php', array('load_address' => 'shipping'));
}

/**
 * Shortcode to display the WooCommerce Lost Password Form
 * Usage: [sals3_lost_password_form]
 */
add_shortcode( 'sals3_lost_password_form', 'sals3_render_lost_password_form' );

function sals3_render_lost_password_form() {
    // Only proceed if WooCommerce is active
    if ( ! class_exists( 'WooCommerce' ) ) return '';

    // IF THE REDIRECT JUST HAPPENED:
    if ( isset($_GET['password-reset']) && $_GET['password-reset'] === 'sent' ) {
        return '
        <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-7"></div>
        <div class="col-lg-4">
                <div class="sals3-success-box">
            <div class="icon">✉️</div>
            <h2>Check your email!</h2>
            <p>We’ve sent a password reset link to your email address. It may take a few minutes to arrive. Don\'t forget to check your spam folder!</p>
            <a href="' . site_url('/account-login/') . '" class="btn-link">Return to Login</a>
        </div> 
        </div>
        

        </div>
        ';
    }

    // If user is already logged in, they don't need this form
    if ( is_user_logged_in() ) {
        return '<p class="already-logged-in">You are already logged in. <a href="">Visit your account</a></p>';
    }

    // Start output buffering to capture the WC template
    ob_start();

    // Load the WooCommerce Lost Password Form template
    wc_get_template( 'myaccount/form-lost-password.php' );

    return ob_get_clean();
}

/**
 * Redirect users to a custom URL after requesting a password reset
 */
add_action( 'woocommerce_after_lost_password_form', 'sals3_redirect_after_lost_password' );

function sals3_redirect_after_lost_password() {
    if ( isset( $_POST['wc_reset_password'] ) && ! wc_has_notice( 'error' ) ) {
        // We use a small script to redirect because WC handles the submission via POST
        // and doesn't have a direct "success redirect" filter for this specific form.
        ?>
        <script type="text/javascript">
            window.location = "<?php echo site_url('/account-lostpassword/?password-reset=sent'); ?>";
        </script>
        <?php
    }
}

function set_custom_mail_from( $email ) {
    // Check if the current action is the user registration email action
    // This makes the change specific to this function if needed,
    // but often, this is set site-wide. We'll set it site-wide for simplicity.
    return 'sales@anythingsupplies.com';
}
add_filter( 'wp_mail_from', 'set_custom_mail_from' );

/**
 * Filter to change the 'From' name for outgoing WP emails.
 */
function set_custom_mail_from_name( $name ) {
    // Set the desired sender name
    return 'Anything Supplies Community';
}
add_filter( 'wp_mail_from_name', 'set_custom_mail_from_name' );

// Step 1: Generate code, save it, and send the email on registration
function my_registration_email_verification( $user_id ) {
    $user_info = get_userdata( $user_id );
    
    // Generate a unique token
    $verification_token = wp_generate_password( 30, false ); // 30 char token, no special chars
    
    // Store the token and a verification status meta
    add_user_meta( $user_id, 'email_verification_token', $verification_token, true );
    add_user_meta( $user_id, 'is_email_verified', '0', true ); // '0' for unverified

    // Create the verification link
    // Replace 'verify-email' with the slug of your actual verification page
   // $verification_url = home_url( '/verify-account/?user=' . base64_encode($user_id) . '&token=' . $verification_token );
    $verification_url = home_url( '/account-registration/?stat=verify&user=' . base64_encode($user_id) . '&token=' . $verification_token );

    $subject = 'Welcome to Anything Supplies! Finish your profile for 20% off your first order';
$logo_url = esc_url( get_stylesheet_directory_uri() . '/images/SALS3-03-scaled.jpg' );

$message = '
<div style="background-color: #f6f9fc; padding: 40px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e6ebf1;">
        
        <div style="padding: 40px 20px; text-align: center; background-color: #ffffff;">
            <img src="' . $logo_url . '" style="width: 220px; height: auto;" alt="Anything Supplies Logo">
        </div>

        <div style="padding: 0 40px 40px 40px;">
            <h2 style="color: #1a1f36; font-size: 24px; font-weight: 700; line-height: 1.3; text-align: center; margin-bottom: 24px;">
                Welcome to Anything Supplies<br>
                <span style="color: #2eb82e; font-size: 16px; font-weight: 400; text-transform: uppercase; letter-spacing: 1px;">The Smart Affordable Lifestyle Shopping</span>
            </h2>
            
            <p style="color: #4f566b; font-size: 16px; line-height: 1.6; text-align: center;">
                Thank you for registering! We\'re excited to have you join our community. To finalize your account and start shopping, please verify your email address below.
            </p>

            <div style="padding: 30px 0; text-align: center;">
                <a href="' . esc_url( $verification_url ) . '" 
                   style="background-color: #2eb82e; color: #ffffff; padding: 16px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 6px rgba(46, 184, 46, 0.2);">
                   Verify Your Email Address
                </a>
            </div>

            <p style="color: #a3acb9; font-size: 13px; line-height: 1.5; text-align: center; margin-top: 10px;">
                This link is valid for <strong>24 hours</strong>. <br>
                If the button doesn\'t work, copy and paste this link:<br>
                <a href="' . esc_url( $verification_url ) . '" style="color: #2eb82e; word-break: break-all;">' . esc_url( $verification_url ) . '</a>
            </p>

            <div style="margin-top: 40px; padding-top: 25px; border-top: 1px solid #e6ebf1;">
                <p style="color: #1a1f36; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Important Security Notice</p>
                <p style="color: #8792a2; font-size: 12px; line-height: 1.6; margin-bottom: 20px;">
                    For your security, please do not share this link with anyone. If you did not initiate this registration, please disregard this email.
                </p>
                
                <p style="color: #4f566b; font-size: 14px; margin-bottom: 5px;">Best regards,</p>
                <p style="color: #1a1f36; font-size: 14px; font-weight: 700;">The Anything Supplies Team</p>
            </div>
        </div>

        <div style="padding: 20px; background-color: #f8fafc; text-align: center;">
            <p style="color: #9ba3af; font-size: 11px; margin: 0;">&copy; 2026 Anything Supplies. All rights reserved.</p>
        </div>
    </div>
</div>';
    
    // Set headers for HTML email
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_logout();
    // Send the email
    wp_mail( $user_info->user_email, $subject, $message, $headers );
    
}

add_action( 'user_register', 'my_registration_email_verification', 10, 1 );

// Step 2: Prevent login for unverified users
function my_prevent_unverified_login( $user, $password ) {
    if ( is_wp_error( $user ) ) {
        return $user; // Return existing errors
    }
    
    $is_verified = get_user_meta( $user->ID, 'is_email_verified', true );

    if ( $is_verified !== '1' ) {
        // Log out the user in case they were auto-logged in by a plugin
        wp_logout(); 
        
        return new WP_Error( 
            'email_unverified', 
            __( '<strong>ERROR:</strong> Your account is not yet verified. Please check your email for the verification link.', 'your-text-domain' ) 
        );
    }
    
    return $user;
}
add_filter( 'authenticate', 'my_prevent_unverified_login', 30, 3 ); // Lower priority to run after core checks

// Step 3: Handle the verification link click
function my_verify_email_handler() {
   // echo '<!-- Verification Handler Triggered -->'; // Debugging line
    // Check if the necessary GET parameters are present
     error_log("my account : step one", 3, CJSYNC . '/wccj_error.log');

    if ( isset( $_GET['user'] ) && isset( $_GET['token'] ) ) {
       $user_id = base64_decode( $_GET['user'] );
        $token_from_url = sanitize_text_field( $_GET['token'] );

        $user = get_user_by( 'id', $user_id );
		
        if ( $user ) {
            $stored_token = get_user_meta( $user_id, 'email_verification_token', true );

            // Check if the token matches and is not empty
            if ( $stored_token && $token_from_url === $stored_token ) {
                
                // Set the user as verified
                update_user_meta( $user_id, 'is_email_verified', '1' );
                
                // Remove the token so it can't be used again
                delete_user_meta( $user_id, 'email_verification_token' );
                
                // Optionally log the user in immediately
                wp_set_current_user( $user_id, $user->user_login );
                wp_set_auth_cookie( $user_id, true );
                
                // Redirect to a success page
                wp_redirect( home_url( '/verified-account/?user='.$user_id.'&status=success' ) ); 
                exit;
            } else {
                // Invalid or expired token
                wp_redirect( home_url( '/verified-account/?user='.$user_id.'&status=failed' ) );
                exit;
            }
        }
    }

    // --- Configuration ---
    //echo $_SERVER['REQUEST_URI'];
    $account_page_slug = "my-account";      // The slug of your My Account page
    $verify_page_slug = 'account-verification'; // The slug of your verification instructions page
    // ---------------------

// 1. Check if the user is logged in
    if ( is_user_logged_in() ) {
  

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        
        // --- NEW CHECK: Bail if the user is an Administrator ---
        // 'administrator' is the default WordPress admin role slug
        if ( in_array( 'administrator', (array) $current_user->roles ) ) {
            return; // Admins are allowed to proceed regardless of verification status
        }
        // ----------------------------------------------------

        // 2. Check the user's verification status
        $is_verified = get_user_meta( $user_id, 'is_email_verified', true );

        // 3. Check if the current page is the My Account page AND the user is NOT verified
        if ( strpos( $_SERVER['REQUEST_URI'], $account_page_slug ) == true && $is_verified !== '1' ) {
          //  echo '<!-- Redirecting unverified user -->'; // Debugging line
            // Prevent an infinite loop
                // Perform the redirect
                //wp_redirect( home_url( '/' . $verify_page_slug ) );
                error_log("Login: sals3_redirect_after_lost_password status is: $is_verified", 3, CJSYNC . '/wccj_error.log');
                wp_redirect( home_url( '/' . $verify_page_slug ) );
                exit; // Stop execution after redirect
            
        }
    }
     error_log("My Account: sals3_redirect_after_lost_password status is: $is_verified", 3, CJSYNC . '/wccj_error.log');

}
//add_action( 'init', 'my_verify_email_handler' );



// Step 4 (Template Logic): Display messages on your /verify-email/ page
// You would edit the template for your /verify-email/ page to check $_GET['status']
// and display "Success! You are verified." or "Verification failed."

/**
 * Redirects the user to a "Thank You/Verification" page after registration.
 */
add_filter('woocommerce_registration_redirect', 'custom_registration_redirect_and_logout', 2);

function custom_registration_redirect_and_logout($redirect_url) {
    // Log the user out immediately so they aren't "in session"
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    wp_logout();

    // Set the URL to your specific "account-verification" or "thank-you" page
    // We add a 'stat' parameter so the redirect logic we built earlier doesn't trigger
    $redirect_url = home_url('/account-registration/?stat=emailverification&user=' . base64_encode($user_id));
    return $redirect_url;
}

// Register the shortcode with WordPress
add_shortcode( 'account_registration', 'account_registration_process' );

function account_registration_process() {
    
    if ( $_GET['stat']==='emailverification' ) { 

        // Use the user's Display Name (which is often their full name or chosen screen name)

        $output = '
        <div class="row">
            <div class="col-lg-3">  </div>
            <div class="col-lg-6">                  
            <div class="account-registeration-verification">
   
    <h1>Registration Successful!</h1>
    <p style="font-size:16px; ">
        An activation email has been sent to your email address. Please follow the instructions in the email to verify your account and complete the process.
    </p>

    <div class="button-group">
        <a href="/account-login" class=" btn-black ">Proceed to Login</a>
        <a href="/account-registration/?stat=resendverification&user=MTQy" class=" btn-red">Resend Verification Email</a>
    </div>
    <div class="divider">
    <p class="footer-note clearfix" style="font-size:16px; margin-top:20px;">
        Didn\'t see the email? Please check your <strong>Spam</strong> or <strong>Promotions</strong> folder.
    </p>
    </div>
</div></div>
<div class="col-lg-3">  </div>
</div>
        ';
    }
    
    if ( $_GET['stat']==='resendverification' ) { 

        // Use the user's Display Name (which is often their full name or chosen screen name)

        $output = '
        <div class="row">
            <div class="col-lg-3">  </div>
            <div class="col-lg-6">                  
            <div class="account-registeration-verification">
   
    <h1>Verification Re-sent!</h1>
    <p style="font-size:16px; ">
        An activation email has been re-sent to your email address. Please follow the instructions in the email to verify your account and complete the process.
    </p>

    <div class="button-group"> 
        <a href="/account-login" class=" btn-black ">Proceed to Login</a>
        <a href="/account-registration/?stat=resendverification&user=MTQy" class=" btn-red">Resend Verification Email</a>
    </div>
    <div class="divider">
    <p class="footer-note clearfix" style="font-size:16px; margin-top:20px;">
        Didn\'t see the email? Please check your <strong>Spam</strong> or <strong>Promotions</strong> folder.
    </p>
    </div>
</div></div>
<div class="col-lg-3">  </div>
</div>
        ';
    }

    if ( $_GET['stat']==='verify' ) { 
        $output = account_registration_verify();
    }
    return $output;
}

function account_registration_verify() {
    error_log("my account : step two\n", 3, CJSYNC . '/wccj_error.log');
    // Check if the necessary GET parameters are present
    if ( isset( $_GET['user'] ) && isset( $_GET['token'] ) ) {
        error_log("my account : step three\n", 3, CJSYNC . '/wccj_error.log');
        $user_id = base64_decode( $_GET['user'] );
        $token_from_url = sanitize_text_field( $_GET['token'] );

        $user = get_user_by( 'id', $user_id );

        if ( $user ) {
            $stored_token = get_user_meta( $user_id, 'email_verification_token', true );

            // Check if the token matches and is not empty
            if ( $stored_token && $token_from_url === $stored_token ) {
                
                // 1. Set the user as verified
                update_user_meta( $user_id, 'is_email_verified', '1' );
                
                // 2. Remove the token so it can't be used again
                delete_user_meta( $user_id, 'email_verification_token' );
                
                // 3. Log the user in immediately
                wp_set_current_user( $user_id, $user->user_login );
                wp_set_auth_cookie( $user_id, true );

                // 4. Redirect to the account completion page
                error_log( "[VERIFY] User {$user_id} verified — redirect to account-completion disabled.\n", 3, CJSYNC . '/wccj_error.log' );
                // wp_safe_redirect( home_url( '/account-completion' ) );
                // exit;
                
            } else {
                error_log("my account : step four\n", 3, CJSYNC . '/wccj_error.log');
                // Invalid or expired token
                return '<div class="verification-error" style="margin: 100px auto; text-align:center;">
                            <h1>Invalid Link</h1>
                            <p>This verification link is invalid or has already been used.</p>
                            <a href="/my-account" class="btn-black">Go to Login</a>
                        </div>';
            }
        }
    }
}

//add_action( 'template_redirect', 'account_registration_verify' );


function anythingsupplies_completion_shortcode() {
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/my-account' ) );
        exit;
    }
    // Form processing and redirects moved to functions.php (handle_profile_completion_redirect)

    $user_id = get_current_user_id();
    $user = get_userdata($user_id);

    ob_start(); ?>
    <div class="completion-container">
    
    <div class="stepper-wrapper">
        <ul class="step-progress">
            <li class="step-item active" id="tab-1">
                <div class="step-counter">1</div>
                <div class="step-name">Account Type</div>
            </li>

            <li class="step-item " id="tab-2">
                <div class="step-counter">2</div>
                <div class="step-name">Profile</div>
            </li>

            <li class="step-item" id="tab-3">
                <div class="step-counter">3</div>
                <div class="step-name">Photo</div>
            </li>
        </ul>
    </div>  

        <form method="post" enctype="multipart/form-data" id="completion-form">
            <input type="hidden" name="action" value="complete_profile_submission">
            <?php wp_nonce_field( 'complete_profile_nonce', 'profile_nonce' ); ?>


            <div class="tab-content active" id="step-1">
                <div class="step-welcome-header" style="text-align: center; margin-bottom: 0px;">
        <h2 style="color: #333; margin-bottom: 10px;">Welcome to Anything Supplies!</h2>
        <p style="color: #666; font-size: 15px;">To complete your account, please follow the steps below starting with your account type.</p>
    </div>  
             
                <div class="account-type-grid">
                    <label class="type-card">
                        <img src="<?php echo get_stylesheet_directory_uri (). '/images/icons/partner.jpg' ; ?>" alt="Partner">
                        <input type="checkbox" name="account_types[]" class="toggle-check" value="partner">
                        
                    </label>
                    <label class="type-card">
                        <img src="<?php echo get_stylesheet_directory_uri (). '/images/icons/seller.jpg' ; ?>" alt="Seller">
                        <input type="checkbox" name="account_types[]" class="toggle-check" value="seller">
                        
                    </label> 
                    <label class="type-card">
                        <img src="<?php echo get_stylesheet_directory_uri (). '/images/icons/shopper.jpg' ; ?>" alt="Partner">
                        <input type="checkbox" name="account_types[]" class="toggle-check" value="customer">
                        
                    </label>
                </div>
                <div class="btn-group">
                   <p class="action-type-status"></p> <button type="button" class="next-btn" onclick="nextStep(2)">Next: Profile</button>
                </div>
            </div>

            <div class="tab-content " id="step-2">

                <h4>Profile Information</h4>
                <p>Complete your profile by providing the following information.</p>
                <div class="input-grid">
                    <div class="input-label"><label>Email</label></div>
                    <div class="input-group"><input type="text" name="email" placeholder="Email *" value="<?php echo esc_attr( $user->user_email ); ?>" required readonly style="background-color: #f0f0f0;"></div>
                </div>

                <div class="input-grid">
                    <div class="input-label"><label>First Name</label></div>
                    <div class="input-group"><input type="text" name="first_name" placeholder="First Name *" value="<?php echo esc_attr( $user->first_name ); ?>" required></div>
                </div>

                <div class="input-grid">
                    <div class="input-label"><label>Last Name</label></div>
                    <div class="input-group"><input type="text" name="last_name" placeholder="Last Name *" value="<?php echo esc_attr( $user->last_name ); ?>" required></div>
                </div>

                <div class="input-grid completion-password-wrapper">
                    <div class="input-label"><label>Password</label></div>
                    <div class="input-group completion-right-wrapper">
                        <input type="password" id="main_password" name="password" placeholder="New Password *" required minlength="8" onkeyup="checkPasswordStrength();">
                        <div id="password-meter-bar"></div> 
                        <small id="password-text">Strength: Too Short</small>
                    </div>
                </div> 

                <div class="input-grid">
                    <div class="input-label"><label>Confirm Password</label></div>
                    <div class="input-group completion-right-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password *" required onkeyup="validatePasswordMatch();" >
                        <small id="match-text"></small>
                    </div>
                </div>
                <div class="input-grid">
                    <div class="input-label"><label>Contact Number</label></div>
                    <div class="input-group">
                        <input type="tel" name="contact_number" placeholder="Contact Number" value="<?php echo esc_attr( get_user_meta( $user->ID, 'contact_number', true ) ); ?>">
                    </div>
                </div>
                <span style="font-size:14px; color:#cc0001; width:100%; text-align:center;" class="profile-action-type-status"></span>
                <div class="btn-group">
                    <button type="button" class="prev-btn" onclick="nextStep(1)">Back</button>
                    <button type="button" class="next-btn" onclick="nextStep(3)">Next: Photo</button>
                </div>
            </div>

            <div class="tab-content" id="step-3">
                <h4>Profile Photo</h4>
                <p>Upload a photo for your **anythingsupplies.com** profile.</p>
                
                <div id="drop-area" class="upload-box">
                    <div class="upload-icon">📸</div>
                    <p>Drag & Drop your photo here or <span class="browse-link">browse</span></p>
                    <input type="file" name="profile_photo" id="profile_photo" accept="image/*" style="display:none;">
                    <div id="image-preview"></div>
                </div>

                <div class="btn-group">
                    <button type="button" class="prev-btn" onclick="nextStep(2)">Back</button>
                    <button type="submit" class="submit-btn">Complete Account</button>
                </div>
            </div>

        </form>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'complete_account_form', 'anythingsupplies_completion_shortcode' );


function account_verification_status_shortcode() {
    // 1. Set default attributes and merge with user attributes
    
   $user_id = absint(base64_decode($_GET['user']));
   $status = $_GET['status'];
    $user_data = get_userdata( $user_id );
    
    $atts = shortcode_atts(
        array(
            'type' => $_GET['status'], // Default to success
            'user' => $user_data->display_name, // Default username
        ),
        $atts,
        'verify_status'
    );
    print_r($user_data);
    // Sanitize the user input for security
    $type = sanitize_key( $atts['type'] );
    $username = esc_html( $atts['user'] );
    
    $output = '';

    // 2. Conditional logic based on the 'type' attribute
    if ( $type === 'success' ) {
        
        $output = '
            <div class="verification-success">
                <div style="display: flex; justify-content: center; flex-direction: column; align-items: center; margin-top: 40px;">
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <i style="font-size: 9rem; text-align: center;">🎉</i>
                        <h1>Welcome to Anything Supplies!</h1>
                    </div>

                    <div style="text-align: center; max-width: 500px;">
                        <p style="font-size: 16px;">
                            Great news! Your email address has been successfully verified, and your e-commerce account is now fully active.
                            You can now log in and start browsing our catalog!
                        </p>

                        <div style="display: flex; gap: 1.25rem; justify-content: center;">
                            <a href="/my-account" class="button" style="display: inline-block; font-size: 1.75rem; font-weight: 600; padding: 15px 42px; background-color: red; color: white; text-decoration: none;">
                                Log In
                            </a>
                            <a href="/shop" class="button" style="display: inline-block; font-size: 1.75rem; font-weight: 600; padding: 15px 28px; background-color: black; color: white; text-decoration: none;">
                                Shop Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        ';
        
    } elseif ( $type === 'failed' ) {
        
        $output = '
            <div class="verification-failed">
                <div style="display: flex; justify-content: center; flex-direction: column; align-items: center; margin-top: 40px;">
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <i style="font-size: 9rem; text-align: center;">⚠️</i>
                        <h1 style="text-align: center;">Account Verification Failed</h1>
                    </div>

                    <div style="text-align: center; max-width: 500px;">
                        <p style="font-size: 16px;">
                            The verification attempt for your e-commerce account was not successful. Either the verification link has expired,
                            the link has already been used.
                        </p>

                        <a href="/resend-verification" class="button" style="display: inline-block; font-size: 1.75rem; font-weight: 600; padding: 15px 28px; background-color: red; color: white; text-decoration: none;">
                            Resend Verification
                        </a>
                    </div>
                </div>
            </div>
        ';
        
    } else {
        // Fallback for an invalid type
        $output = '<p>Error:Invalid verification status type specified.</p>';
    }

    // 3. Return the content (DO NOT use 'echo')
    return $output;
}

// Register the shortcode with WordPress
add_shortcode( 'verify_status', 'account_verification_status_shortcode' );


function custom_logout_link_url( $logout_url, $redirect ) {
    // Replace 'my-secret-login' with your custom login file/page name.
    // If you used a plugin to hide wp-login, you might need to find its custom link.
    $custom_url = home_url( '/sals3-account/?action=logout' );
    
    // Add the nonce and optional redirect parameter to the new URL
    $logout_url = wp_nonce_url( $custom_url, 'log-out' );
    $redirect = home_url();
    if ( $redirect ) {
        $logout_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $logout_url );
    }
    
    return $logout_url;
}
add_filter( 'logout_url', 'custom_logout_link_url', 10, 2 );

function custom_logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
    // Check if a specific 'redirect_to' was already requested (e.g., in a menu link)
    if ( ! empty( $requested_redirect_to ) ) {
        return $requested_redirect_to;
    }
    
    // Set the default URL you want to redirect all users to after logout
    // Example: Redirects to the homepage
    wp_logout();
    return home_url('/my-account/'); 
    
    // Example: Redirects to a custom 'Thank You' page
    //return 'https://yourdomain.com/thank-you-for-shopping/'; 
    
    // Example: Redirects to the main shop page
    // return get_permalink( get_option( 'woocommerce_shop_page_id' ) );
}
add_filter( 'logout_redirect', 'custom_logout_redirect', 10, 3 );


function custom_sals3_logout_handler() {
    // 1. Define your target page slug and action parameter
    $target_page_slug = 'sals3-account';
    $target_action = 'logout';

    // 2. Check if we are on the correct page
    // Using is_page() with the slug is the most reliable method
    if ( is_page( $target_page_slug ) ) {
        
        // 3. Check if the 'action' query parameter is set to 'logout'
        // Use filter_input for secure retrieval of GET variables
        $action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        
        if ( $action === $target_action ) {
            
            // SECURITY CHECK: This is crucial for preventing CSRF attacks.
            // Check for the WordPress logout nonce to ensure the request is legitimate.
            // The link triggering this should use wp_nonce_url( $page_url, 'log-out' )
            if ( ! check_admin_referer( 'log-out' ) ) {
                // If the nonce is missing or invalid, fail securely and redirect back.
                wp_safe_redirect( home_url( $target_page_slug ) );
                exit;
            }
            
            // --- 4. EXECUTE LOGOUT ---
            wp_logout();
            
            // 5. Redirect the user immediately after logout to prevent infinite loops
            // We redirect them to the homepage, or a dedicated 'logged out' page.
            wp_safe_redirect( home_url() ); 
            exit; // Stop further script execution
        }
    }
}
// Hook the function early in the WordPress loading process
add_action( 'template_redirect', 'custom_sals3_logout_handler' );

/**
 * 1. Display the field on the Profile Edit screen
 */
add_action( 'show_user_profile', 'display_custom_verification_field' );
add_action( 'edit_user_profile', 'display_custom_verification_field' );

function display_custom_verification_field( $user ) {
    // Get current value
    $status = get_user_meta( $user->ID, 'is_email_verified', true );
    ?>
    <h3>Account Verification</h3>
    <table class="form-table">
        <tr>
            <th><label for="is_email_verified">Email Verified?</label></th>
            <td>
                <select name="is_email_verified" id="is_email_verified">
                    <option value="0" <?php selected( $status, '0' ); ?>>No (False)</option>
                    <option value="1" <?php selected( $status, '1' ); ?>>Yes (True)</option>
                </select>
                <p class="description">Manually toggle the user's email verification status.</p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * 2. Save the field when the profile is updated
 */
add_action( 'personal_options_update', 'save_custom_verification_field' );
add_action( 'edit_user_profile_update', 'save_custom_verification_field' );


function save_custom_verification_field( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }
    update_user_meta( $user_id, 'is_email_verified', $_POST['is_email_verified'] );
}

function display_pdf_invoice_button( $order_id ) {
	$download_url = add_query_arg( array(
	'generate_pdf_invoice' => '1',
	'order_id' => $order_id,
	'nonce' => wp_create_nonce( 'download_invoice_' . $order_id )
	), wc_get_endpoint_url( 'view-order', $order_id, wc_get_page_permalink( 'myaccount' ) ) );

		echo '<a href="' . esc_url( $download_url ) . '" class="btn-darkgray" ">Download Invoice</a>';
		
}


function custom_user_photo_edit_shortcode() {
    if ( ! is_user_logged_in() ) return 'Please log in.';

    $current_user = wp_get_current_user();

    // Handle the Upload
    if ( isset($_POST['upload_photo_nonce']) && wp_verify_nonce($_POST['upload_photo_nonce'], 'user_photo_upload') ) {
        if ( ! empty($_FILES['user_photo']['name']) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            $attachment_id = media_handle_upload( 'user_photo', 0 );

            if ( is_wp_error( $attachment_id ) ) {
                $error = 'Error uploading image: ' . $attachment_id->get_error_message();
            } else {
                $image_url = wp_get_attachment_url( $attachment_id );
                update_user_meta( $current_user->ID, 'user_custom_photo', $image_url );
                $success = 'Photo updated successfully!';
            }
        }
    }

    $current_photo = get_user_meta( $current_user->ID, 'user_custom_photo', true );
    $img_src       = $current_photo ? $current_photo : 'https://via.placeholder.com/150';

    ob_start(); ?>

    <!-- ── PREVIEW MODAL ── -->
    <div id="photo-preview-modal" style="
        display:none; position:fixed; inset:0; z-index:99999;
        background:rgba(0,0,0,0.6); justify-content:center; align-items:center;">

        <div style="
            background:#fff; border-radius:12px; padding:32px 28px;
            max-width:420px; width:90%; text-align:center;
            box-shadow:0 8px 32px rgba(0,0,0,0.25); position:relative;">

            <h3 style="margin:0 0 6px; font-size:1.2em;">Preview Profile Photo</h3>
            <p style="margin:0 0 20px; color:#666; font-size:0.9em;">
                This is how your profile picture will look.
            </p>

            <!-- Circular preview -->
            <img id="modal-preview-img" src="" alt="Preview" style="
                width:150px; height:150px; border-radius:50%;
                object-fit:cover; border:3px solid #eee;
                display:block; margin:0 auto 24px;">

            <div style="display:flex; gap:12px; justify-content:center;">
                <button type="button" id="modal-confirm-btn" class="btn-orange" style="padding:10px 24px; cursor:pointer;">
                    Upload Photo
                </button>
                <button type="button" id="modal-cancel-btn" style="
                    padding:10px 24px; border:1px solid #ccc;
                    background:#fff; cursor:pointer;">
                    Cancel
                </button>
            </div>

            <p id="modal-upload-status" style="margin-top:14px; font-size:0.9em; min-height:1.2em;"></p>
        </div>
    </div>

    <!-- ── MAIN WIDGET ── -->
    <div class="edit-account-photo-container">
        <div id="photo-preview-wrapper">
            <img id="user-avatar-preview-big" src="<?php echo esc_url( $img_src ); ?>"
                 style="width:100px; height: 100px; border-radius:50%; object-fit:cover;">
        </div>

        <div class="custom-upload-wrapper" style="margin: auto 0;">
            <input type="file" id="ajax_photo_input" accept="image/*" style="display:none;">
            <button type="button" class="action-button" onclick="document.getElementById('ajax_photo_input').click();">
                <i class="icon-upload2" style="padding-right: 6px;"></i>
                Change Photo
            </button>
            <div style="font-size:14px;">
                Up to 1 MB • JPEG, PNG, JPG
            </div>
        </div>
        <p id="upload-status" style="margin-top:10px; font-size:0.9em;"></p>
    </div>

    <script>
    jQuery(document).ready(function($) {

        var pendingFile = null; // holds the chosen File object until confirmed

        // ── Step 1: file chosen → show modal preview ──────────────────────
        $('#ajax_photo_input').on('change', function() {
            var file = this.files[0];
            if (!file) return;

            // Basic client-side validation before showing the preview
            var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                $('#upload-status').text('Only JPEG and PNG files are allowed.').css('color','red');
                $(this).val('');
                return;
            }
            if (file.size > 1 * 1024 * 1024) {
                $('#upload-status').text('File size must be 1 MB or less.').css('color','red');
                $(this).val('');
                return;
            }

            pendingFile = file;

            // Read file locally — no upload yet
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#modal-preview-img').attr('src', e.target.result);
                $('#modal-upload-status').text('').css('color','#666');
                $('#modal-confirm-btn').prop('disabled', false).text('Upload Photo');
                $('#photo-preview-modal').css('display','flex');
            };
            reader.readAsDataURL(file);

            // Reset input so the same file can be re-selected if needed
            $(this).val('');
        });

        // ── Step 2a: user cancels ──────────────────────────────────────────
        $('#modal-cancel-btn').on('click', function() {
            pendingFile = null;
            $('#photo-preview-modal').css('display','none');
        });

        // Close modal when clicking the dark backdrop
        $('#photo-preview-modal').on('click', function(e) {
            if ($(e.target).is('#photo-preview-modal')) {
                pendingFile = null;
                $(this).css('display','none');
            }
        });

        // ── Step 2b: user confirms → upload ───────────────────────────────
        $('#modal-confirm-btn').on('click', function() {
            if (!pendingFile) return;

            var form_data = new FormData();
            form_data.append('file',   pendingFile);
            form_data.append('action', 'upload_user_photo_ajax');
            form_data.append('nonce',  '<?php echo wp_create_nonce("hp_photo_nonce"); ?>');

            $('#modal-upload-status').text('Uploading…').css('color','#666');
            $('#modal-confirm-btn').prop('disabled', true).text('Uploading…');

            $.ajax({
                url:         '<?php echo admin_url("admin-ajax.php"); ?>',
                type:        'POST',
                data:        form_data,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        // Update both avatar instances on the page
                        $('#user-avatar-preview').attr('src',     response.data.url);
                        $('#user-avatar-preview-big').attr('src', response.data.url);

                        $('#modal-upload-status').text('Profile photo updated!').css('color','green');
                        $('#upload-status').text('').css('color','green');

                        // Auto-close the modal after a short delay
                        setTimeout(function() {
                            $('#photo-preview-modal').css('display','none');
                            pendingFile = null;
                        }, 1200);
                    } else {
                        $('#modal-upload-status').text(response.data).css('color','red');
                        $('#modal-confirm-btn').prop('disabled', false).text('Upload Photo');
                    }
                },
                error: function() {
                    $('#modal-upload-status').text('Upload failed. Please try again.').css('color','red');
                    $('#modal-confirm-btn').prop('disabled', false).text('Upload Photo');
                }
            });
        });

    });
    </script>

<?php
return ob_get_clean();
}
add_shortcode('edit_account_photo', 'custom_user_photo_edit_shortcode');

add_action('wp_ajax_upload_user_photo_ajax', 'handle_user_photo_upload_ajax');

function handle_user_photo_upload_ajax() {
    // Security check
    check_ajax_referer('hp_photo_nonce', 'nonce');

    if ( ! is_user_logged_in() ) {
        wp_send_json_error('You must be logged in.');
    }

    if ( empty($_FILES['file']) ) {
        wp_send_json_error('No file uploaded.');
    }

    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );

    // Upload to Media Library
    $attachment_id = media_handle_upload( 'file', 0 );

    if ( is_wp_error( $attachment_id ) ) {
        wp_send_json_error( $attachment_id->get_error_message() );
    } else {
        $image_url = wp_get_attachment_url( $attachment_id );
        update_user_meta( get_current_user_id(), 'user_custom_photo', $image_url );
        
        wp_send_json_success(['url' => $image_url]);
    }
}

// Save logic remains the same
add_action( 'woocommerce_save_account_details', function( $user_id ) {
    if ( isset( $_POST['contact_number'] ) ) {
        update_user_meta( $user_id, 'contact_number', sanitize_text_field( $_POST['contact_number'] ) );
    }
}, 12, 1 );

// Add Contact Number to the standard "Contact Info" section
add_filter( 'user_contactmethods', 'add_contact_method_under_email', 10, 1 );

function add_contact_method_under_email( $contactmethods ) {
    // This adds the field to the Contact Info section in the admin profile
    $contactmethods['contact_number'] = __( 'Contact Number', 'woocommerce' );
    return $contactmethods;
}

add_action( 'woocommerce_save_account_details', 'save_custom_contact_number_field', 12, 1 );

function save_custom_contact_number_field( $user_id ) {
    // Check if the field was sent in the POST request
    if ( isset( $_POST['contact_number'] ) ) {
        // Sanitize the input to prevent security issues and save
        update_user_meta( $user_id, 'contact_number', sanitize_text_field( $_POST['contact_number'] ) );
    }
}

add_action( 'template_redirect', 'process_password_change' );

function process_password_change() {
    // Only run if the form was submitted
    if ( ! isset( $_POST['submit_security'] ) ) return;

    // 1. Verify Nonce for Security
    if ( ! wp_verify_nonce( $_POST['security_nonce'], 'update_security_action' ) ) {
        wc_add_notice( 'Security check failed. Please try again.', 'error' );
        return;
    }

    $user = wp_get_current_user();
    $current_pass = $_POST['current_password'];
    $new_pass     = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // 2. Validation
    if ( ! wp_check_password( $current_pass, $user->user_pass, $user->ID ) ) {
        wc_add_notice( 'Your current password is incorrect.', 'error' );
        return;
    }

    if ( $new_pass !== $confirm_pass ) {
        wc_add_notice( 'New passwords do not match.', 'error' );
        return;
    }

    if ( strlen( $new_pass ) < 8 ) {
        wc_add_notice( 'Password must be at least 8 characters long.', 'error' );
        return;
    }

    // 3. Success - Update Password
    wp_set_password( $new_pass, $user->ID );
    
    // Log user back in (wp_set_password logs them out)
    $creds = array(
        'user_login'    => $user->user_login,
        'user_password' => $new_pass,
        'remember'      => true
    );
    wp_signon( $creds, false );

    wc_add_notice( 'Password updated successfully!', 'success' );
}

add_filter( 'woocommerce_form_field', 'custom_phone_field_class', 10, 4 );

function custom_phone_field_class( $field, $key, $args, $value ) {
    // 1. Logic for validation classes
    $isemail = ($args['type'] === 'email') ? ' validate-required validate-email ' : '';

    // 2. Handle Country vs State options
    if ( $key === 'billing_country' || $key === 'shipping_country' ) {
        $args['type'] = 'select';
        $args['options'] = WC()->countries->get_allowed_countries();
    } 
    elseif ( $key === 'billing_state' || $key === 'shipping_state' ) {
        $args['type'] = 'select';
        
        // Get the current country to load relevant states
        // We check the posted data, then the user meta, then the base shop country
        $current_cc = WC()->checkout->get_value('billing_country') ? WC()->checkout->get_value('billing_country') : WC()->countries->get_base_country();
        $args['options'] = WC()->countries->get_states( $current_cc );
        
        // If the country has no states, default back to a text input
        if ( empty( $args['options'] ) ) {
            $args['type'] = 'text';
        }
    }

    // 3. Start building the HTML
    $field = '<div class="edit-form-row '.$isemail.'" id="'.esc_attr($args['id']).'_field" >
        <label for="'.esc_attr($args['id']).'" class="'.(isset($args['class'][1]) ? esc_attr($args['class'][1]) : '').'">'.esc_html($args['label']).'<span class="required" aria-hidden="true">*</span></label>
        <div>';

    if ( $args['type'] === 'select' && !empty($args['options']) ) {
        $field .= '<select name="'.esc_attr($args['id']).'" id="'.esc_attr($args['id']).'" class="input-text">';
        foreach ( $args['options'] as $option_key => $option_value ) {
            $selected = selected( $value, $option_key, false );
            $field .= '<option value="' . esc_attr( $option_key ) . '"' . $selected . '>' . esc_html( $option_value ) . '</option>';
        }
        $field .= '</select>';
    } else {
        // Fallback for text inputs (and states for countries that don't have defined regions)
        $field .= '<input type="'.esc_attr($args['type']).'" class="input-text" name="'.esc_attr($args['id']) .'" id="'.esc_attr($args['id']).'" value="'.esc_attr($value).'" autocomplete="'.esc_attr($args['type']).'">';
    }

    $field .= '</div></div>'; 
    
    return $field;
}


add_shortcode('recent_orders_priority', 'display_priority_orders_shortcode');

function display_priority_orders_shortcode() {
    // 1. Fetch a buffer of recent orders
   $current_user_id = get_current_user_id();

    $args = array(
        'customer_id' => $current_user_id, // Filter by logged-in user
        'limit'       => 4, 
        'orderby'     => 'date',
        'order'       => 'DESC',
    );
    $orders = wc_get_orders($args);

   

    if (empty($orders)) {
        return '<p>No orders have been placed yet.</p>'; 
    }

    // 2. Sort: 'pending' orders move to the top
    usort($orders, function($a, $b) {
        $status_a = $a->get_status();
        $status_b = $b->get_status();

        if ($status_a === 'pending' && $status_b !== 'pending') return -1;
        if ($status_a !== 'pending' && $status_b === 'pending') return 1;
        return 0;
    });

    // 3. Slice to get only the top 4
    $top_three = array_slice($orders, 0, 4);

    // 4. Start building the output
    ob_start();
    ?>
    <div class="card-content">
    <div class="priority-orders-list">
        <ul style="list-style: none; padding: 0;"> 
            <?php foreach ($top_three as $order) : 
                $status = $order->get_status();
                $is_pending = ($status === 'pending');
                ?>
                <li style="font-size:14px; margin-bottom: 3px; padding: 4px; border-bottom: 1px solid #ddd; <?php echo $is_pending ? 'background-color: #fff9e6; border-left: 4px solid #ffba00;' : ''; ?>">
                    <span>Order #<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>"><?php echo $order->get_id(); ?></a></span> | 
                    <span><?php echo ucfirst($status); ?></span><br>
                    <span>Total: <?php echo $order->get_formatted_order_total(); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card-foot"><a class="btn-orange" href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>">All Orders</a></div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('to_receive_orders', 'display_to_receive_orders_shortcode');

function display_to_receive_orders_shortcode() {
    $current_user_id = get_current_user_id();
    
    if ( $current_user_id === 0 ) {
        return '<div class="woocommerce-info">Please log in to view your orders.</div>';
    }

    // 1. Fetch orders with status 'to-receive' or 'pending'
    $args = array(
        'customer_id' => $current_user_id,
        'status'      => array('to-receive', 'processing'), 
        'limit'       => 10, 
        'orderby'     => 'date',
        'order'       => 'DESC',
    );
    $orders = wc_get_orders($args);

    if ( empty($orders) ) {
        return '<p>No orders to receive or processing at this moment.</p>';
    }

    // 2. Sort: Keep 'pending' at the top
    usort($orders, function($a, $b) {
        $status_a = $a->get_status();
        $status_b = $b->get_status();
        if ($status_a === 'pending' && $status_b !== 'pending') return -1;
        if ($status_a !== 'pending' && $status_b === 'pending') return 1;
        return 0;
    });

    $top_three = array_slice($orders, 0, 3);

    ob_start();
    ?>
    <div class="card-content">
    <div class="to-receive-list">
        <ul style="list-style: none; padding: 0; margin: 0;">
            <?php foreach ($top_three as $order) : 
                $status = $order->get_status();
                $items = $order->get_items();
                ?>
                <li>
                     <div class="order-products">
                        <?php foreach ( $items as $item ) : 
                            $product = $item->get_product();
                            // Fallback if product no longer exists
                            $thumbnail = $product ? $product->get_image(array(50, 50)) : '<div style="width:50px;height:50px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;font-size:10px;color:#ccc;">N/A</div>';
                            $full_name = $item->get_name();
                            $product_link = $product ? get_permalink($product->get_id()) : '#';
                            $display_name = (strlen($full_name) > 75) ? substr($full_name, 0, 75) . '...' : $full_name;
                            ?>
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                                <div class="order-thumb-wrapper" style="width: 50px; height: 50px; flex-shrink: 0; overflow: hidden; border-radius: 6px; border: 1px solid #eee;">
                                    <?php echo $thumbnail; ?>
                                </div>
                                <div style="font-size: 1.2rem; color: #181818;">
                                    <span style="font-weight: 500; color: #181818;">#<?php echo $order->get_id(); ?></span>
                                    <span><a target="_blank" href="<?php echo esc_url($product_link); ?>"><?php echo $display_name; ?></a></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    </div>
    <div class="card-foot"><a class="btn-orange" href="<?php echo esc_url( wc_get_endpoint_url( 'my-invoice' ) ); ?>">To Receive</a></div>
    <style>
        /* Force 50x50 dimensions on the WooCommerce generated img tag */
        .order-thumb-wrapper img { 
            width: 50px !important; 
            height: 50px !important; 
            object-fit: cover; 
            display: block; 
        }
    </style>
    <?php
    return ob_get_clean();
}