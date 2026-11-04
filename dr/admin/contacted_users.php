<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>

<?php
// ── Auto-add status column if not exists ──────────────────
$col_check = mysqli_query($con, "SHOW COLUMNS FROM contact LIKE 'status'");
if (mysqli_num_rows($col_check) == 0) {
    mysqli_query($con, "ALTER TABLE contact ADD COLUMN status TINYINT(1) DEFAULT 0 AFTER msg");
}

// ── AJAX: Mark as read (checkbox) ─────────────────────────
if (isset($_POST['mark_read']) && is_numeric($_POST['mark_read'])) {
    $id  = (int)$_POST['mark_read'];
    $val = isset($_POST['checked']) && $_POST['checked'] == '1' ? 1 : 0;
    mysqli_query($con, "UPDATE contact SET status = $val WHERE id = $id");
    echo json_encode(['success' => true]);
    exit();
}

// ── AJAX: Delete ───────────────────────────────────────────
if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    mysqli_query($con, "DELETE FROM contact WHERE id = $id");
    echo json_encode(['success' => true]);
    exit();
}

// ── Filters ───────────────────────────────────────────────
$date_from = isset($_GET['date_from']) && !empty($_GET['date_from']) ? mysqli_real_escape_string($con, $_GET['date_from']) : '';
$date_to   = isset($_GET['date_to'])   && !empty($_GET['date_to'])   ? mysqli_real_escape_string($con, $_GET['date_to'])   : '';
$f_status  = isset($_GET['f_status'])  && $_GET['f_status'] !== ''   ? (int)$_GET['f_status']  : '';
$f_subject = isset($_GET['f_subject']) && !empty($_GET['f_subject']) ? mysqli_real_escape_string($con, $_GET['f_subject']) : '';

$where = [];
if ($date_from !== '') $where[] = "DATE(created_at) >= '$date_from'";
if ($date_to   !== '') $where[] = "DATE(created_at) <= '$date_to'";
if ($f_status  !== '') $where[] = "status = $f_status";
if ($f_subject !== '') $where[] = "subject = '$f_subject'";

$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// ── Pagination ─────────────────────────────────────────────
$per_page    = 10;
$page        = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset      = ($page - 1) * $per_page;

$total_q     = mysqli_query($con, "SELECT COUNT(*) as total FROM contact $where_sql");
$total_rows  = mysqli_fetch_assoc($total_q)['total'];
$total_pages = ceil($total_rows / $per_page);

$records     = mysqli_query($con, "SELECT * FROM contact $where_sql ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");

// Distinct subjects for filter dropdown
$subjects_q  = mysqli_query($con, "SELECT DISTINCT subject FROM contact WHERE subject != '' ORDER BY subject ASC");

// Counts for badges
$unread_q    = mysqli_query($con, "SELECT COUNT(*) as c FROM contact WHERE status = 0");
$unread_cnt  = mysqli_fetch_assoc($unread_q)['c'];

// Build query string for pagination links
$qs_parts = [];
if ($date_from) $qs_parts[] = "date_from=$date_from";
if ($date_to)   $qs_parts[] = "date_to=$date_to";
if ($f_status !== '') $qs_parts[] = "f_status=$f_status";
if ($f_subject) $qs_parts[] = "f_subject=" . urlencode($f_subject);
$qs = count($qs_parts) ? '&' . implode('&', $qs_parts) : '';
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/admin/inc/nav.php'; ?>
<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
<div class="pcoded-content">
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="container-fluid">

                        <!-- Page Header -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <h4 class="mb-1" style="color:#1C3F8F; font-weight:800;">
                                            <i class="fas fa-envelope me-2"></i>Contacted Users
                                        </h4>
                                        <p class="mb-0 text-muted" style="font-size:0.88rem;">
                                            Total: <strong><?= $total_rows ?></strong> messages &nbsp;|&nbsp;
                                            <span style="color:#e74c3c; font-weight:700;"><?= $unread_cnt ?> unread</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── FILTERS ─────────────────────────────────── -->
                        <div style="background:white; border-radius:12px; box-shadow:0 2px 16px rgba(28,63,143,0.08); padding:18px 20px; margin-bottom:20px;">
                            <form method="GET" action="">
                                <div class="row">

                                    <div class="col-lg-2 col-md-4 col-sm-6" style="margin-bottom:12px;">
                                        <label style="font-size:0.78rem; font-weight:700; color:#1C3F8F; display:block; margin-bottom:5px;">
                                            <i class="fas fa-calendar-alt" style="margin-right:4px;"></i> Date From
                                        </label>
                                        <input type="date" name="date_from"
                                               value="<?= htmlspecialchars($date_from) ?>"
                                               style="width:100%; padding:7px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.85rem; color:#0D2B6E; outline:none;">
                                    </div>

                                    <div class="col-lg-2 col-md-4 col-sm-6" style="margin-bottom:12px;">
                                        <label style="font-size:0.78rem; font-weight:700; color:#1C3F8F; display:block; margin-bottom:5px;">
                                            <i class="fas fa-calendar-alt" style="margin-right:4px;"></i> Date To
                                        </label>
                                        <input type="date" name="date_to"
                                               value="<?= htmlspecialchars($date_to) ?>"
                                               style="width:100%; padding:7px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.85rem; color:#0D2B6E; outline:none;">
                                    </div>

                                    <div class="col-lg-2 col-md-4 col-sm-6" style="margin-bottom:12px;">
                                        <label style="font-size:0.78rem; font-weight:700; color:#1C3F8F; display:block; margin-bottom:5px;">
                                            <i class="fas fa-circle-dot" style="margin-right:4px;"></i> Status
                                        </label>
                                        <select name="f_status"
                                                style="width:100%; padding:7px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.85rem; color:#0D2B6E; background:white; outline:none;">
                                            <option value="">All</option>
                                            <option value="0" <?= $f_status === 0 ? 'selected' : '' ?>>Unread</option>
                                            <option value="1" <?= $f_status === 1 ? 'selected' : '' ?>>Read</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-3 col-md-4 col-sm-6" style="margin-bottom:12px;">
                                        <label style="font-size:0.78rem; font-weight:700; color:#1C3F8F; display:block; margin-bottom:5px;">
                                            <i class="fas fa-tag" style="margin-right:4px;"></i> Subject
                                        </label>
                                        <select name="f_subject"
                                                style="width:100%; padding:7px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.85rem; color:#0D2B6E; background:white; outline:none;">
                                            <option value="">All Subjects</option>
                                            <?php while ($sub = mysqli_fetch_assoc($subjects_q)): ?>
                                                <option value="<?= htmlspecialchars($sub['subject']) ?>" <?= $f_subject == $sub['subject'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars(ucfirst($sub['subject'])) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-lg-3 col-md-4 col-sm-6" style="margin-bottom:12px;">
                                        <label style="font-size:0.78rem; font-weight:700; color:transparent; display:block; margin-bottom:5px;">.</label>
                                        <div style="display:flex; gap:8px;">
                                            <button type="submit"
                                                    style="flex:1; padding:8px 12px; background:linear-gradient(135deg,#1C3F8F,#00A8CC); color:white; border:none; border-radius:8px; font-weight:700; font-size:0.83rem; cursor:pointer;">
                                                <i class="fas fa-filter" style="margin-right:4px;"></i> Filter
                                            </button>
                                            <a href="contacted_users.php"
                                               style="flex:1; padding:8px 12px; background:#f1f5f9; color:#475569; border:none; border-radius:8px; font-weight:700; font-size:0.83rem; text-decoration:none; text-align:center; display:inline-block;">
                                                <i class="fas fa-rotate-left" style="margin-right:4px;"></i> Reset
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>

                        <!-- ── TABLE ───────────────────────────────────── -->
                        <div class="card" style="border:none; border-radius:14px; box-shadow:0 2px 16px rgba(28,63,143,0.08);">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="contactTable">
                                        <thead>
                                            <tr style="background:linear-gradient(135deg,#1C3F8F,#00A8CC); color:white;">
                                                <th style="border:none; padding:14px 16px; font-size:0.82rem; font-weight:700; white-space:nowrap;">#</th>
                                                <th style="border:none; padding:14px 16px; font-size:0.82rem; font-weight:700;">Name</th>
                                                <th style="border:none; padding:14px 16px; font-size:0.82rem; font-weight:700;">Email</th>
                                                <th style="border:none; padding:14px 16px; font-size:0.82rem; font-weight:700;">Phone</th>
                                                <th style="border:none; padding:14px 16px; font-size:0.82rem; font-weight:700;">Subject</th>
                                                <th style="border:none; padding:14px 16px; font-size:0.82rem; font-weight:700; min-width:200px;">Message</th>
                                                <th style="border:none; padding:14px 16px; font-size:0.82rem; font-weight:700;">Date</th>
                                                <th style="border:none; padding:14px 16px; font-size:0.82rem; font-weight:700; text-align:center;">Mark Read</th>
                                                <th style="border:none; padding:14px 16px; font-size:0.82rem; font-weight:700; text-align:center;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($records) > 0):
                                                $sr = $offset + 1;
                                                while ($row = mysqli_fetch_assoc($records)):
                                                    $is_read   = $row['status'] == 1;
                                                    $row_style = $is_read ? 'background:#f8fafc;' : 'background:#fff; font-weight:600;';
                                            ?>
                                            <tr id="row-<?= $row['id'] ?>" style="<?= $row_style ?> transition:background 0.3s;">
                                                <td style="padding:12px 16px; color:#64748b; font-size:0.85rem;"><?= $sr++ ?></td>

                                                <td style="padding:12px 16px; font-size:0.88rem; color:#0D2B6E;">
                                                    <?= htmlspecialchars($row['name']) ?>
                                                    <?php if (!$is_read): ?>
                                                        <span style="display:inline-block; width:8px; height:8px; background:#e74c3c; border-radius:50%; margin-left:6px; vertical-align:middle;"></span>
                                                    <?php endif; ?>
                                                </td>

                                                <td style="padding:12px 16px; font-size:0.85rem;">
                                                    <a href="mailto:<?= htmlspecialchars($row['email']) ?>" style="color:#00A8CC; text-decoration:none;">
                                                        <?= htmlspecialchars($row['email']) ?>
                                                    </a>
                                                </td>

                                                <td style="padding:12px 16px; font-size:0.85rem; color:#475569; white-space:nowrap;">
                                                    <?= htmlspecialchars($row['phone'] ?: '—') ?>
                                                </td>

                                                <td style="padding:12px 16px;">
                                                    <span class="subject-badge subject-<?= htmlspecialchars($row['subject']) ?>">
                                                        <?= htmlspecialchars(ucfirst($row['subject'] ?: 'general')) ?>
                                                    </span>
                                                </td>

                                                <td style="padding:12px 16px; font-size:0.85rem; color:#475569; max-width:240px;">
                                                    <span class="msg-preview" title="<?= htmlspecialchars($row['msg']) ?>">
                                                        <?= htmlspecialchars(mb_strimwidth($row['msg'], 0, 80, '...')) ?>
                                                    </span>
                                                </td>

                                                <td style="padding:12px 16px; font-size:0.82rem; color:#64748b; white-space:nowrap;">
                                                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                                                    <br><small><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                                                </td>

                                                <!-- Mark Read Checkbox -->
                                                <td style="padding:12px 16px; text-align:center;">
                                                    <label class="mark-checkbox-wrap" title="<?= $is_read ? 'Mark as unread' : 'Mark as read' ?>">
                                                        <input type="checkbox"
                                                               class="mark-checkbox"
                                                               data-id="<?= $row['id'] ?>"
                                                               <?= $is_read ? 'checked' : '' ?>>
                                                        <span class="mark-checkmark"></span>
                                                    </label>
                                                </td>

                                                <!-- Action: Delete -->
                                                <td style="padding:12px 16px; text-align:center;">
                                                    <button class="btn-del" onclick="deleteRecord(<?= $row['id'] ?>)" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; else: ?>
                                            <tr>
                                                <td colspan="9" style="text-align:center; padding:50px; color:#94a3b8;">
                                                    <i class="fas fa-inbox" style="font-size:2.5rem; margin-bottom:12px; display:block;"></i>
                                                    No records found
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- ── PAGINATION ─────────────────────────── -->
                            <?php if ($total_pages > 1): ?>
                            <div class="card-footer" style="background:#f8fafc; border:none; border-radius:0 0 14px 14px; padding:16px 20px;">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <small class="text-muted">
                                        Showing <?= $offset + 1 ?>–<?= min($offset + $per_page, $total_rows) ?> of <?= $total_rows ?> records
                                    </small>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0" style="gap:4px;">

                                            <!-- Prev -->
                                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                                <a class="page-link sn-page-link" href="?page=<?= $page - 1 ?><?= $qs ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>

                                            <?php
                                            $start = max(1, $page - 2);
                                            $end   = min($total_pages, $page + 2);
                                            if ($start > 1): ?>
                                                <li class="page-item"><a class="page-link sn-page-link" href="?page=1<?= $qs ?>">1</a></li>
                                                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                            <?php endif; ?>

                                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link sn-page-link" href="?page=<?= $i ?><?= $qs ?>"><?= $i ?></a>
                                            </li>
                                            <?php endfor; ?>

                                            <?php if ($end < $total_pages): ?>
                                                <?php if ($end < $total_pages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                                <li class="page-item"><a class="page-link sn-page-link" href="?page=<?= $total_pages ?><?= $qs ?>"><?= $total_pages ?></a></li>
                                            <?php endif; ?>

                                            <!-- Next -->
                                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                                <a class="page-link sn-page-link" href="?page=<?= $page + 1 ?><?= $qs ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>

                                        </ul>
                                    </nav>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div><!-- container-fluid -->
                </div><!-- page-body -->
            </div><!-- page-wrapper -->
        </div><!-- main-body -->
    </div><!-- pcoded-inner-content -->
</div><!-- pcoded-content -->
                                                </div></div>
<!-- ── STYLES ─────────────────────────────────────────────── -->
<style>
/* Subject badges */
.subject-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: capitalize;
}
.subject-general    { background: #e8f7ff; color: #1C3F8F; }
.subject-appointment{ background: #f0fff8; color: #27C96A; }
.subject-emergency  { background: #fff0f0; color: #e74c3c; }
.subject-feedback   { background: #fff8e8; color: #f59e0b; }
.subject-complaint  { background: #fdf2f8; color: #9c27b0; }
.subject-partnership{ background: #e8f0ff; color: #00A8CC; }

/* Checkbox styling */
.mark-checkbox-wrap {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    position: relative;
    width: 24px; height: 24px;
}
.mark-checkbox-wrap input[type="checkbox"] {
    opacity: 0;
    position: absolute;
    width: 100%; height: 100%;
    cursor: pointer;
    z-index: 2;
    margin: 0;
}
.mark-checkmark {
    width: 24px; height: 24px;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    background: white;
    transition: all 0.2s ease;
    display: flex; align-items: center; justify-content: center;
    position: relative;
}
.mark-checkmark::after {
    content: '✓';
    color: white;
    font-size: 13px;
    font-weight: 900;
    opacity: 0;
    transition: opacity 0.2s;
}
.mark-checkbox-wrap input:checked ~ .mark-checkmark {
    background: linear-gradient(135deg, #27C96A, #00C9A7);
    border-color: transparent;
}
.mark-checkbox-wrap input:checked ~ .mark-checkmark::after { opacity: 1; }

/* Delete button */
.btn-del {
    width: 34px; height: 34px;
    border: none;
    border-radius: 8px;
    background: #fff0f0;
    color: #e74c3c;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-del:hover {
    background: #e74c3c;
    color: white;
    transform: scale(1.1);
}

/* Pagination */
.sn-page-link {
    border-radius: 8px !important;
    border: 1px solid rgba(0,168,204,0.2) !important;
    color: #1C3F8F !important;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 5px 11px;
    transition: all 0.2s;
}
.sn-page-link:hover {
    background: linear-gradient(135deg, #1C3F8F, #00A8CC) !important;
    color: white !important;
    border-color: transparent !important;
}
.page-item.active .sn-page-link {
    background: linear-gradient(135deg, #1C3F8F, #00A8CC) !important;
    color: white !important;
    border-color: transparent !important;
}
.page-item.disabled .sn-page-link {
    background: #f1f5f9 !important;
    color: #cbd5e1 !important;
}

/* Row unread highlight */
tbody tr:not(.no-records):hover { background: #f0f9ff !important; }
</style>

<!-- ── SCRIPTS ─────────────────────────────────────────────── -->
<script>
// Mark read/unread via checkbox
document.querySelectorAll('.mark-checkbox').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var id      = this.dataset.id;
        var checked = this.checked ? '1' : '0';
        var row     = document.getElementById('row-' + id);

        fetch('contacted_users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'mark_read=' + id + '&checked=' + checked
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                if (checked === '1') {
                    row.style.background   = '#f8fafc';
                    row.style.fontWeight   = 'normal';
                    // Remove red dot if exists
                    var dot = row.querySelector('span[style*="border-radius:50%"]');
                    if (dot) dot.remove();
                } else {
                    row.style.background = '#fff';
                    row.style.fontWeight = '600';
                }
                // Update unread count in header
                updateUnreadCount(checked === '1' ? -1 : 1);
            }
        })
        .catch(function(e) { console.error(e); });
    });
});

// Delete record
function deleteRecord(id) {
    if (!confirm('Are you sure you want to delete this message?')) return;

    fetch('contacted_users.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'delete_id=' + id
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var row = document.getElementById('row-' + id);
            row.style.transition = 'opacity 0.4s, transform 0.4s';
            row.style.opacity    = '0';
            row.style.transform  = 'translateX(20px)';
            setTimeout(function() { row.remove(); }, 400);
        }
    })
    .catch(function(e) { console.error(e); });
}

// Update unread count badge
function updateUnreadCount(delta) {
    var badge = document.querySelector('span[style*="color:#e74c3c"]');
    if (badge) {
        var match = badge.textContent.match(/\d+/);
        if (match) {
            var newVal = Math.max(0, parseInt(match[0]) + delta);
            badge.textContent = newVal + ' unread';
        }
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>
