<?
add_shortcode( 'as_home_products_section', function ( $atts ) {
    
    as_enqueue_assets('as_home_products_section', 'as-home-products-section.css', null);

    // Shortcode attributes with defaults
    $atts = shortcode_atts(array(
        'limit' => 30,
    ), $atts);
    
    // Query for recent products
    $base_args = array(
        'post_type'      => 'product',
        'posts_per_page' => $atts['limit'],
        'no_found_rows'  => true,
        'post_status'   => 'publish',
    );
    
    $home_products = new WP_Query(array_merge($base_args, array(
        'orderby' => 'rand',
    )));
    
    // Start output buffering
    ob_start();
    ?>
            <div class="hpg-header" style="text-align: center;">
            <h2 style="font-size:2.5em; margin-bottom: 10px;">Explore Our Diverse Product Range</h2>
            <p>Discover a wide variety of products tailored to meet your business needs.</p>
        </div>
    <div class="hpg-container">

        <div class="clearfix" style="clear: both;"></div>
        <div class="hpg-grid">
        <?php
        if ($home_products->have_posts()) {
            while ($home_products->have_posts()) {
                $home_products->the_post();
                ?>
                <div class="grid-item">
                    <a href="<?php the_permalink(); ?>">
                            <div class="item-image">
                                    <?php echo get_the_post_thumbnail(get_the_ID(), 'medium'); ?>
                            </div>
                            <div class="item-label truncated-text"><?php the_title(); ?></div> 
                    </a>
                    <div class="product-info-wrapper">
                        <div class="product-info-footer">
                            <?php echo add_inquiry_list_button(get_the_ID()); ?>
                        </div>
                    </div>
                </div>  
                <?php
            }
            wp_reset_postdata();
        } else {
            echo '<p>No products found.</p>';
        }
        ?>  
        </div>
        
    </div>
        <div style="text-align: center; margin-top: 15px;"><a href="/shop/" class="see-all btn-orange button">See all products</a></div> 
    <?php
    return ob_get_clean();
});