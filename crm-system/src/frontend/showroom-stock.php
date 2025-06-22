<?php
require_once '../backend/auth.php';

// Check if user is logged in and is showroom user
if (!is_logged_in() || !($_SESSION['role_id'] === 3 && $_SESSION['department'] === 'showroom')) {
    header('Location: index.php');
    exit;
}

// Read stock data
$stock_json = file_get_contents(__DIR__ . '/../backend/stock.json');
$stock_data = json_decode($stock_json, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Showroom Stock Management - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Showroom Stock Management</span>
                </div>
                <div class="flex items-center">
                    <a href="dashboard-user.php" class="mr-4 text-blue-600 hover:text-blue-800">Back to Dashboard</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Update Stock Form -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Update Stock</h3>
                <form action="../backend/update_showroom_stock.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Item</label>
                            <select name="item_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php foreach ($stock_data['items'] as $item): ?>
                                    <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Transaction Type</label>
                            <select name="transaction_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="out">Stock Out (Sale)</option>
                                <option value="return">Stock Return</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="quantity" required min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Update Stock
                    </button>
                </form>
            </div>
        </div>

        <!-- Stock List -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Current Stock</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($stock_data['items'] as $item): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="<?php echo $item['quantity'] < 10 ? 'text-red-600 font-semibold' : 'text-gray-900'; ?>">
                                            <?php echo htmlspecialchars($item['quantity']); ?>
                                        </span>
                                        <?php if ($item['quantity'] < 10): ?>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Low Stock
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($item['last_updated']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Transactions</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $showroom_transactions = array_filter($stock_data['transactions'], function($transaction) {
                                return $transaction['updated_by'] === $_SESSION['username'];
                            });
                            $showroom_transactions = array_slice(array_reverse($showroom_transactions), 0, 10);
                            
                            foreach ($showroom_transactions as $transaction): 
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($transaction['date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($transaction['item_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $transaction['type'] === 'return' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($transaction['type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($transaction['quantity']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($transaction['notes'] ?? ''); ?></td>
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
