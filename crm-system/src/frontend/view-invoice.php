<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Get sale ID
$sale_id = (int)($_GET['id'] ?? 0);

// Read sales data
$sales_json = file_get_contents(__DIR__ . '/../backend/sales.json');
$sales_data = json_decode($sales_json, true);

// Find sale
$sale = null;
foreach ($sales_data['sales'] as $s) {
    if ($s['id'] === $sale_id) {
        $sale = $s;
        break;
    }
}

if (!$sale) {
    header('Location: sales-management.php?error=Sale not found');
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Invoice - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Invoice Details</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="sales-management.php" class="text-blue-600 hover:text-blue-800">Back to Sales</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($_GET['message'])): ?>
            <div class="mb-4 p-4 rounded-md bg-green-50 text-green-800">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Invoice Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">INVOICE</h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($sale['invoice_number']); ?></p>
                        <p class="text-gray-600">Date: <?php echo date('Y-m-d', strtotime($sale['created_at'])); ?></p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-lg font-medium">Your Company Name</h3>
                        <p class="text-gray-600">123 Business Street</p>
                        <p class="text-gray-600">City, State, ZIP</p>
                        <p class="text-gray-600">GST No: XXXXXXXXXXXX</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Details -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Bill To</h3>
                <?php if ($customer): ?>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($customer['name']); ?></p>
                            <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($customer['address'])); ?></p>
                            <p class="text-gray-600">Phone: <?php echo htmlspecialchars($customer['phone']); ?></p>
                            <p class="text-gray-600">Email: <?php echo htmlspecialchars($customer['email']); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-600">Payment Status: 
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
                            </p>
                            <?php if ($sale['payment_status'] === 'partial'): ?>
                                <p class="text-gray-600 mt-2">Paid Amount: ₹<?php echo number_format($sale['paid_amount'], 2); ?></p>
                                <p class="text-gray-600">Balance: ₹<?php echo number_format($sale['total_amount'] - $sale['paid_amount'], 2); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Items</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($sale['items'] as $item): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($item['description']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right"><?php echo $item['quantity']; ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">₹<?php echo number_format($item['price'], 2); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">₹<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <div class="w-full md:w-1/2 ml-auto">
                    <div class="space-y-2">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal:</span>
                            <span>₹<?php echo number_format($sale['subtotal'], 2); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>GST (<?php echo $sale['gst_rate']; ?>%):</span>
                            <span>₹<?php echo number_format($sale['gst_amount'], 2); ?></span>
                        </div>
                        <?php if ($sale['discount_amount'] > 0): ?>
                            <div class="flex justify-between text-gray-700">
                                <span>Discount (<?php echo $sale['discount_percentage']; ?>%):</span>
                                <span>-₹<?php echo number_format($sale['discount_amount'], 2); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between font-bold text-lg border-t pt-2">
                            <span>Total:</span>
                            <span>₹<?php echo number_format($sale['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
            <a href="generate-invoice.php?id=<?php echo $sale['id']; ?>" 
               class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                Download Invoice
            </a>
            <?php if ($sale['payment_status'] !== 'paid'): ?>
                <a href="record-payment.php?invoice_id=<?php echo $sale['id']; ?>" 
                   class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                    Record Payment
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
