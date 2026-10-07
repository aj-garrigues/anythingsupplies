<?

function as_explore_similar() {
    if ( ! is_singular( 'product' ) ) {
        return '';
    }

    global $post;
    $product_id = $post->ID;

    // Get product categories
    $terms = wp_get_post_terms( $product_id, 'product_cat', array(
        'fields' => 'ids'
    ));

    if ( empty( $terms ) ) {
        return '';
    }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 4,
        'post__not_in'   => array( $product_id ),
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $terms,
            ),
        ),
    );

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '';
    }

    ob_start();
    ?>
    <div class="explore-similar-header">
        <h4>Explore Similar</h4>
    </div>
    <div class="explore-similar">
    <?php
    while ( $query->have_posts() ) {
        $query->the_post();
    ?>
    <div class="anal-choice-product-card">
        <a href="<?php echo get_permalink(); ?>">
            <div class="anal-choice-product-image">
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
    <?php
    }
    ?>
    </div>
    <?php
    
    wp_reset_postdata();
    return ob_get_clean();
}