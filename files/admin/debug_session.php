<?php
// Simple admin session test
session_start();

echo "<h2>Admin Session Debug</h2>";

echo "<h3>Current Session Data:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Include session functions
require_once '../includes/session_config.php';

echo "<h3>Session Function Tests:</h3>";
echo "<p>isLoggedIn(): " . (isLoggedIn() ? 'YES' : 'NO') . "</p>";
echo "<p>isAdmin(): " . (isAdmin() ? 'YES' : 'NO') . "</p>";
echo "<p>getCurrentUserId(): " . (getCurrentUserId() ?? 'NULL') . "</p>";

if (isset($_SESSION['user_type'])) {
    echo "<p>User Type: '" . $_SESSION['user_type'] . "'</p>";
    echo "<p>User Type (trimmed/lowercase): '" . strtolower(trim($_SESSION['user_type'])) . "'</p>";
}

echo "<h3>Manual Admin Login (for testing):</h3>";
echo "<form method='post'>";
echo "<button type='submit' name='set_admin'>Set Admin Session</button>";
echo "<button type='submit' name='clear_session'>Clear Session</button>";
echo "</form>";

if (isset($_POST['set_admin'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_type'] = 'admin';
    echo "<p>✅ Admin session set! Refresh to see changes.</p>";
}

if (isset($_POST['clear_session'])) {
    session_destroy();
    echo "<p>✅ Session cleared! Refresh to see changes.</p>";
}

echo "<h3>Test Admin APIs:</h3>";
echo "<p><a href='dashboard_data.php' target='_blank'>Test Dashboard Data API</a></p>";
echo "<p><a href='inventory_fetch.php' target='_blank'>Test Inventory API</a></p>";
?>
