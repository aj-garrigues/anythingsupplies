<?
add_shortcode( 'as_hot_products', function ( $atts ) {

    as_enqueue_assets('as_hot_products', 'as-hot-products.css', null);

    $atts = shortcode_atts([
        'limit' => 4,
        'orderby' => 'rand'
    ], $atts );

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => (int) $atts['limit'],
        'orderby'        => $atts['orderby'],
        'order'          => $atts['order'],
    ];

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '';
    }

    ob_start();
    echo '<ul class="hot-products-list" >';

    while ( $query->have_posts() ) {
        $query->the_post();
        global $product; 

        echo '<li class="hot-product-item">
				  <a href="' . get_permalink() . '" class="hot-product-link">
                  <div class="hot-item-wrapper">
				  ';

        // Product Image
        if ( has_post_thumbnail() ) {
            echo '<div class="hot-product-image">';
            echo get_the_post_thumbnail( get_the_ID(), 'woocommerce_thumbnail' );
            echo '</div>';
        }
        
        // Product Info
        $product_desc = wp_strip_all_tags( $product->get_description() );
			echo '
				<div class="hot-product-info">
					<span class="hot-product-title truncated-text">' . get_the_title() . '</span>
				</div>
            </div>
			</a>
			</li>';
    }

    echo '</ul>';

    wp_reset_postdata();

    return ob_get_clean();
});