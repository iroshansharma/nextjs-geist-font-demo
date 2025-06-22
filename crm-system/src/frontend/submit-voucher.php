<?php
require_once '../backend/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

// Get voucher types
$voucher_types_json = file_get_contents(__DIR__ . '/../backend/voucher_types.json');
$voucher_types = json_decode($voucher_types_json, true)['types'];

// Filter only active voucher types
$active_voucher_types = array_filter($voucher_types, function($type) {
    return $type['active'] === true;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Voucher Claim - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Submit Voucher Claim</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard-user.php" class="text-gray-600 hover:text-gray-900">
                        <span class="material-icons">arrow_back</span>
                    </a>
                    <span class="mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg px-8 py-6">
            <div class="flex items-center mb-6">
                <span class="material-icons text-blue-600 text-3xl mr-3">receipt_long</span>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">New Voucher Claim</h2>
                    <p class="text-gray-600">Submit a new reimbursement request</p>
                </div>
            </div>

            <form action="../backend/add_voucher.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Voucher Type</label>
                        <select name="type_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select voucher type</option>
                            <?php foreach ($active_voucher_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">₹</span>
                            </div>
                            <input type="number" name="amount" required step="0.01" min="0"
                                class="pl-7 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" required max="<?php echo date('Y-m-d'); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bill/Receipt</label>
                        <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">Upload JPG, PNG or PDF (max 5MB)</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" required placeholder="Provide details about the expense"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="dashboard-user.php" class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">Cancel</a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Submit Claim
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
