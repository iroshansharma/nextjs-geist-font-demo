<?php
require_once '../backend/auth.php';

// Check if user is logged in and is super admin
if (!is_logged_in() || !has_role(1)) {
    header('Location: index.php');
    exit;
}

// Get summary data
$customers_json = file_get_contents(__DIR__ . '/../backend/customers.json');
$customers_data = json_decode($customers_json, true);
$total_customers = count($customers_data['customers'] ?? []);

$tasks_json = file_get_contents(__DIR__ . '/../backend/tasks.json');
$tasks_data = json_decode($tasks_json, true);
$pending_tasks = 0;
$completed_tasks = 0;
$overdue_tasks = 0;
$current_date = date('Y-m-d');

foreach ($tasks_data['tasks'] ?? [] as $task) {
    if ($task['status'] === 'pending') {
        $pending_tasks++;
        if (strtotime($task['due_date']) < strtotime($current_date)) {
            $overdue_tasks++;
        }
    } elseif ($task['status'] === 'completed') {
        $completed_tasks++;
    }
}

$stock_json = file_get_contents(__DIR__ . '/../backend/stock.json');
$stock_data = json_decode($stock_json, true);
$total_stock_items = count($stock_data['items'] ?? []);

$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);
$total_employees = count($users_data['users'] ?? []) - 1; // Exclude super admin
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - CRM System</title>
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
                    <span class="text-xl font-semibold">Veils India - Super Admin</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 flex-grow">
        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Customer Management -->
            <a href="customer-management.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-blue-500 text-3xl">people</span>
                        <span class="text-2xl font-bold text-blue-500"><?php echo $total_customers; ?></span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Customer Management</h3>
                    <p class="text-gray-600 mt-2">Total Active Customers</p>
                </div>
            </a>

            <!-- Sales Management -->
            <a href="sales-management.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-indigo-500 text-3xl">receipt_long</span>
                        <span class="text-2xl font-bold text-indigo-500">
                            <?php 
                            $sales_json = file_get_contents(__DIR__ . '/../backend/sales.json');
                            $sales_data = json_decode($sales_json, true);
                            echo count($sales_data['sales'] ?? []);
                            ?>
                        </span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Sales Management</h3>
                    <p class="text-gray-600 mt-2">Manage Sales & Invoices</p>
                </div>
            </a>

            <!-- Project Management -->
            <a href="project-management.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-purple-500 text-3xl">engineering</span>
                        <span class="text-2xl font-bold text-purple-500">
                            <?php 
                            $projects_json = file_get_contents(__DIR__ . '/../backend/projects.json');
                            $projects_data = json_decode($projects_json, true);
                            $ongoing_projects = array_filter($projects_data['projects'] ?? [], function($project) {
                                return $project['status'] === 'ongoing';
                            });
                            echo count($ongoing_projects);
                            ?>
                        </span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Project Management</h3>
                    <p class="text-gray-600 mt-2">Active Projects</p>
                </div>
            </a>

            <!-- Stock Management -->
            <a href="stock-management.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-green-500 text-3xl">inventory_2</span>
                        <span class="text-2xl font-bold text-green-500"><?php echo $total_stock_items; ?></span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Stock Management</h3>
                    <p class="text-gray-600 mt-2">Total Stock Items</p>
                </div>
            </a>

            <!-- Salary Management -->
            <a href="salary-management.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-purple-500 text-3xl">payments</span>
                        <span class="text-2xl font-bold text-purple-500"><?php echo $total_employees; ?></span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Salary Management</h3>
                    <p class="text-gray-600 mt-2">Active Employees</p>
                </div>
            </a>

            <!-- Attendance Management -->
            <a href="manage-attendance.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-indigo-500 text-3xl">event_available</span>
                        <span class="text-2xl font-bold text-indigo-500"><?php echo $total_employees; ?></span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Attendance Management</h3>
                    <p class="text-gray-600 mt-2">Track Employee Attendance</p>
                </div>
            </a>

            <!-- Voucher Management -->
            <a href="manage-vouchers.php" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-icons text-teal-500 text-3xl">receipt_long</span>
                        <span class="text-2xl font-bold text-teal-500">
                            <?php 
                            $claims_json = file_get_contents(__DIR__ . '/../backend/voucher_claims.json');
                            $claims_data = json_decode($claims_json, true);
                            $pending_claims = array_filter($claims_data['claims'] ?? [], function($claim) {
                                return $claim['status'] === 'pending';
                            });
                            echo count($pending_claims);
                            ?>
                        </span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Voucher Management</h3>
                    <p class="text-gray-600 mt-2">Process Expense Claims</p>
                </div>
            </a>

            <!-- Task Management -->
            <div class="md:col-span-2 lg:col-span-3 bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <span class="material-icons text-yellow-500 text-3xl mr-2">assignment</span>
                            <h3 class="text-lg font-semibold text-gray-900">Task Management</h3>
                        </div>
                        <a href="task-management.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">View All Tasks</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-yellow-700">Pending Tasks</span>
                                <span class="text-2xl font-bold text-yellow-700"><?php echo $pending_tasks; ?></span>
                            </div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-green-700">Completed Tasks</span>
                                <span class="text-2xl font-bold text-green-700"><?php echo $completed_tasks; ?></span>
                            </div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-red-700">Overdue Tasks</span>
                                <span class="text-2xl font-bold text-red-700"><?php echo $overdue_tasks; ?></span>
                            </div>
                        </div>
                    </div>
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
