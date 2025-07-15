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

        .password-container {
            position: relative;
            width: 100%;
            margin-bottom: 1rem;
        }

        .toggle-password {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #917489;
            padding: 8px;
            z-index: 10;
            width: 42px;
            height: 42px;
            transition: all 0.2s ease;
            background: transparent;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .toggle-password:hover {
            color: var(--lavender-accent);
            background-color: rgba(145, 116, 137, 0.1);
        }

        .toggle-password svg {
            width: 28px;
            height: 28px;
            stroke-width: 2px;
        }

        .password-container input {
            width: 100%;
            padding: 0.8rem 1rem;
            padding-right: 50px;
            margin-bottom: 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
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
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="New Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
                <div class="password-container">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')" aria-label="Toggle password visibility">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
    <script>
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleBtn = passwordInput.nextElementSibling;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                <line x1="3" y1="3" x2="21" y2="21" />
            </svg>`;
    } else {
        passwordInput.type = 'password';
        toggleBtn.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
            </svg>`;
    }
}
</script>
</body>
</html>