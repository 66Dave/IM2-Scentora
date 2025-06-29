<?php
session_start();

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "scentoradb";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    die("<h2>Please enter both username and password.</h2><p><a href='loginpage.php'>Back</a></p>");
}

// Prepare and execute SQL
$sql = "SELECT * FROM user WHERE Name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Compare plain text password directly
    if ($password === $user['Password']) {
        // Start session and redirect
        $_SESSION['username'] = $user['Name'];
        header("Location: dashboard.html"); // Make sure this file exists!
        exit();
    } else {
        echo "<h2>Login failed. Incorrect password.</h2>";
        echo '<p><a href="loginpage.php">Try again</a></p>';
    }
} else {
    echo "<h2>Login failed. User not found.</h2>";
    echo '<p><a href="loginpage.php">Try again</a></p>';
}

// Close connections
$stmt->close();
$conn->close();
?>
