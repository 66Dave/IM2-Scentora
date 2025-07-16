<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "Not logged in.";
    exit;
}
$host = "localhost";
$user_db = "root";
$pass_db = "";
$dbname = "scentoradb";
$conn = new mysqli($host, $user_db, $pass_db, $dbname);
if ($conn->connect_error) {
    echo "Database error.";
    exit;
}
$user_id = $_SESSION['user_id'];
$sql = "SELECT Order_ID, Order_Date, Total_Amount, Status FROM `order` WHERE User_ID = ? ORDER BY Order_Date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($oid, $odate, $total, $status);

echo "<table style='width:100%;border-collapse:collapse;'>";
echo "<tr style='background:#efe2fa;'><th style='padding:8px;'>Order #</th><th style='padding:8px;'>Date</th><th style='padding:8px;'>Total</th><th style='padding:8px;'>Status</th></tr>";
$found = false;
while ($stmt->fetch()) {
    $found = true;
    echo "<tr>";
    echo "<td style='padding:8px;border-bottom:1px solid #eee;'>".htmlspecialchars($oid)."</td>";
    echo "<td style='padding:8px;border-bottom:1px solid #eee;'>".htmlspecialchars($odate)."</td>";
    echo "<td style='padding:8px;border-bottom:1px solid #eee;'>₱".number_format($total,2)."</td>";
    echo "<td style='padding:8px;border-bottom:1px solid #eee;'>".htmlspecialchars($status)."</td>";
    echo "</tr>";
}
if (!$found) {
    echo "<tr><td colspan='4' style='padding:16px;text-align:center;'>No orders found.</td></tr>";
}
echo "</table>";
$stmt->close();
$conn->close();
?>