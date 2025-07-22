<?php
/**
 * Centralized Session Management for Scentora
 * This file handles all session initialization and security
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    
    // Set session lifetime (24 hours)
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    
    session_start();
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && 
           isset($_SESSION['user_type']) && 
           strtolower(trim($_SESSION['user_type'])) === 'admin';
}

/**
 * Check if user is consumer
 */
function isConsumer() {
    return isLoggedIn() && 
           isset($_SESSION['user_type']) && 
           strtolower(trim($_SESSION['user_type'])) === 'consumer';
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin($redirectPath = '/IM2-Scentora/files/admin/loginpage.php') {
    if (!isLoggedIn()) {
        header("Location: $redirectPath");
        exit();
    }
}

/**
 * Require admin access - redirect if not admin
 */
function requireAdmin($redirectPath = '/IM2-Scentora/files/admin/loginpage.php') {
    if (!isAdmin()) {
        header("Location: $redirectPath");
        exit();
    }
}

/**
 * Require consumer access - redirect if not consumer
 */
function requireConsumer($redirectPath = '/IM2-Scentora/files/admin/loginpage.php') {
    if (!isConsumer()) {
        header("Location: $redirectPath");
        exit();
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user type
 */
function getCurrentUserType() {
    return $_SESSION['user_type'] ?? null;
}

/**
 * Destroy session and logout
 */
function logout($redirectPath = '/IM2-Scentora/files/admin/loginpage.php') {
    // Clear all session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Destroy session
    session_destroy();
    
    // Redirect to login
    header("Location: $redirectPath");
    exit();
}

/**
 * Set session message for next page load
 */
function setSessionMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear session message
 */
function getSessionMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return ['message' => $message, 'type' => $type];
    }
    
    return null;
}

/**
 * Check session timeout (optional feature)
 */
function checkSessionTimeout($timeoutMinutes = 120) { // 2 hours default
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > ($timeoutMinutes * 60))) {
        logout();
    }
    $_SESSION['last_activity'] = time();
}
?>
