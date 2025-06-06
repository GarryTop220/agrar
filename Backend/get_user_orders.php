<?php
include 'db_connect.php';

$response = ['success' => false, 'orders' => []];

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'] ?? null;
    
    if (!$user_id) {
        $response['message'] = 'User ID не передано.';
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT p.id, p.name, p.surname, p.phone, p.email, p.address, p.total_amount, p.created_at,
                   pi.good_id, pi.quantity, pi.price, g.title, g.image
            FROM purchases p
            LEFT JOIN purchase_items pi ON p.id = pi.purchase_id
            LEFT JOIN goods g ON pi.good_id = g.id
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['id'];
        
        if (!isset($orders[$order_id])) {
            $orders[$order_id] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'surname' => $row['surname'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'address' => $row['address'],
                'total_amount' => $row['total_amount'],
                'created_at' => $row['created_at'],
                'items' => []
            ];
        }
        
        if ($row['good_id']) {
            $orders[$order_id]['items'][] = [
                'good_id' => $row['good_id'],
                'title' => $row['title'],
                'image' => $row['image'],
                'quantity' => $row['quantity'],
                'price' => $row['price']
            ];
        }
    }

    $response['success'] = true;
    $response['orders'] = array_values($orders);
    
    $stmt->close();
} else {
    $response['message'] = 'Невірний метод запиту.';
}

echo json_encode($response);
?>