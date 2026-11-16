<?php
session_start();
require_once "../includes/security.php";
require_kazi_auth('html', 'login.php');

$raw_registration_number = $_GET['registration_number'] ?? '';
$registration_number = is_string($raw_registration_number) ? trim($raw_registration_number) : '';

if (empty($registration_number)) {
    die("অবৈধ বা অনুপস্থিত বিবাহ নিবন্ধন নম্বর।");
}

// Redirect to the unified official Digital Certificate with QR code
header("Location: ../Certificate/view_certificate.php?reg=" . urlencode($registration_number));
exit();
?>
