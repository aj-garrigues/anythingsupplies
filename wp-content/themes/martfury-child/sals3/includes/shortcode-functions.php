<?php

add_shortcode('analyst_choice', 'render_analyst_choice_shortcode');

function render_analyst_choice_shortcode($atts = array()) {
    $atts = shortcode_atts(array(
        'hot_category' => 'pets',    
        'oem_category' => 'battery', 
        'limit' => 3,               
        'hot_title' => 'Hot Selling', 
        'oem_title' => 'OEM Products', 
        'low_title' => 'Low Priced Products' 
    ), $atts, 'analyst_choice');
    
    ob_start();
    ?>

    <div class="ac-section ac-section-main">
        <div class="ac-header">
            <h3 class="ac-title">Analyst's Choice</h3>
            <p class="ac-subtitle">
                Goods & services handpicked by B2B sourcing & procurement specialists.
            </p>
        </div>
        
        <div class="ac-layout">
            <aside class="ac-sidebar">
                <?php
                $labels = ['cat_filter_trending', 'cat_filter_hot-picks', 'cat_filter_innovative'];
                $taxonomy = 'product_cat';

                $product_categories = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                ));

                $filtered_categories = array();

                foreach ($product_categories as $category) {
                    $all_meta = get_term_meta($category->term_id);
                    $has_yes = false;

                    foreach ($all_meta as $key => $value) {
                        if (strpos($key, 'cat_filter_') === 0) {
                            $has_yes = true;
                            break;
                        }
                    }

                    if ($has_yes) {
                        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                        $thumbnail_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';
                        $filtered_categories[] = [
                            'term_id' => $category->term_id,
                            'name'    => $category->name,
                            'cat_desc' => $category->description,
                            'cat_thumbnail' => $thumbnail_url
                        ];
                    }
                } 

                foreach ($filtered_categories as $cat) :
                    ?>
                        <a href="<?php echo esc_url(get_term_link($cat['term_id'], $taxonomy)); ?>" class="ac-sidebar-item">
                            <?php if ($cat['cat_thumbnail']): ?>
                                <img src="<?php echo esc_url($cat['cat_thumbnail']); ?>" alt="<?php echo esc_attr($cat['name']); ?>" style="min-width:0px; object-fit:cover; border-radius:2px;">
                            <?php endif; ?>
                            <div style="display: flex; flex-direction: column; ">
                                <h2 class="ac-sidebar-item-title" style="margin:0px; font-size:14px;"><?php echo esc_html($cat['name']); ?></h2>
                                <p class="ac-category-desc"><?php echo esc_html(limit_chars($cat['cat_desc'], 50)); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <hr>    
            </aside>

            <main class="ac-grid">
                <h2 class="ac-grid-title"><?php echo esc_html($atts['hot_title']); ?></h2>

                <div class="ac-product-grid">
                    <?php
                    $products = wc_get_products([
                        'limit'   => intval($atts['limit']),
                        'orderby' => 'popularity',
                        'status'  => 'publish',
                        'category' => [$atts['hot_category']]
                    ]);

                    foreach ( $products as $product ) : ?>
                        <div class="ac-product-card">
                            <?php echo $product->get_image('medium'); ?>
                            <h4 class="ac-product-title"><?php echo esc_html( $product->get_name() ); ?></h4>
                            <a href="<?php echo get_permalink( $product->get_id() ); ?>" class="ac-inquire-btn">
                                Inquire Now
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>

    <?php
    return ob_get_clean();
}


add_shortcode('oem_products', 'render_oem_products');

function render_oem_products($atts = []) {

    $atts = shortcode_atts([
        'title'    => 'OEM Products',
        'category' => 'battery',
        'limit'    => 3,
        'ids'      => '15772,15771,15769' // sidebar products
    ], $atts);

    $sidebar_ids = array_filter(array_map('intval', explode(',', $atts['ids'])));

    ob_start();
    ?>

    <div class="ac-section ac-section-oem">

        <div class="ac-header">
            <h3 class="ac-title"><?php echo esc_html($atts['title']); ?></h3>
            <p class="ac-subtitle">
                Goods & services handpicked by B2B sourcing & procurement specialists.
            </p>
        </div>

        <div class="ac-layout">

            <!-- Sidebar -->
            <aside class="ac-sidebar">
                <?php foreach ($sidebar_ids as $id) :
                    $product = wc_get_product($id);
                    if (!$product) continue;
                ?>
                    <a href="<?php echo get_permalink($product->get_id()); ?>" class="ac-sidebar-item">
                        <?php echo $product->get_image([60,60]); ?>
                        <span class="ac-sidebar-item-title">
                            <?php echo esc_html($product->get_name()); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </aside>

            <!-- Main grid -->
            <main class="ac-grid">
                <div class="ac-product-grid">
                    <?php
                    $products = wc_get_products([
                        'limit'   => intval($atts['limit']),
                        'status'  => 'publish',
                        'orderby' => 'date',
                        'order'   => 'DESC',
                        'category'=> [$atts['category']]
                    ]);

                    foreach ($products as $product) :
                    ?>
                        <div class="ac-product-card">
                            <?php echo $product->get_image('medium'); ?>
                            <h4 class="ac-product-title"><?php echo esc_html($product->get_name()); ?></h4>
                            <a href="<?php echo get_permalink($product->get_id()); ?>" class="ac-inquire-btn">
                                Inquire Now
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>

        </div>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('low_products', 'render_low_products');

function render_low_products($atts = []) {

    $atts = shortcode_atts([
        'title' => 'Low Priced Products',
        'limit' => 4
    ], $atts);

    ob_start();
    ?>

    <div class="ac-section ac-section-low">

        <div class="ac-header">
            <h3 class="ac-title"><?php echo esc_html($atts['title']); ?></h3>
            <p class="ac-subtitle">
                Goods & services handpicked by B2B sourcing & procurement specialists.
            </p>
        </div>

        <div class="ac-layout">

            <!-- Sidebar -->
            <aside class="ac-sidebar">
                <?php
                $sidebar_products = wc_get_products([
                    'limit'   => 4,
                    'status'  => 'publish',
                    'orderby' => 'price',
                    'order'   => 'ASC',
                ]);

                foreach ($sidebar_products as $product) :
                ?>
                    <a href="<?php echo get_permalink($product->get_id()); ?>" class="ac-sidebar-item">
                        <?php echo $product->get_image([60,60]); ?>
                        <span class="ac-sidebar-item-title">
                            <?php echo esc_html($product->get_name()); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </aside>

            <!-- Main grid -->
            <main class="ac-grid">
                <div class="ac-product-grid">
                    <?php
                    $products = wc_get_products([
                        'limit'   => intval($atts['limit']),
                        'status'  => 'publish',
                        'orderby' => 'price',
                        'order'   => 'ASC',
                    ]);

                    foreach ($products as $product) :
                    ?>
                        <div class="ac-product-card">
                            <?php echo $product->get_image('medium'); ?>
                            <h4 class="ac-product-title"><?php echo esc_html($product->get_name()); ?></h4>
                            <a href="<?php echo get_permalink($product->get_id()); ?>" class="ac-inquire-btn">
                                Inquire Now
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>

        </div>
    </div>

    <?php
    return ob_get_clean();
}


// Define limit_chars if not already defined
if (!function_exists('limit_chars')) {
    function limit_chars($string, $limit = 100, $end = '...') {
        // Use mb_ functions if available for better multibyte support
        if (function_exists('mb_strlen')) {
            if (mb_strlen($string) <= $limit) {
                return $string;
            }
            return mb_substr($string, 0, $limit) . $end;
        } else {
            // Fallback to regular functions
            if (strlen($string) <= $limit) {
                return $string;
            }
            return substr($string, 0, $limit) . $end;
        }
    }
}
?>