<?php
// Start output buffering to prevent any accidental output
ob_start();

require_once '../includes/session_config.php';
require_once '../includes/api_middleware.php';

try {
    // Require consumer login for API
    apiRequireConsumer();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $user_id = getCurrentUserId();
    if (!$user_id) {
        throw new Exception('User ID not found in session');
    }

    // Check if this is a batch review submission
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input && isset($input['reviews'])) {
        // Handle multiple reviews
        $reviews = $input['reviews'];
        
        if (!is_array($reviews) || empty($reviews)) {
            throw new Exception('No reviews provided');
        }
        
        // Database connection
        $host = "localhost";
        $username = "root";
        $password = "";
        $database = "scentoradb";

        $conn = new mysqli($host, $username, $password, $database);
        if ($conn->connect_error) {
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }
        
        $success_count = 0;
        $errors = [];
        
        foreach ($reviews as $review) {
            $order_id = (int)($review['order_id'] ?? 0);
            $product_id = (int)($review['product_id'] ?? 0);
            $rating = (int)($review['rating'] ?? 0);
            $review_text = trim($review['review_text'] ?? '');
            
            // Validate input
            if ($order_id <= 0 || $product_id <= 0) {
                $errors[] = "Invalid order or product ID for product $product_id";
                continue;
            }
            
            if ($rating < 1 || $rating > 5) {
            $errors[] = "Invalid rating for product $product_id";
            continue;
        }
        
        if (strlen($review_text) > 1000) {
            $errors[] = "Review text too long for product $product_id";
            continue;
        }
        
        // Verify the order belongs to the user and is completed within review period
        $verify_sql = "SELECT o.Order_ID, o.Status, o.Arrival_Date,
                       DATEDIFF(CURDATE(), o.Arrival_Date) as days_since_arrival
                       FROM `order` o
                       INNER JOIN orderdetails od ON o.Order_ID = od.Order_ID
                       WHERE o.Order_ID = ? 
                       AND o.User_ID = ? 
                       AND od.Product_ID = ?
                       AND o.Status = 'Completed' 
                       AND o.Arrival_Date IS NOT NULL
                       AND DATEDIFF(CURDATE(), o.Arrival_Date) <= 7";

        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("iii", $order_id, $user_id, $product_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();

        if ($verify_result->num_rows === 0) {
            $verify_stmt->close();
            $errors[] = "Product $product_id not eligible for review or review period expired";
            continue;
        }
        $verify_stmt->close();

        // Check if review already exists
        $check_sql = "SELECT Review_ID FROM product_reviews WHERE Order_ID = ? AND Product_ID = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $order_id, $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $check_stmt->close();
            $errors[] = "Product $product_id already reviewed";
            continue;
        }
        $check_stmt->close();

        // Insert the review
        $insert_sql = "INSERT INTO product_reviews (Order_ID, Product_ID, User_ID, Rating, Review_Text, Review_Date, Is_Approved) 
                       VALUES (?, ?, ?, ?, ?, NOW(), 1)";

        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iiiis", $order_id, $product_id, $user_id, $rating, $review_text);

        if ($insert_stmt->execute()) {
            $success_count++;
        } else {
            $errors[] = "Failed to submit review for product $product_id";
        }
        $insert_stmt->close();
    }
    
        $conn->close();
        
        if ($success_count > 0) {
            $response = [
                'success' => true, 
                'message' => "Successfully submitted $success_count reviews" . (count($errors) > 0 ? ' with some errors' : ''),
                'success_count' => $success_count,
                'errors' => $errors
            ];
        } else {
            throw new Exception('No reviews were submitted: ' . implode(', ', $errors));
        }
        
    } else {
        // Handle single review (original functionality)
        $order_id = (int)($_POST['order_id'] ?? 0);
        $product_id = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $review_text = trim($_POST['review_text'] ?? '');

        // Validate input
        if ($order_id <= 0 || $product_id <= 0) {
            throw new Exception('Invalid order or product ID');
        }

        if ($rating < 1 || $rating > 5) {
            throw new Exception('Rating must be between 1 and 5 stars');
        }

        if (strlen($review_text) > 1000) {
            throw new Exception('Review text too long (max 1000 characters)');
        }

        // Database connection
        $host = "localhost";
        $username = "root";
        $password = "";
        $database = "scentoradb";

        $conn = new mysqli($host, $username, $password, $database);
        if ($conn->connect_error) {
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }

        // Verify the order belongs to the user and is completed within review period
        $verify_sql = "SELECT o.Order_ID, o.Status, o.Arrival_Date,
                       DATEDIFF(CURDATE(), o.Arrival_Date) as days_since_arrival
                       FROM `order` o
                       INNER JOIN orderdetails od ON o.Order_ID = od.Order_ID
                       WHERE o.Order_ID = ? 
                       AND o.User_ID = ? 
                       AND od.Product_ID = ?
                       AND o.Status = 'Completed' 
                       AND o.Arrival_Date IS NOT NULL
                       AND DATEDIFF(CURDATE(), o.Arrival_Date) <= 7";

        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("iii", $order_id, $user_id, $product_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();

        if ($verify_result->num_rows === 0) {
            $verify_stmt->close();
            $conn->close();
            throw new Exception('Order not eligible for review or review period expired');
        }

        $verify_stmt->close();

        // Check if review already exists
        $check_sql = "SELECT Review_ID FROM product_reviews WHERE Order_ID = ? AND Product_ID = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $order_id, $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $check_stmt->close();
            $conn->close();
            throw new Exception('You have already reviewed this product');
        }

        $check_stmt->close();

        // Insert the review
        $insert_sql = "INSERT INTO product_reviews (Order_ID, Product_ID, User_ID, Rating, Review_Text, Review_Date, Is_Approved) 
                       VALUES (?, ?, ?, ?, ?, NOW(), 1)";

        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iiiis", $order_id, $product_id, $user_id, $rating, $review_text);

        if ($insert_stmt->execute()) {
            $review_id = $conn->insert_id;
            $insert_stmt->close();
            $conn->close();
            
            $response = [
                'success' => true, 
                'message' => 'Review submitted successfully!',
                'review_id' => $review_id
            ];
        } else {
            $insert_stmt->close();
            $conn->close();
            throw new Exception('Failed to submit review');
        }
    }

    // Clean output buffer and send JSON response
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    // Clean output buffer and send error response
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
