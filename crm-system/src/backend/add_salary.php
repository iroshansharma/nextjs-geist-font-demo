<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $month = $_POST['month'] ?? '';
    $base_salary = (float)($_POST['base_salary'] ?? 0);
    $bonus = (float)($_POST['bonus'] ?? 0);
    $notes = $_POST['notes'] ?? '';
    $calculate_from_attendance = isset($_POST['calculate_from_attendance']) && $_POST['calculate_from_attendance'] === 'yes';

    // Validate input
    if (empty($user_id) || empty($month) || ($base_salary <= 0 && !$calculate_from_attendance)) {
        header('Location: ../frontend/salary-management.php?error=invalid_input');
        exit;
    }

    // Read current salary data
    $salary_file = __DIR__ . '/salary.json';
    $salary_json = file_get_contents($salary_file);
    $salary_data = json_decode($salary_json, true);

    // Check if salary entry already exists for this user and month
    foreach ($salary_data['salary_data'] as $entry) {
        if ($entry['user_id'] === $user_id && $entry['month'] === $month) {
            header('Location: ../frontend/salary-management.php?error=entry_exists');
            exit;
        }
    }

    // Get next entry ID
    $max_id = 0;
    foreach ($salary_data['salary_data'] as $entry) {
        $max_id = max($max_id, $entry['id']);
    }
    $new_id = $max_id + 1;

    // Calculate salary from attendance if requested
    if ($calculate_from_attendance) {
        // Read attendance data
        $attendance_json = file_get_contents(__DIR__ . '/attendance.json');
        $attendance_data = json_decode($attendance_json, true);

        // Get month start and end dates
        $month_start = date('Y-m-01', strtotime($month));
        $month_end = date('Y-m-t', strtotime($month));

        // Calculate total working hours for the month
        $total_hours = 0;
        foreach ($attendance_data['records'] as $record) {
            if ($record['user_id'] === $user_id && 
                $record['date'] >= $month_start && 
                $record['date'] <= $month_end &&
                !empty($record['time_in']) && 
                !empty($record['time_out'])) {
                $total_hours += $record['working_hours'];
            }
        }

        // Calculate salary based on working hours
        // Assuming 8 hours per day, 22 working days per month = 176 hours
        $expected_hours = 176;
        $hourly_rate = $base_salary / $expected_hours;
        $calculated_salary = $total_hours * $hourly_rate;

        // Update base salary with calculated amount
        $base_salary = $calculated_salary;
        
        // Add calculation details to notes
        $calculation_notes = "Calculated from attendance:\n";
        $calculation_notes .= "Total working hours: " . number_format($total_hours, 2) . " hrs\n";
        $calculation_notes .= "Hourly rate: $" . number_format($hourly_rate, 2) . "\n";
        $calculation_notes .= "Expected monthly hours: " . $expected_hours . " hrs\n";
        $notes = $calculation_notes . "\n" . $notes;
    }

    // Add new salary entry
    $salary_data['salary_data'][] = [
        'id' => $new_id,
        'user_id' => $user_id,
        'month' => $month,
        'base_salary' => $base_salary,
        'bonus' => $bonus,
        'notes' => $notes,
        'added_by' => $_SESSION['username'],
        'added_at' => date('Y-m-d H:i:s'),
        'admin_id' => $_SESSION['user_id']
    ];

    // Add to salary history
    $salary_data['salary_history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => 'added',
        'entry_id' => $new_id,
        'user_id' => $user_id,
        'month' => $month,
        'base_salary' => $base_salary,
        'bonus' => $bonus,
        'performed_by' => $_SESSION['username']
    ];

    // Save updated salary data
    file_put_contents($salary_file, json_encode($salary_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/salary-management.php?message=entry_added');
    exit;
}

// If not POST request, redirect to salary management page
header('Location: ../frontend/salary-management.php');
exit;
?>
