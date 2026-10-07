<?php


// initialize 
// add_action( 'admin_init', 'getAccessToken' );
/**
 * Get cached token or fetch new one if expired
 */
function getAccessToken() {
	$email       = 'ajaneshsingh@bigpond.com';
	$apiKey      = '01564a00bfe9428c87cd2b41acf2f9e6';
	
	//$outputDir   = $_SERVER['DOCUMENT_ROOT'].'/fetchcj/products';
	$tokenFile = $_SERVER['DOCUMENT_ROOT'].'/salsdata/cj_token.json';

    if (file_exists($tokenFile)) {
        $cached = json_decode(file_get_contents($tokenFile), true);
        if (isset($cached['token'], $cached['expires']) && time() < $cached['expires']) {
           // echo "Using cached token (expires in " . round(($cached['expires'] - time()) / 60) . " minutes)</br>";
            return $cached['token'];
        }
    }
    echo 'hello world';
   
    $url = "https://developers.cjdropshipping.com/api2.0/v1/authentication/getAccessToken";

    $data = json_encode([
        'email' => $email,
        'apiKey' => $apiKey
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);

    $response = curl_exec($ch);
	// print_r($response);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['data']['accessToken'])) {
        $token = $result['data']['accessToken'];

        $cacheData = [
            'token' => $token,
            'expires' => time() + (23 * 60 * 60),
            'fetched_at' => date('Y-m-d H:i:s')
        ];
		
        file_put_contents($tokenFile, json_encode($cacheData, JSON_PRETTY_PRINT));

        return $token;
    }

    echo "Failed to get access token:</br>";
    print_r($result);
    exit(1);
}
