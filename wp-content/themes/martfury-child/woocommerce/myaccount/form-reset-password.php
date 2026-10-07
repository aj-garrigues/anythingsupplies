<?php
/**
 * Lost password reset form.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-reset-password.php.
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

do_action( 'woocommerce_before_reset_password_form' );
?>
<div class="container" style="margin-top: 40px; margin-bottom: 40px;">
	<div class="row">
		<div class="col-lg-1"></div>
		<div class="col-lg-6"></div>
		<div class="col-lg-4" style="background-color: #ffffff; padding: 30px; border-radius: 3px; margin-top: 30px; margin-bottom: 30px;">		

<form method="post" class="woocommerce-ResetPassword lost_reset_password">
	<h3><?php esc_html_e( 'Reset your password', 'woocommerce' ); ?></h3>
	<p><?php echo apply_filters( 'woocommerce_reset_password_message', esc_html__( 'Enter a new password below.', 'woocommerce' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="password_1" class="d-block f-strong f-16"><?php esc_html_e( 'New password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
		<input type="password" class="woocommerce-Input woocommerce-Input--text input-text  f-16" style="width:100%; padding:13px"  name="password_1" id="password_1" autocomplete="new-password" required aria-required="true" />
	</p>
	<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
		<label for="password_2" class="d-block f-strong f-16"><?php esc_html_e( 'Re-enter new password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
		<input type="password" class="woocommerce-Input woocommerce-Input--text input-text  f-16" style="width:100%; padding:13px" name="password_2" id="password_2" autocomplete="new-password" required aria-required="true" />
	</p>

	<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
	<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

	<div class="clear"></div>

	<?php do_action( 'woocommerce_resetpassword_form' ); ?>

	<p class="woocommerce-form-row form-row">
		<input type="hidden" name="wc_reset_password" value="true" />
		<button type="submit" class="btn-red border-none <?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Save', 'woocommerce' ); ?>"><?php esc_html_e( 'Save', 'woocommerce' ); ?></button>
</p>

	<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>

</form>
</div>
<div class="col-lg-1"></div>
</div></div>	
<?php
do_action( 'woocommerce_after_reset_password_form' );

