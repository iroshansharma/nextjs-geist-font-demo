<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2, 3])) { // 3 is showroom staff
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $payment_type = $_POST['payment_type'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Validate input
    if (empty($customer_id) || empty($amount) || empty($payment_type)) {
        header('Location: ../frontend/customer-payments.php?error=Please fill in all required fields');
        exit;
    }

    // Validate payment type
    if (!in_array($payment_type, ['full', 'partial', 'installment'])) {
        header('Location: ../frontend/customer-payments.php?error=Invalid payment type');
        exit;
    }

    // Read payments data
    $payments_file = __DIR__ . '/payments.json';
    $payments_data = [];
    
    if (file_exists($payments_file)) {
        $payments_json = file_get_contents($payments_file);
        $payments_data = json_decode($payments_json, true);
    }

    if (!isset($payments_data['payments'])) {
        $payments_data['payments'] = [];
    }

    // Get next payment ID
    $next_id = ($payments_data['last_id'] ?? 0) + 1;
    $payments_data['last_id'] = $next_id;

    // Generate invoice number
    $invoice_number = 'INV-' . date('Y') . sprintf('%06d', $next_id);

    // Create new payment record
    $new_payment = [
        'id' => $next_id,
        'invoice_number' => $invoice_number,
        'customer_id' => $customer_id,
        'amount' => $amount,
        'payment_type' => $payment_type,
        'notes' => $notes,
        'status' => 'completed',
        'created_by' => $_SESSION['username'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_by' => $_SESSION['username'],
        'updated_at' => date('Y-m-d H:i:s'),
        'department' => $_SESSION['department'] ?? 'showroom'
    ];

    // Add to payments array
    $payments_data['payments'][] = $new_payment;

    // Save updated payments data
    if (file_put_contents($payments_file, json_encode($payments_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/customer-payments.php?error=Failed to save payment');
        exit;
    }

    // Update customer's payment history
    $customers_file = __DIR__ . '/customers.json';
    $customers_json = file_get_contents($customers_file);
    $customers_data = json_decode($customers_json, true);

    foreach ($customers_data['customers'] as &$customer) {
        if ($customer['id'] === $customer_id) {
            if (!isset($customer['payments'])) {
                $customer['payments'] = [];
            }
            $customer['payments'][] = [
                'payment_id' => $next_id,
                'amount' => $amount,
                'type' => $payment_type,
                'date' => date('Y-m-d H:i:s')
            ];
            $customer['total_payments'] = ($customer['total_payments'] ?? 0) + $amount;
            break;
        }
    }

    file_put_contents($customers_file, json_encode($customers_data, JSON_PRETTY_PRINT));

    // Add to history
    if (!isset($customers_data['history'])) {
        $customers_data['history'] = [];
    }

    $customers_data['history'][] = [
        'action' => 'payment_added',
        'customer_id' => $customer_id,
        'performed_by' => $_SESSION['username'],
        'department' => $_SESSION['department'] ?? 'showroom',
        'timestamp' => date('Y-m-d H:i:s'),
        'details' => [
            'payment_id' => $next_id,
            'amount' => $amount,
            'type' => $payment_type,
            'invoice_number' => $invoice_number
        ]
    ];

    file_put_contents($customers_file, json_encode($customers_data, JSON_PRETTY_PRINT));

    // Redirect to invoice generation
    header('Location: ../frontend/generate_invoice.php?payment_id=' . $next_id);
    exit;
}

// If not POST request, redirect to payments page
header('Location: ../frontend/customer-payments.php');
exit;
?>
