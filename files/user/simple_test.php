<?php
// Direct API test without session authentication
header('Content-Type: text/html');
echo "<h2>Direct API Response Test</h2>";

$url = 'http://localhost/IM2-Scentora/files/user/get_reviewable_orders.php';
$response = file_get_contents($url);

echo "<h3>Raw Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

if (json_decode($response)) {
    echo "<h3>✅ Valid JSON Response</h3>";
} else {
    echo "<h3>❌ Invalid JSON Response</h3>";
    echo "<p>This explains why the frontend gets 'SyntaxError: Unexpected token'</p>";
}
?>
