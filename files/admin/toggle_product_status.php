<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = intval($_POST['id']);
$active = intval($_POST['active']); // 0 or 1

$sql = "UPDATE product SET Is_Active=? WHERE Product_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $active, $id);

if ($stmt->execute()) {
  echo "success";
} else {
  echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>