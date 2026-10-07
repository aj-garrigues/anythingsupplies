<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="my-account-header">
	<h2>Payments</h2>
  <div class="my-account-header-tags"></div>
</div>
<div class="wp-tab-container inquries-section">
  
  <input type="radio" name="wp-tabs" id="tab-inquiries" class="wp-tab-input" checked>
  <label for="tab-inquiries" class="wp-tab-label">All Invoice</label>
  <div id="content-inquiries" class="wp-tab-content">
    <!-- all order content -->
     <?php include_once( get_stylesheet_directory() . '/woocommerce/myaccount/order-content/all-orders.php' ); ?>
  </div>

  <input type="radio" name="wp-tabs" id="tab-refund-aftersales" class="wp-tab-input">
  <label for="tab-refund-aftersales" class="wp-tab-label">Paid</label>
  <div id="content-refund-aftersales" class="wp-tab-content">
    <!-- all order content -->
     <?php include_once( get_stylesheet_directory() . '/woocommerce/myaccount/order-content/refund-aftersales.php' ); ?>
  </div>

  <input type="radio" name="wp-tabs" id="tab-rfqs" class="wp-tab-input">
  <label for="tab-rfqs" class="wp-tab-label">Pending</label>
  <div id="content-rfqs" class="wp-tab-content">
      <!-- refund and after sales -->
     <?php include_once( get_stylesheet_directory() . '/woocommerce/myaccount/order-content/reviews.php' ); ?>
  </div>

  <input type="radio" name="wp-tabs" id="tab-invoice" class="wp-tab-input">
  <label for="tab-invoice" class="wp-tab-label">Cancelled</label>
  <div id="content-invoice" class="wp-tab-content">
      <!-- Coupons content -->
     <?php include_once( get_stylesheet_directory() . '/woocommerce/myaccount/order-content/coupons.php' ); ?>
  </div>

</div>

</div>

<div id="popup" class="popup">
    <button id="closeBtn" class="close-icon" aria-label="Close">&times;</button>
    
<div class="popup-content">
        <h2>Product Details</h2>
        <hr>
        <p>You are viewing details for Product ID: <strong><span id="display-id">---</span></strong></p>
    </div>
</div>

<script>
const popup = document.getElementById('popup');
const closeBtn = document.getElementById('closeBtn');
const displayId = document.getElementById('display-id');
const triggers = document.querySelectorAll('.trigger-btn');

// Handle Multiple Buttons
triggers.forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-product-id');
        
        // Update the content
        displayId.innerText = id;
        
        // Open the popup (it will stay open)
        popup.classList.add('show');
    });
});

// The ONLY way to close the popup
closeBtn.addEventListener('click', () => {
    popup.classList.remove('show');
});

</script>