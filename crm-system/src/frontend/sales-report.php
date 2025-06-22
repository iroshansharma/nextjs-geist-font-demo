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
$total_gst = 0;
$total_discount = 0;

foreach ($filtered_sales as $sale) {
    $total_amount += $sale['total_amount'];
    $total_paid += $sale['paid_amount'] ?? 0;
    $total_gst += $sale['gst_amount'] ?? 0;
    $total_discount += $sale['discount_amount'] ?? 0;
}
$total_outstanding = $total_amount - $total_paid;

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="sales_report_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 5px; }
        .header { background-color: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Sales Report</h2>
    <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
    <?php if ($start_date || $end_date): ?>
        <p>Period: <?php echo $start_date ?: 'All'; ?> to <?php echo $end_date ?: 'All'; ?></p>
    <?php endif; ?>

    <!-- Summary -->
    <table>
        <tr class="header">
            <th colspan="2">Summary</th>
        </tr>
        <tr>
            <td>Total Sales Amount</td>
            <td>₹<?php echo number_format($total_amount, 2); ?></td>
        </tr>
        <tr>
            <td>Total Paid Amount</td>
            <td>₹<?php echo number_format($total_paid, 2); ?></td>
        </tr>
        <tr>
            <td>Total Outstanding Amount</td>
            <td>₹<?php echo number_format($total_outstanding, 2); ?></td>
        </tr>
        <tr>
            <td>Total GST</td>
            <td>₹<?php echo number_format($total_gst, 2); ?></td>
        </tr>
        <tr>
            <td>Total Discount</td>
            <td>₹<?php echo number_format($total_discount, 2); ?></td>
        </tr>
    </table>

    <br><br>

    <!-- Detailed Report -->
    <table>
        <tr class="header">
            <th>Invoice Number</th>
            <th>Date</th>
            <th>Customer Name</th>
            <th>Total Amount</th>
            <th>GST</th>
            <th>Discount</th>
            <th>Paid Amount</th>
            <th>Balance</th>
            <th>Status</th>
        </tr>
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
                <td><?php echo htmlspecialchars($sale['invoice_number']); ?></td>
                <td><?php echo date('Y-m-d', strtotime($sale['created_at'])); ?></td>
                <td><?php echo $customer ? htmlspecialchars($customer['name']) : 'N/A'; ?></td>
                <td>₹<?php echo number_format($sale['total_amount'], 2); ?></td>
                <td>₹<?php echo number_format($sale['gst_amount'] ?? 0, 2); ?></td>
                <td>₹<?php echo number_format($sale['discount_amount'] ?? 0, 2); ?></td>
                <td>₹<?php echo number_format($sale['paid_amount'] ?? 0, 2); ?></td>
                <td>₹<?php echo number_format($sale['total_amount'] - ($sale['paid_amount'] ?? 0), 2); ?></td>
                <td><?php echo ucfirst($sale['payment_status']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($status || $customer_id): ?>
        <p>
            <?php if ($status) echo "Status Filter: " . ucfirst($status); ?>
            <?php if ($customer_id && $customer) echo "<br>Customer Filter: " . htmlspecialchars($customer['name']); ?>
        </p>
    <?php endif; ?>
</body>
</html>
