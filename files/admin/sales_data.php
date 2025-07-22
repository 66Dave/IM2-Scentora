<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$month = isset($_GET['month']) && $_GET['month'] !== 'all' ? intval($_GET['month']) : null;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

try {
    // Base where clause for completed orders
    $whereClause = "WHERE o.Status = 'Completed'";
    if ($month) {
        $whereClause .= " AND MONTH(o.Order_Date) = $month";
    }

    // Get sales statistics
    $statsQuery = "SELECT 
        COALESCE(SUM(o.Total_Amount), 0) as totalSales,
        COUNT(DISTINCT o.Order_ID) as orderCount,
        COALESCE(AVG(o.Total_Amount), 0) as averageOrderValue
        FROM `order` o
        $whereClause";

    $result = $conn->query($statsQuery);
    $stats = $result->fetch_assoc();

    // Get paginated order details
    $ordersQuery = "SELECT 
        o.Order_ID,
        o.Order_Date,
        o.Total_Amount,
        u.Name as Buyer_Name,
        od.Product_Qty,
        od.Product_Price,
        od.Subtotal,
        p.Product_Name
        FROM `order` o
        JOIN user u ON o.User_ID = u.User_ID
        JOIN orderdetails od ON o.Order_ID = od.Order_ID
        JOIN product p ON od.Product_ID = p.Product_ID
        $whereClause
        ORDER BY o.Order_Date DESC
        LIMIT $limit OFFSET $offset";

    $result = $conn->query($ordersQuery);
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    // Get total count for pagination
    $countQuery = "SELECT COUNT(DISTINCT o.Order_ID) as total 
                   FROM `order` o 
                   $whereClause";
    $result = $conn->query($countQuery);
    $totalItems = $result->fetch_assoc()['total'];
    $totalPages = ceil($totalItems / $limit);

    // Monthly data for chart - Create full date range from January 2025 to current date
    $startDate = '2025-01-01';
    $endDate = date('Y-m-d'); // Current date
    
    // Generate all months from start to current
    $allMonths = [];
    $current = new DateTime($startDate);
    $end = new DateTime($endDate);
    $end->modify('first day of next month'); // Include current month
    
    while ($current < $end) {
        $monthKey = $current->format('Y-m');
        $monthLabel = $current->format('M Y');
        $allMonths[$monthKey] = [
            'label' => $monthLabel,
            'total' => 0
        ];
        $current->modify('first day of next month');
    }
    
    // Get actual sales data
    $chartQuery = "SELECT 
        DATE_FORMAT(Order_Date, '%Y-%m') as month,
        SUM(Total_Amount) as total
        FROM `order`
        WHERE Status = 'Completed' 
        AND Order_Date >= '$startDate'
        AND Order_Date <= '$endDate'
        GROUP BY DATE_FORMAT(Order_Date, '%Y-%m')
        ORDER BY month";

    $result = $conn->query($chartQuery);
    
    // Fill in actual sales data
    while ($row = $result->fetch_assoc()) {
        $monthKey = $row['month'];
        if (isset($allMonths[$monthKey])) {
            $allMonths[$monthKey]['total'] = floatval($row['total']);
        }
    }
    
    // Prepare chart data arrays
    $chartData = ['labels' => [], 'values' => []];
    foreach ($allMonths as $monthKey => $monthData) {
        $chartData['labels'][] = $monthData['label'];
        $chartData['values'][] = $monthData['total'];
    }

    $response = [
        'totalSales' => number_format($stats['totalSales'], 2, '.', ''),
        'orderCount' => $stats['orderCount'],
        'averageOrderValue' => number_format($stats['averageOrderValue'], 2, '.', ''),
        'orders' => $orders,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'itemsPerPage' => $limit,
            'totalItems' => $totalItems
        ],
        'labels' => $chartData['labels'] ?? [],
        'values' => $chartData['values'] ?? [],
        'monthlySales' => $month ? $stats['totalSales'] : array_sum($chartData['values'] ?? [0]),
        'debug' => [
            'dateRange' => $startDate . ' to ' . $endDate,
            'totalMonths' => count($allMonths),
            'hasData' => !empty($chartData['labels']),
            'queryMonth' => $month
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>