<?php
/**
 * Register the 'Partners' admin menu page
 */
/**
 * Enqueue Bootstrap only on the Registration Template
 */
add_action('wp_enqueue_scripts', 'enqueue_bootstrap_for_registration_page');

function enqueue_bootstrap_for_registration_page() {
    // Check if the current page is using your specific "Template Registration"
    if (is_page_template('template-registration.php')) {
        
        // Enqueue Bootstrap CSS
        wp_enqueue_style(
            'bootstrap-5', 
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', 
            array(), 
            '5.3.0'
        );

        // Enqueue Bootstrap JS (Bundle includes Popper)
        wp_enqueue_script(
            'bootstrap-5-js', 
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', 
            array(), 
            '5.3.0', 
            true
        );
    }
}

function create_partner_user_role() {
    add_role(
        'partner', // Internal slug
        __('Partner'), // Display name
        array(
            'read'         => true,   // Allows them to log in and view their profile
            'edit_posts'   => false,  // Prevents them from writing blog posts
            'delete_posts' => false,  // Prevents them from deleting content
        )
    );
}
add_action('init', 'create_partner_user_role');

add_action('admin_menu', 'register_partners_admin_menu');

function register_partners_admin_menu() {
    add_menu_page(
        'Partners List',          // Page Title
        'Partners',               // Menu Title
        'manage_options',         // Capability required (Admins only)
        'partners-list',          // Menu Slug
        'render_partners_page',   // Callback function
        'dashicons-groups',       // Icon
        25                        // Position in menu
    );
}

add_action('admin_menu', 'register_partners_submenus');

function register_partners_submenus() {
    // The first parameter is the 'parent_slug' (the slug of your existing Partners menu)
    $parent_slug = 'partners-list'; 

    // 1. All Partners
    add_submenu_page($parent_slug, 'All Partners', 'All Partners', 'manage_options', 'all-partners', 'render_partners_page');

    // 2. Inquiries
    add_submenu_page($parent_slug, 'Inquiries', 'Inquiries', 'manage_options', 'partner-inquiries', 'inquiries_page_callback');

    // 3. RFQs
    add_submenu_page($parent_slug, 'RFQs', 'RFQs', 'manage_options', 'partner-rfqs', 'rfqs_page_callback');

    // 4. Invoice
    add_submenu_page($parent_slug, 'Invoices', 'Invoices', 'manage_options', 'partner-invoices', 'invoices_page_callback');
}

function invoices_page_callback() {
    global $wpdb;
    $rfq_id = isset($_GET['rfq_id']) ? intval($_GET['rfq_id']) : 0;
    echo '<div class="wrap"><h1>Invoices</h1></div>';
    render_rfq_invoice_listing();
}


function render_rfq_invoice_listing() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'invoices';

    // 1. Pagination Setup
    $per_page = 10;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // 2. Fetch Data
    $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page, $offset
    ));

    $total_pages = ceil($total_items / $per_page);

    // 3. Render Table
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">RFQ Invoices</h1>
        <hr class="wp-header-end">

        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Inquiry/Order</th>
                    <th>Customer</th>
                    <th>Qty</th>
                    <th>Price/Disc</th>
                    <th>Adjustment</th>
                    <th>Note & File</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results) : foreach ($results as $row) : 
                    $order_link = $row->wc_order_id ? get_edit_post_link($row->wc_order_id) : '#';
                    ?>
                    <tr>
                        <td><strong>#<?php echo $row->id; ?></strong></td>
                        <td>
                            Inquiry: #<?php echo $row->inquiry_id; ?><br>
                            <?php if ($row->wc_order_id): ?>
                                <a href="<?php echo $order_link; ?>">Order #<?php echo $row->wc_order_id; ?></a>
                            <?php else: ?>
                                <span class="description">No Order</span>
                            <?php endif; ?>
                        </td>
                        <td>ID: <?php echo $row->customer_id; ?></td>
                        <td><?php echo $row->adj_quantity; ?></td>
                        <td>
                            Price: <?php echo wc_price($row->adj_price); ?><br>
                            Disc: -<?php echo wc_price($row->discount); ?>
                        </td>
                        <td><?php echo wc_price($row->total_adjustment); ?></td>
                        <td>
                            <span class="dashicons dashicons-testimonial" title="<?php echo esc_attr($row->invoice_note); ?>"></span>
                            <?php if ($row->attachment_url): ?>
                                <a href="<?php echo esc_url($row->attachment_url); ?>" target="_blank"><span class="dashicons dashicons-paperclip"></span></a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($row->created_at)); ?></td>
                    </tr>
                <?php endforeach; else : ?>
                    <tr><td colspan="8">No invoices found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo $total_items; ?> items</span>
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $current_page
                ));
                ?>
            </div>
        </div>
    </div>
    <?php
}

function rfqs_page_callback() {
    global $wpdb;
    $partner_id = isset($_GET['partner_id']) ? intval($_GET['partner_id']) : 0;


// --- 1. HANDLE FORM SUBMISSION (Create RFQ) ---
   if (isset($_POST['sls_submit_rfq'])) {
        check_admin_referer('create_rfq_action');
        
        $wpdb->insert('sls_inquiries', array(
            'customer_id'   => intval($_POST['partner_id']),
            'product_id'    => intval($_POST['product_id']),
            'agent_id'      => intval($_POST['agent_id']),
            'type_id'       => 3, // RFQ type
            'inquired_qty'  => sanitize_text_field($_POST['inquired_qty']),
            'inquiry_type' => sanitize_text_field($_POST['inquired_type']),
            'message'       => wp_kses_post($_POST['message']),
            'status'        => 'open',
            'post_status'   => 'active',
            'created_at'    => current_time('mysql')
        ));

        // Redirect to prevent form resubmission on refresh
        wp_redirect(admin_url('admin.php?page=partner-rfqs&partner_id=' . $_GET['partner_id'] . '&updated=1'));
        exit;
    }

    // --- 1. HANDLE BULK ACTIONS ---// --- HANDLE SINGLE ROW ACTIONS ---
    if (isset($_GET['action']) && isset($_GET['rfq_id'])) {
        $rfq_id = intval($_GET['rfq_id']);
        check_admin_referer('rfq_action_' . $rfq_id);

        if ($_GET['action'] === 'archive') {
            $wpdb->update('sls_inquiries', ['post_status' => 'archive'], ['id' => $rfq_id]);
            echo '<div class="updated notice is-dismissible"><p>RFQ Archived.</p></div>';
        } elseif ($_GET['action'] === 'delete') {
            $wpdb->delete('sls_inquiries', ['id' => $rfq_id]);
            echo '<div class="updated notice is-dismissible"><p>RFQ Deleted.</p></div>';
        } elseif ($_GET['action'] === 'send_to_lead') {
            // Example: Update agent_id to a Lead Agent (ID 1)
            $wpdb->update('sls_inquiries', ['agent_id' => 1], ['id' => $rfq_id]);
            echo '<div class="updated notice is-dismissible"><p>RFQ sent to Lead Agent.</p></div>';
        }
    }

    if (isset($_POST['bulk_action']) && isset($_POST['rfq_ids'])) {
        $action = $_POST['bulk_action'];
        $ids = array_map('intval', $_POST['rfq_ids']);
        $ids_placeholders = implode(',', array_fill(0, count($ids), '%d'));

        if ($action === 'delete') {
            $wpdb->query($wpdb->prepare("DELETE FROM sls_inquiries WHERE id IN ($ids_placeholders)", $ids));
            echo '<div class="updated notice is-dismissible"><p>Selected items deleted.</p></div>';
        } elseif ($action === 'active' || $action === 'archive') {
            $wpdb->query($wpdb->prepare("UPDATE sls_inquiries SET post_status = %s WHERE id IN ($ids_placeholders)", $action, ...$ids));
            echo '<div class="updated notice is-dismissible"><p>Items marked as ' . ucfirst($action) . '.</p></div>';
        }
    }

    // --- 2. FETCH DATA ---
    if ($partner_id){
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT i.* FROM sls_inquiries i
            LEFT JOIN sls_inquiry_types t ON i.type_id = t.id
            WHERE i.customer_id = %d AND t.type_name = 'rfqs'
            ORDER BY i.created_at DESC
        ", $partner_id));
    }else{
        $results = $wpdb->get_results("
            SELECT i.* FROM sls_inquiries i
            LEFT JOIN sls_inquiry_types t ON i.type_id = t.id
            WHERE t.type_name = 'rfqs'
            ORDER BY i.created_at DESC");
    }

    // -- load the agent

    $agents = get_users(array(
        'role'    => 'sls_agent', // Only fetch users with this role
        'orderby' => 'display_name',
        'order'   => 'ASC'
    ));

    $partners = get_users(array(
        'role'    => 'partner', // Only fetch users with this role
        'orderby' => 'display_name',
        'order'   => 'ASC'
    ));

    //print_r($agents);


    $partner_user = get_userdata($partner_id);
    $agent_name = get_userdata(26) ? get_userdata(26)->display_name : "Unassigned";


    echo '<div class="wrap">';
    
    // Header
    echo '<h1 class="wp-heading-inline">Request for a Quotation (RFQs)</h1>';

    // --- 2. HEADER WITH "ADD NEW" BUTTON ---
  ?>
<?php if (isset($_GET['action']) && $_GET['action']==='new'): ?>

         <div class="admin-partner-page page-spacing" style="padding:20px; width:75%">
            <h2>Create New RFQ</h2>
            <form method="post" action="<?php echo admin_url('admin.php?page=partner-rfqs&partner_id=' . $partner_id); ?>">
                <?php wp_nonce_field('create_rfq_action'); ?>
                
                        <div style="width:100%; margin-bottom:15px;">
                            <label style="display:block;">Product</label>
                            <input type="text" class="wc-product-search" placeholder="Search for published products...">
                           
                            <input type="hidden" name="product_id" id="product_id" >
                        </div>
                   
                        <div style="display:flex; margin-bottom:15px; gap:10px;">
                            <div>
                                <label style="display:block;">Quantity</label>
                                <input type="text" style="width:120px" name="inquired_qty" class="regular-text" required placeholder="e.g. 100">
                            </div>
                            <div>
                                <label style="display:block;">Inquiry Type</label>
                                <input type="text" style="width:200px" name="inquired_type" class="regular-text" required placeholder="e.g. Pieces">
                            </div>

                            <div>
                                <label style="display:block;">For Partner</label>
                                <select name="partner_id" style="width:300px">
                                    <option value="0" selected>Select partner</option>
                                    <?php foreach ($partners as $partner) : ?>
                                        <option value="<?php echo $partner->ID; ?>" <?php selected($partner->ID, 26); ?>>
                                            <?php echo esc_html($partner->display_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label style="display:block;">Assign to agent</label>
                                <select name="agent_id" style="width:300px">
                                    <option value="0" selected>Select agent</option>
                                    <?php foreach ($agents as $agent) : ?>
                                        <option value="<?php echo $agent->ID; ?>" <?php selected($agent->ID, 26); ?>>
                                            <?php echo esc_html($agent->display_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                    </div>
                            <div>
                                <label><strong>RFQ Message / Details</strong></label>
                                <div style="margin-top:0px;">
                                    <?php 
                                    $settings = array(
                                        'textarea_name' => 'message', // This is the key for $_POST
                                        'media_buttons' => false,         // Hide Add Media for a cleaner look
                                        'textarea_rows' => 25,
                                        'teeny'         => true,          // Simple toolbar
                                        'quicktags'     => true
                                    );
                                    wp_editor('', 'rfq_editor_id', $settings); 
                                    ?>
                                </div>
                            </div>

                <p class="submit">
                    <input type="submit" name="sls_submit_rfq" class="button button-primary" value="Save RFQ">
                    <a href="#" class="button" onclick="tb_remove(); return false;">Cancel</a>
                </p>
            </form>
        </div>

<?php else: ?> 
<?php
echo '<a href="admin.php?page=partner-rfqs&action=new" title="Create New RFQ" class=" page-title-action">Add New RFQ</a>';
echo '<hr class="wp-header-end">';
    if ($partner_id){
    echo '<div style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 4px; margin-bottom: 20px;">
            <h1 style="margin:0;">RFQs for: ' . esc_html($partner_user->display_name) . '</h1>
          </div>';
    }
    // --- 3. ACTIONS FORM ---
    echo '<form method="post">';
    echo '<div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="bulk_action">
                    <option value="-1">Bulk Actions</option>
                    <option value="active">Set to Active</option>
                    <option value="archive">Archive</option>
                    <option value="delete">Delete Permanently</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
          </div>';

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"></td>
                <th>Product</th>
                <th>Qty Requested</th>
                <th>Agent</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
          </thead><tbody>';

    if ($results) {
        foreach ($results as $row) {
            $product_url = get_permalink($row->product_id);
            $product_name = get_the_title($row->product_id);
            
            // Subtle Post Status indicator
            $status_label = ($row->post_status === 'archive') ? ' <span style="font-size:9px; background:#eee; padding:2px 4px; color:#666; border-radius:3px;">ARCHIVED</span>' : '';

            // Nonce-based URLs for the hover actions
            $base_url = "admin.php?page=partner-rfqs&partner_id=$partner_id&rfq_id={$row->id}";
            $archive_url = wp_nonce_url($base_url . "&action=archive", 'rfq_action_' . $row->id);
            $delete_url  = wp_nonce_url($base_url . "&action=delete", 'rfq_action_' . $row->id);
            $lead_url    = wp_nonce_url($base_url . "&action=send_to_lead", 'rfq_action_' . $row->id);
            $action_create_invoice    = wp_nonce_url("admin.php?page=partner-invoices&rfq_id={$row->id}&action=new", 'rfq_action_' . $row->id);
            
            echo "<tr>
                <th scope='row' class='check-column'><input type='checkbox' name='rfq_ids[]' value='{$row->id}'></th>
                <td>
                    <a href='" . esc_url($product_url) . "' target='_blank' style='font-weight:600; text-decoration:none;'>
                        " . esc_html($product_name) . "
                    </a>" . $status_label . "

                    <div class='row-actions'>
                        <span class='edit'><a href='{$lead_url}' title='Assign to Lead Agent'>Send to Lead</a> | </span>
                        <span class='inline hide-if-no-js'><a href='{$archive_url}' title='Move to Archive'>Archive</a> | </span>
                        <span class='trash'><a href='{$delete_url}' class='submitdelete' title='Delete permanently' onclick='return confirm(\"Are you sure?\")' style='color:#a00;'>Delete</a></span>
                    </div>

                </td>
                <td><strong>" . esc_html($row->inquired_qty) . " / <span class='type-pill'>" . esc_html($row->inquiry_type) . "</span></strong></td>
                <td>" . sls_get_user_agent($row->agent_id) . "</td>
                <td><span class='badge status-" . esc_attr($row->status) . "'>" . ucfirst($row->status) . "</span></td>
                <td>" . date('M j, Y', strtotime($row->created_at)) . "</td>
                <td><a href='{$action_create_invoice}'>Create Invoice</a></td>
            </tr>";
        }
    } else {
        echo '<tr><td colspan="6">No RFQs found.</td></tr>';
    }
    echo '</tbody></table></form>';

    ?>
    <?php endif; ?>
    <?php

    echo "</div>";

  //  sls_render_rfq_modal_form($partner_id);

}

add_action('admin_enqueue_scripts', function($hook) {
    // Only load on your specific RFQ page
    if (strpos($hook, 'partner-rfqs') !== false) {
        add_thickbox();
    }
});

// 1. Enqueue the scripts
add_action('wp_enqueue_scripts', function() {
    // 1. Register the jQuery UI Autocomplete (built into WP)
    wp_enqueue_script('jquery-ui-autocomplete');

    // 2. If your JS is in a file: 
    // wp_enqueue_script('my-custom-search', get_template_directory_uri() . '/js/search.js', array('jquery', 'jquery-ui-autocomplete'), null, true);

    // 3. THE FIX: Define the variable globally on the 'jquery' handle 
    // so it's available to all scripts depending on jQuery.
    wp_localize_script('jquery', 'wc_autocomplete', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
});

// 2. The AJAX search handler
add_action('wp_ajax_nopriv_product_search', 'handle_product_autocomplete');
add_action('wp_ajax_product_search', 'handle_product_autocomplete');

function handle_product_autocomplete() {
    $search_term = sanitize_text_field($_GET['term']);
    
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish', // Strictly published products
        'posts_per_page' => 10,
        's'              => $search_term,
    );

    $products = new WP_Query($args);
    $results  = array();

    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            global $product;
            $results[] = array(
                'id'    => get_the_ID(), // ADDED: Send the ID to JavaScript
                'label' => get_the_title(),
                'url'   => get_permalink(),
                'price' => $product->get_price_html(),
                'img'   => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail')
            );
        }
    }
    
    wp_send_json($results);
}


/**
 * Get Agent Thumbnail and Name with Profile Link
 */
function sls_get_user_agent($agent_id) {
    if (!$agent_id) {
        return '<span class="description" style="color:#d63638;">Unassigned</span>';
    }

    $user = get_userdata($agent_id);
    if (!$user) {
        return '<span class="description">Unknown Agent</span>';
    }

    // Generate the URL to the agent's profile in the WordPress admin
    $edit_link = get_edit_user_link($agent_id);
    
    // Get the avatar
    $avatar = get_avatar($agent_id, 32);

    return sprintf(
        '<a href="%s" class="agent-info-wrapper agent-cell" >
            <div class="agent-photo">%s</div>
            <div class="agent-name">%s</div>
        </a>',
        esc_url($edit_link),
        $avatar,
        esc_html($user->display_name)
    );
}
function inquiries_page_callback() {
    global $wpdb;

    $partner_id = isset($_GET['partner_id']) ? intval($_GET['partner_id']) : 0;

    if (!$partner_id) {
        echo '<div class="wrap"><h1>Inquiries</h1><div class="notice notice-error"><p>No Partner selected.</p></div></div>';
        return;
    }

    // Handle Delete Action if triggered
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['inquiry_id'])) {
        check_admin_referer('delete_inquiry_' . $_GET['inquiry_id']);
        $wpdb->delete('sls_inquiries', array('id' => intval($_GET['inquiry_id'])));
        echo '<div class="updated notice is-dismissible"><p>Inquiry deleted successfully.</p></div>';
    }

    // Fetch Agent Info for Header
    $agent_id = 26; // As per your specific requirement
    $agent_data = get_userdata($agent_id);
    $agent_name = $agent_data ? $agent_data->display_name : "Unassigned";

    // Query (Removed Inquiry Type Join)
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT i.* FROM sls_inquiries i 
        WHERE i.customer_id = %d AND i.type_id = 1 
        ORDER BY i.created_at DESC
    ", $partner_id));

    $partner_user = get_userdata($partner_id);

    echo '<div class="wrap">';
    echo '<div class="">';
    echo '<h1>Product Inquiries for: ' . esc_html($partner_user->display_name) . '</h1>';
    echo '</div>';

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Product</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Agent</th>
                <th>Date</th>
                <th style="width: 120px;">Actions</th>
            </tr>
          </thead><tbody>';

    if ($results) {
        foreach ($results as $row) {
            // Get the permalink for the specific product in this row
            $product_url = get_permalink($row->product_id);
            $product_name = get_the_title($row->product_id);

            // Nonce for secure deletion
            $delete_url = wp_nonce_url(
                admin_url("admin.php?page=partner-inquiries&partner_id=$partner_id&action=delete&inquiry_id={$row->id}"),
                'delete_inquiry_' . $row->id
            );

            echo "<tr>
                <td>{$row->id}</td>
                <td>
                    <a href='" . esc_url($product_url) . "' target='_blank' style='text-decoration:none; font-weight:600;'>
                        " . esc_html($product_name) . " 
                        <span class='dashicons dashicons-external' style='font-size:14px; vertical-align:middle;'></span>
                    </a>
                </td>
                <td>" . esc_html($row->subject) . "</td>
                <td>".sls_get_user_agent($row->agent_id)."</td>
                <td><span class='badge status-" . esc_attr($row->status) . "'>" . ucfirst($row->status) . "</span></td>
                <td>" . date('M j, Y', strtotime($row->created_at)) . "</td>
                <td>
                    <a href='" . admin_url("admin.php?page=view-inquiry&id=" . $row->id) . "' class='view-inquiry'>View</a> | 
                    <a href='{$delete_url}' class='submitdelete' onclick='return confirm(\"Are you sure?\")' style='color: #a00;'>Delete</a>
                </td>
            </tr>";
        }
    } else {
        echo '<tr><td colspan="6">No inquiries found.</td></tr>';
    }
    echo '</tbody></table></div>';
}

function send_partner_invitation($email) {
    // Generate a unique 16-character token
    $token = wp_generate_password(16, false);
    
    // Store the token in the database for 48 hours
    // Key format: partner_invite_{token} = {email}
    set_transient('partner_invite_' . $token, $email, 48 * HOUR_IN_SECONDS);
    $email_ecoded = base64_encode($email);
    // Create the secure URL
    echo $registration_url = home_url('/partner-registration/?invite_email='. $email_ecoded.'&token=' . $token);

    $subject = "Secure Invite: Join our Partner Program";
    $message = "You have been invited to join our Partner network.\n\n" . 
               "Please complete your registration using this secure link (expires in 48 hours):\n" . 
               $registration_url;

    wp_mail($email, $subject, $message);
}

add_shortcode('partner_registration_form', 'render_partner_registration_form');

function render_partner_registration_form() {
    if (is_user_logged_in()) return '<div class="alert alert-info">You are already registered and logged in.</div>';

    // 1. Validate Token
    $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
    $invited_email = get_transient('partner_invite_' . $token);
    // Enqueue Bootstrap from CDN for styling
   $stat = handle_partner_registration_submission();
  
    if ($stat === 'success') {     
        ?><style>
        body{
            font-size: 16px !important;
        }        a {
        text-decoration: none !important;
        }
        .bg-bluegreen {
            background-color: #0073aa !important;      
        }</style>
        <div class="container text-center mt-5 mb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="mt-5 mb-5   ">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3">Registration successful! Redirecting to your dashboard...</p>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                window.location.href = "<?=home_url('/partner-new-account/?status=registered&msg=thank-you')?>";
            }, 1000); // 1 second delay for a smooth transition
        </script>
        <?php
    } 


    if (!$invited_email) {
        return '<style>
        body{
            font-size: 16px !important;
        }        a {
        text-decoration: none !important;
        }
        .bg-bluegreen {
            background-color: #0073aa !important;      
        }</style><div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
            <div class="mt-5 mb-5   ">
             <div class="alert alert-danger">
                <strong>Invalid or Expired Link.</strong><br> 
                This invitation link is no longer valid. Please contact the administrator for a new invite.
                </div>
             </div>
             </div> 
             </div>
        </div>';
    }


    $invited_email = isset($_GET['invite_email']) ? $_GET['invite_email']: '';

    ob_start(); ?>
    <style>
        body{
            font-size: 16px !important;
        }
        a {
        text-decoration: none !important;
        }
        .form-label {
            font-weight: 600;
        }
        input[type=password], input[type=tel], input[type=text], input[type=email], select, textarea {
            border: 1px solid #d9d9d9;
            background: #fff;
            padding: 10px; 
            font-size: 14px !important;
        }
        input[type=email], input[type=text], input[type=tel]{
            font-size: 14px !important;
        }
        .partner-header {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .partner-filters { display: flex; gap: 10px; align-items: center; }
        .partner-card-container { margin-top: 20px; }
        .p-input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 250px; }
        .stat-badge { background: #e7f0ff; color: #0073aa; padding: 4px 12px; border-radius: 20px; font-weight: 600; font-size: 12px; }
    </style>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center"> 
            <div class="col-lg-10">
                <form id="partner-reg-form" method="post" class="needs-validation mb-5 mt-5" novalidate>
                    <?php wp_nonce_field('partner_reg_nonce_action', 'partner_reg_nonce'); ?>
                    <input type="hidden" name="invite_token" value="<?php echo esc_attr($token); ?>">
            
                    <div class="alert alert-success">
                        Verifying invitation for: <strong><?php echo base64_decode($invited_email); ?></strong>
                    </div>
                    <?php $invited_email = base64_decode($invited_email); ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-bluegreen text-dark">
                            <h4 class="mb-0 h2 p-3">1. Personal Profile</h4> 
                        </div>
                        <div class="card-body"> 
                            <div class="p-5">
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label">Username *</label><input type="text" name="reg_username" class="form-control" required></div>
                                    <div class="col-md-4"><label class="form-label">Password *</label><input type="password" name="reg_password" class="form-control" required></div>
                                    <div class="col-md-4"><label class="form-label">Email *</label><input type="email" name="reg_email" value="<?php echo esc_attr($invited_email); ?>" class="form-control" required readnly></div>
                                    <div class="col-md-6"><label class="form-label">Mobile</label><input type="text" name="profile_mobile" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="profile_phone" class="form-control" required></div>
                                    <div class="col-12 pl-4"><label class="form-label">Address Street</label><input type="text" name="profile_street" class="form-control" required></div>
                                    <div class="col-md-3"><label class="form-label">City</label><input type="text" name="profile_city" class="form-control" required></div>
                                    <div class="col-md-3"><label class="form-label">State</label><input type="text" name="profile_state" class="form-control"></div>
                                    <div class="col-md-3"><label class="form-label">Country</label><input type="text" name="profile_country" class="form-control" required></div>
                                    <div class="col-md-3"><label class="form-label">Zip</label><input type="text" name="profile_zip" class="form-control" required></div>
                                </div> 
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm ">
                        <div class="card-header bg-bluegreen text-dark">
                            <h4 class="mb-0 h2 p-3">2. Company Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="p-5">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Company Name</label><input type="text" name="co_name" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label">Person to Contact</label><input type="text" name="co_contact_person" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label">Company Email</label><input type="email" name="co_email" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label">Company Contact Number</label><input type="text" name="co_phone" class="form-control"></div>
                                <div class="col-12 pl-4"><label class="form-label">Company Address Street</label><input type="text" name="co_street" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label">City</label><input type="text" name="co_city" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label">State</label><input type="text" name="co_state" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label">Country</label><input type="text" name="co_country" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label">Zip</label><input type="text" name="co_zip" class="form-control"></div>
                            </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm ">
                        <div class="card-header bg-bluegreen text-dark">
                            <h4 class="mb-0 h2 p-3">3. Billing Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="p-5">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Billing Name</label><input type="text" name="billing_first_name" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label">Billing Contact Number</label><input type="text" name="billing_phone" class="form-control"></div>
                                <div class="col-12 pl-4"><label class="form-label">Billing Address Street</label><input type="text" name="billing_address_1" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label">City</label><input type="text" name="billing_city" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label">State</label><input type="text" name="billing_state" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label">Country</label><input type="text" name="billing_country" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label">Zip</label><input type="text" name="billing_postcode" class="form-control"></div>
                            </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="submit_partner_reg" class="btn btn-primary" style="text-decoration: none !important; font-weight: bold; font-size: 16px; display: inline-block; width: 300px">Complete Partner Registration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();

}

function handle_partner_registration_submission() {

    if (isset($_POST['submit_partner_reg']) && wp_verify_nonce($_POST['partner_reg_nonce'], 'partner_reg_nonce_action')) {
        
        $token = sanitize_text_field($_POST['invite_token']);
        $invited_email = get_transient('partner_invite_' . $token);
        $username = sanitize_user($_POST['reg_username']);
        $email    = sanitize_email($_POST['reg_email']);
        $password = $_POST['reg_password'];

        if (!$invited_email) {
            return '<div class="alert alert-danger">Security Error: Invalid Token.</div>';
        }

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            return '<div class="alert alert-danger mt-3">' . $user_id->get_error_message() . '</div>';
        }

        // Assign 'Partner' role
        $user = new WP_User($user_id);

        // ASSIGNING DUAL ROLES
        $user->set_role('customer'); // Set primary role to Customer
        $user->add_role('partner');  // Add Partner as a secondary role

        // Mapping Profile Data
        update_user_meta($user_id, 'mobile_number', sanitize_text_field($_POST['profile_mobile']));
        update_user_meta($user_id, 'phone_number', sanitize_text_field($_POST['profile_phone']));
        update_user_meta($user_id, 'profile_address_street', sanitize_text_field($_POST['profile_street']));

        // Mapping Company Data
        update_user_meta($user_id, 'billing_company', sanitize_text_field($_POST['co_name']));
        update_user_meta($user_id, 'company_email', sanitize_email($_POST['co_email']));
        update_user_meta($user_id, 'company_phone', sanitize_text_field($_POST['co_phone']));
        update_user_meta($user_id, 'contact_person', sanitize_text_field($_POST['co_contact_person']));

        // Mapping WooCommerce Billing Data (Important for Checkout)
        update_user_meta($user_id, 'billing_first_name', sanitize_text_field($_POST['billing_first_name']));
        update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
        update_user_meta($user_id, 'billing_address_1', sanitize_text_field($_POST['billing_address_1']));
        update_user_meta($user_id, 'billing_city', sanitize_text_field($_POST['billing_city']));
        update_user_meta($user_id, 'billing_state', sanitize_text_field($_POST['billing_state']));
        update_user_meta($user_id, 'billing_country', sanitize_text_field($_POST['billing_country']));
        update_user_meta($user_id, 'billing_postcode', sanitize_text_field($_POST['billing_postcode']));

        // verified email
        update_user_meta($user_id, 'is_email_verified', 1);

        delete_transient('partner_invite_' . $token);

        return 'success';
    }
} 

function sls_register_agent_role() {
    add_role(
        'sls_agent',      // Internal ID
        'Agent',          // Display Name
        array(
            'read'         => true,  // Can log in to the admin
            'edit_posts'   => false, // Cannot edit blog posts
            'upload_files' => true,  // Can upload invoices/docs
   
        )
    );
}
add_action('init', 'sls_register_agent_role');

add_action('admin_head', 'custom_all_partners_css');

function custom_all_partners_css() {
    // Check if we are on the specific "All Partners" page via the global $pagenow or the screen ID
    $screen = get_current_screen();

    if (!isset($_GET['page'])): return; endif;

    if (isset($_GET['page']) && $_GET['page'] == 'all-partners' || $_GET['page'] == 'partner-rfqs' || $screen->id === 'toplevel_page_partners-list') {
        echo '<style>
            /* Your custom CSS goes here */
            .admin-partner-listing {
                margin-top: 20px;
            } 
            .admin-partner-listing .view-extend span{

                background: #aa0000;
                color: #fff;
                margin-right: 8px;
                border-radius: 20px;
                padding: 3px 5px;
                font-size: 11px;
            }
            .submitdelete:hover {
            color: #dc3232 !important;
            }
            .view-inquiry {
                font-weight: 600;
                color: #2271b1;
            }

            .wp-list-table td { vertical-align: middle; }
            .status-quoted { background: #d4edda; color: #155724; }
            .status-negotiation { background: #e2e3e5; color: #383d41; }
            .row-actions {
                visibility: hidden;
                padding: 2px 0 0;
            }

            tr:hover .row-actions {
                visibility: visible;
            }

            /* Optional styling for the "Send to Lead" to make it stand out */
            .row-actions .edit a {
                color: #2271b1;
                font-weight: 500;
            }
            .agent-photo img {
            border-radius: 50% !important; /* Makes it a circle */
            width: 32px;
            height: 32px;
            object-fit: cover; /* Ensures the image isn\'t stretched */
            border: 1px solid #dcdcde; /* Subtle border */
            display: block;
        }
        
        .agent-info-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .agent-name strong {
            font-weight: 600;
            color: #2c3338;
        }
        .admin-partner-page{
            padding:20px;
        }
        
        .admin-partner-page form input, .admin-partner-page form select{
            padding:2px 10px !important; font-size:14px;
        }
        .admin-partner-page form label{
            margin-bottom:3px;
        }
        .admin-partner-page form .wc-product-search{
            width:60%;
        }
        </style>';

        ?>

        <script>
jQuery(document).ready(function($) {
    if (typeof wc_autocomplete === 'undefined') {
        var wc_autocomplete = { ajax_url: '/wp-admin/admin-ajax.php' };
    }

    $('.wc-product-search').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: wc_autocomplete.ajax_url,
                data: {
                    action: 'product_search',
                    term: request.term
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
            $('input[name="product_id"]').val(ui.item.id);
            console.log("Selected:", ui.item.label);
            return false;
        }
    })
    /**
     * This part handles the visual "look" of the list item
     */
    .data("ui-autocomplete")._renderItem = function(ul, item) {
        return $("<li>")
            .append(
                "<div style='display: flex; align-items: center; padding: 5px;'>" +
                    "<img src='" + item.img + "' style='width: 40px; height: 40px; margin-right: 10px; object-fit: cover; border-radius: 4px;'>" +
                    "<div>" +
                        "<div style='font-weight: bold; font-size: 14px;'>" + item.label + "</div>" +
                        "<div style='font-size: 12px; color: #666;'>" + item.price + "</div>" +
                    "</div>" +
                "</div>"
            )
            .appendTo(ul);
    };
});
        </script>

        <?php
    }
}

/**
 * Display the list of Partners and the Invitation Form
 */function render_partners_page() {
    // Handle Invitation Logic

    // 1. Handle the Form Submission with Validation
    if (isset($_POST['send_partner_invite']) && check_admin_referer('invite_partner_action', 'partner_nonce')) {
        $email = sanitize_email($_POST['partner_email']);
        
        if (!is_email($email)) {
            echo '<div class="error"><p>Please enter a valid email address.</p></div>';
        } 
        // CHECK 1: Does the user already exist in WordPress?
        elseif (email_exists($email)) {
            $existing_user = get_user_by('email', $email);
            echo '<div class="error"><p><strong>Error:</strong> A user with the email "' . esc_html($email) . '" already exists as a ' . esc_html(implode(', ', $existing_user->roles)) . '.</p></div>';
        } 
        else {
            // CHECK 2: Is there a pending invitation for this email?
            // (Optional: You can iterate through transients if you want to be very strict)
            
            send_partner_invitation($email);
            echo '<div class="updated notice is-dismissible"><p>Invitation successfully sent to <strong>' . esc_html($email) . '</strong>.</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Partner Management</h1>
        <hr class="wp-header-end">

        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 15px;">
            <h2>Invite & Export</h2>
            <div style="display: flex; gap: 20px; align-items: flex-end;">
                <form method="post" action="">
                    <?php wp_nonce_field('invite_partner_action', 'partner_nonce'); ?>
                    <input type="email" name="partner_email" placeholder="partner@email.com" class="regular-text" required>
                    <input type="submit" name="send_partner_invite" class="button button-primary" value="Send Invitation">
                </form>

                <form method="post" action="">
                    <input type="submit" name="export_partners_csv" class="button button-secondary" value="Download Partners CSV">
                </form>
            </div>
        </div>

        <h2 style="margin-top: 30px;">Partner Directory</h2>
        <div class="admin-partner-listing">
        <table class="wp-list-table widefat fixed striped table-view-list users">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Company Name</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Agent</th>
                    <th>Inquiries</th>
                    <th>RFQs</th>
                    <th>Invoices</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $partners = get_users(array('role' => 'partner'));
                if (!empty($partners)) {
                    foreach ($partners as $partner) {
                        $co_name = get_user_meta($partner->ID, 'billing_company', true);
                        $contact = get_user_meta($partner->ID, 'contact_person', true);
                        ?>
                        <tr class="partner-row">
                            <td><strong><?php echo esc_html($partner->user_login); ?></strong></td>
                            <td><?php echo esc_html($co_name ?: '—'); ?></td>
                            <td><?php echo esc_html($contact ?: '—'); ?></td>
                            <td><?php echo esc_html($partner->user_email); ?></td>
                            <td><?php 
                                $agents = get_users(array(
                                    'role'    => 'sls_agent',
                                    'meta_key' => 'assigned_partner',
                                    'meta_value' => $partner->ID
                                ));
                                if (!empty($agents)) {
                                    foreach ($agents as $agent) {
                                        echo esc_html($agent->user_login) . '<br>';
                                    }
                                } else {
                                    echo '—';
                                }   ?>
                            <td><a href="<?php echo admin_url('admin.php?page=partner-inquiries&partner_id=' . $partner->ID); ?>" class="view-extend"><span>20</span>View Inquiries</a></td>
                            <td><a href="<?php echo admin_url('admin.php?page=partner-rfqs&partner_id=' . $partner->ID); ?>" class="view-extend"><span>10</span>View RFQs</a></td>
                            <td><a href="<?php echo admin_url('admin.php?page=partner-invoices&partner_id=' . $partner->ID); ?>" class="view-extend"><span>2</span>View Invoices</a></td>
                            <td><a href="<?php echo get_edit_user_link($partner->ID); ?>" class="button button-small">View Full Profile</a></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="5">No partners found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    </div>
    <?php
}

add_action('admin_init', 'handle_partner_csv_export');

function handle_partner_csv_export() {
    if (isset($_POST['export_partners_csv'])) {
        $partners = get_users(array('role' => 'partner'));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=partners_export_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, array(
            'Username', 'Email', 'Mobile', 'Phone', 'Profile Address', 
            'Company Name', 'Contact Person', 'Company Email', 'Company Phone',
            'Billing Name', 'Billing Address', 'Billing City', 'Billing Zip'
        ));

        foreach ($partners as $partner) {
            $uid = $partner->ID;
            fputcsv($output, array(
                $partner->user_login,
                $partner->user_email,
                get_user_meta($uid, 'mobile_number', true),
                get_user_meta($uid, 'phone_number', true),
                get_user_meta($uid, 'profile_address_street', true),
                get_user_meta($uid, 'billing_company', true),
                get_user_meta($uid, 'contact_person', true),
                get_user_meta($uid, 'company_email', true),
                get_user_meta($uid, 'company_phone', true),
                get_user_meta($uid, 'billing_first_name', true),
                get_user_meta($uid, 'billing_address_1', true),
                get_user_meta($uid, 'billing_city', true),
                get_user_meta($uid, 'billing_postcode', true),
            ));
        }

        fclose($output);
        exit;
    }
}


add_shortcode('partner_notice', 'render_partner_notice');

function render_partner_notice() {
    if (isset($_GET['status']) && $_GET['status'] === 'registered' && isset($_GET['msg']) && $_GET['msg'] === 'thank-you') {
        return '<div class="container mt-5 mb-5"><div class="row justify-content-center"><div class="col-md-10"><div class="mt-5 mb-5"><div class=""><h2 class="alert-heading mb-5">🎉 Registration Successful!</h2>
                <p>Thank you for registering as a partner! Your account is currently being configured, and you will receive a confirmation email shortly.</p>
                <hr>
                <p class="mb-0">Ready to start? <a href="' . esc_url(home_url('/account-login/?accounttype=partner')) . '" class="fw-bold text-decoration-none">Log in to your account here.</a></p></div></div></div></div></div>';
    }
    return '';
}

// Arthur Start Code Here <----
// Get Whole sale page Inquiries type Chat by customer / current user logged in
function getWholesaleInquiriesChatByCustomer($inquiry_id){

    global $wpdb;

    if($inquiry_id == null){

        return null;

    }

    $current_user_id = wp_get_current_user()->ID;

    $table_name = $wpdb->prefix . 'inquiries';
    // $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE customer_id = %d AND type_id = %d", $current_user_id, 1), ARRAY_A);
    $results = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $inquiry_id), ARRAY_A);

    return $results;
}

function getAgentById($agent_id = null){

    global $wpdb;

    if($agent_id == null){

        return [];

    }

    return get_user_by('id', $agent_id);

}

// Arthur End Code Here <----