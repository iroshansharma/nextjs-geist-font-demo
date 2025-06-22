<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Read salary data
$salary_json = file_get_contents(__DIR__ . '/../backend/salary.json');
$salary_data = json_decode($salary_json, true);

// Read users data
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
    <title>Salary Management - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Salary Management</span>
                </div>
                <div class="flex items-center">
                    <a href="<?php echo $_SESSION['role_id'] === 1 ? 'dashboard-super.php' : 'dashboard-admin.php'; ?>" 
                       class="mr-4 text-blue-600 hover:text-blue-800">Back to Dashboard</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Add Salary Entry -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Salary Entry</h3>
                <form action="../backend/add_salary.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Employee</label>
                            <select name="user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php foreach ($normal_users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Month</label>
                            <input type="month" name="month" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Salary Calculation</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="radio" id="fixed_salary" name="calculation_type" value="fixed" checked
                                           onchange="toggleSalaryInput()" class="mr-2">
                                    <label for="fixed_salary">Fixed Base Salary</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" id="attendance_based" name="calculation_type" value="attendance"
                                           onchange="toggleSalaryInput()" class="mr-2">
                                    <label for="attendance_based">Calculate from Attendance</label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Base Salary/Hourly Rate</label>
                            <input type="number" step="0.01" name="base_salary" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500" id="salary_note">Enter fixed base salary amount</p>
                            <input type="hidden" name="calculate_from_attendance" id="calculate_from_attendance" value="no">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bonus</label>
                            <input type="number" step="0.01" name="bonus" value="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Salary Entry
                    </button>
                </form>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Salary</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Working Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bonus</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $salary_entries = array_reverse($salary_data['salary_data']);
                            foreach ($salary_entries as $entry): 
                                $employee = array_filter($users_data['users'], function($u) use ($entry) {
                                    return $u['id'] == $entry['user_id'];
                                });
                                $employee = reset($employee);
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($employee['username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($entry['month']); ?>
                                        <?php if (strpos($entry['notes'] ?? '', 'Calculated from attendance:') !== false): ?>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Attendance Based
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($entry['base_salary'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        if (strpos($entry['notes'] ?? '', 'Total working hours:') !== false) {
                                            preg_match('/Total working hours: ([\d.]+)/', $entry['notes'], $matches);
                                            echo $matches[1] ?? '0.00';
                                        } else {
                                            echo '-';
                                        }
                                        ?> hrs
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($entry['bonus'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($entry['base_salary'] + $entry['bonus'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($entry['added_by']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($_SESSION['role_id'] === 1): ?>
                                            <a href="edit_salary.php?id=<?php echo $entry['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-4">Edit</a>
                                            <a href="../backend/delete_salary.php?id=<?php echo $entry['id']; ?>" 
                                               class="text-red-600 hover:text-red-900"
                                               onclick="return confirm('Are you sure?')">Delete</a>
                                        <?php endif; ?>
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
    function toggleSalaryInput() {
        const calculationType = document.querySelector('input[name="calculation_type"]:checked').value;
        const baseSalaryInput = document.querySelector('input[name="base_salary"]');
        const salaryNote = document.getElementById('salary_note');
        const calculateFromAttendance = document.getElementById('calculate_from_attendance');

        if (calculationType === 'attendance') {
            baseSalaryInput.placeholder = 'Enter hourly rate';
            salaryNote.textContent = 'Enter hourly rate for attendance-based calculation';
            calculateFromAttendance.value = 'yes';
        } else {
            baseSalaryInput.placeholder = 'Enter base salary';
            salaryNote.textContent = 'Enter fixed base salary amount';
            calculateFromAttendance.value = 'no';
        }
    }
    </script>
</body>
</html>
