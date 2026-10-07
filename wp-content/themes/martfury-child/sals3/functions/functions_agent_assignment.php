<?php
/**
 * Dedicated Agent Assignment
 * Each customer/partner gets one permanently assigned sls_agent (round-robin by load).
 * Assignment is stored in user meta `assigned_agent_id`.
 */

// ─── Core assignment function ─────────────────────────────────────────────────

/**
 * Returns the dedicated agent data for a user.
 * If the user has no assignment yet, picks the agent with the fewest assigned users
 * (round-robin), saves it, then returns it.
 * Falls back to a random sls_agent, then to user ID 28.
 *
 * @param  int   $user_id  WP user ID (0 for guests).
 * @return array { id, name, email }
 */
function as_get_or_assign_agent( $user_id = 0 ) {
    global $wpdb;

    // 1. Return saved assignment if it exists and the agent is still active.
    if ( $user_id ) {
        $saved_id = (int) get_user_meta( $user_id, 'assigned_agent_id', true );
        if ( $saved_id ) {
            $agent = get_userdata( $saved_id );
            if ( $agent ) {
                return [ 'id' => $agent->ID, 'name' => $agent->display_name, 'email' => $agent->user_email ];
            }
            // Agent was deleted — clear stale meta and re-assign below.
            delete_user_meta( $user_id, 'assigned_agent_id' );
        }
    }

    // 2. Get all active sls_agents.
    $agents = get_users( [
        'role'    => 'sls_agent',
        'fields'  => [ 'ID', 'user_email', 'display_name' ],
        'orderby' => 'ID',
        'order'   => 'ASC',
        'number'  => -1,
    ] );

    // Fallback: try legacy role.
    if ( empty( $agents ) ) {
        $agents = get_users( [
            'role'   => 'user-agent',
            'fields' => [ 'ID', 'user_email', 'display_name' ],
            'number' => -1,
        ] );
    }

    // Last resort fallback.
    if ( empty( $agents ) ) {
        $fallback = get_userdata( 28 );
        return [
            'id'    => 28,
            'name'  => $fallback ? $fallback->display_name : 'Admin',
            'email' => $fallback ? $fallback->user_email  : 'admin@anythingsupplies.com',
        ];
    }

    // 3. Round-robin: pick agent with fewest assigned users.
    $min_count    = PHP_INT_MAX;
    $selected     = $agents[0]; // default to first

    foreach ( $agents as $candidate ) {
        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'assigned_agent_id' AND meta_value = %d",
            $candidate->ID
        ) );
        if ( $count < $min_count ) {
            $min_count = $count;
            $selected  = $candidate;
        }
    }

    // 4. Persist the assignment.
    if ( $user_id ) {
        update_user_meta( $user_id, 'assigned_agent_id', $selected->ID );
        error_log( "[AGENT] User {$user_id} assigned to agent {$selected->ID} ({$selected->display_name})\n", 3, CJSYNC . '/wccj_error.log' );
    }

    return [ 'id' => $selected->ID, 'name' => $selected->display_name, 'email' => $selected->user_email ];
}

// ─── Auto-assign on registration ─────────────────────────────────────────────

add_action( 'user_register', 'as_auto_assign_agent_on_register', 20 );

function as_auto_assign_agent_on_register( $user_id ) {
    $user = get_userdata( $user_id );
    if ( ! $user ) return;

    // Skip admins and agents themselves.
    $skip_roles = [ 'administrator', 'sls_agent', 'agent', 'user-agent' ];
    if ( array_intersect( $skip_roles, (array) $user->roles ) ) return;

    as_get_or_assign_agent( $user_id );
}

// ─── Admin: user list column ──────────────────────────────────────────────────

add_filter( 'manage_users_columns', 'as_agent_column_header' );
function as_agent_column_header( $columns ) {
    $columns['assigned_agent'] = 'Assigned Agent';
    return $columns;
}

add_filter( 'manage_users_custom_column', 'as_agent_column_value', 10, 3 );
function as_agent_column_value( $value, $column, $user_id ) {
    if ( $column !== 'assigned_agent' ) return $value;

    $agent_id = (int) get_user_meta( $user_id, 'assigned_agent_id', true );
    if ( ! $agent_id ) return '<span style="color:#bbb;">—</span>';

    $agent = get_userdata( $agent_id );
    return $agent
        ? '<span style="color:#2c3e50;font-weight:600;">' . esc_html( $agent->display_name ) . '</span>'
        : '<span style="color:#c00;">Deleted (#' . $agent_id . ')</span>';
}

add_filter( 'manage_users_sortable_columns', function( $cols ) {
    $cols['assigned_agent'] = 'assigned_agent';
    return $cols;
} );

// ─── Admin: user profile field ────────────────────────────────────────────────

add_action( 'show_user_profile', 'as_agent_profile_field' );
add_action( 'edit_user_profile', 'as_agent_profile_field' );

function as_agent_profile_field( $user ) {
    $skip_roles = [ 'administrator', 'sls_agent', 'agent', 'user-agent' ];
    if ( array_intersect( $skip_roles, (array) $user->roles ) ) return;

    $current_agent_id = (int) get_user_meta( $user->ID, 'assigned_agent_id', true );

    $agents = get_users( [
        'role'    => 'sls_agent',
        'fields'  => [ 'ID', 'display_name' ],
        'orderby' => 'display_name',
        'order'   => 'ASC',
        'number'  => -1,
    ] );
    ?>
    <h2>Agent Assignment</h2>
    <table class="form-table">
        <tr>
            <th><label for="assigned_agent_id">Dedicated Agent</label></th>
            <td>
                <select name="assigned_agent_id" id="assigned_agent_id">
                    <option value="">— Auto-assign on next inquiry —</option>
                    <?php foreach ( $agents as $a ) : ?>
                        <option value="<?php echo (int) $a->ID; ?>" <?php selected( $current_agent_id, $a->ID ); ?>>
                            <?php echo esc_html( $a->display_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">
                    The agent who handles all inquiries from this user. Leave blank to auto-assign via round-robin.
                </p>
            </td>
        </tr>
    </table>
    <?php
}

add_action( 'personal_options_update',  'as_save_agent_profile_field' );
add_action( 'edit_user_profile_update', 'as_save_agent_profile_field' );

function as_save_agent_profile_field( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) return;
    if ( ! isset( $_POST['assigned_agent_id'] ) ) return;

    $new_agent_id = (int) $_POST['assigned_agent_id'];
    if ( $new_agent_id ) {
        update_user_meta( $user_id, 'assigned_agent_id', $new_agent_id );
    } else {
        delete_user_meta( $user_id, 'assigned_agent_id' );
    }
}