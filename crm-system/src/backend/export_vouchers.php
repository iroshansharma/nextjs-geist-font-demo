<?php
require_once 'auth.php';

// Check if user is admin or super admin
if (!is_logged_in() || ($_SESSION['role_id'] !== 1 && $_SESSION['role_id'] !== 2)) {
    header('Location: ../frontend/index.php');
    exit;
}

// Get voucher types for reference
$voucher_types_json = file_get_contents(__DIR__ . '/voucher_types.json');
$voucher_types = json_decode($voucher_types_json, true)['types'];
$voucher_types_map = array_column($voucher_types, null, 'id');

// Get all voucher claims
$claims_json = file_get_contents(__DIR__ . '/voucher_claims.json');
$claims_data = json_decode($claims_json, true);

// Sort claims by submission date (newest first)
usort($claims_data['claims'], function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});

if ($_GET['format'] === 'excel') {
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="voucher_claims_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');

    // Create Excel content
    echo "Employee\tDate\tVoucher Type\tAmount\tStatus\tSubmitted At\tUpdated At\tComments\n";
    
    foreach ($claims_data['claims'] as $claim) {
        $comments = str_replace("\n", " ", implode(" | ", $claim['comments']));
        echo implode("\t", [
            $claim['username'],
            $claim['date'],
            $voucher_types_map[$claim['type_id']]['name'],
            $claim['amount'],
            ucfirst($claim['status']),
            $claim['submitted_at'],
            $claim['updated_at'],
            $comments
        ]) . "\n";
    }
} else {
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="voucher_claims_' . date('Y-m-d') . '.pdf"');

    // Basic HTML for PDF
    $html = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f5f5f5; }
            .header { text-align: center; margin-bottom: 20px; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>Voucher Claims Report</h2>
            <p>Generated on ' . date('Y-m-d H:i:s') . '</p>
        </div>
        
        <table>
            <tr>
                <th>Employee</th>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Comments</th>
            </tr>';

    foreach ($claims_data['claims'] as $claim) {
        $comments = str_replace("\n", "<br>", implode("<br>", $claim['comments']));
        $html .= '<tr>
            <td>' . htmlspecialchars($claim['username']) . '</td>
            <td>' . htmlspecialchars($claim['date']) . '</td>
            <td>' . htmlspecialchars($voucher_types_map[$claim['type_id']]['name']) . '</td>
            <td>₹' . number_format($claim['amount'], 2) . '</td>
            <td>' . ucfirst(htmlspecialchars($claim['status'])) . '</td>
            <td>' . $comments . '</td>
        </tr>';
    }

    $html .= '</table>
        <div class="footer">
            <p>This is a system-generated report.</p>
        </div>
    </body>
    </html>';

    // Use browser's PDF generation capability
    echo $html;
}
?>
