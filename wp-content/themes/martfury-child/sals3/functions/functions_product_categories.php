<?php
/**
 * 1. Add the SEO Tags field to the "Add New Category" screen
 */
add_action( 'product_cat_add_form_fields', 'custom_category_seo_tags_field', 10 );
function custom_category_seo_tags_field() {
    ?>
    <div class="form-field">
        <label for="cat_seo_tags"><?php _e( 'Category SEO Keywords', 'woocommerce' ); ?></label>
        <input type="text" name="cat_seo_tags" id="cat_seo_tags" value="">
        <p><?php _e( 'Enter tags for this category, separated by commas.', 'woocommerce' ); ?></p>
    </div>
    <?php
}

/**
 * 2. Add the SEO Tags field to the "Edit Category" screen
 */
add_action( 'product_cat_edit_form_fields', 'custom_edit_category_seo_tags_field', 10, 2 );
function custom_edit_category_seo_tags_field( $term, $taxonomy ) {
    // Retrieve the existing value
    $seo_tags = get_term_meta( $term->term_id, 'cat_seo_tags', true );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="cat_seo_tags"><?php _e( 'Category SEO Keywords', 'woocommerce' ); ?></label></th>
        <td>
            <input type="text" name="cat_seo_tags" id="cat_seo_tags" value="<?php echo esc_attr( $seo_tags ); ?>">
            <p class="description"><?php _e( 'Add comma-separated keywords to improve SEO for this category page.', 'woocommerce' ); ?></p>
        </td>
    </tr>
    <?php
}

/**
 * 3. Save the field data when creating or editing a category
 */
add_action( 'edited_product_cat', 'save_category_seo_tags', 10, 1 );
add_action( 'create_product_cat', 'save_category_seo_tags', 10, 1 );
function save_category_seo_tags( $term_id ) {
    if ( isset( $_POST['cat_seo_tags'] ) ) {
        update_term_meta( $term_id, 'cat_seo_tags', sanitize_text_field( $_POST['cat_seo_tags'] ) );
    }
}

/**
 * 4. Display the SEO Tags on the Category Page (Frontend)
 */
add_action( 'woocommerce_archive_description', 'display_category_seo_tags', 20 );
function display_category_seo_tags() {
    if ( is_product_category() ) {
        $term_id = get_queried_object_id();
        $seo_tags = get_term_meta( $term_id, 'cat_seo_tags', true );

        if ( ! empty( $seo_tags ) ) {
            $tags = explode( ',', $seo_tags );
            echo '<div class="custom-cat-tags" style="margin: 10px 0; font-size: 0.9em; color: #666;">';
            echo '<strong>Related:</strong> ';
            foreach ( $tags as $tag ) {
                echo '<span class="tag-item" style="background: #eee; padding: 2px 8px; border-radius: 4px; margin-right: 5px;">' . esc_html( trim( $tag ) ) . '</span>';
            }
            echo '</div>';
        }
    }
}