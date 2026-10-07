<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="order-product-item">
    <div class="product-details">
        <h3 class="product-title"><?php echo $item->get_name(); ?></h3>
        <div class="product-pricing">
            <?php 
                $qty = $item->get_quantity();
                $price = $order->get_item_subtotal( $item ); // Single item price formatted
                echo $price . ' x ' . $qty; 
            ?>
        </div>
    </div>
    
    <div class="product-image">
        <?php echo $product->get_image( array( 250, 250 ) ); ?>
    </div>
</div>