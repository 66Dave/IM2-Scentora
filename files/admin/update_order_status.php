<?php
$conn = new mysqli("localhost", "root", "", "scentoradb");
if ($conn->connect_error) die("Connection failed");

$order_id = intval($_POST['order_id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($order_id < 1 || !in_array($status, ['Accepted', 'Declined'])) {
    echo "Invalid request";
    exit;
}

$sql = "UPDATE `order` SET Status = ? WHERE Order_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $order_id);
if ($stmt->execute()) {
    echo "updated";
} else {
    echo "error";
}
$stmt->close();
$conn->close();
?>