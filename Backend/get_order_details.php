<?php
include 'db_connect.php';

$response = ['success' => false, 'order' => null];

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $order_id = $_GET['order_id'] ?? null;
    
    if (!$order_id) {
        $response['message'] = 'Order ID не передано.';
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT p.id, p.user_id, p.name, p.surname, p.phone, p.email, p.address, p.total_amount, p.created_at,
                   pi.good_id, pi.quantity, pi.price, g.title, g.image
            FROM purchases p
            LEFT JOIN purchase_items pi ON p.id = pi.purchase_id
            LEFT JOIN goods g ON pi.good_id = g.id
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $order = null;
    while ($row = $result->fetch_assoc()) {
        if (!$order) {
            $order = [
                'id' => $row['id'],
                'user_id' => $row['user_id'],
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
            $order['items'][] = [
                'good_id' => $row['good_id'],
                'title' => $row['title'],
                'image' => $row['image'],
                'quantity' => $row['quantity'],
                'price' => $row['price']
            ];
        }
    }

    if ($order) {
        $response['success'] = true;
        $response['order'] = $order;
    } else {
        $response['message'] = 'Замовлення не знайдено.';
    }
    
    $stmt->close();
} else {
    $response['message'] = 'Невірний метод запиту.';
}

echo json_encode($response);
?>