<?php
/**
 * WooCommerce PHP Currency Converter
 * Automatically converts prices based on visitor location
 */

if (!defined('ABSPATH')) exit;

class WC_PHP_Currency_Converter {

    // ============================================================================
    // PROPERTIES
    // ============================================================================
    
    private $api_url = 'https://api.frankfurter.app/latest';
    private $base_currency;
    private $target_currency;
    private $exchange_rate;
    private $visitor_location = array();
    private $is_converting = false;
    private $user_country;

    // ============================================================================
    // INITIALIZATION
    // ============================================================================

    public function __construct() {
        if (!class_exists('WooCommerce')) {
            error_log('[Currency Converter] ERROR: WooCommerce not found!', 3, CJSYNC . '/wccj_error.log');
            return;
        }

        $this->base_currency = get_woocommerce_currency();
        error_log('[Currency Converter] Constructor initialized. Base currency: ' . $this->base_currency, 3, CJSYNC . '/wccj_error.log');
        
        $this->init();
        
        add_action('init', [$this, 'initialize_settings']);
    }

    public function initialize_settings() {
        add_shortcode('sals3_location_currency', array($this, 'render_location_currency'));
    }

    private function init() {
        $this->target_currency = $this->get_user_currency();
        error_log('[Currency Converter] Target currency: ' . $this->target_currency . ' (Country: ' . $this->user_country . ')', 3, CJSYNC . '/wccj_error.log');

        if ($this->target_currency === $this->base_currency) {
            error_log('[Currency Converter] Base and target currency are the same. No conversion needed.', 3, CJSYNC . '/wccj_error.log');
            return;
        }

        $this->exchange_rate = $this->get_exchange_rate($this->base_currency, $this->target_currency);
        error_log('[Currency Converter] Exchange rate (' . $this->base_currency . ' to ' . $this->target_currency . '): ' . ($this->exchange_rate ? $this->exchange_rate : 'FAILED'), 3, CJSYNC . '/wccj_error.log');

        if (!$this->exchange_rate) {
            error_log('[Currency Converter] CRITICAL: No exchange rate available. Aborting.', 3, CJSYNC . '/wccj_error.log');
            return;
        }

        $this->setup_filters();
        error_log('[Currency Converter] Filters registered successfully.', 3, CJSYNC . '/wccj_error.log');
    }

    // ============================================================================
    // GEOLOCATION & CURRENCY DETECTION
    // ============================================================================

    private function get_user_country() {
        $country_code = '';

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $country_code = get_user_meta($user_id, 'billing_country', true);
            
            if (!empty($country_code)) {
                $this->user_country = $country_code;
                error_log('[Currency Converter] User logged in. Country from billing: ' . $country_code, 3, CJSYNC . '/wccj_error.log');
                return $country_code;
            }
        }

        $ip = $this->get_visitor_ip();
        error_log('[Currency Converter] Proceeding to WC_Geolocation... Detected IP: ' . $ip, 3, CJSYNC . '/wccj_error.log');

        if (class_exists('WC_Geolocation')) {
            $location = WC_Geolocation::geolocate_ip();
            $country_code = $location['country'] ?? '';
            
            error_log('[Currency Converter] WC_Geolocation result for IP ' . $ip . ': ' . print_r($location, true), 3, CJSYNC . '/wccj_error.log');
            error_log('[Currency Converter] Country detected: ' . ($country_code ? $country_code : 'NOT FOUND'), 3, CJSYNC . '/wccj_error.log');
        }

        if (empty($country_code) && function_exists('WC') && WC()->customer) {
            $country_code = WC()->customer->get_billing_country();
            if (empty($country_code)) {
                $country_code = WC()->customer->get_shipping_country();
            }
            error_log('[Currency Converter] Country from WC Customer: ' . ($country_code ? $country_code : 'NOT FOUND'), 3, CJSYNC . '/wccj_error.log');
        }

        $this->user_country = !empty($country_code) ? $country_code : 'Unknown';
        return $country_code;
    }

    private function get_visitor_ip() {
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ip_list[0]);
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    private function get_user_currency() {
        $country_code = $this->get_user_country();
        
        if (empty($country_code)) {
            error_log('[Currency Converter] Could not detect country. Using base currency.', 3, CJSYNC . '/wccj_error.log');
            return $this->base_currency;
        }
        
        $currency = $this->get_currency_from_country($country_code);
        error_log('[Currency Converter] Currency mapping: ' . $country_code . ' -> ' . $currency, 3, CJSYNC . '/wccj_error.log');
        
        return $currency;
    }

    private function get_currency_from_country($country_code) {
        $currency_map = array(
            'PH' => 'PHP',
            'JP' => 'JPY',
            'CN' => 'CNY',
            'SG' => 'SGD',
            'MY' => 'MYR',
            'TH' => 'THB',
            'ID' => 'IDR',
            'VN' => 'VND',
            'KR' => 'KRW',
            'TW' => 'TWD',
            'HK' => 'HKD',
            'IN' => 'INR',
            'PK' => 'PKR',
            'BD' => 'BDT',
            'NZ' => 'NZD',
            'AU' => 'AUD',
            'GB' => 'GBP',
            'DE' => 'EUR',
            'FR' => 'EUR',
            'IT' => 'EUR',
            'ES' => 'EUR',
            'NL' => 'EUR',
            'BE' => 'EUR',
            'AT' => 'EUR',
            'PT' => 'EUR',
            'IE' => 'EUR',
            'GR' => 'EUR',
            'FI' => 'EUR',
            'DK' => 'DKK',
            'SE' => 'SEK',
            'NO' => 'NOK',
            'CH' => 'CHF',
            'PL' => 'PLN',
            'CZ' => 'CZK',
            'HU' => 'HUF',
            'RO' => 'RON',
            'BG' => 'BGN',
            'HR' => 'EUR',
            'RU' => 'RUB',
            'TR' => 'TRY',
            'US' => 'USD',
            'CA' => 'CAD',
            'MX' => 'MXN',
            'BR' => 'BRL',
            'AR' => 'ARS',
            'CL' => 'CLP',
            'CO' => 'COP',
            'PE' => 'PEN',
            'AE' => 'AED',
            'SA' => 'SAR',
            'IL' => 'ILS',
            'EG' => 'EGP',
            'ZA' => 'ZAR',
            'NG' => 'NGN',
            'KE' => 'KES',
        );
        
        return isset($currency_map[$country_code]) ? $currency_map[$country_code] : $this->base_currency;
    }

    // ============================================================================
    // EXCHANGE RATE API
    // ============================================================================

    private function get_exchange_rate($from, $to) {
        $transient_key = 'wc_rate_' . $from . '_' . $to;
        $cached_rate = get_transient($transient_key);

        if ($cached_rate !== false) {
            error_log('[Currency Converter] Using cached exchange rate: ' . $cached_rate, 3, CJSYNC . '/wccj_error.log');
            return floatval($cached_rate);
        }

        $url = "{$this->api_url}?from={$from}&to={$to}";
        error_log('[Currency Converter] Fetching exchange rate from: ' . $url, 3, CJSYNC . '/wccj_error.log');
        
        $response = wp_remote_get($url, array('timeout' => 15));

        if (is_wp_error($response)) {
            error_log('[Currency Converter] API ERROR: ' . $response->get_error_message(), 3, CJSYNC . '/wccj_error.log');
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code != 200) {
            error_log('[Currency Converter] API returned status ' . $code . ': ' . $body, 3, CJSYNC . '/wccj_error.log');
            return false;
        }

        $data = json_decode($body, true);
        
        if (isset($data['rates'][$to])) {
            $rate = floatval($data['rates'][$to]);
            error_log('[Currency Converter] SUCCESS: Rate ' . $from . ' to ' . $to . ' = ' . $rate, 3, CJSYNC . '/wccj_error.log');
            set_transient($transient_key, $rate, 12 * HOUR_IN_SECONDS);
            return $rate;
        }

        error_log('[Currency Converter] ERROR: Rate not found in API response', 3, CJSYNC . '/wccj_error.log');
        return false;
    }

    // ============================================================================
    // MOBILE APP DETECTION
    // ============================================================================

    private function is_mobile_api_request() {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }

        if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'wc_') !== false) {
            return true;
        }

        if (!empty($_SERVER['HTTP_X_APP_REQUEST']) || isset($_GET['app_request'])) {
            return true;
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
            if (
                stripos($ua, 'okhttp') !== false ||
                stripos($ua, 'CFNetwork') !== false ||
                stripos($ua, 'Darwin') !== false
            ) {
                return true;
            }
        }

        return false;
    }

    // ============================================================================
    // FILTER SETUP
    // ============================================================================

    private function setup_filters() {
        if ($this->is_mobile_api_request()) {
            error_log('[Currency Converter] Mobile API request detected. Filters NOT registered.', 3, CJSYNC . '/wccj_error.log');
            return;
        }

        add_filter('woocommerce_currency', array($this, 'filter_currency_code'), 999);
        add_filter('woocommerce_currency_symbol', array($this, 'filter_currency_symbol'), 999, 2);
        
        add_filter('woocommerce_product_get_price', array($this, 'convert_price'), 10, 2);
        add_filter('woocommerce_product_get_regular_price', array($this, 'convert_price'), 10, 2);
        add_filter('woocommerce_product_get_sale_price', array($this, 'convert_sale_price'), 10, 2);
        
        add_filter('woocommerce_product_variation_get_price', array($this, 'convert_price'), 10, 2);
        add_filter('woocommerce_product_variation_get_regular_price', array($this, 'convert_price'), 10, 2);
        add_filter('woocommerce_product_variation_get_sale_price', array($this, 'convert_sale_price'), 10, 2);
        
        add_filter('woocommerce_variation_prices', array($this, 'convert_variation_prices'), 10, 3);
        add_filter('woocommerce_package_rates', array($this, 'convert_shipping_rates'), 10, 2);
    }

    // ============================================================================
    // CURRENCY DISPLAY FILTERS
    // ============================================================================

    public function filter_currency_code($currency) {
        return $this->target_currency;
    }

    public function filter_currency_symbol($symbol, $currency) {
        $symbols = array(
            'PHP' => '₱',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'AUD' => '$',
            'CAD' => '$',
        );
        
        return isset($symbols[$this->target_currency]) ? $symbols[$this->target_currency] : $symbol;
    }

    // ============================================================================
    // PRICE CONVERSION
    // ============================================================================

    public function convert_price($price, $product) {
        if ($this->is_converting || empty($price) || $price == 0) {
            return $price;
        }
        
        $this->is_converting = true;
        $converted = floatval($price) * $this->exchange_rate;
        $this->is_converting = false;
        
        return $converted;
    }

    public function convert_sale_price($price, $product) {
        if (empty($price) || $price === '') {
            return $price;
        }
        
        return $this->convert_price($price, $product);
    }

    public function convert_variation_prices($prices, $product, $for_display) {
        if ($this->is_converting) {
            return $prices;
        }
        
        $this->is_converting = true;
        
        foreach ($prices as $key => $price_array) {
            foreach ($price_array as $variation_id => $price) {
                if ($price > 0) {
                    $prices[$key][$variation_id] = floatval($price) * $this->exchange_rate;
                }
            }
        }
        
        $this->is_converting = false;
        
        return $prices;
    }

    public function convert_shipping_rates($rates, $package) {
        if ($this->is_converting) {
            return $rates;
        }
        
        $this->is_converting = true;
        
        foreach ($rates as $rate_id => $rate) {
            if (isset($rate->cost) && $rate->cost > 0) {
                $rates[$rate_id]->cost = floatval($rate->cost) * $this->exchange_rate;
                
                if (isset($rate->taxes) && is_array($rate->taxes)) {
                    foreach ($rate->taxes as $tax_id => $tax_amount) {
                        $rates[$rate_id]->taxes[$tax_id] = floatval($tax_amount) * $this->exchange_rate;
                    }
                }
            }
        }
        
        $this->is_converting = false;
        
        return $rates;
    }

    // ============================================================================
    // SHORTCODE
    // ============================================================================

    public function render_location_currency() {
        return $this->get_user_country() . ' - ' . $this->get_user_currency();
    }
}

// ============================================================================
// PLUGIN INITIALIZATION
// ============================================================================

function init_currency_converter() {
    if (class_exists('WooCommerce')) {
        new WC_PHP_Currency_Converter();
    }
}
add_action('after_setup_theme', 'init_currency_converter', 20);