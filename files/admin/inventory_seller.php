<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['name'] ?? '';
    $code = $_POST['code'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category = $_POST['category'] ?? '';
    $brand = $_POST['brand'] ?? 'Scentora';
    $description = $_POST['description'] ?? '';
    $is_active = 1;

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image = $targetPath;
            }
        }
    }

    // Insert into database
    $sql = "INSERT INTO product (
        User_ID, Product_Name, Product_Price, Product_Code, 
        Category, Image_URL, Date_Added, Date_Updated, 
        Brand, Description, Is_Active, Available_Stocks
    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $user_id = 1; // Default admin user
    
    $stmt->bind_param(
        "isdsssssiii",
        $user_id, $product_name, $price, $code,
        $category, $image, $brand, $description,
        $is_active, $stock
    );

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
