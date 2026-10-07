<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hook - woocommerce_before_edit_account_form.
 *
 * @since 2.6.0
 */

do_action( 'woocommerce_before_edit_account_form' );
?>
<style>
	.text-label {
		font-size: 14px;
		font-weight: 600;
		color: #374151;
	}
	.text-field {
		width: 100%;
		padding: 12px 16px !important;
		border: 1.5px solid #D1D5DB !important;
		border-radius: 4px;
		font-size: 15px;
		font-family: inherit;
		transition: all 0.2s;
	}
	.tel {
		padding-left: 48px !important;
	}
	.input-group {
		display: flex;
		flex-direction: column;
		gap: 8px;
		width: 100%;
	}

	.disabled {
		background-color: #e9e9e9 !important;
		color: #a0a0a0 !important;
		cursor: not-allowed !important;
	}

	.group-wrapper {
		padding: 8px 16px;
		background-color: #fff;
		border: 1.5px solid #D1D5DB !important;
		border-radius: 10px;
		margin-bottom: 14px;
	}
	.group-header {
		color: #374151;
		font-weight: 700;
	}
	.action-button {
		padding: 8px 14px;
		font-size: 14px;
		border-radius: 8px;
		border: 1.5px solid #D1D5DB !important;
		font-weight: 500;
		background-color: #fff;
		color: #000;
	}
	.submit-button {
		background-color: #00719a;
		color: #fff;
	}
</style>

	<div class="edit-account-fields">

	<div class="edit-photo-section">
		<?php echo do_shortcode('[edit_account_photo]'); ?>
	</div>

	<div class="edit-account-inner">
<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >

	<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

	<div class="group-wrapper">
		<h2 class="group-header">ACCOUNT</h2>
		<div style="display: flex; gap: 12px; margin-bottom: 16px;">
			<div class="input-group" >
				<label for="account_username" class="text-label"><?php esc_html_e( 'Username', 'woocommerce' ); ?></label>
				<input type="text" class="text-field disabled" name="username" id="account_username" value="<?php echo esc_html($user->user_login); ?>" readonly style="width: 100%;"/>
			</div>
			<div class="input-group" >
				<label for="account_email" class="text-label"><?php esc_html_e( 'Email address', 'woocommerce' ); ?></label>
				<input type="email" class="text-field disabled" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" readonly style="width: 100%;"/>
			</div>
		</div>
	</div>

	<div class="group-wrapper">
		<h2 class="group-header">PERSONAL INFORMATION</h2>
		<div style="display: flex; gap: 12px; margin-bottom: 16px;">
			<div class="input-group">
				<label for="account_first_name" class="text-label"><?php esc_html_e( 'First name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true"></span></label>
				<input type="text" class="text-field" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" aria-required="true" />
			</div>
			
			<div class="input-group">
				<label for="account_last_name" class="text-label"><?php esc_html_e( 'Last name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true"></span></label>
				<input type="text" class="text-field" name="account_last_name" id="account_last_name" autocomplete="given-name" value="<?php echo esc_attr( $user->last_name ); ?>" aria-required="true" />
			</div>
		</div>

		<div style="display: flex; gap: 12px; margin-bottom: 16px; flex-direction: column;">
			<div class="input-group" >
				<label for="account_display_name" class="text-label"><?php esc_html_e( 'Display name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true"></span></label>
				<input type="text" class="text-field" name="account_display_name" id="account_display_name" autocomplete="given-name" value="<?php echo esc_attr( $user->display_name ); ?>" aria-required="true" />
			</div>
			
			<div class="input-group" >
				<label for="contact_number" class="text-label"><?php esc_html_e( 'Contact', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true"></span></label>
				<input type="tel" class="text-field tel" name="contact_number" id="contact_number" autocomplete="contact" value="<?php echo  get_user_meta( $user->ID, 'contact_number', true ); ?>" aria-required="true" />
			</div>
		</div>
	</div>
	

	<?php
		/**
		 * Hook where additional fields should be rendered.
		 *
		 * @since 8.7.0
		 */
		do_action( 'woocommerce_edit_account_form_fields' );
	?>
	<?php
		/**
		 * My Account edit account form.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_edit_account_form' );
	?>
	<div class=" edit-form-row ">
		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
		<div style="display: flex; justify-content: space-between;">
			<button type="submit" class="woocommerce-Button  action-button submit-button <?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>"><?php esc_html_e( 'Save changes', 'woocommerce' ); ?></button>
			<input type="hidden" name="action" value="save_account_details" />

			<div>
				<a href="/my-account/edit-security/">
					<button type="button" class="action-button">
						<i class="ion-android-lock" style="padding-right: 6px;"></i>
						Change Password
					</button>
				</a>
				<a href="/my-account/edit-address/">
					<button type="button" class="action-button">
						<i class="ion-ios-location" style="padding-right: 6px;"></i>
						Update Address
					</button>
				</a>	
			</div>
		</div>
		
	</div>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>
	</div>

	</div>




<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
