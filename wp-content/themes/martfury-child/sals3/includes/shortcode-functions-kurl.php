<?php

add_shortcode( 'hero_categories', function ( $atts ) {

    $atts = shortcode_atts( [
        'categories' => '', // comma-separated slugs
        'number'     => 18,
    ], $atts );

    $args = [
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'number'     => intval( $atts['number'] ),
    ];

    // If specific categories were passed
    if ( ! empty( $atts['categories'] ) ) {
        $slugs = array_map( 'trim', explode( ',', $atts['categories'] ) );
        $args['slug'] = $slugs;
    }

    $terms = get_terms( $args );

    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return '';
    }

    ob_start();

    echo '<ul class="hero-product-categories">';

    foreach ( $terms as $term ) {
        echo '<li class="category-item">';
        echo '<a href="' . esc_url( get_term_link( $term ) ) . '">';
        echo esc_html( $term->name );
        echo '</a>';
        echo '</li>';
    }

    echo '</ul>';

    return ob_get_clean();
});

add_shortcode( 'hot_products', function ( $atts ) {

    $atts = shortcode_atts([
        'limit' => 4,
        'orderby' => 'rand'
    ], $atts );

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => (int) $atts['limit'],
        'orderby'        => $atts['orderby'],
        'order'          => $atts['order'],
    ];

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '';
    }

    ob_start();
    echo '<ul class="hot-products-list" >';

    while ( $query->have_posts() ) {
        $query->the_post();
        global $product; 

        echo '<li class="hot-product-item">
				  <a href="' . get_permalink() . '" class="hot-product-link">
                  <div class="hot-item-wrapper">
				  ';

        // Product Image
        if ( has_post_thumbnail() ) {
            echo '<div class="hot-product-image">';
            echo get_the_post_thumbnail( get_the_ID(), 'woocommerce_thumbnail' );
            echo '</div>';
        }
        
        // Product Info
        $product_desc = wp_strip_all_tags( $product->get_description() );
			echo '
				<div class="hot-product-info">
					<span class="hot-product-title truncated-text">' . get_the_title() . '</span>
				</div>
            </div>
			</a>
			</li>';
    }

    echo '</ul>';

    wp_reset_postdata();

    return ob_get_clean();
});

// Register the shortcode
add_shortcode('custom_oem_products', 'display_new_products');

function display_new_products($atts) {
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
    <script>
        const carousel = document.getElementById('newProducts');
        const cardWidth = carousel.querySelector('.product-card').offsetWidth + 25;

        document.querySelector('.next').addEventListener('click', () => {
            carousel.scrollBy({ left: cardWidth, behavior: 'smooth' });
        });

        document.querySelector('.prev').addEventListener('click', () => {
            carousel.scrollBy({ left: -cardWidth, behavior: 'smooth' });
        });
    </script>

    <?php
    return ob_get_clean();
}
function global_manufacturing_footprint_shortcode() {
    ob_start();
    ?>
    <div class="gmf-container">
        <div class="gmf-header">
            <h2>Our Global Manufacturing Footprint</h2>
            <p>Facilities across six countries delivering quality worldwide</p>
        </div>

        <div class="gmf-grid">
            <div class="gmf-card">
                <div class="gmf-flag">🇨🇳</div>
                <div class="gmf-country">CHINA</div>
            </div>

            <div class="gmf-card">
                <div class="gmf-flag">🇺🇸</div>
                <div class="gmf-country">USA</div>
            </div>

            <div class="gmf-card">
                <div class="gmf-flag">🇳🇱</div>
                <div class="gmf-country">NETHERLANDS</div>
            </div>

            <div class="gmf-card">
                <div class="gmf-flag">🇰🇷</div>
                <div class="gmf-country">SOUTH KOREA</div>
            </div>

            <div class="gmf-card">
                <div class="gmf-flag">🇻🇳</div>
                <div class="gmf-country">VIETNAM</div>
            </div>

            <div class="gmf-card">
                <div class="gmf-flag">🇮🇳</div>
                <div class="gmf-country">INDIA</div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('manufacturing_footprint', 'global_manufacturing_footprint_shortcode');
function home_aec_box($atts) {
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

<div class="analyst-card" 
    style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri() . '/images/bg/' . $box_bg[$title]); ?>'); background-size: cover; background-position: center;"
    fetchpriority="high"
>
    <h2 style="color: #ffffff; padding: 0px; margin: 0;"><?php echo esc_html($title); ?></h2>
    <div style="color: #ffffff; margin-bottom: 10px;"><?php echo esc_html($sub); ?></div>
    
    <div class="product-grid-two">
        <?php foreach ($chunks[$i] as $post_item) : 
            $product = wc_get_product($post_item->ID); 
            ?>
            <div class="grid-item">
                <a href="<?php echo get_permalink($post_item->ID); ?>">
                    <div class="item-image">
                        <?php echo $product->get_image('thumbnail', array('class' => 'fit-image')); ?>
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
}

add_shortcode('analyst_choice_new', 'home_aec_box');

function load_low_moq_products($atts) {
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
}
add_shortcode('low_moq_products_new', 'load_low_moq_products');


function load_buyer_services() {
    ob_start();
    ?>

    <div class="buyer-container">
        <div class="content">
            <h1>Order and fulfill everything you need for your business from a single platform</h1>
            
            <div class="features">
                <div class="feature-item">
                    <div class="icon-wrapper">
                        <div class="icon">🔍</div>
                    </div>
                    <div class="feature-text">
                        <div class="feature-title">Search for matches</div>
                        <div class="feature-description">
                            Search or send us an inquiry for any products that you need.
                        </div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="icon-wrapper">
                        <div class="icon">📧</div>
                    </div>
                    <div class="feature-text">
                        <div class="feature-title">Contact and confirm</div>
                        <div class="feature-description">
                            We will contact you and confirm all the details of your request.
                        </div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="icon-wrapper">
                        <div class="icon">💳</div>
                    </div>
                    <div class="feature-text">
                        <div class="feature-title">Pay with confidence</div>
                        <div class="feature-description">
                            Pay with confidence and fulfill with transparency.
                        </div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="icon-wrapper">
                        <div class="icon">👥</div>
                    </div>
                    <div class="feature-text">
                        <div class="feature-title">Manage with ease</div>
                        <div class="feature-description">
                            Manage order status and shipments with ease.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="visuals">
            <div class="visual-card" style="background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%); display: flex; align-items: center; justify-content: center;">
                 <img class="visuals-img" src="https://www.shutterstock.com/image-photo/audit-documents-magnifying-glass-verification-600nw-2708642557.jpg" alt="searching" style="height: 100%;">
            </div>
            <div class="visual-card" style="background: linear-gradient(135deg, #f5e6d3 0%, #e8d4b8 100%); display: flex; align-items: center; justify-content: center;">
                 <img class="visuals-img" src="https://cdn.wilsonsons.com.br/wp-content/uploads/2023/09/stock-inventory.jpeg" alt="agreement" style="height: 100%;">
            </div>
            <div class="visual-card" style="background: linear-gradient(135deg, #f5f0e8 0%, #e8ddc8 100%); display: flex; align-items: center; justify-content: center;">
                 <img class="visuals-img" src="https://media.istockphoto.com/id/1916729901/photo/meeting-success-two-business-persons-shaking-hands-standing-outside.jpg?s=612x612&w=0&k=20&c=Zpa1CaJlGI4mYdzqJGjCIEWFRCkqo3DmHxLopdki-SE=" alt="inventory" style="height: 100%;">
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('buyer_services', 'load_buyer_services');

function load_ready_to_order_products($atts) {
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
}
add_shortcode('ready_to_order_products', 'load_ready_to_order_products');

function render_featured_categories_shortcode2($atts = []) {

    $atts = shortcode_atts([
        'title'      => 'Featured Categories',
        'hide_empty' => 'no', // yes | no
    ], $atts, 'featured_categories');

    $hide_empty = ($atts['hide_empty'] === 'yes');

    $categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => $hide_empty,
        'meta_query' => [
            [
                'key'   => 'featured_category',
                'value' => 'yes',
            ]
        ],
    ]);

    ob_start();
    ?>
    <div class="category-container">
        <div class="header">
            <h2>Featured Categories</h2>
            <p>The most in-demand categories among buyers</p>
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
                                            src="https://i.fbcd.co/products/original/667ca7502e4e218f01e4fbb26e01e2fc7fe17370f64bf444f60818b9d1b2c2b2.jpg"
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
}
add_shortcode('featured_categories2', 'render_featured_categories_shortcode2');

function load_home_product_grid($atts) {
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
}
add_shortcode('home_product_grid', 'load_home_product_grid');

function load_analyst_choice_collection($atts) {
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
}

add_shortcode('analyst_choice_collection', 'load_analyst_choice_collection');

function load_low_moq_collection($atts) {
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
    <!-- <div class="anal-choice-hero">
        <h1>Low MOQ</h1>
        <h2>Scale your business at your own pace.</h2>
    </div> -->

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
}

add_shortcode('low_moq_collection', 'load_low_moq_collection');

function load_oem_collection($atts) {
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
    <!-- <div class="anal-choice-hero">
        <h1>OEM Products</h1>
        <h2>Custom-built solutions tailored to your brand’s specs.</h2>
    </div> -->

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
}

add_shortcode('oem_collection', 'load_oem_collection');

function render_cateory_collection($atts = []) {

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
}
add_shortcode('category_collection', 'render_cateory_collection');

function martfury_extra_inquiry_basket() {
    $inquiry_list = count(WC()->session->get('custom_inquiry_list', array())) ?? 0;

    printf(
        '<li class="extra-menu-item menu-item-basket woocommerce">
            <a class="basket-contents" id="icon-basket-contents" href="/inquiry-basket/">
                <i class="icon-cart extra-icon"></i>
                <span class="mini-item-counter mf-background-primary">
                    '.$inquiry_list.'
                </span>
            </a>
        </li>',
    );
}

function render_explore_similar() {
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

add_shortcode( 'explore_similar', 'render_explore_similar' );

function render_faq_page() {

    wp_enqueue_style(
        'faq_page_style',
        get_stylesheet_directory_uri() . '/sals3/assets/css/faq_page.css',
        array(),
        '1.0'
    );

    ob_start();
    ?>

    <div class="faq-wrapper">
    <header class="faq-header">
        <div class="eyebrow">Help Center</div>
        <h1 class="faq-main-h1">Frequently <em>Asked </em>Questions</h1>
        <p class="subtitle">Everything you need to know. Can't find an answer? Reach out to our team.</p>
    </header>

    <div class="faq-container">
        <!-- Tab List -->
        <nav class="tab-list" role="tablist">
        <button class="tab-btn active" role="tab" data-tab="products" aria-selected="true">
            <span class="tab-icon">◉</span> Products & Availability
            <span class="tab-count">4</span>
        </button>
        <button class="tab-btn" role="tab" data-tab="orders" aria-selected="false">
            <span class="tab-icon">◉</span> Orders & Inquiries
            <span class="tab-count">4</span>
        </button>
        <button class="tab-btn" role="tab" data-tab="shipping" aria-selected="false">
            <span class="tab-icon">◉</span> Shipping & Delivery
            <span class="tab-count">4</span>
        </button>
        <button class="tab-btn" role="tab" data-tab="payments" aria-selected="false">
            <span class="tab-icon">◉</span> Payments & Account Support
            <span class="tab-count">4</span>
        </button>
        </nav>

        <!-- Tab Panels -->
        <div class="tab-panels">

        <div class="tab-panel active" id="tab-products">
            <h2 class="panel-title"><span class="icon">◉</span> Products & Availability</h2>
            <div class="faq-item">
                <p class="faq-q">What information is included on each product page?</p>
                <p class="faq-a">Each product page typically includes product specifications, available variations, minimum order quantities (MOQ), estimated lead times, packaging details, and inquiry options. Information may vary depending on the supplier and product category.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Are all products always in stock?</p>
                <p class="faq-a">Product availability may change based on supplier inventory and demand. Submitting an inquiry is the best way to confirm current stock availability and estimated fulfillment timelines.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Can I request customizations or private labeling?</p>
                <p class="faq-a">Yes, customization options such as branding, packaging, colors, sizes, or specifications may be available for select products. Include your customization requirements when submitting your inquiry.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Do products have minimum order quantities (MOQ)?</p>
                <p class="faq-a">Many products have minimum order quantity requirements that vary by supplier and item type. MOQ details are usually listed on the product page or can be confirmed during the inquiry process.</p>
            </div>
        </div>

        <div class="tab-panel" id="tab-orders">
            <h2 class="panel-title"><span class="icon">◉</span> Orders & Inquiries</h2>
            <div class="faq-item">
                <p class="faq-q">How do I submit an inquiry?</p>
                <p class="faq-a">You can submit an inquiry directly from the product page by providing your requested quantity, specifications, and contact details. Suppliers or representatives will respond with pricing and additional information.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Can I inquire about multiple products at once?</p>
                <p class="faq-a">Yes, you may submit inquiries for multiple products depending on the platform features available. Providing detailed requirements helps streamline the quotation process.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">How long does it take to receive a response?</p>
                <p class="faq-a">Response times may vary depending on the supplier, time zone, and inquiry complexity. Most inquiries are typically reviewed within a few business days.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Am I obligated to place an order after submitting an inquiry?</p>
                <p class="faq-a">No, submitting an inquiry does not create a purchase obligation. It simply allows you to request pricing, availability, and additional product details before making a decision.</p>
            </div>
        </div>

        <div class="tab-panel" id="tab-shipping">
            <h2 class="panel-title"><span class="icon">◉</span> Shipping & Delivery</h2>
            <div class="faq-item">
                <p class="faq-q">Do you offer international shipping?</p>
                <p class="faq-a">Shipping availability depends on the supplier and destination country. Delivery options and shipping terms can be discussed during the inquiry and quotation process.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">How are shipping costs calculated?</p>
                <p class="faq-a">Shipping costs are generally based on factors such as order quantity, package dimensions, destination, shipping method, and applicable customs requirements.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Can I arrange my own freight forwarder or logistics provider?</p>
                <p class="faq-a">In many cases, buyers may use their preferred freight forwarder or logistics partner. Coordination details can be finalized before order confirmation.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">How long does delivery usually take?</p>
                <p class="faq-a">Delivery timelines vary depending on production schedules, order volume, shipping method, and destination. Estimated lead times are usually provided with the quotation.</p>
            </div>
        </div>

        <div class="tab-panel" id="tab-payments">
            <h2 class="panel-title"><span class="icon">◉</span> Payments & Account Support</h2>
            <div class="faq-item">
                <p class="faq-q">What payment methods are accepted?</p>
                <p class="faq-a">Accepted payment methods may vary by supplier and order type. Common options may include bank transfers, online payment services, or other agreed payment arrangements.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Do I need an account to submit an inquiry?</p>
                <p class="faq-a">Some platforms allow guest inquiries, while others may require account registration for easier order tracking and communication management.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Is my business information kept confidential?</p>
                <p class="faq-a">Business and contact information submitted through the platform is generally handled according to applicable privacy and data protection practices.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Who can I contact for additional assistance?</p>
                <p class="faq-a">If you need more information about products, quotations, or account-related concerns, you can use the platform’s contact or inquiry forms for further assistance.</p>
            </div>
        </div>

        </div>
    </div>

    <div class="contact-strip">
        <p style="margin: 0;">Still have questions? Contact our support team.</p>
        <button id="openPopup" class="contact-btn">Contact Support →</button>
    </div>
    </div>

    <!-- Popup Overlay -->
    <div class="popup-overlay" id="popupOverlay" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <div class="popup">
            <div class="popup-header">
                <div>
                    <p class="popup-eyebrow">Support</p>
                    <h2 class="popup-title" id="popupTitle">Contact Us</h2>
                </div>
                <button class="popup-close" id="closePopup" aria-label="Close">&times;</button>
            </div>
            <div class="popup-body">
                <?php // echo do_shortcode('[contact-form-7 id="076d2c5" title="FAQ support form"]') ?>
                <div style="display: flex; gap: 1.5rem; margin-bottom: 38px;">
                    <div style="width: 100%; max-width: 320px;">
                        <label for="contact-name" style="display: block;">Your Name</label>
                        <input type="text" id="contact-name" name="contact-name" required placeholder="Your Name" style="width: 100%; max-width: 320px;">
                    </div>
                    <div style="width: 100%; max-width: 320px;">
                        <label for="contact-email" style="display: block;">Your Email</label>
                        <input type="email" id="contact-name" name="contact-name" required placeholder="Your Email Address" style="width: 100%; max-width: 320px;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="contact-msg" style="display: block;">Your Message/Comment</label>
                    <textarea id="contact-msg" name="contact-msg" rows="5" required style="width: 100%;"></textarea>
                </div>

                <div style="width: 100%; text-align: end;">
                    <button type="submit" id="contact-submit" style="padding: 12px; color: white; background-color: #00719a; font-weight: 600; border: none;">Send Message</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    const tabs = document.querySelectorAll('.tab-btn');
    const panels = document.querySelectorAll('.tab-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
        const target = tab.dataset.tab;

        tabs.forEach(t => {
            t.classList.remove('active');
            t.setAttribute('aria-selected', 'false');
        });
        panels.forEach(p => p.classList.remove('active'));

        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');

        const panel = document.getElementById('tab-' + target);
        panel.classList.add('active');
        });
    });

    // Popup logic
    const overlay = document.getElementById('popupOverlay');
    const openBtn = document.getElementById('openPopup');
    const closeBtn = document.getElementById('closePopup');
    
    openBtn.addEventListener('click', () => overlay.classList.add('open'));
    closeBtn.addEventListener('click', () => overlay.classList.remove('open'));
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.classList.remove('open');
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') overlay.classList.remove('open');
    });
    </script>
    <?php
}

add_shortcode( 'anythingsupplies_faq_page', 'render_faq_page' );

function hero_featured_categories() {
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
    <style>
        .hfc_container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 50%;
            border-radius: 12px;
            padding: 12px 0px;
            height: 240px;
        }
        .hfc_name {
            margin: 0;
            font-size: 24px;
            text-align: center;
            color: #fff;
            font-family: 'Roboto', Sans-serif;
            font-weight: 600;
        }
        .hfc_button {
            padding: 8px 20px;
            border-radius: 30px;
            border: none;
            margin-top: 10px;
            font-weight: 500;
        }

    </style>
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
}
add_shortcode( 'hero_featured_categories', 'hero_featured_categories' );

function landing_page_hero_text() {
    ob_start();
    ?>
        <style>
            .hero-content h1 {
                font-family: "Sora", sans-serif;
                font-size: clamp(36px, 4.5vw, 58px);
                font-weight: 800;
                color: #fff;
                line-height: 1.12;
                margin-bottom: 22px;
            }
            .hero-content h1 em {
                color: #f47c20;
                font-style: normal;
            }
            .hero-content h1 {
               font-size: 56px;
               margin: 4px 0px;
            }

            .typing-word {
                color: #f47c20;
                border-right: 3px solid #f47c20;
                white-space: nowrap;
                display: inline;
                animation: blink 0.8s step-end infinite;
            }
            @keyframes blink {
                0%, 100% {
                    border-color: #f47c20;
            }
                50% {
                    border-color: transparent;
                }
            }

            @media (max-width: 800px) {
                .hero-content h1 {
                    font-size: 46px;
                }
            }

            @media (max-width: 425px) {
                .hero-content h1 {
                    font-size: 36px;
                }
            }
        </style>
        <div class="hero-content">
            <h1>
                Think You're<br />
                Getting the<br />
                <em style="color: #f47c20; font-style: normal">Best <span class="typing-word" id="typing-word">Price</span></em>?<br />
                Let Us Beat It.
            </h1>
        </div>

        <script>
         (function () {
            const words = ["Price", "Quality", "Results"];
            const el = document.getElementById("typing-word");
            if (!el) return;
            let wordIdx = 0,
               charIdx = words[0].length,
               deleting = false;
            el.style.animation = "blink 0.8s step-end infinite";

            function type() {
               const current = words[wordIdx];
               if (!deleting) {
                  el.textContent = current.slice(0, charIdx);
                  if (charIdx === current.length) {
                     deleting = true;
                     setTimeout(type, 2000);
                     return;
                  }
                  charIdx++;
                  setTimeout(type, 90);
               } else {
                  el.textContent = current.slice(0, charIdx);
                  if (charIdx === 0) {
                     deleting = false;
                     wordIdx = (wordIdx + 1) % words.length;
                     charIdx = 0;
                     setTimeout(type, 350);
                     return;
                  }
                  charIdx--;
                  setTimeout(type, 50);
               }
            }
            // Hold "Price" for 2s then start cycling
            setTimeout(() => {
               deleting = true;
               type();
            }, 2000);
         })();
        </script>
    <?php
}

add_shortcode( 'landing_page_hero_text', 'landing_page_hero_text' );

function landing_page_results_counter($atts) {
    $atts = shortcode_atts( [
        'target' => '23',
        'suffix'     => '% Saved',
    ], $atts );

    ob_start();
    ?>
    <style>
        .result-savings-counter {
            font-family: "Sora", sans-serif;
            font-size: 38px;
            font-weight: 800;
            color: #f47c20;
            line-height: 1;
        }
    </style>

    <div class="result-savings-counter">
        <span class="result-counter" data-target="<?php echo esc_attr($atts['target']); ?>" data-suffix="<?php echo esc_attr($atts['suffix']); ?>">
            0%
        </span>
    </div>

    <script>
        function animateCounter(el) {
            const target = parseFloat(el.dataset.target);
            const suffix = el.dataset.suffix || "";
            const isDecimal = el.dataset.decimal === "1";
            const duration = 2000,
               steps = 60,
               interval = duration / steps;
            let step = 0;
            function easeOut(t) {
               return t * (2 - t);
            }
            const timer = setInterval(() => {
               step++;
               const progress = easeOut(Math.min(step / steps, 1));
               let current = target * progress;
               if (step >= steps) {
                  current = target;
                  clearInterval(timer);
               }
               const display = isDecimal ? current.toFixed(1) : Math.round(current).toLocaleString();
               el.innerHTML = display + '<span class="counter-suffix">' + suffix + "</span>";
            }, interval);
         }

         // Result card counters (viewport triggered)
         const resultObserver = new IntersectionObserver(
            (entries) => {
               entries.forEach((entry) => {
                  if (entry.isIntersecting) {
                     entry.target.querySelectorAll(".result-counter").forEach((el) => {
                        if (!el.dataset.animated) {
                           el.dataset.animated = "1";
                           animateCounter(el);
                        }
                     });
                     resultObserver.unobserve(entry.target);
                  }
               });
            },
            { threshold: 0.4 },
         );
         const resultCounter = document.querySelector(".result-counter");
         if (resultCounter) resultObserver.observe(resultCounter);
    </script>
    <?php

    return ob_get_clean();
}

add_shortcode( 'landing_page_results_counter', 'landing_page_results_counter' );

function landing_page_testimony() {

    ob_start();
    ?>
        <style>
            .testimonials-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
                margin-top: 48px;
            }
            .testimonial {
                background: #f0f6fa;
                border-radius: 14px;
                padding: 28px;
                border: 1.5px solid #c8dce8;
            }
            .stars {
                color: #f47c20;
                font-size: 18px;
                margin-bottom: 12px;
            }
            .testimonial blockquote {
                font-size: 15px;
                color: #0a2b3e;
                line-height: 1.65;
                font-style: italic;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                font-family: 'Roboto';
            }
            .testimonial-meta {
                margin-top: 18px;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .testimonial-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: #005a8f;
                color: #fff;
                display: grid;
                place-items: center;
                font-weight: 700;
                font-size: 16px;
            }
            .testimonial-name {
                font-size: 14px;
                font-weight: 700;
                color: #005a8f;
            }
            .testimonial-role {
                font-size: 12px;
                color: #5a88a0;
            }

            /* ── TESTIMONIAL SLIDER ── */
            .testi-slider-wrap {
                position: relative;
                overflow: hidden;
            }
            .testi-track {
                display: flex;
                gap: 24px;
                transition: transform 0.45s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                will-change: transform;
            }
            .testimonial {
                background: #f0f6fa;
                border-radius: 14px;
                padding: 28px;
                border: 1.5px solid #c8dce8;
                flex: 0 0 calc(33.333% - 16px);
                min-width: 0;
            }
            .testi-nav {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 14px;
                margin-top: 28px;
            }
            .testi-arrow {
                width: 42px;
                height: 42px;
                border-radius: 50%;
                background: #fff;
                border: 1.5px solid #c8dce8;
                display: grid;
                place-items: center;
                cursor: pointer;
                transition: all 0.2s;
                flex-shrink: 0;
            }
            .testi-arrow:hover {
                background: #005a8f;
                border-color: #005a8f;
            }
            .testi-arrow:hover svg {
                stroke: #fff;
            }
            .testi-arrow svg {
                width: 18px;
                height: 18px;
                stroke: #005a8f;
                fill: none;
                stroke-width: 2;
                stroke-linecap: round;
                stroke-linejoin: round;
                transition: stroke 0.2s;
            }
            .testi-dots {
                display: flex;
                gap: 6px;
            }
            .testi-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #c8dce8;
                transition:
                background 0.2s,
                width 0.2s;
            }
            .testi-dot.active {
                background: #005a8f;
                width: 20px;
                border-radius: 4px;
            }

            @media (max-width: 900px) {
                .testimonial {
                    flex: 0 0 calc(50% - 12px);
                }
            }

            @media (max-width: 600px) {
                .testimonial {
                    flex: 0 0 100%;
                    padding: 22px 18px;
                }
                .testi-arrow {
                    width: 36px;
                    height: 36px;
                }
            }
        </style>
        <div class="testi-slider-wrap">
            <div class="testi-track" id="testiTrack">
                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <blockquote>"We submitted three supplier quotes we had and Anything Supplies came back with a significantly lower price for all three. The supplier they matched us to is now our primary vendor."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #005a8f">R</div>
                    <div>
                        <div class="testimonial-name">Ricky M.</div>
                        <div class="testimonial-role">Procurement Manager · Manila, PH</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <blockquote>"The side-by-side comparison they sent was incredibly detailed. I could see exactly what I was gaining — not just price, but certification levels and lead time too. Brilliant service."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #3294a4">S</div>
                    <div>
                        <div class="testimonial-name">Sarah K.</div>
                        <div class="testimonial-role">Operations Director · Melbourne, AU</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★☆</div>
                    <blockquote>"First time using a sourcing platform like this. I was skeptical but the response time was fast, the comparison was fair, and we ended up saving about 20% on our first order."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #0088c6">D</div>
                    <div>
                        <div class="testimonial-name">David T.</div>
                        <div class="testimonial-role">SME Owner · Singapore</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <blockquote>"Our previous sourcing agent was adding a 20% markup we didn't know about. Anything Supplies showed us the actual factory price. The transparency alone is worth switching for."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #f47c20">A</div>
                    <div>
                        <div class="testimonial-name">Ana L.</div>
                        <div class="testimonial-role">Business Owner · Cebu, PH</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <blockquote>"We needed 500 office chairs on a tight budget. They came back with three verified options all under our price ceiling, fully spec-matched. Ordered within the week."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #005a8f">J</div>
                    <div>
                        <div class="testimonial-name">James W.</div>
                        <div class="testimonial-role">Facilities Manager · London, UK</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <blockquote>"I sent my Alibaba quote thinking it was already competitive. They beat it by 27%. I was genuinely surprised and the supplier quality has been just as good."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #3294a4">M</div>
                    <div>
                        <div class="testimonial-name">Marco D.</div>
                        <div class="testimonial-role">Import Specialist · Sydney, AU</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <blockquote>"The team is professional and responsive. Quoted us within 24 hours and the report was thorough — pricing, MOQ, lead times, compliance certs. Nothing was left out."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #0088c6">C</div>
                    <div>
                        <div class="testimonial-name">Christine B.</div>
                        <div class="testimonial-role">Supply Chain Lead · Dubai, UAE</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★☆</div>
                    <blockquote>"Really impressed with the verification process. Knowing suppliers are audited before I see them removes so much of the anxiety around overseas procurement."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #f47c20">T</div>
                    <div>
                        <div class="testimonial-name">Tony R.</div>
                        <div class="testimonial-role">Operations Head · Auckland, NZ</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <blockquote>"We've now used Anything Supplies for three separate product lines. Consistent savings, consistent quality, no surprises. It's become our default first step for any new procurement."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #005a8f">L</div>
                    <div>
                        <div class="testimonial-name">Lena C.</div>
                        <div class="testimonial-role">COO · Makati, PH</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <blockquote>"As a small business I never thought I could compete with larger buyers on price. Anything Supplies changed that. My unit costs are now on par with what big chains are paying."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #3294a4">P</div>
                    <div>
                        <div class="testimonial-name">Patricia N.</div>
                        <div class="testimonial-role">Retail Owner · Singapore</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <blockquote>"Submitted a CCTV quote on a Monday, had a better offer by Tuesday afternoon. Placed the order Wednesday. That's the kind of turnaround that makes a real difference to our business."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #0088c6">K</div>
                    <div>
                        <div class="testimonial-name">Kevin O.</div>
                        <div class="testimonial-role">Security Integrator · Davao, PH</div>
                    </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="stars">★★★★☆</div>
                    <blockquote>"Good experience overall. The price match came in lower and the supplier communication has been excellent. Only minor thing — would love to see a dedicated dashboard for tracking orders."</blockquote>
                    <div class="testimonial-meta">
                    <div class="testimonial-avatar" style="background: #f47c20">H</div>
                    <div>
                        <div class="testimonial-name">Hannah S.</div>
                        <div class="testimonial-role">Procurement Analyst · Brisbane, AU</div>
                    </div>
                    </div>
                </div>
            </div>
            <!-- /testi-track -->

            <div class="testi-nav">
                <button class="testi-arrow" id="testiPrev" aria-label="Previous">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6" /></svg>
                </button>
                <div class="testi-dots" id="testiDots"></div>
                <button class="testi-arrow" id="testiNext" aria-label="Next">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6" /></svg>
                </button>
            </div>
        </div>

        <script>
            (function () {
            const track = document.getElementById("testiTrack");
            const prevBtn = document.getElementById("testiPrev");
            const nextBtn = document.getElementById("testiNext");
            const dotsWrap = document.getElementById("testiDots");
            if (!track) return;

            const cards = Array.from(track.children);
            let perView = window.innerWidth <= 600 ? 1 : window.innerWidth <= 900 ? 2 : 3;
            let current = 0;
            const total = cards.length;
            const pages = Math.ceil(total / perView);

            // Build dots
            function buildDots() {
               dotsWrap.innerHTML = "";
               for (let i = 0; i < pages; i++) {
                  const d = document.createElement("div");
                  d.className = "testi-dot" + (i === 0 ? " active" : "");
                  d.addEventListener("click", () => goTo(i));
                  dotsWrap.appendChild(d);
               }
            }

            function goTo(page) {
               current = Math.max(0, Math.min(page, pages - 1));
               const gap = 24;
               // Card width = (100% of container - gaps) / perView
               const containerW = track.parentElement.offsetWidth;
               const cardW = (containerW - gap * (perView - 1)) / perView;
               const offset = current * (cardW + gap) * perView;
               track.style.transform = `translateX(-${offset}px)`;
               dotsWrap.querySelectorAll(".testi-dot").forEach((d, i) => {
                  d.classList.toggle("active", i === current);
               });
            }

            function updatePerView() {
               perView = window.innerWidth <= 600 ? 1 : window.innerWidth <= 900 ? 2 : 3;
               buildDots();
               goTo(0);
            }

            buildDots();
            prevBtn.addEventListener("click", () => goTo(current - 1));
            nextBtn.addEventListener("click", () => goTo(current + 1));
            window.addEventListener("resize", updatePerView);

            // Auto-slide every 5s
            let autoTimer = setInterval(() => goTo((current + 1) % pages), 5000);
            [prevBtn, nextBtn].forEach((b) =>
               b.addEventListener("click", () => {
                  clearInterval(autoTimer);
                  autoTimer = setInterval(() => goTo((current + 1) % pages), 5000);
               }),
            );
         })();
        </script>
    <?php
}

add_shortcode( 'landing_page_testimony', 'landing_page_testimony' );