<?php
// ==============================
// 1. ADD CATEGORY CHECKBOXES (ADD SCREEN)
// ==============================
add_action('product_cat_add_form_fields', 'add_category_filter_checkboxes', 10);
function add_category_filter_checkboxes() {

    $labels = [
        'Featured Category',
        'Trending',
        'Hot Picks',
        'Innovative',
        'Hot Deals',
        'Daily Deals',
        'Flash Deals',
        '5-star Rated'
    ];
    ?>
    <div class="form-field">
        <label>Category Options</label>
        <div style="background:#fff; padding:10px; border:1px solid #ccd0d4;">
            <?php foreach ($labels as $label) :
                $slug = ($label === 'Featured Category')
                    ? 'featured_category'
                    : 'cat_filter_' . sanitize_title($label);
            ?>
                <label style="display:block; margin-bottom:5px;">
                    <input type="checkbox" name="<?php echo esc_attr($slug); ?>" value="yes">
                    <?php echo esc_html($label); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <p>Select which badges or visibility options apply to this category.</p>
    </div>
    <?php
}

// ==============================
// 2. EDIT CATEGORY SCREEN
// ==============================
add_action('product_cat_edit_form_fields', 'edit_category_filter_checkboxes', 10, 2);
function edit_category_filter_checkboxes($term, $taxonomy) {

    $labels = [
        'Featured Category',
        'Trending',
        'Hot Picks',
        'Innovative',
        'Hot Deals',
        'Daily Deals',
        'Flash Deals',
        '5-star Rated'
    ];
    ?>
    <tr class="form-field">
        <th scope="row"><label>Category Options</label></th>
        <td>
            <?php foreach ($labels as $label) :
                $slug = ($label === 'Featured Category')
                    ? 'featured_category'
                    : 'cat_filter_' . sanitize_title($label);

                $value = get_term_meta($term->term_id, $slug, true);
            ?>
                <label style="display:block; margin-bottom:5px;">
                    <input type="checkbox" name="<?php echo esc_attr($slug); ?>" value="yes" <?php checked($value, 'yes'); ?>>
                    <?php echo esc_html($label); ?>
                </label>
            <?php endforeach; ?>
            <p class="description">Used for homepage sections, badges, and filtering.</p>
        </td>
    </tr>
    <?php
}

// ==============================
// 3. SAVE CATEGORY META
// ==============================
add_action('edited_product_cat', 'save_category_filter_checkboxes', 10);
add_action('created_product_cat', 'save_category_filter_checkboxes', 10);

function save_category_filter_checkboxes($term_id) {

    $labels = [
        'Featured Category',
        'Trending',
        'Hot Picks',
        'Innovative',
        'Hot Deals',
        'Daily Deals',
        'Flash Deals',
        '5-star Rated'
    ];

    foreach ($labels as $label) {

        $slug = ($label === 'Featured Category')
            ? 'featured_category'
            : 'cat_filter_' . sanitize_title($label);

        if (isset($_POST[$slug]) && $_POST[$slug] === 'yes') {
            update_term_meta($term_id, $slug, 'yes');
        } else {
            update_term_meta($term_id, $slug, 'no');
        }
    }
}
