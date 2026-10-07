<?php
function limit_chars($text, $limit = 50) {
    if (mb_strlen($text) > $limit) {
        return mb_substr($text, 0, $limit) . '...';
    }
    return $text;
}

// function add_inquiry_list_button($product_id=null) {
//     $bnt_inry  = "<button class='inquiry-btn' data-product-id='" . esc_attr($product_id) . "'><i class=\"icon-plus extra-icon\"></i></button>"; 
//      $bnt_inry  .= "<a href='" . get_permalink($product_id) . "' class='inquire-now-btn' data-product-id='" . esc_attr($product_id) . "'>Enquire</a>";  

//     return $bnt_inry;
// }
function add_inquiry_list_button($product_id=null) {
    ?>
    <div class="product-btns">
        <button class="inquiry-btn inquiry-trigger"
            data-product-id="<?php echo esc_attr($product_id); ?>"
            title="Add to inquiry basket">
            <i class="icon-plus extra-icon"></i>
        </button>
    
        <?php echo do_shortcode('[wcboost_wishlist_button product_id="' . esc_attr($product_id) . '"]'); ?>
    </div>

    <a href="<?php echo esc_url(get_permalink($product_id)); ?>"
    class="inquire-now-btn"
    data-product-id="<?php echo esc_attr($product_id); ?>">
    Enquire
    </a>
    <?php
}

// Moving social login to the top of the form
add_action('woocommerce_login_form_start', function() {
    echo do_shortcode('[nextend_social_login]');
});

// Disable [Login Details] email when signing up using social login
add_filter('wp_new_user_notification_email', '__return_false');