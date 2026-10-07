<div class="wp-tab-container inquries-section">
  
  <input type="radio" name="wp-tabs" id="tab-inquiries" class="wp-tab-input" checked>
  <label for="tab-inquiries" class="wp-tab-label">All Orders</label>
  <div id="content-inquiries" class="wp-tab-content">
			<?php
            $order_count = 20;
            $customer_orders = get_posts(
                apply_filters(
                    'woocommerce_my_account_my_orders_query',
                    array(
                        'numberposts' => $order_count,
                        'meta_key'    => '_customer_user',
                        'meta_value'  => get_current_user_id(),
                        'post_type'   => wc_get_order_types( 'view-orders' ),
                        'post_status' => array_keys( wc_get_order_statuses() ),
                    )
                )
            );
            print_r($customer_orders);
if ( $customer_orders ) : ?>
    <table class="inquiry-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Date</th>
          <th>Status</th>
          <th>Total</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
			<?php

			foreach ( $customer_orders as $customer_order ) :
				$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$item_count = $order->get_item_count();
				?>
				<tr class="order">
					<?php foreach ( $my_orders_columns as $column_id => $column_name ) : ?>
						<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>
								<?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>

							<?php elseif ( 'order-number' === $column_id ) : ?>
								<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
									<?php echo _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>

							<?php elseif ( 'order-date' === $column_id ) : ?>
								<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>

							<?php elseif ( 'order-status' === $column_id ) : ?>
								<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>

							<?php elseif ( 'order-total' === $column_id ) : ?>
								<?php
								/* translators: 1: formatted order total 2: total order items */
								printf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>

							<?php elseif ( 'order-actions' === $column_id ) : ?>
								<?php
								$actions = wc_get_account_orders_actions( $order );

								if ( ! empty( $actions ) ) {
									foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
										echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
									}
								}
								?>
							<?php endif; ?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
    </table>
    <?php endif; ?>
  </div>

  <input type="radio" name="wp-tabs" id="tab-rfqs" class="wp-tab-input">
  <label for="tab-rfqs" class="wp-tab-label">My RFQs</label>
  <div id="content-rfqs" class="wp-tab-content">
    <table class="inquiry-table">
      <thead>
        <tr>
          <th>RFQ Reference</th>
          <th>Status</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
<tr>
          <td><a href="/rfq/99281" class="row-link">#RFQ-99281</a></td>
          <td><span class="status-badge status-pending">Pending</span></td>
          <td>Jan 18, 2026</td>
          <td><a href="#" class="view-btn">View Details</a></td>
        </tr>
        <tr>
          <td><a href="/rfq/99285" class="row-link">#RFQ-99285</a></td>
          <td><span class="status-badge">Processing</span></td>
          <td>Jan 20, 2026</td>
          <td><a href="#" class="view-btn">View Details</a></td>
        </tr>
      </tbody>
    </table>
  </div>

  <input type="radio" name="wp-tabs" id="tab-invoice" class="wp-tab-input">
  <label for="tab-invoice" class="wp-tab-label">Invoice</label>
  <div id="content-invoice" class="wp-tab-content">
    <table class="inquiry-table">
      <thead>
        <tr>
          <th>Invoice #</th>
          <th>Amount</th>
          <th>Due Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>

        <tr>
          <td>INV-2026-001</td>
          <td>$1,250.00</td>
          <td>Feb 01, 2026</td>
          <td><a href="#" class="status-btn">Paid</a></td>
          <td><a href="#" class="view-btn">Download PDF</a></td>
        </tr>
        <tr>
          <td>INV-2026-002</td>
          <td>$10,250.00</td>
          <td>Feb 20, 2026</td>
          <td><a href="#" class="status-btn">Pending</a></td>
          <td><a href="#" class="view-btn">Download PDF</a></td>
        </tr>
      </tbody>
    </table>
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