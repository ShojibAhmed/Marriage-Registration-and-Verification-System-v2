<?php
/**
 * Security and Validation Utilities
 * CSRF Protection, Input Sanitization, and Authorization Guards
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function application_timezone() {
    static $timezone = null;
    if ($timezone === null) {
        $timezone = new DateTimeZone('Asia/Dhaka');
    }
    return $timezone;
}

function kazi_add_one_calendar_year(DateTimeImmutable $date) {
    $year = (int)$date->format('Y') + 1;
    $month = (int)$date->format('n');
    $day = (int)$date->format('j');

    if ($month === 2 && $day === 29 && !checkdate(2, 29, $year)) {
        return $date->setDate($year, 3, 1);
    }

    return $date->setDate($year, $month, $day);
}

function kazi_date_time($value) {
    if ($value instanceof DateTimeInterface) {
        return DateTimeImmutable::createFromInterface($value)->setTimezone(application_timezone());
    }
    return new DateTimeImmutable((string)$value, application_timezone());
}

function clear_kazi_session() {
    unset(
        $_SESSION['kazi_id'],
        $_SESSION['kazi_name'],
        $_SESSION['license_no'],
        $_SESSION['mobile_no'],
        $_SESSION['division'],
        $_SESSION['district'],
        $_SESSION['upazila']
    );
}

function clear_admin_session() {
    unset(
        $_SESSION['admin_id'],
        $_SESSION['admin_username'],
        $_SESSION['admin_name'],
        $_SESSION['admin_role']
    );
}

function database_connection() {
    global $conn;
    if (!isset($conn) || !$conn) {
        require_once __DIR__ . '/../Database/connection.php';
    }
    return $conn;
}

function current_kazi_account() {
    if (empty($_SESSION['kazi_id'])) {
        return null;
    }

    $conn = database_connection();
    $stmt = mysqli_prepare($conn, "SELECT kazi_id, full_name, mobile_no, license_no, division, district, upazila, status, created_at, expires_at FROM kazi_users WHERE kazi_id = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException('Unable to prepare Kazi account validation.');
    }

    $kazi_id = (int)$_SESSION['kazi_id'];
    mysqli_stmt_bind_param($stmt, "i", $kazi_id);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new RuntimeException('Unable to validate Kazi account.');
    }

    $account = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
    mysqli_stmt_close($stmt);
    return $account;
}

function kazi_account_is_valid(array $account, ?DateTimeImmutable $now = null) {
    if (($account['status'] ?? null) !== 'Active') {
        return false;
    }

    $created_at = kazi_date_time($account['created_at']);
    $expires_at = !empty($account['expires_at'])
        ? kazi_date_time($account['expires_at'])
        : kazi_add_one_calendar_year($created_at);
    $current_time = $now ?: new DateTimeImmutable('now', application_timezone());

    return $current_time < $expires_at;
}

function is_current_kazi_session() {
    if (empty($_SESSION['kazi_id'])) {
        return false;
    }

    try {
        $account = current_kazi_account();
        if (!$account || !kazi_account_is_valid($account)) {
            clear_kazi_session();
            return false;
        }

        $_SESSION['kazi_name'] = $account['full_name'];
        $_SESSION['license_no'] = $account['license_no'];
        $_SESSION['mobile_no'] = $account['mobile_no'];
        $_SESSION['division'] = $account['division'];
        $_SESSION['district'] = $account['district'];
        $_SESSION['upazila'] = $account['upazila'];
        return true;
    } catch (Throwable $exception) {
        clear_kazi_session();
        error_log('Kazi session validation failed: ' . $exception->getMessage());
        throw $exception;
    }
}

function is_current_admin_session() {
    if (empty($_SESSION['admin_id'])) {
        return false;
    }

    $conn = database_connection();
    $stmt = mysqli_prepare($conn, "SELECT status FROM admin_users WHERE admin_id = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException('Unable to prepare Admin account validation.');
    }

    $admin_id = (int)$_SESSION['admin_id'];
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new RuntimeException('Unable to validate Admin account.');
    }

    $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$admin || $admin['status'] !== 'Active') {
        clear_admin_session();
        return false;
    }
    return true;
}

/**
 * Generate or retrieve the active CSRF token.
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF token input field for HTML forms.
 */
function csrf_field() {
    $token = get_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify submitted CSRF token.
 */
function verify_csrf_token($submitted_token) {
    if (empty($_SESSION['csrf_token']) || empty($submitted_token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submitted_token);
}

/**
 * Validate Bangladesh prototype 11-digit mobile number rule.
 * Must be exactly 11 digits (e.g. 01712345678).
 */
function validate_bd_mobile($mobile) {
    $clean = trim($mobile);
    return (bool)preg_match('/^[0-9]{11}$/', $clean);
}

/**
 * Validate name contains only letters (English/Bengali) and spaces.
 */
function validate_person_name($name) {
    $clean = trim($name);
    if (empty($clean) || strlen($clean) < 2 || strlen($clean) > 100) {
        return false;
    }
    // Allow Unicode letters (covers Bengali script \p{Bengali}) and English letters, spaces, dots, hyphens
    return (bool)preg_match('/^[\p{L}\s\.\-]+$/u', $clean);
}

/**
 * Require current Kazi account authorization on every protected request.
 */
function require_kazi_auth($response_type = 'html', $login_url = '../kazi/login.php') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        if (is_current_kazi_session()) {
            return;
        }
    } catch (Throwable $exception) {
        if ($response_type === 'json') {
            http_response_code(503);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Authentication service unavailable.']);
        } else {
            http_response_code(503);
            echo 'Authentication service unavailable.';
        }
        exit();
    }

    clear_kazi_session();
    if ($response_type === 'json') {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit();
    }

    header("Location: " . $login_url);
    exit();
}

function require_admin_auth($login_url = 'login.php') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        if (is_current_admin_session()) {
            return;
        }
    } catch (Throwable $exception) {
        error_log('Admin session validation failed: ' . $exception->getMessage());
        http_response_code(503);
        echo 'Authentication service unavailable.';
        exit();
    }

    clear_admin_session();
    header("Location: " . $login_url);
    exit();
}

/**
 * Safe HTML escape output.
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Calculate completed age in full years on a specified reference date.
 *
 * Strict validation:
 * - Requires valid calendar dates in YYYY-MM-DD format.
 * - Handles birthday boundaries and leap-year birthdays (Feb 29).
 * - Returns null if date is invalid, empty, or if DOB is in the future relative to reference date.
 *
 * @param string $dob_string Date of birth (YYYY-MM-DD)
 * @param string $reference_date_string Reference/solemnization date (YYYY-MM-DD)
 * @return int|null Completed age in years, or null on failure
 */
function calculate_age_on_date($dob_string, $reference_date_string = 'now') {
    $dob_clean = trim((string)$dob_string);
    $ref_clean = trim((string)$reference_date_string);

    if (empty($dob_clean) || empty($ref_clean)) {
        return null;
    }

    if ($ref_clean === 'now') {
        $ref_clean = date('Y-m-d');
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob_clean) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ref_clean)) {
        return null;
    }

    list($y1, $m1, $d1) = explode('-', $dob_clean);
    list($y2, $m2, $d2) = explode('-', $ref_clean);
    if (!checkdate((int)$m1, (int)$d1, (int)$y1) || !checkdate((int)$m2, (int)$d2, (int)$y2)) {
        return null;
    }

    try {
        $dob = new DateTime($dob_clean);
        $ref = new DateTime($ref_clean);

        if ($dob > $ref) {
            return null; // Future date of birth relative to reference date
        }

        $diff = $ref->diff($dob);
        return (int)$diff->y;
    } catch (Exception $e) {
        return null;
    }
}
