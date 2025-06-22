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

// Read users data for employee names
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);

// Get project manager details
$manager = null;
foreach ($users_data['users'] as $user) {
    if ($user['id'] === $project['manager_id']) {
        $manager = $user;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Project - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">View Project</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="project-management.php" class="text-blue-600 hover:text-blue-800">Back to Projects</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Project Images -->
            <?php if (!empty($project['files'])): ?>
                <div class="relative h-64">
                    <div class="absolute inset-0 flex">
                        <?php foreach ($project['files'] as $file): 
                            $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif']);
                        ?>
                            <?php if ($is_image): ?>
                                <div class="flex-1">
                                    <img src="<?php echo htmlspecialchars($file); ?>" alt="Project Image" class="w-full h-64 object-cover">
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="p-6">
                <!-- Project Header -->
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($project['name']); ?></h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($project['location']); ?></p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
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

                <!-- Project Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Project Details</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Client Name</dt>
                                <dd class="text-sm text-gray-900"><?php echo htmlspecialchars($project['client_name']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Project Manager</dt>
                                <dd class="text-sm text-gray-900"><?php echo $manager ? htmlspecialchars($manager['username']) : 'N/A'; ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                <dd class="text-sm text-gray-900">
                                    <?php echo date('Y-m-d', strtotime($project['start_date'])); ?> to 
                                    <?php echo date('Y-m-d', strtotime($project['end_date'])); ?>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Project Files</h3>
                        <?php if (!empty($project['files'])): ?>
                            <ul class="space-y-2">
                                <?php foreach ($project['files'] as $file): 
                                    $file_name = basename($file);
                                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $is_document = in_array($file_ext, ['pdf', 'doc', 'docx']);
                                ?>
                                    <?php if ($is_document): ?>
                                        <li class="flex items-center">
                                            <span class="material-icons text-gray-400 mr-2">description</span>
                                            <a href="<?php echo htmlspecialchars($file); ?>" 
                                               class="text-blue-600 hover:text-blue-800"
                                               target="_blank">
                                                <?php echo htmlspecialchars($file_name); ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">No files attached</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Project Remarks -->
                <?php if (!empty($project['remarks'])): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Remarks</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($project['remarks']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Project History -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Project History</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created By</dt>
                            <dd class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($project['created_by']); ?> on 
                                <?php echo date('Y-m-d H:i', strtotime($project['created_at'])); ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated By</dt>
                            <dd class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($project['updated_by']); ?> on 
                                <?php echo date('Y-m-d H:i', strtotime($project['updated_at'])); ?>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end space-x-4">
                    <a href="edit-project.php?id=<?php echo $project['id']; ?>" 
                       class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                        Edit Project
                    </a>
                    <a href="../backend/delete_project.php?id=<?php echo $project['id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this project?')"
                       class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Delete Project
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
