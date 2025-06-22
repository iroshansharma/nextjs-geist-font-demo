<?php
require_once '../backend/auth.php';

// Check if user is logged in and has showroom staff role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2, 3])) { // 3 is showroom staff
    header('Location: index.php');
    exit;
}

// Read customers data
$customers_json = file_get_contents(__DIR__ . '/../backend/customers.json');
$customers_data = json_decode($customers_json, true);

// Read payments data
$payments_json = file_get_contents(__DIR__ . '/../backend/payments.json');
$payments_data = json_decode($payments_json, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Payments - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Customer Payments</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="customer-management.php" class="text-blue-600 hover:text-blue-800">Back to Customers</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Add New Payment Section -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Record New Payment</h3>
                <form action="../backend/add_payment.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Customer</label>
                            <select name="customer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Customer</option>
                                <?php foreach ($customers_data['customers'] as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>">
                                        <?php echo htmlspecialchars($customer['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" name="amount" step="0.01" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Type</label>
                            <select name="payment_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="full">Full Payment</option>
                                <option value="partial">Partial Payment</option>
                                <option value="installment">Installment</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Record Payment
                    </button>
                </form>
            </div>
        </div>

        <!-- Payments List -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
                    <div class="flex space-x-2">
                        <button onclick="filterPayments('all')" class="px-3 py-1 rounded text-sm bg-gray-200 hover:bg-gray-300">All</button>
                        <button onclick="filterPayments('full')" class="px-3 py-1 rounded text-sm bg-green-100 hover:bg-green-200">Full</button>
                        <button onclick="filterPayments('partial')" class="px-3 py-1 rounded text-sm bg-yellow-100 hover:bg-yellow-200">Partial</button>
                        <button onclick="filterPayments('installment')" class="px-3 py-1 rounded text-sm bg-blue-100 hover:bg-blue-200">Installment</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($payments_data['payments'] ?? [] as $payment): 
                                $customer = null;
                                foreach ($customers_data['customers'] as $c) {
                                    if ($c['id'] === $payment['customer_id']) {
                                        $customer = $c;
                                        break;
                                    }
                                }
                            ?>
                                <tr class="payment-row" data-type="<?php echo $payment['payment_type']; ?>">
                                    <td class="px-6 py-4">
                                        <?php if ($customer): ?>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($customer['name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($customer['email']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">₹<?php echo number_format($payment['amount'], 2); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            <?php 
                                            switch($payment['payment_type']) {
                                                case 'full':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'partial':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'installment':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                            }
                                            ?>">
                                            <?php echo ucfirst($payment['payment_type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <?php echo date('Y-m-d', strtotime($payment['created_at'])); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo date('H:i', strtotime($payment['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $payment['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <a href="view_payment.php?id=<?php echo $payment['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <a href="generate_invoice.php?payment_id=<?php echo $payment['id']; ?>" class="text-green-600 hover:text-green-900">Invoice</a>
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
    function filterPayments(type) {
        const rows = document.querySelectorAll('.payment-row');
        rows.forEach(row => {
            if (type === 'all' || row.dataset.type === type) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    </script>
</body>
</html>
