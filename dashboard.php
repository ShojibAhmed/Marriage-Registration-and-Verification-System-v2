<?php
session_start();
require_once "includes/security.php";

// If Kazi is logged in, redirect directly to Kazi dashboard
try {
    if (is_current_kazi_session()) {
        header("Location: kazi/dashboard.php");
        exit();
    }
} catch (Throwable $exception) {
    clear_kazi_session();
}

// If Admin is logged in, redirect to admin dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

// Public visitors should view the official homepage
header("Location: index.php");
exit();
