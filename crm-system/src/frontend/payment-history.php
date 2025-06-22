<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Read sales data
$sales_json = file_get_contents(__DIR__ . '/../backend/sales.json');
$sales_data = json_decode($sales_json, true);

// Read customers data
$customers_json = file_get_contents(__DIR__ . '/../backend/customers.json');
$customers_data = json_decode($customers_json, true);

// Filter parameters
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$status = $_GET['status'] ?? '';
$customer_id = (int)($_GET['customer_id'] ?? 0);

// Filter sales based on parameters
$filtered_sales = array_filter($sales_data['sales'] ?? [], function($sale) use ($start_date, $end_date, $status, $customer_id) {
    $matches = true;
    
    if ($start_date && strtotime($sale['created_at']) < strtotime($start_date)) {
        $matches = false;
    }
    
    if ($end_date && strtotime($sale['created_at']) > strtotime($end_date . ' 23:59:59')) {
        $matches = false;
    }
    
    if ($status && $sale['payment_status'] !== $status) {
        $matches = false;
    }
    
    if ($customer_id && $sale['customer_id'] !== $customer_id) {
        $matches = false;
    }
    
    return $matches;
});

// Calculate totals
$total_amount = 0;
$total_paid = 0;
$total_outstanding = 0;

foreach ($filtered_sales as $sale) {
    $total_amount += $sale['total_amount'];
    $total_paid += $sale['paid_amount'] ?? 0;
}
$total_outstanding = $total_amount - $total_paid;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Payment History</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="sales-management.php" class="text-blue-600 hover:text-blue-800">Back to Sales</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Payments</h3>
                <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All</option>
                            <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="partial" <?php echo $status === 'partial' ? 'selected' : ''; ?>>Partial</option>
                            <option value="unpaid" <?php echo $status === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer</label>
                        <select name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Customers</option>
                            <?php foreach ($customers_data['customers'] as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>" <?php echo $customer_id === $customer['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customer['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-4 flex justify-end space-x-4">
                        <a href="payment-history.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Total Amount</p>
                        <h3 class="text-2xl font-bold">₹<?php echo number_format($total_amount, 2); ?></h3>
                    </div>
                    <span class="material-icons text-blue-500 text-3xl">payments</span>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Total Paid</p>
                        <h3 class="text-2xl font-bold">₹<?php echo number_format($total_paid, 2); ?></h3>
                    </div>
                    <span class="material-icons text-green-500 text-3xl">check_circle</span>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Outstanding Amount</p>
                        <h3 class="text-2xl font-bold">₹<?php echo number_format($total_outstanding, 2); ?></h3>
                    </div>
                    <span class="material-icons text-red-500 text-3xl">warning</span>
                </div>
            </div>
        </div>

        <!-- Payment History Table -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Payment Records</h3>
                    <a href="sales-report.php?<?php echo http_build_query($_GET); ?>" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Export Report
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($filtered_sales as $sale): 
                                $customer = null;
                                foreach ($customers_data['customers'] as $c) {
                                    if ($c['id'] === $sale['customer_id']) {
                                        $customer = $c;
                                        break;
                                    }
                                }
                            ?>
                                <tr>
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
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">₹<?php echo number_format($sale['paid_amount'] ?? 0, 2); ?></div>
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
                                        <?php if ($sale['payment_status'] !== 'paid'): ?>
                                            <a href="record-payment.php?invoice_id=<?php echo $sale['id']; ?>" class="text-green-600 hover:text-green-900">Record Payment</a>
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
