<?php
require_once 'auth.php';

// Check if user is admin or super admin
if (!is_logged_in() || ($_SESSION['role_id'] !== 1 && $_SESSION['role_id'] !== 2)) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && isset($_GET['action'])) {
    $claim_id = intval($_GET['id']);
    $action = $_GET['action'];

    // Load existing claims
    $claims_json = file_get_contents(__DIR__ . '/voucher_claims.json');
    $claims_data = json_decode($claims_json, true);

    // Find the claim to update
    $claim_index = -1;
    foreach ($claims_data['claims'] as $index => $claim) {
        if ($claim['id'] === $claim_id) {
            $claim_index = $index;
            break;
        }
    }

    if ($claim_index === -1) {
        header('Location: ../frontend/manage-vouchers.php?error=claim_not_found');
        exit;
    }

    // Update claim based on action
    switch ($action) {
        case 'approve':
            if ($claims_data['claims'][$claim_index]['status'] === 'pending') {
                $claims_data['claims'][$claim_index]['status'] = 'approved';
                $claims_data['claims'][$claim_index]['comments'][] = 'Approved by ' . $_SESSION['username'] . ' on ' . date('Y-m-d H:i:s');
            }
            break;

        case 'reject':
            if ($claims_data['claims'][$claim_index]['status'] === 'pending') {
                $claims_data['claims'][$claim_index]['status'] = 'rejected';
                $claims_data['claims'][$claim_index]['comments'][] = 'Rejected by ' . $_SESSION['username'] . ' on ' . date('Y-m-d H:i:s');
            }
            break;

        case 'paid':
            if ($claims_data['claims'][$claim_index]['status'] === 'approved') {
                $claims_data['claims'][$claim_index]['status'] = 'paid';
                $claims_data['claims'][$claim_index]['comments'][] = 'Marked as paid by ' . $_SESSION['username'] . ' on ' . date('Y-m-d H:i:s');
                $claims_data['claims'][$claim_index]['paid_at'] = date('Y-m-d H:i:s');
            }
            break;

        default:
            header('Location: ../frontend/manage-vouchers.php?error=invalid_action');
            exit;
    }

    $claims_data['claims'][$claim_index]['updated_at'] = date('Y-m-d H:i:s');
    $claims_data['last_updated'] = date('Y-m-d H:i:s');

    // Save updated claims
    file_put_contents(__DIR__ . '/voucher_claims.json', json_encode($claims_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/manage-vouchers.php?success=' . $action);
    exit;
} else {
    header('Location: ../frontend/manage-vouchers.php');
    exit;
}
?>
