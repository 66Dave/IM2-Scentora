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
echo "<tr style='background:var(--card);'><th style='padding:8px;color:var(--primary);'>Order #</th><th style='padding:8px;color:var(--primary);'>Date</th><th style='padding:8px;color:var(--primary);'>Total</th><th style='padding:8px;color:var(--primary);'>Status</th></tr>";
$found = false;
while ($stmt->fetch()) {
    $found = true;
    echo "<tr style='background:#2a2236;'>";
    echo "<td style='padding:8px;border-bottom:1px solid #eee;color:#fff;'>".htmlspecialchars($oid)."</td>";
    echo "<td style='padding:8px;border-bottom:1px solid #eee;color:#fff;'>".htmlspecialchars($odate)."</td>";
    echo "<td style='padding:8px;border-bottom:1px solid #eee;color:#fff;'>₱".number_format($total,2)."</td>";
    echo "<td style='padding:8px;border-bottom:1px solid #eee;color:#fff;'>".htmlspecialchars($status)."</td>";
    echo "</tr>";
}
if (!$found) {
    echo "<tr><td colspan='4' style='padding:16px;text-align:center;background:#2a2236;color:#fff;'>No orders found.</td></tr>";
}
echo "</table>";
$stmt->close();
$conn->close();
?>