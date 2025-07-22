# Scentora Session Management System

## Overview
This document describes the centralized session management system implemented for Scentora.

## Files Structure

### Core Session Files
- `files/includes/session_config.php` - Main session configuration and functions
- `files/includes/api_middleware.php` - API-specific session handling 
- `files/includes/session_status.php` - Session status checker for AJAX calls

### Key Functions

#### session_config.php Functions
- `isLoggedIn()` - Check if user is logged in
- `isAdmin()` - Check if user is admin
- `isConsumer()` - Check if user is consumer  
- `requireLogin($redirectPath)` - Require login or redirect
- `requireAdmin($redirectPath)` - Require admin access or redirect
- `requireConsumer($redirectPath)` - Require consumer access or redirect
- `getCurrentUserId()` - Get current user ID
- `getCurrentUserType()` - Get current user type
- `logout($redirectPath)` - Secure logout
- `checkSessionTimeout($minutes)` - Check and enforce session timeout
- `setSessionMessage($message, $type)` - Set flash message
- `getSessionMessage()` - Get and clear flash message

#### api_middleware.php Functions
- `apiRequireLogin()` - Return JSON error if not logged in
- `apiRequireAdmin()` - Return JSON error if not admin
- `apiRequireConsumer()` - Return JSON error if not consumer
- `setJsonHeaders()` - Set appropriate JSON headers
- `setCorsHeaders()` - Set CORS headers

## Security Features

### Session Security
- HTTP-only cookies
- Secure cookie settings
- Session ID regeneration every 5 minutes
- Session timeout after 2 hours of inactivity
- Proper session destruction on logout

### Access Control  
- Role-based access (Admin/Consumer)
- Automatic redirects for unauthorized access
- API endpoint protection
- Session validation on every request

## Implementation Examples

### Page Protection (HTML Pages)
```php
<?php
require_once '../includes/session_config.php';
requireConsumer(); // or requireAdmin() for admin pages
checkSessionTimeout();
?>
```

### API Protection (JSON Endpoints)
```php
<?php
require_once '../includes/api_middleware.php';
setJsonHeaders();
apiRequireConsumer(); // or apiRequireAdmin()
?>
```

### Using Session Data
```php
$userId = getCurrentUserId();
$userType = getCurrentUserType();

if (isAdmin()) {
    // Admin-specific code
}
```

### Flash Messages
```php
// Set message
setSessionMessage('Order created successfully!', 'success');

// Get message (automatically clears after reading)
$message = getSessionMessage();
if ($message) {
    echo "<div class='alert alert-{$message['type']}'>{$message['message']}</div>";
}
```

## Client-Side Integration

### JavaScript Session Monitoring
The system includes automatic session monitoring:
- Checks session status every 5 minutes
- Warns user when session expires soon
- Auto-redirects on session expiration
- Monitors page visibility for active session checking

### Implementation in Pages
```javascript
// Session monitoring is automatically started in shop_user.php
// Can be added to other pages as needed
startSessionMonitoring();
```

## Updated Files

### User Files Updated
- `shop_user.php` - Main shop page
- `user_profile.php` - User profile  
- `orders_user.php` - User orders
- `add_to_cart.php` - Add to cart API
- `get_top_selling.php` - Top selling API

### Admin Files Updated  
- `loginpage.php` - Login page
- `logout.php` - Logout handler
- `dashboard_data.php` - Dashboard data
- `inventory_fetch.php` - Inventory API

## Migration Benefits

1. **Centralized Management** - All session logic in one place
2. **Enhanced Security** - Proper session handling and timeout
3. **Role-Based Access** - Clear separation of admin/consumer access
4. **API Protection** - JSON-appropriate error responses
5. **Session Monitoring** - Client-side session status tracking
6. **Easy Maintenance** - Consistent session handling across all files

## Usage Notes

- Always include session files at the top of PHP files
- Use appropriate require functions based on page type
- API endpoints should use api_middleware.php
- Regular pages should use session_config.php
- Session timeout can be adjusted in checkSessionTimeout() calls
