<?php
require_once '../backend/auth.php';

// Check if user is logged in and has admin role
if (!is_logged_in() || $_SESSION['role_id'] !== 2) {
    header('Location: index.php');
    exit;
}

// Get all normal users
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);
$normal_users = array_filter($users_data['users'], function($user) {
    return $user['role_id'] == 3;
});

// Get all tasks
$tasks_json = file_get_contents(__DIR__ . '/../backend/tasks.json');
$tasks_data = json_decode($tasks_json, true);
$pending_tasks = array_filter($tasks_data['tasks'] ?? [], function($task) {
    return $task['status'] === 'pending';
});

// Get attendance records
$attendance_json = file_get_contents(__DIR__ . '/../backend/attendance.json');
$attendance_data = json_decode($attendance_json, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10">
                        <img src="assets/images/logo.svg" alt="Veils India Logo" class="w-full h-full text-blue-600">
                    </div>
                    <span class="text-xl font-semibold">Veils India - Admin Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 flex-grow">
        <!-- Management Modules Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Customer Management -->
            <a href="customer-management.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-blue-500 text-3xl">people</span>
                        <span class="text-2xl font-bold text-blue-500"><?php echo count($normal_users); ?></span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Customer Management</h3>
                    <p class="text-gray-600 mt-2">Manage Customer Records</p>
                </div>
            </a>

            <!-- Project Management -->
            <a href="project-management.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-purple-500 text-3xl">engineering</span>
                        <span class="material-icons text-purple-500">arrow_forward</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Project Management</h3>
                    <p class="text-gray-600 mt-2">Track Project Progress</p>
                </div>
            </a>

            <!-- Stock Management -->
            <a href="stock-management.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-green-500 text-3xl">inventory</span>
                        <span class="material-icons text-green-500">arrow_forward</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Stock Management</h3>
                    <p class="text-gray-600 mt-2">Monitor Inventory</p>
                </div>
            </a>

            <!-- Salary Management -->
            <a href="salary-management.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-yellow-500 text-3xl">payments</span>
                        <span class="material-icons text-yellow-500">arrow_forward</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Salary Management</h3>
                    <p class="text-gray-600 mt-2">Manage Employee Salaries</p>
                </div>
            </a>

            <!-- Attendance Management -->
            <a href="manage-attendance.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-red-500 text-3xl">event_available</span>
                        <span class="material-icons text-red-500">arrow_forward</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Attendance Management</h3>
                    <p class="text-gray-600 mt-2">Track Employee Attendance</p>
                </div>
            </a>

            <!-- Sales Management -->
            <a href="sales-management.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-indigo-500 text-3xl">point_of_sale</span>
                        <span class="material-icons text-indigo-500">arrow_forward</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Sales Management</h3>
                    <p class="text-gray-600 mt-2">Monitor Sales & Revenue</p>
                </div>
            </a>

            <!-- Voucher Management -->
            <a href="manage-vouchers.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-teal-500 text-3xl">receipt_long</span>
                        <span class="material-icons text-teal-500">arrow_forward</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Voucher Management</h3>
                    <p class="text-gray-600 mt-2">Process Expense Claims</p>
                </div>
            </a>
        </div>

        <!-- Task Assignment Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg rounded-lg mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <span class="material-icons text-white text-4xl mr-3">assignment_add</span>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Assign New Task</h3>
                            <p class="text-blue-100">Pending Tasks: <?php echo count($pending_tasks ?? []); ?></p>
                        </div>
                    </div>
                </div>
                <form action="../backend/assign_task.php" method="POST" class="space-y-4 bg-white p-6 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Assign To</label>
                        <select name="user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <?php foreach ($normal_users as $user): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Task Title</label>
                        <input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="3"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="date" name="due_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors duration-300">
                            <span class="material-icons mr-2">add_task</span>
                            Assign Task
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tasks Overview -->
        <div class="bg-white shadow-lg rounded-lg mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <span class="material-icons text-gray-700 text-3xl mr-3">list_alt</span>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Tasks Overview</h3>
                            <p class="text-gray-500">Manage and track assigned tasks</p>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($tasks_data['tasks'] as $task): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $assigned_user = array_filter($users_data['users'], function($u) use ($task) {
                                            return $u['id'] == $task['user_id'];
                                        });
                                        $assigned_user = reset($assigned_user);
                                        echo htmlspecialchars($assigned_user['username']);
                                        ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($task['description']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($task['due_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $task['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo ucfirst(htmlspecialchars($task['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-4">Edit</a>
                                        <a href="../backend/delete_task.php?id=<?php echo $task['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Attendance Overview -->
        <div class="bg-white shadow-lg rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <span class="material-icons text-gray-700 text-3xl mr-3">event_available</span>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Today's Attendance</h3>
                            <p class="text-gray-500">Track employee attendance status</p>
                        </div>
                    </div>
                    <a href="manage-attendance.php" class="flex items-center text-blue-600 hover:text-blue-800">
                        <span class="text-sm font-medium">View All</span>
                        <span class="material-icons text-sm ml-1">arrow_forward</span>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $today = date('Y-m-d');
                            foreach ($normal_users as $user):
                                $attendance = array_filter($attendance_data['attendance'], function($a) use ($user, $today) {
                                    return $a['user_id'] == $user['id'] && $a['date'] == $today;
                                });
                                $attendance = reset($attendance);
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($attendance): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Present
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Absent
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo $attendance ? htmlspecialchars($attendance['time']) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-xs text-gray-500">
                © 2025 Veils India Private Limited. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>
