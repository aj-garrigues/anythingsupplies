<?php do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<?php

//filter the order to pending and on-hold orders only

$customer_orders = wc_get_orders( array(
    'customer_id' => get_current_user_id(),
    'status'      => array( 'pending', 'on-hold', 'processing', 'completed', 'cancelled', 'refunded' ),
   // 'status'      => array( 'pending', 'on-hold' ),
    'limit'       => -1, // Get all orders
) );

$has_orders = ! empty( $customer_orders );

?>


<?php if ( $customer_orders ) : ?>

<?php
echo get_current_user_id();
print_r($customer_orders->orders);
			foreach ( $customer_orders->orders as $customer_order ) {
				$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();

				//start the row orders here

					?>
<div class="order-listing">
    <div class="order-list-head">
        <div class="head-left">
            <span>Order #<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>"><?php echo $order->get_order_number(); ?></a></span> - 
            <span><?php echo wc_format_datetime( $order->get_date_created() ); ?></span>
        </div>
        <div class="head-right">
            <?php echo wc_get_order_status_name( $order->get_status() ); ?>
        </div>
    </div>
    <div class="order-product-list"> 
		<?php
		$item_count = 0;

        foreach ( $order->get_items() as $item_id => $item ) {
			$item_count++;
            $product = $item->get_product();
            wc_get_template( 'order/order-details-items.php', array(
                'order'              => $order,
				'item_count'        => $item_count,
                'item_id'            => $item_id,
                'item'               => $item,
                'show_purchase_note' => $show_purchase_note,
                'purchase_note'      => $product ? $product->get_purchase_note() : '',
                'product'            => $product,
            ) );
        }
		
        ?>
		



    </div>
</div>
    <div class="order-listing-footer">
		<div class="order-footer-items">
			<div class="order-footer-notes">
				<?php 
				$internal_note = $order->get_meta( 'order_info_from_as' );
				if ( ! empty( $internal_note ) ) : ?>
					<p>
						<strong>Note:</strong> <?php echo esc_html( $internal_note ); ?>
					</p>
				<?php endif;
				?>

			</div>
			<div class="order-footer-total-actions">
				<div class="order-footer-total">

				<?php 
    // 1. Calculate the 'Gross' Subtotal (before any discounts)
    // We sum up the line subtotals to get the "Regular" price total
    $order_subtotal = $order->get_subtotal();
    $total_discount = $order->get_total_discount();
    $tax_total      = $order->get_total_tax();
    $shipping_total = $order->get_shipping_total();
    ?>

    <div class="footer-row">
        <span>Regular Price:</span>
        <span><?php echo wc_price($order_subtotal); ?></span>
    </div>

    <?php if ( $total_discount > 0 ) : ?>
        <div class="footer-row discount">
            <span>
                Discounts 
                <?php 
                // Display specific coupon codes used
                $coupons = $order->get_coupon_codes();
                if ( !empty($coupons) ) {
                    //echo '<small>(' . implode(', ', $coupons) . ')</small>';
                }
                ?>:
            </span>
            <span>- <?php echo wc_price($total_discount); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( $shipping_total > 0 ) : ?>
        <div class="footer-row">
            <span>Shipping:</span>
            <span><?php echo wc_price($shipping_total); ?></span>
        </div>
    <?php endif; ?>

    <div class="footer-row grand-total">
        <strong>Total Order:</strong>
        <strong><?php echo $order->get_formatted_order_total(); ?></strong>
    </div>

					
				</div>
				<div class="order-footer-actions">
					<a href="mailto:support@yourdomain.com?subject=Inquiry for Order #<?php echo $order->get_order_number(); ?>" class="order-button-contact-agent">
						Contact Agent
					</a> 
					<?php if ( $order->get_status()==='completed' ) : ?>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'order_again', $order->get_id(), wc_get_cart_url() ), 'woocommerce-order_again' ) ); ?>" class="order-button-buy-again">
						Buy Again
					</a>
					<?php endif; ?>
					
					<?php if ( $order->get_status()==='completed' ) : ?>
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-order', $order->get_id(), wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="order-button-style">
							View Invoice
						</a>
					<?php elseif ( $order->get_status() === 'processing' ) : ?>
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-order', $order->get_id(), wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="order-button-style">
							Download Invoice
						</a>
					<?php elseif ( $order->get_status() === 'on-hold' ) : ?>
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-order', $order->get_id(), wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="order-button-style">
							Invoice Pending
						</a>
					<?php elseif ( $order->get_status() === 'pending' ) : ?>
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-order', $order->get_id(), wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="order-button-style">
							Pay now
						</a>
					<?php elseif ( $order->get_status() === 'cancelled' ) : ?>
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-order', $order->get_id(), wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="order-button-style">
							Invoice Unavailable
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

        


    </div>
<?php
				//end for each order row
			}
			?>
	
	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
			<?php endif; ?>

			<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>

	<?php wc_print_notice( esc_html__( 'No order has been made yet.', 'woocommerce' ) . ' <a class="woocommerce-Button wc-forward button' . esc_attr( $wp_button_class ) . '" href="' . esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ) . '">' . esc_html__( 'Browse products', 'woocommerce' ) . '</a>', 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>


<div class="" style="margin:100px auto; text-align:center; display:block">
<i class="icon-portrait2" style="font-size:50px; font-weight:400"></i>
<p>No after-sales service requests at the moment</p>
</div>