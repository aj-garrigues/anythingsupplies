<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);
?>
<style>
#content {
	padding-top: 28px;
}
.site-content {
	padding-bottom: 28px;
}
.dash-container {
	max-width: 1200px;
	margin: 0 auto;
	padding: 32px 20px;
}

/* Page Header */
.page-header {
	margin-bottom: 32px;
}

.welcome-section {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 12px;
	flex-wrap: wrap;
	gap: 16px;
}

.welcome-text h1 {
	font-size: 28px;
	font-weight: 700;
	color: #111827;
	margin-bottom: 4px;
}

.welcome-greeting {
	font-size: 15px;
	color: #6B7280;
}

.welcome-greeting strong {
	color: #111827;
	font-weight: 600;
}

.page-subtitle {
	color: #6B7280;
	font-size: 15px;
}

/* Stats Grid */
.stats-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
	gap: 20px;
	margin-bottom: 32px;
}

.stat-card {
	background: white;
	padding: 24px;
	border-radius: 12px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
	display: flex;
	align-items: center;
	gap: 16px;
	transition: all 0.2s;
	cursor: pointer;
}

.stat-card:hover {
	box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
	transform: translateY(-2px);
}

.stat-icon {
	width: 56px;
	height: 56px;
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 24px;
	flex-shrink: 0;
}

.stat-icon.orange {
	background: #FFF7ED;
	color: #00719a;
}

.stat-icon.blue {
	background: #EFF6FF;
	color: #2563EB;
}

.stat-icon.green {
	background: #D1FAE5;
	color: #059669;
}

.stat-icon.purple {
	background: #EDE9FE;
	color: #7C3AED;
}

.stat-content h3 {
	font-size: 28px;
	font-weight: 700;
	color: #111827;
	margin-bottom: 2px;
}

.stat-content p {
	font-size: 14px;
	color: #6B7280;
}

/* Main Grid */
.dashboard-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 24px;
}

/* Card Styles */
.dashboard-card {
	background: white;
	border-radius: 12px;
	padding: 24px;
	border: 1.5px solid #D1D5DB !important;
}

.card-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20px;
	padding-bottom: 16px;
	border-bottom: 2px solid #F3F4F6;
}

.card-title {
	font-size: 18px;
	font-weight: 700;
	color: #111827;
}

.view-all-link {
	color: #00719a;
	text-decoration: none;
	font-size: 14px;
	font-weight: 500;
	transition: color 0.2s;
}

.view-all-link:hover {
	color: #00719a;
}

/* Order List */
.order-list {
	display: flex;
	flex-direction: column;
	gap: 16px;
}

.order-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 12px;
	background: #F9FAFB;
	border-radius: 8px;
	transition: all 0.2s;
	cursor: pointer;
}

.order-item:hover {
	background: #F3F4F6;
}

.order-info {
	flex: 1;
}

.order-info img {
	width: 70px;
	height: 70px;
	border-radius: 8px;
	object-fit: cover;
	background: white;
	border: 1px solid #E5E7EB;
	flex-shrink: 0;
}

.order-number {
	font-size: 14px;
	font-weight: 600;
	color: #2563EB;
	margin-bottom: 4px;
}

.order-total {
	font-size: 13px;
	color: #6B7280;
}

.order-status {
	padding: 4px 10px;
	border-radius: 6px;
	font-size: 12px;
	font-weight: 600;
	white-space: nowrap;
}

.order-status.processing {
	background: #DBEAFE;
	color: #1E40AF;
}

.order-status.failed {
	background: #FEE2E2;
	color: #991B1B;
}

.order-status.completed {
	background: #D1FAE5;
	color: #065F46;
}

/* To Receive Section */
.receive-item {
	display: flex;
	gap: 16px;
	padding: 16px;
	background: #F9FAFB;
	border-radius: 8px;
	margin-bottom: 16px;
}

.receive-item:last-child {
	margin-bottom: 0;
}

.receive-image {
	width: 70px;
	height: 70px;
	border-radius: 8px;
	object-fit: cover;
	background: white;
	border: 1px solid #E5E7EB;
	flex-shrink: 0;
}

.receive-details {
	flex: 1;
}

.receive-order-number {
	font-size: 12px;
	color: #2563EB;
	font-weight: 600;
	margin-bottom: 4px;
}

.receive-product-name {
	font-size: 14px;
	color: #111827;
	font-weight: 500;
	margin-bottom: 8px;
	line-height: 1.4;
}

.receive-action {
	margin-top: 8px;
}

.btn {
	padding: 8px 16px;
	border-radius: 6px;
	font-size: 13px;
	font-weight: 600;
	cursor: pointer;
	transition: all 0.2s;
	border: none;
	text-decoration: none;
	display: inline-block;
}

.btn-primary {
	background: #00719a;
	color: white;
}

.btn-primary:hover {
	background: #E64A19;
	box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
}

.btn-secondary {
	background: white;
	color: #374151;
	border: 1.5px solid #D1D5DB;
}

.btn-secondary:hover {
	background: #F9FAFB;
	border-color: #9CA3AF;
}

/* Empty State */
.empty-state {
	text-align: center;
	padding: 40px 20px;
}

.empty-icon {
	font-size: 48px;
	margin-bottom: 12px;
	opacity: 0.3;
}

.empty-text {
	color: #9CA3AF;
	font-size: 14px;
}

/* Quick Actions */
.quick-actions {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 12px;
	margin-top: 20px;
}

.action-btn {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	padding: 12px;
	background: #F9FAFB;
	border: 1.5px solid #E5E7EB;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 500;
	color: #374151;
	cursor: pointer;
	transition: all 0.2s;
	text-decoration: none;
}
.action-btn.quick {
	padding: 6px 8px;
}
.action-btn.quick i {
	height: 30px;
}
.action-btn.quick.wishlist i {
	color: #c3312d;
}

.action-btn.quick.logout i {
	color: white;
	background: none;
	
}
.action-btn.quick.logout {
	background-color: #2563EB;
	color: white;
}

.action-btn:hover {
	background: white;
	border-color: #00719a;
	color: #00719a;
}

/* Account Info Card */
.account-info-list {
	display: flex;
	flex-direction: column;
	gap: 16px;
}

.info-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px;
	background: #F9FAFB;
	border-radius: 8px;
}

.info-icon {
	width: 40px;
	height: 40px;
	background: white;
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 18px;
	flex-shrink: 0;
}

.info-content {
	flex: 1;
}

.info-label {
	font-size: 12px;
	color: #6B7280;
	margin-bottom: 2px;
}

.info-value {
	font-size: 14px;
	color: #111827;
	font-weight: 500;
}

/* Status Badge */
.status-badge {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-badge.processing {
    background: #DBEAFE;
    color: #1E40AF;
}
.status-badge.failed {
    background: #FEE2E2;
    color: #991B1B;
}

.status-badge.completed {
    background: #D1FAE5;
    color: #065F46;
}

.status-badge.shipped {
    background: #E0E7FF;
    color: #4338CA;
}

.status-badge.pending {
    background: #FEF3C7;
    color: #92400E;
}

/* Responsive */
@media (max-width: 968px) {
	.dashboard-grid {
			grid-template-columns: 1fr;
	}
}

@media (max-width: 640px) {
	.dash-container {
			padding: 20px 16px;
	}
	
	.welcome-section {
			flex-direction: column;
	}
	
	.stats-grid {
			grid-template-columns: 1fr;
	}
	
	.quick-actions {
			grid-template-columns: 1fr;
	}
	
	.receive-item {
			flex-direction: column;
	}
	
	.receive-image {
			width: 100%;
			height: 150px;
	}
}

.empty-state-wrapper {
	display: flex; 
	flex-direction: column; 
	align-items: center; 
	justify-content: center; 
	min-height: 400px; 
	padding: 40px; 
	text-align: center; 
	background-color: #f9fafb; 
	border: 2px dashed #e5e7eb; 
	border-radius: 12px; 
}
.empty-container {
	display: flex; 
	align-items: center; 
	justify-content: center; 
	width: 80px; 
	height: 80px; 
	margin-bottom: 6px;
}
.shop-btn {
	margin-top: 26px;
	padding: 10px 24px;
	background-color: #2563eb;
	color: #ffffff;
	text-decoration: none;
	font-weight: 500;
	border-radius: 8px;
	font-size: 1.5rem;
}
</style>
<!-- <div class="my-account-header">
	<h2>Dashboard</h2>
  <div class="my-account-header-tags">Manage your settings and preferences</div>
</div> -->

<div class="dashboard-grid">
	<!-- Recent Orders -->
	<div class="dashboard-card">
		<div class="card-header">
			<h2 class="card-title">Recent Orders</h2>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>" class="view-all-link">All Orders →</a>
		</div>

		<?php echo do_shortcode('[recent_orders_priority]'); ?>
	</div>
	
	<!-- Account Information -->
	<?php
	$user = wp_get_current_user();

	$username = $user->display_name;
	$email    = $user->user_email;
	$phone    = get_user_meta($user->ID, 'billing_phone', true);
	?>

	<div class="dashboard-card">
		<div class="card-header">
			<h2 class="card-title">Account Information</h2>
			<a href="<?php echo esc_url( wc_get_endpoint_url('edit-account') ); ?>" class="view-all-link">Edit →</a>
		</div>
		
		<div class="account-info-list">
			<div class="info-item">
					<i class="info-icon ion-android-person"></i>
					<div class="info-content">
						<div class="info-label">Username</div>
						<div class="info-value"><?php echo esc_html($username); ?></div>
					</div>
			</div>
			
			<div class="info-item">
					<i class="info-icon ion-android-mail"></i>
					<div class="info-content">
						<div class="info-label">Email</div>
						<div class="info-value"><?php echo esc_html($email); ?></div>
					</div>
			</div>
			
			<div class="info-item">
					<i class="info-icon ion-ios-telephone"></i>
					<div class="info-content">
						<div class="info-label">Phone</div>
						<div class="info-value">
							<?php echo esc_html($phone ? $phone : 'Not set'); ?>
						</div>
					</div>
			</div>
		</div>

		<div style="margin-top: 40px;">
			<h2 class="card-title">Quick Links</h2>			
			<div class="quick-actions">
				<?php
					// Get the wishlist page URL
					$wishlist_page_id = get_option( 'wcboost_wishlist_page_id' );
					$wishlist_url = get_permalink( $wishlist_page_id );
				?>
				<a href="<?php echo esc_url( $wishlist_url ) ?>" class="action-btn quick wishlist">
					<i class="info-icon ion-heart"></i>
					Wishlist
				</a>
				<a href="<?php echo home_url('/my-account/edit-address'); ?>" class="action-btn quick">
					<i class="info-icon ion-android-pin"></i>
					Addresses
				</a>
				<a href="<?php echo home_url('/my-account/edit-security'); ?>" class="action-btn quick">
					<i class="info-icon ion-locked"></i>
					Security
				</a>
				<a href="<?php echo wp_logout_url(); ?>" class="action-btn quick logout">
					<i class="info-icon ion-android-exit"></i>
					Logout
				</a>
			</div>
		</div>
	</div>
</div>


<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
