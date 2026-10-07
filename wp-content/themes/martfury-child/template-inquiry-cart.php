<?php

/**
 * Template Name: Template Inquiry Cart
 * Template Post Type: page
 */

// Disabling the standard header/footer to keep the landing page clean.
// If you want your site's menu, change this to get_header();

    // 1. Safety Guard: Don't run in admin or if WC isn't loaded
    if ( is_admin() || ! did_action( 'woocommerce_init' ) || ! isset( WC()->session ) ) {
        return '<p>Loading Inquiry List...</p>';
    }

    $inquiry_list = WC()->session->get('custom_inquiry_list', array());

    //print_r($inquiry_list);
    $customer_id = get_current_user_id() ? get_current_user_id() : 0; // Use 0 for guests

    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry']) ) {
        global $wpdb;
        // echo "testing";

        // 2. Generate the Master Key for AnythingSupplies
        $inquired_key = $wpdb->insert_id."-".rand(1000,9999);

        // 3. Insert Main Inquiry (The sticky textarea content)
        $main_message = isset($_POST['product_inquiry_message']) ? sanitize_textarea_field($_POST['product_inquiry_message']) : '';
        
        $agent_data = as_get_or_assign_agent( $customer_id );

        $data = [
            'customer_id'    => $customer_id, // Integer
            'name'           => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '', // For guests
            'contact_number' => isset($_POST['contact_number']) ? sanitize_text_field($_POST['contact_number']) : '', // For guests
            'customer_email' => isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '', // For guests
            'inquiry_type'   => 'pieces',
            'agent_id'       => $agent_data['id'], // Ensure it's not null
            'type_id'        => 3, // Integer
            'parent_inquiries' => 0,
            'message'        => $main_message,
            'subject'        => 'Bulk Order Inquiry',
            'created_at'     => current_time('mysql'), // Best practice: track when it happened
        ];

        // 3. Define the Table Name
        $table_name = $wpdb->prefix . 'inquiries';

        // 4. Insert the data with Format Specifiers (Security)
        // %d = integer, %s = string
        $formats = ['%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s'];

        $inserted = $wpdb->insert($table_name, $data, $formats);
        $inquired_key = $wpdb->insert_id."-".rand(1000,9999);

        if ( $inserted ) {

            // The data you want to update
            $inquired_key = $wpdb->insert_id."-".rand(1000,9999);

            $data = array(
                'inquired_key' => $inquired_key // Replace with the actual value
            );

            // The WHERE clause (which row should be updated?)
            $where = array(
                'id' => $wpdb->insert_id // Replace with the specific ID or unique identifier for the row
            );

            // Optional: define the format of the data (%s = string, %d = integer)
            $format = array('%s'); 
            $where_format = array('%d');

            $updated = $wpdb->update($table_name, $data, $where, $format, $where_format);


            //this wehere to insert the products for the inquiry from the list
        // 4. Loop through POST data to find Product IDs
            // We look for keys starting with "product_qty_" to identify which products were sent

            foreach ( $inquiry_list as $product_id ) : 
                    // Get the specific quantity and message for THIS product
                    $qty = $_POST['product_qty_' . $product_id];
                    $item_msg = isset($_POST['product_inquirymsg_' . $product_id]) ? sanitize_textarea_field($_POST['product_inquirymsg_' . $product_id]) : '';
                    
                    //load the product info to the array to be used in the email template
                    $inquired_products[] = [
                        'inquired_key' => $inquired_key,
                        'product_id' => $product_id,
                        'requested_qty' => $qty,
                        'requested_msg' => $item_msg
                    ];

                    $wpdb->insert(
                        $wpdb->prefix . 'inquiry_products',
                        array(
                            'inquired_key'  => $inquired_key,
                            'product_id'    => intval($product_id),
                            'requested_qty' => $qty,
                            'requested_msg' => $item_msg
                        ),
                        array('%s', '%d', '%d', '%s')
                        );
            endforeach;

        }

        $inquired_key = $inquired_key; // Add the generated key to the data array
        $data['agent_id'] = $agent_data['id']; // Add customer ID to the data array
        $agent_name = $agent_data['name']; // Add the inquired products details to the data array
        $data['agent_email'] = $agent_data['email']; // Add customer email to the data array
        
        error_log("[INQUIRY] new inquiry added\n", 3, CJSYNC . '/wccj_error.log');
        //send the email to the agent with the inquired products details
        $email_body = "
        <html>
        <body style='font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;'>
            <div style='max-width: 600px; margin: 20px auto; border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                
                <div style='background-color: #2c3e50; color: #ffffff; padding: 25px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 1px;'>New Order Inquiry</h1>
                    <p style='margin: 5px 0 0; opacity: 0.8;'>Priority: <span style='color: #ff1900; font-weight: bold;'>High</span></p>
                </div>

                <div style='background-color: #fff3cd; color: #856404; padding: 15px; text-align: center; font-weight: bold; border-bottom: 1px solid #ffeeba;'>
                    ACTION REQUIRED: Please respond to this inquiry immediately.
                </div>

                <div style='padding: 30px; background-color: #ffffff;'>
                    <p>Hello <strong>{$agent_name}</strong>,</p>
                    <p>A new high-volume inquiry has been assigned to you. Please review the details below and provide a quotation as soon as possible.</p>

                    <div style='background-color: #f8f9fa; border-left: 4px solid #3498db; padding: 20px; margin-bottom: 25px;'>
                        <h3 style='margin-top: 0; color: #2c3e50;'>Inquiry Information</h3>
                        <p style='margin: 5px 0;'><strong>Inquiry No:</strong> {$inquired_key}</p>
                    </div>

                    <h3 style='color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px;'>Customer Details</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; color: #7f8c8d; width: 40%;'>Customer Name:</td>
                            <td style='padding: 8px 0; font-weight: bold;'>{$data['name']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #7f8c8d;'>Email:</td>
                            <td style='padding: 8px 0;'><a href='mailto:{$data['customer_email']}' style='color: #3498db;'>{$data['customer_email']}</a></td>
                        </tr>
                        
                        <tr>
                            <td style='padding: 8px 0; color: #7f8c8d;'>Contact:</td>
                            <td style='padding: 8px 0;'>{$data['contact_number']}</td>
                        </tr>
                    </table>

                    <div style='margin-top: 25px;'>";
                   
                    foreach ($inquired_products as $index => $product) {
                        $email_body .= "<ul style='list-style: none; padding: 15px; background: #f0f0f0; border-radius: 5px; margin-bottom: 20px;'>";    
                        $prod = wc_get_product($product['product_id']);
                        $qty = $product['requested_qty'];
                        $msg = $product['requested_msg'] ? $product['requested_msg'] : 'No additional message provided.';
                        $product_name = $prod ? $prod->get_name() : 'Unknown Product';
                        $email_body .= "<li style='width:20%'><strong>Product Name:</strong></li>";
                        $email_body .= "<li style='width: 78%'>{$product_name}</li>";
                        $email_body .= "<li style='width:20%'><strong>Requested Quantity:</strong></li>";
                        $email_body .= "<li style='width: 78%'>{$qty}</li>";
                        $email_body .= "<li style='width:20%; vertical-align: top;'><strong>Customer Message:</strong></li>";
                        $email_body .= "<li style='width: 78%; padding: 10px; background: #f0f0f0; border-radius: 5px; font-style: italic; color: #555;'>{$msg}</li>";  
                        $email_body .= "</ul>";
                    }
                     

                    $email_body .= "
                    </div>
                </div>

                <div style='background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #95a5a6;'>
                    This is an automated priority alert from the <strong>Anythingsupplies Portal</strong>.<br>
                    Agent Assignment ID: {$agent_data['id']}
                </div>
            </div>
        </body>
        </html>";


    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $data['name'] . ' <' . $data['customer_email'] . '>'
    );

    $to = $data['agent_email'].',marinel@anythingsupplies.com,admin@anythingsupplies.com,rodel@anythingsupplies.com';
    $subject = "New High-Volume Inquiry Assigned: {$inquired_key}";

    wp_mail( $to, $subject, $email_body, $headers );
    error_log("[INQUIRY] Notify agent email sent\n", 3, CJSYNC . '/wccj_error.log');

    // ── GHL Integration ──────────────────────────────────────────────────────
    $ghl_api_key     = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJsb2NhdGlvbl9pZCI6IlVsWHBzaGtpdXhTY0w0RjJ0cEwwIiwidmVyc2lvbiI6MSwiaWF0IjoxNzYwNDE5MzgzNDEzLCJzdWIiOiJwRzNWR21jNXV2VVIwQW9IZ1JQTiJ9.1LxTT0_59kFpCXoYiMVGH6XCA2Vrq_stHXistRAeChk';
    $ghl_location_id = 'UlXpshkiuxScL4F2tpL0';
    $ghl_pipeline_id = '5gBzT6bLi2mUvT8S40Ui';
    $ghl_stage_id    = '746d1ea9-b6ac-4dfd-ab34-24053afcf1cb';
    $ghl_headers = [
        'Authorization' => 'Bearer ' . $ghl_api_key,
        'Content-Type'  => 'application/json',
    ];

    // Build contact payload — logged-in user or guest
    $current_user = wp_get_current_user();
    if ( $current_user->ID ) {
        $ghl_first   = $current_user->first_name ?: $current_user->display_name;
        $ghl_last    = $current_user->last_name;
        $ghl_email   = $current_user->user_email;
        $ghl_phone   = get_user_meta( $current_user->ID, 'billing_phone', true );
        $ghl_company = get_user_meta( $current_user->ID, 'billing_company', true );
    } else {
        $name_parts  = explode( ' ', $data['name'], 2 );
        $ghl_first   = $name_parts[0];
        $ghl_last    = $name_parts[1] ?? '';
        $ghl_email   = $data['customer_email'];
        $ghl_phone   = $data['contact_number'];
        $ghl_company = '';
    }

    $ghl_contact_payload = array_filter( [
        'firstName'   => $ghl_first,
        'lastName'    => $ghl_last,
        'email'       => $ghl_email,
        'phone'       => $ghl_phone,
        'companyName' => $ghl_company,
        'source'      => 'Web AS - www.anythingsupplies.com',
        'tags'        => [ 'rfq', 'inquiry-basket' ],
    ] );

    // Step 1: Create/update contact (v1 API)
    $contact_resp = wp_remote_post( 'https://rest.gohighlevel.com/v1/contacts/', [
        'method'  => 'POST',
        'headers' => $ghl_headers,
        'body'    => json_encode( $ghl_contact_payload ),
        'timeout' => 30,
    ] );

    $ghl_contact_id = null;
    if ( ! is_wp_error( $contact_resp ) ) {
        $ghl_http_code  = wp_remote_retrieve_response_code( $contact_resp );
        $ghl_raw        = wp_remote_retrieve_body( $contact_resp );
        $contact_body   = json_decode( $ghl_raw, true );
        $ghl_contact_id = $contact_body['contact']['id'] ?? null;
        error_log( "[GHL] Contact HTTP {$ghl_http_code} | id: {$ghl_contact_id}\n", 3, CJSYNC . '/wccj_error.log' );
    } else {
        error_log( "[GHL] Contact WP error: " . $contact_resp->get_error_message() . "\n", 3, CJSYNC . '/wccj_error.log' );
    }

    // Step 2: Create pipeline opportunity (v1 API)
    if ( $ghl_contact_id ) {
        $product_lines = [];
        foreach ( $inquired_products as $p ) {
            $prod = wc_get_product( $p['product_id'] );
            $product_lines[] = ( $prod ? $prod->get_name() : 'Product #' . $p['product_id'] ) . ' x' . $p['requested_qty'];
        }

        $opp_payload = [
            'title'     => 'RFQ #' . $inquired_key . ' — ' . trim( $ghl_first . ' ' . $ghl_last ),
            'status'    => 'open',
            'stageId'   => $ghl_stage_id,
            'contactId' => $ghl_contact_id,
        ];

        $opp_resp = wp_remote_post( 'https://rest.gohighlevel.com/v1/pipelines/' . $ghl_pipeline_id . '/opportunities/', [
            'method'  => 'POST',
            'headers' => $ghl_headers,
            'body'    => json_encode( $opp_payload ),
            'timeout' => 30,
        ] );

        if ( ! is_wp_error( $opp_resp ) ) {
            $opp_http = wp_remote_retrieve_response_code( $opp_resp );
            $opp_raw  = wp_remote_retrieve_body( $opp_resp );
            $opp_body = json_decode( $opp_raw, true );
            $opp_id   = $opp_body['id'] ?? null;
            error_log( "[GHL] Opportunity HTTP {$opp_http} | id: {$opp_id} | items: " . implode( ', ', $product_lines ) . "\n", 3, CJSYNC . '/wccj_error.log' );
        } else {
            error_log( "[GHL] Opportunity WP error: " . $opp_resp->get_error_message() . "\n", 3, CJSYNC . '/wccj_error.log' );
        }
    }
    // ─────────────────────────────────────────────────────────────────────────

        WC()->session->set('custom_inquiry_list', array());

        wp_safe_redirect( add_query_arg('status', 'success', get_permalink()) );
        exit;
    }

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Sals3 Account | <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
    <style>    
        div#content {
            padding-top: 8px !important;
        }

        .inquiry-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Page Header */
        .inquiry-page-header {
            margin-bottom: 24px;
        }
        
        .inquiry-page-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            padding-bottom: 12px;
            border-bottom: 3px solid #0891B2;
        }
        
        .inquiry-page-subtitle {
            color: #6B7280;
            font-size: 14px;
        }
        
        /* Main Layout */
        .inquiry-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 24px;
        }
        
        /* Product List */
        .products-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: box-shadow 0.3s;
        }
        
        .product-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .product-header {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 20px;
            margin-bottom: 20px;
            align-items: start;
        }
        
        .product-image {
            width: 120px;
            height: 120px;
            background: #F3F4F6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-title {
            font-size: 16px;
            font-weight: 600;
            color: #2563EB;
            margin-bottom: 8px;
            cursor: pointer;
            line-height: 1.4;
        }
        
        .product-title:hover {
            text-decoration: underline;
        }
        
        .product-sku {
            font-size: 13px;
            color: #6B7280;
            margin-bottom: 8px;
        }
        
        .product-description {
            font-size: 14px;
            color: #4B5563;
            line-height: 1.5;
        }
        
        .remove-btn {
            padding: 8px 20px;
            background: white;
            border: 1.5px solid #E5E7EB;
            border-radius: 8px;
            color: #EF4444;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .remove-btn:hover {
            background: #FEF2F2;
            border-color: #EF4444;
        }
        
        /* Product Form Section */
        .product-form {
            display: grid;
            gap: 16px;
            padding-top: 16px;
            border-top: 1px solid #E5E7EB;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 16px;
            align-items: start;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }
        
        .quantity-input {
            padding: 10px 14px;
            border: 1.5px solid #D1D5DB;
            border-radius: 8px;
            font-size: 14px;
            width: 100%;
            transition: all 0.2s;
        }
        
        .quantity-input:focus {
            outline: none;
            border-color: #0891B2;
            box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.1);
        }
        
        .instructions-group {
            grid-column: 1 / -1;
        }
        
        .char-limit {
            font-size: 12px;
            color: #9CA3AF;
        }
        
        .instructions-textarea {
            padding: 12px 14px;
            border: 1.5px solid #D1D5DB;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
            transition: all 0.2s;
        }
        
        .instructions-textarea:focus {
            outline: none;
            border-color: #0891B2;
            box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.1);
        }
        
        /* Timeline Info */
        .timeline-info {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            font-size: 13px;
            color: #6B7280;
            padding: 12px 16px;
            background: #F9FAFB;
            border-radius: 8px;
            margin-top: 12px;
        }
        
        .timeline-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .timeline-item strong {
            color: #374151;
            font-weight: 600;
        }
        
        .timeline-item::before {
            content: '•';
            color: #10B981;
            font-size: 16px;
            font-weight: bold;
        }
        
        /* Summary Sidebar */
        .summary-sidebar {
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        
        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        
        .summary-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #111827;
        }
        
        .general-inquiry {
            margin-bottom: 24px;
        }
        
        .general-inquiry label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .general-textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #D1D5DB;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 120px;
        }
        
        .general-textarea:focus {
            outline: none;
            border-color: #0891B2;
            box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.1);
        }
        
        /* Privacy Section */
        .privacy-section {
            background: #F9FAFB;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .privacy-checkbox {
            display: flex;
            align-items: start;
            gap: 12px;
            cursor: pointer;
        }
        
        .privacy-checkbox input[type="checkbox"] {
            margin-top: 2px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #0891B2;
        }
        
        .privacy-text {
            font-size: 12px;
            color: #6B7280;
            line-height: 1.6;
        }
        
        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: #0891B2;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .submit-btn:hover {
            background: #0E7490;
            box-shadow: 0 4px 12px rgba(8, 145, 178, 0.3);
        }
        
        .submit-btn:disabled {
            background: #D1D5DB;
            cursor: not-allowed;
        }
        
        /* Empty State */
        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        
        .empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 18px;
            color: #6B7280;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            color: #9CA3AF;
            font-size: 14px;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .inquiry-layout {
                grid-template-columns: 1fr 350px;
            }
        }
        
        @media (max-width: 968px) {
            .inquiry-layout {
                grid-template-columns: 1fr;
            }
            
            .summary-sidebar {
                position: static;
            }
            
            .product-header {
                grid-template-columns: 100px 1fr;
            }
            
            .remove-btn {
                grid-column: 2;
                width: fit-content;
                justify-self: end;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 640px) {
            .inquiry-container {
                padding: 12px;
            }
            
            .inquiry-page-title {
                font-size: 22px;
            }
            
            .product-card {
                padding: 16px;
            }
            
            .product-header {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .product-image {
                width: 100%;
                height: 200px;
            }
            
            .remove-btn {
                width: 100%;
                grid-column: 1;
            }
            
            .timeline-info {
                flex-direction: column;
                gap: 8px;
            }
            
            .summary-card {
                padding: 20px;
            }
        }
        .stick {
            margin-top: 120px;
        }

        .inquiry-header-txt {
            font-size: 28px;
            margin-bottom: 28px;
            font-weight: 700;
            color: #000;
            font-family: 'Inter', sans-serif;
        }

        .cta-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #00719a;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            text-decoration: none;
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            transition: background-color 0.3s ease;
        }

        .cta-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body <?php body_class(); ?>>
<?php get_header(); ?>

 <?php
if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
endif;
?>
    
    <?php wc_print_notices(); ?>
    <div class="inquiry-container">
        <?php if ( isset( $_GET['status'] ) && $_GET['status'] === 'success' ) : ?>
            <div class="empty-inquiry" style="text-align:center; padding: 50px;">
                <img src="https://static.vecteezy.com/system/resources/previews/047/468/658/large_2x/empty-state-empty-cart-illustration-free-vector.jpg" alt="cart empty" style="width: 200px;border-radius: 50%;">
                <h2 class="inquiry-header-txt">Sucessfully submited.</h2>
                <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="button">
                    <button type="button" class="cta-button">Browse Products</button>
                </a>
            </div>
        <?php elseif ( empty( $inquiry_list ) ) : ?>
            <div class="empty-inquiry" style="text-align:center; padding: 50px;">
                <img src="https://static.vecteezy.com/system/resources/previews/047/468/658/large_2x/empty-state-empty-cart-illustration-free-vector.jpg" alt="cart empty" style="width: 200px;border-radius: 50%;">
                <h2 class="inquiry-header-txt">Your inquiry list is empty.</h2>
                <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="button">
                    <button type="button" class="cta-button">Browse Products</button>
                </a>
            </div>
        <?php else : ?>

        <!-- Page Header -->
        <div class="inquiry-page-header">
            <h1 class="inquiry-page-title">Your Inquiry Basket</h1>
            <p class="inquiry-page-subtitle">Review your selected products and submit your inquiry</p>
        </div>
        
        <!-- Main Layout -->
        <form method="post" name="inquiry-form-list" id="inquiry-form-list">
        <div class="inquiry-layout">
            <!-- Products Section -->
            <div class="products-section">
                <?php foreach ( $inquiry_list as $product_id ) : 
                    $product = wc_get_product( $product_id );
                    if ( ! $product ) continue;
                ?> 
                <!-- Product 1 -->
                <div class="product-card" id="basket-item-<?php echo $product_id;?>">
                    <div class="product-header">
                        <div class="product-image">
                            <?php echo $product->get_image('thumbnail'); ?>
                        </div>
                        
                        <div class="product-info">
                            <a href="<?php echo get_permalink($product->get_id()); ?>">
                                <h3 class="product-title"><?php echo $product->get_name(); ?></h3>
                            </a>
                            <div class="product-sku">SKU: <?php echo $product->get_sku(); ?></div>
                            <p class="product-description"><?php echo wp_trim_words( $product->get_short_description(), 15, '...' ); ?></p>
                        </div>
                        
                        <button class="remove-btn remove-from-basket" data-id="<?php echo $product_id; ?>">Remove</button>
                    </div>
                    
                    <div class="product-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="product_qty_<?php echo $product_id; ?>" >Request Quantity: </label>
                                <input type="number" class="quantity-input" id="product_qty_<?php echo $product_id; ?>" name="product_qty_<?php echo $product_id; ?>" min="1" />
                            </div>
                        </div>
                        
                        <div class="instructions-group">
                            <div class="form-group">
                                <label for="product_inquirymsg_<?php echo $product_id; ?>" class="form-label">
                                    Add your request instructions 
                                    <span class="char-limit">(Maximum: 500 characters)</span>
                                </label>
                                <textarea id="product_inquirymsg_<?php echo $product_id; ?>" class="instructions-textarea" name="product_inquirymsg_<?php echo $product_id; ?>" placeholder="Enter any special requirements, customization requests, or shipping instructions..." maxlength="500"></textarea>
                                <!-- <div class="char-counter">0 / 500</div> -->
                            </div>
                        </div>
                        
                        <div class="timeline-info">
                            <div class="timeline-item">
                                <strong>Production lead time:</strong> 7 business days
                            </div>
                            <div class="timeline-item">
                                <strong>Shipping time:</strong> 3-7 business days
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
                
            <!-- Summary Sidebar -->
            <aside class="summary-sidebar">
                <div class="summary-card">
                    <h2 class="summary-title">Enquiry Details</h2>
                    <?php if ( $customer_id == 0 ) : ?>
                    
                    <div class="form-group"> 
                        <label for="my-name" class="form-label">Your Name</label>
                        <input type="text" id="my-name" class="quantity-input" name="name" placeholder="Your Name" required />
                    </div>
                    <div class="form-group"> 
                        <label for="my-contact" class="form-label">Your Contact</label>
                        <input type="tel" id="my-contact" class="quantity-input" name="contact_number" placeholder="Your Contact" required />
                    </div>

                    <div class="form-group"> 
                        <label for="my-email" class="form-label">Your Email</label>
                        <input type="email" id="my-email" class="quantity-input" name="customer_email" placeholder="Your Email" required />
                    </div>
                    <?php endif; ?>
                    
                    <div class="general-inquiry">
                        <label>Write your inquiry details here</label>
                        <textarea class="general-textarea" placeholder="Add general information about your inquiry, budget, timeline, or any other details..." maxlength="500"></textarea>
                    </div>
                    
                    <div class="privacy-section">
                        <span class="privacy-text">
                            I confirm that I have read and agree to AnythingSupplies' Terms of Use. I acknowledge that the information provided may be used by AnythingSupplies for database management, direct marketing, and business matching purposes to better serve my procurement needs.
                        </span>
                    </div>
                    
                    <button type="submit" name="submit_inquiry" class="submit-btn">Send Quote for List</button>
                </div>
            </aside>
        </div>
        </form>
        <?php endif; ?>
    </div>

<script>
    (function() {
        // Sticky summary logic
        const summary = document.querySelector('.summary-card');
        if (!summary) return;

        if (window.innerWidth >= 1024) {
            window.addEventListener('scroll', function() {
                if (window.scrollY >= 220) {
                    summary.classList.add('stick');
                } else {
                    summary.classList.remove('stick');
                }
            });
        }

        // Disable submit button on form submit
        const form = document.querySelector('#inquiry-form-list');
        const submitBtn = document.querySelector('.submit-btn');
        let isSubmitting = false;

        form.addEventListener('submit', function(e) {

            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            isSubmitting = true;
            submitBtn.textContent = 'Sending...';
        });
    })();
</script>
<?php wp_footer();
get_footer();
?>
</body>
</html>