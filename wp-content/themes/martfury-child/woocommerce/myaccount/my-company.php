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
<div class="my-account-header">
	<h2>Company Information</h2>
  <div class="my-account-header-tags">Manage your company information and details</div>
</div>

<div class="edit-account-fields">
    <?php
    // Ensure scripts are loaded for the strength meter
    wp_enqueue_script('password-strength-meter');
    ?>
    <form id="password-change-form" method="post" action="">
        <?php wp_nonce_field('update_security_action', 'security_nonce'); ?>
        

	<div class="edit-form-row"><label class="form-label">Company Name</label><div><input type="text" name="co_name" class="form-control"></div></div>
	<div class="edit-form-row"><label class="form-label">Person to Contact</label><div><input type="text" name="co_contact_person" class="form-control"></div></div>
	<div class="edit-form-row"><label class="form-label">Company Email</label><div><input type="email" name="co_email" class="form-control"></div></div>
	<div class="edit-form-row"><label class="form-label">Contact Number</label><div><input type="text" name="co_phone" class="form-control"></div></div>
	<div class="edit-form-row"><label class="form-label">Address Street</label><div><input type="text" name="co_street" class="form-control"></div></div>
	<div class="edit-form-row"><label class="form-label">City</label><div><input type="text" name="co_city" class="form-control"></div></div>
	<div class="edit-form-row"><label class="form-label">State</label><div><input type="text" name="co_state" class="form-control"></div></div>
	<div class="edit-form-row"><label class="form-label">Country</label><div><input type="text" name="co_country" class="form-control"></div></div>
	<div class="edit-form-row"><label class="form-label">Zip</label><div><input type="text" name="co_zip" class="form-control"></div></div>
        
	<div class=" edit-form-row ">
            <label for="security_question"></label>
            <div><input type="submit" name="submit_security" class="btn-orange" value="Update Password"></div>
        </div>                            
							
    </form>

    
</div>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
