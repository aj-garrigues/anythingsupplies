<?php
define('CJSYNC', ABSPATH . 'salsdata/');
 
add_action( 'wp_enqueue_scripts', 'martfury_child_enqueue_scripts', 20 );
function martfury_child_enqueue_scripts() {
	wp_enqueue_style( 'martfury-child-style', get_stylesheet_uri() );
	if ( is_rtl() ) {
		wp_enqueue_style( 'martfury-rtl', get_template_directory_uri() . '/rtl.css', array(), '20180105' );
	}
}

function enqueue_font_awesome() {
    // Replace the URL below with the latest version or your specific Kit URL
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_font_awesome' );

// Account completion form processing and redirect (formerly in functions_myaccount.php)
add_action( 'template_redirect', 'handle_profile_completion_redirect' );
function handle_profile_completion_redirect() {
    if ( ! is_user_logged_in() && is_page( 'account-completion' ) ) {
        wp_safe_redirect( home_url( '/my-account' ) );
        exit;
    }

    $current_user = wp_get_current_user();

    // Bypass for administrators and agents
    $bypass_roles = array( 'administrator', 'sls_agent', 'agent' );
    if ( array_intersect( $bypass_roles, (array) $current_user->roles ) ) {
        return;
    }

    $user_id = $current_user->ID;

    // Redirect already-completed users away from the completion page
    if ( is_page( 'account-completion' ) ) { // use your page slug
        $is_completed = get_user_meta( $user_id, 'profile_completed', true );
        if ( $is_completed === '1' ) {
            wp_safe_redirect( home_url( '/my-account' ) );
            exit;
        }
    }

    // Handle form submission redirect
    if ( isset( $_POST['action'] ) && $_POST['action'] === 'complete_profile_submission' ) {
        if ( isset( $_POST['profile_nonce'] ) && wp_verify_nonce( $_POST['profile_nonce'], 'complete_profile_nonce' ) ) {
            
            $user_id = get_current_user_id();

            // 1. Update Basic Profile Info
            $userdata = array(
                'ID'         => $user_id,
                'first_name' => sanitize_text_field( $_POST['first_name'] ),
                'last_name'  => sanitize_text_field( $_POST['last_name'] ),
            );
 
            // 2. Update Password and Clear Temporary Status
            if ( ! empty( $_POST['password'] ) ) {
                $userdata['user_pass'] = $_POST['password'];
                
                // 1. Disable the password change notification email
                add_filter( 'send_password_change_email', '__return_false' );
                
                // 2. Clear temporary status
                delete_user_meta( $user_id, 'default_password_nag' );
                delete_user_meta( $user_id, 'user_pass_res' );
            }

            wp_update_user( $userdata );

            // 3. Handle Multiple Roles (Account Types)
            // Note: In WP, 'user_meta' is often better for multiple custom roles than actual WP Roles
            if ( isset( $_POST['account_types'] ) && is_array( $_POST['account_types'] ) ) {
                $sanitized_types = array_map( 'sanitize_text_field', $_POST['account_types'] );
                update_user_meta( $user_id, 'account_types', $sanitized_types );
            }

            // 4. Update Contact Number
            if ( isset( $_POST['contact_number'] ) ) {
                update_user_meta( $user_id, 'contact_number', sanitize_text_field( $_POST['contact_number'] ) );
            }

            // 5. Handle Photo Upload (user_custom_photo)
            if ( ! empty( $_FILES['profile_photo']['name'] ) ) {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/media.php' );

                // Handle the upload
                $attachment_id = media_handle_upload( 'profile_photo', 0 ); // 0 means not attached to a post

                if ( ! is_wp_error( $attachment_id ) ) {
                    // Save the Attachment ID or the URL to user meta
                    $photo_url = wp_get_attachment_url( $attachment_id );
                    update_user_meta( $user_id, 'user_custom_photo', $photo_url );
                }
            }

            update_user_meta( $user_id, 'profile_completed', '1' );
            wp_safe_redirect( home_url( '/my-account' ) );
            exit;
        }
    }
}

function my_account_phone_validation_scripts() {

    if ( is_account_page() || is_page('account-completion')) {
        
        wp_enqueue_style(
            'intl-tel-input-css',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@26.8.1/build/css/intlTelInput.css'
        );

        wp_enqueue_script(
            'intl-tel-input-js',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@26.8.1/build/js/intlTelInput.min.js',
            array(),
            null,
            true
        );

        if(is_account_page()) {
            wp_enqueue_script(
                'account-phone-validation',
                get_stylesheet_directory_uri() . '/js/account-phone-validation.js',
                array('intl-tel-input-js'),
                null,
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'my_account_phone_validation_scripts');

//
function load_custom_styles() {
    $custom_styles = [
        'kurl-style' => [ 'path' => '/sals3/assets/css/kurl.css', 'ver' => '1.0.12'],
    ];

    foreach($custom_styles as $handle => $style) {
        wp_enqueue_style(
            $handle,
            get_stylesheet_directory_uri() . $style['path'],
            array(),
            $style['ver']
        );
    }
}
add_action( 'wp_enqueue_scripts', 'load_custom_styles' );

function preload_lcp_bg_image() {
    echo '<link rel="preload" as="image" href="https://anythingsupplies.com/wp-content/themes/martfury-child/images/bg/product-bundel-Analyst.webp" fetchpriority="high">';
}
add_action('wp_head', 'preload_lcp_bg_image', 1);

// Show a completion notice on the My Account page for unfinished profiles.
// Called directly from template-myaccount.php before the .my-account wrapper.
function sals3_profile_completion_notice() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $current_user = wp_get_current_user();
    $bypass_roles = array( 'administrator', 'sls_agent', 'agent' );
    if ( array_intersect( $bypass_roles, (array) $current_user->roles ) ) {
        return;
    }

    $is_completed = get_user_meta( $current_user->ID, 'profile_completed', true );
    if ( $is_completed === '1' ) {
        return;
    }
    ?>
    <div style="background:#fff8e1;border-left:4px solid #f5a623;padding:16px 20px;margin-bottom:24px;border-radius:3px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <span style="font-size:22px;">⚠️</span>
        <div style="flex:1;min-width:200px;">
            <strong style="display:block;margin-bottom:4px;">Complete your registration</strong>
            <span style="color:#555;font-size:14px;">Your account setup is not yet finished. Please complete your profile to unlock all features.</span>
        </div>
        <a href="<?php echo esc_url( home_url( '/account-completion/' ) ); ?>" style="background:#f5a623;color:#fff;padding:10px 20px;border-radius:3px;text-decoration:none;font-weight:600;white-space:nowrap;">Complete Now</a>
    </div>
    <?php
}

include_once 'sals3/sals3.php';
