<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);

    // Validate input
    if (empty($name) || $quantity <= 0 || $price <= 0) {
        header('Location: ../frontend/stock-management.php?error=invalid_input');
        exit;
    }

    // Read current stock data
    $stock_file = __DIR__ . '/stock.json';
    $stock_json = file_get_contents($stock_file);
    $stock_data = json_decode($stock_json, true);

    // Check if item already exists
    foreach ($stock_data['items'] as $item) {
        if (strtolower($item['name']) === strtolower($name)) {
            header('Location: ../frontend/stock-management.php?error=item_exists');
            exit;
        }
    }

    // Get next item ID
    $max_id = 0;
    foreach ($stock_data['items'] as $item) {
        $max_id = max($max_id, $item['id']);
    }
    $new_id = $max_id + 1;

    // Add new item
    $stock_data['items'][] = [
        'id' => $new_id,
        'name' => $name,
        'quantity' => $quantity,
        'price' => $price,
        'last_updated' => date('Y-m-d H:i:s')
    ];

    // Add transaction record
    $stock_data['transactions'][] = [
        'date' => date('Y-m-d H:i:s'),
        'item_name' => $name,
        'type' => 'in',
        'quantity' => $quantity,
        'updated_by' => $_SESSION['username'],
        'department' => $_SESSION['department']
    ];

    // Save updated stock data
    file_put_contents($stock_file, json_encode($stock_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/stock-management.php?message=item_added');
    exit;
}

// If not POST request, redirect to stock management page
header('Location: ../frontend/stock-management.php');
exit;
?>
