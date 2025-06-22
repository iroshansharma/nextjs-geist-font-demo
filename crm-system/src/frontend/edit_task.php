<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Get task ID
$task_id = (int)($_GET['id'] ?? 0);

// Read tasks data
$tasks_json = file_get_contents(__DIR__ . '/../backend/tasks.json');
$tasks_data = json_decode($tasks_json, true);

// Find task
$task = null;
foreach ($tasks_data['tasks'] as $t) {
    if ($t['id'] === $task_id) {
        $task = $t;
        break;
    }
}

if (!$task) {
    header('Location: task-management.php?error=Task not found');
    exit;
}

// Get users for assignment
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Edit Task</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="task-management.php" class="text-blue-600 hover:text-blue-800">Back to Tasks</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($_GET['error'])): ?>
            <div class="mb-4 p-4 rounded-md bg-red-50 text-red-800">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Edit Task Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Task Details</h3>
                <form action="../backend/update_task.php" method="POST" class="space-y-4">
                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Assign To</label>
                            <select name="assigned_to" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php foreach ($users_data['users'] as $user): ?>
                                    <?php if ($user['role_id'] != 1): // Exclude super admin ?>
                                        <option value="<?php echo $user['id']; ?>" <?php echo $user['id'] === $task['assigned_to'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?> 
                                            (<?php echo $user['department']; ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input type="date" name="due_date" value="<?php echo date('Y-m-d', strtotime($task['due_date'])); ?>" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Priority</label>
                            <select name="priority" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="low" <?php echo $task['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo $task['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo $task['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" required 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($task['description']); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>

                    <div class="flex justify-between">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Update Task
                        </button>
                        <a href="task-management.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Task History -->
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Task History</h3>
                <div class="space-y-4">
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <div class="text-sm text-gray-600">Created by <?php echo htmlspecialchars($task['created_by']); ?></div>
                        <div class="text-sm text-gray-600">on <?php echo date('Y-m-d H:i', strtotime($task['created_at'])); ?></div>
                    </div>
                    <?php if ($task['updated_at'] !== $task['created_at']): ?>
                        <div class="border-l-4 border-yellow-500 pl-4 py-2">
                            <div class="text-sm text-gray-600">Last updated by <?php echo htmlspecialchars($task['updated_by']); ?></div>
                            <div class="text-sm text-gray-600">on <?php echo date('Y-m-d H:i', strtotime($task['updated_at'])); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($task['completed_by'])): ?>
                        <div class="border-l-4 border-green-500 pl-4 py-2">
                            <div class="text-sm text-gray-600">Completed by <?php echo htmlspecialchars($task['completed_by']); ?></div>
                            <div class="text-sm text-gray-600">on <?php echo date('Y-m-d H:i', strtotime($task['completed_at'])); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
