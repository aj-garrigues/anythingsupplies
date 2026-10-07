<?

add_shortcode( 'as_collection_page', function ( $atts ) {

   as_enqueue_assets('as_collection_page', 'as-collection-page.css', null);
	// Shortcode attributes with defaults
    $atts = shortcode_atts(array(
        'limit' => 50,
    ), $atts);
    
    // Query for recent products
    $base_args = array(
        'post_type'      => 'product',
        'posts_per_page' => $atts['limit'],
        'no_found_rows'  => true,
        'post_status'   => 'publish',
    );
    
    $anal_choice = new WP_Query(array_merge($base_args, array(
        'orderby' => 'rand',
    )));
    
    // Start output buffering
    ob_start();
    ?>

    <div class="anal-choice-container">
        <div class="anal-choice-products-grid" id="productsGrid">
        <?php
        if ($anal_choice->have_posts()) {
            while ($anal_choice->have_posts()) {
                $anal_choice->the_post();
                ?>
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
                    <div class="product-info-footer" style="margin-bottom: 8px;"><?php echo add_inquiry_list_button(get_the_ID()); ?></div>
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

    <?php
    return ob_get_clean();
});