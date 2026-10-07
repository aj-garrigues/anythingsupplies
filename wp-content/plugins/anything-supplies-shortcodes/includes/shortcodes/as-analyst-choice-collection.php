<?
add_shortcode( 'as_analyst_choice_collection', function ( $atts ) {

    as_enqueue_assets('as_analyst_choice_collection', 'as-analyst-choice-collection.css', null);

    $atts = shortcode_atts(array(
        'limit' => 4, // Products per box
        'days'  => 14,
    ), $atts);

    // 1. Fetch 12 random products in ONE query to avoid duplicates
    $total_needed = $atts['limit'] * 3;
    $all_products_query = new WP_Query(array(
        'post_type'      => 'product',
        'posts_per_page' => $total_needed,
        'orderby'        => 'rand',
        'date_query'     => array(
            array('after' => $atts['days'] . ' days ago')
        ),
        'post_status'    => 'publish',
    ));

    $products = $all_products_query->posts;
    if (empty($products)) return '<p>No products found.</p>';

    // 2. Split the products into 3 distinct arrays
    $chunks = array_chunk($products, $atts['limit']);
    
    // Define the titles for your three boxes
    $box_titles = [
        "Analyst's Choice" => "Pro-sourced B2B essentials.",
        "Expert Picks"     => "Hand-selected for quality.",
        "Certified Deals"  => "Top-rated by our inspectors."
    ];

    $box_bg = [
        "Analyst's Choice" => "product-bundel-Analyst.webp",
        "Expert Picks"     => "product-bundel-Expert.webp",
        "Certified Deals"  => "product-bundel-Certified.webp"
    ];

    ob_start();
    echo '<div class="main-wrapper">';

    $i = 0;
    foreach ($box_titles as $title => $sub) {
        if (!isset($chunks[$i])) break;
        ?>

    <div class="analyst-card" style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri() . '/images/bg/' . $box_bg[$title]); ?>'); background-size: cover; background-position: center;"   >
        <h2 style="color: #ffffff; padding: 0px; margin: 0;"><?php echo esc_html($title); ?></h2>
        <div style="color: #ffffff; margin-bottom: 10px;"><?php echo esc_html($sub); ?></div>
        
        <div class="product-grid-two">
            <?php foreach ($chunks[$i] as $post_item) : 
                $product = wc_get_product($post_item->ID); 
                ?>
                <div class="grid-item">
                    <a href="<?php echo get_permalink($post_item->ID); ?>">
                        <div class="item-image">
                            <?php echo $product->get_image('woocommerce_thumbnail', array('class' => 'fit-image')); ?>
                        </div>
                        <div class="item-label truncated-text">
                            <?php $title = get_the_title($post_item->ID);
                                echo $title;
                            ?>
                        </div>
                    </a>
                    <div class="product-info-wrapper">
                        <div class="product-info-footer">
                            <?php add_inquiry_list_button($post_item->ID); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="/analysts-choice"  style="color: #ffffff;" class="see-all">See all deals</a>
    </div>

        <?php
        $i++;
    }

    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
});