<?php
$page_title = "Registered Kazis";
require_once "includes/header.php";
require_once "../includes/security.php";

$notice = $_SESSION['kazi_account_message'] ?? null;
unset($_SESSION['kazi_account_message']);

$search = trim($_GET['search'] ?? '');
$sql = "SELECT k.kazi_id, k.full_name, k.mobile_no, k.email, k.license_no, k.division, k.district, k.upazila, k.status, k.created_at, k.expires_at, k.last_renewed_at,
               (SELECT COUNT(*) FROM marriages m WHERE m.kazi_license_no = k.license_no) AS total_marriages
        FROM kazi_users k";
if ($search !== '') {
    $sql .= " WHERE k.full_name LIKE ? OR k.license_no LIKE ? OR k.mobile_no LIKE ? OR k.district LIKE ?";
}
$sql .= " ORDER BY k.kazi_id DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    throw new RuntimeException('Unable to prepare Kazi account list.');
}
if ($search !== '') {
    $term = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "ssss", $term, $term, $term, $term);
}
if (!mysqli_stmt_execute($stmt)) {
    throw new RuntimeException('Unable to load Kazi accounts.');
}
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$selected_kazi = null;
$history_rows = [];
$history_id = filter_var($_GET['history'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($history_id !== false && $history_id !== null) {
    $detail_stmt = mysqli_prepare($conn, "SELECT kazi_id, full_name, mobile_no, email, license_no, division, district, upazila, full_address, status, created_at, expires_at, last_renewed_at FROM kazi_users WHERE kazi_id = ? LIMIT 1");
    mysqli_stmt_bind_param($detail_stmt, "i", $history_id);
    mysqli_stmt_execute($detail_stmt);
    $selected_kazi = mysqli_fetch_assoc(mysqli_stmt_get_result($detail_stmt));
    mysqli_stmt_close($detail_stmt);

    if ($selected_kazi) {
        $history_stmt = mysqli_prepare($conn, "SELECT history_id, kazi_id, action_type, previous_status, new_status, previous_expiry, new_expiry, admin_id, action_at, reason FROM kazi_account_history WHERE kazi_id = ? ORDER BY action_at DESC, history_id DESC");
        mysqli_stmt_bind_param($history_stmt, "i", $history_id);
        mysqli_stmt_execute($history_stmt);
        $history_result = mysqli_stmt_get_result($history_stmt);
        while ($history_row = mysqli_fetch_assoc($history_result)) {
            $history_rows[] = $history_row;
        }
        mysqli_stmt_close($history_stmt);
    }
}

$now = new DateTimeImmutable('now', application_timezone());
$timezone = application_timezone();
$format_account_date = static function ($value) use ($timezone) {
    return empty($value) ? 'Not set' : (new DateTimeImmutable($value, $timezone))->format('d M Y, H:i');
};
?>

<?php if ($notice): ?>
    <div class="alert alert-<?= htmlspecialchars($notice['type'] === 'success' ? 'success' : 'danger', ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($notice['text'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['history']) && !$selected_kazi): ?>
    <div class="alert alert-warning">Kazi account was not found.</div>
<?php endif; ?>

<?php if ($selected_kazi): ?>
    <div class="data-card mb-4">
        <div class="data-card-header">
            <h6 class="fw-bold mb-0 text-dark">Account Details &amp; History: <?= htmlspecialchars($selected_kazi['full_name'], ENT_QUOTES, 'UTF-8') ?></h6>
            <a href="manage_kazis.php<?= $search !== '' ? '?search=' . urlencode($search) : '' ?>" class="btn btn-sm btn-outline-secondary">Close</a>
        </div>
        <div class="p-3">
            <div class="row g-2 small">
                <div class="col-md-4"><strong>Kazi ID:</strong> #<?= (int)$selected_kazi['kazi_id'] ?></div>
                <div class="col-md-4"><strong>License:</strong> <?= htmlspecialchars($selected_kazi['license_no'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-md-4"><strong>Manual status:</strong> <?= htmlspecialchars($selected_kazi['status'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-md-4"><strong>Registration:</strong> <?= htmlspecialchars($format_account_date($selected_kazi['created_at']), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-md-4"><strong>Expiry:</strong> <?= htmlspecialchars($format_account_date($selected_kazi['expires_at']), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-md-4"><strong>Last renewal:</strong> <?= htmlspecialchars($format_account_date($selected_kazi['last_renewed_at']), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-md-4"><strong>Mobile:</strong> <?= htmlspecialchars($selected_kazi['mobile_no'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-md-4"><strong>Email:</strong> <?= htmlspecialchars($selected_kazi['email'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-md-4"><strong>Jurisdiction:</strong> <?= htmlspecialchars($selected_kazi['upazila'] . ', ' . $selected_kazi['district'] . ', ' . $selected_kazi['division'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-12"><strong>Address:</strong> <?= htmlspecialchars($selected_kazi['full_address'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Kazi ID</th><th>Action</th><th>Previous Status</th><th>New Status</th>
                        <th>Previous Expiry</th><th>New Expiry</th><th>Admin ID</th><th>Timestamp</th><th>Reason</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (!$history_rows): ?>
                        <tr><td colspan="9" class="text-center text-muted py-3">No account actions recorded.</td></tr>
                    <?php else: ?>
                        <?php foreach ($history_rows as $history): ?>
                            <tr>
                                <td>#<?= (int)$history['kazi_id'] ?></td>
                                <td><?= htmlspecialchars(ucfirst($history['action_type']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($history['previous_status'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($history['new_status'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($format_account_date($history['previous_expiry']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($format_account_date($history['new_expiry']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $history['admin_id'] === null ? 'System' : '#' . (int)$history['admin_id'] ?></td>
                                <td><?= htmlspecialchars($format_account_date($history['action_at']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= nl2br(htmlspecialchars($history['reason'] ?? '', ENT_QUOTES, 'UTF-8')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="data-card">
    <div class="data-card-header flex-wrap gap-2">
        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-user-tie text-primary me-2"></i>Registered Kazi Accounts</h6>
        <form method="GET" class="d-flex" style="max-width: 320px;">
            <div class="input-group input-group-sm">
                <input type="text" name="search" class="form-control" placeholder="Search name, license, mobile..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                <?php if ($search !== ''): ?>
                    <a href="manage_kazis.php" class="btn btn-outline-danger" title="Clear"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>ID</th><th>Kazi Name &amp; Contact</th><th>Generated License</th>
                    <th>Chamber Jurisdiction</th><th>Marriages Recorded</th><th>Registration</th>
                    <th>Expiry / Last Renewal</th><th>Manual Status</th><th>Validity</th><th>Actions</th>
                </tr>
            </thead>
            <tbody class="small">
                <?php if (mysqli_num_rows($result) === 0): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">No registered Kazi accounts found.</td></tr>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                        $expiry = !empty($row['expires_at'])
                            ? new DateTimeImmutable($row['expires_at'], $timezone)
                            : kazi_add_one_calendar_year(new DateTimeImmutable($row['created_at'], $timezone));
                        $is_valid = $now < $expiry;
                        ?>
                        <tr>
                            <td>#<?= (int)$row['kazi_id'] ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($row['mobile_no'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="text-muted"><i class="fa-solid fa-envelope me-1"></i><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td><span class="badge bg-light text-primary border font-monospace"><?= htmlspecialchars($row['license_no'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= htmlspecialchars($row['upazila'] . ', ' . $row['district'] . ', ' . $row['division'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-info-subtle text-info"><?= (int)$row['total_marriages'] ?></span></td>
                            <td><?= htmlspecialchars($format_account_date($row['created_at']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div><strong>Expiry:</strong> <?= htmlspecialchars($format_account_date($expiry->format('Y-m-d H:i:s')), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="text-muted"><strong>Renewed:</strong> <?= htmlspecialchars($format_account_date($row['last_renewed_at']), ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $row['status'] === 'Active' ? 'success' : 'danger' ?>-subtle text-<?= $row['status'] === 'Active' ? 'success' : 'danger' ?>">
                                    <?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><span class="badge bg-<?= $is_valid ? 'success' : 'secondary' ?>-subtle text-<?= $is_valid ? 'success' : 'secondary' ?>"><?= $is_valid ? 'Valid' : 'Expired' ?></span></td>
                            <td class="text-nowrap">
                                <a class="btn btn-sm btn-outline-primary mb-1" href="manage_kazis.php?history=<?= (int)$row['kazi_id'] ?><?= $search !== '' ? '&amp;search=' . urlencode($search) : '' ?>">Details / History</a>
                                <form method="POST" action="process_kazi_account.php" class="mb-1">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="kazi_id" value="<?= (int)$row['kazi_id'] ?>">
                                    <input type="hidden" name="action" value="renew">
                                    <button type="submit" class="btn btn-sm btn-outline-success w-100" onclick="return confirm('Renew this Kazi account for one calendar year?')">Renew Account</button>
                                </form>
                                <?php if ($row['status'] === 'Active'): ?>
                                    <form method="POST" action="process_kazi_account.php" class="mb-1">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="kazi_id" value="<?= (int)$row['kazi_id'] ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <input type="text" name="reason" class="form-control form-control-sm mb-1" placeholder="Reason to deactivate" minlength="3" maxlength="2000" required>
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Deactivate this Kazi account immediately?')">Deactivate</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="process_kazi_account.php" class="mb-1">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="kazi_id" value="<?= (int)$row['kazi_id'] ?>">
                                        <input type="hidden" name="action" value="reactivate">
                                        <input type="text" name="reason" class="form-control form-control-sm mb-1" placeholder="Reason to reactivate" minlength="3" maxlength="2000" required>
                                        <button type="submit" class="btn btn-sm btn-outline-warning w-100" <?= $is_valid ? '' : 'disabled' ?> onclick="return confirm('Reactivate this Kazi account without extending its expiry?')">Reactivate</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
