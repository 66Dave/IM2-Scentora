<?php
require_once '../includes/api_middleware.php';

setJsonHeaders();

// Check session status and return information
$response = [
    'logged_in' => isLoggedIn(),
    'user_type' => getCurrentUserType(),
    'user_id' => getCurrentUserId(),
    'session_valid' => true
];

// Check if session is about to expire (within 5 minutes)
if (isset($_SESSION['last_activity'])) {
    $timeLeft = (120 * 60) - (time() - $_SESSION['last_activity']); // 2 hours - elapsed time
    $response['time_left'] = max(0, $timeLeft);
    $response['expires_soon'] = $timeLeft < 300; // Less than 5 minutes
}

echo json_encode($response);
?>
