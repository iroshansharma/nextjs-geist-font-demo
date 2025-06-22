<?php
require_once 'auth.php';

// Check if user is logged in and is super admin
if (!is_logged_in() || !has_role(1)) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entry_id = (int)($_POST['entry_id'] ?? 0);
    $user_id = (int)($_POST['user_id'] ?? 0);
    $month = $_POST['month'] ?? '';
    $base_salary = (float)($_POST['base_salary'] ?? 0);
    $bonus = (float)($_POST['bonus'] ?? 0);
    $notes = $_POST['notes'] ?? '';

    // Validate input
    if (empty($entry_id) || empty($user_id) || empty($month) || $base_salary <= 0) {
        header('Location: ../frontend/edit_salary.php?id=' . $entry_id . '&error=invalid_input');
        exit;
    }

    // Read current salary data
    $salary_file = __DIR__ . '/salary.json';
    $salary_json = file_get_contents($salary_file);
    $salary_data = json_decode($salary_json, true);

    // Find and update entry
    $entry_found = false;
    foreach ($salary_data['salary_data'] as &$entry) {
        if ($entry['id'] === $entry_id) {
            // Check if month is being changed and if it conflicts with existing entry
            if ($month !== $entry['month']) {
                foreach ($salary_data['salary_data'] as $other_entry) {
                    if ($other_entry['id'] !== $entry_id && 
                        $other_entry['user_id'] === $user_id && 
                        $other_entry['month'] === $month) {
                        header('Location: ../frontend/edit_salary.php?id=' . $entry_id . '&error=month_exists');
                        exit;
                    }
                }
            }

            // Store old values for history
            $old_values = [
                'base_salary' => $entry['base_salary'],
                'bonus' => $entry['bonus']
            ];

            // Update entry
            $entry['user_id'] = $user_id;
            $entry['month'] = $month;
            $entry['base_salary'] = $base_salary;
            $entry['bonus'] = $bonus;
            $entry['notes'] = $notes;
            $entry['updated_by'] = $_SESSION['username'];
            $entry['updated_at'] = date('Y-m-d H:i:s');

            // Add to salary history
            $salary_data['salary_history'][] = [
                'date' => date('Y-m-d H:i:s'),
                'action' => 'updated',
                'entry_id' => $entry_id,
                'user_id' => $user_id,
                'month' => $month,
                'base_salary' => $base_salary,
                'bonus' => $bonus,
                'old_base_salary' => $old_values['base_salary'],
                'old_bonus' => $old_values['bonus'],
                'performed_by' => $_SESSION['username']
            ];

            $entry_found = true;
            break;
        }
    }

    if (!$entry_found) {
        header('Location: ../frontend/salary-management.php?error=entry_not_found');
        exit;
    }

    // Save updated salary data
    file_put_contents($salary_file, json_encode($salary_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/salary-management.php?message=entry_updated');
    exit;
}

// If not POST request, redirect to salary management page
header('Location: ../frontend/salary-management.php');
exit;
?>
