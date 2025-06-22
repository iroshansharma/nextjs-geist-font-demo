<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['id', 'name', 'location', 'start_date', 'end_date', 'manager_id', 'client_name', 'status'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            header('Location: ../frontend/edit-project.php?id=' . $_POST['id'] . '&error=All fields are required');
            exit;
        }
    }

    // Validate dates
    $start_date = strtotime($_POST['start_date']);
    $end_date = strtotime($_POST['end_date']);
    
    if ($end_date < $start_date) {
        header('Location: ../frontend/edit-project.php?id=' . $_POST['id'] . '&error=End date cannot be earlier than start date');
        exit;
    }

    // Read projects data
    $projects_file = __DIR__ . '/projects.json';
    $projects_json = file_get_contents($projects_file);
    $projects_data = json_decode($projects_json, true);

    // Find project
    $project_index = -1;
    $project = null;
    foreach ($projects_data['projects'] as $index => $p) {
        if ($p['id'] === (int)$_POST['id']) {
            $project_index = $index;
            $project = $p;
            break;
        }
    }

    if ($project_index === -1) {
        header('Location: ../frontend/project-management.php?error=Project not found');
        exit;
    }

    // Handle file uploads
    $upload_dir = __DIR__ . '/../uploads/projects';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $current_files = $project['files'] ?? [];

    // Remove files if requested
    if (!empty($_POST['remove_files'])) {
        foreach ($_POST['remove_files'] as $index) {
            $file_path = __DIR__ . '/../' . $current_files[$index];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            unset($current_files[$index]);
        }
        $current_files = array_values($current_files); // Reindex array
    }

    // Add new files
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['files']['name'][$key];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validate file extension
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
            if (!in_array($file_ext, $allowed_extensions)) {
                header('Location: ../frontend/edit-project.php?id=' . $_POST['id'] . '&error=Invalid file type');
                exit;
            }

            // Generate unique filename
            $new_filename = uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . '/' . $new_filename;

            if (move_uploaded_file($tmp_name, $upload_path)) {
                $current_files[] = 'uploads/projects/' . $new_filename;
            }
        }
    }

    // Check if manager has changed
    $manager_changed = $project['manager_id'] !== (int)$_POST['manager_id'];
    $old_manager_id = $project['manager_id'];

    // Update project data
    $projects_data['projects'][$project_index] = array_merge($project, [
        'name' => $_POST['name'],
        'location' => $_POST['location'],
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'],
        'manager_id' => (int)$_POST['manager_id'],
        'client_name' => $_POST['client_name'],
        'status' => $_POST['status'],
        'remarks' => $_POST['remarks'] ?? '',
        'files' => $current_files,
        'updated_by' => $_SESSION['username'],
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    // Save updated data
    if (file_put_contents($projects_file, json_encode($projects_data, JSON_PRETTY_PRINT)) === false) {
        header('Location: ../frontend/edit-project.php?id=' . $_POST['id'] . '&error=Failed to update project');
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

    // Notify old manager if changed
    if ($manager_changed) {
        $notifications_data['notifications'][] = [
            'user_id' => $old_manager_id,
            'type' => 'project_reassigned',
            'message' => "You have been removed as manager from project: " . $_POST['name'],
            'created_at' => date('Y-m-d H:i:s'),
            'read' => false,
            'project_id' => (int)$_POST['id']
        ];

        // Notify new manager
        $notifications_data['notifications'][] = [
            'user_id' => (int)$_POST['manager_id'],
            'type' => 'project_assigned',
            'message' => "You have been assigned as manager for project: " . $_POST['name'],
            'created_at' => date('Y-m-d H:i:s'),
            'read' => false,
            'project_id' => (int)$_POST['id']
        ];
    }

    file_put_contents($notifications_file, json_encode($notifications_data, JSON_PRETTY_PRINT));

    // Redirect to project management page
    header('Location: ../frontend/project-management.php?message=Project updated successfully');
    exit;
}

// If not POST request, redirect to project management page
header('Location: ../frontend/project-management.php');
exit;
?>
