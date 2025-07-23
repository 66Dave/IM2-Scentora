<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin without redirecting
function isAdminUser() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['user_type']) && 
           strtolower(trim($_SESSION['user_type'])) === 'admin';
}

// If not admin, return error
if (!isAdminUser()) {
    http_response_code(401);
    die('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

try {
    // Database connection
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "scentoradb";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath;
            } else {
                throw new Exception("Failed to upload image");
            }
        } else {
            throw new Exception("Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.");
        }
    }

    // Get form data
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validate required fields
    if (empty($name) || empty($code) || empty($category) || empty($brand) || $price <= 0) {
        throw new Exception("Please fill in all required fields with valid values");
    }

    // Check if product code already exists
    $checkQuery = "SELECT Product_ID FROM product WHERE Product_Code = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $code);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        throw new Exception("Product code already exists. Please use a different code.");
    }

    // Insert new product
    $insertQuery = "INSERT INTO product (Product_Name, Product_Code, Product_Price, Available_Stocks, Category, Brand, Description, Image_URL, Is_Active, Date_Added, Date_Updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, CURDATE(), CURDATE())";
    
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssdissss", $name, $code, $price, $stock, $category, $brand, $description, $imagePath);
    
    if ($stmt->execute()) {
        echo "success: Product added successfully";
    } else {
        throw new Exception("Failed to add product: " . $stmt->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo "error: " . $e->getMessage();
}

if (isset($conn)) {
    $conn->close();
}
?>
