<?php
include 'db_connect.php';

$response = ['success' => false, 'orders' => []];

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT p.id, p.user_id, p.name, p.surname, p.phone, p.email, p.address, p.total_amount, p.created_at,
                   u.first_name, u.last_name, u.email as user_email,
                   COUNT(pi.id) as items_count
            FROM purchases p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN purchase_items pi ON p.id = pi.purchase_id
            GROUP BY p.id
            ORDER BY p.created_at DESC";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response['orders'][] = $row;
        }
        $response['success'] = true;
    } else {
        $response['message'] = 'Немає замовлень.';
    }
} else {
    $response['message'] = 'Невірний метод запиту.';
}

echo json_encode($response);
?>