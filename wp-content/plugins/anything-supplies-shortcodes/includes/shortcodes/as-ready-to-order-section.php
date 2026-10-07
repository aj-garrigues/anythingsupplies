<?
add_shortcode( 'as_ready_to_order_section', function ( $atts ) {

    as_enqueue_assets('as_ready_to_order_section', 'as-ready-to-order-section.css', null);

    // Shortcode attributes with defaults
    $atts = shortcode_atts(array(
        'limit' => 6,
    ), $atts);
    
    // Query for recent products
    $base_args = array(
        'post_type'      => 'product',
        'posts_per_page' => $atts['limit'],
        'no_found_rows'  => true,
        'post_status'   => 'publish',
    );
    
    $ready_to_order = new WP_Query(array_merge($base_args, array(
        'orderby' => 'rand',
    )));
    
    // Start output buffering
    ob_start();
    ?>
    <div class="rto-card">
        <div class="rto-card-heading">
            <p class="rto-sub-heading" style="color: #000;">Order now</p>
            <h2>Ready To Order Products</h2>
            <a href="/shop"><button type="button" class="rto-button">Explore now</button></a>
        </div>
        
        <div class="rto-grid">
        <?php
        if ($ready_to_order->have_posts()) {
            while ($ready_to_order->have_posts()) {
                $ready_to_order->the_post();
                ?>
                    <div class="grid-item">
                        <a href="<?php the_permalink(); ?>">
                            <div class="item-image">
                                    <?php echo get_the_post_thumbnail(get_the_ID(), 'medium'); ?>
                            </div>
                            <div class="item-label truncated-text">
                                <?php the_title(); ?>
                            </div>
                        </a>
                        <div class="product-info-footer"><?php add_inquiry_list_button(get_the_ID()); ?></div>
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