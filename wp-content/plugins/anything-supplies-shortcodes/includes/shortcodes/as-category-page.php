<?
add_shortcode( 'as_category_page', function ( $atts ) {
    
   as_enqueue_assets('as_category_page', 'as-category-page.css', null);

	$atts = shortcode_atts([
        'limit' => 0,
        'hide_empty' => true,
        'parent' => 0,
   ], $atts);

	$categories = get_terms([
		'taxonomy'   => 'product_cat',
		'hide_empty' => filter_var($atts['hide_empty'], FILTER_VALIDATE_BOOLEAN),
		'parent'     => intval($atts['parent']),
		'number'     => intval($atts['limit']),
	]);

	ob_start();
	?>
	<div class="category-container">
		<div class="header">
			<h2>Categories</h2>
			<p>The Complete Directory for Your Business Essentials</p>
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
														src="https://anythingsupplies.com/wp-content/uploads/2026/07/category-placeholder-150x150.jpg"
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