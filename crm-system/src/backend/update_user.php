<?php
require_once 'auth.php';

// Check if user is logged in and is super admin
if (!is_logged_in() || !has_role(1)) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role_id = (int)($_POST['role_id'] ?? 3);
    $department = $_POST['department'] ?? '';

    // Validate input
    if (empty($username) || !in_array($role_id, [2, 3]) || 
        !in_array($department, ['office', 'showroom'])) {
        header('Location: ../frontend/edit_user.php?id=' . $user_id . '&error=invalid_input');
        exit;
    }

    // Read current users
    $users_file = __DIR__ . '/users.json';
    $users_json = file_get_contents($users_file);
    $users_data = json_decode($users_json, true);

    // Check if username already exists (except for current user)
    foreach ($users_data['users'] as $user) {
        if ($user['username'] === $username && $user['id'] !== $user_id) {
            header('Location: ../frontend/edit_user.php?id=' . $user_id . '&error=username_exists');
            exit;
        }
    }

    // Update user data
    foreach ($users_data['users'] as &$user) {
        if ($user['id'] === $user_id && $user['role_id'] !== 1) {
            $user['username'] = $username;
            $user['role_id'] = $role_id;
            $user['department'] = $department;
            
            // Update password only if a new one is provided
            if (!empty($password)) {
                $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            // Add update tracking
            $user['updated_at'] = date('Y-m-d H:i:s');
            $user['updated_by'] = $_SESSION['username'];
            break;
        }
    }

    // Save updated users data
    file_put_contents($users_file, json_encode($users_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/dashboard-super.php?message=user_updated');
    exit;
}

// If not POST request, redirect to dashboard
header('Location: ../frontend/dashboard-super.php');
exit;
?>
