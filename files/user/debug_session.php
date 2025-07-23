<?php
require_once '../includes/session_config.php';

header('Content-Type: application/json');

// Debug: Check what session variables are available
$sessionDebug = [
    'session_data' => $_SESSION,
    'session_id' => session_id(),
    'all_possible_user_vars' => []
];

// Check for various possible user ID keys
$possibleUserKeys = ['user_id', 'User_ID', 'userid', 'id', 'consumer_id', 'Consumer_ID'];
foreach ($possibleUserKeys as $key) {
    if (isset($_SESSION[$key])) {
        $sessionDebug['all_possible_user_vars'][$key] = $_SESSION[$key];
    }
}

// Check for various possible user type keys
$possibleTypeKeys = ['user_type', 'User_Type', 'usertype', 'type', 'role', 'User_Role'];
foreach ($possibleTypeKeys as $key) {
    if (isset($_SESSION[$key])) {
        $sessionDebug['all_possible_user_vars'][$key] = $_SESSION[$key];
    }
}

echo json_encode([
    'success' => true,
    'debug' => $sessionDebug,
    'message' => 'Session debug information'
]);
?>
