<?php   
function generate_master_sitemap() {
    // 1. Fetch Data
    $products = get_posts(array('post_type' => 'product', 'post_status' => 'publish', 'numberposts' => -1));
    $posts    = get_posts(array('post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1));
    
    $product_cats = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true));
    $blog_cats    = get_terms(array('taxonomy' => 'category', 'hide_empty' => true));

    // 2. Start XML
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

    // --- SECTION: PRODUCTS ---
    foreach ($products as $product) {
        $xml .= '<url>';
        $xml .= '<loc>' . get_permalink($product->ID) . '</loc>';
        $xml .= '<lastmod>' . get_post_modified_time('c', false, $product->ID) . '</lastmod>';
        
        $image_id = get_post_thumbnail_id($product->ID);
        if ($image_id) {
            $img_url = wp_get_attachment_image_url($image_id, 'full');
            // Logic: Caption -> Alt Text -> Product Title
            $img_cap = get_post_field('post_excerpt', $image_id);
            if (empty($img_cap)) { $img_cap = get_post_meta($image_id, '_wp_attachment_image_alt', true); }
            if (empty($img_cap)) { $img_cap = $product->post_title; }

            $xml .= '<image:image>';
            $xml .= '<image:loc>' . esc_url($img_url) . '</image:loc>';
            $xml .= '<image:title>' . esc_html($product->post_title) . '</image:title>';
            $xml .= '<image:caption>' . esc_html($img_cap) . '</image:caption>';
            $xml .= '</image:image>';
        }
        $xml .= '<priority>0.9</priority>';
        $xml .= '</url>';
    }

    // --- SECTION: BLOG POSTS ---
    foreach ($posts as $post) {
        $xml .= '<url>';
        $xml .= '<loc>' . get_permalink($post->ID) . '</loc>';
        $xml .= '<lastmod>' . get_post_modified_time('c', false, $post->ID) . '</lastmod>';
        $xml .= '<priority>0.7</priority>';
        $xml .= '</url>';
    }

    // --- SECTION: CATEGORIES (Both Woo and Blog) ---
    $all_cats = array_merge($product_cats, $blog_cats);
    foreach ($all_cats as $cat) {
        $xml .= '<url>';
        $xml .= '<loc>' . get_term_link($cat) . '</loc>';
        $xml .= '<changefreq>weekly</changefreq>';
        $xml .= '<priority>0.5</priority>';
        $xml .= '</url>';
    }

    $xml .= '</urlset>';

    // 3. Save File
    $file = ABSPATH . "sitemap.xml";
    $fp = fopen($file, 'w');
    fwrite($fp, $xml);
    fclose($fp);
}

// 4. Automation Triggers
//add_action('save_post', 'generate_master_sitemap'); // Triggers for both posts and products
//add_action('edited_term', 'generate_master_sitemap'); // Triggers when any category is edited