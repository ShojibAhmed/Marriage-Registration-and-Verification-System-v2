<?php
session_start();
require_once "../includes/security.php";

$access_type = null;
if (!empty($_SESSION['admin_id'])) {
    require_admin_auth('../admin/login.php');
    $access_type = 'admin';
} elseif (!empty($_SESSION['kazi_id'])) {
    require_kazi_auth('html', '../kazi/login.php');
    $access_type = 'kazi';
} else {
    header("Location: ../kazi/login.php");
    exit();
}

require_once "../Database/connection.php";
require_once "../includes/upload_helper.php";

$raw_case_reference = $_GET['ref'] ?? null;
if (!is_string($raw_case_reference)) {
    http_response_code(400);
    exit('Invalid document request.');
}
$case_reference = trim($raw_case_reference);
if ($case_reference === '' || strlen($case_reference) > 50) {
    http_response_code(400);
    exit('Invalid document request.');
}

$stmt = mysqli_prepare($conn, "SELECT kazi_id, supporting_document FROM divorce_records WHERE case_reference = ? LIMIT 1");
if (!$stmt) {
    throw new RuntimeException('Unable to prepare divorce document lookup.');
}
mysqli_stmt_bind_param($stmt, "s", $case_reference);
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    throw new RuntimeException('Unable to load divorce document record.');
}
$record = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$record || empty($record['supporting_document'])) {
    http_response_code(404);
    exit('Document not found.');
}
if ($access_type === 'kazi' && (int)$record['kazi_id'] !== (int)$_SESSION['kazi_id']) {
    http_response_code(403);
    exit('You are not authorized to access this document.');
}

try {
    $document_path = resolve_divorce_document_path($record['supporting_document']);
} catch (RuntimeException $exception) {
    error_log('Unable to resolve divorce document storage: ' . $exception->getMessage());
    http_response_code(500);
    exit('Document service unavailable.');
}
if ($document_path === null) {
    http_response_code(404);
    exit('Document not found.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($document_path);
$allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
if (!in_array($mime_type, $allowed_types, true)) {
    http_response_code(415);
    exit('Unsupported document type.');
}

$extension = strtolower(pathinfo($document_path, PATHINFO_EXTENSION));
$download_name = 'divorce-document.' . $extension;
header('Content-Type: ' . $mime_type);
header('Content-Disposition: inline; filename="' . $download_name . '"');
header('Content-Length: ' . filesize($document_path));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, max-age=0');
readfile($document_path);
exit();
