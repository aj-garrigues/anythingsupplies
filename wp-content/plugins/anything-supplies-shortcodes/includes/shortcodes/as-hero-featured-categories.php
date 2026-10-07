<?

add_shortcode( 'as_hero_featured_categories', function ( $atts ) {
    
    as_enqueue_assets('as_hero_featured_categories', 'as-hero-featured-categories.css', null);

    $feat_categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'meta_query' => [
            [
                'key'   => 'featured_category',
                'value' => 'yes',
            ]
        ],
    ]);

    shuffle($feat_categories);
    $feat_categories = array_slice($feat_categories, 0, 2);

    if(!empty($feat_categories) && !is_wp_error($feat_categories)) { ?>
        <div style="display: flex; gap: 8px;"> <?php
            foreach($feat_categories as $category) :
                $thumbnail_id  = get_term_meta($category->term_id, 'thumbnail_id', true);
                $thumbnail_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';
            ?>
            <div class="hfc_container" style="background: linear-gradient(rgba(0, 0, 0, 0.42),rgba(0, 0, 0, 0.42)),url(<?php echo $thumbnail_url; ?>);background-repeat: no-repeat;background-position: center;background-size: cover;">
                <a href="<?php echo esc_url(get_term_link($category));?>">
                    <h2 class="hfc_name"><?php echo esc_html($category->name);?></h2>
                </a>
                <a href="<?php echo esc_url(get_term_link($category));?>">
                    <button class="hfc_button">View More</button>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
});