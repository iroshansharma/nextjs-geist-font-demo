<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role (Super Admin or Admin)
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Read attendance data
$attendance_json = file_get_contents(__DIR__ . '/../backend/attendance.json');
$attendance_data = json_decode($attendance_json, true);

// Read users data
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);

// Get all users except super admin
$employees = array_filter($users_data['users'], function($user) {
    return $user['role_id'] != 1;
});

// Calculate working hours and days
function calculateWorkingHours($time_in, $time_out) {
    if (empty($time_in) || empty($time_out)) return 0;
    $in = strtotime($time_in);
    $out = strtotime($time_out);
    return ($out - $in) / 3600; // Convert seconds to hours
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Attendance Management</span>
                </div>
                <div class="flex items-center">
                    <?php if ($_SESSION['role_id'] == 1): ?>
                        <a href="dashboard-super.php" class="mr-4 text-blue-600 hover:text-blue-800">Back to Dashboard</a>
                    <?php else: ?>
                        <a href="dashboard-admin.php" class="mr-4 text-blue-600 hover:text-blue-800">Back to Dashboard</a>
                    <?php endif; ?>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Edit Attendance Form -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Attendance</h3>
                <?php if (isset($_GET['message'])): ?>
                    <div class="mb-4 p-4 rounded-md bg-green-50 text-green-800">
                        <?php 
                        $message = $_GET['message'];
                        switch($message) {
                            case 'record_updated':
                                echo 'Attendance record has been successfully updated.';
                                break;
                            default:
                                echo htmlspecialchars($message);
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="mb-4 p-4 rounded-md bg-red-50 text-red-800">
                        <?php 
                        $error = $_GET['error'];
                        switch($error) {
                            case 'invalid_input':
                                echo 'Please fill in all required fields.';
                                break;
                            case 'record_not_found':
                                echo 'The attendance record could not be found.';
                                break;
                            case 'save_failed':
                                echo 'Failed to save the attendance record. Please try again.';
                                break;
                            default:
                                echo htmlspecialchars($error);
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <form action="../backend/update_attendance.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Employee</label>
                            <select name="user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php foreach ($employees as $user): ?>
                                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?> (<?php echo ucfirst($user['department']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Time In</label>
                            <input type="time" name="time_in" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Time Out</label>
                            <input type="time" name="time_out" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Update Attendance
                    </button>
                </form>
            </div>
        </div>

        <!-- Attendance Records -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Attendance Records</h3>
                    <div class="flex space-x-4">
                        <select id="departmentFilter" onchange="filterRecords()" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="all">All Departments</option>
                            <option value="showroom">Showroom</option>
                            <option value="office">Office</option>
                        </select>
                        <input type="date" id="dateFilter" onchange="filterRecords()" 
                               class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Working Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            if (isset($attendance_data['records'])) {
                                // Sort records by date and time in descending order
                                usort($attendance_data['records'], function($a, $b) {
                                    $a_datetime = strtotime($a['date'] . ' ' . ($a['time_out'] ?? $a['time_in']));
                                    $b_datetime = strtotime($b['date'] . ' ' . ($b['time_out'] ?? $b['time_in']));
                                    return $b_datetime - $a_datetime;
                                });

                                foreach ($attendance_data['records'] as $record): 
                                    $employee = array_filter($users_data['users'], function($u) use ($record) {
                                        return $u['id'] == $record['user_id'];
                                    });
                                    $employee = reset($employee);
                                    
                                    if (!$employee) continue; // Skip if employee not found
                                    
                                    // Use working_hours from record if available, otherwise calculate
                                    $working_hours = isset($record['working_hours']) ? 
                                        $record['working_hours'] : 
                                        calculateWorkingHours($record['time_in'], $record['time_out']);
                            ?>
                                <tr class="attendance-row" data-department="<?php echo $employee['department']; ?>" data-date="<?php echo $record['date']; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($record['date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($employee['username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $employee['department'] === 'showroom' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo ucfirst($employee['department']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $record['time_in'] ? date('h:i A', strtotime($record['time_in'])) : '-'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $record['time_out'] ? date('h:i A', strtotime($record['time_out'])) : '-'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo number_format($working_hours, 2); ?> hrs</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="edit_attendance.php?id=<?php echo $record['id']; ?>" class="text-blue-600 hover:text-blue-900">Edit</a>
                                    </td>
                                </tr>
                            <?php 
                                endforeach;
                            } 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function filterRecords() {
        const department = document.getElementById('departmentFilter').value;
        const date = document.getElementById('dateFilter').value;
        const rows = document.querySelectorAll('.attendance-row');
        
        rows.forEach(row => {
            const rowDept = row.dataset.department;
            const rowDate = row.dataset.date;
            const deptMatch = department === 'all' || rowDept === department;
            const dateMatch = !date || rowDate === date;
            
            row.style.display = deptMatch && dateMatch ? '' : 'none';
        });
    }
    </script>
</body>
</html>
