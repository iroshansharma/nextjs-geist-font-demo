<?php
require_once 'auth.php';

// Call the logout function
logout();

// Redirect to login page
header('Location: ../frontend/index.php');
exit;
?>
