<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role (Super Admin or Admin)
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

$record_id = (int)($_GET['id'] ?? 0);

// Read attendance data
$attendance_json = file_get_contents(__DIR__ . '/../backend/attendance.json');
$attendance_data = json_decode($attendance_json, true);

// Find the record
$record = null;
foreach ($attendance_data['records'] as $r) {
    if ($r['id'] === $record_id) {
        $record = $r;
        break;
    }
}

if (!$record) {
    header('Location: manage-attendance.php?error=record_not_found');
    exit;
}

// Read users data
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);

// Get employee details
$employee = null;
foreach ($users_data['users'] as $user) {
    if ($user['id'] === $record['user_id']) {
        $employee = $user;
        break;
    }
}

if (!$employee) {
    header('Location: manage-attendance.php?error=employee_not_found');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance Record - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Edit Attendance Record</span>
                </div>
                <div class="flex items-center">
                    <a href="manage-attendance.php" class="mr-4 text-blue-600 hover:text-blue-800">Back to Attendance Management</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Attendance Record for <?php echo htmlspecialchars($employee['username']); ?></h3>
                
                <form action="../backend/update_attendance.php" method="POST" class="space-y-4">
                    <input type="hidden" name="record_id" value="<?php echo $record_id; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $employee['id']; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" required value="<?php echo $record['date']; ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Time In</label>
                            <input type="time" name="time_in" required 
                                   value="<?php echo date('H:i', strtotime($record['time_in'])); ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Time Out</label>
                            <input type="time" name="time_out" 
                                   value="<?php echo $record['time_out'] ? date('H:i', strtotime($record['time_out'])) : ''; ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="2" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($record['notes'] ?? ''); ?></textarea>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Record Information</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <p>Department: <?php echo ucfirst($employee['department']); ?></p>
                                <p>Created by: <?php echo htmlspecialchars($record['created_by']); ?></p>
                                <p>Created at: <?php echo date('Y-m-d H:i:s', strtotime($record['created_at'])); ?></p>
                            </div>
                            <div>
                                <p>Last updated by: <?php echo htmlspecialchars($record['updated_by']); ?></p>
                                <p>Last updated at: <?php echo date('Y-m-d H:i:s', strtotime($record['updated_at'])); ?></p>
                                <?php if ($record['time_in'] && $record['time_out']): ?>
                                    <p>Working hours: <?php echo number_format(($record['working_hours'] ?? 0), 2); ?> hrs</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Update Record
                        </button>
                        <a href="manage-attendance.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
