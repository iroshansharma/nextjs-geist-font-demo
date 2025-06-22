<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $transaction_type = $_POST['transaction_type'] ?? '';

    // Validate input
    if (empty($item_id) || empty($name) || $quantity <= 0 || $price <= 0 || empty($transaction_type)) {
        header('Location: ../frontend/edit_stock.php?id=' . $item_id . '&error=invalid_input');
        exit;
    }

    // Read current stock data
    $stock_file = __DIR__ . '/stock.json';
    $stock_json = file_get_contents($stock_file);
    $stock_data = json_decode($stock_json, true);

    // Find and update item
    $item_found = false;
    foreach ($stock_data['items'] as &$item) {
        if ($item['id'] === $item_id) {
            $old_quantity = $item['quantity'];
            
            // Check if name already exists in other items
            foreach ($stock_data['items'] as $other_item) {
                if ($other_item['id'] !== $item_id && strtolower($other_item['name']) === strtolower($name)) {
                    header('Location: ../frontend/edit_stock.php?id=' . $item_id . '&error=name_exists');
                    exit;
                }
            }

            // Update item data
            $item['name'] = $name;
            $item['price'] = $price;
            $item['last_updated'] = date('Y-m-d H:i:s');

            // Handle different transaction types
            switch ($transaction_type) {
                case 'in':
                    $item['quantity'] += $quantity;
                    break;
                case 'out':
                    if ($item['quantity'] < $quantity) {
                        header('Location: ../frontend/edit_stock.php?id=' . $item_id . '&error=insufficient_stock');
                        exit;
                    }
                    $item['quantity'] -= $quantity;
                    break;
                case 'adjust':
                    $item['quantity'] = $quantity;
                    break;
            }

            // Add transaction record
            $stock_data['transactions'][] = [
                'date' => date('Y-m-d H:i:s'),
                'item_name' => $name,
                'type' => $transaction_type,
                'quantity' => $quantity,
                'updated_by' => $_SESSION['username'],
                'department' => $_SESSION['department'],
                'old_quantity' => $old_quantity,
                'new_quantity' => $item['quantity']
            ];

            $item_found = true;
            break;
        }
    }

    if (!$item_found) {
        header('Location: ../frontend/stock-management.php?error=item_not_found');
        exit;
    }

    // Save updated stock data
    file_put_contents($stock_file, json_encode($stock_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/stock-management.php?message=item_updated');
    exit;
}

// If not POST request, redirect to stock management page
header('Location: ../frontend/stock-management.php');
exit;
?>
