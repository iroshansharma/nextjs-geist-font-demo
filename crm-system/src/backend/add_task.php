<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $assigned_to = (int)($_POST['assigned_to'] ?? 0);
    $due_date = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';

    // Validate input
    if (empty($title) || empty($description) || empty($assigned_to) || empty($due_date)) {
        header('Location: ../frontend/task-management.php?error=Please fill in all required fields');
        exit;
    }

    // Validate priority
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        header('Location: ../frontend/task-management.php?error=Invalid priority level');
        exit;
    }

    // Read tasks data
    $tasks_file = __DIR__ . '/tasks.json';
    $tasks_json = file_get_contents($tasks_file);
    $tasks_data = json_decode($tasks_json, true);

    if (!isset($tasks_data['tasks'])) {
        $tasks_data['tasks'] = [];
    }

    // Get next task ID
    $next_id = ($tasks_data['last_id'] ?? 0) + 1;
    $tasks_data['last_id'] = $next_id;

    // Create new task
    $new_task = [
        'id' => $next_id,
        'title' => $title,
        'description' => $description,
        'assigned_to' => $assigned_to,
        'due_date' => $due_date,
        'priority' => $priority,
        'status' => 'pending',
        'created_by' => $_SESSION['username'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_by' => $_SESSION['username'],
        'updated_at' => date('Y-m-d H:i:s'),
        'department' => $_SESSION['department'] ?? 'office',
        'comments' => []
    ];

    // Add to tasks array
    $tasks_data['tasks'][] = $new_task;

    // Save updated tasks data
    if (file_put_contents($tasks_file, json_encode($tasks_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/task-management.php?error=Failed to save task');
        exit;
    }

    // Add notification for assigned user
    $notifications_file = __DIR__ . '/notifications.json';
    $notifications_data = [];
    
    if (file_exists($notifications_file)) {
        $notifications_json = file_get_contents($notifications_file);
        $notifications_data = json_decode($notifications_json, true);
    }

    if (!isset($notifications_data['notifications'])) {
        $notifications_data['notifications'] = [];
    }

    $notifications_data['notifications'][] = [
        'user_id' => $assigned_to,
        'type' => 'task_assigned',
        'message' => "You have been assigned a new task: $title",
        'created_at' => date('Y-m-d H:i:s'),
        'read' => false,
        'task_id' => $next_id
    ];

    file_put_contents($notifications_file, json_encode($notifications_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/task-management.php?message=Task added successfully');
    exit;
}

// If not POST request, redirect to task management page
header('Location: ../frontend/task-management.php');
exit;
?>
