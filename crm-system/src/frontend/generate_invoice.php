<?php
require_once '../backend/auth.php';
require_once '../vendor/autoload.php'; // For TCPDF

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2, 3])) {
    header('Location: index.php');
    exit;
}

// Get payment ID
$payment_id = (int)($_GET['payment_id'] ?? 0);

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

if (!$customer) {
    header('Location: customer-payments.php?error=Customer not found');
    exit;
}

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('CRM System');
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('Invoice #' . $payment['invoice_number']);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Company Logo and Details
$pdf->Image('../assets/logo.png', 15, 15, 50);
$pdf->SetXY(15, 40);
$pdf->Cell(0, 10, 'Your Company Name', 0, 1);
$pdf->Cell(0, 10, '123 Business Street', 0, 1);
$pdf->Cell(0, 10, 'City, State, ZIP', 0, 1);
$pdf->Cell(0, 10, 'Phone: (123) 456-7890', 0, 1);

// Invoice Details
$pdf->SetXY(120, 15);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'INVOICE', 0, 1, 'R');
$pdf->SetFont('helvetica', '', 12);
$pdf->SetXY(120, 25);
$pdf->Cell(0, 10, 'Invoice #: ' . $payment['invoice_number'], 0, 1, 'R');
$pdf->SetXY(120, 35);
$pdf->Cell(0, 10, 'Date: ' . date('Y-m-d', strtotime($payment['created_at'])), 0, 1, 'R');

// Customer Details
$pdf->SetXY(15, 80);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Bill To:', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $customer['name'], 0, 1);
$pdf->MultiCell(0, 10, $customer['address'], 0, 'L');
$pdf->Cell(0, 10, 'Phone: ' . $customer['phone'], 0, 1);
$pdf->Cell(0, 10, 'Email: ' . $customer['email'], 0, 1);

// Payment Details Table
$pdf->SetXY(15, 140);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(90, 10, 'Description', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Type', 1, 0, 'C', true);
$pdf->Cell(45, 10, 'Amount', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(90, 10, 'Payment', 1, 0, 'L');
$pdf->Cell(40, 10, ucfirst($payment['payment_type']), 1, 0, 'C');
$pdf->Cell(45, 10, '₹' . number_format($payment['amount'], 2), 1, 1, 'R');

// Total
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(130, 10, 'Total Amount:', 1, 0, 'R');
$pdf->Cell(45, 10, '₹' . number_format($payment['amount'], 2), 1, 1, 'R');

// Notes
if (!empty($payment['notes'])) {
    $pdf->SetXY(15, 180);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Notes:', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 10, $payment['notes'], 0, 'L');
}

// Terms and Conditions
$pdf->SetXY(15, 220);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Terms and Conditions:', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 10, '1. Payment is due upon receipt of invoice.
2. Please include invoice number in all correspondence.
3. Make checks payable to Your Company Name.
4. Thank you for your business!', 0, 'L');

// Output PDF
$pdf->Output('Invoice_' . $payment['invoice_number'] . '.pdf', 'D');
?>
