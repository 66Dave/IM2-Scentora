<?php
$success = "";
$errors = [];
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        // Enhanced success message with extra info
        $success = '
        <div style="text-align:center; padding:2rem 1rem;">
            <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:1.1rem; color:#222;">Reset Link Sent</h2>
            <p style="font-size:1.08rem; color:#333; margin-bottom:1.2rem;">
                If an account with that email exists, a password reset link has been sent.<br>
                <span style="display:block; margin-top:1.2rem; font-size:1.08rem;">
                    <b>Reset link not received?</b> Contact our administrator at:<br>
                    <span style="font-weight:700; color:#222; font-size:1.08rem;">devnest_admin@gmail.com</span>
                </span>
            </p>
            <a href="mailto:devnest_admin@gmail.com">
                <button style="background:#b497bd; color:#fff; border:none; border-radius:2rem; padding:0.8rem 2.2rem; font-size:1.1rem; font-weight:600; cursor:pointer; margin-top:0.5rem; transition:background 0.2s;">Send Email</button>
            </a>
        </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | Scentora</title>
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
            --shadow: 0 8px 32px 0 rgba(145,116,137,0.10);
            --lavender-border: #b497bd;
        }
        html, body { height: 100%; margin: 0; padding: 0; font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: transparent; overflow: hidden; }
        body { min-height: 100vh; min-width: 100vw; display: flex; flex-direction: column; position: relative; }
        .bg-video { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -2; min-width: 100vw; min-height: 100vh; background: #c89bce; }
        header { width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 1.2rem 2.5rem; background: rgba(255,255,255,0.08); backdrop-filter: blur(8px); position: fixed; top: 0; left: 0; z-index: 10; box-sizing: border-box; }
        .logo { font-size: 1.3rem; font-weight: 600; color: #fff; letter-spacing: 1px; text-shadow: 0 1px 2px rgba(0,0,0,0.18); }
        .nav-links { display: flex; gap: 2rem; }
        .nav-links a { color: #fff; text-decoration: none; font-family: 'Inter', Arial, sans-serif; font-size: 0.95rem; font-weight: 600; transition: color 0.2s; padding: 0.25rem 0.5rem; }
        .nav-links a:hover { color: #b497bd; }
        .container { width: 100vw; height: 100vh; display: flex; justify-content: center; align-items: center; padding-top: 6.5rem; box-sizing: border-box; position: relative; }
        .form-container { background: var(--white); border-radius: 2.5rem; box-shadow: var(--shadow); padding: 3.5rem 3rem 2.5rem 3rem; width: 50vw; max-width: 700px; min-width: 0; min-height: 0; display: flex; flex-direction: column; gap: 1.2rem; position: relative; z-index: 2; border: 4px solid var(--lavender-border); justify-content: center; margin-top: 0; }
        .form-container h2 { text-align: center; color: var(--primary); margin-bottom: 1.2rem; font-size: 2.2rem; font-weight: 700; letter-spacing: 1px; }
        .input-group { width: 100%; margin-bottom: 0; display: flex; flex-direction: column; gap: 0.4rem; }
        .input-group label { font-weight: 600; color: var(--primary); font-size: 1.1rem; }
        .input-group input { width: 100%; padding: 1.2rem 1.2rem; border: 1.5px solid #e0d6e6; border-radius: 0.8rem; font-size: 1.15rem; background: #f8f6fb; transition: border 0.2s; }
        .input-group input:focus { border-color: var(--accent); outline: none; background: #fff; }
        .btn-row { display: flex; gap: 1rem; justify-content: flex-end; }
        .btn { background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%); color: #fff; border: none; padding: 1.1rem 0; border-radius: 0.8rem; font-size: 1.2rem; font-weight: 600; cursor: pointer; transition: background 0.3s, transform 0.2s; margin-top: 0.5rem; width: 100%; max-width: 220px; }
        .btn:hover { background: linear-gradient(90deg, var(--accent) 0%, var(--primary) 100%); transform: translateY(-2px) scale(1.03); }
        .error, .success { padding: 1rem 1.2rem; margin-bottom: 0.7rem; border-radius: 0.7rem; font-size: 1.1rem; text-align: center; }
        .error { background: var(--error); color: #721c24; }
        .success { background: var(--success); color: #155724; }
        .signin-link { text-align: center; margin-top: 1.2rem; font-size: 1.1rem; }
        .signin-link a { color: var(--accent); text-decoration: underline; font-weight: 600; }
        @media (max-width: 900px) { header { padding: 1rem 0.5rem; } .nav-links { gap: 1rem; } .form-container { width: 90vw; max-width: 98vw; } }
        @media (max-width: 600px) { .container { min-height: 100vh; padding-top: 6.5rem; } .form-container { min-height: unset; height: 99vh; width: 99vw; padding: 1rem 0.2rem; } .form-container h2 { font-size: 1.3rem; } }
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
            <h2>Forgot Password</h2>
            <?php if (!empty($errors)): ?>
                <div class="error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <?php echo $success; ?>
                <div style="text-align:center; margin-top:1.5rem;">
                    <form method="post">
                        <button type="submit" class="btn" style="background:#eee; color:#917489; border:none; border-radius:0.8rem; padding:0.8rem 2.2rem; font-size:1.1rem; font-weight:600; cursor:pointer; margin-top:0.5rem; transition:background 0.2s;">
                            &#8592; Back
                        </button>
                    </form>
                </div>
            <?php else: ?>
            <form method="POST" autocomplete="off" style="width:100%;">
                <div class="input-group">
                    <label for="email">Enter your email address</label>
                    <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="btn-row">
                    <button type="submit" class="btn">Send Reset Link</button>
                </div>
            </form>
            <div class="signin-link">
                Remembered your password? <a href="signin.php">Sign in</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>