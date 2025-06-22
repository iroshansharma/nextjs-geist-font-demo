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

    // Find task and store its data for archiving
    $task_found = false;
    $deleted_task = null;
    $new_tasks = [];

    foreach ($tasks_data['tasks'] as $task) {
        if ($task['id'] === $task_id) {
            $task_found = true;
            $deleted_task = $task;
        } else {
            $new_tasks[] = $task;
        }
    }

    if (!$task_found) {
        header('Location: ../frontend/task-management.php?error=Task not found');
        exit;
    }

    // Update tasks array
    $tasks_data['tasks'] = $new_tasks;

    // Save updated tasks data
    if (file_put_contents($tasks_file, json_encode($tasks_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/task-management.php?error=Failed to delete task');
        exit;
    }

    // Archive the deleted task
    $archives_file = __DIR__ . '/archived_tasks.json';
    $archives_data = [];
    
    if (file_exists($archives_file)) {
        $archives_json = file_get_contents($archives_file);
        $archives_data = json_decode($archives_json, true);
    }

    if (!isset($archives_data['tasks'])) {
        $archives_data['tasks'] = [];
    }

    // Add deletion metadata to the task
    $deleted_task['deleted_by'] = $_SESSION['username'];
    $deleted_task['deleted_at'] = date('Y-m-d H:i:s');
    $deleted_task['deletion_reason'] = 'Manual deletion by ' . $_SESSION['username'];

    $archives_data['tasks'][] = $deleted_task;

    // Save archived data
    file_put_contents($archives_file, json_encode($archives_data, JSON_PRETTY_PRINT));

    // Add notification for task owner
    $notifications_file = __DIR__ . '/notifications.json';
    $notifications_data = [];
    
    if (file_exists($notifications_file)) {
        $notifications_json = file_get_contents($notifications_file);
        $notifications_data = json_decode($notifications_json, true);
    }

    if (!isset($notifications_data['notifications'])) {
        $notifications_data['notifications'] = [];
    }

    // Notify task owner
    $notifications_data['notifications'][] = [
        'user_id' => $deleted_task['assigned_to'],
        'type' => 'task_deleted',
        'message' => "Task '{$deleted_task['title']}' has been deleted by " . $_SESSION['username'],
        'created_at' => date('Y-m-d H:i:s'),
        'read' => false
    ];

    file_put_contents($notifications_file, json_encode($notifications_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/task-management.php?message=Task deleted successfully');
    exit;
}

// If not GET request, redirect to task management page
header('Location: ../frontend/task-management.php');
exit;
?>
