<?php
/**
 * Plugin Name: Anything Supplies Shortcodes
 * Description: Custom frontend elements made from scratch.
 * Version:     1.0.0
 * Author:      Anything Supplies
 */

defined('ABSPATH') || exit;

define('AS_SHORTCODES_URL', plugin_dir_url(__FILE__));
define('AS_SHORTCODES_PATH', plugin_dir_path(__FILE__));

require_once AS_SHORTCODES_PATH . 'includes/enqueue.php';

foreach (glob(AS_SHORTCODES_PATH . 'includes/shortcodes/*.php') as $file) {
    require_once $file;
}


// Enqueue assets/css/global.css
function as_global_styles() {
    // Enqueue a custom stylesheet located in your theme folder
    wp_enqueue_style( 
        'as-global-style', 
        AS_SHORTCODES_URL . 'assets/css/global.css',
        array(), 
        '1.0.1',
        'all' 
    );
}
add_action('wp_enqueue_scripts', 'as_global_styles');

// Enqueue in Wishlist page
function wishlist_table_styles() {
    // Enqueue a custom stylesheet located in your theme folder
    if(is_page('wishlist')) {
        wp_enqueue_style( 
            'wishlist_table_style', 
            AS_SHORTCODES_URL . 'assets/css/wishlist-table.css',
            array(), 
            '1.0.0',
            'all' 
        );
    }
}
add_action('wp_enqueue_scripts', 'wishlist_table_styles');

// Inquiry basket
// function martfury_extra_inquiry_basket() {
//     $inquiry_list = count(WC()->session->get('custom_inquiry_list', array())) ?? 0;

//     printf(
//         '<li class="extra-menu-item menu-item-basket woocommerce">
//             <a class="basket-contents" id="icon-basket-contents" href="/inquiry-basket/">
//                 <i class="icon-cart extra-icon"></i>
//                 <span class="mini-item-counter mf-background-primary">
//                     '.$inquiry_list.'
//                 </span>
//             </a>
//         </li>',
//     );
// }