<?
add_shortcode( 'as_faq_page', function ( $atts ) {

   as_enqueue_assets('as_faq_page', 'as-faq-page.css', 'as-faq-page.js');

	ob_start();
    ?>

    <div class="faq-wrapper">
    <header class="faq-header">
        <div class="eyebrow">Help Center</div>
        <h1 class="faq-main-h1">Frequently <em>Asked </em>Questions</h1>
        <p class="subtitle">Everything you need to know. Can't find an answer? Reach out to our team.</p>
    </header>

    <div class="faq-container">
        <!-- Tab List -->
        <nav class="tab-list" role="tablist">
        <button class="tab-btn active" role="tab" data-tab="products" aria-selected="true">
            <span class="tab-icon">◉</span> Products & Availability
            <span class="tab-count">4</span>
        </button>
        <button class="tab-btn" role="tab" data-tab="orders" aria-selected="false">
            <span class="tab-icon">◉</span> Orders & Inquiries
            <span class="tab-count">4</span>
        </button>
        <button class="tab-btn" role="tab" data-tab="shipping" aria-selected="false">
            <span class="tab-icon">◉</span> Shipping & Delivery
            <span class="tab-count">4</span>
        </button>
        <button class="tab-btn" role="tab" data-tab="payments" aria-selected="false">
            <span class="tab-icon">◉</span> Payments & Account Support
            <span class="tab-count">4</span>
        </button>
        </nav>

        <!-- Tab Panels -->
        <div class="tab-panels">

        <div class="tab-panel active" id="tab-products">
            <h2 class="panel-title"><span class="icon">◉</span> Products & Availability</h2>
            <div class="faq-item">
                <p class="faq-q">What information is included on each product page?</p>
                <p class="faq-a">Each product page typically includes product specifications, available variations, minimum order quantities (MOQ), estimated lead times, packaging details, and inquiry options. Information may vary depending on the supplier and product category.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Are all products always in stock?</p>
                <p class="faq-a">Product availability may change based on supplier inventory and demand. Submitting an inquiry is the best way to confirm current stock availability and estimated fulfillment timelines.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Can I request customizations or private labeling?</p>
                <p class="faq-a">Yes, customization options such as branding, packaging, colors, sizes, or specifications may be available for select products. Include your customization requirements when submitting your inquiry.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Do products have minimum order quantities (MOQ)?</p>
                <p class="faq-a">Many products have minimum order quantity requirements that vary by supplier and item type. MOQ details are usually listed on the product page or can be confirmed during the inquiry process.</p>
            </div>
        </div>

        <div class="tab-panel" id="tab-orders">
            <h2 class="panel-title"><span class="icon">◉</span> Orders & Inquiries</h2>
            <div class="faq-item">
                <p class="faq-q">How do I submit an inquiry?</p>
                <p class="faq-a">You can submit an inquiry directly from the product page by providing your requested quantity, specifications, and contact details. Suppliers or representatives will respond with pricing and additional information.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Can I inquire about multiple products at once?</p>
                <p class="faq-a">Yes, you may submit inquiries for multiple products depending on the platform features available. Providing detailed requirements helps streamline the quotation process.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">How long does it take to receive a response?</p>
                <p class="faq-a">Response times may vary depending on the supplier, time zone, and inquiry complexity. Most inquiries are typically reviewed within a few business days.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Am I obligated to place an order after submitting an inquiry?</p>
                <p class="faq-a">No, submitting an inquiry does not create a purchase obligation. It simply allows you to request pricing, availability, and additional product details before making a decision.</p>
            </div>
        </div>

        <div class="tab-panel" id="tab-shipping">
            <h2 class="panel-title"><span class="icon">◉</span> Shipping & Delivery</h2>
            <div class="faq-item">
                <p class="faq-q">Do you offer international shipping?</p>
                <p class="faq-a">Shipping availability depends on the supplier and destination country. Delivery options and shipping terms can be discussed during the inquiry and quotation process.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">How are shipping costs calculated?</p>
                <p class="faq-a">Shipping costs are generally based on factors such as order quantity, package dimensions, destination, shipping method, and applicable customs requirements.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Can I arrange my own freight forwarder or logistics provider?</p>
                <p class="faq-a">In many cases, buyers may use their preferred freight forwarder or logistics partner. Coordination details can be finalized before order confirmation.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">How long does delivery usually take?</p>
                <p class="faq-a">Delivery timelines vary depending on production schedules, order volume, shipping method, and destination. Estimated lead times are usually provided with the quotation.</p>
            </div>
        </div>

        <div class="tab-panel" id="tab-payments">
            <h2 class="panel-title"><span class="icon">◉</span> Payments & Account Support</h2>
            <div class="faq-item">
                <p class="faq-q">What payment methods are accepted?</p>
                <p class="faq-a">Accepted payment methods may vary by supplier and order type. Common options may include bank transfers, online payment services, or other agreed payment arrangements.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Do I need an account to submit an inquiry?</p>
                <p class="faq-a">Some platforms allow guest inquiries, while others may require account registration for easier order tracking and communication management.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Is my business information kept confidential?</p>
                <p class="faq-a">Business and contact information submitted through the platform is generally handled according to applicable privacy and data protection practices.</p>
            </div>
            <div class="faq-item">
                <p class="faq-q">Who can I contact for additional assistance?</p>
                <p class="faq-a">If you need more information about products, quotations, or account-related concerns, you can use the platform’s contact or inquiry forms for further assistance.</p>
            </div>
        </div>

        </div>
    </div>

    <div class="contact-strip">
        <p style="margin: 0;">Still have questions? Contact our support team.</p>
        <button id="openPopup" class="contact-btn">Contact Support →</button>
    </div>
    </div>

    <!-- Popup Overlay -->
    <div class="popup-overlay" id="popupOverlay" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <div class="popup">
            <div class="popup-header">
                <div>
                    <p class="popup-eyebrow">Support</p>
                    <h2 class="popup-title" id="popupTitle">Contact Us</h2>
                </div>
                <button class="popup-close" id="closePopup" aria-label="Close">&times;</button>
            </div>
            <div class="popup-body">
                <?php // echo do_shortcode('[contact-form-7 id="076d2c5" title="FAQ support form"]') ?>
                <div style="display: flex; gap: 1.5rem; margin-bottom: 38px;">
                    <div style="width: 100%; max-width: 320px;">
                        <label for="contact-name" style="display: block;">Your Name</label>
                        <input type="text" id="contact-name" name="contact-name" required placeholder="Your Name" style="width: 100%; max-width: 320px;">
                    </div>
                    <div style="width: 100%; max-width: 320px;">
                        <label for="contact-email" style="display: block;">Your Email</label>
                        <input type="email" id="contact-name" name="contact-name" required placeholder="Your Email Address" style="width: 100%; max-width: 320px;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="contact-msg" style="display: block;">Your Message/Comment</label>
                    <textarea id="contact-msg" name="contact-msg" rows="5" required style="width: 100%;"></textarea>
                </div>

                <div style="width: 100%; text-align: end;">
                    <button type="submit" id="contact-submit" style="padding: 12px; color: white; background-color: #00719a; font-weight: 600; border: none;">Send Message</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script in as-faq-page.js -->
    <?php
});