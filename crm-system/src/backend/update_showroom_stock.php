<?php
require_once 'auth.php';

// Check if user is logged in and is showroom user
if (!is_logged_in() || !($_SESSION['role_id'] === 3 && $_SESSION['department'] === 'showroom')) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $transaction_type = $_POST['transaction_type'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 0);
    $notes = $_POST['notes'] ?? '';

    // Validate input
    if (empty($item_id) || empty($transaction_type) || $quantity <= 0) {
        header('Location: ../frontend/showroom-stock.php?error=invalid_input');
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
            // Check if enough stock for sale
            if ($transaction_type === 'out' && $item['quantity'] < $quantity) {
                header('Location: ../frontend/showroom-stock.php?error=insufficient_stock');
                exit;
            }

            // Update quantity based on transaction type
            if ($transaction_type === 'out') {
                $item['quantity'] -= $quantity;
            } else { // return
                $item['quantity'] += $quantity;
            }

            $item['last_updated'] = date('Y-m-d H:i:s');

            // Add transaction record
            $stock_data['transactions'][] = [
                'date' => date('Y-m-d H:i:s'),
                'item_name' => $item['name'],
                'type' => $transaction_type,
                'quantity' => $quantity,
                'notes' => $notes,
                'updated_by' => $_SESSION['username'],
                'department' => 'showroom'
            ];

            $item_found = true;
            break;
        }
    }

    if (!$item_found) {
        header('Location: ../frontend/showroom-stock.php?error=item_not_found');
        exit;
    }

    // Save updated stock data
    file_put_contents($stock_file, json_encode($stock_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/showroom-stock.php?message=stock_updated');
    exit;
}

// If not POST request, redirect to showroom stock page
header('Location: ../frontend/showroom-stock.php');
exit;
?>
