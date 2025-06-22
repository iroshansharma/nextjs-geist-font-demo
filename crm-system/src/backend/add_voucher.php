<?php
require_once 'auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['type_id']) || empty($_POST['amount']) || empty($_POST['date']) || empty($_POST['description'])) {
        header('Location: ../frontend/submit-voucher.php?error=missing_fields');
        exit;
    }

    // Load existing claims
    $claims_json = file_get_contents(__DIR__ . '/voucher_claims.json');
    $claims_data = json_decode($claims_json, true);

    // Handle file upload
    $receipt_path = '';
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['receipt']['name']);
        $extension = strtolower($file_info['extension']);
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($extension, $allowed_types)) {
            header('Location: ../frontend/submit-voucher.php?error=invalid_file');
            exit;
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = __DIR__ . '/../uploads/vouchers';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $filename = uniqid('voucher_') . '.' . $extension;
        $receipt_path = 'uploads/vouchers/' . $filename;

        // Move uploaded file
        move_uploaded_file($_FILES['receipt']['tmp_name'], __DIR__ . '/../' . $receipt_path);
    }

    // Create new claim
    $new_claim = [
        'id' => $claims_data['next_id'],
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'type_id' => intval($_POST['type_id']),
        'amount' => floatval($_POST['amount']),
        'date' => $_POST['date'],
        'description' => $_POST['description'],
        'receipt_path' => $receipt_path,
        'status' => 'pending',
        'comments' => [],
        'submitted_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Add claim to data
    $claims_data['claims'][] = $new_claim;
    $claims_data['next_id']++;
    $claims_data['last_updated'] = date('Y-m-d H:i:s');

    // Save updated claims
    file_put_contents(__DIR__ . '/voucher_claims.json', json_encode($claims_data, JSON_PRETTY_PRINT));

    header('Location: ../frontend/my-vouchers.php?success=claim_submitted');
    exit;
} else {
    header('Location: ../frontend/submit-voucher.php');
    exit;
}
?>
