<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $project_id = (int)$_GET['id'];
    
    // Read projects data
    $projects_file = __DIR__ . '/projects.json';
    $projects_json = file_get_contents($projects_file);
    $projects_data = json_decode($projects_json, true);

    // Find project index
    $project_index = -1;
    $project = null;
    foreach ($projects_data['projects'] as $index => $p) {
        if ($p['id'] === $project_id) {
            $project_index = $index;
            $project = $p;
            break;
        }
    }

    if ($project_index === -1) {
        header('Location: ../frontend/project-management.php?error=Project not found');
        exit;
    }

    // Delete associated files
    if (!empty($project['files'])) {
        foreach ($project['files'] as $file_path) {
            $full_path = __DIR__ . '/../' . $file_path;
            if (file_exists($full_path)) {
                unlink($full_path);
            }
        }
    }

    // Remove project from array
    array_splice($projects_data['projects'], $project_index, 1);

    // Save updated data
    if (file_put_contents($projects_file, json_encode($projects_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/project-management.php?error=Failed to delete project');
        exit;
    }

    // Add notification
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
        'user_id' => $project['manager_id'],
        'type' => 'project_deleted',
        'message' => "Project '{$project['name']}' has been deleted by " . $_SESSION['username'],
        'created_at' => date('Y-m-d H:i:s'),
        'read' => false
    ];

    file_put_contents($notifications_file, json_encode($notifications_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/project-management.php?message=Project deleted successfully');
    exit;
}

// If not GET request or no ID provided, redirect to project management page
header('Location: ../frontend/project-management.php');
exit;
?>
