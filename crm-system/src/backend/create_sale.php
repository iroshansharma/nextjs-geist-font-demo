<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $items = $_POST['items'] ?? [];
    $gst_rate = (float)($_POST['gst_rate'] ?? 0);
    $discount_percentage = (float)($_POST['discount_percentage'] ?? 0);
    $payment_status = $_POST['payment_status'] ?? '';
    $paid_amount = (float)($_POST['paid_amount'] ?? 0);
    $notes = $_POST['notes'] ?? '';

    // Validate input
    if (empty($customer_id) || empty($items)) {
        header('Location: ../frontend/create-invoice.php?error=Please fill in all required fields');
        exit;
    }

    // Calculate totals
    $subtotal = 0;
    foreach ($items as $item) {
        $quantity = (int)($item['quantity'] ?? 0);
        $price = (float)($item['price'] ?? 0);
        $subtotal += $quantity * $price;
    }

    $gst_amount = ($subtotal * $gst_rate) / 100;
    $discount_amount = ($subtotal * $discount_percentage) / 100;
    $total_amount = $subtotal + $gst_amount - $discount_amount;

    // Validate payment status and amount
    if ($payment_status === 'partial' && ($paid_amount <= 0 || $paid_amount >= $total_amount)) {
        header('Location: ../frontend/create-invoice.php?error=Invalid partial payment amount');
        exit;
    }

    if ($payment_status === 'paid') {
        $paid_amount = $total_amount;
    } elseif ($payment_status === 'unpaid') {
        $paid_amount = 0;
    }

    // Read sales data
    $sales_file = __DIR__ . '/sales.json';
    $sales_data = [];
    
    if (file_exists($sales_file)) {
        $sales_json = file_get_contents($sales_file);
        $sales_data = json_decode($sales_json, true);
    }

    if (!isset($sales_data['sales'])) {
        $sales_data['sales'] = [];
    }

    // Get next sale ID and invoice number
    $next_id = ($sales_data['last_id'] ?? 0) + 1;
    $sales_data['last_id'] = $next_id;
    $invoice_number = 'INV-' . date('Y') . sprintf('%06d', $next_id);

    // Create new sale record
    $new_sale = [
        'id' => $next_id,
        'invoice_number' => $invoice_number,
        'customer_id' => $customer_id,
        'items' => $items,
        'subtotal' => $subtotal,
        'gst_rate' => $gst_rate,
        'gst_amount' => $gst_amount,
        'discount_percentage' => $discount_percentage,
        'discount_amount' => $discount_amount,
        'total_amount' => $total_amount,
        'payment_status' => $payment_status,
        'paid_amount' => $paid_amount,
        'notes' => $notes,
        'created_by' => $_SESSION['username'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_by' => $_SESSION['username'],
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Add to sales array
    $sales_data['sales'][] = $new_sale;

    // Save updated sales data
    if (file_put_contents($sales_file, json_encode($sales_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/create-invoice.php?error=Failed to save sale');
        exit;
    }

    // Update customer's sales history
    $customers_file = __DIR__ . '/customers.json';
    $customers_json = file_get_contents($customers_file);
    $customers_data = json_decode($customers_json, true);

    foreach ($customers_data['customers'] as &$customer) {
        if ($customer['id'] === $customer_id) {
            if (!isset($customer['sales'])) {
                $customer['sales'] = [];
            }
            $customer['sales'][] = [
                'sale_id' => $next_id,
                'invoice_number' => $invoice_number,
                'amount' => $total_amount,
                'date' => date('Y-m-d H:i:s')
            ];
            $customer['total_sales'] = ($customer['total_sales'] ?? 0) + $total_amount;
            break;
        }
    }

    file_put_contents($customers_file, json_encode($customers_data, JSON_PRETTY_PRINT));

    // Add notification
    $notifications_file = __DIR__ . '/notifications.json';
    $notifications_data = [];
    
    if (file_exists($notifications_file)) {
        $notifications_json = file_get_contents($notifications_file);
        $notifications_data = json_decode($notifications_json, true);
    }

    if (!isset($notifications_data['notifications'])) {
        $notifications_data['notifications'] = [];
    }

    $notifications_data['notifications'][] = [
        'user_id' => $customer_id,
        'type' => 'new_sale',
        'message' => "New invoice created: $invoice_number for ₹" . number_format($total_amount, 2),
        'created_at' => date('Y-m-d H:i:s'),
        'read' => false,
        'sale_id' => $next_id
    ];

    file_put_contents($notifications_file, json_encode($notifications_data, JSON_PRETTY_PRINT));

    // Redirect to invoice view
    header('Location: ../frontend/view-invoice.php?id=' . $next_id . '&message=Sale recorded successfully');
    exit;
}

// If not POST request, redirect to create invoice page
header('Location: ../frontend/create-invoice.php');
exit;
?>
