<?php
/**
 * API Session Middleware for Scentora
 * Handles session checks for API endpoints with JSON responses
 */

require_once '../includes/session_config.php';

/**
 * Check if user is logged in and return JSON if not
 */
function apiRequireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'message' => 'Authentication required. Please login.',
            'redirect' => '/IM2-Scentora/files/admin/loginpage.php'
        ]);
        exit();
    }
}

/**
 * Check if user is admin and return JSON if not
 */
function apiRequireAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Admin access required.',
            'redirect' => '/IM2-Scentora/files/admin/loginpage.php'
        ]);
        exit();
    }
}

/**
 * Check if user is consumer and return JSON if not
 */
function apiRequireConsumer() {
    if (!isConsumer()) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Consumer access required.',
            'redirect' => '/IM2-Scentora/files/admin/loginpage.php'
        ]);
        exit();
    }
}

/**
 * Set CORS headers for API responses
 */
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

/**
 * Set JSON response headers
 */
function setJsonHeaders() {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
}
?>
