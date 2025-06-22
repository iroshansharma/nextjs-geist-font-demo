<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($username, $password)) {
        // Successful login
        $role_id = get_user_role();
        
        // Redirect based on role
        switch ($role_id) {
            case 1: // Super User Admin
                header('Location: ../frontend/dashboard-super.php');
                break;
            case 2: // Admin User
                header('Location: ../frontend/dashboard-admin.php');
                break;
            case 3: // Normal User
                header('Location: ../frontend/dashboard-user.php');
                break;
            default:
                header('Location: ../frontend/index.php?error=invalid_role');
        }
        exit;
    } else {
        // Failed login
        header('Location: ../frontend/index.php?error=invalid_credentials');
        exit;
    }
} else {
    // If not POST request, redirect to login page
    header('Location: ../frontend/index.php');
    exit;
}
?>
