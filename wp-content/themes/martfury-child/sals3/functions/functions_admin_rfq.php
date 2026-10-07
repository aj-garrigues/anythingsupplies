<?php
/**
 * Admin RFQ Inquiries Menu
 * Adds "RFQ Inquiries" under WooCommerce > Orders in the admin sidebar.
 */

add_action( 'admin_menu', 'as_rfq_admin_menu' );

function as_rfq_admin_menu() {
    add_submenu_page(
        'woocommerce',
        'RFQ Inquiries',
        'RFQ Inquiries',
        'manage_woocommerce',
        'as-rfq-inquiries',
        'as_rfq_admin_page'
    );
}

// Push it right below Orders (position 2) in the WC submenu
add_action( 'admin_menu', 'as_rfq_reorder_submenu', 999 );

function as_rfq_reorder_submenu() {
    global $submenu;
    if ( empty( $submenu['woocommerce'] ) ) return;

    $target_slug = 'as-rfq-inquiries';
    $found       = null;

    foreach ( $submenu['woocommerce'] as $key => $item ) {
        if ( $item[2] === $target_slug ) {
            $found = $item;
            unset( $submenu['woocommerce'][ $key ] );
            break;
        }
    }

    if ( ! $found ) return;

    $submenu['woocommerce'] = array_values( $submenu['woocommerce'] );

    // Find Orders position and insert after it
    $insert_after = 0;
    foreach ( $submenu['woocommerce'] as $i => $item ) {
        if ( $item[2] === 'edit.php?post_type=shop_order' || $item[2] === 'wc-orders' ) {
            $insert_after = $i + 1;
            break;
        }
    }

    array_splice( $submenu['woocommerce'], $insert_after, 0, [ $found ] );
}

// ─── Styles ──────────────────────────────────────────────────────────────────

add_action( 'admin_head', 'as_rfq_admin_styles' );

function as_rfq_admin_styles() {
    $screen = get_current_screen();
    if ( ! $screen || strpos( $screen->id, 'as-rfq-inquiries' ) === false ) return;
    ?>
    <style>
        .rfq-wrap { margin: 20px 20px 0 0; }
        .rfq-filters { display:flex; gap:12px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
        .rfq-filters input[type=text], .rfq-filters input[type=date], .rfq-filters select { height:34px; padding:0 10px; border:1px solid #c3c4c7; border-radius:4px; }
        .rfq-filters .date-range { display:flex; align-items:center; gap:6px; }
        .rfq-filters .date-range label { font-size:12px; color:#666; white-space:nowrap; }
        .rfq-table { border-collapse:collapse; width:100%; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.1); border-radius:6px; overflow:hidden; }
        .rfq-table th { background:#2c3e50; color:#fff; padding:12px 14px; text-align:left; font-size:12px; text-transform:uppercase; letter-spacing:.5px; }
        .rfq-table td { padding:11px 14px; border-bottom:1px solid #f0f0f0; font-size:13px; vertical-align:middle; }
        .rfq-table tr:last-child td { border-bottom:none; }
        .rfq-table tr:hover td { background:#f9f9f9; }
        .rfq-badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; text-transform:uppercase; }
        .rfq-badge.open       { background:#d1ecf1; color:#0c5460; }
        .rfq-badge.pending    { background:#fff3cd; color:#856404; }
        .rfq-badge.processing { background:#cce5ff; color:#004085; }
        .rfq-badge.completed  { background:#d4edda; color:#155724; }
        .rfq-badge.cancelled  { background:#f8d7da; color:#721c24; }
        .rfq-badge.new        { background:#e2d9f3; color:#4a1f9e; }
        .rfq-detail-toggle { cursor:pointer; color:#2271b1; text-decoration:underline; background:none; border:none; padding:0; font-size:13px; }
        .rfq-products-row td { background:#f6f7f7; padding:0; }
        .rfq-products-inner { padding:12px 14px; }
        .rfq-products-inner table { width:100%; border-collapse:collapse; font-size:12px; }
        .rfq-products-inner th { background:#eee; padding:7px 10px; }
        .rfq-products-inner td { padding:7px 10px; border-bottom:1px solid #e5e5e5; }
        .rfq-pagination { margin-top:16px; display:flex; gap:6px; align-items:center; }
        .rfq-pagination a, .rfq-pagination span { padding:5px 12px; border:1px solid #c3c4c7; border-radius:4px; text-decoration:none; font-size:13px; }
        .rfq-pagination span.current { background:#2c3e50; color:#fff; border-color:#2c3e50; }
        .rfq-stat-cards { display:flex; gap:14px; margin-bottom:20px; flex-wrap:wrap; }
        .rfq-stat { background:#fff; border-left:4px solid #2c3e50; padding:14px 20px; border-radius:6px; box-shadow:0 1px 3px rgba(0,0,0,.08); min-width:140px; }
        .rfq-stat .num  { font-size:28px; font-weight:700; color:#2c3e50; }
        .rfq-stat .lbl  { font-size:12px; color:#666; text-transform:uppercase; letter-spacing:.5px; }
        .rfq-stat.green { border-color:#28a745; } .rfq-stat.green .num { color:#28a745; }
        .rfq-stat.blue  { border-color:#007bff; } .rfq-stat.blue  .num { color:#007bff; }
        .rfq-stat.red   { border-color:#dc3545; } .rfq-stat.red   .num { color:#dc3545; }
    </style>
    <?php
}

// ─── Page render ─────────────────────────────────────────────────────────────

function as_rfq_admin_page() {
    global $wpdb;

    $table      = $wpdb->prefix . 'inquiries';
    $table_prod = $wpdb->prefix . 'inquiry_products';

    $per_page   = 20;
    $page       = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
    $offset     = ( $page - 1 ) * $per_page;

    // Filters
    $search     = sanitize_text_field( $_GET['s'] ?? '' );
    $status_f   = sanitize_text_field( $_GET['rfq_status'] ?? '' );
    $agent_f    = (int) ( $_GET['agent_id'] ?? 0 );
    $date_from  = sanitize_text_field( $_GET['date_from'] ?? '' );
    $date_to    = sanitize_text_field( $_GET['date_to'] ?? '' );

    $where  = 'WHERE 1=1';
    $params = [];

    if ( $search ) {
        $where   .= ' AND (i.name LIKE %s OR i.customer_email LIKE %s OR i.inquired_key LIKE %s)';
        $like     = '%' . $wpdb->esc_like( $search ) . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if ( $status_f ) {
        $where   .= ' AND i.status = %s';
        $params[] = $status_f;
    }
    if ( $agent_f ) {
        $where   .= ' AND i.agent_id = %d';
        $params[] = $agent_f;
    }
    if ( $date_from ) {
        $where   .= ' AND DATE(i.created_at) >= %s';
        $params[] = $date_from;
    }
    if ( $date_to ) {
        $where   .= ' AND DATE(i.created_at) <= %s';
        $params[] = $date_to;
    }

    $base_sql = "FROM {$table} i {$where}";
    $count_sql = $params
        ? $wpdb->prepare( "SELECT COUNT(*) {$base_sql}", ...$params )
        : "SELECT COUNT(*) {$base_sql}";

    $total     = (int) $wpdb->get_var( $count_sql );
    $total_pages = ceil( $total / $per_page );

    $data_sql = $params
        ? $wpdb->prepare( "SELECT i.* {$base_sql} ORDER BY i.id DESC LIMIT %d OFFSET %d", ...[...$params, $per_page, $offset] )
        : $wpdb->prepare( "SELECT i.* {$base_sql} ORDER BY i.id DESC LIMIT %d OFFSET %d", $per_page, $offset );

    $rows = $wpdb->get_results( $data_sql, ARRAY_A );

    // Stat counts
    $stats = $wpdb->get_results( "SELECT status, COUNT(*) as cnt FROM {$table} GROUP BY status", ARRAY_A );
    $stat_map = [ 'total' => $total ];
    foreach ( $stats as $s ) $stat_map[ $s['status'] ] = (int) $s['cnt'];

    // Agents list for filter
    $agents = $wpdb->get_results( "SELECT DISTINCT agent_id FROM {$table} WHERE agent_id > 0" );

    // Build page URL helper
    $base_url = admin_url( 'admin.php?page=as-rfq-inquiries' );
    $filter_qs = ( $search ? '&s=' . urlencode( $search ) : '' )
               . ( $status_f ? '&rfq_status=' . urlencode( $status_f ) : '' )
               . ( $agent_f ? '&agent_id=' . $agent_f : '' )
               . ( $date_from ? '&date_from=' . urlencode( $date_from ) : '' )
               . ( $date_to ? '&date_to=' . urlencode( $date_to ) : '' );

    ?>
    <div class="wrap rfq-wrap">
        <h1 class="wp-heading-inline">RFQ Inquiries</h1>
        <hr class="wp-header-end">

        <!-- Stat Cards -->
        <div class="rfq-stat-cards">
            <div class="rfq-stat">
                <div class="num"><?php echo esc_html( $stat_map['total'] ?? 0 ); ?></div>
                <div class="lbl">Total</div>
            </div>
            <div class="rfq-stat blue">
                <div class="num"><?php echo esc_html( $stat_map['pending'] ?? 0 ); ?></div>
                <div class="lbl">Pending</div>
            </div>
            <div class="rfq-stat green">
                <div class="num"><?php echo esc_html( $stat_map['completed'] ?? 0 ); ?></div>
                <div class="lbl">Completed</div>
            </div>
            <div class="rfq-stat red">
                <div class="num"><?php echo esc_html( $stat_map['cancelled'] ?? 0 ); ?></div>
                <div class="lbl">Cancelled</div>
            </div>
        </div>

        <!-- Filters -->
        <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
            <input type="hidden" name="page" value="as-rfq-inquiries">
            <div class="rfq-filters">
                <input type="text" name="s" placeholder="Search name, email, key…" value="<?php echo esc_attr( $search ); ?>">
                <select name="rfq_status">
                    <option value="">All Statuses</option>
                    <?php foreach ( [ 'open', 'new', 'pending', 'processing', 'completed', 'cancelled' ] as $st ) : ?>
                        <option value="<?php echo $st; ?>" <?php selected( $status_f, $st ); ?>><?php echo ucfirst( $st ); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="agent_id">
                    <option value="">All Agents</option>
                    <?php foreach ( $agents as $a ) :
                        $u = get_userdata( $a->agent_id );
                        if ( ! $u ) continue;
                    ?>
                        <option value="<?php echo (int) $a->agent_id; ?>" <?php selected( $agent_f, $a->agent_id ); ?>>
                            <?php echo esc_html( $u->display_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="date-range">
                    <label>From</label>
                    <input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>">
                    <label>To</label>
                    <input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>">
                </div>
                <button type="submit" class="button">Filter</button>
                <a href="<?php echo esc_url( $base_url ); ?>" class="button">Reset</a>
            </div>
        </form>

        <!-- Table -->
        <table class="rfq-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Key</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Agent</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Products</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty( $rows ) ) : ?>
                <tr><td colspan="9" style="text-align:center;padding:30px;color:#999;">No inquiries found.</td></tr>
            <?php else : ?>
                <?php foreach ( $rows as $row ) :
                    $agent    = get_userdata( $row['agent_id'] );
                    $agent_name = $agent ? $agent->display_name : '—';
                    $status   = $row['status'] ?? 'new';
                    $products = $wpdb->get_results(
                        $wpdb->prepare( "SELECT ip.*, p.post_title FROM {$table_prod} ip LEFT JOIN {$wpdb->posts} p ON p.ID = ip.product_id WHERE ip.inquired_key = %s", $row['inquired_key'] ),
                        ARRAY_A
                    );
                    $row_id = 'rfq-prod-' . $row['id'];
                ?>
                <tr>
                    <td><?php echo (int) $row['id']; ?></td>
                    <td><code><?php echo esc_html( $row['inquired_key'] ); ?></code></td>
                    <td><?php echo esc_html( $row['name'] ); ?></td>
                    <td><?php echo esc_html( $row['customer_email'] ); ?></td>
                    <td><?php echo esc_html( $row['contact_number'] ); ?></td>
                    <td><?php echo esc_html( $agent_name ); ?></td>
                    <td><span class="rfq-badge <?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status ); ?></span></td>
                    <td style="white-space:nowrap;"><?php
                        $ts = strtotime( $row['created_at'] );
                        if ( $ts && $ts > 0 ) {
                            echo '<span style="font-weight:600;">' . esc_html( date( 'M j, Y', $ts ) ) . '</span>';
                            echo '<br><span style="font-size:11px;color:#888;">' . esc_html( date( 'g:i a', $ts ) ) . '</span>';
                        } else {
                            echo '<span style="color:#bbb;font-size:12px;">—</span>';
                        }
                    ?></td>
                    <td>
                        <?php if ( $products ) : ?>
                            <button class="rfq-detail-toggle" onclick="rfqToggle('<?php echo esc_js( $row_id ); ?>')">
                                <?php echo count( $products ); ?> item<?php echo count( $products ) > 1 ? 's' : ''; ?> ▾
                            </button>
                        <?php else : ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ( $products ) : ?>
                <tr class="rfq-products-row" id="<?php echo esc_attr( $row_id ); ?>" style="display:none;">
                    <td colspan="9">
                        <div class="rfq-products-inner">
                            <strong>Message:</strong> <?php echo esc_html( $row['message'] ); ?><br><br>
                            <table>
                                <thead><tr><th>Product</th><th>SKU</th><th>Qty Requested</th><th>Note</th></tr></thead>
                                <tbody>
                                <?php foreach ( $products as $p ) :
                                    $product = wc_get_product( $p['product_id'] );
                                ?>
                                    <tr>
                                        <td>
                                            <?php if ( $product ) : ?>
                                                <a href="<?php echo esc_url( get_edit_post_link( $p['product_id'] ) ); ?>" target="_blank">
                                                    <?php echo esc_html( $product->get_name() ); ?>
                                                </a>
                                            <?php else : ?>
                                                <?php echo esc_html( $p['post_title'] ?? 'ID: ' . $p['product_id'] ); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $product ? esc_html( $product->get_sku() ) : '—'; ?></td>
                                        <td><?php echo (int) $p['requested_qty']; ?></td>
                                        <td><?php echo esc_html( $p['requested_msg'] ); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ( $total_pages > 1 ) : ?>
        <div class="rfq-pagination">
            <?php for ( $p = 1; $p <= $total_pages; $p++ ) :
                $url = $base_url . $filter_qs . '&paged=' . $p;
            ?>
                <?php if ( $p === $page ) : ?>
                    <span class="current"><?php echo $p; ?></span>
                <?php else : ?>
                    <a href="<?php echo esc_url( $url ); ?>"><?php echo $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <span style="color:#666;font-size:12px;">
                Showing <?php echo $offset + 1; ?>–<?php echo min( $offset + $per_page, $total ); ?> of <?php echo $total; ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <script>
    function rfqToggle(id) {
        var row = document.getElementById(id);
        if (!row) return;
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }
    </script>
    <?php
}