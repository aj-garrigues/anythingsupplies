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
<style>
.order-listing {
    border: 1px solid #e7e7e7;
    margin-bottom: 4px; border-top:2px solid #ff4c00; 
     transition: transform 0.2s ease;
}
.order-listing:hover{
   border-left:1px solid #ff9466;  
   border-right:1px solid #ff9466; 
    border-bottom:1px solid #ff9466; 
}
.order-listing:hover ~ .order-listing-footer {
  border:1px solid #ff9466; 
}

/* Header: Order info left, Status right */
.order-list-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f6fdff;
    padding: 15px;
    border-bottom: 1px solid #e7e7e7;
    font-weight: 400; color:#ff4c00;
}
.order-list-head .head-left{
   color: #262626; font-weight:500;
}

/* Product Item: Details left, 250px Image right */
.order-product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    transition: transform 0.2s ease;
}
.border-b{
    border-bottom:1px solid #eeeeee; 
}
.order-product-item:hover {
    transform: translateY(-3px); /* The hover effect you wanted */
}

.order-product-details {
    flex: 1;
    padding-left: 15px;
}

.order-product-title {
    margin: 0 0 5px 0;
    font-size: 1.4rem;
}

.order-product-image img {
    width: 60px;
    height: auto;
    display: block;
}
.order-product-pricing {
    font-size: 1.2rem;
    color: #555;
}
/* Mobile Responsive */
@media (max-width: 600px) {
    .order-product-item {
        flex-direction: column-reverse;
        text-align: center;
    }
    .order-product-details {
        padding-right: 0;
        margin-top: 15px;
    }
}

.order-listing-footer {
    padding: 15px;
    background-color: #fffefa;
    border: 1px solid #e7e7e7;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px; 
}

.footer-total {
    font-size: 1.2rem;
    color: #333;
}
.order-footer-items {
    display: flex;
    gap: 20px;
    align-items: center; width: 100%;
}
.order-footer-total{
    font-size:1.5rem;
    color: #1b1b1b;
    font-weight:500; text-align:right; padding:20px 0px
}
.order-footer-total .footer-row .amount{
    font-size: 2rem; color:#1b1b1b; font-weight:400; text-decoration: line-through;
}
.order-footer-total .grand-total .amount{
    font-size: 3rem; color:#ff4c00; font-weight:400; text-decoration: none;
}

.order-footer-total .discount .amount{
    font-size: 2rem; color:#1b1b1b; font-weight:400; text-decoration: none;
}
.order-footer-total .discount span:nth-child(2){
    font-size: 2rem; color:#1b1b1b; font-weight:400;
}
.order-footer-total-actions {
   width: 34%;
}
.order-footer-notes {
    font-size: 1.2rem;
    color: #666; width: 65%;
}
.order-footer-actions {
    align-items: right; text-align:right;
}


/* Button Styling */
.order-listing-footer .order-button-style.primary {
    background-color: #000;
    color: #fff;
}

.order-listing-footer .order-button-style.secondary {
    background-color: #eee;
    color: #333;
    border: 1px solid #ccc;
}

.order-listing-footer .button:hover {
    transform: translateY(-3px); /* Matching your previous hover style */
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Mobile Responsive */
@media (max-width: 600px) {
    .order-listing-footer {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}

.order-product-total {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.1rem;
}

.regular-price-strikethrough {
    text-decoration: line-through;
    color: #999; /* Faded grey */
    font-size: 1.2em;
    font-weight: normal;
}

.final-price {
    color: #ff4c00; /* Optional: Make the sale price red or keep it bold */
    font-weight: 500; font-size: 1.6em;
}

</style>
<div class="my-account-header">
	<h2>Orders</h2>
  <div class="my-account-header-tags">Manage your orders and track their status</div>
</div>

<div class="wp-tab-container inquries-section">
  <input type="radio" name="wp-tabs" id="tab-inquiries" class="wp-tab-input" checked>
  <label for="tab-inquiries" class="wp-tab-label"><span>All Orders</span></label>
  <div id="content-inquiries" class="wp-tab-content">
    <!-- all order content -->
     <?php include_once( get_stylesheet_directory() . '/woocommerce/myaccount/order-content/all-orders.php' ); ?>
  </div>

  <input type="radio" name="wp-tabs" id="tab-refund-aftersales" class="wp-tab-input">
  <label for="tab-refund-aftersales" class="wp-tab-label"><span>To Pay</span></label>
  <div id="content-refund-aftersales" class="wp-tab-content">
    <!-- all order content -->
     <?php include_once( get_stylesheet_directory() . '/woocommerce/myaccount/order-content/to-pay.php' ); ?>
  </div>

  <input type="radio" name="wp-tabs" id="tab-rfqs" class="wp-tab-input">
  <label for="tab-rfqs" class="wp-tab-label"><span>To Ship</span></label>
  <div id="content-rfqs" class="wp-tab-content">
      <!-- refund and after sales -->
     <?php include_once( get_stylesheet_directory() . '/woocommerce/myaccount/order-content/to-ship.php' ); ?>
  </div>
 
  <input type="radio" name="wp-tabs" id="tab-receive" class="wp-tab-input">
  <label for="tab-receive" class="wp-tab-label"><span>To Receive</span></label>
  <div id="content-receive" class="wp-tab-content">
      <!-- Coupons content -->
     <?php include_once( get_stylesheet_directory() . '/woocommerce/myaccount/order-content/to-receive.php' ); ?>
  </div>

  <input type="radio" name="wp-tabs" id="tab-complete" class="wp-tab-input">
  <label for="tab-complete" class="wp-tab-label"><span>Completed</span></label>
  <div id="content-complete" class="wp-tab-content">
      <!-- Coupons content -->
     <?php include_once( get_stylesheet_directory() . '/woocommerce/myaccount/order-content/completed.php' ); ?>
  </div>

  <input type="radio" name="wp-tabs" id="tab-review" class="wp-tab-input">
  <label for="tab-review" class="wp-tab-label"><span>Reviews</span></label>
  <div id="content-review" class="wp-tab-content">
      <!-- Coupons content -->
     <?php include_once( get_stylesheet_directory() . '/woocommerce/myaccount/order-content/reviews.php' ); ?>
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