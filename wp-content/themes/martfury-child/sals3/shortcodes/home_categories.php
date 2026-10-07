<?php

/**
 * Registers the [cetegories_grid] shortcode.
 *
 * This shortcode outputs a responsive grid of department categories using Bootstrap 5,
 * along with necessary inline CSS and script dependencies for self-containment.
 */
function cetegories_grid_shortcode() {
    // Start output buffering to capture the HTML content
    ob_start();
    ?>
 <style>
        .categories-container h2 {
            font-size: 17px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        /* --- GRID LAYOUT --- */
        .category-grid {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            /* Responsive columns: 8 columns on large screens, less on mobile */
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 1px; 
            border: 1px solid #e1e1e1;
            background-color: #eee; /* Creates the grid lines effect */
        }

        /* --- CATEGORY ITEM STYLES --- */
        .category-item {
            background-color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; 
            padding: 10px 10px 0px;
            text-align: center;
            cursor: pointer;
            transition:all 0.2s;
            height: 100%;
            box-sizing: border-box;
            border: 1px solid transparent; /* Separates items visually */
        }

        .category-item:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1; /* Bring hovered item forward */
            border: 1px solid #dd2400;
        }
        
        /* --- DASHICON CONTAINER (The Circle) --- */
        .category-icon {
            background-color: #f7f7f7; 
            border: 1px solid #eee; 
            /* Center the icon inside the circle */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- DASHICON FONT STYLES --- */
        .category-icon .dashicons {
            font-size: 40px; /* Make the icon large */
            color: #444; 
            transition: color 0.2s;
        }

        .category-item:hover .category-icon .dashicons {
            color: #0073aa; /* Standard WordPress blue on hover */
        }
        
        /* Category Name Styling */
        .category-name {
            color: #333;
            line-height: 1.5;
            margin-top: -32px;
            display: block;

        }

        /* --- INDIVIDUAL CATEGORY BACKGROUND COLORS (for visual distinction) --- */
        .icon-apparel { background-color: #e0f2f7; }
        .icon-mobiles { background-color: #f7e0e0; }
        .icon-acc { background-color: #f7f7e0; }
        .icon-home-ent { background-color: #e0f7e0; }
        .icon-babies { background-color: #f0e0f7; }
        .icon-home-living { background-color: #e0f7f0; }
        .icon-groceries { background-color: #fff2e0; }
        .icon-toys { background-color: #e0e0ff; }
        .icon-w-bags { background-color: #e7ffe0; }
        .icon-w-acc { background-color: #ffdde0; }
        .icon-w-apparel { background-color: #ffe0ff; }
        .icon-health { background-color: #e0ffdd; }
        .icon-makeup { background-color: #dde0ff; }
        .icon-home-app { background-color: #fff0e0; }
        .icon-laptops { background-color: #f7e0f0; }
        .icon-cameras { background-color: #e0fffa; }
        
        /* --- MEDIA QUERIES --- */
        /* Force 8 columns on standard desktop views to match the image */
        @media (min-width: 1024px) {
            .category-grid {
                grid-template-columns: repeat(8, 1fr);
            }
        }
    </style>   
<div class="categories-container">
        <h2>CATEGORIES</h2>
        <ul class="category-grid">
            <li class="category-item">
                <div class="category-icon icon-apparel">
                    
                        <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />
                  
                </div>
                <span class="category-name">Men's Apparel</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-mobiles">
                    <img src="<?=home_url()?>/images/product-categories/mobile-gadgets.jpeg" alt="Mobile gadgets" />
                  
                </div>
                <span class="category-name">Mobiles & Gadgets</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-acc">
                    <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />
                  
                </div>
                <span class="category-name">Mobiles Accessories</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-home-ent">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Home Entertainment</span>
            </li> 
            <li class="category-item">
                <div class="category-icon icon-babies">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Babies & Kids</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-home-living">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Home & Living</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-groceries">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Groceries</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-toys">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Toys, Games & Collectibles</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-w-bags">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Women's Bags</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-w-acc">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Women Accessories</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-w-apparel">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Women's Apparel</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-health">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Health & Personal Care</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-makeup">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Makeup & Fragrances</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-home-app">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Home Appliances</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-laptops">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Laptops & Computers</span>
            </li>
            <li class="category-item">
                <div class="category-icon icon-cameras">
                   <img src="<?=home_url()?>/images/product-categories/mens-apparel.jpeg" alt="Men's Apparel" />

                </div>
                <span class="category-name">Cameras</span>
            </li>
        </ul>
    </div>
    <?php
    // Return the captured content
    return ob_get_clean();
}

// 5. Register the shortcode
add_shortcode('cetegories_grid', 'cetegories_grid_shortcode');
