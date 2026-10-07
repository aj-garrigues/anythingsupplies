<?php do_action( 'woocommerce_before_account_orders', $has_orders );
$completed = $customer_orders->orders;
?>

<?php

// 1. Filter the orders array before starting the loop
    $completed = array_filter( $completed, function( $order_obj ) {
        // Get the order object if it's just an ID, or use it directly
        $order = ( $order_obj instanceof WC_Order ) ? $order_obj : wc_get_order( $order_obj );
        
        if ( ! $order ) return false;

        $status = $order->get_status();
        
        // Only keep 'on-hold' and 'pending'
        return ( $status === 'completed' ); 
    });
    
	if ( $completed ) :

			foreach ( $completed as $customer_order ) {
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
				<div class="order-footer-notes-content">
				<?php 
				$internal_note = $order->get_meta( 'order_info_from_as' );
				if ( ! empty( $internal_note ) ) : ?>
					<p>
						<strong>Note:</strong> <?php echo esc_html( $internal_note ); ?>
					</p>
				<?php endif;
				?>
				</div>
				<div>Contact your agent for more information.</div>
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
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'order_again', $order->get_id(), wc_get_cart_url() ), 'woocommerce-order_again' ) ); ?>" class=" btn-orange">
						Buy Again
					</a>

						<?php
						    display_pdf_invoice_button( $order->get_id() );
						?>
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
 <img style="display:block; margin: 100px auto; width: 200px;" src="<?php echo get_stylesheet_directory_uri(); ?>/images/no-data-list.png" alt="No Data" />

<?php endif; ?>
<!-- End of orders loop --> 
<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>