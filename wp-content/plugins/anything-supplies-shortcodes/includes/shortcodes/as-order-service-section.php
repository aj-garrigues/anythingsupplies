<?
add_shortcode( 'as_buyer_service_section', function () {

		wp_enqueue_style(
			'as_buyer_service_section_style',
			AS_SHORTCODES_URL . 'assets/css/as-buyer-service-section.css',
			array(),
			'1.0' 
		);

    ob_start();
    ?>

    <div class="buyer-container">
        <div class="content">
            <h1>Order and fulfill everything you need for your business from a single platform</h1>
            
            <div class="features">
                <div class="feature-item">
                    <div class="icon-wrapper">
                        <div class="icon">🔍</div>
                    </div>
                    <div class="feature-text">
                        <div class="feature-title">Search for matches</div>
                        <div class="feature-description">
                            Search or send us an inquiry for any products that you need.
                        </div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="icon-wrapper">
                        <div class="icon">📧</div>
                    </div>
                    <div class="feature-text">
                        <div class="feature-title">Contact and confirm</div>
                        <div class="feature-description">
                            We will contact you and confirm all the details of your request.
                        </div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="icon-wrapper">
                        <div class="icon">💳</div>
                    </div>
                    <div class="feature-text">
                        <div class="feature-title">Pay with confidence</div>
                        <div class="feature-description">
                            Pay with confidence and fulfill with transparency.
                        </div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="icon-wrapper">
                        <div class="icon">👥</div>
                    </div>
                    <div class="feature-text">
                        <div class="feature-title">Manage with ease</div>
                        <div class="feature-description">
                            Manage order status and shipments with ease.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="visuals">
            <div class="visual-card" style="background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%); display: flex; align-items: center; justify-content: center;">
                 <img class="visuals-img" src="https://www.shutterstock.com/image-photo/audit-documents-magnifying-glass-verification-600nw-2708642557.jpg" alt="searching" style="height: 100%;">
            </div>
            <div class="visual-card" style="background: linear-gradient(135deg, #f5e6d3 0%, #e8d4b8 100%); display: flex; align-items: center; justify-content: center;">
                 <img class="visuals-img" src="https://cdn.wilsonsons.com.br/wp-content/uploads/2023/09/stock-inventory.jpeg" alt="agreement" style="height: 100%;">
            </div>
            <div class="visual-card" style="background: linear-gradient(135deg, #f5f0e8 0%, #e8ddc8 100%); display: flex; align-items: center; justify-content: center;">
                 <img class="visuals-img" src="https://media.istockphoto.com/id/1916729901/photo/meeting-success-two-business-persons-shaking-hands-standing-outside.jpg?s=612x612&w=0&k=20&c=Zpa1CaJlGI4mYdzqJGjCIEWFRCkqo3DmHxLopdki-SE=" alt="inventory" style="height: 100%;">
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});