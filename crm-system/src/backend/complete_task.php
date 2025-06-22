<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $task_id = (int)($_GET['id'] ?? 0);

    if (empty($task_id)) {
        header('Location: ../frontend/task-management.php?error=Invalid task ID');
        exit;
    }

    // Read tasks data
    $tasks_file = __DIR__ . '/tasks.json';
    $tasks_json = file_get_contents($tasks_file);
    $tasks_data = json_decode($tasks_json, true);

    // Find and update task
    $task_found = false;
    $task_owner = null;
    foreach ($tasks_data['tasks'] as &$task) {
        if ($task['id'] === $task_id) {
            $task_owner = $task['assigned_to'];
            $task['status'] = 'completed';
            $task['completed_by'] = $_SESSION['username'];
            $task['completed_at'] = date('Y-m-d H:i:s');
            $task['updated_by'] = $_SESSION['username'];
            $task['updated_at'] = date('Y-m-d H:i:s');
            $task_found = true;
            break;
        }
    }

    if (!$task_found) {
        header('Location: ../frontend/task-management.php?error=Task not found');
        exit;
    }

    // Save updated tasks data
    if (file_put_contents($tasks_file, json_encode($tasks_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/task-management.php?error=Failed to update task');
        exit;
    }

    // Add notification for task creator
    $notifications_file = __DIR__ . '/notifications.json';
    $notifications_data = [];
    
    if (file_exists($notifications_file)) {
        $notifications_json = file_get_contents($notifications_file);
        $notifications_data = json_decode($notifications_json, true);
    }

    if (!isset($notifications_data['notifications'])) {
        $notifications_data['notifications'] = [];
    }

    // Notify task creator
    $notifications_data['notifications'][] = [
        'user_id' => $task_owner,
        'type' => 'task_completed',
        'message' => "Task #{$task_id} has been marked as completed by " . $_SESSION['username'],
        'created_at' => date('Y-m-d H:i:s'),
        'read' => false,
        'task_id' => $task_id
    ];

    file_put_contents($notifications_file, json_encode($notifications_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/task-management.php?message=Task marked as completed');
    exit;
}

// If not GET request, redirect to task management page
header('Location: ../frontend/task-management.php');
exit;
?>
