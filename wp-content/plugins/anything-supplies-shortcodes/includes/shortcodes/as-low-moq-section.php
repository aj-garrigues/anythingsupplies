<?
add_shortcode( 'as_low_moq_section', function ( $atts ) {
    
    as_enqueue_assets('as_low_moq_section', 'as-low-moq-section.css', null);

    // Shortcode attributes with defaults
    $atts = shortcode_atts(array(
        'limit' => 3,
    ), $atts);
    
    // Query for recent products
    $base_args = array(
        'post_type'      => 'product',
        'posts_per_page' => $atts['limit'],
        'no_found_rows'  => true,
        'post_status'   => 'publish',
    );
    
    $low_moq_1 = new WP_Query(array_merge($base_args, array(
        'orderby' => 'rand',
    )));
    $low_moq_2 = new WP_Query(array_merge($base_args, array(
        'orderby' => 'rand',
    )));
    
    // Start output buffering
    ob_start();
    ?>
    <div class="low_moq-container">
        <div class="low_moq-card">
            <h2>Low MOQ Products</h2>
            <p>Big quality for small batches—perfect for growing brands.</p>
            
            <div class="low_moq-grid">
            <?php
            if ($low_moq_1->have_posts()) {
                while ($low_moq_1->have_posts()) {
                    $low_moq_1->the_post();
                    ?>
                    <div class="grid-item">
                        <a href="<?php the_permalink(); ?>">
                            <div class="item-image">
                                <?php echo get_the_post_thumbnail(get_the_ID(), 'medium'); ?>
                            </div>
                            <div class="item-label truncated-text"><?php the_title(); ?></div>
                            
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

            <a href="/low-moq/" class="see-all">See all deals</a>
        </div>

        <div class="low_moq-card">
            <h2>Starter-Friendly Picks</h2>
            <p>Premium products accessible at any stage of business growth.</p>
            
            <div class="low_moq-grid">
            <?php
            if ($low_moq_2->have_posts()) {
                while ($low_moq_2->have_posts()) {
                    $low_moq_2->the_post();
                    ?>
                    <div class="grid-item">
                        <a href="<?php the_permalink(); ?>">
                            <div class="item-image">
                                <?php echo get_the_post_thumbnail(get_the_ID(), 'medium'); ?>
                            </div>
                            <div class="item-label truncated-text"><?php the_title(); ?></div>
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

            <a href="/low-moq/" class="see-all">See all deals</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
});