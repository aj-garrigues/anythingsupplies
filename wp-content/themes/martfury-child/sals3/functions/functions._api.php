<?php
/**
 * REST API endpoints for external platform integration.
 * Registers routes:
 *   GET/POST  /wp-json/as/v1/user
 *   GET/POST  /wp-json/as/v1/product
 *   GET       /wp-json/as/v1/products          — paginated product listing (30/page)
 *   GET       /wp-json/as/v1/inquiries         — paginated inquiry listing
 *   GET       /wp-json/as/v1/inquiry           — single inquiry by id or key
 *   GET       /wp-json/as/v1/users             — list users by role (?wprole=customer)
 *   GET       /wp-json/as/v1/orders            — orders by customer_id with pagination
 *   POST      /wp-json/as/v1/auth/agent        — agent login, returns user info
 *
 * Authentication: Bearer token defined in AS_API_SECRET constant.
 * Define it in wp-config.php:  define( 'AS_API_SECRET', 'your-secret-token' );
 */

if ( ! defined( 'AS_API_SECRET' ) ) {
    define( 'AS_API_SECRET', 'as_api_secret_change_me' );
}

add_action( 'rest_api_init', 'as_register_api_routes' );

function as_register_api_routes() {
    // --- User endpoint ---
    register_rest_route( 'as/v1', '/user', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'as_api_get_user',
            'permission_callback' => 'as_api_auth',
            'args'                => [
                'id'    => [ 'type' => 'integer' ],
                'email' => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_email' ],
            ],
        ],
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'as_api_create_user',
            'permission_callback' => 'as_api_auth',
            'args'                => [
                'password' => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ],
    ] );

    // --- Inquiries listing endpoint ---
    register_rest_route( 'as/v1', '/inquiries', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'as_api_get_inquiries',
            'permission_callback' => 'as_api_auth',
            'args'                => [
                'page'        => [ 'type' => 'integer', 'default' => 1,    'minimum' => 1 ],
                'per_page'    => [ 'type' => 'integer', 'default' => 30,   'minimum' => 1, 'maximum' => 100 ],
                'status'      => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
                'customer_id' => [ 'type' => 'integer' ],
                'agent_id'    => [ 'type' => 'integer' ],
                'search'      => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ],
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'as_api_create_inquiry',
            'permission_callback' => 'as_api_auth',
            'args'                => [
                'customer_id' => [ 'type' => 'integer', 'default' => 0 ],
                'name'        => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
                'email'       => [ 'type' => 'string',  'required' => true, 'sanitize_callback' => 'sanitize_email' ],
                'phone'       => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
                'message'     => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ],
                'products'    => [ 'type' => 'array',   'required' => true ],
            ],
        ],
    ] );

    // --- Agent auth endpoint ---
    register_rest_route( 'as/v1', '/auth/agent', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'as_api_auth_agent',
        'permission_callback' => '__return_true',
        'args'                => [
            'email'    => [ 'type' => 'string', 'required' => true,  'sanitize_callback' => 'sanitize_email' ],
            'password' => [ 'type' => 'string', 'required' => true ],
        ],
    ] );

    // --- Orders by customer endpoint ---
    register_rest_route( 'as/v1', '/orders', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'as_api_get_orders',
        'permission_callback' => 'as_api_auth',
        'args'                => [
            'customer_id' => [ 'type' => 'integer', 'required' => true ],
            'page'        => [ 'type' => 'integer', 'default' => 1,  'minimum' => 1 ],
            'per_page'    => [ 'type' => 'integer', 'default' => 10, 'minimum' => 1, 'maximum' => 50 ],
        ],
    ] );

    // --- Users by role endpoint ---
    register_rest_route( 'as/v1', '/users', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'as_api_get_users',
        'permission_callback' => 'as_api_auth',
        'args'                => [
            'wprole' => [ 'type' => 'string', 'default' => 'customer', 'sanitize_callback' => 'sanitize_text_field' ],
            'number' => [ 'type' => 'integer', 'default' => 100, 'minimum' => 1, 'maximum' => 500 ],
        ],
    ] );

    // --- Single inquiry endpoint ---
    register_rest_route( 'as/v1', '/inquiry', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'as_api_get_inquiry',
        'permission_callback' => 'as_api_auth',
        'args'                => [
            'id'  => [ 'type' => 'integer' ],
            'key' => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
        ],
    ] );

    // --- Products listing endpoint (paginated) ---
    register_rest_route( 'as/v1', '/products', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'as_api_get_products',
        'permission_callback' => 'as_api_auth',
        'args'                => [
            'page'     => [ 'type' => 'integer', 'default' => 1, 'minimum' => 1 ],
            'per_page' => [ 'type' => 'integer', 'default' => 30, 'minimum' => 1, 'maximum' => 100 ],
            'category' => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
            'search'   => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
            'orderby'  => [ 'type' => 'string',  'default' => 'date', 'enum' => [ 'date', 'title', 'price', 'popularity' ] ],
            'order'    => [ 'type' => 'string',  'default' => 'DESC', 'enum' => [ 'ASC', 'DESC' ] ],
        ],
    ] );

    // --- Product endpoint ---
    register_rest_route( 'as/v1', '/product', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'as_api_get_product',
            'permission_callback' => 'as_api_auth',
            'args'                => [
                'id'  => [ 'type' => 'integer' ],
                'sku' => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ],
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'as_api_create_product',
            'permission_callback' => 'as_api_auth',
        ],
    ] );
}

// ─── Auth ────────────────────────────────────────────────────────────────────

function as_api_auth( WP_REST_Request $request ) {
    $auth   = $request->get_header( 'Authorization' );
    $token  = $auth ? trim( str_replace( 'Bearer', '', $auth ) ) : $request->get_param( 'api_key' );

    if ( ! $token || ! hash_equals( AS_API_SECRET, $token ) ) {
        return new WP_Error( 'unauthorized', 'Invalid or missing API key.', [ 'status' => 401 ] );
    }

    return true;
}

// ─── User handlers ───────────────────────────────────────────────────────────

/**
 * GET /wp-json/as/v1/user?id=123
 * GET /wp-json/as/v1/user?email=foo@bar.com
 */
function as_api_get_user( WP_REST_Request $request ) {
    $id    = (int) $request->get_param( 'id' );
    $email = $request->get_param( 'email' );

    if ( $id ) {
        $user = get_userdata( $id );
    } elseif ( $email ) {
        $user = get_user_by( 'email', $email );
    } else {
        return new WP_Error( 'missing_param', 'Provide id or email.', [ 'status' => 400 ] );
    }

    if ( ! $user ) {
        return new WP_Error( 'not_found', 'User not found.', [ 'status' => 404 ] );
    }

    return rest_ensure_response( as_format_user( $user ) );
}

/**
 * POST /wp-json/as/v1/user
 * Body: { first_name, last_name, email, phone, company, role }
 */
function as_api_create_user( WP_REST_Request $request ) {
    $body = $request->get_json_params();

    $email = isset( $body['email'] ) ? sanitize_email( $body['email'] ) : '';
    if ( ! is_email( $email ) ) {
        return new WP_Error( 'invalid_email', 'A valid email is required.', [ 'status' => 400 ] );
    }

    if ( email_exists( $email ) ) {
        $user = get_user_by( 'email', $email );
        return rest_ensure_response( [ 'created' => false, 'user' => as_format_user( $user ) ] );
    }

    $username   = sanitize_user( strtolower( $body['first_name'] ?? '' ) . '.' . strtolower( $body['last_name'] ?? '' ) . '.' . wp_generate_password( 4, false ) );
    $password   = ! empty( $body['password'] ) ? $body['password'] : wp_generate_password( 16 );
    $role       = in_array( $body['role'] ?? '', [ 'customer', 'subscriber', 'editor' ], true ) ? $body['role'] : 'customer';

    $user_id = wp_insert_user( [
        'user_login'   => $username,
        'user_email'   => $email,
        'user_pass'    => $password,
        'first_name'   => sanitize_text_field( $body['first_name'] ?? '' ),
        'last_name'    => sanitize_text_field( $body['last_name'] ?? '' ),
        'display_name' => sanitize_text_field( trim( ( $body['first_name'] ?? '' ) . ' ' . ( $body['last_name'] ?? '' ) ) ),
        'role'         => $role,
    ] );

    if ( is_wp_error( $user_id ) ) {
        return new WP_Error( 'create_failed', $user_id->get_error_message(), [ 'status' => 500 ] );
    }

    update_user_meta( $user_id, 'is_email_verified', 1 );

    // WooCommerce billing meta
    update_user_meta( $user_id, 'billing_first_name', sanitize_text_field( $body['first_name'] ?? '' ) );
    update_user_meta( $user_id, 'billing_last_name',  sanitize_text_field( $body['last_name'] ?? '' ) );
    update_user_meta( $user_id, 'billing_email',      $email );
    update_user_meta( $user_id, 'billing_phone',      sanitize_text_field( $body['phone'] ?? '' ) );
    update_user_meta( $user_id, 'billing_company',    sanitize_text_field( $body['company'] ?? '' ) );

    return rest_ensure_response( [
        'created' => true,
        'user'    => as_format_user( get_userdata( $user_id ) ),
    ] );
}

function as_format_user( WP_User $user ) {
    return [
        'id'          => $user->ID,
        'email'       => $user->user_email,
        'username'    => $user->user_login,
        'first_name'  => $user->first_name,
        'last_name'   => $user->last_name,
        'display_name'=> $user->display_name,
        'role'        => implode( ', ', $user->roles ),
        'phone'       => get_user_meta( $user->ID, 'billing_phone', true ),
        'company'     => get_user_meta( $user->ID, 'billing_company', true ),
        'registered'  => $user->user_registered,
    ];
}

// ─── Products listing handler ────────────────────────────────────────────────

/**
 * GET /wp-json/as/v1/products
 * GET /wp-json/as/v1/products?page=2
 * GET /wp-json/as/v1/products?page=1&per_page=30&category=office-supplies&search=pen&orderby=price&order=ASC
 */
function as_api_get_products( WP_REST_Request $request ) {
    $page     = (int) $request->get_param( 'page' );
    $per_page = (int) $request->get_param( 'per_page' );
    $category = $request->get_param( 'category' );
    $search   = $request->get_param( 'search' );
    $orderby  = $request->get_param( 'orderby' );
    $order    = $request->get_param( 'order' );

    $args = [
        'status'         => 'publish',
        'limit'          => $per_page,
        'page'           => $page,
        'paginate'       => true,
        'order'          => $order,
        'orderby'        => $orderby === 'price' ? 'meta_value_num' : $orderby,
    ];

    if ( $orderby === 'price' ) {
        $args['meta_key'] = '_price';
    }

    if ( $category ) {
        $args['category'] = [ $category ];
    }

    if ( $search ) {
        $args['s'] = $search;
    }

    $result   = wc_get_products( $args );
    $products = [];

    foreach ( $result->products as $product ) {
        $products[] = as_format_product( $product );
    }

    $response = rest_ensure_response( [
        'page'        => $page,
        'per_page'    => $per_page,
        'total'       => (int) $result->total,
        'total_pages' => (int) $result->max_num_pages,
        'products'    => $products,
    ] );

    $response->header( 'X-WP-Total',      (int) $result->total );
    $response->header( 'X-WP-TotalPages', (int) $result->max_num_pages );

    return $response;
}

// ─── Product handlers ────────────────────────────────────────────────────────

/**
 * GET /wp-json/as/v1/product?id=456
 * GET /wp-json/as/v1/product?sku=ABC-123
 */
function as_api_get_product( WP_REST_Request $request ) {
    $id  = (int) $request->get_param( 'id' );
    $sku = $request->get_param( 'sku' );

    if ( $id ) {
        $product = wc_get_product( $id );
    } elseif ( $sku ) {
        $product_id = wc_get_product_id_by_sku( $sku );
        $product    = $product_id ? wc_get_product( $product_id ) : null;
    } else {
        return new WP_Error( 'missing_param', 'Provide id or sku.', [ 'status' => 400 ] );
    }

    if ( ! $product ) {
        return new WP_Error( 'not_found', 'Product not found.', [ 'status' => 404 ] );
    }

    return rest_ensure_response( as_format_product( $product ) );
}

/**
 * POST /wp-json/as/v1/product
 * Body: { name, sku, price, stock_quantity, description, categories[], image_url }
 */
function as_api_create_product( WP_REST_Request $request ) {
    $body = $request->get_json_params();

    $name = sanitize_text_field( $body['name'] ?? '' );
    if ( ! $name ) {
        return new WP_Error( 'missing_name', 'Product name is required.', [ 'status' => 400 ] );
    }

    $sku = sanitize_text_field( $body['sku'] ?? '' );
    if ( $sku && wc_get_product_id_by_sku( $sku ) ) {
        $existing = wc_get_product( wc_get_product_id_by_sku( $sku ) );
        return rest_ensure_response( [ 'created' => false, 'product' => as_format_product( $existing ) ] );
    }

    $product = new WC_Product_Simple();
    $product->set_name( $name );
    $product->set_status( 'publish' );
    $product->set_description( wp_kses_post( $body['description'] ?? '' ) );
    $product->set_short_description( wp_kses_post( $body['short_description'] ?? '' ) );
    $product->set_regular_price( sanitize_text_field( $body['price'] ?? '' ) );
    $product->set_manage_stock( true );
    $product->set_stock_quantity( (int) ( $body['stock_quantity'] ?? 0 ) );

    if ( $sku ) {
        $product->set_sku( $sku );
    }

    // Categories
    if ( ! empty( $body['categories'] ) && is_array( $body['categories'] ) ) {
        $cat_ids = [];
        foreach ( $body['categories'] as $cat_name ) {
            $term = get_term_by( 'name', sanitize_text_field( $cat_name ), 'product_cat' );
            if ( $term ) {
                $cat_ids[] = $term->term_id;
            }
        }
        if ( $cat_ids ) {
            $product->set_category_ids( $cat_ids );
        }
    }

    $product_id = $product->save();

    if ( ! $product_id ) {
        return new WP_Error( 'create_failed', 'Could not save product.', [ 'status' => 500 ] );
    }

    // Attach image from URL
    if ( ! empty( $body['image_url'] ) ) {
        $image_id = as_upload_image_from_url( esc_url_raw( $body['image_url'] ), $product_id );
        if ( $image_id && ! is_wp_error( $image_id ) ) {
            $product->set_image_id( $image_id );
            $product->save();
        }
    }

    return rest_ensure_response( [
        'created' => true,
        'product' => as_format_product( wc_get_product( $product_id ) ),
    ] );
}

function as_format_product( WC_Product $product ) {
    return [
        'id'             => $product->get_id(),
        'name'           => $product->get_name(),
        'sku'            => $product->get_sku(),
        'status'         => $product->get_status(),
        'price'          => $product->get_price(),
        'regular_price'  => $product->get_regular_price(),
        'sale_price'     => $product->get_sale_price(),
        'stock_quantity' => $product->get_stock_quantity(),
        'stock_status'   => $product->get_stock_status(),
        'description'    => $product->get_description(),
        'short_description' => $product->get_short_description(),
        'categories'     => wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'names' ] ),
        'image_url'      => wp_get_attachment_url( $product->get_image_id() ),
        'permalink'      => get_permalink( $product->get_id() ),
    ];
}

// ─── Agent auth handler ───────────────────────────────────────────────────────

/**
 * POST /wp-json/as/v1/auth/agent
 * Body: { email, password }
 */
function as_api_auth_agent( WP_REST_Request $request ) {
    $email    = sanitize_email( $request->get_param( 'email' ) );
    $password = $request->get_param( 'password' );

    $user = get_user_by( 'email', $email );

    if ( ! $user || ! wp_check_password( $password, $user->data->user_pass, $user->ID ) ) {
        return new WP_Error( 'invalid_credentials', 'Invalid credentials.', [ 'status' => 401 ] );
    }

    $roles = (array) $user->roles;
    if ( ! in_array( 'sls_agent', $roles, true ) && ! in_array( 'agent', $roles, true ) ) {
        return new WP_Error( 'not_agent', 'User is not an agent.', [ 'status' => 403 ] );
    }

    return new WP_REST_Response( [
        'id'         => $user->ID,
        'name'       => $user->display_name,
        'email'      => $user->user_email,
        'roles'      => $roles,
        'avatar_url' => get_avatar_url( $user->ID, [ 'size' => 96 ] ),
    ], 200 );
}

// ─── Orders handler ──────────────────────────────────────────────────────────

/**
 * GET /wp-json/as/v1/orders?customer_id=91
 * GET /wp-json/as/v1/orders?customer_id=91&page=2&per_page=10
 */
function as_api_get_orders( WP_REST_Request $request ) {
    $customer_id = (int) $request->get_param( 'customer_id' );
    $page        = (int) $request->get_param( 'page' );
    $per_page    = (int) $request->get_param( 'per_page' );

    $args = [
        'customer_id' => $customer_id,
        'limit'       => $per_page,
        'offset'      => ( $page - 1 ) * $per_page,
        'orderby'     => 'date',
        'order'       => 'DESC',
    ];

    $orders      = wc_get_orders( $args );
    $total       = (int) wc_get_orders( array_merge( $args, [ 'limit' => -1, 'return' => 'ids', 'count_only' => true ] ) );
    $total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;

    $data = [];
    foreach ( $orders as $order ) {
        $items = [];
        foreach ( $order->get_items() as $item ) {
            $product = $item->get_product();
            $items[] = [
                'product_id' => $item->get_product_id(),
                'name'       => $item->get_name(),
                'sku'        => $product ? $product->get_sku() : '',
                'image_url'  => $product ? wp_get_attachment_url( $product->get_image_id() ) : '',
                'qty'        => $item->get_quantity(),
                'subtotal'   => (float) $item->get_subtotal(),
            ];
        }

        $created = $order->get_date_created();
        $data[]  = [
            'id'           => $order->get_id(),
            'order_number' => $order->get_order_number(),
            'status'       => $order->get_status(),
            'total'        => (float) $order->get_total(),
            'currency'     => $order->get_currency(),
            'date_created' => $created ? $created->date( 'Y-m-d H:i:s' ) : null,
            'items'        => $items,
            'billing'      => [
                'first_name' => $order->get_billing_first_name(),
                'last_name'  => $order->get_billing_last_name(),
                'email'      => $order->get_billing_email(),
                'phone'      => $order->get_billing_phone(),
                'address_1'  => $order->get_billing_address_1(),
                'city'       => $order->get_billing_city(),
                'country'    => $order->get_billing_country(),
            ],
        ];
    }

    return new WP_REST_Response( [
        'page'        => $page,
        'per_page'    => $per_page,
        'total'       => $total,
        'total_pages' => $total_pages,
        'orders'      => $data,
    ], 200 );
}

// ─── Users handler ───────────────────────────────────────────────────────────

/**
 * GET /wp-json/as/v1/users
 * GET /wp-json/as/v1/users?wprole=sls_agent&number=50
 */
function as_api_get_users( WP_REST_Request $request ) {
    $role   = $request->get_param( 'wprole' );
    $number = (int) $request->get_param( 'number' );

    $users = get_users( [
        'role__in' => [ $role ],
        'number'   => $number,
    ] );

    $data = [];
    foreach ( $users as $user ) {
        $data[] = [
            'id'         => $user->ID,
            'name'       => $user->display_name,
            'username'   => $user->user_login,
            'email'      => $user->user_email,
            'avatar_url' => get_avatar_url( $user->ID, [ 'size' => 96 ] ),
        ];
    }

    return new WP_REST_Response( $data, 200 );
}

// ─── Inquiry handlers (read-only) ────────────────────────────────────────────

/**
 * GET /wp-json/as/v1/inquiries
 * GET /wp-json/as/v1/inquiries?page=2&per_page=30&status=open&agent_id=27&search=john
 */
function as_api_get_inquiries( WP_REST_Request $request ) {
    global $wpdb;

    $table      = $wpdb->prefix . 'inquiries';
    $table_prod = $wpdb->prefix . 'inquiry_products';

    $page       = (int) $request->get_param( 'page' );
    $per_page   = (int) $request->get_param( 'per_page' );
    $offset     = ( $page - 1 ) * $per_page;
    $status     = $request->get_param( 'status' );
    $customer_id = (int) $request->get_param( 'customer_id' );
    $agent_id   = (int) $request->get_param( 'agent_id' );
    $search     = $request->get_param( 'search' );

    $where  = 'WHERE 1=1';
    $params = [];

    if ( $status ) {
        $where   .= ' AND status = %s';
        $params[] = $status;
    }
    if ( $customer_id ) {
        $where   .= ' AND customer_id = %d';
        $params[] = $customer_id;
    }
    if ( $agent_id ) {
        $where   .= ' AND agent_id = %d';
        $params[] = $agent_id;
    }
    if ( $search ) {
        $where   .= ' AND (name LIKE %s OR customer_email LIKE %s OR inquired_key LIKE %s)';
        $like     = '%' . $wpdb->esc_like( $search ) . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $base = "FROM {$table} {$where}";

    $total = (int) $wpdb->get_var(
        $params
            ? $wpdb->prepare( "SELECT COUNT(*) {$base}", ...$params )
            : "SELECT COUNT(*) {$base}"
    );

    $rows = $wpdb->get_results(
        $params
            ? $wpdb->prepare( "SELECT * {$base} ORDER BY id DESC LIMIT %d OFFSET %d", ...[...$params, $per_page, $offset] )
            : $wpdb->prepare( "SELECT * {$base} ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset ),
        ARRAY_A
    );

    $inquiries = [];
    foreach ( $rows as $row ) {
        $inquiries[] = as_format_inquiry( $row, $table_prod );
    }

    $response = rest_ensure_response( [
        'page'        => $page,
        'per_page'    => $per_page,
        'total'       => $total,
        'total_pages' => (int) ceil( $total / $per_page ),
        'inquiries'   => $inquiries,
    ] );

    $response->header( 'X-WP-Total',      $total );
    $response->header( 'X-WP-TotalPages', (int) ceil( $total / $per_page ) );

    return $response;
}

/**
 * GET /wp-json/as/v1/inquiry?id=1
 * GET /wp-json/as/v1/inquiry?key=1-4025
 */
function as_api_get_inquiry( WP_REST_Request $request ) {
    global $wpdb;

    $table      = $wpdb->prefix . 'inquiries';
    $table_prod = $wpdb->prefix . 'inquiry_products';

    $id  = (int) $request->get_param( 'id' );
    $key = $request->get_param( 'key' );

    if ( $id ) {
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A );
    } elseif ( $key ) {
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE inquired_key = %s", $key ), ARRAY_A );
    } else {
        return new WP_Error( 'missing_param', 'Provide id or key.', [ 'status' => 400 ] );
    }

    if ( ! $row ) {
        return new WP_Error( 'not_found', 'Inquiry not found.', [ 'status' => 404 ] );
    }

    return rest_ensure_response( as_format_inquiry( $row, $table_prod ) );
}

/**
 * POST /wp-json/as/v1/inquiries
 *
 * Body (JSON):
 *   email       string  required
 *   products    array   required  [{ product_id, qty, note? }]
 *   customer_id int     optional  WP user ID
 *   name        string  optional  display name (for guests)
 *   phone       string  optional
 *   message     string  optional  general inquiry message
 */
function as_api_create_inquiry( WP_REST_Request $request ) {
    global $wpdb;

    $table      = $wpdb->prefix . 'inquiries';
    $table_prod = $wpdb->prefix . 'inquiry_products';

    $customer_id = (int) $request->get_param( 'customer_id' );
    $email       = $request->get_param( 'email' );
    $phone       = $request->get_param( 'phone' ) ?? '';
    $message     = $request->get_param( 'message' ) ?? '';
    $products    = $request->get_param( 'products' );

    // Resolve display name — prefer WP user if customer_id given.
    $name = $request->get_param( 'name' ) ?? '';
    if ( $customer_id ) {
        $wp_user = get_userdata( $customer_id );
        if ( ! $wp_user ) {
            return new WP_Error( 'invalid_customer', 'Customer not found.', [ 'status' => 404 ] );
        }
        if ( ! $name ) $name  = $wp_user->display_name;
        if ( ! $email ) $email = $wp_user->user_email;
        if ( ! $phone ) $phone = get_user_meta( $customer_id, 'billing_phone', true );
    }

    if ( ! $email ) {
        return new WP_Error( 'missing_email', 'email is required.', [ 'status' => 400 ] );
    }

    // Validate products array.
    if ( empty( $products ) || ! is_array( $products ) ) {
        return new WP_Error( 'missing_products', 'products array is required and must not be empty.', [ 'status' => 400 ] );
    }
    foreach ( $products as $i => $p ) {
        if ( empty( $p['product_id'] ) ) {
            return new WP_Error( 'invalid_product', "products[{$i}] missing product_id.", [ 'status' => 400 ] );
        }
    }

    // Get or assign dedicated agent.
    $agent = as_get_or_assign_agent( $customer_id );

    // Insert main inquiry row.
    $inserted = $wpdb->insert(
        $table,
        [
            'customer_id'    => $customer_id,
            'name'           => $name,
            'contact_number' => $phone,
            'customer_email' => $email,
            'inquiry_type'   => 'pieces',
            'agent_id'       => $agent['id'],
            'type_id'        => 3,
            'parent_inquiries' => 0,
            'message'        => $message,
            'subject'        => 'Bulk Order Inquiry',
            'created_at'     => current_time( 'mysql' ),
        ],
        [ '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s' ]
    );

    if ( ! $inserted ) {
        return new WP_Error( 'db_error', 'Failed to save inquiry.', [ 'status' => 500 ] );
    }

    $row_id       = $wpdb->insert_id;
    $inquired_key = $row_id . '-' . rand( 1000, 9999 );

    $wpdb->update( $table, [ 'inquired_key' => $inquired_key ], [ 'id' => $row_id ], [ '%s' ], [ '%d' ] );

    // Insert each product line.
    foreach ( $products as $p ) {
        $wpdb->insert(
            $table_prod,
            [
                'inquired_key'  => $inquired_key,
                'product_id'    => (int) $p['product_id'],
                'requested_qty' => (int) ( $p['qty'] ?? $p['requested_qty'] ?? 1 ),
                'requested_msg' => sanitize_textarea_field( $p['note'] ?? $p['requested_msg'] ?? '' ),
            ],
            [ '%s', '%d', '%d', '%s' ]
        );
    }

    // Return the full inquiry object.
    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $row_id ), ARRAY_A );

    return rest_ensure_response( [
        'success' => true,
        'inquiry' => as_format_inquiry( $row, $table_prod ),
    ] );
}

function as_format_inquiry( array $row, string $table_prod ) {
    global $wpdb;

    $agent    = get_userdata( $row['agent_id'] );
    $customer = get_userdata( $row['customer_id'] );

    $products_raw = $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM {$table_prod} WHERE inquired_key = %s", $row['inquired_key'] ),
        ARRAY_A
    );

    $products = [];
    foreach ( $products_raw as $p ) {
        $wc = wc_get_product( $p['product_id'] );
        $products[] = [
            'product_id'   => (int) $p['product_id'],
            'name'         => $wc ? $wc->get_name() : null,
            'sku'          => $wc ? $wc->get_sku()  : null,
            'image_url'    => $wc ? wp_get_attachment_url( $wc->get_image_id() ) : null,
            'requested_qty'=> (int) $p['requested_qty'],
            'note'         => $p['requested_msg'] ?? '',
        ];
    }

    $ts = strtotime( $row['created_at'] );

    return [
        'id'             => (int) $row['id'],
        'inquired_key'   => $row['inquired_key'],
        'status'         => $row['status'] ?? 'open',
        'inquiry_type'   => $row['inquiry_type'],
        'subject'        => $row['subject'],
        'message'        => $row['message'],
        'customer'       => [
            'id'      => (int) $row['customer_id'],
            'name'    => $row['name'] ?: ( $customer ? $customer->display_name : null ),
            'email'   => $row['customer_email'] ?: ( $customer ? $customer->user_email : null ),
            'phone'   => $row['contact_number'] ?: get_user_meta( $row['customer_id'], 'billing_phone', true ),
        ],
        'agent'          => [
            'id'    => (int) $row['agent_id'],
            'name'  => $agent ? $agent->display_name : null,
            'email' => $agent ? $agent->user_email   : null,
        ],
        'products'       => $products,
        'created_at'     => ( $ts && $ts > 0 ) ? date( 'c', $ts ) : null,
    ];
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function as_upload_image_from_url( $url, $post_id ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = download_url( $url );
    if ( is_wp_error( $tmp ) ) {
        return $tmp;
    }

    $file = [
        'name'     => basename( parse_url( $url, PHP_URL_PATH ) ),
        'type'     => mime_content_type( $tmp ),
        'tmp_name' => $tmp,
        'error'    => 0,
        'size'     => filesize( $tmp ),
    ];

    $attachment_id = media_handle_sideload( $file, $post_id );
    @unlink( $tmp );

    return $attachment_id;
}
