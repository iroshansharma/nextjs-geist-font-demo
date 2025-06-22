<?php
session_start();
require_once 'config.php';

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// User login function
function login($username, $password) {
    global $pdo;
    $username = sanitize($username);

    $stmt = $pdo->prepare("SELECT id, username, password_hash, role_id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'];
        return true;
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
