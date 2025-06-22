<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $source = $_POST['source'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Validate input
    if (empty($name) || empty($email) || empty($phone) || empty($source)) {
        header('Location: ../frontend/customer-management.php?error=Please fill in all required fields');
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../frontend/customer-management.php?error=Invalid email format');
        exit;
    }

    // Read current customers data
    $customers_file = __DIR__ . '/customers.json';
    $customers_json = file_get_contents($customers_file);
    $customers_data = json_decode($customers_json, true);

    // Check if email already exists
    foreach ($customers_data['customers'] as $customer) {
        if ($customer['email'] === $email) {
            header('Location: ../frontend/customer-management.php?error=Email already exists');
            exit;
        }
    }

    // Get next customer ID
    $new_id = ($customers_data['last_id'] ?? 0) + 1;
    $customers_data['last_id'] = $new_id;

    // Create new customer record
    $new_customer = [
        'id' => $new_id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'source' => $source,
        'notes' => $notes,
        'status' => 'active',
        'created_by' => $_SESSION['username'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_by' => $_SESSION['username'],
        'updated_at' => date('Y-m-d H:i:s'),
        'department' => $_SESSION['department'] ?? 'office',
        'follow_ups' => []
    ];

    // Add to customers array
    $customers_data['customers'][] = $new_customer;

    // Add to history
    if (!isset($customers_data['history'])) {
        $customers_data['history'] = [];
    }

    $customers_data['history'][] = [
        'action' => 'created',
        'customer_id' => $new_id,
        'performed_by' => $_SESSION['username'],
        'department' => $_SESSION['department'] ?? 'office',
        'timestamp' => date('Y-m-d H:i:s'),
        'details' => [
            'name' => $name,
            'email' => $email,
            'source' => $source
        ]
    ];

    // Save updated customers data
    if (file_put_contents($customers_file, json_encode($customers_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/customer-management.php?error=Failed to save customer data');
        exit;
    }

    header('Location: ../frontend/customer-management.php?message=Customer added successfully');
    exit;
}

// If not POST request, redirect to customer management page
header('Location: ../frontend/customer-management.php');
exit;
?>
