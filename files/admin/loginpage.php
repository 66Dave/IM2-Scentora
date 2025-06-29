<?php
$servername = "localhost";
$db_username = "root"; // THIS IS DEFAULT FOR XAMPP, IF YOU GUYS WANT TO DEPLOY IT IN A DOMAND OR GITHUB PAGE, WE WILL HAVE TO CHANGE IT RA
$db_password = "";
$dbname = "scentoradb";

//for connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//get details
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    die("Please provide both username and password.");
}

$sql = "SELECT * FROM User WHERE Name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['Password'])) {
        // Redirect to dashboard.html on successful login
        header("Location: dashboard.html");
        exit();
    } else {
        echo "<h2>Login failed. Incorrect password.</h2>";
        echo '<p><a href="loginpage.html">Try again</a></p>';
    }
} else {
    echo "<h2>Login failed. User not found.</h2>";
    echo '<p><a href="loginpage.html">Try again</a></p>';
}

$stmt->close();
$conn->close();
?>
