<?php
/**
 * My Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();
$customer = new WC_Customer( get_current_user_id() );

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Billing address', 'woocommerce' ),
			'shipping' => __( 'Shipping address', 'woocommerce' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Billing address', 'woocommerce' ),
		),
		$customer_id
	);
}

?>
<style>
	:root {
            --color-background-primary: light-dark(rgba(255, 255, 255, 1), rgba(48, 48, 46, 1));
            --color-background-secondary: light-dark(rgba(245, 244, 237, 1), rgba(38, 38, 36, 1));
            --color-background-success: light-dark(rgba(233, 241, 220, 1), rgba(27, 70, 20, 1));
            --color-text-primary: light-dark(rgba(20, 20, 19, 1), rgba(250, 249, 245, 1));
            --color-text-secondary: light-dark(rgba(61, 61, 58, 1), rgba(194, 192, 182, 1));
            --color-text-tertiary: light-dark(rgba(115, 114, 108, 1), rgba(156, 154, 146, 1));
            --color-text-success: light-dark(rgba(38, 91, 25, 1), rgba(122, 185, 72, 1));
            --color-border-secondary: light-dark(rgba(31, 30, 29, 0.3), rgba(222, 220, 209, 0.3));
            --color-border-tertiary: light-dark(rgba(31, 30, 29, 0.15), rgba(222, 220, 209, 0.15));
            --border-radius-md: 8px;
            --border-radius-lg: 10px;
         }
		
	.addr-grid {
		display: flex;
		/* flex-wrap: wrap; */
		gap: 1.25rem;
	}
	.addr-card {
		background: var(--color-background-primary);
		border: 0.5px solid var(--color-border-tertiary);
		border-radius: var(--border-radius-lg);
		padding: 1.25rem;
		display: flex;
		flex-direction: column;
		gap: 1rem;
		width: 100%;
	}
	.addr-card-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
	.addr-badge {
		display: flex;
		align-items: center;
		gap: 6px;
		font-size: 12px;
		font-weight: 500;
		padding: 3px 10px;
		border-radius: 20px;
	}
	.badge-billing {
		background: #e1f5ee;
		color: #0f6e56;
	}
	.badge-shipping {
		background: #e6f1fb;
		color: #185fa5;
	}
	.badge-icon {
		font-size: 13px;
	}
	.edit-btn {
		display: flex;
		align-items: center;
		gap: 5px;
		font-size: 12px;
		color: var(--color-text-secondary);
		border: 0.5px solid var(--color-border-secondary);
		border-radius: var(--border-radius-md);
		padding: 4px 15px;
		background: transparent;
		cursor: pointer;
		transition: background 0.1s;
	}
	.edit-btn:hover {
		background: var(--color-background-secondary);
		color: var(--color-text-primary);
	}
	.edit-btn i {
		font-size: 13px;
	}
	.addr-body {
		display: flex;
		flex-direction: column;
		gap: 8px;
		margin-bottom: 14px;
	}
	.addr-row {
		display: flex;
		align-items: flex-start;
		gap: 8px;
	}
	.addr-row i {
		font-size: 15px;
		color: var(--color-text-tertiary);
		margin-top: 1px;
		flex-shrink: 0;
	}
	.addr-text {
		font-size: 14px;
		font-weight: 500;
		color: var(--color-text-primary);
		line-height: 1.5;
	}
	.addr-muted {
		font-size: 13px;
		color: var(--color-text-secondary);
	}
	.divider {
		border: none;
		border-top: 0.5px solid var(--color-border-tertiary);
	}
	.set-default {
		font-size: 12px;
		color: var(--color-text-tertiary);
		display: flex;
		align-items: center;
		gap: 5px;
		cursor: pointer;
	}
	.set-default i {
		font-size: 13px;
	}
	.set-default:hover {
		color: var(--color-text-secondary);
	}
	.default-tag {
		font-size: 11px;
		font-weight: 500;
		color: var(--color-text-success);
		background: var(--color-background-success);
		border-radius: 20px;
		padding: 2px 8px;
	}
</style>

<?php if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) : ?>
<div class="hihi">
<?php endif; ?>
<div class="addr-grid">
<?php foreach ( $get_addresses as $name => $address_title ) : ?>

<div class="addr-card">
	<?php if ( 'billing' === $name ) : ?>
		<div class="addr-card-head">
			<span class="addr-badge badge-billing">
				<i class="ion-android-clipboard"></i> Billing
			</span>
			

			<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="edit-btn">
				<i class="ion-compose"></i> Edit
			</a>
		</div>

		<div class="addr-body">
			<div class="addr-row">
				<i class="ti ti-map-pin"></i>

				<span class="addr-text">
					<em><?php if ( empty( $customer->get_billing_address_1() ) || empty( $customer->get_billing_city() ) ) { _e( 'You have not set up this type of address yet.', 'woocommerce' ); }?></em>
					<?php echo $customer->get_billing_address_1(); ?><br>
					<?php echo $customer->get_billing_city(); ?><br>
					<?php echo $customer->get_billing_postcode(); ?><br>
					<?php echo $customer->get_billing_phone(); ?>
				</span>
			</div>
		</div>

	<?php elseif ( 'shipping' === $name ) : ?>
		<div class="addr-card-head">
			<span class="addr-badge badge-shipping">
				<i class="ion-cube"></i> Shipping
			</span>

			<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="edit-btn">
				<i class="ion-compose"></i> Edit
			</a>
		</div>

		<div class="addr-body">
			<div class="addr-row">
				<i class="ti ti-map-pin"></i>

				<span class="addr-text">
					<em><?php if ( empty( $customer->get_shipping_address_1() ) || empty( $customer->get_shipping_city() ) ) {_e( 'You have not set up this type of address yet.', 'woocommerce' );}?></em>
					<?php echo $customer->get_shipping_address_1(); ?><br>
					<?php if($customer->get_shipping_address_2()) {echo $customer->get_shipping_address_2() . '<br>';}?>
					<?php echo $customer->get_shipping_city(); ?><br>
					<?php echo $customer->get_shipping_postcode(); ?><br>
					<?php echo $customer->get_shipping_country(); ?>
				</span>
			</div>
		</div>

	<?php endif; ?>

	<div style="display:flex;justify-content:space-between;align-items:center;">
		<span class="default-tag">
			✓ Default <?php echo esc_html( $name ); ?>
		</span>
	</div>

</div>

<?php endforeach; ?>
</div>

<?php if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) : ?>
</div>
<?php endif; ?>
