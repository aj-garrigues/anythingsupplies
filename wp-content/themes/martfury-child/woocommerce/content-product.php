<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?> >
	<div class="anal-choice-product-card">
		<a href="<?php echo get_permalink(); ?>">
			<div class="anal-choice-product-image">
					<!-- <div class="anal-choice-badge"></div> -->
					<div class="anal-image" style="font-size: 80px;">
						<?php echo get_the_post_thumbnail(get_the_ID(), 'medium'); ?>
					</div>
			</div>
			<div class="anal-choice-product-info" style="padding-bottom: 0px;">
					<div class="anal-choice-product-title"><?php the_title(); ?></div>
			</div>
		</a>
		<div class="product-info-footer" style="margin-bottom: 8px;">
			<?php add_inquiry_list_button(get_the_ID()); ?>
		</div>
	</div>
</li>
