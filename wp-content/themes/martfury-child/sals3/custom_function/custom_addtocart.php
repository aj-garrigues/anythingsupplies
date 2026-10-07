<?php
// Note: This file should start with the standard child theme setup if you have one.
// The code below assumes this is part of your child theme's functionality.

/**
 * 1. Enqueue Custom Scripts and Styles only on Single Product Pages
 * We use the WordPress conditional tag is_product() for this.
 */
function custom_enqueue_cart_modal_assets() {
    // Check if the current page is a single WooCommerce product page
    if ( is_product() ) {
        // Enqueue CSS (Handles the modal positioning and styling)
        wp_enqueue_style( 'custom-cart-modal-css', get_stylesheet_directory_uri() . '/css/cart-modal.css', array(), '1.0' );

        // Enqueue JS (Handles the trigger logic, requires jQuery and WooCommerce AJAX)
        wp_enqueue_script( 'custom-cart-modal-js', get_stylesheet_directory_uri() . '/js/cart-modal.js', array('jquery', 'wc-add-to-cart'), '1.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'custom_enqueue_cart_modal_assets' );


/**
 * 2. Add Modal HTML to the Footer only on Single Product Pages
 * The modal structure is output in the footer.
 */
function custom_add_to_cart_modal_html() {
    // Check if the current page is a single WooCommerce product page
    if ( is_product() ) {
        ?>
        <div id="custom-add-to-cart-modal">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <p>✅ **Product successfully added to your cart!**</p>
                <div class="modal-actions">
                    <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="button view-cart-button">View Cart</a>
                    <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="button checkout-button">Checkout</a>
                </div>
            </div>
        </div>
        <?php
    }
}
add_action( 'wp_footer', 'custom_add_to_cart_modal_html' );

// End of functions.php