<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Get invoice ID
$invoice_id = (int)($_GET['invoice_id'] ?? 0);

// Read sales data
$sales_json = file_get_contents(__DIR__ . '/../backend/sales.json');
$sales_data = json_decode($sales_json, true);

// Find sale
$sale = null;
foreach ($sales_data['sales'] as $s) {
    if ($s['id'] === $invoice_id) {
        $sale = $s;
        break;
    }
}

if (!$sale) {
    header('Location: sales-management.php?error=Invoice not found');
    exit;
}

// Get customer data
$customers_json = file_get_contents(__DIR__ . '/../backend/customers.json');
$customers_data = json_decode($customers_json, true);

$customer = null;
foreach ($customers_data['customers'] as $c) {
    if ($c['id'] === $sale['customer_id']) {
        $customer = $c;
        break;
    }
}

$balance = $sale['total_amount'] - ($sale['paid_amount'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Payment - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Record Payment</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="view-invoice.php?id=<?php echo $invoice_id; ?>" class="text-blue-600 hover:text-blue-800">Back to Invoice</a>
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

        <!-- Invoice Summary -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600">Invoice Number: <span class="font-medium"><?php echo htmlspecialchars($sale['invoice_number']); ?></span></p>
                        <p class="text-gray-600">Date: <span class="font-medium"><?php echo date('Y-m-d', strtotime($sale['created_at'])); ?></span></p>
                        <?php if ($customer): ?>
                            <p class="text-gray-600">Customer: <span class="font-medium"><?php echo htmlspecialchars($customer['name']); ?></span></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-gray-600">Total Amount: <span class="font-medium">₹<?php echo number_format($sale['total_amount'], 2); ?></span></p>
                        <p class="text-gray-600">Paid Amount: <span class="font-medium">₹<?php echo number_format($sale['paid_amount'] ?? 0, 2); ?></span></p>
                        <p class="text-gray-600">Balance Due: <span class="font-medium text-red-600">₹<?php echo number_format($balance, 2); ?></span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Record Payment</h3>
                <form action="../backend/record_payment.php" method="POST" class="space-y-4">
                    <input type="hidden" name="invoice_id" value="<?php echo $invoice_id; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Amount</label>
                            <input type="number" name="amount" step="0.01" min="0" max="<?php echo $balance; ?>" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Maximum amount: ₹<?php echo number_format($balance, 2); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                        <input type="text" name="reference_number" placeholder="Transaction ID, Cheque number, etc."
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="view-invoice.php?id=<?php echo $invoice_id; ?>" 
                           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment History -->
        <?php if (!empty($sale['payment_history'])): ?>
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment History</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($sale['payment_history'] as $payment): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo date('Y-m-d H:i', strtotime($payment['date'])); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">₹<?php echo number_format($payment['amount'], 2); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo ucfirst($payment['method']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($payment['reference_number'] ?? '-'); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($payment['recorded_by']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
