<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /IM2-Scentora/files/admin/loginpage.php");
    exit();
}

$host = "localhost";
$user_db = "root";
$pass_db = "";
$dbname = "scentoradb";

$conn = new mysqli($host, $user_db, $pass_db, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Handle address update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address'])) {
    $new_address = $_POST['address'];
    $update_sql = "UPDATE user SET Address = ? WHERE User_ID = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_address, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Handle account details update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullname']) && isset($_POST['email'])) {
    $new_name = $_POST['fullname'];
    $new_email = $_POST['email'];
    $update_sql = "UPDATE user SET Name = ?, Email = ? WHERE User_ID = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $new_name, $new_email, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "../uploads/profile/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
        $update_sql = "UPDATE user SET Profile_Image = ? WHERE User_ID = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $target_file, $user_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
}

// Fetch user info
$sql = "SELECT Name, Email, Address, Profile_Image FROM user WHERE User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fullname, $email, $address, $profile_image);
$stmt->fetch();
$stmt->close();
$conn->close();

$profile_pic = !empty($profile_image) ? $profile_image : "https://ui-avatars.com/api/?name=" . urlencode($fullname) . "&background=a182c9&color=fff&size=256";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Scentora | User Profile</title>
  <style>
    :root {
      --primary: #a182c9;
      --accent: #e5d6f7;
      --background: #f7f5fa;
      --card: #fff7ff;
      --sidebar: #efe2fa;
      --text: #392e44;
      --nav-bg: rgba(114, 69, 173, 0.7);
      --nav-blur: blur(10px);
      --white: #fff;
      --shadow: 0 2px 6px rgba(161,130,201,0.12);
    }
    body {
      font-family: 'Segoe UI', sans-serif;
      background: var(--background);
      color: var(--text);
      margin: 0;
      padding-top: 74px;
      transition: background 0.5s ease, color 0.5s ease;
    }
    body.darkmode {
      --primary: #b89fff;
      --accent: #28294b;
      --background: #191922;
      --card: #232336;
      --sidebar: #232336;
      --text: #f7f5fa;
      --nav-bg: rgba(32, 31, 50, 0.92);
      --white: #e9e9ff;
      --shadow: 0 2px 16px rgba(17, 17, 22, 0.12);
    }
    header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: var(--nav-bg);
      backdrop-filter: var(--nav-blur);
      color: var(--white);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
    }
    body.darkmode header {
      background: var(--nav-bg);
      color: #fff;
    }
    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      letter-spacing: 1px;
    }
    .search-bar {
      flex: 1;
      margin-left: 2rem;
      margin-right: 2.5rem;
    }
    .search-bar input {
      width: 100%;
      padding: 0.5rem 1rem;
      border-radius: 999px;
      border: 2px solid var(--primary);
      background: var(--card);
      color: var(--text);
      font-size: 1rem;
      box-shadow: var(--shadow);
      outline: none;
    }
    .header_contents {
      display: flex;
      gap: 1.5rem;
    }
    .nav-links {
      display: flex;
      gap: 1rem;
    }
    .nav-links a {
      text-decoration: none;
      color: var(--white);
      font-weight: 500;
      font-size: 1rem;
      padding: 0.4rem 1rem;
      border-radius: 999px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.3rem;
    }
    body.darkmode .nav-links a {
      color: #fff;
    }
    .nav-links a:not([href="#logout"]).active,
    .nav-links a.active {
      background: var(--white);
      color: var(--primary);
      font-weight: 600;
      box-shadow: 0 0 8px rgba(255, 255, 255, 0.2);
    }
    body.darkmode .nav-links a.active {
      background: #fff;
      color: #a182c9;
    }
    .nav-links a:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }
    .toggle-switch {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .toggle-switch label {
      font-size: 0.96rem;
      color: var(--white);
    }

    .toggle-switch input[type="checkbox"] {
      width: 40px;
      height: 20px;
      appearance: none;
      background: #bda6e7;
      outline: none;
      border-radius: 15px;
      position: relative;
      transition: background 0.3s;
      cursor: pointer;
    }

    .toggle-switch input[type="checkbox"]:checked {
      background: #392e44;
    }

    .toggle-switch input[type="checkbox"]::before {
      content: "";
      position: absolute;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      top: 1px;
      left: 2px;
      background: #fff;
      transition: 0.3s;
    }

    .toggle-switch input[type="checkbox"]:checked::before {
      transform: translateX(19px);
      background: #a182c9;
    }
    .profile-container {
      max-width: 600px;
      margin: 2.5rem auto 2rem auto;
      background: var(--card);
      border-radius: 16px;
      box-shadow: var(--shadow);
      padding: 2.5rem 2rem;
      display: flex;
      gap: 2.5rem;
      align-items: flex-start;
    }
    body.darkmode .profile-container {
      background: #392e44;
    }
    .profile-pic {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--primary);
      background: var(--sidebar);
      margin-bottom: 1rem;
      transition: box-shadow 0.2s, border-color 0.2s;
      cursor: default;
    }
    .profile-pic.editable {
      box-shadow: 0 0 0 4px var(--primary), 0 2px 8px rgba(161,130,201,0.15);
      border-color: #8e6bbf;
      cursor: pointer;
    }
    body.darkmode .profile-pic {
      border-color: #fff;
      background: #392e44;
    }
    .profile-sidebar {
      min-width: 140px;
      text-align: center;
    }
    .profile-sidebar h2 {
      font-size: 1.2rem;
      color: var(--primary);
      margin: 0.5rem 0 0.2rem 0;
    }
    .profile-main {
      flex: 1;
    }
    .profile-section {
      margin-bottom: 2rem;
      background: var(--sidebar);
      border-radius: 10px;
      padding: 1.5rem 1.5rem 1rem 1.5rem;
      box-shadow: var(--shadow);
    }
    body.darkmode .profile-section,
    body.darkmode .profile-container,
    body.darkmode .profile-sidebar {
      background: var(--card);
      color: var(--text);
    }
    .profile-section h3 {
      color: var(--primary);
      margin-top: 0;
      margin-bottom: 1rem;
      font-size: 1.15rem;
    }
    .profile-details label {
      font-weight: 500;
      color: var(--primary);
      display: block;
      margin-bottom: 0.2rem;
    }
    .profile-details input, .profile-details textarea {
      width: 100%;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      border: 1px solid var(--accent);
      background: var(--card);
      color: var(--text);
      font-size: 1rem;
      margin-bottom: 1rem;
      box-sizing: border-box;
    }
    body.darkmode .profile-details input,
    body.darkmode .profile-details textarea {
      background: #2a2236;
      color: #f7f5fa;
      border: 1px solid #a182c9;
    }
    .profile-details input[readonly], .profile-details textarea[readonly] {
      background: #f2eafd;
      color: #888;
      cursor: not-allowed;
    }
    body.darkmode .profile-details input[readonly],
    body.darkmode .profile-details textarea[readonly] {
      background: #392e44;
      color: #aaa;
    }
    .profile-details .edit-btn {
      background: var(--primary);
      color: var(--white);
      border: none;
      border-radius: 6px;
      padding: 0.5rem 1.2rem;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 0.5rem;
      transition: background 0.3s;
    }
    .profile-details .edit-btn:hover {
      background: #8e6bbf;
    }
    /* Modal color fix */
    #ordersModal {
      background: rgba(0,0,0,0.35);
    }
    #ordersModal .modal-content {
      background: var(--card);
      color: var(--text);
    }
    body.darkmode #ordersModal .modal-content {
      background: #392e44;
      color: #f7f5fa;
    }
    @media (max-width: 700px) {
      .profile-container {
        flex-direction: column;
        align-items: stretch;
        padding: 1.5rem 0.5rem;
      }
      .profile-sidebar {
        margin-bottom: 1.5rem;
      }
      .search-bar {
        margin: 1rem 0;
      }
      .header_contents {
        flex-direction: column;
        gap: 0.5rem;
      }
    }
  </style>
</head>
<body>
  <header>
  <div class="logo">Scentora</div>
  <nav class="nav-links">
    <a href="shop_user.html">Shop</a>
    <a href="orders_user.php">Orders</a>
    <a href="userCart.html" title="Cart">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;">
        <circle cx="9" cy="21" r="1"></circle>
        <circle cx="20" cy="21" r="1"></circle>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
      </svg>
    </a>
    <a href="user_profile.php" class="active" title="Profile">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;">
        <circle cx="12" cy="8" r="4"></circle>
        <path d="M4 20c0-4 4-6 8-6s8 2 8 6"></path>
      </svg>
    </a>
    <a href="#logout" id="logout-link">Logout</a>
  </nav>
  <div class="toggle-switch">
    <label for="darkmode">Dark mode</label>
    <input type="checkbox" id="darkmode" title="Toggle dark mode" />
  </div>
</header>
  <main>
    <div class="profile-container">
      <div class="profile-sidebar">
        <form id="uploadForm" enctype="multipart/form-data" method="post" style="margin-bottom:1rem;">
          <input type="file" name="profile_image" id="profile_image" accept="image/*" style="display:none;" onchange="document.getElementById('uploadForm').submit()">
          <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-pic" id="profilePic" title="Click to change image" />
        </form>
        <div style="font-size:1.1rem;font-weight:600;color:var(--primary);margin-bottom:0.2rem;"><?php echo htmlspecialchars($fullname); ?></div>
        <div style="font-size:0.95rem;color:var(--text);margin-bottom:1rem;" id="profileEmail"><?php echo htmlspecialchars($email); ?></div>
      </div>
      <div class="profile-main">
        <section class="profile-section">
          <h3>Account Details & Shipping</h3>
          <form class="profile-details" id="detailsForm" autocomplete="off" method="post" enctype="multipart/form-data">
            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" readonly>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
            <label for="address">Shipping Address</label>
            <textarea id="address" name="address" rows="2" readonly><?php echo htmlspecialchars($address); ?></textarea>
            <div style="display:flex;gap:0.5rem;">
              <button type="button" class="edit-btn" id="editBtn" onclick="enableEditProfile()">Edit Profile</button>
              <button type="submit" class="edit-btn" id="saveBtn" style="display:none;">Save</button>
              <button type="button" class="edit-btn" id="cancelBtn" style="display:none;" onclick="cancelEditProfile()">Cancel</button>
            </div>
          </form>
        </section>
      </div>
    </div>
  </main>
  <!-- Orders Modal -->
<div id="ordersModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:2000; align-items:center; justify-content:center;">
  <div class="modal-content" style="max-width:600px; width:90%; margin:auto; border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,0.18); padding:2rem; position:relative;">
    <button onclick="closeOrdersModal()" style="position:absolute; top:12px; right:16px; background:none; border:none; font-size:1.5rem; color:#a182c9; cursor:pointer;">&times;</button>
    <h2 style="color:#a182c9; margin-top:0;">My Orders</h2>
    <div id="ordersContent">Loading...</div>
  </div>
</div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
  // Dark mode
  const darkToggle = document.getElementById('darkmode');
  if (localStorage.getItem('scentoraDark') === '1') {
    document.body.classList.add('darkmode');
    darkToggle.checked = true;
  } else {
    document.body.classList.remove('darkmode');
    darkToggle.checked = false;
  }
  darkToggle.addEventListener('change', () => {
    document.body.classList.toggle('darkmode', darkToggle.checked);
    localStorage.setItem('scentoraDark', darkToggle.checked ? '1' : '0');
  });

  // Logout confirmation
  document.getElementById("logout-link").onclick = function(e) {
    e.preventDefault();
    if (confirm("Are you sure you want to logout?")) {
      window.location.href = "/IM2-Scentora/files/admin/loginpage.php";
    }
  };
});

let editingProfile = false;

// Enable editing for all profile fields and image
function enableEditProfile() {
  document.getElementById('fullname').readOnly = false;
  document.getElementById('email').readOnly = false;
  document.getElementById('address').readOnly = false;
  document.getElementById('saveBtn').style.display = 'inline-block';
  document.getElementById('cancelBtn').style.display = 'inline-block';
  document.getElementById('editBtn').style.display = 'none';
  editingProfile = true;
  document.getElementById('profilePic').classList.add('editable');
}

// Cancel editing for all profile fields and image
function cancelEditProfile() {
  document.getElementById('fullname').readOnly = true;
  document.getElementById('email').readOnly = true;
  document.getElementById('address').readOnly = true;
  document.getElementById('saveBtn').style.display = 'none';
  document.getElementById('cancelBtn').style.display = 'none';
  document.getElementById('editBtn').style.display = 'inline-block';
  editingProfile = false;
  document.getElementById('profilePic').classList.remove('editable');
}

// Make profile image clickable only when editing
document.getElementById('profilePic').addEventListener('click', function() {
  if (editingProfile) {
    document.getElementById('profile_image').click();
  }
});

// Show orders modal
function showOrdersModal() {
  document.getElementById('ordersModal').style.display = 'flex';
  document.getElementById('ordersContent').innerHTML = 'Loading...';
  fetch('fetch_user_orders.php')
    .then(response => response.text())
    .then(html => {
      document.getElementById('ordersContent').innerHTML = html;
    })
    .catch(() => {
      document.getElementById('ordersContent').innerHTML = 'Failed to load orders.';
    });
}

// Close orders modal
function closeOrdersModal() {
  document.getElementById('ordersModal').style.display = 'none';
}
  </script>
</body>
</html>
