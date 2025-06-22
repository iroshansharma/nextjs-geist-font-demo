<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = (int)($_POST['task_id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $assigned_to = (int)($_POST['assigned_to'] ?? 0);
    $due_date = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $status = $_POST['status'] ?? '';

    // Validate input
    if (empty($task_id) || empty($title) || empty($description) || empty($assigned_to) || empty($due_date) || empty($priority) || empty($status)) {
        header('Location: ../frontend/edit_task.php?id=' . $task_id . '&error=Please fill in all required fields');
        exit;
    }

    // Validate priority
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        header('Location: ../frontend/edit_task.php?id=' . $task_id . '&error=Invalid priority level');
        exit;
    }

    // Validate status
    if (!in_array($status, ['pending', 'completed'])) {
        header('Location: ../frontend/edit_task.php?id=' . $task_id . '&error=Invalid status');
        exit;
    }

    // Read tasks data
    $tasks_file = __DIR__ . '/tasks.json';
    $tasks_json = file_get_contents($tasks_file);
    $tasks_data = json_decode($tasks_json, true);

    // Find and update task
    $task_found = false;
    $old_assigned_to = null;
    foreach ($tasks_data['tasks'] as &$task) {
        if ($task['id'] === $task_id) {
            $old_assigned_to = $task['assigned_to'];
            
            // Store old data for history
            $old_data = [
                'title' => $task['title'],
                'description' => $task['description'],
                'assigned_to' => $task['assigned_to'],
                'due_date' => $task['due_date'],
                'priority' => $task['priority'],
                'status' => $task['status']
            ];

            // Update task data
            $task['title'] = $title;
            $task['description'] = $description;
            $task['assigned_to'] = $assigned_to;
            $task['due_date'] = $due_date;
            $task['priority'] = $priority;
            $task['status'] = $status;
            $task['updated_by'] = $_SESSION['username'];
            $task['updated_at'] = date('Y-m-d H:i:s');

            // If status changed to completed
            if ($status === 'completed' && $old_data['status'] !== 'completed') {
                $task['completed_by'] = $_SESSION['username'];
                $task['completed_at'] = date('Y-m-d H:i:s');
            }

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
        header('Location: ../frontend/edit_task.php?id=' . $task_id . '&error=Failed to update task');
        exit;
    }

    // Add notifications
    $notifications_file = __DIR__ . '/notifications.json';
    $notifications_data = [];
    
    if (file_exists($notifications_file)) {
        $notifications_json = file_get_contents($notifications_file);
        $notifications_data = json_decode($notifications_json, true);
    }

    if (!isset($notifications_data['notifications'])) {
        $notifications_data['notifications'] = [];
    }

    // If task was reassigned, notify new assignee
    if ($assigned_to !== $old_assigned_to) {
        $notifications_data['notifications'][] = [
            'user_id' => $assigned_to,
            'type' => 'task_assigned',
            'message' => "You have been assigned to task: $title",
            'created_at' => date('Y-m-d H:i:s'),
            'read' => false,
            'task_id' => $task_id
        ];

        // Notify previous assignee
        $notifications_data['notifications'][] = [
            'user_id' => $old_assigned_to,
            'type' => 'task_unassigned',
            'message' => "You have been unassigned from task: $title",
            'created_at' => date('Y-m-d H:i:s'),
            'read' => false,
            'task_id' => $task_id
        ];
    }

    // If task was marked as completed
    if ($status === 'completed' && $old_data['status'] !== 'completed') {
        $notifications_data['notifications'][] = [
            'user_id' => $assigned_to,
            'type' => 'task_completed',
            'message' => "Task '$title' has been marked as completed",
            'created_at' => date('Y-m-d H:i:s'),
            'read' => false,
            'task_id' => $task_id
        ];
    }

    file_put_contents($notifications_file, json_encode($notifications_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/task-management.php?message=Task updated successfully');
    exit;
}

// If not POST request, redirect to task management page
header('Location: ../frontend/task-management.php');
exit;
?>
