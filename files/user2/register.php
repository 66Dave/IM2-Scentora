<?php

//filepath: c:\xampp\htdocs\IM2-Scentora\files\user\register.php <-

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "scentoradb";
$conn = new mysqli($host, $user, $pass, $db);

$name = $email = $address = $password = $confirm_password = "";
$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validation
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
            $name = $email = $address = "";
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
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
        }
        .bg-video {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            object-fit: cover;
            z-index: -2;
            min-width: 100%;
            min-height: 100%;
            background: #c89bce;
        }
        .bg-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(40, 20, 40, 0.35);
            z-index: -1;
        }
        .register-container {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(8px);
            padding: 1.2rem 1rem 1rem 1rem; /* reduced padding */
            border-radius: 1.2rem;
            box-shadow: 0 8px 32px rgba(145, 116, 137, 0.18);
            width: 100%;
            max-width: 370px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .register-container h2 {
            text-align: center;
            color: #6c4f6b;
            margin-bottom: 0.7rem; /* reduced margin */
            font-size: 1.7rem; /* slightly smaller */
            font-weight: 700;
            letter-spacing: 1px;
        }
        .register-container form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.6rem; /* reduced gap */
        }
        .register-container label {
            font-weight: 500;
            color: #6c4f6b;
            margin-bottom: 0.1rem; /* reduced margin */
            font-size: 0.97rem;
        }
        .register-container input[type="text"],
        .register-container input[type="email"],
        .register-container input[type="password"] {
            padding: 0.5rem; /* reduced padding */
            border: 1.2px solid #c89bce;
            border-radius: 0.5rem;
            font-size: 0.97rem;
            outline: none;
            background: rgba(255,255,255,0.7);
            transition: border 0.2s;
            margin-bottom: 0.1rem; /* reduced margin */
        }
        .register-container input:focus {
            border-color: #917489;
        }
        .register-container .btn {
            background: linear-gradient(90deg, #917489 0%, #b497bd 100%);
            color: #fff;
            border: none;
            padding: 0.6rem; /* reduced padding */
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.3rem; /* reduced margin */
            transition: background 0.2s;
            width: 100%;
        }
        .register-container .btn + .btn {
            margin-top: 0.3rem !important; /* ensure login button is close to register */
        }
        .register-container .btn:hover {
            background: linear-gradient(90deg, #b497bd 0%, #917489 100%);
        }
        .register-container .error, .register-container .success {
            padding: 0.5rem 0.7rem; /* reduced padding */
            border-radius: 0.5rem;
            margin-bottom: 0.4rem; /* reduced margin */
            font-size: 0.95rem;
        }
        .success, .error {
            opacity: 0;
            transition: opacity 0.5s;
        }
        .success.visible, .error.visible {
            opacity: 1;
        }
        #client-error {
            color: #b1003a;
            background: none;
            border: none;
            padding: 0;
            margin: 0.2rem 0 0.4rem 0; /* reduced margin */
            font-size: 0.95rem;
            min-height: 1em;
            box-shadow: none;
            display: block;
            text-align: center;
        }
        .signin-link {
            margin-top: 0.5rem;
            text-align: center;
            font-size: 1rem;
        }
        .signin-link a {
            color: #1976d2;
            text-decoration: underline;
            cursor: pointer;
            font-weight: 500;
            transition: color 0.2s;
        }
        .signin-link a:hover {
            color: #0d47a1;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 0.7rem 0.2rem;
                max-width: 98vw;
            }
            .register-container h2 {
                font-size: 1.2rem;
            }
        }
    </style>
    <script>
document.addEventListener("DOMContentLoaded", function() {
    var form = document.getElementById("regForm");
    var clientError = document.getElementById("client-error");
    var successDiv = document.getElementById("success-message");
    var errorDiv = document.getElementById("server-error");

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        // Client-side validation
        var password = form.password.value;
        var confirm = form.confirm_password.value;
        clientError.innerText = "";
        if (password.length < 8) {
            clientError.innerText = "Password must be at least 8 characters.";
            return;
        }
        if (password !== confirm) {
            clientError.innerText = "Passwords do not match.";
            return;
        }

        // Prepare form data
        var formData = new FormData(form);

        // AJAX request
        fetch("register_action.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Clear previous messages
            if (errorDiv) errorDiv.remove();
            if (successDiv) successDiv.remove();

            if (data.errors && data.errors.length > 0) {
                var err = document.createElement("div");
                err.className = "error visible";
                err.id = "server-error";
                err.innerHTML = data.errors.join("<br>");
                form.parentNode.insertBefore(err, form);
            } else if (data.success) {
                var succ = document.createElement("div");
                succ.className = "success visible";
                succ.id = "success-message";
                succ.innerText = data.success;
                form.parentNode.insertBefore(succ, form);
                setTimeout(function() {
                    succ.classList.remove("visible");
                    setTimeout(function() { succ.style.display = "none"; }, 500);
                }, 4000);
                form.reset();
            }
        });
    });

    // Add click event for sign in link
    var signinLink = document.getElementById("signin-link");
    if (signinLink) {
        signinLink.addEventListener("click", function(e) {
            e.preventDefault();
            window.location.href = "loginpage.html";
        });
    }
});
    </script>
</head>
<body>
    <video class="bg-video" src="/IM2-Scentora/files/videos/bg.mp4" autoplay loop muted playsinline></video>
    <div class="bg-overlay"></div>
    <div class="register-container">
        <h2>User Registration</h2>
        <?php if (!empty($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
    </div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="success" id="success-message"><?php echo $success; ?></div>
<?php endif; ?>
        <form id="regForm" autocomplete="off">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>

            <label for="address">Address</label>
            <input type="text" name="address" id="address" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required minlength="8">

            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required minlength="8">
            <span id="client-error"></span>

            <button type="submit" class="btn">Register</button>
            <!-- Replace login button with sign in text link -->
            <div class="signin-link">
                Already have an account? <a href="#" id="signin-link">Sign in</a>
            </div>
        </form>
    </div>
</body>
</html>