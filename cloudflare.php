<?php

$zone_id = '';
$email = '';
$api_key = '';
$base_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone_id;

// Function to make the API request
function cloudflareApiRequest($url, $method, $data = [], $email, $api_key) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Auth-Email: ' . $email,
            'X-Auth-Key: ' . $api_key
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        return json_decode($response, true);
    }
}

// Toggle Development Mode
function toggleDevelopmentMode($enable, $email, $api_key, $base_url) {
    $url = $base_url . '/settings/development_mode';
    $method = 'PATCH';
    $data = ['value' => $enable ? 'on' : 'off'];

    $response = cloudflareApiRequest($url, $method, $data, $email, $api_key);

    echo $enable ? 'Enabling' : 'Disabling' . " Development Mode:\n";
    print_r($response);
}

// Clear Cache
function clearCache($email, $api_key, $base_url) {
    $url = $base_url . '/purge_cache';
    $method = 'POST';
    $data = ['purge_everything' => true];

    $response = cloudflareApiRequest($url, $method, $data, $email, $api_key);

    echo "Clearing Cache:\n";
    print_r($response);
}

// Toggle Development Mode Example
toggleDevelopmentMode(true, $email, $api_key, $base_url); // Enable Development Mode
//toggleDevelopmentMode(false, $email, $api_key, $base_url); // Disable Development Mode

// Clear Cache Example
clearCache($email, $api_key, $base_url);

?>
