<?php
require_once 'auth.php';

// Check if user is logged in and is super admin
if (!is_logged_in() || !has_role(1)) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role_id = (int)($_POST['role_id'] ?? 3);
    $department = $_POST['department'] ?? '';

    // Validate input
    if (empty($username) || empty($password) || !in_array($role_id, [2, 3]) || 
        !in_array($department, ['office', 'showroom'])) {
        header('Location: ../frontend/dashboard-super.php?error=invalid_input');
        exit;
    }

    // Read current users
    $users_file = __DIR__ . '/users.json';
    $users_json = file_get_contents($users_file);
    $users_data = json_decode($users_json, true);

    // Check if username already exists
    foreach ($users_data['users'] as $user) {
        if ($user['username'] === $username) {
            header('Location: ../frontend/dashboard-super.php?error=username_exists');
            exit;
        }
    }

    // Get next user ID
    $max_id = 0;
    foreach ($users_data['users'] as $user) {
        $max_id = max($max_id, $user['id']);
    }
    $new_id = $max_id + 1;

    // Add new user
    $users_data['users'][] = [
        'id' => $new_id,
        'username' => $username,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role_id' => $role_id,
        'department' => $department,
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => $_SESSION['username']
    ];

    // Save updated users data
    file_put_contents($users_file, json_encode($users_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/dashboard-super.php?message=user_added');
    exit;
}

// If not POST request, redirect to dashboard
header('Location: ../frontend/dashboard-super.php');
exit;
?>
