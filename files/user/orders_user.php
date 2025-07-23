<?php
require_once '../includes/session_config.php';

// Add at the top of send_order_receipt.php
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error.log');

// Require consumer login
requireConsumer();
checkSessionTimeout();

$user_id = getCurrentUserId();

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
            // Update status to Completed and set completion timestamp
            $completion_date = date('Y-m-d H:i:s');
            $update = $conn->prepare("UPDATE `order` SET Status = 'Completed', Arrival_Date = ? WHERE Order_ID = ? AND User_ID = ?");
            $update->bind_param("sii", $completion_date, $oid, $user_id);
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
      border: 2px solid rgba(114, 69, 173, 0.7);
      background: #2a2236;
      color: #a182c9;
    }
    body.darkmode tr {
      background: #232336;
      color: #f7f5fa;
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

    .receipt-btn {
      padding: 4px 10px;
      border-radius: 6px;
      background: #6f58e9;
      color: #fff;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      margin-right: 8px;
    }

    .receipt-btn svg {
      width: 16px;
      height: 16px;
      fill: none;
      stroke: currentColor;
    }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    .spinner {
      width: 16px;
      height: 16px;
      margin-right: 8px;
    }

    /* Rating Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      backdrop-filter: blur(4px);
    }

    .modal-content {
      background-color: var(--card);
      margin: 5% auto;
      padding: 2rem;
      border-radius: 12px;
      width: 90%;
      max-width: 600px;
      max-height: 80vh;
      overflow-y: auto;
      position: relative;
    }

    .close {
      position: absolute;
      right: 1rem;
      top: 1rem;
      color: var(--text);
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .close:hover {
      color: var(--primary);
    }

    .product-item {
      display: flex;
      gap: 1rem;
      padding: 1rem;
      border: 1px solid var(--accent);
      border-radius: 8px;
      margin-bottom: 1rem;
      background: var(--background);
    }

    .product-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 6px;
    }

    .product-details {
      flex: 1;
    }

    .rating-stars {
      display: flex;
      gap: 0.2rem;
      margin: 0.5rem 0;
    }

    .star {
      font-size: 1.5rem;
      color: #ddd;
      cursor: pointer;
      transition: color 0.2s;
    }

    .star.active,
    .star:hover {
      color: #ffc107;
    }

    .review-text {
      width: 100%;
      min-height: 60px;
      padding: 0.5rem;
      border: 1px solid var(--accent);
      border-radius: 6px;
      background: var(--card);
      color: var(--text);
      font-family: inherit;
      resize: vertical;
    }

    .submit-ratings {
      background: var(--primary);
      color: var(--white);
      padding: 1rem 2rem;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      width: 100%;
      margin-top: 1rem;
    }

    .submit-ratings:hover {
      background: #8e6bbf;
    }

    .rate-btn {
      background: #ffc107 !important;
      color: #fff !important;
    }

    body.darkmode .modal-content {
      background: var(--card);
      color: var(--text);
    }

    body.darkmode .product-item {
      background: var(--accent);
      border-color: var(--primary);
    }

    body.darkmode .review-text {
      background: var(--sidebar);
      border-color: var(--primary);
      color: var(--text);
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">Scentora</div>
    <nav class="nav-links">
      <a href="shop_user.php">Shop</a>
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
          // First, get all orders
          $sql = "SELECT o.Order_ID, o.Order_Date, o.Total_Amount, o.Status, o.Arrival_Date 
                  FROM `order` o 
                  WHERE o.User_ID = ? 
                  ORDER BY o.Order_Date DESC";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("i", $user_id);
          $stmt->execute();
          $result = $stmt->get_result();
          
          $orders = [];
          while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
          }
          $stmt->close();
          
          // Get all review counts for completed orders
          $review_counts = [];
          if (!empty($orders)) {
            $completed_order_ids = [];
            foreach ($orders as $order) {
              if (strtolower($order['Status']) === 'completed') {
                $completed_order_ids[] = $order['Order_ID'];
              }
            }
            
            if (!empty($completed_order_ids)) {
              $placeholders = str_repeat('?,', count($completed_order_ids) - 1) . '?';
              $review_sql = "SELECT Order_ID, COUNT(*) as review_count FROM product_reviews WHERE Order_ID IN ($placeholders) GROUP BY Order_ID";
              $review_stmt = $conn->prepare($review_sql);
              $review_stmt->bind_param(str_repeat('i', count($completed_order_ids)), ...$completed_order_ids);
              $review_stmt->execute();
              $review_result = $review_stmt->get_result();
              
              while ($review_row = $review_result->fetch_assoc()) {
                $review_counts[$review_row['Order_ID']] = $review_row['review_count'];
              }
              $review_stmt->close();
            }
          }
          
          $found = false;
          foreach ($orders as $order) {
            $oid = $order['Order_ID'];
            $odate = $order['Order_Date'];
            $total = $order['Total_Amount'];
            $status = $order['Status'];
            $arrival_date = $order['Arrival_Date'];
            $existing_reviews = isset($review_counts[$oid]) ? $review_counts[$oid] : 0;
            
            $found = true;
            echo "<tr>";
            echo "<td>" . htmlspecialchars($oid) . "</td>";
            echo "<td>" . htmlspecialchars($odate) . "</td>";
            echo "<td>₱" . number_format($total, 2) . "</td>";
            echo "<td>" . htmlspecialchars($status) . "</td>";
            echo "<td>";
            
            // Add Receipt button for Accepted/Completed orders
            if (in_array(strtolower($status), ["accepted", "completed"])) {
                echo "<button onclick='sendReceipt({$oid})' class='receipt-btn' 
                      style='padding:4px 10px;border-radius:6px;background:#6f58e9;color:#fff;
                      border:none;cursor:pointer;display:inline-flex;align-items:center;gap:5px;
                      margin-right:8px;'>
                      <svg width='16' height='16' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path d='M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7m-16 0l5-5h6l5 5M3 7l5-5M21 7l-5-5'></path>
                      </svg>
                      Receipt
                    </button>";
            }

            // Add Rate Products button for Completed orders within 7 days
            if (strtolower($status) === "completed") {
                if ($existing_reviews == 0) {
                    // Use arrival_date if set, otherwise use order_date
                    $reference_date = $arrival_date ? $arrival_date : $odate;
                    $reference_timestamp = strtotime($reference_date);
                    $current_timestamp = time();
                    $days_since_completion = floor(($current_timestamp - $reference_timestamp) / (60 * 60 * 24));
                    
                    // Show review button for completed orders within 7 days
                    if ($days_since_completion <= 7) {
                        echo "<button onclick='showRateProductsModal({$oid})' class='rate-btn' 
                              style='padding:4px 10px;border-radius:6px;background:#ffc107;color:#fff;
                              border:none;cursor:pointer;display:inline-flex;align-items:center;gap:5px;
                              margin-right:8px;'>
                              <svg width='16' height='16' fill='currentColor' viewBox='0 0 24 24'>
                                <path d='M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z'/>
                              </svg>
                              Rate Products
                            </button>";
                    } else {
                        // Show that review period has expired
                        echo "<small style='color:#888; font-size:10px;'>(Review period expired)</small>";
                    }
                } else {
                    // Show that user has already reviewed
                    echo "<small style='color:#4caf50; font-size:10px;'>✓ Reviewed</small>";
                }
            }

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
          $conn->close();
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- Rating Modal -->
  <div id="ratingModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeRatingModal()">&times;</span>
      <h2>Rate Your Products</h2>
      <div id="rateProductsList">
        <!-- Products will be loaded here -->
      </div>
      <button class="submit-ratings" onclick="submitAllRatings()">Submit All Ratings</button>
    </div>
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

    // Send receipt function
function sendReceipt(orderId) {
    const formData = new FormData();
    formData.append('order_id', orderId);

    // Show loading state
    const btn = event.target.closest('.receipt-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `
        <svg class="spinner" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="4"></circle>
        </svg>
        Sending...
    `;

    fetch('send_order_receipt.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text().then(text => {
            console.log('Raw response:', text); // Debug line
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse:', text);
                throw new Error('Server response was not valid JSON');
            }
        });
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
        } else {
            throw new Error(data.message || 'Failed to send receipt');
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        alert('Error sending receipt: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

    // Rating functionality
    let currentOrderId = null;
    let productRatings = {};

    function showRateProductsModal(orderId) {
      currentOrderId = orderId;
      productRatings = {};
      
      // Show modal
      document.getElementById('ratingModal').style.display = 'block';
      
      // Show loading message
      document.getElementById('rateProductsList').innerHTML = '<p>Loading products...</p>';
      
      // Load products for this order
      fetch('get_reviewable_orders.php')
        .then(response => {
          console.log('Response status:', response.status);
          return response.text();
        })
        .then(text => {
          console.log('Raw response:', text);
          try {
            const data = JSON.parse(text);
            console.log('Parsed data:', data);
            if (data.success) {
              const orderProducts = data.reviewable_items.filter(item => item.Order_ID == orderId);
              console.log('Filtered products for order', orderId, ':', orderProducts);
              if (orderProducts.length > 0) {
                renderRateProductsList(orderProducts);
              } else {
                document.getElementById('rateProductsList').innerHTML = '<p>No products found to rate for this order. The review period may have expired or you may have already reviewed all products.</p>';
              }
            } else {
              document.getElementById('rateProductsList').innerHTML = '<p>Error: ' + (data.error || 'Unknown error') + '</p>';
            }
          } catch (e) {
            console.error('JSON parse error:', e);
            document.getElementById('rateProductsList').innerHTML = '<p>Error parsing server response. Response was: ' + text.substring(0, 200) + '...</p>';
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
          document.getElementById('rateProductsList').innerHTML = '<p>Error loading products: ' + error.message + '</p>';
        });
    }

    function renderRateProductsList(products) {
      console.log('Rendering products:', products);
      const container = document.getElementById('rateProductsList');
      container.innerHTML = '';
      
      if (!products || products.length === 0) {
        container.innerHTML = '<p>No products available for rating.</p>';
        return;
      }
      
      products.forEach(product => {
        console.log('Rendering product:', product);
        const productDiv = document.createElement('div');
        productDiv.className = 'product-item';
        
        const imageSrc = product.Image_URL || '../images/placeholder.jpg';
        const productName = product.Product_Name || 'Unknown Product';
        const brand = product.Brand || 'Scentora';
        const price = parseFloat(product.Product_Price || 0).toFixed(2);
        
        productDiv.innerHTML = `
          <img src="${imageSrc}" alt="${productName}" class="product-image" onerror="this.src='../images/placeholder.jpg'">
          <div class="product-details">
            <h4>${productName}</h4>
            <p><strong>Brand:</strong> ${brand}</p>
            <p><strong>Price:</strong> ₱${price}</p>
            <div class="rating-stars" data-product-id="${product.Product_ID}">
              ${[1,2,3,4,5].map(i => `<span class="star" data-rating="${i}">★</span>`).join('')}
            </div>
            <textarea class="review-text" placeholder="Write your review (optional)..." 
                      data-product-id="${product.Product_ID}"></textarea>
          </div>
        `;
        container.appendChild(productDiv);
      });

      // Add click handlers for stars
      document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('click', function() {
          const productId = this.parentElement.dataset.productId;
          const rating = parseInt(this.dataset.rating);
          console.log('Star clicked:', productId, rating);
          setRating(productId, rating);
        });

        star.addEventListener('mouseenter', function() {
          const rating = parseInt(this.dataset.rating);
          const stars = this.parentElement.querySelectorAll('.star');
          stars.forEach((s, index) => {
            s.classList.toggle('active', index < rating);
          });
        });
      });

      // Reset stars on mouse leave
      document.querySelectorAll('.rating-stars').forEach(container => {
        container.addEventListener('mouseleave', function() {
          const productId = this.dataset.productId;
          const savedRating = productRatings[productId]?.rating || 0;
          const stars = this.querySelectorAll('.star');
          stars.forEach((s, index) => {
            s.classList.toggle('active', index < savedRating);
          });
        });
      });
      
      console.log('Products rendered successfully');
    }

    function setRating(productId, rating) {
      if (!productRatings[productId]) {
        productRatings[productId] = {};
      }
      productRatings[productId].rating = rating;
      
      // Update visual stars
      const stars = document.querySelector(`[data-product-id="${productId}"]`).querySelectorAll('.star');
      stars.forEach((star, index) => {
        star.classList.toggle('active', index < rating);
      });
    }

    function submitAllRatings() {
      // Collect all ratings and reviews
      const reviews = [];
      
      Object.keys(productRatings).forEach(productId => {
        const rating = productRatings[productId].rating;
        const reviewTextArea = document.querySelector(`textarea[data-product-id="${productId}"]`);
        const reviewText = reviewTextArea ? reviewTextArea.value.trim() : '';
        
        if (rating && rating > 0) {
          reviews.push({
            order_id: currentOrderId,
            product_id: productId,
            rating: rating,
            review_text: reviewText
          });
        }
      });

      if (reviews.length === 0) {
        alert('Please rate at least one product before submitting.');
        return;
      }

      // Submit reviews
      const submitBtn = document.querySelector('.submit-ratings');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting...';

      fetch('submit_review.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ reviews: reviews })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Thank you for your reviews!');
          closeRatingModal();
          location.reload(); // Refresh page to update buttons
        } else {
          alert('Error submitting reviews: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error submitting reviews. Please try again.');
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit All Ratings';
      });
    }

    function closeRatingModal() {
      document.getElementById('ratingModal').style.display = 'none';
      currentOrderId = null;
      productRatings = {};
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
      const modal = document.getElementById('ratingModal');
      if (event.target === modal) {
        closeRatingModal();
      }
    });
  </script>
</body>
</html>