<?php
/**
 * My Orders - Deprecated
 *
 * @deprecated 2.6.0 this template file is no longer used. My Account shortcode uses orders.php.
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

$my_orders_columns = apply_filters(
	'woocommerce_my_account_my_orders_columns',
	array(
		'order-number'  => esc_html__( 'Order', 'woocommerce' ),
		'order-date'    => esc_html__( 'Date', 'woocommerce' ),
		'order-status'  => esc_html__( 'Status', 'woocommerce' ),
		'order-total'   => esc_html__( 'Total', 'woocommerce' ),
		'order-actions' => '&nbsp;',
	)
);

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

if ( $customer_orders ) : ?>

	<h2><?php echo apply_filters( 'woocommerce_my_account_my_orders_title', esc_html__( 'Recent orders', 'woocommerce' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>

	<table class="shop_table shop_table_responsive my_account_orders">

		<thead>
			<tr>
				<?php foreach ( $my_orders_columns as $column_id => $column_name ) : ?>
					<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
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

<style>
  /* Container for the tabs */
  .inquries-section a{ color:#2271b1; text-decoration:none; font-weight:500 !important; }
  .wp-tab-container {
    display: flex;
    flex-wrap: wrap;
    max-width: 100%;
    font-family: sans-serif;
  
  }

  /* Hide the actual radio buttons */
  .wp-tab-input {
    display: none; 
  }

  /* Style the tab labels */
  .wp-tab-label {
    padding: 7px 25px;
    background: #f1f1f1;
    cursor: pointer;
    border: 1px solid #ccc;
    border-bottom: none;
    margin-right: 5px;
    font-weight: bold;
    border-radius: 5px 5px 0 0;
  }

  /* Style the content area */
  .wp-tab-content {
    width: 100%;
    padding: 20px;
    border: 1px solid #ccc;   border-radius: 0px 8px 8px 8px;
    display: none;
    order: 1; /* Ensures content appears below labels */
    background: #fff;
  }

 /* Show content for the checked radio */
  #tab-inquiries:checked ~ #content-inquiries,
  #tab-rfqs:checked ~ #content-rfqs,
  #tab-invoice:checked ~ #content-invoice { display: block; }

  /* Active Tab Styling */
  .wp-tab-input:checked + .wp-tab-label { background: #fff; border: 1px solid #ddd; border-bottom: 1px solid #fff; position: relative; z-index: 2; color: #2271b1; }

  /* Table Styling */
  .inquiry-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    border: none; /* Removes outer border */
  }
  .inquiry-table th {
    text-align: left;
    padding: 8px 15px;
    border-bottom: 1px solid #eee; /* Header underline */
    border-right: none; /* Removes right border */
    color: #333;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background-color: #f5f5f5;
  }
  .inquiry-table td {
    padding: 8px15px;
    border-bottom: 1px solid #eee; /* Only bottom row border */
    border-right: none; /* Removes right border */
    vertical-align: middle;
  }

/* Agent Photo Styling */
  .agent-cell { display: flex; align-items: center; gap: 8px; font-weight: 400; }
  .agent-photo img{
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #ddd;
  }

  /* Mouseover Row Effect */
  .inquiry-table tr:hover {
    background-color: #fafafa !important;
  }

  /* Chat Now Button */
  .chat-btn {
    background-color: #2271b1;
    color: white;
    padding: 5px 16px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    display: inline-block;
    transition: all 0.2s ease;
  }
  .chat-btn:hover {
    background-color: #135e96;
    color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  .inquiries-product-cell{
    width:40%;
  }
  /* Initial hidden state */
.popup {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 300px;
    background-color: #ffffff;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border-radius: 8px;
    padding: 20px;
    border-left: 5px solid #e64900;
    
    /* Animation setup */
    transform: translateY(150%); /* Move it down off-screen */
    transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 1000;
}
.popup {
    position: fixed;
    bottom: 20px;
    right: 20px;
    
    /* Specified Size */
    min-width: 700px;
    min-height: 700px;
    max-width: 90vw; /* Responsive safety */
    max-height: 90vh; /* Responsive safety */
    
    background-color: #ffffff;
    box-shadow: -10px 10px 40px rgba(0,0,0,0.2);
    border-radius: 10px;
    overflow: hidden; /* Keeps scrollbar inside radius */
    display: flex;
    flex-direction: column;

    /* Animation */
    transform: translateY(110%); /* Hidden below */
    transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    z-index: 2000;
}

.popup.show {
    transform: translateY(0);
}

/* The Top-Right Close Icon */
.close-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #f5f5f5;
    border: none;
    font-size: 28px;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s, transform 0.2s;
    z-index: 1001; /* Ensure icon is always on top */
}

.close-icon:hover {
    background-color: #e0e0e0;
    transform: scale(1.1);
}

/* Content Area */
.popup-scroll-container {
    padding: 40px;
    overflow-y: auto;
    height: 100%;
}

.popup-content h2 {
    margin-top: 0;
    font-family: sans-serif;
}
</style>

<div class="wp-tab-container inquries-section">
  
  <input type="radio" name="wp-tabs" id="tab-inquiries" class="wp-tab-input" checked>
  <label for="tab-inquiries" class="wp-tab-label">My Inquiries</label>
  <div id="content-inquiries" class="wp-tab-content">
    <table class="inquiry-table">
      <thead>
        <tr>
          <th>Inquiry</th>
          <th>Agent</th>
          <th>Status</th>
          <th>Date Sent</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>

      <?php
      //print_r($inquiries);

    if ($inquiries) {
        foreach ($inquiries as $row) {
            // Get the permalink for the specific product in this row
            $product_post = get_post($row->product_id);

            $product_url = get_permalink($row->product_id);
           // $product_name = get_the_title($row->product_id);

            // Nonce for secure deletion
            $delete_url = wp_nonce_url(
                admin_url("admin.php?page=partner-inquiries&partner_id=$partner_id&action=delete&inquiry_id={$row->id}"),
                'delete_inquiry_' . $row->id
            );

            echo "<tr>
               
                <td class='inquiries-product-cell'>
                    <a href='" . esc_url($product_url) . "' target='_blank' style='text-decoration:none; font-weight:400;'>
                        " . esc_html($product_post->post_title) . " 
                       
                    </a>
                </td>
                
                <td>".sls_get_user_agent($row->agent_id)."</td>
                <td><span class='badge status-" . esc_attr($row->status) . "'>" . ucfirst($row->status) . "</span></td>
                <td>" . date('M j, Y', strtotime($row->created_at)) . "</td>
                <td>
                    
                    <a href=\"#\" class=\"trigger-btn\" data-product-id=\"" . esc_attr($row->product_id) . "\">view</a>
                </td>
            </tr>";
        }
    }

      ?>
       
      </tbody>
    </table>
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
