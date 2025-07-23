<?php
require_once '../includes/api_middleware.php';

// Allow both logged in and guest users to view reviews
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

$product_id = (int)($_GET['product_id'] ?? 0);

if ($product_id <= 0) {
    die(json_encode(['success' => false, 'error' => 'Invalid product ID']));
}

// Get product reviews with user details
$sql = "SELECT 
    pr.Review_ID, pr.Rating, pr.Review_Text, pr.Review_Date, pr.Helpful_Count,
    COALESCE(cd.Consumer_Name, 'Anonymous User') as Consumer_Name,
    DATE_FORMAT(pr.Review_Date, '%M %d, %Y') as Formatted_Date
    FROM product_reviews pr
    LEFT JOIN user u ON pr.User_ID = u.User_ID
    LEFT JOIN consumerdetails cd ON u.User_ID = cd.User_ID
    WHERE pr.Product_ID = ? 
    AND pr.Is_Approved = 1
    ORDER BY pr.Review_Date DESC
    LIMIT 50";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
$total_rating = 0;
$rating_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

while ($row = $result->fetch_assoc()) {
    $total_rating += (int)$row['Rating'];
    $rating_counts[(int)$row['Rating']]++;
    
    $reviews[] = [
        'Review_ID' => (int)$row['Review_ID'],
        'Rating' => (int)$row['Rating'],
        'Review_Text' => $row['Review_Text'],
        'Consumer_Name' => $row['Consumer_Name'],
        'Review_Date' => $row['Review_Date'],
        'Formatted_Date' => $row['Formatted_Date'],
        'Helpful_Count' => (int)$row['Helpful_Count']
    ];
}

$total_reviews = count($reviews);
$average_rating = $total_reviews > 0 ? round($total_rating / $total_reviews, 1) : 0;

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'product_id' => $product_id,
    'reviews' => $reviews,
    'total_reviews' => $total_reviews,
    'average_rating' => $average_rating,
    'rating_distribution' => $rating_counts
]);
?>
