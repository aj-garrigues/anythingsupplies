<?php


// 1. Define your Zapier Webhook URL here
define( 'ZAPIER_WEBHOOK_URL', 'https://hooks.zapier.com/hooks/catch/24389294/ugigho5/' );

// 2. Hook into the WooCommerce order status change (e.g., when payment is processing)
add_action( 'woocommerce_order_status_processing', 'send_order_data_to_zapier', 10, 1 );

function send_order_data_to_zapier( $order_id ) {
    // Get the order object
    $order = wc_get_order( $order_id );

    if ( ! $order ) return;

    // Prepare the data to send
    $data = [
        'order_id'       => $order_id,
        'date_ordered'      => $order->get_date_created()->date('Y-m-d H:i:s'), // Format: 2024-05-20 14:30:05
        'order_status'   => $order->get_status(),
        'total'          => $order->get_total(),
        'currency'       => $order->get_currency(),
        
        // --- Billing Contact Info ---
        'email'          => $order->get_billing_email(),
        'phone'          => $order->get_billing_phone(),
        'first_name'     => $order->get_billing_first_name(),
        'last_name'      => $order->get_billing_last_name(),
        
        // --- Billing Address Details ---
        'billing_address_1' => $order->get_billing_address_1(),
        'billing_address_2' => $order->get_billing_address_2(),
        'billing_city'      => $order->get_billing_city(),
        'billing_state'     => $order->get_billing_state(),
        'billing_postcode'  => $order->get_billing_postcode(),
        'billing_country'   => $order->get_billing_country(),
        'billing_company'   => $order->get_billing_company(),

        'items'          => []
    ];

    // Add line items
    foreach ( $order->get_items() as $item_id => $item ) {
        $data['items'][] = [
            'product_id' => $item->get_product_id(),
            'name'       => $item->get_name(),
            'quantity'   => $item->get_quantity(),
            'subtotal'   => $item->get_subtotal(),
        ];
    }

    // Send the data to Zapier
    wp_remote_post( ZAPIER_WEBHOOK_URL, [
        'method'    => 'POST',
        'timeout'   => 45,
        'blocking'  => false, 
        'headers'   => [ 'Content-Type' => 'application/json' ],
        'body'      => json_encode( $data ),
    ]);
}