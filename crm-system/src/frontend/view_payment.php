<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2, 3])) {
    header('Location: index.php');
    exit;
}

// Get payment ID
$payment_id = (int)($_GET['id'] ?? 0);

// Read payments data
$payments_json = file_get_contents(__DIR__ . '/../backend/payments.json');
$payments_data = json_decode($payments_json, true);

// Find payment
$payment = null;
foreach ($payments_data['payments'] as $p) {
    if ($p['id'] === $payment_id) {
        $payment = $p;
        break;
    }
}

if (!$payment) {
    header('Location: customer-payments.php?error=Payment not found');
    exit;
}

// Get customer data
$customers_json = file_get_contents(__DIR__ . '/../backend/customers.json');
$customers_data = json_decode($customers_json, true);

$customer = null;
foreach ($customers_data['customers'] as $c) {
    if ($c['id'] === $payment['customer_id']) {
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
    <title>View Payment - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Payment Details</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="customer-payments.php" class="text-blue-600 hover:text-blue-800">Back to Payments</a>
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

        <!-- Payment Details -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Payment Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($payment['invoice_number']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                <dd class="mt-1 text-sm text-gray-900">₹<?php echo number_format($payment['amount'], 2); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Payment Type</dt>
                                <dd class="mt-1">
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
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $payment['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($payment['created_by']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Customer Information -->
                    <?php if ($customer): ?>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($customer['name']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($customer['email']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($customer['phone']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($customer['address'])); ?></dd>
                            </div>
                        </dl>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($payment['notes'])): ?>
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Notes</h3>
                    <div class="bg-gray-50 rounded p-4 text-sm text-gray-700">
                        <?php echo nl2br(htmlspecialchars($payment['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="mt-6 flex justify-end space-x-4">
                    <a href="generate_invoice.php?payment_id=<?php echo $payment['id']; ?>" 
                       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Download Invoice
                    </a>
                    <?php if ($payment['status'] !== 'completed'): ?>
                    <a href="../backend/update_payment_status.php?id=<?php echo $payment['id']; ?>&status=completed" 
                       class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Mark as Completed
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
