<?php
require_once 'auth.php';

// Check if user is logged in and is super admin
if (!is_logged_in() || !has_role(1)) {
    header('Location: ../frontend/index.php');
    exit;
}

$entry_id = (int)($_GET['id'] ?? 0);

if ($entry_id > 0) {
    // Read current salary data
    $salary_file = __DIR__ . '/salary.json';
    $salary_json = file_get_contents($salary_file);
    $salary_data = json_decode($salary_json, true);

    // Find entry details before deletion
    $deleted_entry = null;
    foreach ($salary_data['salary_data'] as $entry) {
        if ($entry['id'] === $entry_id) {
            $deleted_entry = $entry;
            break;
        }
    }

    if ($deleted_entry) {
        // Remove entry
        $salary_data['salary_data'] = array_filter($salary_data['salary_data'], function($entry) use ($entry_id) {
            return $entry['id'] !== $entry_id;
        });

        // Reindex array
        $salary_data['salary_data'] = array_values($salary_data['salary_data']);

        // Add deletion record to history
        $salary_data['salary_history'][] = [
            'date' => date('Y-m-d H:i:s'),
            'action' => 'deleted',
            'entry_id' => $entry_id,
            'user_id' => $deleted_entry['user_id'],
            'month' => $deleted_entry['month'],
            'base_salary' => $deleted_entry['base_salary'],
            'bonus' => $deleted_entry['bonus'],
            'performed_by' => $_SESSION['username']
        ];

        // Save updated salary data
        file_put_contents($salary_file, json_encode($salary_data, JSON_PRETTY_PRINT));

        header('Location: ../frontend/salary-management.php?message=entry_deleted');
        exit;
    }
}

// If no valid entry ID or entry not found, redirect to salary management page
header('Location: ../frontend/salary-management.php?error=entry_not_found');
exit;
?>
