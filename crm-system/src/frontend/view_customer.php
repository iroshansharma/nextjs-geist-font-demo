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
    <title>View Customer - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">Customer Details</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="customer-management.php" class="text-blue-600 hover:text-blue-800">Back to Customers</a>
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

        <!-- Customer Details -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-medium text-gray-900">Customer Information</h3>
                    <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" 
                       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Edit Customer
                    </a>
                </div>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Basic Information</h4>
                        <div class="mt-2 space-y-2">
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">Name:</span> 
                                <?php echo htmlspecialchars($customer['name']); ?>
                            </p>
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">Email:</span> 
                                <?php echo htmlspecialchars($customer['email']); ?>
                            </p>
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">Phone:</span> 
                                <?php echo htmlspecialchars($customer['phone']); ?>
                            </p>
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">Source:</span> 
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    switch($customer['source']) {
                                        case 'walk-in':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'referral':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'online':
                                            echo 'bg-purple-100 text-purple-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst(htmlspecialchars($customer['source'])); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Additional Details</h4>
                        <div class="mt-2 space-y-2">
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">Address:</span><br>
                                <?php echo nl2br(htmlspecialchars($customer['address'])); ?>
                            </p>
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">Notes:</span><br>
                                <?php echo nl2br(htmlspecialchars($customer['notes'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Follow-up Section -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Follow-ups</h3>
                    <button onclick="document.getElementById('addFollowupForm').classList.toggle('hidden')"
                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Add Follow-up
                    </button>
                </div>

                <!-- Add Follow-up Form -->
                <form id="addFollowupForm" action="../backend/add_followup.php" method="POST" class="hidden mb-6 p-4 bg-gray-50 rounded-lg">
                    <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="call">Phone Call</option>
                                <option value="email">Email</option>
                                <option value="meeting">Meeting</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Next Follow-up Date</label>
                            <input type="date" name="next_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" required rows="3" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Save Follow-up
                        </button>
                    </div>
                </form>

                <!-- Follow-ups List -->
                <div class="space-y-4">
                    <?php if (!empty($customer['follow_ups'])): ?>
                        <?php foreach (array_reverse($customer['follow_ups']) as $followup): ?>
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            switch($followup['type']) {
                                                case 'call':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'email':
                                                    echo 'bg-purple-100 text-purple-800';
                                                    break;
                                                case 'meeting':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo ucfirst(htmlspecialchars($followup['type'])); ?>
                                        </span>
                                        <span class="ml-2 text-sm text-gray-500">
                                            <?php echo date('Y-m-d H:i', strtotime($followup['created_at'])); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($followup['next_date'])): ?>
                                        <span class="text-sm text-gray-500">
                                            Next Follow-up: <?php echo date('Y-m-d', strtotime($followup['next_date'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="mt-2 text-sm text-gray-900">
                                    <?php echo nl2br(htmlspecialchars($followup['notes'])); ?>
                                </p>
                                <p class="mt-2 text-xs text-gray-500">
                                    By <?php echo htmlspecialchars($followup['created_by']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">No follow-ups recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
