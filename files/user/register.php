<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "scentoradb";
$conn = new mysqli($host, $user, $pass, $db);

$name = $email = $password = $confirm_password = "";
$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validation
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
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
        $stmt = $conn->prepare("INSERT INTO user (Name, Email, User_Type, Password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $user_type, $hashed_password);
        if ($stmt->execute()) {
            $success = "Registration successful! You may now log in.";
            $name = $email = "";
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Scentora</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts for modern look -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #917489;
            --accent: #b497bd;
            --bg: #f7f5fa;
            --white: #fff;
            --error: #ffb3b3;
            --success: #d4edda;
            --shadow: 0 8px 32px 0 rgba(145,116,137,0.10);
            --lavender-border: #b497bd;
            --navbar-bg: rgba(255,255,255,0.85);
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: transparent;
            overflow: hidden; /* Prevent scrolling */
        }
        body {
            min-height: 100vh;
            min-width: 100vw;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        /* Moving background video */
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
        /* Header styles - make smaller to match homepage.html */
        header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding:  1rem 2rem;
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10;
            box-sizing: border-box;
        }
        .logo {
            font-size: 1.8rem;
    font-weight: bold;
    color: var(--white);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .nav-links {
            display: flex;
            gap: 2rem;
        }
        .nav-links a {
          text-decoration: none;
  color: var(--white);
  font-weight: 500;
  font-size: 1rem;
  transition: color 0.3s ease;
  padding: 0.25rem 0.5rem;
        }
        .nav-links a:hover {
            color: #b497bd;
        }
        /* Container and Form */
        .container {
            width: 100vw;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            /* Add top padding equal to navbar height */
            padding-top: 6.5rem;
            box-sizing: border-box;
            position: relative;
        }
        .form-container {
            background: var(--white);
            border-radius: 2.5rem;
            box-shadow: var(--shadow);
            padding: 3.5rem 3rem 2.5rem 3rem;
            width: 50vw; /* Reduce width to give navbar more space */
            max-width: 700px;
            height: 75vh;
            min-width: 0;
            min-height: 0;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            position: relative;
            z-index: 2;
            border: 4px solid var(--lavender-border);
            justify-content: center;
            /* Ensure the box doesn't go under the navbar */
            margin-top: 0;
        }
        .form-container h2 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 1.2rem;
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .step {
            display: none;
            animation: fadeIn 1s;
        }
        .step.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: none;}
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
        /* Progress bar */
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #f1eaf5;
            border-radius: 4px;
            margin-bottom: 2rem;
            overflow: hidden;
            position: relative;
        }
        .progress {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            width: 50%; /* Initial width for step 1 */
            transition: width 1s cubic-bezier(.4,0,.2,1), background 0.5s;
            position: absolute;
            left: 0;
            top: 0;
        }
        /* Responsive */
        @media (max-width: 900px) {
            header {
                padding: 1rem 0.5rem;
            }
            .nav-links {
                gap: 1rem;
            }
            .form-container {
                width: 90vw;
                max-width: 98vw;
            }
        }
        @media (max-width: 600px) {
            .container {
                min-height: 100vh;
                padding-top: 6.5rem;
            }
            .form-container {
                min-height: unset;
                height: 99vh;
                width: 99vw;
                padding: 1rem 0.2rem;
            }
            .form-container h2 {
                font-size: 1.3rem;
            }
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let currentStep = 1;
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const nextBtn = document.getElementById('next-btn');
            const prevBtn = document.getElementById('prev-btn');
            const progress = document.getElementById('progress');
            function updateProgress() {
                progress.style.width = currentStep === 1 ? "50%" : "100%";
            }
            updateProgress();

            nextBtn.addEventListener('click', function() {
                step1.classList.remove('active');
                step2.classList.add('active');
                currentStep = 2;
                updateProgress();
            });

            prevBtn.addEventListener('click', function() {
                step2.classList.remove('active');
                step1.classList.add('active');
                currentStep = 1;
                updateProgress();
            });
        });
    </script>
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
            <h2>Create Your Account</h2>
            <div class="progress-bar">
                <div class="progress" id="progress"></div>
            </div>
            <?php if (!empty($errors)): ?>
                <div class="error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form id="regForm" method="POST" autocomplete="off" style="width:100%;">
                <div id="step1" class="step active">
                    <div class="input-row">
                        <div class="input-group">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($name); ?>">
                        </div>
                        <div class="input-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="button" id="next-btn" class="btn">Next</button>
                    </div>
                </div>
                <div id="step2" class="step">
                    <div class="input-row">
                        <div class="input-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" required minlength="8">
                        </div>
                        <div class="input-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" required minlength="8">
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="button" id="prev-btn" class="btn" style="background:#eee;color:#917489;">Back</button>
                        <button type="submit" id="submit-btn" class="btn">Register</button>
                    </div>
                </div>
            </form>
            <div class="signin-link">
                Already have an account? <a href="signin.php">Sign in</a>
            </div>
        </div>
    </div>
</body>
</html>
