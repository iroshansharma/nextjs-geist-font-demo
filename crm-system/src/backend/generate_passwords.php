<?php
$users = [
    [
        "id" => 1,
        "username" => "superadmin",
        "password_hash" => password_hash("SuperAdmin123!", PASSWORD_DEFAULT),
        "role_id" => 1
    ],
    [
        "id" => 2,
        "username" => "adminuser",
        "password_hash" => password_hash("AdminUser123!", PASSWORD_DEFAULT),
        "role_id" => 2
    ],
    [
        "id" => 3,
        "username" => "normaluser",
        "password_hash" => password_hash("NormalUser123!", PASSWORD_DEFAULT),
        "role_id" => 3
    ]
];

$data = ["users" => $users];
file_put_contents(__DIR__ . '/users.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Password hashes generated and saved successfully!\n";
?>
