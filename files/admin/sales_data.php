<?php
header('Content-Type: application/json');

try {
    $conn = new mysqli("localhost", "root", "", "scentoradb");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get total sales from completed orders only
    $totalSalesQuery = "
        SELECT 
            COALESCE(SUM(Total_Amount), 0) as total
        FROM `order` 
        WHERE status = 'Completed'
    ";
    
    $totalResult = $conn->query($totalSalesQuery);
    if (!$totalResult) {
        throw new Exception("Total sales query failed: " . $conn->error);
    }
    $totalSales = (float)($totalResult->fetch_assoc()['total'] ?? 0);

    // Get monthly sales for completed orders only
    $monthlySalesQuery = "
        SELECT 
            DATE_FORMAT(Order_Date, '%Y-%m') as month,
            COALESCE(SUM(Total_Amount), 0) as total,
            COUNT(DISTINCT Order_ID) as order_count
        FROM `order`
        WHERE 
            status = 'Completed'
            AND Order_Date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(Order_Date, '%Y-%m')
        ORDER BY month ASC
    ";

    $monthlySales = $conn->query($monthlySalesQuery);
    if (!$monthlySales) {
        throw new Exception("Monthly sales query failed: " . $conn->error);
    }

    $labels = [];
    $values = [];
    $currentMonthSales = 0;
    $salesByMonth = [];

    // Store the monthly data
    while($row = $monthlySales->fetch_assoc()) {
        $salesByMonth[$row['month']] = [
            'total' => (float)$row['total'],
            'count' => (int)$row['order_count']
        ];
    }

    // Generate last 6 months
    $startDate = new DateTime('first day of -5 months');
    $endDate = new DateTime('last day of this month');
    
    $period = new DatePeriod(
        $startDate,
        new DateInterval('P1M'),
        $endDate->modify('+1 month')
    );

    foreach ($period as $date) {
        $monthKey = $date->format('Y-m');
        $labels[] = $date->format('M Y');
        
        if (isset($salesByMonth[$monthKey])) {
            $values[] = (float)$salesByMonth[$monthKey]['total'];
            
            if($monthKey == date('Y-m')) {
                $currentMonthSales = (float)$salesByMonth[$monthKey]['total'];
            }
        } else {
            $values[] = 0;
        }
    }

    // Debug information to verify the data
    echo json_encode([
        'totalSales' => number_format($totalSales, 2, '.', ''),
        'monthlySales' => number_format($currentMonthSales, 2, '.', ''),
        'labels' => $labels,
        'values' => array_map(function($val) {
            return number_format((float)$val, 2, '.', '');
        }, $values),
        'debug' => [
            'rawSales' => $totalSales,
            'rawMonthly' => $currentMonthSales,
            'monthlyData' => $salesByMonth,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}