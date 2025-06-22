<?php
require_once '../backend/auth.php';

// Check if user is logged in and is super admin
if (!is_logged_in() || !has_role(1)) {
    header('Location: index.php');
    exit;
}

// Get salary entry data
$entry_id = $_GET['id'] ?? 0;
$salary_json = file_get_contents(__DIR__ . '/../backend/salary.json');
$salary_data = json_decode($salary_json, true);

$edit_entry = null;
foreach ($salary_data['salary_data'] as $entry) {
    if ($entry['id'] == $entry_id) {
        $edit_entry = $entry;
        break;
    }
}

if (!$edit_entry) {
    header('Location: salary-management.php');
    exit;
}

// Get users data
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);

// Get normal users
$normal_users = array_filter($users_data['users'], function($user) {
    return $user['role_id'] == 3;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Salary Entry - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Edit Salary Entry</span>
                </div>
                <div class="flex items-center">
                    <a href="salary-management.php" class="mr-4 text-blue-600 hover:text-blue-800">Back to Salary Management</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Salary Entry</h3>
                <form action="../backend/update_salary.php" method="POST" class="space-y-4">
                    <input type="hidden" name="entry_id" value="<?php echo $edit_entry['id']; ?>">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Employee</label>
                            <select name="user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php foreach ($normal_users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo $user['id'] == $edit_entry['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Month</label>
                            <input type="month" name="month" value="<?php echo htmlspecialchars($edit_entry['month']); ?>" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Base Salary</label>
                            <input type="number" step="0.01" name="base_salary" value="<?php echo htmlspecialchars($edit_entry['base_salary']); ?>" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bonus</label>
                            <input type="number" step="0.01" name="bonus" value="<?php echo htmlspecialchars($edit_entry['bonus']); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($edit_entry['notes']); ?></textarea>
                    </div>
                    <div class="flex justify-between">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Update Salary Entry
                        </button>
                        <a href="salary-management.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancel
                        </a>
                    </div>
                </form>

                <!-- Salary History -->
                <div class="mt-8">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Entry History</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Salary</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bonus</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated By</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                $entry_history = array_filter($salary_data['salary_history'], function($history) use ($entry_id) {
                                    return $history['entry_id'] == $entry_id;
                                });
                                $entry_history = array_reverse($entry_history);
                                
                                foreach ($entry_history as $history):
                                ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($history['date']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $history['action'] === 'added' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                                <?php echo ucfirst($history['action']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($history['base_salary'], 2); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($history['bonus'], 2); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($history['performed_by']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
