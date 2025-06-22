<?php
require_once '../backend/auth.php';

// Check if user is logged in and is super admin
if (!is_logged_in() || !has_role(1)) {
    header('Location: index.php');
    exit;
}

// Get user data
$user_id = $_GET['id'] ?? 0;
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);

$edit_user = null;
foreach ($users_data['users'] as $user) {
    if ($user['id'] == $user_id) {
        $edit_user = $user;
        break;
    }
}

if (!$edit_user || $edit_user['role_id'] == 1) {
    header('Location: dashboard-super.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Edit User - CRM System</span>
                </div>
                <div class="flex items-center">
                    <a href="dashboard-super.php" class="mr-4 text-blue-600 hover:text-blue-800">Back to Dashboard</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit User: <?php echo htmlspecialchars($edit_user['username']); ?></h3>
                <form action="../backend/update_user.php" method="POST" class="space-y-4">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($edit_user['username']); ?>" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="2" <?php echo $edit_user['role_id'] == 2 ? 'selected' : ''; ?>>Admin User</option>
                                <option value="3" <?php echo $edit_user['role_id'] == 3 ? 'selected' : ''; ?>>Normal User</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Department</label>
                            <select name="department" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="office" <?php echo ($edit_user['department'] ?? '') === 'office' ? 'selected' : ''; ?>>Office</option>
                                <option value="showroom" <?php echo ($edit_user['department'] ?? '') === 'showroom' ? 'selected' : ''; ?>>Showroom</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Update User
                        </button>
                        <a href="dashboard-super.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
