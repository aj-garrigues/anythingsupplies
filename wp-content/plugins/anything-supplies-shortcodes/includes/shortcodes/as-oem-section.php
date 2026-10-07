<?
add_shortcode( 'as_oem_section', function ( $atts ) {
	
    as_enqueue_assets('as_oem_section', 'as-oem-section.css', 'as-oem-section.js');

    // Shortcode attributes with defaults
    $atts = shortcode_atts(array(
        'limit' => 6,
        'days' => 14,
        'title' => 'Original Equipment Manufacturer Products',
        'subtitle' => 'Factory-direct products built to original specifications.'
    ), $atts);
    
    // Query for recent products
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $atts['limit'],
        'orderby' => 'rand',
        // 'order' => 'DESC',
        'date_query' => array(
            array(
                'after' => $atts['days'] . ' days ago'
            )
        )
    );
    
    $products = new WP_Query($args);
    
    // Start output buffering
    ob_start();
    ?>
    
    <div class="new-products-section">
        <div class="new-products-header">
            <h2 class="new-products-title">
                <!-- <span class="title-new">New</span>  -->
                <span class="title-products">OEM Products</span>
            </h2>
            <p class="new-products-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
            <a href="/oem-products" class="see-all-link">See All</a>
        </div>
        
        <div class="carousel-wrapper">
            <button class="carousel-btn prev">‹</button>
                <div class="new-products-grid" id="newProducts">
                    <?php
                    if ($products->have_posts()) {
                        while ($products->have_posts()) {
                            $products->the_post();
                            global $product;
                            ?>
                            <div class="product-card">
                                <a href="<?php the_permalink(); ?>" class="product-link">
                                    <div class="product-image">
                                        <?php echo get_the_post_thumbnail(get_the_ID(), 'medium'); ?>
                                    </div>
                                    
                                    <div class="product-info">
                                        <div class="product-price">
                                            <?php echo $product->get_price_html(); ?>
                                        </div>
                                        
                                        <div class="product-meta">
                                            <?php
                                            // Get product attributes or custom fields
                                            $meta = get_post_meta(get_the_ID(), '_product_quantity_label', true);
                                            if ($meta) {
                                                echo '<span class="product-quantity">' . esc_html($meta) . '</span>';
                                            }
                                            ?>
                                        </div>
                                        
                                        <h3 class="product-title truncated-text"><?php the_title(); ?></h3>
                                        
                                    </div>
                                    
                                </a>
                                <div class="product-info-footer"><?php echo add_inquiry_list_button(get_the_ID()); ?></div>
                            </div>
                            <?php
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<p>No products found.</p>';
                    }
                    ?>
                </div>
            <button class="carousel-btn next">›</button>
    </div>
	 <!-- script in as-oem-section.js -->
    <?php
    return ob_get_clean();
});