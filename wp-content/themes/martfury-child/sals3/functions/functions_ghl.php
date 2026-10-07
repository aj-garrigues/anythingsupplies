<?php
add_action('woocommerce_payment_complete', 'send_order_to_gohighlevel', 10, 1);

function send_order_to_gohighlevel($order_id) {
    // 1. Get the Order Object
    $order = wc_get_order($order_id);
    
    // 2. Prepare Data
    $data = [
        'firstName' => $order->get_billing_first_name(),
        'lastName'  => $order->get_billing_last_name(),
        'email'     => $order->get_billing_email(),
        'phone'     => $order->get_billing_phone(),
        'tags'      => ['woocommerce-customer', 'order-' . $order_id],
        'customFields' => [
            'order_total' => $order->get_total(),
            'product_name' => implode(', ', array_map(fn($item) => $item->get_name(), $order->get_items()))
        ]
    ];

    // 3. GHL API Configuration
    $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJsb2NhdGlvbl9pZCI6IlVsWHBzaGtpdXhTY0w0RjJ0cEwwIiwidmVyc2lvbiI6MSwiaWF0IjoxNzYwNDE5MzgzNDEzLCJzdWIiOiJwRzNWR21jNXV2VVIwQW9IZ1JQTiJ9.1LxTT0_59kFpCXoYiMVGH6XCA2Vrq_stHXistRAeChk';
    $url = 'https://rest.gohighlevel.com/v1/contacts/'; // Use v2 URL if you have an OAuth token

    // 4. Send Request via WordPress HTTP API
    $response = wp_remote_post($url, [
        'method'    => 'POST',
        'headers'   => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body'      => json_encode($data),
        'timeout'   => 45,
    ]);

    // Optional: Log errors for debugging
    if (is_wp_error($response)) {
        error_log('GHL Integration Error: ' . $response->get_error_message());
    }
}