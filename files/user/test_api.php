<?php
// Simple test script to check the reviewable orders API

// Start a session to simulate a logged-in user
session_start();

// Set test session data (you may need to adjust these based on your actual session structure)
$_SESSION['user_id'] = 1; // Test with user ID 1
$_SESSION['consumer_id'] = 1;
$_SESSION['Consumer_ID'] = 1;

// Make a request to the API
$url = 'http://localhost/IM2-Scentora/files/user/get_reviewable_orders.php';

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "<h2>API Test Results</h2>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Check if response is valid JSON
$json = json_decode($response, true);
if ($json) {
    echo "<h3>Parsed JSON:</h3>";
    echo "<pre>" . print_r($json, true) . "</pre>";
} else {
    echo "<h3>JSON Parse Error:</h3>";
    echo "<p>Response is not valid JSON</p>";
}

curl_close($ch);
?>
