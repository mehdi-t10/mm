<?php
// Test du login directement
$_POST['email'] = 'az@az.com';
$_POST['password'] = 'az2026';

// Inclure le fichier login
ob_start();
include 'api/auth/login.php';
$output = ob_get_clean();

echo $output;
?>

