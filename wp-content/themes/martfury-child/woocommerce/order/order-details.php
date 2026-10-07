<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

 // phpcs:disable WooCommerce.Commenting.CommentHooks.MissingHookComment

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$order_items        = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$downloads          = $order->get_downloadable_items();
$actions            = array_filter(
	wc_get_account_orders_actions( $order ),
	function ( $key ) {
		return 'view' !== $key;
	},
	ARRAY_FILTER_USE_KEY
);

// We make sure the order belongs to the user. This will also be true if the user is a guest, and the order belongs to a guest (userID === 0).
$show_customer_details = $order->get_user_id() === get_current_user_id();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads, 
			'show_title' => true,
		)
	);
}
?>

	<div class="notice notice-info">
		
<?php
echo wp_kses_post(
	/**
	 * Filter to modify order detiails status text.
	 *
	 * @param string $order_status The order status text.
	 *
	 * @since 10.1.0
	 */
	apply_filters(
		'woocommerce_order_details_status',
		sprintf(
		/* translators: 1: order number 2: order date 3: order status */
			esc_html__( 'Order #%1$s was placed on %2$s and is currently %3$s.', 'woocommerce' ),
			'<mark class="order-number">' . $order->get_order_number() . '</mark>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'<mark class="order-date">' . wc_format_datetime( $order->get_date_created() ) . '</mark>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'<mark class="order-status">' . wc_get_order_status_name( $order->get_status() ) . '</mark>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		),
		$order
	)
);

?>
	</div>

<div class="d-flex justify-content-end mb-4 order-actions">
	<div class="flex-2" style="font-size:14px;">
<h2 class="woocommerce-order-details__title h3"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>
	</div>
	<div class="flex-2 text-right">
						<?php
						$wp_button_class = wc_wp_theme_get_element_class_name( 'btn-orange' ) ? ' ' . wc_wp_theme_get_element_class_name( 'btn-bluegreen' ) : '';
						$btnclass['Cancel'] = 'btn-bluegreen';
						$btnclass['Pay'] = 'btn-orange';
						foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							if ( empty( $action['aria-label'] ) ) {
								// Generate the aria-label based on the action name.
								/* translators: %1$s Action name, %2$s Order number. */
								$action_aria_label = sprintf( __( '%1$s order number %2$s', 'woocommerce' ), $action['name'], $order->get_order_number() );
							} else {
								$action_aria_label = $action['aria-label'];
							}
								echo '<a href="' . esc_url( $action['url'] ) . '" class="' . esc_attr( $btnclass[$action['name']] ) . '  ' . sanitize_html_class( $key ) . ' order-actions-button" aria-label="' . esc_attr( $action_aria_label ) . '" style="margin-left:7px; margin-right:7px;">' . esc_html( $action['name'] ) . '</a>';
								unset( $action_aria_label );
								
						}
						display_pdf_invoice_button( $order->get_id() );
						?>
	</div>
</div>
<section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Item', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Qty', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			do_action( 'woocommerce_order_details_before_order_table_items', $order );
			$item_index = 0;
			foreach ( $order_items as $item_id => $item ) {
				$product = $item->get_product();
				$item_index++;
				wc_get_template(
					'order/order-details-item.php',
					array(
						'order'              => $order,
						'item_index'         => $item_index,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => $show_purchase_note,
						'purchase_note'      => $product ? $product->get_purchase_note() : '',
						'product'            => $product,
					)
				);
				
			}

			do_action( 'woocommerce_order_details_after_order_table_items', $order );
			?>
			<tr><td colspan="5">&nbsp;</td></tr>
		</tbody>

		<?php
		if ( ! empty( $actions ) ) :
			?>
		<tfoot>
			<tr>
				<th class="order-actions--heading" colspan="3"></th>
				<td colspan="2">

					</td>
				</tr>
			</tfoot>
			<?php endif ?>
		<tfoot>
			<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				?> 
					<tr>
						<td></td> 
						<td scope="row" colspan="3"><?php echo esc_html( $total['label'] ); ?></td>
						<td ><?php echo wp_kses_post( $total['value'] ); ?></td>
					</tr>
					<?php 
			} 
			?>
			<?php if ( $order->get_customer_note() ) : ?>
				<tr>
					<th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
					<td>
					<?php
					$customer_note = wc_wptexturize_order_note( $order->get_customer_note() );
					echo wp_kses( nl2br( $customer_note ), array( 'br' => array() ) );
					?>
					</td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action( 'woocommerce_after_order_details', $order );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}
