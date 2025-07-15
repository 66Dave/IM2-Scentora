<?php
session_start();
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";
$conn = new mysqli($host, $username, $password, $database);

$cart_id = intval($_POST['cart_id'] ?? 0);

if ($cart_id < 1) {
    echo "Invalid";
    exit;
}

$sql = "DELETE FROM cart WHERE Cart_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cart_id);
$stmt->execute();
echo "removed";
$stmt->close();
$conn->close();
?>
<style>
/* Modern Cart Button Styles */
.qty-btn, .remove-btn, .checkout-btn {
  background: #917489;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 0.35em 1em;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  margin: 0 2px;
  transition: background 0.2s, box-shadow 0.2s;
  box-shadow: 0 2px 8px rgba(145, 116, 137, 0.08);
  outline: none;
}

.qty-btn:hover, .remove-btn:hover, .checkout-btn:hover {
  background: #5a3e6e;
}

.remove-btn {
  background: #f56565;
  color: #fff;
  padding: 0.35em 0.8em;
}

.remove-btn:hover {
  background: #c53030;
}
</style>