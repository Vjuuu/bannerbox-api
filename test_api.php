<?php
/**
 * API Testing Script
 * Run this file to test your CodeIgniter 3 API endpoints
 */

// Configuration
$base_url = 'http://codeigniter-api.local'; // Change this to your local URL
$api_key = 'your-api-key-12345';

// Test credentials
$test_email = 'admin@example.com';
$test_password = 'password';

echo "=== CodeIgniter 3 API Testing Script ===\n\n";

/**
 * Make API Request
 */
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status_code' => $http_code,
        'response' => json_decode($response, true)
    ];
}

// Test 1: Get Categories
echo "1. Testing GET /api/categories\n";
$result = makeRequest($base_url . '/api/categories', 'GET', null, [
    'X-API-KEY: ' . $api_key,
    'Content-Type: application/json'
]);

echo "Status: " . $result['status_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Login
echo "2. Testing POST /api/auth/login\n";
$login_data = [
    'email' => $test_email,
    'password' => $test_password
];

$result = makeRequest($base_url . '/api/auth/login', 'POST', $login_data, [
    'X-API-KEY: ' . $api_key,
    'Content-Type: application/json'
]);

echo "Status: " . $result['status_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// Extract JWT token for further tests
$jwt_token = null;
if ($result['response']['success'] && isset($result['response']['data']['token'])) {
    $jwt_token = $result['response']['data']['token'];
    echo "JWT Token extracted successfully!\n\n";
}

// Test 3: Get User Profile (requires JWT)
if ($jwt_token) {
    echo "3. Testing GET /api/auth/profile (with JWT)\n";
    $result = makeRequest($base_url . '/api/auth/profile', 'GET', null, [
        'X-API-KEY: ' . $api_key,
        'Authorization: Bearer ' . $jwt_token,
        'Content-Type: application/json'
    ]);
    
    echo "Status: " . $result['status_code'] . "\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";
}

// Test 4: Get Posters
echo "4. Testing GET /api/posters\n";
$result = makeRequest($base_url . '/api/posters', 'GET', null, [
    'X-API-KEY: ' . $api_key,
    'Content-Type: application/json'
]);

echo "Status: " . $result['status_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// Test 5: Get Trending Posters
echo "5. Testing GET /api/posters/trending\n";
$result = makeRequest($base_url . '/api/posters/trending', 'GET', null, [
    'X-API-KEY: ' . $api_key,
    'Content-Type: application/json'
]);

echo "Status: " . $result['status_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// Test 6: Create Category (requires JWT)
if ($jwt_token) {
    echo "6. Testing POST /api/categories/create (with JWT)\n";
    $category_data = [
        'name' => 'Test Category',
        'description' => 'This is a test category created by API test script',
        'icon' => 'test-icon',
        'color' => '#FF5733'
    ];
    
    $result = makeRequest($base_url . '/api/categories/create', 'POST', $category_data, [
        'X-API-KEY: ' . $api_key,
        'Authorization: Bearer ' . $jwt_token,
        'Content-Type: application/json'
    ]);
    
    echo "Status: " . $result['status_code'] . "\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";
}

// Test 7: Test Invalid API Key
echo "7. Testing Invalid API Key\n";
$result = makeRequest($base_url . '/api/categories', 'GET', null, [
    'X-API-KEY: invalid-key',
    'Content-Type: application/json'
]);

echo "Status: " . $result['status_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// Test 8: Test Missing API Key
echo "8. Testing Missing API Key\n";
$result = makeRequest($base_url . '/api/categories', 'GET', null, [
    'Content-Type: application/json'
]);

echo "Status: " . $result['status_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

echo "=== API Testing Complete ===\n";
echo "If all tests show status 200 (except invalid/missing API key tests), your API is working correctly!\n";
?>