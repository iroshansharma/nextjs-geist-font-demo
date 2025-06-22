<?php
require_once 'auth.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !has_role(2)) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    // Validate input
    if (empty($user_id) || empty($title) || empty($description) || empty($due_date)) {
        header('Location: ../frontend/dashboard-admin.php?error=invalid_input');
        exit;
    }

    // Read current tasks
    $tasks_file = __DIR__ . '/tasks.json';
    $tasks_json = file_get_contents($tasks_file);
    $tasks_data = json_decode($tasks_json, true);

    // Get next task ID
    $max_id = 0;
    foreach ($tasks_data['tasks'] as $task) {
        $max_id = max($max_id, $task['id']);
    }
    $new_id = $max_id + 1;

    // Add new task
    $tasks_data['tasks'][] = [
        'id' => $new_id,
        'user_id' => $user_id,
        'title' => $title,
        'description' => $description,
        'due_date' => $due_date,
        'status' => 'pending',
        'assigned_by' => $_SESSION['user_id'],
        'assigned_at' => date('Y-m-d H:i:s'),
        'comments' => []
    ];

    // Save updated tasks data
    file_put_contents($tasks_file, json_encode($tasks_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/dashboard-admin.php?message=task_assigned');
    exit;
}

// If not POST request, redirect to dashboard
header('Location: ../frontend/dashboard-admin.php');
exit;
?>
