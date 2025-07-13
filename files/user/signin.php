<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "scentoradb";
$conn = new mysqli($host, $user, $pass, $db);

$email = $password = "";
$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validation
    if (empty($email)) $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (empty($password)) $errors[] = "Password is required.";

    // Check if email exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT User_ID, Password FROM user WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $errors[] = "No account found with that email.";
        } else {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();
            if (!password_verify($password, $hashed_password)) {
                $errors[] = "Incorrect password.";
            } else {
                // Redirect to shop.html after successful login
                header("Location: /IM2-Scentora/files/admin/shop.html");
                exit();
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In | Scentora</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #917489;
            --accent: #b497bd;
            --bg: #f7f5fa;
            --white: #fff;
            --error: #ffb3b3;
            --success: #d4edda;
            --shadow: 0 8px 32px 0 rgba(145, 116, 137, 0.10);
            --lavender-border: #b497bd;
            --navbar-bg: rgba(255,255,255,0.85);
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: transparent;
            overflow: hidden;
        }
        body {
            min-height: 100vh;
            min-width: 100vw;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .bg-video {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            object-fit: cover;
            z-index: -2;
            min-width: 100vw;
            min-height: 100vh;
            background: #c89bce;
        }
        header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 2.5rem 1.2rem 2.5rem;
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10;
            box-sizing: border-box;
        }
        .logo {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: #fff;
            letter-spacing: 1px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.18);
        }
        .nav-links {
            display: flex;
            gap: 2rem;
        }
        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-family: 'Inter', Arial, sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            transition: color 0.2s;
            padding: 0.25rem 0.5rem;
        }
        .nav-links a:hover {
            color: #b497bd;
        }
        .container {
            width: 100vw;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 6.5rem;
            box-sizing: border-box;
            position: relative;
        }
        .form-container {
            background: var(--white);
            border-radius: 2.5rem;
            box-shadow: var(--shadow);
            padding: 3.5rem 3rem 2.5rem 3rem;
            width: 50vw;
            max-width: 700px;
            height: 75vh;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            position: relative;
            z-index: 2;
            border: 4px solid var(--lavender-border);
            justify-content: center;
        }
        .form-container h2 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 1.2rem;
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .input-row {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            width: 100%;
        }
        .input-group {
            width: 100%;
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .input-group label {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }
        .input-group input {
            width: 100%;
            padding: 1.2rem 1.2rem;
            border: 1.5px solid #e0d6e6;
            border-radius: 0.8rem;
            font-size: 1.15rem;
            background: #f8f6fb;
            transition: border 0.2s;
        }
        .input-group input:focus {
            border-color: var(--accent);
            outline: none;
            background: #fff;
        }
        .btn-row {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        .btn {
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            color: #fff;
            border: none;
            padding: 1.1rem 0;
            border-radius: 0.8rem;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            margin-top: 0.5rem;
            width: 100%;
            max-width: 220px;
        }
        .btn:hover {
            background: linear-gradient(90deg, var(--accent) 0%, var(--primary) 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .error, .success {
            padding: 1rem 1.2rem;
            margin-bottom: 0.7rem;
            border-radius: 0.7rem;
            font-size: 1.1rem;
            text-align: center;
        }
        .error {
            background: var(--error);
            color: #721c24;
        }
        .success {
            background: var(--success);
            color: #155724;
        }
        .signin-link {
            text-align: center;
            margin-top: 1.2rem;
            font-size: 1.1rem;
        }
        .signin-link a {
            color: var(--accent);
            text-decoration: underline;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <video class="bg-video" src="/IM2-Scentora/files/videos/bg.mp4" autoplay loop muted playsinline></video>
    <header>
        <div class="logo">Scentora</div>
        <nav class="nav-links">
            <a href="/IM2-Scentora/index.html">Home</a>
            <a href="/IM2-Scentora/shop.html">Shop</a>
            <a href="/IM2-Scentora/files/user/aboutus.html">About us</a>
        </nav>
    </header>
    <div class="container">
        <div class="form-container">
            <h2>Sign In</h2>
            <?php if (!empty($errors)): ?>
                <div class="error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form id="signInForm" method="POST" autocomplete="off">
                <div class="input-row">
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required minlength="8">
                    </div>
                </div>
                <div class="btn-row">
                    <button type="submit" class="btn">Sign In</button>
                </div>
            </form>
            <div class="signin-link">
                Don't have an account? <a href="register.php">Sign up</a>
            </div>
        </div>
    </div>
</body>
</html>
