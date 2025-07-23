<?php
require_once '../includes/session_config.php';

// Require consumer login
requireConsumer();

// Check session timeout (2 hours)
checkSessionTimeout(120);

// Add cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products with ratings
$sql = "SELECT 
    p.Product_ID, p.Product_Name, p.Product_Price, p.Available_Stocks, p.Stock_Level,
    p.Category, p.Image_URL, p.Product_Code, p.Brand, p.Description,
    COALESCE(AVG(pr.Rating), 0) as average_rating,
    COUNT(pr.Review_ID) as total_reviews
    FROM product p
    LEFT JOIN product_reviews pr ON p.Product_ID = pr.Product_ID AND pr.Is_Approved = 1
    WHERE p.Is_Active = 1 AND p.Available_Stocks > 0
    GROUP BY p.Product_ID
    ORDER BY p.Product_ID DESC";

$result = $conn->query($sql);
$products = [];

while ($row = $result->fetch_assoc()) {
    $imagePath = !empty($row['Image_URL']) ? '../admin/' . $row['Image_URL'] : '../images/placeholder.jpg';
    $products[] = [
        'Product_ID' => $row['Product_ID'],
        'Product_Name' => $row['Product_Name'],
        'Product_Price' => (float)$row['Product_Price'],
        'Available_Stocks' => (int)$row['Available_Stocks'],
        'Stock_Level' => (int)$row['Stock_Level'],
        'Category' => $row['Category'],
        'Image_URL' => $imagePath,
        'Product_Code' => $row['Product_Code'],
        'Brand' => $row['Brand'] ?? 'Scentora',
        'Description' => $row['Description'] ?? 'No description available',
        'average_rating' => (float)$row['average_rating'],
        'total_reviews' => (int)$row['total_reviews']
    ];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Scentora | Shop</title>
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

    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 2rem;
      background: var(--nav-bg);
      backdrop-filter: var(--nav-blur);
      color: var(--white);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
    }

    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      letter-spacing: 1px;
      margin-right: 2rem;
    }

    .nav-center {
      display: flex;
      gap: 2.5rem;
      align-items: center;
      justify-content: center;
      flex: 1;
    }

    .nav-center a {
      text-decoration: none;
      color: var(--white);
      font-weight: 500;
      font-size: 1rem;
      padding: 0.4rem 1rem;
      border-radius: 999px;
      transition: all 0.3s ease;
    }

    .nav-center a:not([href="#logout"]).active {
      background: var(--white);
      color: var(--primary);
      font-weight: 600;
      box-shadow: 0 0 8px rgba(255, 255, 255, 0.2);
    }

    .nav-center a:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .toggle-switch {
      margin-left: 2rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      justify-content: flex-end;
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

    .shop-products {
      display: flex;
      gap: 2rem;
      padding: 2rem;
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1rem;
      flex: 1;
    }

    .product-card {
      background: var(--card);
      border-radius: 12px;
      box-shadow: var(--shadow);
      padding: 1rem;
      text-align: center;
    }

    .product-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }

    .product-card h3 {
      font-size: 1.2rem;
      color: var(--primary);
    }

    .product-card p {
      font-size: 1rem;
      color: var(--text);
    }

    .product-card .details-btn {
      background: var(--primary);
      color: var(--white);
      padding: 0.6rem 1.2rem;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
    }

    .sidebar {
      width: 200px;
      background: var(--sidebar);
      padding: 1.5rem;
      border-radius: 10px;
      box-shadow: var(--shadow);
    }

    .sidebar h3 {
      font-size: 1.3rem;
      color: var(--primary);
    }

    .sidebar select,
    .sidebar input {
      width: 90%;
      padding: 0.6rem;
      margin-bottom: 1rem;
      border-radius: 8px;
      border: none;
      background: rgba(114, 69, 173, 0.7);
      color: white;
    }

    /* Search bar adjustments */
    .search-bar {
      margin: 0 2rem;
      flex: 0 0 320px;
      display: flex;
      align-items: center;
    }

    .search-bar input {
      width: 100%;
      min-width: 180px;
      padding: 0.5rem 1rem;
      border-radius: 999px;
      border: 2px solid var(--primary);
      background: var(--card);
      color: var(--text);
      font-size: 1rem;
      box-shadow: var(--shadow);
      outline: none;
    }

    /* Dark Mode */
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
    .header_contents {
    display: flex;
    gap: 1.5rem;
    }
    .pricefilter_en_dis{
      display: flex;
      margin-top: 1rem;
      gap: 0.5rem;
    }
    /* New card styles */
    .card {
      background: var(--card);
      border-radius: 12px;
      box-shadow: var(--shadow);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform 0.3s;
    }

    .card:hover {
      transform: translateY(-4px);
    }

    .img {
      position: relative;
      height: 200px;
      overflow: hidden;
    }

    .save {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(255, 255, 255, 0.8);
      border-radius: 50%;
      padding: 0.5rem;
      cursor: pointer;
      transition: background 0.3s;
    }

    .save:hover {
      background: rgba(255, 255, 255, 0.6);
    }

    .text {
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      text-align: left;
    }

    .brand-name {
      font-size: 0.8rem;
      color: var(--primary);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      margin: 0;
      opacity: 0.8;
    }

    .product-name {
      font-size: 1.1rem;
      font-weight: 500;
      color: var(--text);
      margin: 0;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .price-tag {
      font-size: 1.35rem;
      font-weight: 600;
      color: var(--primary);
      margin: 0.5rem 0;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }

    .price-tag::before {
      content: "₱";
      font-size: 0.9rem;
      opacity: 0.8;
    }

    .stock-status {
      font-size: 0.75rem;
      font-weight: 500;
      padding: 0.35rem 0.75rem;
      border-radius: 99px;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      width: fit-content;
      margin-top: 0.25rem;
      letter-spacing: 0.5px;
    }

    .button-group {
      margin-top: 1.25rem;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.75rem;
    }

    .view-btn,
    .cart-btn {
      padding: 0.75rem 1rem;
      border: none;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.2s ease;
    }

    .view-btn {
      background: var(--sidebar);
      color: var(--primary);
      cursor: pointer;
      font-weight: 600;
      transition: all 0.2s ease;
    }

    .view-btn:hover {
      background: var(--accent);
      color: var(--white);
      transform: translateY(-2px);
    }

    .cart-btn {
      background: var(--primary);
      color: var(--white);
    }

    .cart-btn:hover {
      background: #8e6bbf;
    }

    .img img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px 8px 0 0;
      transition: transform 0.3s ease;
    }

    .img img:hover {
      transform: scale(1.05);
    }

    .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    overflow-y: auto;
    padding: 20px;
    backdrop-filter: blur(8px);
}

.modal-content {
    background: var(--card);
    max-width: 1100px;
    margin: 40px auto;
    border-radius: 24px;
    padding: 2.5rem;
    position: relative;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    transform: translateY(20px);
    animation: modalSlideUp 0.3s ease forwards;
}

@keyframes modalSlideUp {
    to {
        transform: translateY(0);
    }
}

.modal-product-details {
    display: grid;
    grid-template-columns: minmax(300px, 45%) 1fr;
    gap: 3rem;
    margin-top: 1rem;
}

.modal-product-image {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.modal-product-image img {
    width: 100%;
    height: 500px;
    object-fit: cover;
    border-radius: 16px;
    transition: transform 0.3s ease;
}

.modal-product-image:hover img {
    transform: scale(1.03);
}

.modal-product-info {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.modal-product-info h2 {
    color: var(--primary);
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}

.modal-brand, .modal-category, .modal-price, .modal-stock, .modal-code {
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text);
}

.modal-brand span, .modal-category span, .modal-code span {
    color: var(--primary);
    font-weight: 500;
}

.modal-price {
    font-size: 2rem;
    font-weight: 600;
    color: var(--primary);
}

.modal-price span::before {
    content: "₱";
    font-size: 1.5rem;
    opacity: 0.8;
}

.modal-description {
    margin-top: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.modal-description h3 {
    font-size: 1.2rem;
    color: var(--text);
    margin-bottom: 1rem;
}

.modal-description p {
    line-height: 1.6;
    color: var(--text);
    opacity: 0.9;
}

.quantity-selector {
    background: var(--background);
    padding: 1rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
}

.qty-btn {
    width: 45px !important;
    height: 45px !important;
    border-radius: 12px !important;
    font-size: 1.5rem !important;
    transition: transform 0.2s ease !important;
}

.qty-btn:hover {
    transform: scale(1.05);
}

#qtyValue {
    width: 60px !important;
    height: 45px !important;
    font-size: 1.3rem !important;
    border-radius: 12px !important;
    border: 2px solid var(--primary) !important;
    background: var(--card) !important;
    color: var(--text) !important;
}

.close-modal {
    position: absolute;
    right: 2rem;
    top: 2rem;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--background);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--text);
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.close-modal:hover {
    background: var(--primary);
    color: var(--white);
    transform: rotate(90deg);
}

.modal-cart-btn {
    background: var(--primary);
    color: var(--white);
    padding: 1.2rem;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    margin-top: 2rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.modal-cart-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.brand-filter {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--accent);
    border-radius: 8px;
    background: var(--card);
    color: var(--text);
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.brand-filter:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(161, 130, 201, 0.2);
}

body.darkmode .brand-filter {
    background: var(--accent);
    color: var(--white);
    border-color: var(--primary);
}

body.darkmode .brand-filter::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.top-selling-btn {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 2px solid var(--primary);
    border-radius: 8px;
    background: var(--card);
    color: var(--primary);
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.top-selling-btn:hover {
    background: var(--primary);
    color: var(--white);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(161, 130, 201, 0.3);
}

.top-selling-btn.active {
    background: var(--primary);
    color: var(--white);
    box-shadow: 0 0 0 2px rgba(161, 130, 201, 0.4);
}

body.darkmode .top-selling-btn {
    background: var(--accent);
    border-color: var(--primary);
}

body.darkmode .top-selling-btn:hover,
body.darkmode .top-selling-btn.active {
    background: var(--primary);
    color: var(--white);
}

/* Reviews Section Styles */
.modal-reviews-section {
    margin-top: 2rem;
}

.reviews-summary {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--background);
    border-radius: 12px;
}

.rating-overview {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.rating-score {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary);
}

.rating-stars {
    display: flex;
    gap: 0.2rem;
    margin: 0.5rem 0;
}

.star-display {
    color: #ffc107;
    font-size: 1.2rem;
}

.star-display.empty {
    color: #ddd;
}

.total-reviews {
    font-size: 0.9rem;
    color: var(--text);
    opacity: 0.8;
}

.rating-distribution {
    flex: 1;
    margin-left: 1rem;
}

.rating-bar {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.bar-fill {
    flex: 1;
    height: 8px;
    background: #eee;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fill-inner {
    height: 100%;
    background: var(--primary);
    transition: width 0.3s ease;
}

.individual-reviews {
    max-height: 400px;
    overflow-y: auto;
}

.review-item {
    border-bottom: 1px solid rgba(0,0,0,0.1);
    padding: 1rem 0;
}

.review-item:last-child {
    border-bottom: none;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.reviewer-name {
    font-weight: 600;
    color: var(--text);
}

.review-date {
    font-size: 0.85rem;
    color: var(--text);
    opacity: 0.6;
}

.review-rating {
    display: flex;
    gap: 0.2rem;
    margin-bottom: 0.5rem;
}

.review-text {
    line-height: 1.6;
    color: var(--text);
    margin: 0;
}

/* Top Rated Button */
.top-rated-btn {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 2px solid #ffc107;
    border-radius: 8px;
    background: var(--card);
    color: #ffc107;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.top-rated-btn:hover {
    background: #ffc107;
    color: var(--white);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

.top-rated-btn.active {
    background: #ffc107;
    color: var(--white);
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.4);
}

body.darkmode .top-rated-btn {
    background: var(--accent);
    border-color: #ffc107;
}

body.darkmode .top-rated-btn:hover,
body.darkmode .top-rated-btn.active {
    background: #ffc107;
    color: var(--white);
}

/* Product Card Stars */
.product-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.5rem 0;
}

.product-stars {
    display: flex;
    gap: 0.1rem;
}

.product-star {
    color: #ffc107;
    font-size: 0.9rem;
}

.product-star.empty {
    color: #ddd;
}

.rating-text {
    font-size: 0.8rem;
    color: var(--text);
    opacity: 0.8;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .modal-product-details {
        grid-template-columns: 1fr;
    }
    
    .modal-product-image img {
        height: 300px;
    }
    
    .modal-content {
        margin: 20px;
        padding: 1.5rem;
    }
    
    .modal-product-info h2 {
        font-size: 1.8rem;
    }
}
  </style>
</head>
<body>
  <header>
    <div class="logo">Scentora</div>
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search for products" />
    </div>
    <nav class="nav-center">
      <a href="shop_user.php" class="active">Shop</a>
      <a href="orders_user.php">Orders</a>
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

  <section class="shop-products">
    <aside class="sidebar">
      <h3>Filters</h3>
      <div style="margin-bottom: 1.5rem;">
        <button id="topSellingBtn" class="top-selling-btn" onclick="toggleTopSelling()">
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
          </svg>
          Top Selling Items
        </button>
      </div>
      <div>
        <button id="topRatedBtn" class="top-rated-btn" onclick="toggleTopRated()">
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
          </svg>
          Top Rated Items
        </button>
      </div>
      <div>
        <label class="Category">Category:</label>
        <select class="category-select" style="margin-top: 10px;">
          <option value="All">All</option>
          <option value="Floral">Floral</option>
          <option value="Woody">Woody</option>
          <option value="Oriental">Oriental</option>
           <option value="Fresh">Fresh</option>
            <option value="Fruity">Fruity</option>
            <option value="Gourmand">Gourmand</option>
            <option value="Chypre">Chypre</option>
            <option value="Fougere">Fougere</option>
            <option value="Leather"></option>Leather</option>
        </select>
      </div>
      <div style="margin-top: 1.5rem;">
    <label>Brand:</label>
    <input type="text" id="brandFilter" class="brand-filter" placeholder="Search brand..." style="margin-top: 10px;">
</div>
    </aside>

    <div class="product-grid" id="productGrid">
      <!--Will Be Populated by js-->
    </div>
  </section>
  <div id="productModal" class="modal">
  <div class="modal-content">
    <span class="close-modal">&times;</span>
    <div class="modal-product-details">
      <div class="modal-product-image">
        <img id="modalImage" src="" alt="">
      </div>
      <div class="modal-product-info">
        <h2 id="modalName"></h2>
        <p class="modal-brand">Brand: <span id="modalBrand"></span></p>
        <p class="modal-category">Category: <span id="modalCategory"></span></p>
        <p class="modal-price">Price: <span id="modalPrice"></span></p>
        <p class="modal-stock">Stock Status: <span id="modalStock"></span></p>
        <p class="modal-code">Product Code: <span id="modalCode"></span></p>
        <div class="modal-description">
            <h3>Product Information:</h3>
            <p id="modalDescription"></p>
        </div>
        <div class="quantity-selector" style="display:flex;align-items:center;gap:1rem;justify-content:left;margin-top:1rem;">
          <button type="button" class="qty-btn" id="qtyMinus" style="width:40px;height:40px;font-size:1.5rem;border:none;border-radius:8px;background:#5a4a66;color:#fff;cursor:pointer;">-</button>
          <input type="number" id="qtyValue" min="1" value="1" style="font-size:1.3rem;width:48px;text-align:center;border-radius:8px;border:none;background:#392e44;color:#fff;">
          <button type="button" class="qty-btn" id="qtyPlus" style="width:40px;height:40px;font-size:1.5rem;border:none;border-radius:8px;background:#a182c9;color:#fff;cursor:pointer;">+</button>
        </div>
        <button class="cart-btn modal-cart-btn" onclick="addToCart(currentViewingProduct, getModalQty())">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M9 20a1 1 0 100-2 1 1 0 000 2z"></path>
            <path d="M20 20a1 1 0 100-2 1 1 0 000 2z"></path>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          Add to Cart
        </button>
        
        <!-- Product Reviews Section -->
        <div class="modal-reviews-section" id="modalReviews">
          <h3 style="color: var(--primary); margin-top: 2rem; margin-bottom: 1rem; border-top: 1px solid rgba(0,0,0,0.1); padding-top: 1.5rem;">Customer Reviews</h3>
          <div id="reviewsContent">
            <div class="loading-reviews" style="text-align: center; padding: 2rem;">
              <div style="display: inline-block; width: 20px; height: 20px; border: 2px solid var(--primary); border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>
              <p style="color: var(--text); margin-top: 1rem;">Loading reviews...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  <script>
//Dark mode toggle
const darkToggle = document.getElementById('darkmode');
if (localStorage.getItem('scentoraDark') === '1') {
  document.body.classList.add('darkmode');
  darkToggle.checked = true;
}
darkToggle.addEventListener('change', () => {
  document.body.classList.toggle('darkmode', darkToggle.checked);
  localStorage.setItem('scentoraDark', darkToggle.checked ? '1' : '0');
});

//Logout confirmation
document.getElementById("logout-link").onclick = function(e) {
  e.preventDefault();
  if (confirm("Are you sure you want to logout?")) {
    fetch('/IM2-Scentora/files/admin/logout.php')
                .then(() => {
                    localStorage.clear();
                    sessionStorage.clear();
                    window.location.replace("/IM2-Scentora/files/admin/loginpage.php");
                });
  }
};

//Product data store
let allProducts = <?php echo json_encode($products); ?>;
let currentViewingProduct = null;
let isTopSellingMode = false;
let isTopRatedMode = false;
let topSellingProducts = [];
let topRatedProducts = [];

const categorySelect = document.querySelector(".category-select");
const searchInput = document.getElementById("searchInput");
const productGrid = document.getElementById("productGrid");
const brandFilter = document.getElementById("brandFilter");
const topSellingBtn = document.getElementById("topSellingBtn");
const topRatedBtn = document.getElementById("topRatedBtn");

//Category filters
const allCategoryList = [
  "Floral", "Citrus", "Woody", "Oriental",
  "Fresh", "Fruity", "Gourmand", "Chypre", "Fougere", "Leather"
];

function populateCategories() {
  categorySelect.innerHTML = `<option value="All">All</option>`;
  allCategoryList.forEach(cat => {
    categorySelect.innerHTML += `<option value="${cat}">${cat}</option>`;
  });
}

//Top Selling functionality
function toggleTopSelling() {
    isTopSellingMode = !isTopSellingMode;
    
    // Reset other modes
    if (isTopSellingMode) {
        isTopRatedMode = false;
        topRatedBtn.classList.remove('active');
        topRatedBtn.innerHTML = `
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
            Top Rated Items
        `;
    }
    
    if (isTopSellingMode) {
        topSellingBtn.classList.add('active');
        topSellingBtn.innerHTML = `
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
            Top Selling (Active)
        `;
        fetchTopSellingProducts();
    } else {
        topSellingBtn.classList.remove('active');
        topSellingBtn.innerHTML = `
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
            Top Selling Items
        `;
        // Clear top selling products array and render normal products
        topSellingProducts = [];
        renderFilteredProducts();
    }
}

//Top Rated functionality
function toggleTopRated() {
    isTopRatedMode = !isTopRatedMode;
    
    // Reset other modes
    if (isTopRatedMode) {
        isTopSellingMode = false;
        topSellingBtn.classList.remove('active');
        topSellingBtn.innerHTML = `
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
            Top Selling Items
        `;
    }
    
    if (isTopRatedMode) {
        topRatedBtn.classList.add('active');
        topRatedBtn.innerHTML = `
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
            Top Rated (Active)
        `;
        fetchTopRatedProducts();
    } else {
        topRatedBtn.classList.remove('active');
        topRatedBtn.innerHTML = `
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
            Top Rated Items
        `;
        // Clear top rated products array and render normal products
        topRatedProducts = [];
        renderFilteredProducts();
    }
}

function fetchTopSellingProducts() {
    // Show loading state
    productGrid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
            <div style="display: inline-block; width: 20px; height: 20px; border: 2px solid var(--primary); border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="color: var(--text); margin-top: 1rem;">Loading top selling items...</p>
        </div>
    `;

    fetch('get_top_selling.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                topSellingProducts = data.products;
                renderTopSellingProducts();
            } else {
                productGrid.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                        <p style="color: var(--text);">Failed to load top selling items.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching top selling products:', error);
            productGrid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                    <p style="color: var(--text);">Error loading top selling items.</p>
                </div>
            `;
        });
}

function renderTopSellingProducts() {
    const filterText = searchInput.value.toLowerCase();
    const selectedCategory = categorySelect.value;
    const brandText = brandFilter.value.toLowerCase();

    productGrid.innerHTML = "";

    if (!topSellingProducts || topSellingProducts.length === 0) {
        productGrid.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                <p style="color: var(--text);">No products available in top selling view.</p>
            </div>`;
        return;
    }

    const filteredProducts = topSellingProducts.filter(product => {
        const name = (product.Product_Name || "").toLowerCase();
        const brand = (product.Brand || "").toLowerCase();
        const category = (product.Category || "").toLowerCase();
        
        const matchesSearch = name.includes(filterText);
        const matchesCategory = selectedCategory === "All" || category === selectedCategory.toLowerCase();
        const matchesBrand = brandText === "" || brand.includes(brandText);

        return matchesSearch && matchesCategory && matchesBrand;
    });

    if (filteredProducts.length === 0) {
        productGrid.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                <p style="color: var(--text);">No top selling items match your filters.</p>
            </div>`;
        return;
    }

    filteredProducts.forEach((product, index) => {
        const card = document.createElement("div");
        card.className = "product-card";
        card.innerHTML = `
            <div class="card">
                <div class="img">
                    <div style="position: absolute; top: 10px; left: 10px; background: var(--primary); color: white; 
                                border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; 
                                justify-content: center; font-weight: bold; font-size: 0.8rem; z-index: 1;">
                        #${index + 1}
                    </div>
                    <img src="${product.Image_URL || 'placeholder.jpg'}" alt="${product.Product_Name}" 
                         onclick="viewProduct(${product.Product_ID})" style="cursor: pointer;">
                </div>
                <div class="text">
                    <p class="brand-name">${product.Brand || 'Scentora'}</p>
                    <h3 class="product-name">${product.Product_Name}</h3>
                    <p class="price-tag">${parseFloat(product.Product_Price).toFixed(2)}</p>
                    <span class="stock-status ${getStockStatusClass(product.Stock_Level)}">
                        ${product.Available_Stocks} left
                    </span>
                    ${product.total_sold > 0 ? 
                        `<div style="font-size: 0.75rem; color: var(--primary); margin-top: 0.5rem; font-weight: 500;">
                            🔥 ${product.total_sold} sold (last 3 months)
                        </div>` : 
                        `<div style="font-size: 0.75rem; color: #888; margin-top: 0.5rem; font-weight: 400;">
                            No sales yet
                        </div>`
                    }
                    <div class="button-group">
                        <button class="view-btn" onclick="viewProduct(${product.Product_ID})">
                            Details
                        </button>
                        <button class="cart-btn" onclick="addToCart(${product.Product_ID})">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        `;
        productGrid.appendChild(card);
    });
}

function fetchTopRatedProducts() {
    // Show loading state
    productGrid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
            <div style="display: inline-block; width: 20px; height: 20px; border: 2px solid #ffc107; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="color: var(--text); margin-top: 1rem;">Loading top rated items...</p>
        </div>
    `;

    fetch('get_top_rated.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Top rated response:', data); // Debug log
            if (data.success) {
                topRatedProducts = data.products;
                if (topRatedProducts.length === 0) {
                    productGrid.innerHTML = `
                        <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                            <p style="color: var(--text);">No top rated items found. Products need reviews to appear here.</p>
                        </div>
                    `;
                } else {
                    renderTopRatedProducts();
                }
            } else {
                productGrid.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                        <p style="color: var(--text);">Failed to load top rated items: ${data.error || 'Unknown error'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching top rated products:', error);
            productGrid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                    <p style="color: var(--text);">Error loading top rated items: ${error.message}</p>
                    <button onclick="fetchTopRatedProducts()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer;">
                        Try Again
                    </button>
                </div>
            `;
        });
}

function renderTopRatedProducts() {
    const filterText = searchInput.value.toLowerCase();
    const selectedCategory = categorySelect.value;
    const brandText = brandFilter.value.toLowerCase();

    productGrid.innerHTML = "";

    if (!topRatedProducts || topRatedProducts.length === 0) {
        productGrid.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                <p style="color: var(--text);">No products available in top rated view.</p>
            </div>`;
        return;
    }

    const filteredProducts = topRatedProducts.filter(product => {
        const name = (product.Product_Name || "").toLowerCase();
        const brand = (product.Brand || "").toLowerCase();
        const category = (product.Category || "").toLowerCase();
        
        const matchesSearch = name.includes(filterText);
        const matchesCategory = selectedCategory === "All" || category === selectedCategory.toLowerCase();
        const matchesBrand = brandText === "" || brand.includes(brandText);

        return matchesSearch && matchesCategory && matchesBrand;
    });

    if (filteredProducts.length === 0) {
        productGrid.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                <p style="color: var(--text);">No top rated items match your filters.</p>
            </div>`;
        return;
    }

    filteredProducts.forEach((product, index) => {
        const card = document.createElement("div");
        card.className = "product-card";
        const stars = renderStars(product.average_rating || 0);
        card.innerHTML = `
            <div class="card">
                <div class="img">
                    <div style="position: absolute; top: 10px; left: 10px; background: #ffc107; color: white; 
                                border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; 
                                justify-content: center; font-weight: bold; font-size: 0.8rem; z-index: 1;">
                        #${index + 1}
                    </div>
                    <img src="${product.Image_URL || 'placeholder.jpg'}" alt="${product.Product_Name}" 
                         onclick="viewProduct(${product.Product_ID})" style="cursor: pointer;">
                </div>
                <div class="text">
                    <p class="brand-name">${product.Brand || 'Scentora'}</p>
                    <h3 class="product-name">${product.Product_Name}</h3>
                    <p class="price-tag">${parseFloat(product.Product_Price).toFixed(2)}</p>
                    <div class="product-rating">
                        <div class="product-stars">${stars}</div>
                        <span class="rating-text">${product.average_rating ? product.average_rating.toFixed(1) : '0.0'} (${product.total_reviews || 0} reviews)</span>
                    </div>
                    <span class="stock-status ${getStockStatusClass(product.Stock_Level)}">
                        ${product.Available_Stocks} left
                    </span>
                    <div class="button-group">
                        <button class="view-btn" onclick="viewProduct(${product.Product_ID})">
                            Details
                        </button>
                        <button class="cart-btn" onclick="addToCart(${product.Product_ID})">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        `;
        productGrid.appendChild(card);
    });
}

//Filter renderer
function renderFilteredProducts() {
  // If in top selling mode or top rated mode, don't render regular products
  if (isTopSellingMode || isTopRatedMode) {
    return;
  }
  
  const filterText = searchInput.value.toLowerCase();
  const selectedCategory = categorySelect.value;
  const brandText = brandFilter.value.toLowerCase();

  productGrid.innerHTML = "";

  if (!allProducts || allProducts.length === 0) {
      productGrid.innerHTML = `
          <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
              <p style="color: var(--text);">No products available.</p>
          </div>`;
      return;
  }

  const filteredProducts = allProducts.filter(product => {
      const name = (product.Product_Name || "").toLowerCase();
      const brand = (product.Brand || "").toLowerCase();
      const category = (product.Category || "").toLowerCase();
      
      const matchesSearch = name.includes(filterText);
      const matchesCategory = selectedCategory === "All" || category === selectedCategory.toLowerCase();
      const matchesBrand = brandText === "" || brand.includes(brandText);

      return matchesSearch && matchesCategory && matchesBrand;
  });

  if (filteredProducts.length === 0) {
      productGrid.innerHTML = `
          <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
              <p style="color: var(--text);">No products match your filters.</p>
          </div>`;
      return;
  }

  filteredProducts.forEach(product => {
      const card = document.createElement("div");
      card.className = "product-card";
      card.innerHTML = `
          <div class="card">
              <div class="img">
                  <img src="${product.Image_URL || 'placeholder.jpg'}" alt="${product.Product_Name}" 
                       onclick="viewProduct(${product.Product_ID})" style="cursor: pointer;">
              </div>
              <div class="text">
                  <p class="brand-name">${product.Brand || 'Scentora'}</p>
                  <h3 class="product-name">${product.Product_Name}</h3>
                  <p class="price-tag">${parseFloat(product.Product_Price).toFixed(2)}</p>
                  <div class="product-rating">
                      <div class="product-stars" id="stars-${product.Product_ID}">
                          ${renderStars(product.average_rating || 0)}
                      </div>
                      <span class="rating-text" id="rating-text-${product.Product_ID}">
                          ${product.average_rating ? product.average_rating.toFixed(1) : '0.0'} (${product.total_reviews || 0} reviews)
                      </span>
                  </div>
                  <span class="stock-status ${getStockStatusClass(product.Stock_Level)}">
                      ${product.Available_Stocks} left
                  </span>
                  <div class="button-group">
                      <button class="view-btn" onclick="viewProduct(${product.Product_ID})">
                          Details
                      </button>
                      <button class="cart-btn" onclick="addToCart(${product.Product_ID})">
                          Add to Cart
                      </button>
                  </div>
              </div>
          </div>
      `;
      productGrid.appendChild(card);
  });
}

//Filter listeners
categorySelect.addEventListener("change", renderFilteredProducts);
searchInput.addEventListener("input", renderFilteredProducts);
brandFilter.addEventListener("input", renderFilteredProducts);
topSellingBtn.addEventListener("click", toggleTopSelling);
topRatedBtn.addEventListener("click", toggleTopRated);

//Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  console.log("Products loaded:", allProducts.length); // Debug
  populateCategories();
  renderFilteredProducts();
});

let maxQty = 1;

function getModalQty() {
  return parseInt(document.getElementById('qtyValue').value, 10) || 1;
}

function setModalQty(val, max) {
  val = Math.max(1, Math.min(val, max));
  document.getElementById('qtyValue').value = val;
}

document.getElementById('qtyMinus').onclick = function() {
  setModalQty(getModalQty() - 1, maxQty);
};
document.getElementById('qtyPlus').onclick = function() {
  setModalQty(getModalQty() + 1, maxQty);
};
document.getElementById('qtyValue').addEventListener('input', function() {
  let val = parseInt(this.value, 10) || 1;
  if (val > maxQty) val = maxQty;
  if (val < 1) val = 1;
  this.value = val;
});

function viewProduct(productId) {
  // Check both regular products, top selling products, and top rated products
  let product = allProducts.find(p => p.Product_ID == productId);
  if (!product && isTopSellingMode) {
    product = topSellingProducts.find(p => p.Product_ID == productId);
  }
  if (!product && isTopRatedMode) {
    product = topRatedProducts.find(p => p.Product_ID == productId);
  }
  
  currentViewingProduct = productId;
  if (product) {
    document.getElementById("modalImage").src = product.Image_URL || 'placeholder.jpg';
    document.getElementById("modalName").textContent = product.Product_Name;
    document.getElementById("modalBrand").textContent = product.Brand || 'No brand specified';
    document.getElementById("modalCategory").textContent = product.Category;
    document.getElementById("modalPrice").textContent = parseFloat(product.Product_Price).toFixed(2);
    document.getElementById("modalStock").textContent = 
        `${product.Available_Stocks} left`;
    document.getElementById("modalCode").textContent = product.Product_Code;
    document.getElementById("modalDescription").textContent = product.Description || 'No description available.';
    maxQty = parseInt(product.Available_Stocks, 10) || 1;
    setModalQty(1, maxQty);
    document.getElementById("qtyValue").max = maxQty;
    
    // Load reviews for this product
    loadProductReviews(productId);
    
    document.getElementById("productModal").style.display = "block";
    document.body.style.overflow = "hidden";
  }
}

//Modal close logic
const modal = document.getElementById("productModal");
document.querySelector(".close-modal").onclick = () => {
  modal.style.display = "none";
  document.body.style.overflow = "auto";
};
window.onclick = (event) => {
  if (event.target === modal) {
    modal.style.display = "none";
    document.body.style.overflow = "auto";
  }
};
document.addEventListener('keydown', function(event) {
  if (event.key === "Escape" && modal.style.display === "block") {
    modal.style.display = "none";
    document.body.style.overflow = "auto";
  }
});

//Cart logic
function addToCart(productId, quantity = 1) {
  const formData = new FormData();
  formData.append("product_id", productId);
  formData.append("quantity", quantity);

  fetch("add_to_cart.php", {
    method: "POST",
    body: formData
  })
  .then(response => response.json())
  .then (data => {
    if (data.success) {
      alert(data.message);
      document.getElementById("productModal").style.display = "none";
      document.body.style.overflow = "auto";
    } else {
      throw new Error(data.message || "Failed to add item to cart");
    }
  })
  .catch(error => {
    console.error("Add to cart error:", error);
    alert(error.message || "Error adding to cart. Please try again.");
  });
}

//Helpers
function getStockStatusClass(level) {
  switch (level) {
    case 1: return "stock-in";
    case 2: return "stock-low";
    case 3: return "stock-out";
    default: return "stock-unknown";
  }
}

function getStockLabel(level) {
    switch (level) {
        case 1: return '●';
        case 2: return '◐';
        case 3: return '○';
        default: return '?';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Reset filters to default state
    if (categorySelect) categorySelect.value = 'All';
    if (brandFilter) brandFilter.value = '';
    if (searchInput) searchInput.value = '';

    // Initialize products
    populateCategories();
    renderFilteredProducts();
    
    // Start session monitoring
    startSessionMonitoring();
});

// Session monitoring functionality
function startSessionMonitoring() {
    // Check session status every 5 minutes
    setInterval(checkSessionStatus, 5 * 60 * 1000);
    
    // Also check when user becomes active after being away
    let isActive = true;
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && !isActive) {
            checkSessionStatus();
            isActive = true;
        } else if (document.hidden) {
            isActive = false;
        }
    });
}

function checkSessionStatus() {
    fetch('/IM2-Scentora/files/includes/session_status.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                alert('Your session has expired. You will be redirected to login.');
                window.location.replace('/IM2-Scentora/files/admin/loginpage.php');
                return;
            }
            
            if (data.expires_soon && data.time_left > 0) {
                const minutes = Math.floor(data.time_left / 60);
                if (confirm(`Your session will expire in ${minutes} minutes. Do you want to stay logged in?`)) {
                    // Refresh session by making a simple request
                    fetch('/IM2-Scentora/files/includes/session_status.php');
                }
            }
        })
        .catch(error => {
            console.warn('Session check failed:', error);
        });
}

// Helper function to render stars
function renderStars(rating) {
    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            starsHtml += '<span class="product-star">★</span>';
        } else {
            starsHtml += '<span class="product-star empty">★</span>';
        }
    }
    return starsHtml;
}

// Load rating for a specific product
function loadProductRating(productId) {
    fetch(`get_product_reviews.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const starsContainer = document.getElementById(`stars-${productId}`);
                const ratingText = document.getElementById(`rating-text-${productId}`);
                
                if (starsContainer && ratingText) {
                    starsContainer.innerHTML = renderStars(data.average_rating);
                    ratingText.textContent = `${data.average_rating.toFixed(1)} (${data.total_reviews} reviews)`;
                }
            } else {
                const ratingText = document.getElementById(`rating-text-${productId}`);
                if (ratingText) {
                    ratingText.textContent = 'No reviews yet';
                }
            }
        })
        .catch(error => {
            console.error('Error loading product rating:', error);
            const ratingText = document.getElementById(`rating-text-${productId}`);
            if (ratingText) {
                ratingText.textContent = 'No reviews yet';
            }
        });
}

// Load reviews for product modal
function loadProductReviews(productId) {
    const reviewsContent = document.getElementById('reviewsContent');
    
    if (!reviewsContent) {
        console.error('reviewsContent element not found!');
        return;
    }
    
    reviewsContent.innerHTML = `
        <div class="loading-reviews" style="text-align: center; padding: 2rem;">
            <div style="display: inline-block; width: 20px; height: 20px; border: 2px solid var(--primary); border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="color: var(--text); margin-top: 1rem;">Loading reviews...</p>
        </div>
    `;

    fetch(`get_product_reviews.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayReviews(data);
            } else {
                reviewsContent.innerHTML = `
                    <div style="text-align: center; padding: 2rem;">
                        <p style="color: var(--text);">No reviews available for this product.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading reviews:', error);
            reviewsContent.innerHTML = `
                <div style="text-align: center; padding: 2rem;">
                    <p style="color: var(--text);">Error loading reviews.</p>
                </div>
            `;
        });
}

// Display reviews in modal
function displayReviews(reviewData) {
    const reviewsContent = document.getElementById('reviewsContent');
    
    if (reviewData.total_reviews === 0) {
        reviewsContent.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <p style="color: var(--text);">No reviews available for this product yet.</p>
                <p style="color: var(--text); font-size: 0.9rem; opacity: 0.7;">Be the first to leave a review!</p>
            </div>
        `;
        return;
    }

    let reviewsHtml = `
        <div class="reviews-summary">
            <div class="rating-overview">
                <div class="rating-score">${reviewData.average_rating}</div>
                <div class="rating-stars">${renderStars(Math.round(reviewData.average_rating))}</div>
                <div class="total-reviews">${reviewData.total_reviews} review${reviewData.total_reviews !== 1 ? 's' : ''}</div>
            </div>
            <div class="rating-distribution">
                ${[5,4,3,2,1].map(star => {
                    const count = reviewData.rating_distribution[star] || 0;
                    const percentage = reviewData.total_reviews > 0 ? (count / reviewData.total_reviews) * 100 : 0;
                    return `
                        <div class="rating-bar">
                            <span>${star} ★</span>
                            <div class="bar-fill">
                                <div class="bar-fill-inner" style="width: ${percentage}%"></div>
                            </div>
                            <span>${count}</span>
                        </div>
                    `;
                }).join('')}
            </div>
        </div>
        
        <div class="individual-reviews">
            ${reviewData.reviews.map(review => `
                <div class="review-item">
                    <div class="review-header">
                        <span class="reviewer-name">${review.Consumer_Name}</span>
                        <span class="review-date">${review.Formatted_Date}</span>
                    </div>
                    <div class="review-rating">${renderStars(review.Rating)}</div>
                    <p class="review-text">${review.Review_Text || 'No written review provided.'}</p>
                </div>
            `).join('')}
        </div>
    `;

    reviewsContent.innerHTML = reviewsHtml;
}
  </script>
</body>
</html>
