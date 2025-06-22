<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Get customer ID
$customer_id = (int)($_GET['id'] ?? 0);

// Read customers data
$customers_json = file_get_contents(__DIR__ . '/../backend/customers.json');
$customers_data = json_decode($customers_json, true);

// Find customer
$customer = null;
foreach ($customers_data['customers'] as $c) {
    if ($c['id'] === $customer_id) {
        $customer = $c;
        break;
    }
}

if (!$customer) {
    header('Location: customer-management.php?error=Customer not found');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Edit Customer</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="customer-management.php" class="text-blue-600 hover:text-blue-800">Back to Customers</a>
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

        <!-- Edit Customer Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Customer Information</h3>
                <form action="../backend/update_customer.php" method="POST" class="space-y-4">
                    <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Source</label>
                            <select name="source" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="walk-in" <?php echo $customer['source'] === 'walk-in' ? 'selected' : ''; ?>>Walk-in</option>
                                <option value="referral" <?php echo $customer['source'] === 'referral' ? 'selected' : ''; ?>>Referral</option>
                                <option value="online" <?php echo $customer['source'] === 'online' ? 'selected' : ''; ?>>Online</option>
                                <option value="advertisement" <?php echo $customer['source'] === 'advertisement' ? 'selected' : ''; ?>>Advertisement</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="2" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($customer['notes']); ?></textarea>
                    </div>

                    <div class="flex justify-between">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Update Customer
                        </button>
                        <a href="view_customer.php?id=<?php echo $customer['id']; ?>" 
                           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Customer History -->
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Customer History</h3>
                <div class="space-y-4">
                    <?php
                    $customer_history = array_filter($customers_data['history'], function($record) use ($customer_id) {
                        return $record['customer_id'] === $customer_id;
                    });
                    
                    if (!empty($customer_history)): 
                        foreach (array_reverse($customer_history) as $record): ?>
                            <div class="border-l-4 border-blue-500 pl-4 py-2">
                                <div class="text-sm text-gray-600">
                                    <?php echo date('Y-m-d H:i', strtotime($record['timestamp'])); ?> - 
                                    <?php echo htmlspecialchars($record['performed_by']); ?>
                                </div>
                                <div class="text-sm">
                                    <span class="font-medium">Action:</span> 
                                    <?php echo ucfirst(str_replace('_', ' ', $record['action'])); ?>
                                </div>
                                <?php if (!empty($record['details'])): ?>
                                    <div class="text-sm text-gray-600">
                                        <?php foreach ($record['details'] as $key => $value): ?>
                                            <div><?php echo ucfirst($key) . ': ' . htmlspecialchars($value); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p class="text-gray-500 text-center py-4">No history records found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
