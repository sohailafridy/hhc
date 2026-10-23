<?php
include '../config.php';
include '../check_auth.php';

// ============================================
// CSRF TOKEN
// ============================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// ============================================
// CONFIG
// ============================================
$RUN_NOW   = true;   // ⚠️ true = page open hote hi delete ho jayega
$DRY_RUN   = isset($_GET['dry']) && $_GET['dry'] == 1;  // ?dry=1 -> sirf preview, kuch delete nahi
$NEED_CONFIRM = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

$result_log = [];
$error_msg  = '';
$success    = false;

// ============================================
// HELPER
// ============================================
function run_query($con, $sql, $label, &$log) {
    $res = mysqli_query($con, $sql);
    if ($res === false) {
        throw new Exception("[$label] " . mysqli_error($con));
    }
    $affected = mysqli_affected_rows($con);
    $log[] = "$label — $affected row(s) affected";
    return $affected;
}

// ============================================
// MAIN ACTION
// ============================================
if ($RUN_NOW && !$DRY_RUN && $NEED_CONFIRM) {

    // CSRF check
    $token_ok = isset($_GET['csrf_token'], $_SESSION['csrf_token'])
                && hash_equals($_SESSION['csrf_token'], (string)$_GET['csrf_token']);

    if (!$token_ok) {
        $error_msg = "Invalid CSRF token. Please reload the page and try again.";
    } else {
        try {
            mysqli_begin_transaction($con);

            // 1) Saare clinic-linked doctor_in_hospital rows
            $dih_ids = [];
            $q = mysqli_query($con, "SELECT doctor_in_hosp_id 
                                     FROM doctor_in_hospital 
                                     WHERE if_clinic = 1");
            if ($q === false) {
                throw new Exception(mysqli_error($con));
            }
            while ($r = mysqli_fetch_assoc($q)) {
                $dih_ids[] = (int)$r['doctor_in_hosp_id'];
            }

            if (!empty($dih_ids)) {
                $list = implode(',', $dih_ids);

                // 2) clinical_info delete
                run_query($con, "DELETE FROM clinical_info WHERE doctor_in_hosp_id IN ($list)", "clinical_info deleted", $result_log);

                // 3) doctor_in_hospital delete
                run_query($con, "DELETE FROM doctor_in_hospital WHERE doctor_in_hosp_id IN ($list) AND if_clinic = 1", "doctor_in_hospital deleted", $result_log);
            } else {
                $result_log[] = "No clinic-linked hospital rows found.";
            }

            // 4) doctors table reset
            run_query($con, "UPDATE doctors 
                             SET clinic_status = 0, clinic_name = '', clinic_address = '' 
                             WHERE clinic_status = 1 
                                OR clinic_name <> '' 
                                OR clinic_address <> ''", "doctors reset", $result_log);

            mysqli_commit($con);
            $success = true;

        } catch (Throwable $e) {
            mysqli_rollback($con);
            $error_msg = "Failed, nothing was deleted. " . $e->getMessage();
        }
    }
}

// ============================================
// PREVIEW COUNTS (dry run / before delete)
// ============================================
$count_clinic_rows = 0;
$count_doctors     = 0;

$q1 = mysqli_query($con, "SELECT COUNT(*) AS c FROM doctor_in_hospital WHERE if_clinic = 1");
if ($q1) { $count_clinic_rows = (int)mysqli_fetch_assoc($q1)['c']; }

$q2 = mysqli_query($con, "SELECT COUNT(*) AS c FROM doctors 
                          WHERE clinic_status = 1 
                             OR clinic_name <> '' 
                             OR clinic_address <> ''");
if ($q2) { $count_doctors = (int)mysqli_fetch_assoc($q2)['c']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete All Clinics — Admin Utility</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6fb;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            padding: 40px 15px;
        }
        .wrap { max-width: 720px; margin: 0 auto; }
        .card-custom {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            padding: 30px;
        }
        .icon-danger {
            width: 70px; height: 70px;
            border-radius: 50%;
            background: #fdecea;
            color: #d93025;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            margin: 0 auto 16px;
        }
        .log-box {
            background: #0f172a;
            color: #93f9b9;
            font-family: ui-monospace, Menlo, Consolas, monospace;
            font-size: 0.85rem;
            padding: 16px;
            border-radius: 10px;
            max-height: 260px;
            overflow-y: auto;
        }
        .stat-pill {
            background: #eef2ff;
            color: #4338ca;
            padding: 6px 14px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .btn-danger-custom {
            background: #d93025;
            border: none;
            padding: 12px 22px;
            border-radius: 999px;
            font-weight: 700;
        }
        .btn-danger-custom:hover { background: #b71c1c; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="card-custom">

        <?php if ($success): ?>
            <div class="text-center mb-4">
                <div class="icon-danger" style="background:#e6f9ef;color:#0f9d58;">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="fw-bold mb-2">All Clinics Deleted Successfully</h3>
                <p class="text-muted mb-0">The operation completed inside a single transaction.</p>
            </div>

            <div class="log-box mb-4">
                <?php foreach ($result_log as $line): ?>
                    <div>✓ <?= htmlspecialchars($line) ?></div>
                <?php endforeach; ?>
            </div>

            <div class="text-center">
                <a href="<?= BASE_URL ?>admin/doctors/list" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-1"></i> Back to Doctors List
                </a>
            </div>

        <?php elseif ($error_msg): ?>
            <div class="text-center mb-4">
                <div class="icon-danger">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <h3 class="fw-bold mb-2">Operation Failed</h3>
                <p class="text-danger mb-0"><?= htmlspecialchars($error_msg) ?></p>
            </div>
            <div class="text-center">
                <a href="?" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-rotate me-1"></i> Try Again
                </a>
            </div>

        <?php elseif ($DRY_RUN): ?>
            <div class="text-center mb-4">
                <div class="icon-danger" style="background:#fff8e1;color:#f59e0b;">
                    <i class="fas fa-eye"></i>
                </div>
                <h3 class="fw-bold mb-2">Dry Run — Preview Only</h3>
                <p class="text-muted mb-0">Nothing has been deleted.</p>
            </div>

            <div class="text-center mb-4">
                <span class="stat-pill me-2">
                    <i class="fas fa-hospital me-1"></i>
                    <?= $count_clinic_rows ?> clinic-linked hospital rows
                </span>
                <span class="stat-pill">
                    <i class="fas fa-user-md me-1"></i>
                    <?= $count_doctors ?> doctors with clinic data
                </span>
            </div>

            <div class="alert alert-warning small">
                <strong>Note:</strong> If you click "Delete All Clinics", the following will happen:
                <ul class="mb-0 mt-2">
                    <li>All <code>clinical_info</code> rows linked to clinics will be deleted.</li>
                    <li>All <code>doctor_in_hospital</code> rows with <code>if_clinic = 1</code> will be deleted.</li>
                    <li><code>doctors.clinic_status</code> will be reset to <code>0</code>, and <code>clinic_name</code> / <code>clinic_address</code> will be cleared.</li>
                </ul>
            </div>

            <div class="text-center mt-4">
                <a href="?confirm=yes&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                   onclick="return confirm('⚠️ FINAL WARNING\n\nThis will permanently delete clinic data for ALL doctors. This cannot be undone.\n\nProceed?');"
                   class="btn btn-danger-custom text-white">
                    <i class="fas fa-trash me-1"></i> Yes, Delete All Clinics
                </a>
                <a href="<?= BASE_URL ?>admin/doctors/list" class="btn btn-outline-secondary rounded-pill px-4 ms-2">
                    Cancel
                </a>
            </div>

        <?php else: ?>
            <!-- Fallback: auto-redirect to dry run if RUN_NOW is true but no confirm -->
            <div class="text-center">
                <div class="icon-danger" style="background:#fff8e1;color:#f59e0b;">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h3 class="fw-bold mb-2">Safety Check Required</h3>
                <p class="text-muted mb-4">
                    You opened this page directly. To prevent accidental data loss, please review a dry-run first.
                </p>
                <a href="?dry=1" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-eye me-1"></i> Preview Dry Run
                </a>
            </div>
        <?php endif; ?>

    </div>

    <p class="text-center text-muted small mt-3">
        Admin Utility • This action is logged in PHP session logs
    </p>
</div>
</body>
</html>