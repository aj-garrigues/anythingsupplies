<?php 
if ( ! defined( 'ABSPATH' ) ) exit; 
// 1. Get total items and current position

?>

<div class="order-product-item border-b">
    <div class="order-product-image">
        <?php echo $product->get_image( array( 60, 60 ) ); ?>
    </div>
    <div class="order-product-details">
        <div class="order-product-title">
            <?php 
                $full_title = $item->get_name();
                // Limits to 50 chars and adds "..." if it's longer
                echo esc_html( mb_strimwidth( $full_title, 0, 90, '...' ) ); 
            ?>
        </div>
        <div class="order-product-pricing">
            <?php 
                $qty = $item->get_quantity();
                $price = $order->get_item_subtotal( $item ); // Single item price formatted
                echo ' x' . $qty; 
            ?>
        </div>

    </div>
<div class="order-product-total">
    <?php 
        $qty = $item->get_quantity();
        $regular_unit_price = (float) $product->get_regular_price();
        $sale_unit_price    = (float) $product->get_sale_price(); // The price set in the 'Sale' field
        
  
// 1. Get raw values
        $subtotal = $item->get_subtotal(); // Price before discount
        $total    = $item->get_total();    // Price after discount
        

        // 2. If the subtotal is higher than the total, there is a discount
          if ( $product->is_on_sale() && $regular_unit_price > $sale_unit_price ) {
       
            echo '<span class="strikethrough" style="text-decoration: line-through;">' . wc_price($regular_unit_price * $qty) . '</span>';
     
        }

        // 3. Display the actual price paid (The Total)
        echo '<span class="final-price"> ' . $order->get_formatted_line_subtotal( $item ) . '</span>'; 
    ?>
</div>
</div>
<?php 
