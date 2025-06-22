<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $type = $_POST['type'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $next_date = $_POST['next_date'] ?? '';

    // Validate input
    if (empty($customer_id) || empty($type) || empty($notes)) {
        header('Location: ../frontend/view_customer.php?id=' . $customer_id . '&error=Please fill in all required fields');
        exit;
    }

    // Validate follow-up type
    if (!in_array($type, ['call', 'email', 'meeting', 'other'])) {
        header('Location: ../frontend/view_customer.php?id=' . $customer_id . '&error=Invalid follow-up type');
        exit;
    }

    // Read customers data
    $customers_file = __DIR__ . '/customers.json';
    $customers_json = file_get_contents($customers_file);
    $customers_data = json_decode($customers_json, true);

    // Find and update customer
    $customer_found = false;
    foreach ($customers_data['customers'] as &$customer) {
        if ($customer['id'] === $customer_id) {
            // Initialize follow_ups array if it doesn't exist
            if (!isset($customer['follow_ups'])) {
                $customer['follow_ups'] = [];
            }

            // Create new follow-up record
            $new_followup = [
                'id' => count($customer['follow_ups']) + 1,
                'type' => $type,
                'notes' => $notes,
                'next_date' => $next_date,
                'created_by' => $_SESSION['username'],
                'created_at' => date('Y-m-d H:i:s'),
                'department' => $_SESSION['department'] ?? 'office'
            ];

            // Add to follow-ups array
            $customer['follow_ups'][] = $new_followup;

            // Update customer's last contact info
            $customer['last_contact'] = [
                'date' => date('Y-m-d H:i:s'),
                'type' => $type,
                'by' => $_SESSION['username']
            ];

            // Update customer record
            $customer['updated_by'] = $_SESSION['username'];
            $customer['updated_at'] = date('Y-m-d H:i:s');

            $customer_found = true;
            break;
        }
    }

    if (!$customer_found) {
        header('Location: ../frontend/customer-management.php?error=Customer not found');
        exit;
    }

    // Add to history
    if (!isset($customers_data['history'])) {
        $customers_data['history'] = [];
    }

    $customers_data['history'][] = [
        'action' => 'follow_up_added',
        'customer_id' => $customer_id,
        'performed_by' => $_SESSION['username'],
        'department' => $_SESSION['department'] ?? 'office',
        'timestamp' => date('Y-m-d H:i:s'),
        'details' => [
            'type' => $type,
            'next_date' => $next_date
        ]
    ];

    // Save updated customers data
    if (file_put_contents($customers_file, json_encode($customers_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/view_customer.php?id=' . $customer_id . '&error=Failed to save follow-up');
        exit;
    }

    header('Location: ../frontend/view_customer.php?id=' . $customer_id . '&message=Follow-up added successfully');
    exit;
}

// If not POST request, redirect to customer management page
header('Location: ../frontend/customer-management.php');
exit;
?>
