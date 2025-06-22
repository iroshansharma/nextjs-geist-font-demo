<?php
require_once '../backend/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left side -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                    <a href="#" class="flex items-center group">
                        <div class="flex items-center justify-center w-10 h-10">
                            <img src="assets/images/logo.svg" alt="Veils India Logo" class="w-full h-full text-blue-600">
                        </div>
                        <span class="ml-2 text-xl font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-150 hidden sm:block">Veils India</span>
                    </a>
                    </div>
                    
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="h-6 w-px bg-gray-200"></div>
                        <div class="flex items-center ml-6">
                            <span class="material-icons text-gray-500">dashboard</span>
                            <span class="ml-2 text-lg font-medium text-gray-900">Dashboard</span>
                        </div>
                    </div>
                </div>

                <!-- Right side -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile menu button -->
                    <button type="button" class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" onclick="toggleMobileMenu()">
                        <span class="material-icons">menu</span>
                    </button>

                    <div class="hidden sm:flex items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            <span class="material-icons text-gray-400">account_circle</span>
                            <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                        <span class="bg-blue-100 text-blue-800 px-2.5 py-1 rounded-full text-xs font-medium">
                            <?php echo ucfirst($_SESSION['department']); ?>
                        </span>
                    </div>

                    <a href="../backend/logout.php" 
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 hover:text-white hover:bg-red-500 transition-all duration-150">
                        <span class="material-icons text-sm mr-1 sm:mr-2">logout</span>
                        <span class="hidden sm:inline">Logout</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile menu backdrop -->
        <div class="sm:hidden fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity z-20 hidden" id="mobile-backdrop"></div>
        
        <!-- Mobile menu panel -->
        <div class="sm:hidden fixed inset-x-0 top-16 z-30" id="mobile-menu" style="display: none; opacity: 0; transform: translateY(-10px); transition: all 0.3s ease-out;">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-gray-50 border-t border-gray-200">
                <!-- User Info -->
                <div class="px-3 py-3 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <span class="material-icons text-gray-400 text-2xl">account_circle</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                            <div class="mt-1">
                                <span class="bg-blue-100 text-blue-800 px-2.5 py-1 rounded-full text-xs font-medium">
                                    <?php echo ucfirst($_SESSION['department']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="pt-2">
                    <a href="#" class="flex items-center px-3 py-2.5 text-base font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md group">
                        <span class="material-icons text-gray-500 mr-3 group-hover:text-blue-600">dashboard</span>
                        Dashboard
                    </a>
                    
                    <?php if ($_SESSION['department'] === 'office'): ?>
                        <a href="view-stock.php" class="flex items-center px-3 py-2.5 text-base font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md group">
                            <span class="material-icons text-gray-500 mr-3 group-hover:text-blue-600">inventory</span>
                            View Stock
                        </a>
                    <?php elseif ($_SESSION['department'] === 'showroom'): ?>
                        <a href="showroom-stock.php" class="flex items-center px-3 py-2.5 text-base font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md group">
                            <span class="material-icons text-gray-500 mr-3 group-hover:text-blue-600">store</span>
                            Manage Stock
                        </a>
                    <?php endif; ?>

                    <a href="my-salary.php" class="flex items-center px-3 py-2.5 text-base font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md group">
                        <span class="material-icons text-gray-500 mr-3 group-hover:text-blue-600">payments</span>
                        Salary Details
                    </a>

                    <a href="my-vouchers.php" class="flex items-center px-3 py-2.5 text-base font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md group">
                        <span class="material-icons text-gray-500 mr-3 group-hover:text-blue-600">receipt_long</span>
                        Vouchers
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            const backdrop = document.getElementById('mobile-backdrop');
            
            if (mobileMenu.style.display === 'none') {
                // Show menu and backdrop
                backdrop.classList.remove('hidden');
                mobileMenu.style.display = 'block';
                
                // Trigger reflow
                mobileMenu.offsetHeight;
                backdrop.offsetHeight;
                
                // Animate in
                backdrop.style.opacity = '1';
                mobileMenu.style.opacity = '1';
                mobileMenu.style.transform = 'translateY(0)';
            } else {
                // Animate out
                backdrop.style.opacity = '0';
                mobileMenu.style.opacity = '0';
                mobileMenu.style.transform = 'translateY(-10px)';
                
                // Hide after animation
                setTimeout(() => {
                    mobileMenu.style.display = 'none';
                    backdrop.classList.add('hidden');
                }, 300);
            }
        }

        // Initialize mobile menu state
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            const backdrop = document.getElementById('mobile-backdrop');
            
            backdrop.style.opacity = '0';
            backdrop.addEventListener('click', toggleMobileMenu);
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                const menuButton = event.target.closest('button');
                const menu = event.target.closest('#mobile-menu');
                
                if (!menuButton && !menu && mobileMenu.style.display !== 'none') {
                    toggleMobileMenu();
                }
            });
        });
    </script>

    <?php
    // Get tasks data
    $tasks_json = file_get_contents(__DIR__ . '/../backend/tasks.json');
    $tasks_data = json_decode($tasks_json, true);
    $my_tasks = array_filter($tasks_data['tasks'] ?? [], function($task) {
        return $task['user_id'] === $_SESSION['user_id'];
    });
    $pending_tasks = array_filter($my_tasks, function($task) {
        return $task['status'] === 'pending';
    });

    // Get attendance data
    $today = date('Y-m-d');
    $attendance_json = file_get_contents(__DIR__ . '/../backend/attendance.json');
    $attendance_data = json_decode($attendance_json, true);
    $today_record = null;
    if (isset($attendance_data['records'])) {
        foreach ($attendance_data['records'] as $record) {
            if ($record['user_id'] === $_SESSION['user_id'] && $record['date'] === $today) {
                $today_record = $record;
                break;
            }
        }
    }
    ?>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 flex-grow">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Attendance Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <span class="material-icons text-blue-500 text-3xl">schedule</span>
                            <h3 class="ml-3 text-lg font-medium text-gray-900">Attendance</h3>
                        </div>
                        <?php if ($today_record && $today_record['time_out']): ?>
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Completed</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!$today_record): ?>
                        <form action="../backend/mark_attendance.php" method="POST">
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-300">
                                <span class="material-icons mr-2">login</span>
                                Check In
                            </button>
                        </form>
                    <?php elseif (!$today_record['time_out']): ?>
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">Checked in at: <?php echo date('h:i A', strtotime($today_record['time_in'])); ?></p>
                        </div>
                        <form action="../backend/mark_attendance.php" method="POST">
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-300">
                                <span class="material-icons mr-2">logout</span>
                                Check Out
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Check In:</span>
                                <span class="font-medium"><?php echo date('h:i A', strtotime($today_record['time_in'])); ?></span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Check Out:</span>
                                <span class="font-medium"><?php echo date('h:i A', strtotime($today_record['time_out'])); ?></span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-900">
                                <span>Working Hours:</span>
                                <span class="font-medium"><?php echo number_format($today_record['working_hours'], 2); ?> hrs</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stock Management Card -->
            <?php if ($_SESSION['department'] === 'office'): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
                <a href="view-stock.php" class="block p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <span class="material-icons text-purple-500 text-3xl">inventory_2</span>
                            <h3 class="ml-3 text-lg font-medium text-gray-900">View Stock</h3>
                        </div>
                        <span class="material-icons text-gray-400">arrow_forward</span>
                    </div>
                    <p class="text-sm text-gray-600">Check current inventory levels and stock details</p>
                </a>
            </div>
            <?php elseif ($_SESSION['department'] === 'showroom'): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
                <a href="showroom-stock.php" class="block p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <span class="material-icons text-purple-500 text-3xl">inventory_2</span>
                            <h3 class="ml-3 text-lg font-medium text-gray-900">Manage Stock</h3>
                        </div>
                        <span class="material-icons text-gray-400">arrow_forward</span>
                    </div>
                    <p class="text-sm text-gray-600">Update and manage showroom inventory</p>
                </a>
            </div>
            <?php endif; ?>

            <!-- Tasks Card -->
            <?php if (!empty($my_tasks)): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <span class="material-icons text-red-500 text-3xl">assignment</span>
                            <h3 class="ml-3 text-lg font-medium text-gray-900">My Tasks</h3>
                        </div>
                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                            <?php echo count($pending_tasks); ?> Pending
                        </span>
                    </div>
                    <div class="space-y-3">
                        <?php 
                        $displayed_tasks = array_slice($pending_tasks, 0, 2);
                        foreach ($displayed_tasks as $task): 
                        ?>
                            <div class="text-sm">
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></p>
                                <div class="flex justify-between items-center mt-1">
                                    <p class="text-gray-500">Due: <?php echo htmlspecialchars($task['due_date']); ?></p>
                                    <form action="../backend/update_task.php" method="POST" class="flex items-center">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $task['user_id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="text-xs rounded border-gray-200 text-gray-600">
                                            <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($pending_tasks) > 2): ?>
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-700 flex items-center">
                                <span class="material-icons text-sm mr-1">visibility</span>
                                View all tasks (<?php echo count($pending_tasks); ?>)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Salary Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
                <a href="my-salary.php" class="block p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <span class="material-icons text-green-500 text-3xl">payments</span>
                            <h3 class="ml-3 text-lg font-medium text-gray-900">Salary Details</h3>
                        </div>
                        <span class="material-icons text-gray-400">arrow_forward</span>
                    </div>
                    <p class="text-sm text-gray-600">View your salary information and payment history</p>
                </a>
            </div>

            <!-- Voucher Claims Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <span class="material-icons text-yellow-500 text-3xl">receipt_long</span>
                            <h3 class="ml-3 text-lg font-medium text-gray-900">Voucher Claims</h3>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <a href="submit-voucher.php" class="flex items-center text-blue-600 hover:text-blue-700">
                            <span class="material-icons text-sm mr-1">add_circle</span>
                            <span class="text-sm">New Claim</span>
                        </a>
                        <a href="my-vouchers.php" class="flex items-center text-blue-600 hover:text-blue-700">
                            <span class="material-icons text-sm mr-1">list</span>
                            <span class="text-sm">View Claims</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Customer Care System (Showroom) -->
            <?php if ($_SESSION['department'] === 'showroom'): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
                <a href="customer-payments.php" class="block p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <span class="material-icons text-indigo-500 text-3xl">point_of_sale</span>
                            <h3 class="ml-3 text-lg font-medium text-gray-900">Customer Care</h3>
                        </div>
                        <span class="material-icons text-gray-400">arrow_forward</span>
                    </div>
                    <p class="text-sm text-gray-600">Manage invoices and customer payments</p>
                </a>
            </div>
            <?php endif; ?>
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
