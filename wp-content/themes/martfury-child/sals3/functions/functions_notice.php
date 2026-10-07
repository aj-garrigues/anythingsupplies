<?php
add_action('admin_notices', function() {
    if (!empty($_GET['synced']) && $_GET['synced'] == 1) {
        echo '<div class="notice notice-success is-dismissible">
            <p>Product successfully synced from CJDS JSON.</p>
        </div>';
    }
	
	if (!empty($_GET['filejson']) && $_GET['filejson'] == 'no') {
            echo '<div style="background:red; color:#fff" class="notice notice-success is-dismissible">
            <p>Product CANNOT find in the file json - process the CJ SYNC to get latest info of this product.</p>
            </div>';
        }
});

add_action('admin_notices', function() {
    if (isset($_GET['error'])) {
        $error = sanitize_text_field($_GET['error']);
        $messages = [
            'no_variations' => 'Product has no variations. Set to draft.',
            'no_variant_sku' => 'First variation has no SKU.',
            'invalid_sku' => 'Neither parent nor variant has valid CJ SKU. Product set to draft.',
            'pid_not_found' => 'Could not find product in CJ Dropshipping.',
            'sku_not_found' => 'Could not retrieve product SKU from CJ.'
        ];
        
        if (isset($messages[$error])) {
            $debug_info = '';
            if (isset($_GET['debug_sku'])) {
                $debug_info = ' (Searched SKU: ' . esc_html($_GET['debug_sku']) . ')';
            }
            if (isset($_GET['debug_pid'])) {
                $debug_info = ' (PID: ' . esc_html($_GET['debug_pid']) . ')';
            }
            
            echo '<div class="notice notice-error is-dismissible">
                <p><strong>Error:</strong> ' . esc_html($messages[$error]) . $debug_info . '</p>
            </div>';
        }
    }
});


add_action('admin_notices', function() {
	
    if (!empty($_GET['skuinvalid']) && $_GET['skuinvalid'] == 'yes') {
        echo '<div style="background:red; color:#fff" class="notice notice-success is-dismissible">
        <p>Invalid SKU, try to upadte product from JC file.</p>
        </div>';
    }
	if (!empty($_GET['status']) && $_GET['status'] == 'failed') {
        echo '<div style="background:red; color:#fff" class="notice notice-warning is-dismissible">
        <p>'.$_GET['des'].'.</p>
        </div>';
    }
	if (!empty($_GET['status']) && $_GET['status'] == 'success') {
        echo '<div class="updated ">
        <p>'.$_GET['des'].'.</p>
        </div>';
    }
	if (isset($_GET['error']) && $_GET['error'] === 'sku_conflict') {
        $conflict_id = isset($_GET['conflict_id']) ? intval($_GET['conflict_id']) : 0;
        $conflict_sku = isset($_GET['conflict_sku']) ? sanitize_text_field($_GET['conflict_sku']) : '';
        
        $conflict_link = $conflict_id ? ' <a href="' . admin_url("post.php?post={$conflict_id}&action=edit") . '" target="_blank"><strong>View conflicting product (ID: ' . $conflict_id . ') →</strong></a>' : '';
        
        echo '<div class="notice notice-error">
            <p><strong>⚠️ SKU Conflict Detected!</strong></p>
            <p>The SKU <code>' . esc_html($conflict_sku) . '</code> already exists on another product.</p>
            <p>This product has been set to <strong>DRAFT</strong> to prevent duplicate SKUs.</p>
            ' . $conflict_link . '
            <p style="margin-top:10px;"><em>To resolve: Either delete the conflicting product or manually change one of the SKUs.</em></p>
        </div>';
    }
});

// Show bulk sync results
add_action('admin_notices', 'cjds_bulk_sync_admin_notice');
function cjds_bulk_sync_admin_notice() {
    if (!empty($_GET['cjds_bulk_synced'])) {
        $synced = intval($_GET['cjds_bulk_synced']);
        $failed = intval($_GET['cjds_bulk_failed'] ?? 0);
        $skipped = intval($_GET['cjds_bulk_skipped'] ?? 0);
        $conflicts = intval($_GET['cjds_bulk_conflicts'] ?? 0);
        
        $notice_class = ($failed > 0 || $conflicts > 0) ? 'notice-warning' : 'notice-success';
        
        echo '<div class="notice ' . $notice_class . ' is-dismissible">';
        echo '<p><strong>CJDS Bulk Sync Complete:</strong></p>';
        echo '<ul style="margin-left: 20px;">';
        echo '<li><strong>' . $synced . '</strong> products synced successfully</li>';
        
        if ($skipped > 0) {
            echo '<li>⏭<strong>' . $skipped . '</strong> products skipped (not published/draft)</li>';
        }
        if ($conflicts > 0) {
            echo '<li><strong>' . $conflicts . '</strong> products set to draft (SKU conflicts detected)</li>';
        }
        if ($failed > 0) {
            echo '<li><strong>' . $failed . '</strong> products failed (check error log)</li>';
        }
        
        echo '</ul>';
        echo '</div>';
    }
}