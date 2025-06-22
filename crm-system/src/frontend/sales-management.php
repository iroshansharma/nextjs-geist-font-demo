<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) { // Only Super Admin and Admin
    header('Location: index.php');
    exit;
}

// Read customers data
$customers_json = file_get_contents(__DIR__ . '/../backend/customers.json');
$customers_data = json_decode($customers_json, true);

// Read sales data
$sales_json = file_get_contents(__DIR__ . '/../backend/sales.json');
$sales_data = json_decode($sales_json, true);

// Get total sales and outstanding amount
$total_sales = 0;
$total_outstanding = 0;
foreach ($sales_data['sales'] ?? [] as $sale) {
    $total_sales += $sale['total_amount'];
    if ($sale['payment_status'] !== 'paid') {
        $total_outstanding += ($sale['total_amount'] - ($sale['paid_amount'] ?? 0));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Management - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Sales Management</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo $_SESSION['role_id'] == 1 ? 'dashboard-super.php' : 'dashboard-admin.php'; ?>" class="text-blue-600 hover:text-blue-800">Dashboard</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Sales Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Total Sales</p>
                        <h3 class="text-2xl font-bold">₹<?php echo number_format($total_sales, 2); ?></h3>
                    </div>
                    <span class="material-icons text-blue-500 text-3xl">payments</span>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Outstanding Amount</p>
                        <h3 class="text-2xl font-bold">₹<?php echo number_format($total_outstanding, 2); ?></h3>
                    </div>
                    <span class="material-icons text-red-500 text-3xl">account_balance</span>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Total Customers</p>
                        <h3 class="text-2xl font-bold"><?php echo count($customers_data['customers'] ?? []); ?></h3>
                    </div>
                    <span class="material-icons text-green-500 text-3xl">people</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="create-invoice.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100">
                        <span class="material-icons text-blue-500 mr-3">receipt</span>
                        <span class="text-blue-700">Create New Invoice</span>
                    </a>
                    <a href="payment-history.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100">
                        <span class="material-icons text-green-500 mr-3">history</span>
                        <span class="text-green-700">Payment History</span>
                    </a>
                    <a href="sales-report.php" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100">
                        <span class="material-icons text-purple-500 mr-3">analytics</span>
                        <span class="text-purple-700">Generate Report</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Recent Sales</h3>
                    <div class="flex space-x-2">
                        <button onclick="filterSales('all')" class="px-3 py-1 rounded text-sm bg-gray-200 hover:bg-gray-300">All</button>
                        <button onclick="filterSales('paid')" class="px-3 py-1 rounded text-sm bg-green-100 hover:bg-green-200">Paid</button>
                        <button onclick="filterSales('partial')" class="px-3 py-1 rounded text-sm bg-yellow-100 hover:bg-yellow-200">Partial</button>
                        <button onclick="filterSales('unpaid')" class="px-3 py-1 rounded text-sm bg-red-100 hover:bg-red-200">Unpaid</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $recent_sales = array_slice(array_reverse($sales_data['sales'] ?? []), 0, 10);
                            foreach ($recent_sales as $sale): 
                                $customer = null;
                                foreach ($customers_data['customers'] as $c) {
                                    if ($c['id'] === $sale['customer_id']) {
                                        $customer = $c;
                                        break;
                                    }
                                }
                            ?>
                                <tr class="sale-row" data-status="<?php echo $sale['payment_status']; ?>">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($sale['invoice_number']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($customer): ?>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($customer['name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($customer['email']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">₹<?php echo number_format($sale['total_amount'], 2); ?></div>
                                        <?php if ($sale['payment_status'] === 'partial'): ?>
                                            <div class="text-xs text-gray-500">Paid: ₹<?php echo number_format($sale['paid_amount'], 2); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            <?php 
                                            switch($sale['payment_status']) {
                                                case 'paid':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'partial':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                default:
                                                    echo 'bg-red-100 text-red-800';
                                            }
                                            ?>">
                                            <?php echo ucfirst($sale['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo date('Y-m-d', strtotime($sale['created_at'])); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo date('H:i', strtotime($sale['created_at'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <a href="view-invoice.php?id=<?php echo $sale['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <a href="generate-invoice.php?id=<?php echo $sale['id']; ?>" class="text-green-600 hover:text-green-900 mr-3">Download</a>
                                        <?php if ($sale['payment_status'] !== 'paid'): ?>
                                            <a href="record-payment.php?invoice_id=<?php echo $sale['id']; ?>" class="text-purple-600 hover:text-purple-900">Record Payment</a>
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
    function filterSales(status) {
        const rows = document.querySelectorAll('.sale-row');
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
