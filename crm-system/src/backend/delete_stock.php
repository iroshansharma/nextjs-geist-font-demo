<?php
require_once 'auth.php';

// Check if user is logged in and is super admin
if (!is_logged_in() || !has_role(1)) {
    header('Location: ../frontend/index.php');
    exit;
}

$item_id = (int)($_GET['id'] ?? 0);

if ($item_id > 0) {
    // Read current stock data
    $stock_file = __DIR__ . '/stock.json';
    $stock_json = file_get_contents($stock_file);
    $stock_data = json_decode($stock_json, true);

    // Find item name before deletion
    $item_name = '';
    foreach ($stock_data['items'] as $item) {
        if ($item['id'] === $item_id) {
            $item_name = $item['name'];
            break;
        }
    }

    // Remove item
    $stock_data['items'] = array_filter($stock_data['items'], function($item) use ($item_id) {
        return $item['id'] !== $item_id;
    });

    // Reindex array
    $stock_data['items'] = array_values($stock_data['items']);

    // Add deletion record to transactions
    if (!empty($item_name)) {
        $stock_data['transactions'][] = [
            'date' => date('Y-m-d H:i:s'),
            'item_name' => $item_name,
            'type' => 'deleted',
            'quantity' => 0,
            'updated_by' => $_SESSION['username'],
            'department' => $_SESSION['department']
        ];
    }

    // Save updated stock data
    file_put_contents($stock_file, json_encode($stock_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/stock-management.php?message=item_deleted');
    exit;
}

// If no valid item ID, redirect to stock management page
header('Location: ../frontend/stock-management.php');
exit;
?>
