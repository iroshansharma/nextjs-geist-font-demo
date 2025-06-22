<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $source = $_POST['source'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Validate input
    if (empty($customer_id) || empty($name) || empty($email) || empty($phone) || empty($source)) {
        header('Location: ../frontend/edit_customer.php?id=' . $customer_id . '&error=Please fill in all required fields');
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../frontend/edit_customer.php?id=' . $customer_id . '&error=Invalid email format');
        exit;
    }

    // Read customers data
    $customers_file = __DIR__ . '/customers.json';
    $customers_json = file_get_contents($customers_file);
    $customers_data = json_decode($customers_json, true);

    // Check if email already exists (except for current customer)
    foreach ($customers_data['customers'] as $customer) {
        if ($customer['email'] === $email && $customer['id'] !== $customer_id) {
            header('Location: ../frontend/edit_customer.php?id=' . $customer_id . '&error=Email already exists');
            exit;
        }
    }

    // Find and update customer
    $customer_found = false;
    $old_data = null;
    foreach ($customers_data['customers'] as &$customer) {
        if ($customer['id'] === $customer_id) {
            // Store old data for history
            $old_data = [
                'name' => $customer['name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'source' => $customer['source'],
                'address' => $customer['address'],
                'notes' => $customer['notes']
            ];

            // Update customer data
            $customer['name'] = $name;
            $customer['email'] = $email;
            $customer['phone'] = $phone;
            $customer['address'] = $address;
            $customer['source'] = $source;
            $customer['notes'] = $notes;
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

    // Compare old and new data to track changes
    $changes = [];
    foreach ($old_data as $key => $value) {
        if ($value !== ${$key}) {
            $changes[$key] = [
                'from' => $value,
                'to' => ${$key}
            ];
        }
    }

    if (!empty($changes)) {
        $customers_data['history'][] = [
            'action' => 'updated',
            'customer_id' => $customer_id,
            'performed_by' => $_SESSION['username'],
            'department' => $_SESSION['department'] ?? 'office',
            'timestamp' => date('Y-m-d H:i:s'),
            'details' => [
                'changes' => $changes
            ]
        ];
    }

    // Save updated customers data
    if (file_put_contents($customers_file, json_encode($customers_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/edit_customer.php?id=' . $customer_id . '&error=Failed to save customer data');
        exit;
    }

    header('Location: ../frontend/view_customer.php?id=' . $customer_id . '&message=Customer updated successfully');
    exit;
}

// If not POST request, redirect to customer management page
header('Location: ../frontend/customer-management.php');
exit;
?>
