<?php
session_start();
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = new mysqli("localhost", "root", "", "scentoradb");
    if ($conn->connect_error) {
        $error = "Database connection failed.";
        error_log("DB Connection Error: " . $conn->connect_error);
    } else {
        $email = $conn->real_escape_string($_POST["username"]); 
        $password = $_POST["password"];
        
        // Modified query to check is_active status
        $sql = "SELECT * FROM user WHERE Email=? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // First check if account is active
            if (!$row["is_active"]) {
                $error = "This account has been deactivated. Please contact scentora@gmail.com";
                error_log("Login attempt by deactivated account: " . $email);
            } else {
                // Check password only if account is active
                if (str_starts_with($row["Password"], '$2y$')) {
                    $passwordValid = password_verify($password, $row["Password"]);
                } else {
                    $passwordValid = ($password === $row["Password"]);
                }
                
                if ($passwordValid) {
                    $_SESSION["user_id"] = $row["User_ID"];
                    $_SESSION["user_type"] = $row["User_Type"];
                    
                    $usertype = strtolower(trim($row["User_Type"]));
                    
                    if ($usertype === "consumer") {
                        header("Location: /IM2-Scentora/files/user/shop_user.html");
                        exit;
                    } else if ($usertype === "admin") {
                        header("Location: /IM2-Scentora/files/admin/dashboard.html");
                        exit;
                    } else {
                        $error = "Invalid user type";
                    }
                } else {
                    $error = "Invalid username or password.";
                    error_log("Password match failed for: " . $email);
                }
            }
        } else {
            $error = "Invalid username or password.";
            error_log("No user found with email: " . $email);
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | Scentora</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow: hidden;
    }

    :root {
      --lavender-light: #d2b3d6;
      --white: #ffffff;
      --mauve: #917489;
      --lavender-accent: #b497bd;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: bold;
      color: var(--white);
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    header {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(8px);
      position: fixed;
      top: 0;
      left: 0;
      z-index: 10;
      box-sizing: border-box;
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

    .hero {
      position: relative;
      width: 100vw;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      padding-top: 4rem; /* Added padding to avoid content being hidden under the header */
    }

    .hero video {
      position: absolute;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      object-fit: cover;
      z-index: 0;
    }

    .hero::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
      background: rgba(0, 0, 0, 0.4);
    }

    .login-container {
      position: relative;
      z-index: 2;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
      max-width: 400px;
      width: 90%;
      text-align: center;
      color: var(--white);
      animation: fadeIn 1.2s ease-in-out;
    }

    .login-container h2 {
      margin-bottom: 1.5rem;
      font-size: 2rem;
    }

    .login-container input {
      width: 100%;
      padding: 0.8rem 1rem;
      margin-bottom: 1rem;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      outline: none;
    }

    .login-container button {
      width: 100%;
      padding: 0.8rem;
      background-color: var(--lavender-accent);
      color: var(--white);
      border: none;
      border-radius: 50px;
      font-size: 1.1rem;
      cursor: pointer;
      transition: background 0.3s;
    }

    .login-container button:hover {
      background-color: var(--mauve);
      color: var(--white);
    }

    .forgot-link {
      display: inline-block;
      margin-top: 0.7rem;
      color: #1976d2;
      text-decoration: underline;
      font-size: 0.95rem;
      cursor: pointer;
      background: none;
      border: none;
      padding: 0;
      transition: color 0.2s;
      float: right;
    }

    .forgot-link:hover {
      color: #0d47a1;
      text-decoration: underline;
    }

    #error-message {
      color: red;
      margin-top: 10px;
      font-size: 0.95rem;
    }

    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 20;
    }

    .modal-content {
      background: #fff;
      padding: 2.5rem;
      border-radius: 15px;
      width: 90%;
      max-width: 400px;
      text-align: center;
      color: #333;
      position: relative;
      animation: fadeIn 0.4s ease;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .modal-content h3 {
      margin-bottom: 1rem;
      color: var(--mauve);
      font-size: 1.5rem;
    }

    .modal-content p {
      margin-bottom: 1.5rem;
      color: #666;
      font-size: 0.95rem;
    }

    .modal-content input[type="email"] {
      width: 100%;
      padding: 1rem;
      margin-bottom: 1rem;
      border: 2px solid rgba(145, 116, 137, 0.15);
      border-radius: 10px;
      font-size: 1rem;
      transition: border-color 0.3s ease;
      outline: none;
    }

    .modal-content input[type="email"]:focus {
      border-color: var(--lavender-accent);
    }

    .modal-content .email-btn {
      width: 100%;
      padding: 1rem;
      background-color: var(--lavender-accent);
      border: none;
      border-radius: 10px;
      color: white;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
      margin-top: 0.5rem;
    }

    .modal-content .email-btn:hover {
      background-color: var(--mauve);
      transform: translateY(-2px);
    }

    .modal-content .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 1.5rem;
      color: #666;
      background: none;
      border: none;
      cursor: pointer;
    }

    .signup-link {
      display: inline-block;
      margin-top: 1rem;
      color: var(--white);
      text-decoration: none;
      font-size: 0.95rem;
      transition: color 0.2s;
      float: left;
    }

    .signup-link:hover {
      color: var(--lavender-light);
      text-decoration: underline;
    }

    .signup-btn {
      display: block;
      width: 100%;
      padding: 0.8rem;
      background-color: rgba(255, 255, 255, 0.85);
      color: var(--mauve);
      border: none;
      border-radius: 50px;
      font-size: 1.1rem;
      text-decoration: none;
      text-align: center;
      cursor: pointer;
      transition: background 0.3s;
      margin-top: 0.8rem;
    }

    .signup-btn:hover {
      background-color: var(--lavender-accent);
      color: var(--white);
    }

    .password-container {
      position: relative;
      width: 100%;
      margin-bottom: 1rem;
    }

    .toggle-password {
      position: absolute;
      right: 8px;  /* Adjusted from 12px */
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

    /* Add styles for the password input to accommodate the icon */
    .password-container input {
      width: 100%;
      padding: 0.8rem 1rem;
      padding-right: 50px; /* Increased right padding to prevent text overlap with icon */
      margin-bottom: 0;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      outline: none;
    }

    /* Update the SVG sizing and positioning */
    .toggle-password svg {
      width: 32px;      /* Increased from 28px */
      height: 32px;     /* Increased from 28px */
      stroke-width: 2px; /* Slightly thicker for better visibility */
      position: relative;
      left: -1px;       /* Fine-tune horizontal position */
      top: 0px;         /* Fine-tune vertical position */
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    @media (max-width: 500px) {
      .login-container h2 { font-size: 1.5rem; }
      .login-container { padding: 1.5rem; }
    }
  </style>
</head>
<body>

  <header>
    <div class="logo">Scentora</div>
    <nav class="nav-links">
      <a href="/IM2-Scentora/index.html">Home</a>
      <a href="/IM2-Scentora/files/user/shop_user.html">Shop</a>
      <a href="/IM2-Scentora/files/user/aboutus.html">About Us</a>
      <a href="/IM2-Scentora/files/user/register.php">Sign Up</a>
    </nav>
  </header>

  <section class="hero">
    <video autoplay muted loop playsinline>
      <source src="/IM2-Scentora/files/videos/bg.mp4" type="video/mp4" />
      Your browser does not support the video tag.
    </video>

    <div class="login-container">
      <h2>Login to Scentora</h2>
      <form id="login-form" method="POST" autocomplete="off">
        <input type="text" name="username" placeholder="Email" required />
        <div class="password-container">
          <input type="password" name="password" id="password" placeholder="Password" required />
          <svg class="toggle-password" onclick="togglePassword()" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path d="M12 5c-7.333 0-12 6-12 6s4.667 6 12 6 12-6 12-6-4.667-6-12-6z" />
            <circle cx="12" cy="11" r="3" />
          </svg>
        </div>
        <button type="submit">Login</button>
        <a href="/IM2-Scentora/files/user/register.php" class="signup-btn">Sign Up</a>
        <a href="#" class="forgot-link" onclick="document.getElementById('forgotModal').style.display = 'flex'; return false;">Forgot Password?</a>
        <p id="error-message"><?php echo htmlspecialchars($error); ?></p>
      </form>
    </div>
  </section>

  <!-- Modal -->
  <div class="modal" id="forgotModal">
    <div class="modal-content">
        <button class="close-btn" onclick="document.getElementById('forgotModal').style.display='none'">&times;</button>
        <h3>Reset Password</h3>
        <p>Enter your email address to receive password reset instructions.</p>
        <form id="resetForm" method="POST" action="reset_password.php">
            <input type="email" name="reset_email" placeholder="Enter your email" required>
            <button type="submit" class="email-btn">Send Reset Link</button>
        </form>
        <p id="resetMessage"></p>
    </div>
  </div>

  <script>
    window.addEventListener("click", function (e) {
      const modal = document.getElementById("forgotModal");
      if (e.target === modal) modal.style.display = "none";
    });

    function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.toggle-password');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        // Eye icon crossed (hidden password)
        toggleBtn.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                <line x1="3" y1="3" x2="21" y2="21" />
            </svg>`;
    } else {
        passwordInput.type = 'password';
        // Eye icon (show password)
        toggleBtn.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
            </svg>`;
    }
}

document.getElementById('resetForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch('reset_password.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(res => res.json())
    .then(data => {
        const resetMessage = document.getElementById('resetMessage');
        resetMessage.textContent = data.message;
        resetMessage.style.color = data.success ? '#4caf50' : '#f44336';
        
        if (data.success) {
            document.getElementById('resetForm').reset();
            setTimeout(() => {
                document.getElementById('forgotModal').style.display = 'none';
            }, 3000);
        }
    })
    .catch(err => {
        document.getElementById('resetMessage').textContent = 'An error occurred. Please try again.';
        document.getElementById('resetMessage').style.color = '#f44336';
    });
});

  </script>
</body>
</html>
