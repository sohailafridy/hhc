<?php
include '../config.php';
include '../check_auth.php';

// ---------- Validate ID ----------
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || (int)$_GET['id'] <= 0) {
    die("Invalid clinical info ID provided.");
}
$clinical_info_id = (int)$_GET['id'];

$update_message = '';

// ---------- Dropdown options ----------
$working_days_options = [
    "Monday to Friday",
    "Monday to Saturday",
    "Monday to Sunday",
    "Tuesday to Sunday",
    "Friday to Sunday",
    "Saturday & Sunday",
    "Sunday Only",
    "24/7"
];
$off_days_options = [
    "None", "Monday", "Tuesday", "Wednesday", "Thursday",
    "Friday", "Saturday", "Sunday", "Saturday & Sunday", "Friday & Saturday"
];
$season_options = ["Summer", "Winter"];

// ---------- Handle form submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $morning_opening_time = trim($_POST['morning_opening_time'] ?? '');
    $morning_closing_time = trim($_POST['morning_closing_time'] ?? '');
    $evening_opening_time = trim($_POST['evening_opening_time'] ?? '');
    $evening_closing_time = trim($_POST['evening_closing_time'] ?? '');
    $season   = trim($_POST['season'] ?? '');
    $days     = trim($_POST['days'] ?? '');
    $off_days = trim($_POST['off_days'] ?? '');
    $contact  = trim($_POST['contact'] ?? '');
    $detail   = trim($_POST['detail'] ?? '');

    $morning_filled = ($morning_opening_time !== '' && $morning_closing_time !== '');
    $evening_filled = ($evening_opening_time !== '' && $evening_closing_time !== '');

    $morning_partial = (($morning_opening_time !== '') xor ($morning_closing_time !== ''));
    $evening_partial = (($evening_opening_time !== '') xor ($evening_closing_time !== ''));

    if ($morning_partial || $evening_partial) {
        $update_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            Shift ka opening aur closing time dono bharein (ya dono khali chhorein).
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    } else {
        // Auto-detect shift (khali shift = removed)
        if ($morning_filled && $evening_filled) {
            $shift = 'Both';
        } elseif ($morning_filled) {
            $shift = 'Morning';
        } elseif ($evening_filled) {
            $shift = 'Evening';
        } else {
            $shift = ''; // dono shifts remove ho gayi
        }

        $stmt = mysqli_prepare($con, "UPDATE clinical_info SET
                morning_opening_time = ?,
                morning_closing_time = ?,
                evening_opening_time = ?,
                evening_closing_time = ?,
                season   = ?,
                days     = ?,
                shift    = ?,
                off_days = ?,
                contact  = ?,
                detail   = ?
            WHERE clinical_info_id = ?");

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt, 'ssssssssssi',
                $morning_opening_time, $morning_closing_time,
                $evening_opening_time, $evening_closing_time,
                $season, $days, $shift, $off_days, $contact, $detail,
                $clinical_info_id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                // doctor_id: pehle URL se, warna database se
                $redirect_doctor_id = (isset($_GET['doctor_id']) && is_numeric($_GET['doctor_id']))
                    ? (int)$_GET['doctor_id'] : 0;

                if ($redirect_doctor_id <= 0) {
                    $rq = mysqli_query($con, "SELECT dih.doctor_id
                        FROM clinical_info ci
                        INNER JOIN doctor_in_hospital dih ON dih.doctor_in_hosp_id = ci.doctor_in_hosp_id
                        WHERE ci.clinical_info_id = " . (int)$clinical_info_id . " LIMIT 1");
                    if ($rq && ($rrow = mysqli_fetch_assoc($rq))) {
                        $redirect_doctor_id = (int)$rrow['doctor_id'];
                    }
                }

                header("Location: " . BASE_URL . "admin/doctors/profile?id=" . $redirect_doctor_id);
                exit;
            } else {
                $update_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> ' . htmlspecialchars(mysqli_stmt_error($stmt)) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
            mysqli_stmt_close($stmt);
        } else {
            $update_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> ' . htmlspecialchars(mysqli_error($con)) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }
}

// ---------- Fetch clinical information (after update, so fresh data shows) ----------
$stmt = mysqli_prepare($con, "SELECT * FROM clinical_info WHERE clinical_info_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $clinical_info_id);
mysqli_stmt_execute($stmt);
$clinical_result = mysqli_stmt_get_result($stmt);
$clinical = mysqli_fetch_assoc($clinical_result);
mysqli_stmt_close($stmt);

if (!$clinical) {
    $clinical = [];
}

// ---------- Get doctor_id (for Back link) ----------
$doctor_id = 0;
if (!empty($clinical['doctor_in_hosp_id'])) {
    $dih_id = (int)$clinical['doctor_in_hosp_id'];
    $dq = mysqli_query($con, "SELECT doctor_id FROM doctor_in_hospital WHERE doctor_in_hosp_id = $dih_id LIMIT 1");
    if ($dq && ($drow = mysqli_fetch_assoc($dq))) {
        $doctor_id = (int)$drow['doctor_id'];
    }
}

// Helper: print <option>s, keeping a custom/old value if not in the list
function render_options(array $options, $selected) {
    $selected = (string)$selected;
    if ($selected !== '' && !in_array($selected, $options, true)) {
        $options[] = $selected; // keep old custom value
    }
    foreach ($options as $opt) {
        echo '<option value="' . htmlspecialchars($opt) . '"' . ($selected === $opt ? ' selected' : '') . '>'
            . htmlspecialchars($opt) . '</option>';
    }
}
?>
<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>style/edit-clinical-info-admin.css">

<div class="content-wrapper">
    <div class="container-fluid">

        <?php echo $update_message; ?>

        <div class="row">
            <div class="col-lg-12">
                <div class="info-card">
                    <div class="info-card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-map-marker-alt me-2"></i>Edit Clinic Information</h5>
                        <?php if ($doctor_id): ?>
                            <a href="<?= BASE_URL ?>admin/doctors/profile?id=<?= $doctor_id ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="info-card-body">
                        <?php if (!empty($clinical)): ?>
                        <div class="season-group mb-4">
                            <div class="season-group-body">
                                <form action="?id=<?= $clinical_info_id ?><?= (isset($_GET['doctor_id']) && is_numeric($_GET['doctor_id'])) ? '&doctor_id=' . (int)$_GET['doctor_id'] : '' ?>" method="POST" class="row" id="editClinicalForm">

                                    <div class="col-lg-8 mb-3">
                                        <div class="clinical-record-card">
                                            <div class="clinical-record-body">

                                                <div class="row">
                                                    <!-- Morning -->
                                                    <div class="col-md-6">
                                                        <div class="clinical-info-item">
                                                            <i class="fas fa-sun text-warning"></i>
                                                            <div>
                                                                <small class="text-muted">Morning Shift (khali chhorein agar nahi hai)</small>
                                                                <input type="text" name="morning_opening_time" class="form-control mb-2 timepicker"
                                                                       placeholder="Opening Time"
                                                                       value="<?= htmlspecialchars($clinical['morning_opening_time'] ?? '') ?>">
                                                                <input type="text" name="morning_closing_time" class="form-control timepicker"
                                                                       placeholder="Closing Time"
                                                                       value="<?= htmlspecialchars($clinical['morning_closing_time'] ?? '') ?>">
                                                                <button type="button" class="btn btn-sm btn-outline-danger mt-2 btn-remove-shift" data-shift="morning">
                                                                    <i class="fas fa-times"></i> Remove Morning Time
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Evening -->
                                                    <div class="col-md-6">
                                                        <div class="clinical-info-item">
                                                            <i class="fas fa-moon text-primary"></i>
                                                            <div>
                                                                <small class="text-muted">Evening Shift (khali chhorein agar nahi hai)</small>
                                                                <input type="text" name="evening_opening_time" class="form-control mb-2 timepicker"
                                                                       placeholder="Opening Time"
                                                                       value="<?= htmlspecialchars($clinical['evening_opening_time'] ?? '') ?>">
                                                                <input type="text" name="evening_closing_time" class="form-control timepicker"
                                                                       placeholder="Closing Time"
                                                                       value="<?= htmlspecialchars($clinical['evening_closing_time'] ?? '') ?>">
                                                                <button type="button" class="btn btn-sm btn-outline-danger mt-2 btn-remove-shift" data-shift="evening">
                                                                    <i class="fas fa-times"></i> Remove Evening Time
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i>Season</small>
                                                        <select name="season" class="form-control">
                                                            <option value="">-- Select Season --</option>
                                                            <?php render_options($season_options, $clinical['season'] ?? ''); ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted"><i class="fas fa-phone me-1"></i>Contact</small>
                                                        <input type="text" name="contact" class="form-control"
                                                               placeholder="e.g., 0300-1234567"
                                                               value="<?= htmlspecialchars($clinical['contact'] ?? '') ?>">
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <small class="text-muted"><i class="fas fa-calendar-day me-1"></i>Working Days</small>
                                                        <select name="days" class="form-control">
                                                            <option value="">-- Select Working Days --</option>
                                                            <?php render_options($working_days_options, $clinical['days'] ?? ''); ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted"><i class="fas fa-calendar-times me-1"></i>Off Days</small>
                                                        <select name="off_days" class="form-control">
                                                            <option value="">-- Select Off Days --</option>
                                                            <?php render_options($off_days_options, $clinical['off_days'] ?? ''); ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="clinical-detail mt-3">
                                                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Detail</small>
                                                    <textarea name="detail" class="form-control" rows="4"
                                                              placeholder="Additional details..."><?= htmlspecialchars($clinical['detail'] ?? '') ?></textarea>
                                                </div>

                                                <div class="clinical-actions mt-4 text-end">
                                                    <button type="button" class="btn btn-danger me-2 btn-remove-shift" data-shift="both">
                                                        <i class="fas fa-trash"></i> Remove Both Times
                                                    </button>
                                                    <button type="submit" class="btn btn-success me-2">
                                                        <i class="fas fa-save"></i> Update Information
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clinic-medical fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No clinical information found for this record.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    if (typeof flatpickr !== 'undefined') {
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K",
            time_24hr: false,
            minuteIncrement: 5,
            allowInput: true
        });
    }

    // Remove shift time(s)
    const form = document.getElementById('editClinicalForm');

    function clearField(name) {
        const el = form.querySelector('[name="' + name + '"]');
        if (!el) return;
        if (el._flatpickr) { el._flatpickr.clear(); }
        el.value = '';
    }

    document.querySelectorAll('.btn-remove-shift').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const shift = this.dataset.shift;
            const label = shift === 'both' ? 'Morning aur Evening dono' : (shift === 'morning' ? 'Morning' : 'Evening');

            if (!confirm(label + ' time remove karna chahte hain?')) return;

            if (shift === 'morning' || shift === 'both') {
                clearField('morning_opening_time');
                clearField('morning_closing_time');
            }
            if (shift === 'evening' || shift === 'both') {
                clearField('evening_opening_time');
                clearField('evening_closing_time');
            }
            form.submit(); // seedha save kar do
        });
    });

    // Contact: sirf digits aur dash
    const contact = document.querySelector('input[name="contact"]');
    if (contact) {
        contact.addEventListener('input', function () {
            this.value = this.value.replace(/[^\d-]/g, '');
        });
    }
});
</script>