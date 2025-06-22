<?php
session_start();

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// User login function
function login($username, $password) {
    $username = sanitize($username);
    
    // Read users from JSON file
    $users_json = file_get_contents(__DIR__ . '/users.json');
    $users_data = json_decode($users_json, true);
    
    foreach ($users_data['users'] as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['department'] = $user['department'] ?? 'office'; // Default to office for backward compatibility
            return true;
        }
    }
    return false;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Logout function
function logout() {
    session_unset();
    session_destroy();
}

// Check user role
function has_role($required_role_id) {
    if (!is_logged_in()) return false;
    return $_SESSION['role_id'] == $required_role_id;
}

// Get current user role id
function get_user_role() {
    return $_SESSION['role_id'] ?? null;
}
?>
