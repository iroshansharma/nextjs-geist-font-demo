<?php
require_once '../backend/auth.php';

// Check if user is admin or super admin
if (!is_logged_in() || ($_SESSION['role_id'] !== 1 && $_SESSION['role_id'] !== 2)) {
    header('Location: index.php');
    exit;
}

// Get voucher types for reference
$voucher_types_json = file_get_contents(__DIR__ . '/../backend/voucher_types.json');
$voucher_types = json_decode($voucher_types_json, true)['types'];
$voucher_types_map = array_column($voucher_types, null, 'id');

// Get all voucher claims
$claims_json = file_get_contents(__DIR__ . '/../backend/voucher_claims.json');
$claims_data = json_decode($claims_json, true);

// Sort claims by submission date (newest first)
usort($claims_data['claims'], function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});

// Get status counts
$status_counts = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'paid' => 0
];
foreach ($claims_data['claims'] as $claim) {
    $status_counts[$claim['status']]++;
}

// Calculate total amount by status
$total_amounts = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'paid' => 0
];
foreach ($claims_data['claims'] as $claim) {
    $total_amounts[$claim['status']] += $claim['amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Voucher Claims - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Manage Voucher Claims</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo $_SESSION['role_id'] === 1 ? 'dashboard-super.php' : 'dashboard-admin.php'; ?>" 
                       class="text-gray-600 hover:text-gray-900">
                        <span class="material-icons">arrow_back</span>
                    </a>
                    <span class="mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($_GET['success'])): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">
                    <?php
                    echo match($_GET['success']) {
                        'approved' => 'Voucher claim has been approved.',
                        'rejected' => 'Voucher claim has been rejected.',
                        'paid' => 'Voucher claim has been marked as paid.',
                        default => 'Action completed successfully.'
                    };
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Status Overview -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="material-icons text-yellow-500 text-3xl mr-3">pending</span>
                        <div>
                            <p class="text-sm text-gray-600">Pending</p>
                            <p class="text-2xl font-bold"><?php echo $status_counts['pending']; ?></p>
                        </div>
                    </div>
                    <p class="text-lg font-semibold text-gray-600">₹<?php echo number_format($total_amounts['pending'], 2); ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="material-icons text-green-500 text-3xl mr-3">check_circle</span>
                        <div>
                            <p class="text-sm text-gray-600">Approved</p>
                            <p class="text-2xl font-bold"><?php echo $status_counts['approved']; ?></p>
                        </div>
                    </div>
                    <p class="text-lg font-semibold text-gray-600">₹<?php echo number_format($total_amounts['approved'], 2); ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="material-icons text-red-500 text-3xl mr-3">cancel</span>
                        <div>
                            <p class="text-sm text-gray-600">Rejected</p>
                            <p class="text-2xl font-bold"><?php echo $status_counts['rejected']; ?></p>
                        </div>
                    </div>
                    <p class="text-lg font-semibold text-gray-600">₹<?php echo number_format($total_amounts['rejected'], 2); ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="material-icons text-blue-500 text-3xl mr-3">payments</span>
                        <div>
                            <p class="text-sm text-gray-600">Paid</p>
                            <p class="text-2xl font-bold"><?php echo $status_counts['paid']; ?></p>
                        </div>
                    </div>
                    <p class="text-lg font-semibold text-gray-600">₹<?php echo number_format($total_amounts['paid'], 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="mb-6 flex space-x-4">
            <a href="../backend/export_vouchers.php?format=excel" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <span class="material-icons mr-2">file_download</span>
                Export to Excel
            </a>
            <a href="../backend/export_vouchers.php?format=pdf" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <span class="material-icons mr-2">picture_as_pdf</span>
                Export to PDF
            </a>
        </div>

        <!-- Claims List -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">All Claims</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($claims_data['claims'] as $claim): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php echo htmlspecialchars($claim['username']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php echo htmlspecialchars($claim['date']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php echo htmlspecialchars($voucher_types_map[$claim['type_id']]['name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        ₹<?php echo number_format($claim['amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            echo match($claim['status']) {
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'paid' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            ?>">
                                            <?php echo ucfirst(htmlspecialchars($claim['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($claim['receipt_path']): ?>
                                            <a href="../<?php echo htmlspecialchars($claim['receipt_path']); ?>" target="_blank" 
                                               class="text-blue-600 hover:text-blue-900">View Receipt</a>
                                        <?php else: ?>
                                            <span class="text-gray-500">No receipt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($claim['status'] === 'pending'): ?>
                                            <div class="flex space-x-2">
                                                <a href="../backend/update_voucher.php?id=<?php echo $claim['id']; ?>&action=approve" 
                                                   class="text-green-600 hover:text-green-900" onclick="return confirm('Approve this claim?')">
                                                    <span class="material-icons">check_circle</span>
                                                </a>
                                                <a href="../backend/update_voucher.php?id=<?php echo $claim['id']; ?>&action=reject" 
                                                   class="text-red-600 hover:text-red-900" onclick="return confirm('Reject this claim?')">
                                                    <span class="material-icons">cancel</span>
                                                </a>
                                            </div>
                                        <?php elseif ($claim['status'] === 'approved'): ?>
                                            <a href="../backend/update_voucher.php?id=<?php echo $claim['id']; ?>&action=paid" 
                                               class="text-blue-600 hover:text-blue-900" onclick="return confirm('Mark this claim as paid?')">
                                                <span class="material-icons">payments</span>
                                            </a>
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
</body>
</html>
