<?
add_shortcode( 'as_global_manufacturing_section', function ( $atts ) {
    
    as_enqueue_assets('as_global_manufacturing_section', 'as-global-manufacturing-section.css', null);

    ob_start();
    ?>
    <div class="gmf-container">
        <div class="gmf-header">
            <h2>Our Global Manufacturing Footprint</h2>
            <p>Facilities across six countries delivering quality worldwide</p>
        </div>

        <div class="gmf-grid">
            <div class="gmf-card">
                <div class="gmf-flag">🇨🇳</div>
                <div class="gmf-country">CHINA</div>
            </div>

            <div class="gmf-card">
                <div class="gmf-flag">🇺🇸</div>
                <div class="gmf-country">USA</div>
            </div>

            <div class="gmf-card">
                <div class="gmf-flag">🇳🇱</div>
                <div class="gmf-country">NETHERLANDS</div>
            </div>

            <div class="gmf-card">
                <div class="gmf-flag">🇰🇷</div>
                <div class="gmf-country">SOUTH KOREA</div>
            </div>

            <div class="gmf-card">
                <div class="gmf-flag">🇻🇳</div>
                <div class="gmf-country">VIETNAM</div>
            </div>

            <div class="gmf-card">
                <div class="gmf-flag">🇮🇳</div>
                <div class="gmf-country">INDIA</div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});