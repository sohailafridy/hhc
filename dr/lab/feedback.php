<?php
include '../config.php';

// ── DOCTOR AUTH ────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'lab') {
    header("Location: " . BASE_URL . "login"); exit();
}

$user_id = (int)$_SESSION['user_id'];

// Doctor basic info
$lq  = mysqli_query($con, "SELECT lab_name FROM laboratories WHERE user_id = $user_id LIMIT 1");
$labs = mysqli_fetch_assoc($lq);
$labsName = $labs['lab_name'];


if (!$labs) { session_destroy(); header("Location: " . BASE_URL . "login"); exit(); }

// ── PAGINATION ─────────────────────────────────────────────
$page      = max(1, (int)($_GET['page'] ?? 1));
$per_page  = 10;
$offset    = ($page - 1) * $per_page;

$count_q       = mysqli_query($con, "SELECT COUNT(*) as total FROM feedback WHERE user_id=$user_id AND status=1");
$total_records = (int)mysqli_fetch_assoc($count_q)['total'];
$total_pages   = ceil($total_records / $per_page);

$dc_result = mysqli_query($con, "SELECT * FROM feedback WHERE user_id=$user_id AND status=1
                               ORDER BY created_at DESC LIMIT $offset, $per_page");

// ── STATS ──────────────────────────────────────────────────
$rating_q    = mysqli_query($con, "SELECT AVG(stars) as avg, COUNT(*) as total FROM feedback WHERE user_id=$user_id AND status=1");
$rating_data = mysqli_fetch_assoc($rating_q);
$avg_rating  = $rating_data['avg'] ? round($rating_data['avg'], 1) : 0;
$total_reviews = (int)$rating_data['total'];

$rating_dist = [];
for ($i = 1; $i <= 5; $i++) {
    $dq = mysqli_query($con, "SELECT COUNT(*) as c FROM feedback WHERE user_id=$user_id AND status=1 AND stars=$i");
    $rating_dist[$i] = (int)mysqli_fetch_assoc($dq)['c'];
}
?>
<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/lab/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-feedback-admin.css">

<div class="content-wrapper">

    <div class="page-header" style="background:linear-gradient(135deg,#071124 0%,#0D2B6E 50%,#1C3F8F 100%); border-radius:16px; padding:22px 28px; color:white; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h4 style="margin:0; font-size:1.3rem; font-weight:800;">
                <i class="fas fa-star me-2" style="color:#fbbf24;"></i> My Patient Reviews
            </h4>
            <p style="margin:4px 0 0; opacity:0.8; font-size:0.88rem;"><?= htmlspecialchars($labsName) ?></p>
        </div>
        <span style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); border-radius:20px; padding:5px 14px; font-size:0.82rem; font-weight:600;">
            <?= $total_reviews ?> Total Reviews
        </span>
    </div>

    <!-- Stats -->
    <div class="stats-row" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:16px; margin-bottom:24px;">
        <div class="stat-card" style="background:white; border-radius:14px; padding:18px 20px; text-align:center; box-shadow:0 2px 12px rgba(28,63,143,0.08); border:1px solid rgba(0,168,204,0.12);">
            <div style="font-size:1.8rem; font-weight:800; color:#1C3F8F;"><?= $avg_rating > 0 ? $avg_rating : 'N/A' ?></div>
            <div style="font-size:0.8rem; color:#64748b; font-weight:500;">Average Rating</div>
        </div>
        <div class="stat-card" style="background:white; border-radius:14px; padding:18px 20px; text-align:center; box-shadow:0 2px 12px rgba(28,63,143,0.08); border:1px solid rgba(0,168,204,0.12);">
            <div style="font-size:1.8rem; font-weight:800; color:#1C3F8F;"><?= $total_reviews ?></div>
            <div style="font-size:0.8rem; color:#64748b; font-weight:500;">Total Reviews</div>
        </div>
        <div class="stat-card" style="background:white; border-radius:14px; padding:18px 20px; text-align:center; box-shadow:0 2px 12px rgba(28,63,143,0.08); border:1px solid rgba(0,168,204,0.12);">
            <div style="font-size:1.8rem; font-weight:800; color:#27C96A;">
                <?= $total_reviews > 0 ? round(($rating_dist[5] / $total_reviews) * 100) : 0 ?>%
            </div>
            <div style="font-size:0.8rem; color:#64748b; font-weight:500;">5-Star Reviews</div>
        </div>
        <div class="stat-card" style="background:white; border-radius:14px; padding:18px 20px; text-align:center; box-shadow:0 2px 12px rgba(28,63,143,0.08); border:1px solid rgba(0,168,204,0.12);">
            <div style="font-size:1.8rem; font-weight:800; color:#ef4444;">
                <?= $total_reviews > 0 ? round(($rating_dist[1] / $total_reviews) * 100) : 0 ?>%
            </div>
            <div style="font-size:0.8rem; color:#64748b; font-weight:500;">1-Star Reviews</div>
        </div>
    </div>

    <!-- Table -->
    <div style="background:white; border-radius:16px; box-shadow:0 2px 12px rgba(28,63,143,0.08); border:1px solid rgba(0,168,204,0.12); overflow:hidden;">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:linear-gradient(135deg,#1C3F8F,#00A8CC); color:white;">
                        <th style="border:none; padding:14px 16px; font-size:0.82rem;">#</th>
                        <th style="border:none; padding:14px 16px; font-size:0.82rem;">Patient</th>
                        <th style="border:none; padding:14px 16px; font-size:0.82rem;">Rating</th>
                        <th style="border:none; padding:14px 16px; font-size:0.82rem;">Review</th>
                        <th style="border:none; padding:14px 16px; font-size:0.82rem;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($dc_result) > 0):
                        $serial = $offset + 1;
                        while ($review = mysqli_fetch_assoc($dc_result)): ?>
                        <tr>
                            <td style="color:#94a3b8; font-size:0.85rem;"><?= $serial++ ?></td>
                            <td>
                                <strong style="color:#0D2B6E;"><?= htmlspecialchars($review['commenter_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($review['commenter_gmail'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php for ($i=1;$i<=5;$i++): ?>
                                    <i class="fas fa-star <?= $i<=$review['stars'] ? 'text-warning' : 'text-muted' ?>" style="font-size:0.82rem;"></i>
                                <?php endfor; ?>
                            </td>
                            <td style="font-size:0.88rem; color:#475569; max-width:280px;"><?= nl2br(htmlspecialchars($review['comment'])) ?></td>
                            <td style="font-size:0.82rem; color:#64748b; white-space:nowrap;"><?= date('d M Y', strtotime($review['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-comments fa-2x d-block mb-2"></i>No reviews yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div style="padding:16px 20px; border-top:1px solid rgba(0,168,204,0.1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <small class="text-muted">Showing <?= $offset+1 ?>–<?= min($offset+$per_page,$total_records) ?> of <?= $total_records ?></small>
            <nav><ul class="pagination mb-0" style="gap:4px;">
                <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= $page-1 ?>" style="border-radius:8px; border:1px solid rgba(0,168,204,0.2); color:#1C3F8F; font-weight:600;"><i class="fas fa-chevron-left"></i></a></li>
                <?php for ($i=1;$i<=$total_pages;$i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?page=<?= $i ?>" style="border-radius:8px; <?= $i==$page?'background:linear-gradient(135deg,#1C3F8F,#00A8CC);border-color:transparent;':'border:1px solid rgba(0,168,204,0.2);color:#1C3F8F;' ?> font-weight:600;"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= $page+1 ?>" style="border-radius:8px; border:1px solid rgba(0,168,204,0.2); color:#1C3F8F; font-weight:600;"><i class="fas fa-chevron-right"></i></a></li>
            </ul></nav>
        </div>
        <?php endif; ?>
    </div>

</div>
<?php include BASE_PATH.'/admin/inc/footer.php'; ?>
