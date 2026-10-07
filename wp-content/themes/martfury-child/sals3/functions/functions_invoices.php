<?php


/**
 * 2. Catch the Request and Generate the PDF
 */
add_action( 'template_redirect', 'handle_pdf_invoice_download' );
function handle_pdf_invoice_download() {
    // Check if we are on the view-order page and our custom trigger is set
    if ( is_account_page() && isset( $_GET['generate_pdf_invoice'] ) && isset( $_GET['order_id'] ) ) {
        
        $order_id = intval( $_GET['order_id'] );

        // Security Check: Verify Nonce
        if ( ! wp_verify_nonce( $_GET['nonce'], 'download_invoice_' . $order_id ) ) {
            wp_die( 'Security check failed. Please refresh the page.' );
        }

        // Security Check: Ensure user owns this order
        if ( ! current_user_can( 'view_order', $order_id ) ) {
            wp_die( 'You do not have permission to view this invoice.' );
        }

        // Generate the PDF
        run_custom_pdf_generator( $order_id );
    }
}

/**
 * 3. The PDF Engine (Requires Dompdf)
 */

function run_custom_pdf_generator( $order_id ) {
    $order = wc_get_order( $order_id );

    // Get the absolute path to the logo
    $logo_path = get_stylesheet_directory() . '/images/SALS3-03-scaled.webp';

    // Convert image to Base64 to ensure Dompdf can render it
    $logo_data = base64_encode(file_get_contents($logo_path));
    $logo_src = 'data:image/png;base64,' . $logo_data;

    // Path to Dompdf (Adjust this to your actual path)
    $path = get_stylesheet_directory() . '/libraries/dompdf/autoload.inc.php';
    
    if ( ! file_exists( $path ) ) {
        wp_die( 'Dompdf library not found. Please upload it to: ' . $path );
    }

    // Individual Coupons
$total_coupon_discount = $order->get_total_discount();

if ( $total_coupon_discount > 0 ) {
    
    // Optional: Show breakdown of each coupon
    $coupons = $order->get_items( 'coupon' );
    foreach ( $coupons as $coupon_item ) {  
        $coupon_code = $coupon_item->get_code();
        $coupon_discount = $coupon_item->get_discount();
        // You can use $coupon_code and $coupon_discount as needed
    }   
}

    require_once $path;
    $dompdf = new \Dompdf\Dompdf();

    $order_number = $order->get_order_number();
    $order_date   = $order->get_date_created()->date('F j, Y');
    $status       = $order->get_status();
    $currency     = $order->get_currency();
    $payment_method = $order->get_payment_method_title();

    // Simple HTML Template
    $html = '
    <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        .header-table { width: 100%; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .address-table { width: 100%; margin-bottom: 30px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background-color: #f2f2f2; padding: 8px; border: 1px solid #ccc; text-align: left; }
        .items-table td { padding: 8px; border: 1px solid #ccc; }
        .totals-table { width: 35%; margin-left: auto; border-collapse: collapse; }
        .totals-table td { padding: 4px 8px; }
        .total-label { text-align: left; }
        .total-amount { text-align: right; }
        .grand-total { font-weight: bold; font-size: 13px; border-top: 1px solid #333; }
        .discount-text { color: #d9534f; }
    </style>
    </head>
    <body>
    <div class="invoice-box">
        <div><h2>BILLING STATEMENT</h2></div>
        <div class="hr"></div>

    <table class="header-table">
        <tr>
            <td style="width: 65%;">';
                if ( file_exists( $logo_path ) ) {
                    $html .= '<img style="max-width: 150px;" src="' . $logo_src . '" class="logo">';
                }
            $html .= '</td>
            <td class="company-info" style="width: 35%;">';
                $html .= '<h2 style="margin:0;">ANYTHING SUPPLIES</h2>';
                $html .= '<div style="margin:0;">123 Staging Ave, Suite 16<br>
                contact@anythingsupplies.com<br>
                www.anythingsupplies.com</div>
            </td>
        </tr>
    </table>

       
        <div class="hr"></div>

        <table class="address-table">
            <tr>
                <td style="vertical-align: top;">
                    <strong>Invoice To:</strong><br>
                    '.$order->get_billing_first_name().' '.$order->get_billing_last_name().'<br>
                    '.$order->get_billing_address_1().'<br>
                    '.$order->get_billing_city().', '.$order->get_billing_state().' '.$order->get_billing_postcode().'<br>
                    '.$order->get_billing_country().'<br>
                    Email: '.$order->get_billing_email().'
                </td>
                <td style="vertical-align: top;"><strong>Invoice No.</strong><br>
                    <div>'.$order_number.'</div>
                </td>
                <td style="vertical-align: top; text-align:right">
                    <strong>Invoice Date:</strong><br>
                    '.$order_date.'<br><br>
                    <strong>Payment Method:</strong><br>
                    '.$payment_method.'<br><br>
                    <strong>Order Status:</strong><br>
                    '.ucfirst($status).'
                </td>
            </tr>
        </table>

        <div class="hr"></div>
        <table class="items-table">
            <tr class="heading">
                <th style="width: 50%;">Item Name</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>';
            
            foreach ( $order->get_items() as $item ) {
                $html .= '<tr>
                    <td>' . $item->get_name() . ' x ' . $item->get_quantity() . '</td>
                    <td>' . wc_price( $item->get_total() / $item->get_quantity() ) . '</td>
                    <td>' . $item->get_quantity() . '</td>';
                $html .= '<td>' . wc_price( $item->get_total() ) . '</td></tr>';
            }

    $html .= '
        </table>
        

        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">' . $order->get_subtotal_to_display() . '</td>
            </tr>
            <tr>
                <td>Discounts:</td>
                <td class="text-right   "><span class="discount-text">'.wc_price($coupon_discount).'</span></td>
            </tr>       
            <tr>
                <td>Tax:</td>
                <td class="text-right"></td>
            </tr>
            <tr>
                <td>Shipping:</td>
                <td class="text-right"></td>   
            </tr>
            <tr class="grand-total">
                <td>Total:</td>
                <td class="text-right   ">' . wc_price( $order->get_total() ) . '</td>
            </tr>
        </table>
    </div></body>
</html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait'); 
    $dompdf->render();
    
    // Clean buffer to avoid corrupted PDF
    if ( ob_get_length() ) ob_end_clean();

    $dompdf->stream( "Invoice-{$order_id}.pdf", array( "Attachment" => 1 ) );
    exit;
}