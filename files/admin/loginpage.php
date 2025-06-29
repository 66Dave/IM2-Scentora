<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = trim($_POST['username'] ?? '');
$pass = trim($_POST['password'] ?? '');

if (empty($name) || empty($pass)) {
    echo "Please fill in both fields.";
    exit;
}

// Use correct table and column names
$stmt = $conn->prepare("SELECT * FROM user WHERE Name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    
    // Password check (not hashed)
    if ($pass === $row['Password']) {
        // Optional: set session or redirect here
        echo "success";
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "User not found.";
}

$stmt->close();
$conn->close();
?>
