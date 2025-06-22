<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_id = (int)($_POST['invoice_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? '';
    $reference_number = $_POST['reference_number'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Validate input
    if (empty($invoice_id) || empty($amount) || empty($payment_method)) {
        header('Location: ../frontend/record-payment.php?invoice_id=' . $invoice_id . '&error=Please fill in all required fields');
        exit;
    }

    // Read sales data
    $sales_file = __DIR__ . '/sales.json';
    $sales_json = file_get_contents($sales_file);
    $sales_data = json_decode($sales_json, true);

    // Find and update sale
    $sale_found = false;
    foreach ($sales_data['sales'] as &$sale) {
        if ($sale['id'] === $invoice_id) {
            // Validate payment amount
            $balance = $sale['total_amount'] - ($sale['paid_amount'] ?? 0);
            if ($amount > $balance) {
                header('Location: ../frontend/record-payment.php?invoice_id=' . $invoice_id . '&error=Payment amount cannot exceed balance');
                exit;
            }

            // Initialize payment history if not exists
            if (!isset($sale['payment_history'])) {
                $sale['payment_history'] = [];
            }

            // Add payment record
            $payment_record = [
                'date' => date('Y-m-d H:i:s'),
                'amount' => $amount,
                'method' => $payment_method,
                'reference_number' => $reference_number,
                'notes' => $notes,
                'recorded_by' => $_SESSION['username']
            ];
            $sale['payment_history'][] = $payment_record;

            // Update paid amount
            $sale['paid_amount'] = ($sale['paid_amount'] ?? 0) + $amount;

            // Update payment status
            if ($sale['paid_amount'] >= $sale['total_amount']) {
                $sale['payment_status'] = 'paid';
            } elseif ($sale['paid_amount'] > 0) {
                $sale['payment_status'] = 'partial';
            }

            $sale['updated_by'] = $_SESSION['username'];
            $sale['updated_at'] = date('Y-m-d H:i:s');

            $sale_found = true;
            break;
        }
    }

    if (!$sale_found) {
        header('Location: ../frontend/sales-management.php?error=Invoice not found');
        exit;
    }

    // Save updated sales data
    if (file_put_contents($sales_file, json_encode($sales_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/record-payment.php?invoice_id=' . $invoice_id . '&error=Failed to save payment');
        exit;
    }

    // Update customer payment history
    $customers_file = __DIR__ . '/customers.json';
    $customers_json = file_get_contents($customers_file);
    $customers_data = json_decode($customers_json, true);

    foreach ($customers_data['customers'] as &$customer) {
        if ($customer['id'] === $sale['customer_id']) {
            if (!isset($customer['payment_history'])) {
                $customer['payment_history'] = [];
            }
            $customer['payment_history'][] = [
                'invoice_id' => $invoice_id,
                'amount' => $amount,
                'date' => date('Y-m-d H:i:s'),
                'method' => $payment_method
            ];
            $customer['total_payments'] = ($customer['total_payments'] ?? 0) + $amount;
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
        'user_id' => $sale['customer_id'],
        'type' => 'payment_received',
        'message' => "Payment of ₹" . number_format($amount, 2) . " received for invoice " . $sale['invoice_number'],
        'created_at' => date('Y-m-d H:i:s'),
        'read' => false,
        'invoice_id' => $invoice_id
    ];

    file_put_contents($notifications_file, json_encode($notifications_data, JSON_PRETTY_PRINT));

    // Redirect to invoice view
    header('Location: ../frontend/view-invoice.php?id=' . $invoice_id . '&message=Payment recorded successfully');
    exit;
}

// If not POST request, redirect to sales management page
header('Location: ../frontend/sales-management.php');
exit;
?>
