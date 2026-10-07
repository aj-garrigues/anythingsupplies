<?
add_shortcode( 'as_category_list', function ( $atts ) {

    as_enqueue_assets('as_category_list', 'as-category-list.css', null);

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