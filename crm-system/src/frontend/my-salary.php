<?php
require_once '../backend/auth.php';

// Check if user is logged in and is normal user
if (!is_logged_in() || !has_role(3)) {
    header('Location: index.php');
    exit;
}

// Read salary data
$salary_json = file_get_contents(__DIR__ . '/../backend/salary.json');
$salary_data = json_decode($salary_json, true);

// Get user's salary entries
$my_salary = array_filter($salary_data['salary_data'], function($entry) {
    return $entry['user_id'] === $_SESSION['user_id'];
});
$my_salary = array_reverse($my_salary);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Salary - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">My Salary History</span>
                </div>
                <div class="flex items-center">
                    <a href="dashboard-user.php" class="mr-4 text-blue-600 hover:text-blue-800">Back to Dashboard</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Salary Summary Card -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Salary Summary</h3>
                <?php
                $latest_salary = reset($my_salary);
                if ($latest_salary):
                ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Latest Base Salary</p>
                            <p class="text-2xl font-semibold text-gray-900">$<?php echo number_format($latest_salary['base_salary'], 2); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Latest Bonus</p>
                            <p class="text-2xl font-semibold text-green-600">$<?php echo number_format($latest_salary['bonus'], 2); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-2xl font-semibold text-blue-600">$<?php echo number_format($latest_salary['base_salary'] + $latest_salary['bonus'], 2); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No salary records found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Salary History -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Salary History</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Salary</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bonus</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($my_salary as $entry): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($entry['month']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($entry['base_salary'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($entry['bonus'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($entry['base_salary'] + $entry['bonus'], 2); ?></td>
                                    <td class="px-6 py-4">
                                        <?php if (!empty($entry['notes'])): ?>
                                            <span class="text-sm text-gray-600"><?php echo htmlspecialchars($entry['notes']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($my_salary)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No salary records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
