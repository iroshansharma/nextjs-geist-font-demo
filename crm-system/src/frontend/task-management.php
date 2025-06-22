<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Read tasks data
$tasks_json = file_get_contents(__DIR__ . '/../backend/tasks.json');
$tasks_data = json_decode($tasks_json, true);

// Get users for assignment
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);

$current_date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Task Management</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo $_SESSION['role_id'] == 1 ? 'dashboard-super.php' : 'dashboard-admin.php'; ?>" class="text-blue-600 hover:text-blue-800">Dashboard</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Add New Task Section -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Task</h3>
                <form action="../backend/add_task.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Assign To</label>
                            <select name="assigned_to" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php foreach ($users_data['users'] as $user): ?>
                                    <?php if ($user['role_id'] != 1): // Exclude super admin ?>
                                        <option value="<?php echo $user['id']; ?>">
                                            <?php echo htmlspecialchars($user['username']); ?> 
                                            (<?php echo $user['department']; ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input type="date" name="due_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Priority</label>
                            <select name="priority" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Task
                    </button>
                </form>
            </div>
        </div>

        <!-- Tasks List -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">All Tasks</h3>
                    <div class="flex space-x-2">
                        <button onclick="filterTasks('all')" class="px-3 py-1 rounded text-sm bg-gray-200 hover:bg-gray-300">All</button>
                        <button onclick="filterTasks('pending')" class="px-3 py-1 rounded text-sm bg-yellow-100 hover:bg-yellow-200">Pending</button>
                        <button onclick="filterTasks('completed')" class="px-3 py-1 rounded text-sm bg-green-100 hover:bg-green-200">Completed</button>
                        <button onclick="filterTasks('overdue')" class="px-3 py-1 rounded text-sm bg-red-100 hover:bg-red-200">Overdue</button>
                    </div>
                </div>

                <?php if (isset($_GET['message'])): ?>
                    <div class="mb-4 p-4 rounded-md bg-green-50 text-green-800">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($tasks_data['tasks'] ?? [] as $task): 
                                $is_overdue = $task['status'] === 'pending' && strtotime($task['due_date']) < strtotime($current_date);
                                $assigned_user = null;
                                foreach ($users_data['users'] as $user) {
                                    if ($user['id'] === $task['assigned_to']) {
                                        $assigned_user = $user;
                                        break;
                                    }
                                }
                            ?>
                                <tr class="task-row" data-status="<?php echo $is_overdue ? 'overdue' : $task['status']; ?>">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($task['description']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($assigned_user): ?>
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($assigned_user['username']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($assigned_user['department']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm <?php echo $is_overdue ? 'text-red-600 font-medium' : 'text-gray-900'; ?>">
                                            <?php echo date('Y-m-d', strtotime($task['due_date'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            if ($is_overdue) {
                                                echo 'bg-red-100 text-red-800';
                                            } elseif ($task['status'] === 'completed') {
                                                echo 'bg-green-100 text-green-800';
                                            } else {
                                                echo 'bg-yellow-100 text-yellow-800';
                                            }
                                            ?>">
                                            <?php echo $is_overdue ? 'Overdue' : ucfirst($task['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            <?php 
                                            switch($task['priority']) {
                                                case 'high':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                case 'medium':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                default:
                                                    echo 'bg-green-100 text-green-800';
                                            }
                                            ?>">
                                            <?php echo ucfirst($task['priority']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <?php if ($task['status'] === 'pending'): ?>
                                            <a href="../backend/complete_task.php?id=<?php echo $task['id']; ?>" 
                                               class="text-green-600 hover:text-green-900 mr-3">Complete</a>
                                        <?php endif; ?>
                                        <a href="../backend/delete_task.php?id=<?php echo $task['id']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Are you sure you want to delete this task?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function filterTasks(status) {
        const rows = document.querySelectorAll('.task-row');
        rows.forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    </script>
</body>
</html>
