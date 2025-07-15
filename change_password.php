<?php
$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $conn = new mysqli("localhost", "root", "", "scentoradb");
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Debug token verification
    error_log("Token received: " . $token);

    // Modified token verification query with more detailed error checking
    $stmt = $conn->prepare("SELECT User_ID, reset_expiry FROM user WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = "Invalid reset token";
        error_log("No user found with token: " . $token);
    } else {
        $row = $result->fetch_assoc();
        $expiry = strtotime($row['reset_expiry']);
        $now = time();

        if ($now > $expiry) {
            $error = "Reset link has expired. Please request a new one.";
            error_log("Token expired. Expiry: " . date('Y-m-d H:i:s', $expiry) . ", Current: " . date('Y-m-d H:i:s', $now));
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Update password and clear reset token
            $stmt = $conn->prepare("UPDATE user SET Password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $hashed_password, $token);
            
            if ($stmt->execute()) {
                $success = "Password successfully reset. You can now login with your new password.";
            } else {
                $error = "Error updating password";
            }
        } else {
            $error = "Passwords do not match";
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Scentora</title>
    <style>
        :root {
            --lavender-light: #d2b3d6;
            --white: #ffffff;
            --mauve: #917489;
            --lavender-accent: #b497bd;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .reset-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
        }

        h2 {
            color: var(--mauve);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .error {
            color: #dc3545;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success {
            color: #28a745;
            margin-bottom: 1rem;
            text-align: center;
        }

        input {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 0.8rem;
            background: var(--lavender-accent);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background: var(--mauve);
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: var(--mauve);
            text-decoration: none;
        }

        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset Password</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <a href="loginpage.php" class="login-link">Back to Login</a>
        <?php elseif ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <a href="loginpage.php" class="login-link">Go to Login</a>
        <?php else: ?>
            <form method="POST">
                <input type="password" name="password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>