<?php
require_once 'auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../frontend/index.php');
    exit;
}

try {
    // Get current date and time
    $date = date('Y-m-d');
    $time = date('H:i:s');
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $admin_id = $_SESSION['admin_id'] ?? null;

    // Read current attendance data
    $attendance_file = __DIR__ . '/attendance.json';
    $attendance_json = file_get_contents($attendance_file);
    $attendance_data = json_decode($attendance_json, true);

    if (!isset($attendance_data['records'])) {
        $attendance_data['records'] = [];
    }

    // Get next record ID
    $max_id = 0;
    foreach ($attendance_data['records'] as $record) {
        $max_id = max($max_id, $record['id'] ?? 0);
    }
    $new_id = $max_id + 1;

    // Check if attendance already marked for today
    $today_record = null;
    foreach ($attendance_data['records'] as &$record) {
        if ($record['user_id'] === $user_id && $record['date'] === $date) {
            $today_record = &$record;
            break;
        }
    }

    // Read users data to get department
    $users_json = file_get_contents(__DIR__ . '/users.json');
    $users_data = json_decode($users_json, true);
    $user_dept = '';
    foreach ($users_data['users'] as $user) {
        if ($user['id'] === $user_id) {
            $user_dept = $user['department'];
            break;
        }
    }

    if (!$today_record) {
        // First time check-in
        $attendance_data['records'][] = [
            'id' => $new_id,
            'user_id' => $user_id,
            'username' => $username,
            'date' => $date,
            'time_in' => $date . ' ' . $time,
            'time_out' => null,
            'status' => 'present',
            'department' => $user_dept,
            'working_hours' => 0,
            'notes' => '',
            'created_by' => $username,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_by' => $username,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $message = 'checked_in';
    } else if (!$today_record['time_out']) {
        // Check-out
        $checkout_time = $date . ' ' . $time;
        $today_record['time_out'] = $checkout_time;
        
        // Calculate working hours
        $time_in = strtotime($today_record['time_in']);
        $time_out = strtotime($checkout_time);
        $working_hours = ($time_out - $time_in) / 3600; // Convert seconds to hours
        $today_record['working_hours'] = round($working_hours, 2);
        
        // Update record
        $today_record['updated_by'] = $username;
        $today_record['updated_at'] = date('Y-m-d H:i:s');

        // Add to history if not exists
        if (!isset($attendance_data['history'])) {
            $attendance_data['history'] = [];
        }
        
        $attendance_data['history'][] = [
            'date' => date('Y-m-d H:i:s'),
            'action' => 'checked_out',
            'record_id' => $today_record['id'],
            'user_id' => $user_id,
            'time_in' => $today_record['time_in'],
            'time_out' => $checkout_time,
            'working_hours' => $today_record['working_hours'],
            'performed_by' => $username
        ];

        $message = 'checked_out';
    } else {
        // Already checked out
        header('Location: ../frontend/dashboard-user.php?message=already_marked');
        exit;
    }

    // Save updated attendance data
    file_put_contents($attendance_file, json_encode($attendance_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/dashboard-user.php?message=' . $message);
    exit;

} catch (Exception $e) {
    error_log("Error marking attendance: " . $e->getMessage());
    header('Location: ../frontend/dashboard-user.php?error=system_error');
    exit;
}
?>
