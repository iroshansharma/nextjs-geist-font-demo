<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Read customers data
$customers_json = file_get_contents(__DIR__ . '/../backend/customers.json');
$customers_data = json_decode($customers_json, true);

// Read products data
$stock_json = file_get_contents(__DIR__ . '/../backend/stock.json');
$stock_data = json_decode($stock_json, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Create New Invoice</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="sales-management.php" class="text-blue-600 hover:text-blue-800">Back to Sales</a>
                    <a href="../backend/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <form action="../backend/create_sale.php" method="POST" id="invoiceForm" class="space-y-6">
            <!-- Customer Selection -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Select Customer</label>
                        <select name="customer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Choose a customer</option>
                            <?php foreach ($customers_data['customers'] as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>">
                                    <?php echo htmlspecialchars($customer['name']); ?> 
                                    (<?php echo htmlspecialchars($customer['phone']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <a href="customer-management.php?action=add" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Add New Customer
                        </a>
                    </div>
                </div>
            </div>

            <!-- Products/Services -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Products/Services</h3>
                    <button type="button" onclick="addItem()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Item
                    </button>
                </div>
                <div id="itemsContainer" class="space-y-4">
                    <!-- Items will be added here dynamically -->
                </div>
            </div>

            <!-- Additional Details -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">GST Rate (%)</label>
                        <input type="number" name="gst_rate" value="18" min="0" max="28" step="0.01" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Discount (%)</label>
                        <input type="number" name="discount_percentage" value="0" min="0" max="100" step="0.01"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                        <select name="payment_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="paid">Paid</option>
                            <option value="partial">Partial Payment</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4" id="partialPaymentSection" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700">Paid Amount</label>
                    <input type="number" name="paid_amount" step="0.01" min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Summary</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal:</span>
                        <span id="subtotal">₹0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">GST:</span>
                        <span id="gst">₹0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Discount:</span>
                        <span id="discount">₹0.00</span>
                    </div>
                    <div class="flex justify-between font-bold">
                        <span>Total:</span>
                        <span id="total">₹0.00</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" onclick="window.location.href='sales-management.php'" 
                        class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Create Invoice
                </button>
            </div>
        </form>
    </div>

    <script>
    let itemCount = 0;

    function addItem() {
        const container = document.getElementById('itemsContainer');
        const itemDiv = document.createElement('div');
        itemDiv.className = 'grid grid-cols-1 md:grid-cols-5 gap-4 items-end border-b pb-4';
        itemDiv.innerHTML = `
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Item Description</label>
                <input type="text" name="items[${itemCount}][description]" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="items[${itemCount}][quantity]" required min="1" value="1"
                       onchange="calculateTotal()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Price</label>
                <input type="number" name="items[${itemCount}][price]" required step="0.01" min="0"
                       onchange="calculateTotal()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <button type="button" onclick="removeItem(this)" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    Remove
                </button>
            </div>
        `;
        container.appendChild(itemDiv);
        itemCount++;
    }

    function removeItem(button) {
        button.closest('div.grid').remove();
        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;
        const items = document.querySelectorAll('#itemsContainer .grid');
        items.forEach(item => {
            const quantity = parseFloat(item.querySelector('input[name*="[quantity]"]').value) || 0;
            const price = parseFloat(item.querySelector('input[name*="[price]"]').value) || 0;
            subtotal += quantity * price;
        });

        const gstRate = parseFloat(document.querySelector('input[name="gst_rate"]').value) || 0;
        const discountPercentage = parseFloat(document.querySelector('input[name="discount_percentage"]').value) || 0;

        const gstAmount = (subtotal * gstRate) / 100;
        const discountAmount = (subtotal * discountPercentage) / 100;
        const total = subtotal + gstAmount - discountAmount;

        document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
        document.getElementById('gst').textContent = `₹${gstAmount.toFixed(2)}`;
        document.getElementById('discount').textContent = `₹${discountAmount.toFixed(2)}`;
        document.getElementById('total').textContent = `₹${total.toFixed(2)}`;
    }

    // Show/hide partial payment field based on payment status
    document.querySelector('select[name="payment_status"]').addEventListener('change', function() {
        const partialSection = document.getElementById('partialPaymentSection');
        partialSection.style.display = this.value === 'partial' ? 'block' : 'none';
    });

    // Add first item row by default
    addItem();

    // Add event listeners for real-time calculation
    document.querySelectorAll('input[name="gst_rate"], input[name="discount_percentage"]')
        .forEach(input => input.addEventListener('change', calculateTotal));
    </script>
</body>
</html>
