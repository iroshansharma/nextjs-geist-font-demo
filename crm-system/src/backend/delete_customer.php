<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $customer_id = (int)($_GET['id'] ?? 0);

    if (empty($customer_id)) {
        header('Location: ../frontend/customer-management.php?error=Invalid customer ID');
        exit;
    }

    // Read customers data
    $customers_file = __DIR__ . '/customers.json';
    $customers_json = file_get_contents($customers_file);
    $customers_data = json_decode($customers_json, true);

    // Find customer and store their data for history
    $customer_found = false;
    $deleted_customer = null;
    $new_customers = [];

    foreach ($customers_data['customers'] as $customer) {
        if ($customer['id'] === $customer_id) {
            $customer_found = true;
            $deleted_customer = $customer;
        } else {
            $new_customers[] = $customer;
        }
    }

    if (!$customer_found) {
        header('Location: ../frontend/customer-management.php?error=Customer not found');
        exit;
    }

    // Update customers array
    $customers_data['customers'] = $new_customers;

    // Add to history
    if (!isset($customers_data['history'])) {
        $customers_data['history'] = [];
    }

    $customers_data['history'][] = [
        'action' => 'deleted',
        'customer_id' => $customer_id,
        'performed_by' => $_SESSION['username'],
        'department' => $_SESSION['department'] ?? 'office',
        'timestamp' => date('Y-m-d H:i:s'),
        'details' => [
            'name' => $deleted_customer['name'],
            'email' => $deleted_customer['email'],
            'source' => $deleted_customer['source']
        ]
    ];

    // Save updated customers data
    if (file_put_contents($customers_file, json_encode($customers_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/customer-management.php?error=Failed to delete customer');
        exit;
    }

    // Create archived customer record
    $archived_customer = $deleted_customer;
    $archived_customer['deleted_by'] = $_SESSION['username'];
    $archived_customer['deleted_at'] = date('Y-m-d H:i:s');
    $archived_customer['department'] = $_SESSION['department'] ?? 'office';

    // Store archived customer
    $archives_file = __DIR__ . '/archived_customers.json';
    $archives_data = [];

    if (file_exists($archives_file)) {
        $archives_json = file_get_contents($archives_file);
        $archives_data = json_decode($archives_json, true);
    }

    if (!isset($archives_data['customers'])) {
        $archives_data['customers'] = [];
    }

    $archives_data['customers'][] = $archived_customer;

    // Save archived data
    file_put_contents($archives_file, json_encode($archives_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/customer-management.php?message=Customer deleted successfully');
    exit;
}

// If not GET request, redirect to customer management page
header('Location: ../frontend/customer-management.php');
exit;
?>
