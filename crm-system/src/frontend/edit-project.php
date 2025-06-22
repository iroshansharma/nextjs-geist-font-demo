<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: project-management.php');
    exit;
}

// Read projects data
$projects_json = file_get_contents(__DIR__ . '/../backend/projects.json');
$projects_data = json_decode($projects_json, true);

// Find project
$project = null;
foreach ($projects_data['projects'] as $p) {
    if ($p['id'] === (int)$_GET['id']) {
        $project = $p;
        break;
    }
}

if (!$project) {
    header('Location: project-management.php?error=Project not found');
    exit;
}

// Read users data for employee list
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);

// Get employees (excluding super admin)
$employees = array_filter($users_data['users'], function($user) {
    return $user['role_id'] != 1;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Edit Project</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="project-management.php" class="text-blue-600 hover:text-blue-800">Back to Projects</a>
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

        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <form action="../backend/update_project.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Project Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Project Name</label>
                            <input type="text" name="name" required value="<?php echo htmlspecialchars($project['name']); ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Project Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Project Location</label>
                            <input type="text" name="location" required value="<?php echo htmlspecialchars($project['location']); ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" required value="<?php echo $project['start_date']; ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" required value="<?php echo $project['end_date']; ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Project Manager -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Project Manager</label>
                            <select name="manager_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Manager</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo $employee['id']; ?>" 
                                            <?php echo $employee['id'] === $project['manager_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($employee['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Client Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Client Name</label>
                            <input type="text" name="client_name" required value="<?php echo htmlspecialchars($project['client_name']); ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Project Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Project Status</label>
                            <select name="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending" <?php echo $project['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="ongoing" <?php echo $project['status'] === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="completed" <?php echo $project['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>

                        <!-- Project Files -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Additional Files</label>
                            <input type="file" name="files[]" multiple accept="image/*,.pdf,.doc,.docx"
                                   class="mt-1 block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-blue-50 file:text-blue-700
                                          hover:file:bg-blue-100">
                            <p class="mt-1 text-sm text-gray-500">Upload additional project files</p>
                        </div>
                    </div>

                    <!-- Current Files -->
                    <?php if (!empty($project['files'])): ?>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Current Files</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($project['files'] as $index => $file): 
                                    $file_name = basename($file);
                                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif']);
                                ?>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <?php if ($is_image): ?>
                                            <img src="<?php echo htmlspecialchars($file); ?>" alt="Project File" class="w-16 h-16 object-cover rounded">
                                        <?php else: ?>
                                            <span class="material-icons text-gray-400">description</span>
                                        <?php endif; ?>
                                        <span class="flex-1 mx-4 text-sm text-gray-900"><?php echo htmlspecialchars($file_name); ?></span>
                                        <div class="flex items-center">
                                            <a href="<?php echo htmlspecialchars($file); ?>" target="_blank" 
                                               class="text-blue-600 hover:text-blue-800 mr-4">View</a>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="remove_files[]" value="<?php echo $index; ?>"
                                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-600">Remove</span>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Remarks -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Remarks / Notes</label>
                        <textarea name="remarks" rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($project['remarks'] ?? ''); ?></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="project-management.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</a>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Update Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
