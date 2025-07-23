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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Reviews - Scentora</title>
    <style>
        :root {
            --primary: #a182c9;
            --accent: #917489;
            --background: #f7f5fa;
            --card: #fff7ff;
            --sidebar: #fff;
            --text: #392e44;
            --nav-bg: rgba(247, 245, 250, 0.95);
            --white: #ffffff;
            --shadow: 0 2px 16px rgba(161, 130, 201, 0.12);
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: var(--background);
            color: var(--text);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* Header styles */
        header {
            background: var(--nav-bg);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-center {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-center a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-center a:not([href="#logout"]).active {
            background: var(--primary);
            color: var(--white);
        }

        .nav-center a:hover {
            background: var(--accent);
            color: var(--white);
        }

        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toggle-switch input[type="checkbox"] {
            width: 50px;
            height: 24px;
            appearance: none;
            background: #ccc;
            border-radius: 12px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-switch input[type="checkbox"]:checked {
            background: var(--primary);
        }

        .toggle-switch input[type="checkbox"]::before {
            content: "";
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .toggle-switch input[type="checkbox"]:checked::before {
            transform: translateX(26px);
        }

        /* Main content styles */
        .reviews-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--text);
            font-size: 1.1rem;
            opacity: 0.8;
        }

        .reviewable-items {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .review-card {
            background: var(--card);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .review-card:hover {
            transform: translateY(-4px);
        }

        .product-info {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .product-details h3 {
            color: var(--primary);
            margin: 0 0 0.5rem 0;
            font-size: 1.2rem;
        }

        .product-details p {
            margin: 0;
            color: var(--text);
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .order-info {
            background: var(--background);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .order-info p {
            margin: 0.25rem 0;
        }

        .review-form {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 1.5rem;
        }

        .star-rating {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            align-items: center;
        }

        .star {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }

        .star.active,
        .star:hover {
            color: #ffc107;
        }

        .review-textarea {
            width: 100%;
            min-height: 120px;
            padding: 1rem;
            border: 2px solid var(--accent);
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.95rem;
            resize: vertical;
            margin-bottom: 1rem;
        }

        .review-textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .submit-review-btn {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-review-btn:hover {
            background: #8e6bbf;
            transform: translateY(-2px);
        }

        .submit-review-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .already-reviewed {
            background: #e8f5e8;
            color: #2d5a2d;
            text-align: center;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 600;
        }

        .no-items {
            text-align: center;
            background: var(--card);
            padding: 3rem 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
        }

        .no-items h3 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .loading {
            text-align: center;
            padding: 3rem;
        }

        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid var(--accent);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Dark mode */
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

        @media (max-width: 768px) {
            .reviewable-items {
                grid-template-columns: 1fr;
            }

            .nav-center {
                gap: 1rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Scentora</div>
        <nav class="nav-center">
            <a href="shop_user.php">Shop</a>
            <a href="orders_user.php">Orders</a>
            <a href="userCart.php" title="Cart">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </a>
            <a href="reviews_user.php" class="active">Reviews</a>
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

    <div class="reviews-container">
        <div class="page-header">
            <h1>Product Reviews</h1>
            <p>Share your experience with products you've purchased. Reviews help other customers make informed decisions.</p>
        </div>

        <div id="reviewableItems" class="loading">
            <div class="loading-spinner"></div>
            <p>Loading your reviewable items...</p>
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

        // Logout functionality
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

        // Session monitoring functionality
        function startSessionMonitoring() {
            setInterval(checkSessionStatus, 5 * 60 * 1000);
            
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
                            fetch('/IM2-Scentora/files/includes/session_status.php');
                        }
                    }
                })
                .catch(error => {
                    console.warn('Session check failed:', error);
                });
        }

        // Star rating functionality
        function setStarRating(cardElement, rating) {
            const stars = cardElement.querySelectorAll('.star');
            stars.forEach((star, index) => {
                star.classList.toggle('active', index < rating);
            });
            cardElement.dataset.rating = rating;
        }

        // Submit review
        function submitReview(orderID, productID, cardElement) {
            const rating = cardElement.dataset.rating || 0;
            const reviewText = cardElement.querySelector('.review-textarea').value.trim();

            if (rating === 0) {
                alert('Please select a rating (1-5 stars)');
                return;
            }

            if (reviewText.length === 0) {
                alert('Please write a review');
                return;
            }

            const submitBtn = cardElement.querySelector('.submit-review-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            const formData = new FormData();
            formData.append('order_id', orderID);
            formData.append('product_id', productID);
            formData.append('rating', rating);
            formData.append('review_text', reviewText);

            fetch('submit_review.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Review submitted successfully!');
                    loadReviewableItems(); // Reload the items
                } else {
                    alert('Error: ' + (data.error || 'Failed to submit review'));
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Review';
                }
            })
            .catch(error => {
                console.error('Error submitting review:', error);
                alert('Failed to submit review. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Review';
            });
        }

        // Load reviewable items
        function loadReviewableItems() {
            fetch('get_reviewable_orders.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('reviewableItems');
                    
                    if (!data.success) {
                        container.innerHTML = `
                            <div class="no-items">
                                <h3>Error Loading Reviews</h3>
                                <p>Failed to load reviewable items. Please refresh the page.</p>
                            </div>
                        `;
                        return;
                    }

                    if (data.reviewable_items.length === 0) {
                        container.innerHTML = `
                            <div class="no-items">
                                <h3>No Items to Review</h3>
                                <p>You don't have any completed orders eligible for reviews at the moment.</p>
                                <p>Orders become eligible for review after delivery and must be reviewed within 7 days.</p>
                            </div>
                        `;
                        return;
                    }

                    container.className = 'reviewable-items';
                    container.innerHTML = '';

                    data.reviewable_items.forEach(item => {
                        const card = document.createElement('div');
                        card.className = 'review-card';
                        
                        card.innerHTML = `
                            <div class="product-info">
                                <img src="${item.Image_URL}" alt="${item.Product_Name}" class="product-image">
                                <div class="product-details">
                                    <h3>${item.Product_Name}</h3>
                                    <p><strong>${item.Brand}</strong></p>
                                    <p>₱${parseFloat(item.Product_Price).toFixed(2)}</p>
                                </div>
                            </div>
                            
                            <div class="order-info">
                                <p><strong>Order #:</strong> ${item.Order_ID}</p>
                                <p><strong>Delivered:</strong> ${new Date(item.Arrival_Date).toLocaleDateString()}</p>
                                <p><strong>Review Period:</strong> ${item.days_left} day(s) remaining</p>
                            </div>
                            
                            ${item.has_reviewed ? `
                                <div class="already-reviewed">
                                    ✅ You have already reviewed this product
                                </div>
                            ` : `
                                <div class="review-form">
                                    <div class="star-rating">
                                        <span style="margin-right: 0.5rem; font-weight: 600;">Rating:</span>
                                        ${[1,2,3,4,5].map(i => `<span class="star" onclick="setStarRating(this.closest('.review-card'), ${i})">★</span>`).join('')}
                                    </div>
                                    
                                    <textarea class="review-textarea" placeholder="Share your experience with this product... (Max 1000 characters)"></textarea>
                                    
                                    <button class="submit-review-btn" onclick="submitReview(${item.Order_ID}, ${item.Product_ID}, this.closest('.review-card'))">
                                        Submit Review
                                    </button>
                                </div>
                            `}
                        `;
                        
                        container.appendChild(card);
                    });
                })
                .catch(error => {
                    console.error('Error loading reviewable items:', error);
                    document.getElementById('reviewableItems').innerHTML = `
                        <div class="no-items">
                            <h3>Error Loading Reviews</h3>
                            <p>Failed to load reviewable items. Please refresh the page.</p>
                        </div>
                    `;
                });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            startSessionMonitoring();
            loadReviewableItems();
        });
    </script>
</body>
</html>
