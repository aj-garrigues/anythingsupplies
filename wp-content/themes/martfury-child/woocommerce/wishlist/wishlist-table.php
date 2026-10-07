<?php
/**
 * Template for displaying wishlist table.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wishlist/wishlist-table.php.
 *
 * @author  WCBoost
 * @package WCBoost\Wishlist\Templates
 * @version 1.1.5
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $wishlist ) ) {
	return;
}
?>

<?php do_action( 'wcboost_wishlist_before_wishlist_table', $wishlist ); ?>
<style>
	#content {
		padding-top: 30px;
		padding-bottom: 30px;
	}
	.wcboost-wishlist-edit-link-wrapper {
		display: none;
	}
</style>
<div class="header-left">
		<h1>My Wishlist</h1>
		<p><span id="itemCount"><?php echo count($wishlist->get_items()); ?></span> items saved</p>
</div>
<div class="wishlist-card">
	<table class="wishlist-table">
		<thead>
			<tr>
				<!-- <th class="checkbox-cell">
						<input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
				</th> -->
				<th class="hide-mobile"></th>
				<th>Product</th>
				<!-- <th>Price</th> -->
				<th class="hide-mobile">Stock Status</th>
				<th class="hide-mobile">Actions</th>
			</tr>
		</thead>
		<tbody id="wishlistBody">
			<?php
			foreach ( $wishlist->get_items() as $item_key => $item ) :
			/** @var WC_Product */
			$_product = $item->get_product();

			if ( ! $_product || ! $_product->exists() ) {
				continue;
			}

			$product_permalink = $_product->is_visible() ? $_product->get_permalink() : '';
			?>
			<tr>
				<td class="hide-mobile"></td>
				<td>
					<div class="product-cell">
						<?php
						if ( ! $product_permalink ) {
							echo wp_kses_post( $_product->get_image() );
						} else {
							echo wp_kses_post( sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_image() ) );
						}
						?>
						<div class="product-info">
							<!-- <h3>AS-B839 Directors Desk</h3> -->
								<?php
							if ( ! $product_permalink ) {
								echo wp_kses_post( $_product->get_name() );
							} else {
								echo wp_kses_post( sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ) );
							}

							if ( $args['show_variation_data'] && $_product->is_type( 'variation' ) ) {
								echo wp_kses_post( wc_get_formatted_variation( $_product ) );
							}
							?>
							<div class="show-mobile-nav" style="margin-top: 10px; justify-content: space-between;">
								<span class="stock-badge in-stock">
									<!-- In stock -->
									<?php
									$availability = $_product->get_availability();
									printf( '<span class="%s">%s</span>', esc_attr( $availability['class'] ), $availability['availability'] ? esc_html( $availability['availability'] ) : esc_html__( 'In Stock', 'wcboost-wishlist' ) );
									?>
								</span>

								<div class="actions-cell">
									<button class="icon-btn add-to-cart inquiry-trigger" id="wishlist-inquiry-btn" type="button" data-product-id="<?php echo $_product->id ?>"  style="text-wrap: nowrap;">
										+ Inquiry
									</button>
									<?php
										echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											'wcboost_wishlist_item_remove_link',
											sprintf(
												'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" rel="nofollow">&times;</a>',
												esc_url( $item->get_remove_url() ),
												esc_html__( 'Remove this item', 'wcboost-wishlist' ),
												esc_attr( $_product->get_id() ),
												esc_attr( $_product->get_sku() )
											),
											$item_key
										);
										?>
								</div>
							</div>
							<!-- <div class="product-sku">MOQ: </div> -->
						</div>
					</div>
				</td>
				<!-- <td class="price-cell"> -->
						<!-- $1,499.00 -->
							<!-- <?php echo wp_kses_post( $_product->get_price_html() ); ?> -->
						<!-- <div class="price-note">MOQ: 10 units</div> -->
				<!-- </td> -->
				<td class="hide-mobile">
					<span class="stock-badge in-stock">
						<!-- In stock -->
						<?php
						$availability = $_product->get_availability();
						printf( '<span class="%s">%s</span>', esc_attr( $availability['class'] ), $availability['availability'] ? esc_html( $availability['availability'] ) : esc_html__( 'In Stock', 'wcboost-wishlist' ) );
						?>
					</span>
				</td>
				<?php if ( $wishlist->can_edit() ) : ?>
				<td class="hide-mobile">
					<div class="actions-cell">
						<button class="icon-btn add-to-cart inquiry-trigger" id="wishlist-inquiry-btn" type="button" data-product-id="<?php echo $_product->id ?>"  style="margin-right: 1rem; text-wrap: nowrap;">
							+ Inquiry
						</button>
						<?php
							echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'wcboost_wishlist_item_remove_link',
								sprintf(
									'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" rel="nofollow">&times;</a>',
									esc_url( $item->get_remove_url() ),
									esc_html__( 'Remove this item', 'wcboost-wishlist' ),
									esc_attr( $_product->get_id() ),
									esc_attr( $_product->get_sku() )
								),
								$item_key
							);
							?>
					</div>
				</td>
				<?php endif; ?>
			</tr>
			<?php
			endforeach;
			?>
		</tbody>
	</table>
</div>

<?php do_action( 'wcboost_wishlist_after_wishlist_table', $wishlist ); ?>

<?php if ( $wishlist->can_edit() && $args['columns']['quantity'] ) : ?>
	<div class="wcboost-wishlist-actions">
		<button type="submit" class="button alt" name="update_wishlist" value="<?php esc_attr_e( 'Update wishlist', 'wcboost-wishlist' ); ?>"><?php esc_html_e( 'Update wishlist', 'wcboost-wishlist' ); ?></button>
		<input type="hidden" name="wishlist_id" value="<?php echo esc_attr( $wishlist->get_id() ); ?>" />
		<?php wp_nonce_field( 'wcboost-wishlist-update' ); ?>
	</div>
<?php endif; ?>
