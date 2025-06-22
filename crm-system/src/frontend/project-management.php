<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Read projects data
$projects_json = file_get_contents(__DIR__ . '/../backend/projects.json');
$projects_data = json_decode($projects_json, true);

// Read users data for employee list
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);

// Get employees (excluding super admin)
$employees = array_filter($users_data['users'], function($user) {
    return $user['role_id'] != 1;
});

// Filter parameters
$status = $_GET['status'] ?? '';
$employee_id = (int)($_GET['employee_id'] ?? 0);
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$view_mode = $_GET['view'] ?? 'list';

// Filter projects
$filtered_projects = array_filter($projects_data['projects'] ?? [], function($project) use ($status, $employee_id, $start_date, $end_date) {
    $matches = true;
    
    if ($status && $project['status'] !== $status) {
        $matches = false;
    }
    
    if ($employee_id && $project['manager_id'] !== $employee_id) {
        $matches = false;
    }
    
    if ($start_date && strtotime($project['start_date']) < strtotime($start_date)) {
        $matches = false;
    }
    
    if ($end_date && strtotime($project['end_date']) > strtotime($end_date)) {
        $matches = false;
    }
    
    return $matches;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Project Management</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo $_SESSION['role_id'] === 1 ? 'dashboard-super.php' : 'dashboard-admin.php'; ?>" class="text-blue-600 hover:text-blue-800">Back to Dashboard</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($_GET['message'])): ?>
            <div class="mb-4 p-4 rounded-md bg-green-50 text-green-800">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-4 p-4 rounded-md bg-red-50 text-red-800">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="mb-6 flex justify-between items-center">
            <div class="flex space-x-4">
                <a href="add-project.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Add New Project
                </a>
                <a href="project-report.php?<?php echo http_build_query($_GET); ?>" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Export Report
                </a>
            </div>
            <div class="flex space-x-4">
                <a href="?<?php echo http_build_query(array_merge($_GET, ['view' => 'list'])); ?>" 
                   class="<?php echo $view_mode === 'list' ? 'bg-gray-200' : 'bg-white'; ?> px-4 py-2 rounded">
                    <span class="material-icons">view_list</span>
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['view' => 'card'])); ?>"
                   class="<?php echo $view_mode === 'card' ? 'bg-gray-200' : 'bg-white'; ?> px-4 py-2 rounded">
                    <span class="material-icons">grid_view</span>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Projects</h3>
                <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="hidden" name="view" value="<?php echo $view_mode; ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="ongoing" <?php echo $status === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Project Manager</label>
                        <select name="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Managers</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>" <?php echo $employee_id === $employee['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($employee['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-4 flex justify-end space-x-4">
                        <a href="project-management.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($view_mode === 'card'): ?>
            <!-- Card View -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($filtered_projects as $project): 
                    $manager = null;
                    foreach ($employees as $emp) {
                        if ($emp['id'] === $project['manager_id']) {
                            $manager = $emp;
                            break;
                        }
                    }
                ?>
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <?php if (!empty($project['images'])): ?>
                            <img src="<?php echo htmlspecialchars($project['images'][0]); ?>" alt="Project Image" class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2"><?php echo htmlspecialchars($project['name']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($project['location']); ?></p>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600">Client: <?php echo htmlspecialchars($project['client_name']); ?></p>
                                <p class="text-sm text-gray-600">Manager: <?php echo $manager ? htmlspecialchars($manager['username']) : 'N/A'; ?></p>
                                <p class="text-sm text-gray-600">Duration: <?php echo date('Y-m-d', strtotime($project['start_date'])); ?> to <?php echo date('Y-m-d', strtotime($project['end_date'])); ?></p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php 
                                    switch($project['status']) {
                                        case 'completed':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'ongoing':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        default:
                                            echo 'bg-yellow-100 text-yellow-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($project['status']); ?>
                                </span>
                            </div>
                            <div class="mt-4 flex justify-end space-x-3">
                                <a href="view-project.php?id=<?php echo $project['id']; ?>" class="text-blue-600 hover:text-blue-800">View</a>
                                <a href="edit-project.php?id=<?php echo $project['id']; ?>" class="text-yellow-600 hover:text-yellow-800">Edit</a>
                                <a href="../backend/delete_project.php?id=<?php echo $project['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this project?')"
                                   class="text-red-600 hover:text-red-800">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- List View -->
            <div class="bg-white shadow rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($filtered_projects as $project): 
                                $manager = null;
                                foreach ($employees as $emp) {
                                    if ($emp['id'] === $project['manager_id']) {
                                        $manager = $emp;
                                        break;
                                    }
                                }
                            ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($project['name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($project['location']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($project['client_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo $manager ? htmlspecialchars($manager['username']) : 'N/A'; ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <?php echo date('Y-m-d', strtotime($project['start_date'])); ?><br>
                                            to<br>
                                            <?php echo date('Y-m-d', strtotime($project['end_date'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php 
                                            switch($project['status']) {
                                                case 'completed':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'ongoing':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                default:
                                                    echo 'bg-yellow-100 text-yellow-800';
                                            }
                                            ?>">
                                            <?php echo ucfirst($project['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <a href="view-project.php?id=<?php echo $project['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <a href="edit-project.php?id=<?php echo $project['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</a>
                                        <a href="../backend/delete_project.php?id=<?php echo $project['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this project?')"
                                           class="text-red-600 hover:text-red-900">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
