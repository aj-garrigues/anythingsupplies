<?
// 1. Add fields to user profile
add_action( 'show_user_profile', 'add_custom_user_discount_fields' );
add_action( 'edit_user_profile', 'add_custom_user_discount_fields' );

function add_custom_user_discount_fields( $user ) {
    ?>
    <h3>Member Discount Settings</h3>
    <table class="form-table">
        <tr>
            <th><label for="member_discount_type">Discount Class</label></th>
            <td>
                <select name="member_discount_type" id="member_discount_type">
                    <option value="">None</option>
                    <option value="family" <?php selected( get_the_author_meta( 'member_discount_type', $user->ID ), 'family' ); ?>>Family</option>
                    <option value="sales_staff" <?php selected( get_the_author_meta( 'member_discount_type', $user->ID ), 'sales_staff' ); ?>>Sales Staff</option>
                    <option value="club" <?php selected( get_the_author_meta( 'member_discount_type', $user->ID ), 'club' ); ?>>Club</option>
                    <option value="loyalty" <?php selected( get_the_author_meta( 'member_discount_type', $user->ID ), 'loyalty' ); ?>>Loyalty</option>
                    <option value="charity" <?php selected( get_the_author_meta( 'member_discount_type', $user->ID ), 'charity' ); ?>>Charity</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="member_discount_value">Discount Percentage (%)</label></th>
            <td>
                <input type="number" name="member_discount_value" value="<?php echo esc_attr( get_the_author_meta( 'member_discount_value', $user->ID ) ); ?>" class="regular-text" />
            </td>
        </tr>
    </table>
    <?php
}

// 2. Save the fields
add_action( 'personal_options_update', 'save_custom_user_discount_fields' );
add_action( 'edit_user_profile_update', 'save_custom_user_discount_fields' );

function save_custom_user_discount_fields( $user_id ) {
    update_user_meta( $user_id, 'member_discount_type', $_POST['member_discount_type'] );
    update_user_meta( $user_id, 'member_discount_value', $_POST['member_discount_value'] );
}

add_filter( 'woocommerce_product_get_price', 'apply_member_discount_price', 10, 2 );
add_filter( 'woocommerce_product_variation_get_price', 'apply_member_discount_price', 10, 2 );

function apply_member_discount_price( $price, $product ) {
    if ( is_admin() || ! is_user_logged_in() ) return $price;

    $user_id = get_current_user_id();
    $discount_type = get_user_meta( $user_id, 'member_discount_type', true );
    $discount_amount = get_user_meta( $user_id, 'member_discount_value', true );

    // If a discount class and value are set, calculate new price
    if ( ! empty( $discount_type ) && ! empty( $discount_amount ) ) {
        $discount_factor = ( 100 - (float) $discount_amount ) / 100;
        $price = $price * $discount_factor;
    }

    return $price;
}