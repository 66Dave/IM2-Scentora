<?php
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

try {
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];
    $sql = "DELETE FROM cart WHERE User_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>