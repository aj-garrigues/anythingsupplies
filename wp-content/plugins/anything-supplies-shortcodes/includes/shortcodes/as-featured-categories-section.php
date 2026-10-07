<?
add_shortcode( 'as_featured_categories_section', function ( $atts ) {

   as_enqueue_assets('as_featured_categories_section', 'as-featured-categories-section.css', null);

	$atts = shortcode_atts([
        'title'      => 'Featured Categories',
        'hide_empty' => 'no', // yes | no
   ], $atts, 'featured_categories');

    $hide_empty = ($atts['hide_empty'] === 'yes');

    $categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => $hide_empty,
        'meta_query' => [
            [
                'key'   => 'featured_category',
                'value' => 'yes',
            ]
        ],
    ]);

    ob_start();
    ?>
    <div class="category-container">
		<div class="header">
			<h2>Featured Categories</h2>
			<p>The most in-demand categories among buyers</p>
		</div>

        <div class="categories-grid">
            <?php
            if (!empty($categories) && !is_wp_error($categories)) :
                foreach ($categories as $category) :

                    // $thumbnail_id  = get_term_meta($category->term_id, 'thumbnail_id', true);
                    // $thumbnail_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';
                    $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                    ?>
                    <a href="<?php echo esc_url(get_term_link($category)); ?>">
                        <div class="cate-item">
                            <div class="category-circle">
                                <?php 
                                    if ($thumbnail_id) {
                                        echo wp_get_attachment_image($thumbnail_id, 'thumbnail', false, []);
                                    } else {
                                        echo '
                                        <img 
                                            src="https://i.fbcd.co/products/original/667ca7502e4e218f01e4fbb26e01e2fc7fe17370f64bf444f60818b9d1b2c2b2.jpg"
                                            alt="placeholder category thumbnail"
                                        >
                                        ';
                                    }
                                ?>
                            </div>
                            <div class="category-name"><?php echo esc_html($category->name); ?></div>
                        </div>
                    </a>
                <?php endforeach;
            else : ?>
                <p>No featured categories found.</p>
            <?php endif; ?>
        </div>
   </div>

	<?php
	return ob_get_clean();
});