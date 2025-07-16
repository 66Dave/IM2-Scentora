<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /IM2-Scentora/files/admin/loginpage.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Handle "Order Received" and "Cancel Order" buttons
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $host = "localhost";
    $user_db = "root";
    $pass_db = "";
    $dbname = "scentoradb";
    $oid = intval($_POST['order_id']);
    $conn = new mysqli($host, $user_db, $pass_db, $dbname);
    if (!$conn->connect_error) {
        // Get current status
        $check = $conn->prepare("SELECT Status FROM `order` WHERE Order_ID = ? AND User_ID = ?");
        $check->bind_param("ii", $oid, $user_id);
        $check->execute();
        $check->bind_result($current_status);
        $check->fetch();
        $check->close();

        if (isset($_POST['mark_received']) && strtolower($current_status) === "accepted") {
            $update = $conn->prepare("UPDATE `order` SET Status = 'Completed' WHERE Order_ID = ? AND User_ID = ?");
            $update->bind_param("ii", $oid, $user_id);
            $update->execute();
            $update->close();
        }
        if (isset($_POST['cancel_order']) && strtolower($current_status) === "pending") {
            $update = $conn->prepare("UPDATE `order` SET Status = 'Cancelled' WHERE Order_ID = ? AND User_ID = ?");
            $update->bind_param("ii", $oid, $user_id);
            $update->execute();
            $update->close();
        }
        $conn->close();
    }
    header("Location: orders_user.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Scentora | My Orders</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Copy your navbar and table styles from shop_user.html here */
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
    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      letter-spacing: 1px;
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
    }
    .nav-links a.active {
      background: var(--white);
      color: var(--primary);
      font-weight: 600;
      box-shadow: 0 0 8px rgba(255, 255, 255, 0.2);
    }
    .nav-links a:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }
    .orders-container {
      max-width: 900px;
      margin: 100px auto 2rem auto;
      background: var(--card);
      border-radius: 16px;
      box-shadow: var(--shadow);
      padding: 2.5rem 2rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1.5rem;
    }
    th, td {
      padding: 12px 10px;
      text-align: left;
    }
    th {
      background: var(--sidebar);
      color: var(--primary);
      font-weight: 600;
    }
    tr {
      background: var(--card);
      color: var(--text);
      border-bottom: 1px solid #eee;
    }
    tr:last-child {
      border-bottom: none;
    }
    @media (max-width: 700px) {
      .orders-container {
        padding: 1.5rem 0.5rem;
      }
      table, th, td {
        font-size: 0.95rem;
      }
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
    body.darkmode th {
      background: #2a2236;
      color: #a182c9;
    }
    body.darkmode tr {
      background: #232336;
      color: #f7f5fa;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">Scentora</div>
    <nav class="nav-links">
      <a href="shop_user.html">Shop</a>
      <a href="orders_user.php" class="active">Orders</a>
      <a href="userCart.html" title="Cart">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
      </a>
      <a href="user_profile.php" title="Profile">
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
  <div class="orders-container">
    <h2>My Orders</h2>
    <table>
      <thead>
        <tr>
          <th>Order #</th>
          <th>Date</th>
          <th>Total</th>
          <th>Status</th>
          <th>Transac</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $host = "localhost";
        $user_db = "root";
        $pass_db = "";
        $dbname = "scentoradb";
        $conn = new mysqli($host, $user_db, $pass_db, $dbname);
        if ($conn->connect_error) {
          echo "<tr><td colspan='5'>Database error.</td></tr>";
        } else {
          $sql = "SELECT Order_ID, Order_Date, Total_Amount, Status FROM `order` WHERE User_ID = ? ORDER BY Order_Date DESC";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("i", $user_id);
          $stmt->execute();
          $stmt->bind_result($oid, $odate, $total, $status);
          $found = false;
          while ($stmt->fetch()) {
            $found = true;
            echo "<tr>";
            echo "<td>" . htmlspecialchars($oid) . "</td>";
            echo "<td>" . htmlspecialchars($odate) . "</td>";
            echo "<td>₱" . number_format($total, 2) . "</td>";
            echo "<td>" . htmlspecialchars($status) . "</td>";
            echo "<td>";
            if (strtolower($status) === "accepted") {
                // Always show the button for accepted status a
                echo "<form method='post' action='' style='display:inline;'>
                        <input type='hidden' name='order_id' value='".htmlspecialchars($oid)."'>
                        <button type='submit' name='mark_received' style='margin-left:8px;padding:4px 10px;border-radius:6px;background:#a182c9;color:#fff;border:none;cursor:pointer;'>
                            Order Received
                        </button>
                      </form>";
            }
if (strtolower($status) === "pending") {
    echo "<form method='post' action='' style='display:inline;'>
            <input type='hidden' name='order_id' value='".htmlspecialchars($oid)."'>
            <button type='submit' name='cancel_order' style='margin-left:8px;padding:4px 10px;border-radius:6px;background:#e57373;color:#fff;border:none;cursor:pointer;'>
                Cancel Order
            </button>
          </form>";
}
echo "</td>";
            echo "</tr>";
          }
          if (!$found) {
            echo "<tr><td colspan='5' style='text-align:center;'>No orders found.</td></tr>";
          }
          $stmt->close();
          $conn->close();
        }
        ?>
      </tbody>
    </table>
  </div>
  <script>
    // Dark mode toggle
    const darkToggle = document.getElementById('darkmode');
    if (localStorage.getItem('scentoraDark') === '1') {
      document.body.classList.add('darkmode');
      darkToggle.checked = true;
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
  </script>
</body>
</html>