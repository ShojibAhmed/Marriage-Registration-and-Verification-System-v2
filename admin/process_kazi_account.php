<?php
session_start();
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../includes/security.php";
require_once __DIR__ . "/../Database/connection.php";

function kazi_account_redirect($type, $message) {
    $_SESSION['kazi_account_message'] = ['type' => $type, 'text' => $message];
    header("Location: manage_kazis.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    kazi_account_redirect('danger', 'Invalid account management request.');
}

$csrf_token = $_POST['csrf_token'] ?? null;
if (!is_string($csrf_token) || !verify_csrf_token($csrf_token)) {
    kazi_account_redirect('danger', 'The security token is invalid or expired. Please try again.');
}

$raw_action = $_POST['action'] ?? null;
if (!is_string($raw_action)) {
    kazi_account_redirect('danger', 'Invalid account action.');
}
$action = trim($raw_action);
$allowed_actions = ['renew', 'deactivate', 'reactivate'];
if (!in_array($action, $allowed_actions, true)) {
    kazi_account_redirect('danger', 'Invalid account action.');
}

$kazi_id = filter_var($_POST['kazi_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($kazi_id === false || $kazi_id === null) {
    kazi_account_redirect('danger', 'Invalid Kazi account.');
}

$raw_reason = $_POST['reason'] ?? '';
if (!is_string($raw_reason)) {
    kazi_account_redirect('danger', 'Invalid account action reason.');
}
$reason = trim($raw_reason);
if (in_array($action, ['deactivate', 'reactivate'], true) && (strlen($reason) < 3 || strlen($reason) > 2000)) {
    kazi_account_redirect('danger', 'Provide a reason between 3 and 2000 characters.');
}
if ($action === 'renew' && strlen($reason) > 2000) {
    kazi_account_redirect('danger', 'The renewal reason must not exceed 2000 characters.');
}

$conn = database_connection();
$admin_id = (int)$_SESSION['admin_id'];
$now = new DateTimeImmutable('now', application_timezone());
$now_value = $now->format('Y-m-d H:i:s');
$transaction_started = false;

try {
    if (!$conn->begin_transaction()) {
        throw new RuntimeException('Unable to start account transaction.');
    }
    $transaction_started = true;

    $admin_stmt = mysqli_prepare($conn, "SELECT admin_id FROM admin_users WHERE admin_id = ? AND status = 'Active' LIMIT 1 FOR UPDATE");
    if (!$admin_stmt) {
        throw new RuntimeException('Unable to prepare Admin verification.');
    }
    mysqli_stmt_bind_param($admin_stmt, "i", $admin_id);
    if (!mysqli_stmt_execute($admin_stmt)) {
        throw new RuntimeException('Unable to verify Admin account.');
    }
    $current_admin = mysqli_fetch_assoc(mysqli_stmt_get_result($admin_stmt));
    mysqli_stmt_close($admin_stmt);
    if (!$current_admin) {
        clear_admin_session();
        throw new DomainException('Your Admin account is no longer active.');
    }

    $kazi_stmt = mysqli_prepare($conn, "SELECT kazi_id, status, created_at, expires_at FROM kazi_users WHERE kazi_id = ? LIMIT 1 FOR UPDATE");
    if (!$kazi_stmt) {
        throw new RuntimeException('Unable to prepare Kazi account lookup.');
    }
    mysqli_stmt_bind_param($kazi_stmt, "i", $kazi_id);
    if (!mysqli_stmt_execute($kazi_stmt)) {
        throw new RuntimeException('Unable to load Kazi account.');
    }
    $kazi = mysqli_fetch_assoc(mysqli_stmt_get_result($kazi_stmt));
    mysqli_stmt_close($kazi_stmt);
    if (!$kazi) {
        throw new DomainException('Kazi account was not found.');
    }

    $previous_status = $kazi['status'];
    if (!in_array($previous_status, ['Active', 'Inactive'], true)) {
        throw new DomainException('Kazi account has an unsupported status.');
    }
    $created_at = kazi_date_time($kazi['created_at']);
    $previous_expiry = !empty($kazi['expires_at'])
        ? kazi_date_time($kazi['expires_at'])
        : kazi_add_one_calendar_year($created_at);
    $new_status = $previous_status;
    $new_expiry = $previous_expiry;

    if ($action === 'renew') {
        $renewal_base = ($previous_expiry > $now) ? $previous_expiry : $now;
        $new_expiry = kazi_add_one_calendar_year($renewal_base);
        $update_stmt = mysqli_prepare($conn, "UPDATE kazi_users SET expires_at = ?, last_renewed_at = ? WHERE kazi_id = ?");
        if (!$update_stmt) {
            throw new RuntimeException('Unable to prepare renewal update.');
        }
        $new_expiry_value = $new_expiry->format('Y-m-d H:i:s');
        mysqli_stmt_bind_param($update_stmt, "ssi", $new_expiry_value, $now_value, $kazi_id);
    } elseif ($action === 'deactivate') {
        if ($previous_status !== 'Active') {
            throw new DomainException('Only an Active Kazi account can be deactivated.');
        }
        $new_status = 'Inactive';
        $update_stmt = mysqli_prepare($conn, "UPDATE kazi_users SET status = ? WHERE kazi_id = ?");
        if (!$update_stmt) {
            throw new RuntimeException('Unable to prepare deactivation.');
        }
        mysqli_stmt_bind_param($update_stmt, "si", $new_status, $kazi_id);
    } else {
        if ($previous_status !== 'Inactive') {
            throw new DomainException('Only an Inactive Kazi account can be reactivated.');
        }
        if ($now >= $previous_expiry) {
            throw new DomainException('Renew the expired account before reactivation.');
        }
        $new_status = 'Active';
        $update_stmt = mysqli_prepare($conn, "UPDATE kazi_users SET status = ? WHERE kazi_id = ?");
        if (!$update_stmt) {
            throw new RuntimeException('Unable to prepare reactivation.');
        }
        mysqli_stmt_bind_param($update_stmt, "si", $new_status, $kazi_id);
    }

    if (!mysqli_stmt_execute($update_stmt)) {
        mysqli_stmt_close($update_stmt);
        throw new RuntimeException('Unable to update Kazi account.');
    }
    mysqli_stmt_close($update_stmt);

    $previous_expiry_value = $previous_expiry->format('Y-m-d H:i:s');
    $new_expiry_value = $new_expiry->format('Y-m-d H:i:s');
    $history_stmt = mysqli_prepare(
        $conn,
        "INSERT INTO kazi_account_history (kazi_id, action_type, previous_status, new_status, previous_expiry, new_expiry, admin_id, action_at, reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$history_stmt) {
        throw new RuntimeException('Unable to prepare account history entry.');
    }
    mysqli_stmt_bind_param(
        $history_stmt,
        "isssssiss",
        $kazi_id,
        $action,
        $previous_status,
        $new_status,
        $previous_expiry_value,
        $new_expiry_value,
        $admin_id,
        $now_value,
        $reason
    );
    if (!mysqli_stmt_execute($history_stmt)) {
        mysqli_stmt_close($history_stmt);
        throw new RuntimeException('Unable to record account history.');
    }
    mysqli_stmt_close($history_stmt);

    if (!$conn->commit()) {
        throw new RuntimeException('Unable to commit account action.');
    }
    $transaction_started = false;

    $messages = [
        'renew' => 'Kazi account renewed successfully.',
        'deactivate' => 'Kazi account deactivated successfully.',
        'reactivate' => 'Kazi account reactivated successfully.'
    ];
    kazi_account_redirect('success', $messages[$action]);
} catch (DomainException $exception) {
    if ($transaction_started) {
        $conn->rollback();
    }
    kazi_account_redirect('danger', $exception->getMessage());
} catch (Throwable $exception) {
    if ($transaction_started) {
        $conn->rollback();
    }
    error_log('Kazi account action failed: ' . $exception->getMessage());
    kazi_account_redirect('danger', 'The account action could not be completed. No changes were saved.');
}
