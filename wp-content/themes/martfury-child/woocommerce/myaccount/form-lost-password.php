<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>
<div class="container" style="margin-top: 40px; margin-bottom: 40px;">
	<div class="row">
		<div class="col-lg-1"></div>
		<div class="col-lg-7"></div>
		<div class="col-lg-4" style="background-color: #ffffff; padding: 30px; border-radius: 3px; margin-top: 30px; margin-bottom: 30px;">		
<form method="post" class="woocommerce-ResetPassword lost_reset_password">
	<div class="form-notification-info"><?php if($_GET['password-reset'] === 'sent') { echo '<p class="woocommerce-info">Check your email for the password reset link.</p>'; } 	?></div>
	<div class="form-notification-warning"></div>  
	<h2 class="woocommerce-column__title"><?php esc_html_e('Lost your password?', 'martfury'); ?></h2>
	<p class="reset-depscription"><?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Please enter your username or email address. You will receive a link to create a new password via email.', 'martfury' ) ); ?></p>
 
	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="user_login" class="d-block f-strong f-16"><?php esc_html_e( 'Username or email', 'martfury' ); ?></label>
		<input class="woocommerce-Input woocommerce-Input--text input-text f-16" style="width:100%; padding:13px" type="text" name="user_login" id="user_login" required aria-required="true" />
	</p>

	<div class="clear"></div>

	<?php do_action( 'woocommerce_lostpassword_form' ); ?>

	<p class="woocommerce-form-row form-row">
		<input type="hidden" name="wc_reset_password" value="true" />
		<button type="submit" class="btn-red border-none" value="<?php esc_attr_e( 'Reset password', 'martfury' ); ?>"><?php esc_html_e( 'Reset password', 'martfury' ); ?></button>
	</p>
	<div style="border-top: 1px solid #e0e0e0; margin-top: 20px; padding-top: 20px;">
		Back to login: <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="font-black has-icon"><i class="ion-log-in"></i> <?php esc_html_e( 'Login', 'martfury' ); ?></a>
	</div>
	<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

</form>
</div>
</div></div>
<?php
do_action( 'woocommerce_after_lost_password_form' );
