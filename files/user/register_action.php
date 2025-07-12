<?php
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "scentoradb";
$conn = new mysqli($host, $user, $pass, $db);

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$address = trim($_POST["address"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";

$errors = [];
$success = "";

if (empty($name)) $errors[] = "Name is required.";
if (empty($email)) $errors[] = "Email is required.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
if (empty($address)) $errors[] = "Address is required.";
if (empty($password)) $errors[] = "Password is required.";
if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

// Check if email already exists
if (empty($errors)) {
    $stmt = $conn->prepare("SELECT User_ID FROM user WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email is already registered.";
    }
    $stmt->close();
}

// Insert into database
if (empty($errors)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $user_type = "Consumer";
    $stmt = $conn->prepare("INSERT INTO user (Name, Email, Address, User_Type, Password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $address, $user_type, $hashed_password);
    if ($stmt->execute()) {
        $success = "Registration successful! You may now log in.";
    } else {
        $errors[] = "Registration failed. Please try again.";
    }
    $stmt->close();
}

echo json_encode([
    "success" => $success,
    "errors" => $errors
]);