<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp;

$icon_list = [
	'dashboard'      => 'ion-android-desktop',
	'orders'         => 'ion-cube',
	'payment-methods'=> 'ion-card',
	'wholesale-management'      => 'ion-android-chat',
	'company-info'      => 'ion-briefcase',
	'my-invoice'      => 'icon-clipboard-pencil',
	'edit-address'   => 'ion-ios-location-outline',
	'edit-account'   => 'ion-android-person',
	'my-info-parent'   => 'icon-portrait2',
	'billing-info'   => 'ion-card',
	'shipping-info'	 => 'icon-truck',
	'customer-logout'=> 'ion-android-exit',
];


do_action( 'woocommerce_before_account_navigation' );

	$user = get_user_by( 'ID', get_current_user_id() );
		if ( empty( $user ) ) {
			return; 
		} 
		?>
	<style>
		/* ── SIDEBAR ── */
		.sidebar {
			width: 220px;
			flex-shrink: 0;
			background: #fff;
			/* border-radius: 10px; */
			/* border: 1px solid #e8e8e8; */
			overflow: hidden;
		}
		.sidebar-item {
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 0px 6px;
			cursor: pointer;
			color: #374151;
			font-size: 14px;
			transition: background 0.15s;
			border-radius: 8px;
		}
		.sidebar-item span {
			color: #111827;
    		font-size: 14px;
			font-weight: 500;
			margin: auto 0;
		}
		.sidebar-item i {
			padding: 4px 6px;
			background-color: #fff;
			border: 1px solid #ababab;
			border-radius: 6px;
		}
		.sidebar-item:hover {
			background-color: #d3f1f5 !important;
		}
		.sidebar-item.active, .sidebar-item.active span {
			background: #d3f1f5 !important;
			color: #00719a;
			font-weight: 600;
		}
		.sidebar-item i {
			color: #00719a;
		}
		.sidebar-divider { border: none; border-top: 1px solid #e8e8e8; margin: 4px 0; }

		/* Mobile sidebar: horizontal scrollable strip */
		.sidebar-mobile {
			display: none;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			scrollbar-width: none;
			background: #fff;
			border: 1px solid #e8e8e8;
			border-radius: 12px;
			margin-bottom: 16px;
			padding: 4px 8px;
			gap: 4px;
			white-space: nowrap;
		}
		.sidebar-mobile::-webkit-scrollbar { display: none; }
		.sidebar-mobile-item {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 8px 12px;
			border-radius: 8px;
			font-size: 13px;
			color: #374151;
			cursor: pointer;
			white-space: nowrap;
			flex-shrink: 0;
		}
		.sidebar-mobile-item.active {
			background: #eff6ff;
			color: #2563eb;
			font-weight: 600;
		}
		.sidebar-mobile-item svg {
			width: 15px; height: 15px;
			stroke: currentColor; fill: none;
			flex-shrink: 0;
		}

		@media(max-width: 770px) {
			.MyAccount-navigation nav .sidebar {
				width: 100%;
				display: grid !important;
				grid-template-columns: repeat(2, 1fr);
				gap: 12px;
			}
		}
	</style>
<div class="MyAccount-navigation" >
   <div class="account-info">
		<div id="photo-preview-wrapper">
			<?php 
			$current_photo = get_user_meta( get_current_user_id(), 'user_custom_photo', true );
			$img_src = $current_photo ? $current_photo : get_stylesheet_directory_uri() . '/images/no-photo.png';
			?>
			<img id="user-avatar-preview" src="<?php echo esc_url($img_src); ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover; border:1px solid #ccc;" alt="<?php esc_attr_e( 'User Avatar', 'martfury' ); ?>">
		</div>
   
		<div class="account-name">
			<h3><?php echo esc_html( $user->display_name ); ?></h3>
			<span style="font-size: 13px;"><?php echo esc_html( $user->user_email ); ?></span>
		</div>
	</div>
	<!-- <hr> -->

<nav class="" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
	<ul class="sidebar">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<?php
			$icons_data = (isset($icon_list[$endpoint]) ? '<i class="' . esc_attr($icon_list[$endpoint]) . '"></i>' : '');
			?>
			<li class="sidebar-item <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'active' : ''; ?> my-account-menu-item">
				<a style="width: 100%;" href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
					<?php echo $icons_data; ?>
					<span><?php echo esc_html( $label ); ?></span>
					<?php echo ('my-info-parent' === $endpoint) ? '<i class="icon-chevron-down toggle-child-menu"></i>' : '';?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
</div>
<?php do_action( 'woocommerce_after_account_navigation' ); ?>
