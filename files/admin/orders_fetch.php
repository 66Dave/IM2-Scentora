<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "scentoradb");
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit;
}

$orders = [];

$sql = "SELECT o.Order_ID, o.User_ID, u.Name AS Buyer_Name, o.Total_Amount, o.Order_Date,
               o.Shipping_Address, o.Payment_Method, o.Payment_Proof, o.Status
        FROM `order` o
        JOIN user u ON o.User_ID = u.User_ID
        ORDER BY o.Order_ID DESC";

$result = $conn->query($sql);
if (!$result) {
    echo json_encode(["error" => "Query failed: " . $conn->error]);
    exit;
}

while ($row = $result->fetch_assoc()) {
    $order_id = $row['Order_ID'];

    // Fetch items for this order
    $details_sql = "SELECT od.Product_ID, p.Product_Name, od.Product_Qty, od.Product_Price
                    FROM orderdetails od
                    JOIN product p ON od.Product_ID = p.Product_ID
                    WHERE od.Order_ID = ?";
    $stmt = $conn->prepare($details_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $detail_result = $stmt->get_result();

    $items = [];
    while ($item = $detail_result->fetch_assoc()) {
        $items[] = [
            "product_id" => $item['Product_ID'],
            "name" => $item['Product_Name'],
            "quantity" => (int)$item['Product_Qty'],
            "price" => number_format((float)$item['Product_Price'], 2),
            "subtotal" => number_format((float)$item['Product_Price'] * $item['Product_Qty'], 2)
        ];
    }

    $orders[] = [
        "order_id" => $order_id,
        "buyer_id" => $row['User_ID'],
        "buyer_name" => $row['Buyer_Name'],
        "total" => number_format((float)$row['Total_Amount'], 2),
        "date" => $row['Order_Date'],
        "address" => $row['Shipping_Address'],
        "payment_method" => $row['Payment_Method'],
        "proof" => $row['Payment_Proof'],
        "status" => $row['Status'],
        "items" => $items
    ];
    $stmt->close();
}

echo json_encode($orders);
$conn->close();
?>