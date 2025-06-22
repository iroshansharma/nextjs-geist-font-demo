<?php
require_once 'auth.php';

// Check if user is logged in and is super admin
if (!is_logged_in() || !has_role(1)) {
    header('Location: ../frontend/index.php');
    exit;
}

$user_id = (int)($_GET['id'] ?? 0);

if ($user_id > 0) {
    // Read current users
    $users_file = __DIR__ . '/users.json';
    $users_json = file_get_contents($users_file);
    $users_data = json_decode($users_json, true);

    // Remove user (except super admin)
    $users_data['users'] = array_filter($users_data['users'], function($user) use ($user_id) {
        return $user['id'] !== $user_id || $user['role_id'] === 1;
    });

    // Reindex array
    $users_data['users'] = array_values($users_data['users']);

    // Save updated users data
    file_put_contents($users_file, json_encode($users_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/dashboard-super.php?message=user_deleted');
    exit;
}

// If no valid user ID, redirect to dashboard
header('Location: ../frontend/dashboard-super.php');
exit;
?>
