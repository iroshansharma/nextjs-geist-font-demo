<?php
require_once 'auth.php';

// Check if user is logged in and has appropriate role (Super Admin or Admin)
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $record_id = (int)($_POST['record_id'] ?? 0);
    $user_id = (int)($_POST['user_id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $time_in = $_POST['time_in'] ?? '';
    $time_out = $_POST['time_out'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Validate input
    if (empty($record_id) || empty($user_id) || empty($date) || empty($time_in)) {
        header('Location: ../frontend/manage-attendance.php?error=invalid_input');
        exit;
    }

    // Read attendance data
    $attendance_file = __DIR__ . '/attendance.json';
    $attendance_json = file_get_contents($attendance_file);
    $attendance_data = json_decode($attendance_json, true);

    if (!isset($attendance_data['records'])) {
        $attendance_data['records'] = [];
    }

    // Format datetime strings
    $time_in = $date . ' ' . $time_in . ':00';
    $time_out = !empty($time_out) ? $date . ' ' . $time_out . ':00' : null;

    // Calculate working hours
    $working_hours = 0;
    if ($time_out) {
        $in = strtotime($time_in);
        $out = strtotime($time_out);
        $working_hours = ($out - $in) / 3600; // Convert seconds to hours
    }

    // Find and update the record by record_id
    $record_found = false;
    foreach ($attendance_data['records'] as &$record) {
        if ($record['id'] === $record_id) {
            // Update existing record
            $record['time_in'] = $time_in;
            $record['time_out'] = $time_out;
            $record['working_hours'] = $working_hours;
            $record['notes'] = $notes;
            $record['updated_by'] = $_SESSION['username'];
            $record['updated_at'] = date('Y-m-d H:i:s');
            
            // Store old values for history
            $old_values = [
                'time_in' => $record['time_in'],
                'time_out' => $record['time_out'],
                'working_hours' => $record['working_hours'],
                'notes' => $record['notes']
            ];

            // Add to history
            if (!isset($attendance_data['history'])) {
                $attendance_data['history'] = [];
            }
            $attendance_data['history'][] = [
                'date' => date('Y-m-d H:i:s'),
                'action' => 'updated',
                'record_id' => $record_id,
                'user_id' => $user_id,
                'changes' => [
                    'old' => $old_values,
                    'new' => [
                        'time_in' => $time_in,
                        'time_out' => $time_out,
                        'working_hours' => $working_hours,
                        'notes' => $notes
                    ]
                ],
                'performed_by' => $_SESSION['username'],
                'department' => $_SESSION['department']
            ];
            
            $record_found = true;
            break;
        }
    }

    if (!$record_found) {
        header('Location: ../frontend/manage-attendance.php?error=record_not_found');
        exit;
    }

    try {
        // Save updated attendance data
        if (file_put_contents($attendance_file, json_encode($attendance_data, JSON_PRETTY_PRINT)) === false) {
            throw new Exception('Failed to save attendance data');
        }

        header('Location: ../frontend/manage-attendance.php?message=record_updated');
        exit;
    } catch (Exception $e) {
        error_log("Error updating attendance record: " . $e->getMessage());
        header('Location: ../frontend/manage-attendance.php?error=save_failed');
        exit;
    }
}

// If not POST request, redirect to attendance management page
header('Location: ../frontend/manage-attendance.php');
exit;
?>
