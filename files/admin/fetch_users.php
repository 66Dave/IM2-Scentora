<?php
header('Content-Type: application/json');
error_reporting(0);

try {
    $conn = new mysqli("localhost", "root", "", "scentoradb");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT User_ID, Name, Email, User_Type, is_active FROM user ORDER BY User_ID DESC";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $users = array();
    while($row = $result->fetch_assoc()) {
        $users[] = array(
            'User_ID' => (int)$row['User_ID'],
            'Name' => htmlspecialchars($row['Name']),
            'Email' => htmlspecialchars($row['Email']),
            'User_Type' => $row['User_Type'],
            'is_active' => (bool)$row['is_active']
        );
    }

    echo json_encode($users);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>