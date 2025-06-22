<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2, 3])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $payment_id = (int)($_GET['id'] ?? 0);
    $status = $_GET['status'] ?? '';

    if (empty($payment_id) || empty($status)) {
        header('Location: ../frontend/customer-payments.php?error=Invalid parameters');
        exit;
    }

    // Validate status
    if (!in_array($status, ['pending', 'completed'])) {
        header('Location: ../frontend/customer-payments.php?error=Invalid status');
        exit;
    }

    // Read payments data
    $payments_file = __DIR__ . '/payments.json';
    $payments_json = file_get_contents($payments_file);
    $payments_data = json_decode($payments_json, true);

    // Find and update payment
    $payment_found = false;
    $customer_id = null;
    foreach ($payments_data['payments'] as &$payment) {
        if ($payment['id'] === $payment_id) {
            $customer_id = $payment['customer_id'];
            $payment['status'] = $status;
            $payment['updated_by'] = $_SESSION['username'];
            $payment['updated_at'] = date('Y-m-d H:i:s');
            if ($status === 'completed') {
                $payment['completed_by'] = $_SESSION['username'];
                $payment['completed_at'] = date('Y-m-d H:i:s');
            }
            $payment_found = true;
            break;
        }
    }

    if (!$payment_found) {
        header('Location: ../frontend/customer-payments.php?error=Payment not found');
        exit;
    }

    // Save updated payments data
    if (file_put_contents($payments_file, json_encode($payments_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/view_payment.php?id=' . $payment_id . '&error=Failed to update payment status');
        exit;
    }

    // Update customer history
    $customers_file = __DIR__ . '/customers.json';
    $customers_json = file_get_contents($customers_file);
    $customers_data = json_decode($customers_json, true);

    if (!isset($customers_data['history'])) {
        $customers_data['history'] = [];
    }

    $customers_data['history'][] = [
        'action' => 'payment_status_updated',
        'customer_id' => $customer_id,
        'payment_id' => $payment_id,
        'old_status' => $status === 'completed' ? 'pending' : 'completed',
        'new_status' => $status,
        'performed_by' => $_SESSION['username'],
        'department' => $_SESSION['department'] ?? 'showroom',
        'timestamp' => date('Y-m-d H:i:s')
    ];

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
        'type' => 'payment_status_updated',
        'message' => "Payment #$payment_id has been marked as " . ucfirst($status),
        'created_at' => date('Y-m-d H:i:s'),
        'read' => false,
        'payment_id' => $payment_id
    ];

    file_put_contents($notifications_file, json_encode($notifications_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/view_payment.php?id=' . $payment_id . '&message=Payment status updated successfully');
    exit;
}

// If not GET request, redirect to payments page
header('Location: ../frontend/customer-payments.php');
exit;
?>
