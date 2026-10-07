<?php

function sync_shopify_sku_to_woocommerce($sku) {
    // Shopify API credentials
    $shopify_store = 'atiidg-kh.myshopify.com';
    $shopify_access_token = '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';
	
	// APi key 81437d55e10e9327c25e762521e461d4
	// APi secret key 06999016a027d468d181ee007586aad7

    // WooCommerce API credentials
    $consumer_key = 'ck_50c3ac7dacf80ade9b223b44fad3a71bbbfd86c9';
    $consumer_secret = 'cs_f2b73503fd103b1442fac0d74cb9b36a891baf81';
    $woocommerce_api_url = rest_url('/wc/v3/products');

    // Fetch Shopify product by SKU
    $shopify_product = get_shopify_product_by_sku($sku, $shopify_store, $shopify_access_token);
    
	if ($shopify_product['product']['variants'] > 0 ){
		
		echo $firstSku = $shopify_product['product']['variants'][0]['sku']; 
		
		$cj_pid_product = get_cg_product_by_sku($firstSku, $shopify_store, $shopify_access_token);
	
	echo "<pre>";
	print_r($cj_pid_product);
	echo "</pre><hr>";		
	}
	
    if (!$shopify_product) {
        return "Product with SKU '$sku' not found in Shopify.";
    }
	
	
	echo "<pre>";
	//$firstSku = $data['product']['variants'][0]['sku'];
	//print_r($shopify_product['products']); 
	echo "</pre>"; 
	wp_die();
	
    // Check if product exists in WooCommerce
   //$existing_id = get_woocommerce_product_id_by_sku($sku, $woocommerce_api_url, $consumer_key, $consumer_secret);

    // Prepare data for WooCommerce
    $product_data = [
        'name' => $shopify_product['title'],
        'type' => 'simple',
        'regular_price' => $shopify_product['variants'][0]['price'],
        'sku' => $shopify_product['variants'][0]['sku'],
        'description' => $shopify_product['body_html'],
        'images' => array_map(function($img) { return ['src' => $img['src']]; }, $shopify_product['images'])
    ];

    if ($existing_id) {
        // Update product
       // $result = make_woocommerce_request('PUT', "$woocommerce_api_url/$existing_id", $product_data, $consumer_key, $consumer_secret);
    } else {
        // Create product
        //$result = make_woocommerce_request('POST', $woocommerce_api_url, $product_data, $consumer_key, $consumer_secret);
    }

    return $result;
}


function get_cg_product_reviews($pid, $store=null, $access_token=null) {
	 
     $tokenFile = CJSYNC . 'cj_token.json';

    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (!isset($tokenData['token'])) {
        error_log("[CJDS SKU UPDATE] Invalid token data in cj_token.json\n", 3, CJSYNC . '/wccj_error.log');
        return;
    }


    $accessToken = $tokenData['token'];

    $api_url = "https://developers.cjdropshipping.com/api2.0/v1/product/productComments?pid=$pid";
	

	$response = wp_remote_get($api_url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'CJ-Access-Token' => $accessToken,
        ],
        'timeout' => 60,
    ]);
	
	
	if (is_wp_error($response)) {
        return;
    }
	
	
	// get the sku of the of the parent product
    $body = json_decode(wp_remote_retrieve_body($response), true); 
	
	//print_r($body['data']);
    
	//$pid = $body['data']['list'][0]['pid'];
	return $body['data'];
    
    //return cjds_update_sku_parent_product($pid);
}

function get_cg_product_by_sku($sku, $store=null, $access_token=null) {
	 
     $tokenFile = CJSYNC . 'cj_token.json';

    if (!file_exists($tokenFile)) {
        error_log("[CJDS SKU UPDATE] Token file not found:\n", 3, CJSYNC . '/wccj_error.log');
        return;
    }

    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (!isset($tokenData['token'])) {
        error_log("[CJDS SKU UPDATE] Invalid token data in cj_token.json\n", 3, CJSYNC . '/wccj_error.log');
        return;
    }


    $accessToken = $tokenData['token'];

    $api_url = "https://developers.cjdropshipping.cn/api2.0/v1/product/list?productSku=$sku";
	

	$response = wp_remote_get($api_url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'CJ-Access-Token' => $accessToken,
        ],
        'timeout' => 60,
    ]);
	
	
	if (is_wp_error($response)) {
        return;
    }
	
	
	// get the sku of the of the parent product
    $body = json_decode(wp_remote_retrieve_body($response), true); 
	
	//print_r($body['data']);
    
	//$pid = $body['data']['list'][0]['pid'];
	return $body['data'];
    
    //return cjds_update_sku_parent_product($pid);
}


function get_cg_product_by_pid($pid, $store=null, $access_token=null) {
    // Try to get from shared location first
    $json_data = cjds_get_json_data($pid);
    
    if (!empty($json_data) && isset($json_data['details'])) {
        error_log("[CJDS] Using existing JSON for PID: {$pid}\n", 3, CJSYNC . '/wccj_error.log');
        return $json_data['details'];
    }
    
    // If not found, fetch from API
    error_log("[CJDS] Fetching from API for PID: {$pid}\n", 3, CJSYNC . '/wccj_error.log');
    
    $tokenFile = CJSYNC . 'cj_token.json';

    if (!file_exists($tokenFile)) {
        error_log("[CJDS] Token file not found\n", 3, CJSYNC . '/wccj_error.log');
        return null;
    }

    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (!isset($tokenData['token'])) {
        error_log("[CJDS] Invalid token data\n", 3, CJSYNC . '/wccj_error.log');
        return null;
    }

    $accessToken = $tokenData['token'];
    $api_url = "https://developers.cjdropshipping.com/api2.0/v1/product/query?pid={$pid}&features=enable_video";

    $response = wp_remote_get($api_url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'CJ-Access-Token' => $accessToken,
        ],
        'timeout' => 60,
    ]);

    if (is_wp_error($response)) {
        error_log("[CJDS] API request failed: " . $response->get_error_message() . "\n", 3, CJSYNC . '/wccj_error.log');
        return null;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (!isset($body['data'])) {
        error_log("[CJDS] Invalid API response for PID: {$pid}\n", 3, CJSYNC . '/wccj_error.log');
        return null;
    }

    return $body['data'];
}


function cjds_update_sku_parent_product($pid) {
	global $wpdb;
    if (!class_exists('WC_Product')) return;
	
    $tokenFile = CJSYNC . 'cj_token.json';
	 
	$product_id = isset($_GET['product_id'])?$_GET['product_id']: 0;

	if ($product_id==0) return;
	
    if (!file_exists($tokenFile)) {
        error_log("[CJDS SKU UPDATE] Token file not found:\n", 3, CJSYNC . '/wccj_error.log');
        return;
    }

    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (!isset($tokenData['token'])) {
        error_log("[CJDS SKU UPDATE] Invalid token data in cj_token.json\n", 3, CJSYNC . '/wccj_error.log');
        return;
    }

    $accessToken = $tokenData['token'];

    $api_url = "https://developers.cjdropshipping.com/api2.0/v1/product/query?pid=$pid&features=enable_video";
	
	$response = wp_remote_get($api_url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'CJ-Access-Token' => $accessToken,
        ],
        'timeout' => 60,
    ]);
	
	if (is_wp_error($response)) {
        return;
    }
	
	$body = json_decode(wp_remote_retrieve_body($response), true);
    print_r($body);
	//run import of product and update product
	cjds_import_product($body,$product_id, true);

    return $body;
}

function get_woocommerce_product_id_by_sku($sku, $api_url, $ck, $cs) {
    $url = "$api_url?sku=$sku&per_page=1";

    $response = make_woocommerce_request('GET', $url, null, $ck, $cs); 
    if (is_array($response) && isset($response[0]['id'])) {
        return $response[0]['id'];
    }
    return null;
}

function make_woocommerce_request($method, $url, $data, $ck, $cs) {
	
	$response = wp_remote_get($url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $accessToken,
        ],
        'timeout' => 60,
    ]);
	
	if (is_wp_error($response)) {
        error_log("[CJDS SKU UPDATE] API request failed:  {$response->get_error_message()}\n" , 3, CJSYNC . '/wccj_error.log');
        return;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
	
    $args = [
        'method' => $method,
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($ck . ':' . $cs),
            'Content-Type' => 'application/json'
        ],
    ];
    if ($data) {
        $args['body'] = json_encode($data);
    }
    $response = wp_remote_get($url, $args);
    if (is_wp_error($response)) {
        return null;
    }
    return json_decode(wp_remote_body($response), true);
}