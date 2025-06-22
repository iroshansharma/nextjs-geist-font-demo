<?php
require_once 'config.php';

// Insert default roles if they don't exist
$roles = [
    'Super User Admin',
    'Admin User',
    'Normal User'
];

foreach ($roles as $role) {
    $stmt = $mysqli->prepare("INSERT IGNORE INTO roles (role_name) VALUES (?)");
    $stmt->bind_param("s", $role);
    $stmt->execute();
}

// Insert demo users if they don't exist
$users = [
    ['superadmin', 'SuperAdmin123!', 1],  // Super User Admin
    ['adminuser', 'AdminUser123!', 2],    // Admin User
    ['normaluser', 'NormalUser123!', 3]   // Normal User
];

$stmt = $mysqli->prepare("INSERT IGNORE INTO users (username, password_hash, role_id) VALUES (?, ?, ?)");
foreach ($users as $user) {
    $password_hash = password_hash($user[1], PASSWORD_DEFAULT);
    $stmt->bind_param("ssi", $user[0], $password_hash, $user[2]);
    $stmt->execute();
}

echo "Database initialized successfully with demo users!\n";
?>
