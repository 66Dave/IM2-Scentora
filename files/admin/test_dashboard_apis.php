<?php
// Test the dashboard APIs to verify data consistency

echo "<h2>Testing Dashboard APIs</h2>";

echo "<h3>1. Dashboard Data (Initial Load)</h3>";
$dashboard_data = file_get_contents('http://localhost/IM2-Scentora/files/admin/dashboard_data.php');
echo "<pre>" . htmlspecialchars($dashboard_data) . "</pre>";

echo "<h3>2. Sales Data (All)</h3>";
$sales_data_all = file_get_contents('http://localhost/IM2-Scentora/files/admin/sales_data.php?month=all');
echo "<pre>" . htmlspecialchars($sales_data_all) . "</pre>";

echo "<h3>3. Sales Data (January - Month 1)</h3>";
$sales_data_jan = file_get_contents('http://localhost/IM2-Scentora/files/admin/sales_data.php?month=1');
echo "<pre>" . htmlspecialchars($sales_data_jan) . "</pre>";

echo "<h3>Data Comparison</h3>";
$dashboard_json = json_decode($dashboard_data, true);
$sales_all_json = json_decode($sales_data_all, true);

echo "<p><strong>Dashboard totalSales:</strong> " . ($dashboard_json['totalSales'] ?? 'Not set') . "</p>";
echo "<p><strong>Sales Data totalSales:</strong> " . ($sales_all_json['totalSales'] ?? 'Not set') . "</p>";
echo "<p><strong>Sales Data monthlySales:</strong> " . ($sales_all_json['monthlySales'] ?? 'Not set') . "</p>";

if (isset($dashboard_json['totalSales']) && isset($sales_all_json['totalSales'])) {
    $dashboard_sales = floatval($dashboard_json['totalSales']);
    $sales_total = floatval($sales_all_json['totalSales']);
    
    echo "<p><strong>Values Match:</strong> " . ($dashboard_sales === $sales_total ? "✅ YES" : "❌ NO") . "</p>";
    echo "<p><strong>Difference:</strong> " . abs($dashboard_sales - $sales_total) . "</p>";
}

?>
