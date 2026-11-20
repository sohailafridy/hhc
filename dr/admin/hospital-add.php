<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php include_once BASE_PATH . '/includes/colors.php'; ?>

<?php
// ===== EDIT MODE DETECTION =====
$edit_mode = false;
$hospital_data = null;
$beds_data = null;
$facilities_data = [];
$created_at = date('Y-m-d');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $hospital_id = (int)$_GET['id'];
    $edit_query = "SELECT h.*, u.status as estatus, u.username, u.email as hospital_email
                   FROM hospitals h
                   LEFT JOIN users u ON u.user_id = h.user_id
                   WHERE h.hospital_id = $hospital_id";
    $edit_result = mysqli_query($con, $edit_query);

    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $hospital_data = mysqli_fetch_assoc($edit_result);

        $beds_result = mysqli_query($con, "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id");
        $beds_data = mysqli_fetch_assoc($beds_result);

        $facilities_result = mysqli_query($con, "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id");
        while ($row = mysqli_fetch_assoc($facilities_result)) {
            $facilities_data[] = $row;
        }
    }
}
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// ===== CITIES =====
$cities_query = "SELECT c.city_id, c.city_name, p.p_name
                 FROM cities c
                 LEFT JOIN provinces p ON c.province_id = p.p_id
                 WHERE c.status = 1
                 ORDER BY c.city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

// ===== FACILITY LIST =====
$facility_list = [
    'emergency'         => 'Emergency',
    'icu'               => 'ICU',
    'nicu'              => 'NICU',
    'operation_theatre' => 'Operation Theatre',
    'pharmacy'          => 'Pharmacy',
    'laboratory'        => 'Laboratory',
    'blood_bank'        => 'Blood Bank',
    'radiology'         => 'Radiology',
    'mri'               => 'MRI',
    'ct_scan'           => 'CT Scan',
    'ambulance'         => 'Ambulance',
    'parking'           => 'Parking',
    'cafeteria'         => 'Cafeteria',
    'prayer_area'       => 'Prayer Area'
];

// ===== SAVE FACILITIES (same logic as hospital/facilities page) =====
function save_facilities($con, $hospital_id, $facility_list) {
    $hospital_id = (int)$hospital_id;

    // purani facilities hatao, phir sirf wahi save karo jo ab checked hain
    mysqli_query($con, "DELETE FROM hospital_facilities WHERE hospital_id = $hospital_id");

    $available    = $_POST['facilities_available']  ?? [];
    $descriptions = $_POST['facility_descriptions'] ?? [];

    foreach ($facility_list as $key => $facility_name) {
        $is_available = in_array($key, $available, true) ? 1 : 0;
        $description  = mysqli_real_escape_string($con, trim($descriptions[$key] ?? ''));

        if ($is_available == 1 || $description !== '') {
            // name hamesha $facility_list wala (ICU, NICU, MRI, CT Scan ...)
            $fname = mysqli_real_escape_string($con, $facility_name);
            mysqli_query($con, "INSERT INTO hospital_facilities
                                    (hospital_id, facility_name, description, is_available, created_at, updated_at)
                                VALUES
                                    ($hospital_id, '$fname', '$description', $is_available, NOW(), NOW())");
        }
    }
}

// ===== FORM SUBMISSION =====
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $city_id          = mysqli_real_escape_string($con, $_POST['city_id'] ?? '');
    $user_id          = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $hospital_name    = mysqli_real_escape_string($con, $_POST['hospital_name'] ?? '');
    $hospital_address = mysqli_real_escape_string($con, $_POST['hospital_address'] ?? '');
    $hospital_phone   = mysqli_real_escape_string($con, $_POST['hospital_phone'] ?? '');
    $reg_no           = mysqli_real_escape_string($con, $_POST['reg_no'] ?? '');
    $status           = isset($_POST['status']) ? 1 : 0;
    $hospital_email   = mysqli_real_escape_string($con, $_POST['hospital_email'] ?? '');
    $username         = mysqli_real_escape_string($con, $_POST['username'] ?? '');
    $pass             = isset($_POST['password']) ? trim($_POST['password']) : '';
    $password         = !empty($pass) ? base64_encode($pass) : '';

    $total_beds   = (int)($_POST['total_beds'] ?? 0);
    $icu_beds     = (int)($_POST['icu_beds'] ?? 0);
    $general_beds = (int)($_POST['general_beds'] ?? 0);
    $private_beds = (int)($_POST['private_beds'] ?? 0);

    // ===== SERVER-SIDE DUPLICATE CHECK =====
    $dup_errors = [];

    if (!$edit_mode && !empty($username)) {
        $chk = mysqli_query($con, "SELECT user_id FROM users WHERE username = '$username' LIMIT 1");
        if (mysqli_num_rows($chk) > 0) $dup_errors[] = "Username '{$username}' is already taken.";
    }

    if (!empty($reg_no)) {
        $reg_check_where = $edit_mode ? "AND hospital_id != $hospital_id" : "";
        $chk = mysqli_query($con, "SELECT hospital_id FROM hospitals WHERE reg_no = '$reg_no' $reg_check_where LIMIT 1");
        if (mysqli_num_rows($chk) > 0) $dup_errors[] = "Registration No '{$reg_no}' already exists.";
    }

    if (!empty($dup_errors)) {
        $error_msg = implode(' ', $dup_errors);
    } else {

        // ===== FILE UPLOAD =====
        $hospital_pic = '';
        if (isset($_FILES['hospital_pic']) && $_FILES['hospital_pic']['error'] == 0) {
            $target_dir = BASE_PATH . "/admin/inc/uploads/hospitals/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

            $file_name = time() . '_' . basename($_FILES["hospital_pic"]["name"]);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                if (move_uploaded_file($_FILES["hospital_pic"]["tmp_name"], $target_file)) {
                    $hospital_pic = $file_name;
                }
            }
        }

        if ($edit_mode) {
            $update_query = "UPDATE hospitals SET 
                                city_id = '$city_id', 
                                hospital_name = '$hospital_name', 
                                hospital_address = '$hospital_address', 
                                hospital_phone = '$hospital_phone',
                                reg_no = '$reg_no'";

            if (!empty($hospital_pic)) {
                $update_query .= ", hospital_pic = '$hospital_pic'";
            }
            $update_query .= ", updated_at = NOW() WHERE hospital_id = $hospital_id";

            if (mysqli_query($con, $update_query)) {
                if (!empty($user_id)) {
                    $user_update = "UPDATE users SET status = '$status'";
                    if (!empty($password)) $user_update .= ", password = '$password'";
                    $user_update .= " WHERE user_id = $user_id";
                    mysqli_query($con, $user_update);
                }

                $beds_check = mysqli_query($con, "SELECT id FROM hospital_beds WHERE hospital_id = $hospital_id");
                if (mysqli_num_rows($beds_check) > 0) {
                    mysqli_query($con, "UPDATE hospital_beds SET 
                                            total_beds = $total_beds,
                                            icu_beds = $icu_beds,
                                            general_beds = $general_beds,
                                            private_beds = $private_beds,
                                            updated_at = NOW()
                                        WHERE hospital_id = $hospital_id");
                } else {
                    if ($total_beds > 0 || $icu_beds > 0 || $general_beds > 0 || $private_beds > 0) {
                        mysqli_query($con, "INSERT INTO hospital_beds (hospital_id, total_beds, icu_beds, general_beds, private_beds, created_at, updated_at) 
                                            VALUES ($hospital_id, $total_beds, $icu_beds, $general_beds, $private_beds, NOW(), NOW())");
                    }
                }

                // ===== FACILITIES (fixed) =====
                save_facilities($con, $hospital_id, $facility_list);

                $success_msg = "Hospital updated successfully!";

                // Refresh
                $edit_result = mysqli_query($con, "SELECT h.*, u.status as estatus, u.username, u.email as hospital_email
                                                   FROM hospitals h
                                                   LEFT JOIN users u ON u.user_id = h.user_id
                                                   WHERE h.hospital_id = $hospital_id");
                $hospital_data = mysqli_fetch_assoc($edit_result);

                $beds_result = mysqli_query($con, "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id");
                $beds_data = mysqli_fetch_assoc($beds_result);

                $facilities_result = mysqli_query($con, "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id");
                $facilities_data = [];
                while ($row = mysqli_fetch_assoc($facilities_result)) $facilities_data[] = $row;
            } else {
                $error_msg = "Error: " . mysqli_error($con);
            }
        } else {
            $generate_user = "INSERT INTO users (username, email, password, user_type_id, status, created_at)
                              VALUES ('$username', '$hospital_email', '$password', 5, 1, '$created_at')";
            mysqli_query($con, $generate_user);
            $userid = mysqli_insert_id($con);

            $insert_query = "INSERT INTO hospitals 
                                (city_id, user_id, hospital_name, hospital_address, hospital_phone, hospital_pic, reg_no, approve, created_at) 
                             VALUES 
                                ('$city_id', '$userid', '$hospital_name', '$hospital_address', '$hospital_phone', '$hospital_pic', '$reg_no', 1, NOW())";

            if (mysqli_query($con, $insert_query)) {
                $hospital_id = mysqli_insert_id($con);

                if ($total_beds > 0 || $icu_beds > 0 || $general_beds > 0 || $private_beds > 0) {
                    mysqli_query($con, "INSERT INTO hospital_beds (hospital_id, total_beds, icu_beds, general_beds, private_beds, created_at, updated_at) 
                                        VALUES ($hospital_id, $total_beds, $icu_beds, $general_beds, $private_beds, NOW(), NOW())");
                }

                // ===== FACILITIES (fixed) =====
                save_facilities($con, $hospital_id, $facility_list);

                $success_msg = "Hospital added successfully!";
            } else {
                $error_msg = "Error: " . mysqli_error($con);
            }
        }
    }
}
?>

<!-- ===== BRAND COLOR THEME ===== -->
<style>
:root {
    /* brand palette (colors.php) */
    --navy-deep:   <?= $color5 ?>;
    --navy:        <?= $color1 ?>;
    --teal:        <?= $color2 ?>;
    --green:       <?= $color3 ?>;
    --teal-green:  <?= $color4 ?>;
    --tint-blue:   <?= $color6 ?>;
    --tint-green:  <?= $color7 ?>;
    --white:       <?= $color8 ?>;
    --g-navy:      linear-gradient(135deg, <?= $color5 ?> 0%, <?= $color1 ?> 100%);
    --g-navy-teal: linear-gradient(135deg, <?= $color5 ?> 0%, <?= $color1 ?> 62%, <?= $color2 ?> 135%);
    --g-accent:    linear-gradient(90deg, <?= $color2 ?>, <?= $color3 ?>, <?= $color4 ?>);

    /* old variable names kept so inline styles keep working */
    --brand:        <?= $color1 ?>;
    --brand-light:  <?= $color2 ?>;
    --brand-soft:   <?= $color6 ?>;
    --brand-border: #cfe0f3;
    --success:      <?= $color3 ?>;
    --danger:       #dc2626;
    --text:         <?= $color5 ?>;
    --text-muted:   #5b6b86;
    --border:       #dbe4f2;

    --radius: 14px;
    --shadow: 0 10px 30px rgba(13, 43, 110, .10);
    --ring: 0 0 0 4px rgba(0, 168, 204, .18);
}

/* ================= Page header ================= */
.content-wrapper .main-header {
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    margin-bottom: 20px;
}
.content-wrapper .main-header h4 { margin: 0; font-weight: 800; font-size: 1.5rem; color: var(--navy-deep); }
.content-wrapper .main-header .btn-secondary {
    float: none !important; background: var(--white); color: var(--navy); border: 1.5px solid var(--navy);
    border-radius: 10px; font-weight: 600; padding: 8px 18px; transition: all .2s;
}
.content-wrapper .main-header .btn-secondary:hover { background: var(--navy); color: var(--white); }

/* ================= Alerts ================= */
.content-wrapper .alert { border: 0; border-radius: 12px; padding: 14px 18px; font-weight: 500; box-shadow: 0 4px 14px rgba(13,43,110,.06); }
.content-wrapper .alert-success { background: var(--tint-green); color: #14703a; border-left: 5px solid var(--green); }
.content-wrapper .alert-danger  { background: #fef2f2; color: #b91c1c; border-left: 5px solid var(--danger); }

/* ================= Card ================= */
.content-wrapper .card { border: 1px solid var(--border); border-radius: 18px; box-shadow: var(--shadow); overflow: hidden; }
.content-wrapper .card > .card-header {
    background: var(--g-navy-teal); border: 0; padding: 20px 28px; position: relative;
}
.content-wrapper .card > .card-header::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 4px; background: var(--g-accent); }
.content-wrapper .card > .card-header .card-header-text { margin: 0; color: var(--white); font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
.content-wrapper .card > .card-header .card-header-text i { color: var(--green); }
.card .card-block { padding: 28px; }

/* ================= Section titles ================= */
.form-section-title {
    display: flex; align-items: center; gap: 12px;
    font-size: 15px; font-weight: 700; color: var(--navy-deep);
    margin: 2.2rem 0 1.2rem; padding: 0; border: 0;
}
.form-section-title:first-child { margin-top: 0; }
.form-section-title i {
    width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px;
    background: var(--g-navy); color: var(--white); font-size: 14px;
    display: inline-flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 10px rgba(13,43,110,.25);
}
.form-section-title::after { content: ""; flex: 1; height: 2px; border-radius: 2px; background: linear-gradient(90deg, var(--brand-border), transparent); }

/* ================= Registration box ================= */
.regno-box {
    position: relative; overflow: hidden;
    background: var(--g-navy-teal); border: 0; border-radius: 16px;
    padding: 22px 24px; margin-bottom: 26px; box-shadow: 0 12px 28px rgba(13,43,110,.25);
}
.regno-box::after {
    content: ""; position: absolute; right: -60px; top: -80px; width: 240px; height: 240px; border-radius: 50%;
    background: radial-gradient(circle, rgba(0,168,204,.4), rgba(0,168,204,0) 70%); pointer-events: none;
}
.regno-box > * { position: relative; z-index: 1; }
.regno-box label { color: var(--white); font-weight: 700; font-size: 13px; letter-spacing: .6px; text-transform: uppercase; margin-bottom: 8px; }
.regno-box label i { color: var(--green); }
.regno-input { height: 52px !important; font-size: 18px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; background: var(--white); border: 2px solid transparent !important; color: var(--navy-deep); }
.regno-input:focus { border-color: var(--green) !important; box-shadow: 0 0 0 4px rgba(39,201,106,.25) !important; }
.regno-box .field-hint { color: rgba(255,255,255,.85); margin-top: 8px; }
.regno-box .field-hint.is-valid   { color: #86efac; }
.regno-box .field-hint.is-invalid { color: #fecaca; }

/* ================= Labels & controls ================= */
.card-block .form-group { margin-bottom: 18px; }
.card-block label { font-size: 13px; font-weight: 600; color: var(--navy-deep); margin-bottom: 7px; }
.card-block .form-control, .card-block .form-select {
    height: 46px; border: 1.5px solid #d3deef; border-radius: 10px; padding: 0 14px;
    font-size: 14px; color: var(--navy-deep); background: var(--white); transition: border-color .2s, box-shadow .2s;
}
.card-block textarea.form-control { height: auto; padding: 12px 14px; min-height: 98px; }
.card-block .form-control::placeholder { color: #94a3b8; }
.card-block .form-control:hover { border-color: #b7c6e2; }
.form-control:focus, .form-select:focus { border-color: var(--teal); box-shadow: var(--ring); outline: 0; }
.card-block .form-control[readonly] { background: #f1f5fb; color: #5b6b86; cursor: not-allowed; }
.form-control.is-invalid { border-color: var(--danger); background: #fef2f2; }
.form-control.is-valid   { border-color: var(--green);  background: var(--tint-green); }
.regno-box .form-control.is-valid, .regno-box .form-control.is-invalid { background: var(--white); }

.field-hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; display: block; }
.field-hint.is-invalid { color: var(--danger); font-weight: 600; }
.field-hint.is-valid   { color: #14903f; font-weight: 600; }

/* ================= Beds ================= */
.bed-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.bed-card {
    position: relative; background: var(--white); border: 1px solid var(--border); border-radius: 14px;
    padding: 16px 14px 14px; text-align: center; overflow: hidden; transition: transform .2s, box-shadow .2s, border-color .2s;
}
.bed-card::before { content: ""; position: absolute; left: 0; right: 0; top: 0; height: 4px; background: var(--g-navy); }
.bed-card:nth-child(2)::before { background: linear-gradient(90deg, var(--navy), var(--teal)); }
.bed-card:nth-child(3)::before { background: linear-gradient(90deg, var(--teal), var(--teal-green)); }
.bed-card:nth-child(4)::before { background: linear-gradient(90deg, var(--teal-green), var(--green)); }
.bed-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); border-color: var(--teal); background: var(--white); }
.bed-card label { display: block; font-size: 12px; color: var(--text-muted); font-weight: 700; letter-spacing: .3px; margin-bottom: 8px; }
.bed-card .form-control { text-align: center; font-size: 22px; font-weight: 800; color: var(--navy); height: 52px; background: var(--tint-blue); border-color: transparent; }
.bed-card .form-control:focus { background: var(--white); }

/* ================= Facilities ================= */
.facility-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 14px; }
.facility-item {
    position: relative; background: var(--white); border: 1.5px solid var(--border); border-radius: 14px;
    padding: 14px; transition: border-color .2s, box-shadow .2s, background .2s;
}
.facility-item:hover { border-color: var(--teal); }
.facility-item.active { background: var(--tint-blue); border-color: var(--navy); box-shadow: 0 6px 18px rgba(13,43,110,.12); }
.facility-item.active::after {
    content: "\f00c"; font-family: "Font Awesome 5 Free", "Font Awesome 6 Free", FontAwesome; font-weight: 900;
    position: absolute; right: 12px; top: 12px; width: 22px; height: 22px; border-radius: 50%;
    background: var(--green); color: var(--navy-deep); font-size: 10px; display: flex; align-items: center; justify-content: center;
}
.facility-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding-right: 28px; }
.facility-header input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--navy); cursor: pointer; flex-shrink: 0; }
.facility-header label { margin: 0; font-size: 14px; font-weight: 700; color: var(--navy-deep); cursor: pointer; }
.facility-item .form-control { height: 38px; font-size: 13px; padding: 0 12px; }
.facility-item .form-control:disabled { background: #f4f7fc; color: #94a3b8; border-style: dashed; }

/* ================= Upload ================= */
.upload-box {
    border: 2px dashed #b7cbea; border-radius: 14px; padding: 30px 16px; text-align: center; cursor: pointer;
    background: linear-gradient(180deg, var(--tint-blue), var(--white)); transition: all .2s;
}
.upload-box:hover { border-color: var(--navy); background: var(--tint-blue); transform: translateY(-2px); }
.upload-box i { font-size: 34px; margin-bottom: 10px; display: block; background: var(--g-navy-teal); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
.upload-box p { margin: 0 0 4px; font-size: 14px; font-weight: 700; color: var(--navy); }
.upload-box small { font-size: 12px; color: var(--text-muted); }
.upload-box input[type="file"] { display: none; }
.img-preview { margin-top: 14px; border-radius: 14px; overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow); }
.img-preview img { width: 100%; max-height: 200px; object-fit: cover; display: block; }

/* ================= Status switch ================= */
.status-toggle {
    background: var(--white); border: 1.5px solid var(--border); border-radius: 14px; padding: 18px;
    display: flex; align-items: center; gap: 16px;
}
.status-toggle input[type="checkbox"] {
    -webkit-appearance: none; appearance: none; flex-shrink: 0;
    width: 54px; height: 30px; border-radius: 999px; background: #c3cfe6; position: relative; cursor: pointer;
    transition: background .25s; border: 0; outline: 0; margin: 0;
}
.status-toggle input[type="checkbox"]::before {
    content: ""; position: absolute; top: 3px; left: 3px; width: 24px; height: 24px; border-radius: 50%;
    background: var(--white); box-shadow: 0 2px 6px rgba(0,0,0,.25); transition: transform .25s;
}
.status-toggle input[type="checkbox"]:checked { background: var(--g-navy-teal); }
.status-toggle input[type="checkbox"]:checked::before { transform: translateX(24px); }
.status-toggle input[type="checkbox"]:focus-visible { box-shadow: var(--ring); }
.status-toggle .status-text strong { display: block; font-size: 14px; color: var(--navy-deep); font-weight: 700; }
.status-toggle .status-text small { font-size: 12px; color: var(--text-muted); }

/* ================= Sticky action bar ================= */
#hospitalForm > .row.mt-4 {
    position: sticky; bottom: 0; z-index: 20;
    margin: 28px -28px -28px !important; padding: 16px 28px !important;
    background: rgba(255,255,255,.94); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    border-top: 1px solid var(--border) !important; box-shadow: 0 -8px 24px rgba(13,43,110,.06);
}
.btn-submit {
    background: var(--g-navy); color: var(--white); border: 0; padding: 11px 28px; font-weight: 700; border-radius: 10px;
    box-shadow: 0 6px 16px rgba(13,43,110,.28); transition: transform .2s, box-shadow .2s, filter .2s;
}
.btn-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(13,43,110,.35); color: var(--white); filter: brightness(1.12); }
.btn-submit:focus-visible { outline: 3px solid var(--teal); outline-offset: 2px; }
.btn-submit:disabled { opacity: .5; cursor: not-allowed; box-shadow: none; }
#hospitalForm .btn-secondary { background: var(--white); color: var(--navy); border: 1.5px solid #c3cfe6; border-radius: 10px; font-weight: 600; padding: 10px 22px; }
#hospitalForm .btn-secondary:hover { background: var(--tint-blue); border-color: var(--navy); color: var(--navy); }

/* ================= Select2 ================= */
.select2-container--bootstrap-5 .select2-selection {
    border: 1.5px solid #d3deef !important; border-radius: 10px !important; min-height: 46px !important;
    padding: 8px 12px !important; display: flex; align-items: center; color: var(--navy-deep);
}
.select2-container--bootstrap-5.select2-container--focus .select2-selection,
.select2-container--bootstrap-5.select2-container--open .select2-selection { border-color: var(--teal) !important; box-shadow: var(--ring) !important; }
.select2-container--bootstrap-5 .select2-dropdown { border: 1.5px solid var(--navy); border-radius: 10px; overflow: hidden; box-shadow: var(--shadow); }
.select2-container--bootstrap-5 .select2-results__option--highlighted { background: var(--navy) !important; color: var(--white) !important; }
.select2-container--bootstrap-5 .select2-results__option--selected { background: var(--tint-blue) !important; color: var(--navy) !important; }

/* ================= Responsive ================= */
@media (max-width: 991px) {
    .bed-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 768px) {
    .card .card-block { padding: 18px 16px; }
    .content-wrapper .card > .card-header { padding: 16px; }
    .regno-box { padding: 18px 16px; }
    .regno-input { font-size: 16px; }
    .facility-grid { grid-template-columns: 1fr; }
    #hospitalForm > .row.mt-4 { margin: 24px -16px -18px !important; padding: 12px 16px !important; }
    #hospitalForm > .row.mt-4 .btn { width: 100%; margin: 0 0 8px !important; }
}
@media (prefers-reduced-motion: reduce) {
    .bed-card, .upload-box, .btn-submit, .status-toggle input[type="checkbox"]::before { transition: none; }
}
</style>


<div class="content-wrapper">
    <!-- Container-fluid starts -->
    <div class="container-fluid">

        <!-- ===== PAGE HEADER ===== -->
        <div class="row">
            <div class="main-header">
                <h4><?= $edit_mode ? 'Edit Hospital' : 'Add New Hospital' ?></h4>
                <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn btn-secondary pull-right">
                    <i class="icon-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <!-- ===== ALERTS ===== -->
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-1"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-1"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <!-- ===== FORM CARD ===== -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-header-text">
                            <i class="fas fa-hospital me-2"></i>
                            Hospital Information
                        </h5>
                    </div>
                    <div class="card-block">
                        <form method="POST" action="" enctype="multipart/form-data" id="hospitalForm">

                            <!-- ===== REG NO (HIGHLIGHT) ===== -->
                            <div class="regno-box">
                                <label for="reg_no">
                                    <i class="fas fa-certificate me-1"></i>
                                    Registration No <span style="color:var(--danger);">*</span>
                                </label>
                                <input type="text" class="form-control regno-input" id="reg_no" name="reg_no"
                                       placeholder="e.g. HOSP-2025-0001" required autocomplete="off"
                                       value="<?= $edit_mode ? htmlspecialchars($hospital_data['reg_no'] ?? '') : '' ?>">
                                <small class="field-hint" id="reg_no_hint">
                                    <i class="fas fa-info-circle me-1"></i> Unique hospital registration number
                                </small>
                            </div>

                            <!-- ===== LOGIN ACCOUNT ===== -->
                            <div class="form-section-title">
                                <i class="fas fa-user-lock"></i>
                                <span>Login Account</span>
                            </div>

                            <input type="hidden" name="user_id" value="<?= $edit_mode && isset($hospital_data['user_id']) ? htmlspecialchars($hospital_data['user_id']) : '' ?>">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="username">Username <span style="color:var(--danger);">*</span></label>
                                        <input type="text" class="form-control" id="username" name="username"
                                               placeholder="Enter username" required autocomplete="off"
                                               value="<?= $edit_mode ? htmlspecialchars($hospital_data['username'] ?? '') : '' ?>"
                                               <?= $edit_mode ? 'readonly' : '' ?>>
                                        <small class="field-hint" id="username_hint">
                                            <i class="fas fa-info-circle me-1"></i> Unique username for login
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="hospital_email">Email</label>
                                        <input type="email" class="form-control" id="hospital_email" name="hospital_email"
                                               placeholder="hospital@example.com"
                                               value="<?= $edit_mode ? htmlspecialchars($hospital_data['hospital_email'] ?? '') : '' ?>"
                                               <?= $edit_mode ? 'readonly' : '' ?>>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password">
                                            Password <?= $edit_mode ? '' : '<span style="color:var(--danger);">*</span>' ?>
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password"
                                               placeholder="<?= $edit_mode ? 'Leave blank to keep current' : 'Enter password' ?>"
                                               <?= $edit_mode ? '' : 'required' ?>>
                                        <?php if ($edit_mode): ?>
                                            <small class="field-hint">Leave blank to keep current password</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== BASIC DETAILS ===== -->
                            <div class="form-section-title">
                                <i class="fas fa-info-circle"></i>
                                <span>Basic Details</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hospitalName">Hospital Name <span style="color:var(--danger);">*</span></label>
                                        <input type="text" class="form-control" id="hospitalName" name="hospital_name"
                                               placeholder="Enter hospital name" required
                                               value="<?= $edit_mode ? htmlspecialchars($hospital_data['hospital_name'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cityId">City <span style="color:var(--danger);">*</span></label>
                                        <select class="form-control" id="cityId" name="city_id" required>
                                            <option value="">-- Search & select city --</option>
                                            <?php
                                            if ($cities_result) {
                                                mysqli_data_seek($cities_result, 0);
                                                while ($city = mysqli_fetch_assoc($cities_result)):
                                            ?>
                                                <option value="<?= $city['city_id'] ?>"
                                                    <?= ($edit_mode && isset($hospital_data['city_id']) && $hospital_data['city_id'] == $city['city_id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($city['city_name']) ?>
                                                    <?= !empty($city['p_name']) ? '(' . htmlspecialchars($city['p_name']) . ')' : '' ?>
                                                </option>
                                            <?php endwhile; } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hospitalPhone">Helpline / Phone <small class="text-muted">(optional)</small></label>
                                        <input type="text" class="form-control" id="hospitalPhone" name="hospital_phone"
                                               placeholder="e.g. 091-1234567"
                                               value="<?= $edit_mode ? htmlspecialchars($hospital_data['hospital_phone'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hospitalAddress">Address <small class="text-muted">(optional)</small></label>
                                        <textarea class="form-control" id="hospitalAddress" name="hospital_address"
                                                  placeholder="Enter full hospital address" rows="2"><?= $edit_mode ? htmlspecialchars($hospital_data['hospital_address'] ?? '') : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== BEDS ===== -->
                            <div class="form-section-title">
                                <i class="fas fa-bed"></i>
                                <span>Bed Availability</span>
                            </div>

                            <div class="bed-grid">
                                <div class="bed-card">
                                    <label>Total Beds</label>
                                    <input type="number" class="form-control" name="total_beds"
                                           min="0" value="<?= $edit_mode && $beds_data ? $beds_data['total_beds'] : 0 ?>">
                                </div>
                                <div class="bed-card">
                                    <label>ICU Beds</label>
                                    <input type="number" class="form-control" name="icu_beds"
                                           min="0" value="<?= $edit_mode && $beds_data ? $beds_data['icu_beds'] : 0 ?>">
                                </div>
                                <div class="bed-card">
                                    <label>General Beds</label>
                                    <input type="number" class="form-control" name="general_beds"
                                           min="0" value="<?= $edit_mode && $beds_data ? $beds_data['general_beds'] : 0 ?>">
                                </div>
                                <div class="bed-card">
                                    <label>Private Beds</label>
                                    <input type="number" class="form-control" name="private_beds"
                                           min="0" value="<?= $edit_mode && $beds_data ? $beds_data['private_beds'] : 0 ?>">
                                </div>
                            </div>

                            <!-- ===== FACILITIES ===== -->
                            <div class="form-section-title">
                                <i class="fas fa-concierge-bell"></i>
                                <span>Facilities & Services</span>
                            </div>

                            <?php
                            // DB me purane rows kisi bhi spelling (Ct Scan / CT Scan) me ho sakte hain, isliye lowercase key
                            $existing_facilities = [];
                            foreach ($facilities_data as $fac) {
                                $existing_facilities[strtolower(trim($fac['facility_name']))] = $fac;
                            }
                            ?>

                            <div class="facility-grid">
                                <?php foreach ($facility_list as $key => $label):
                                    $row_f       = $existing_facilities[strtolower($label)] ?? null;
                                    $is_checked  = $row_f && (int)$row_f['is_available'] === 1;
                                    $description = $row_f['description'] ?? '';
                                ?>
                                <div class="facility-item <?= $is_checked ? 'active' : '' ?>">
                                    <div class="facility-header">
                                        <input type="checkbox"
                                               class="facility-checkbox"
                                               id="chk_<?= $key ?>"
                                               name="facilities_available[]"
                                               value="<?= $key ?>"
                                               <?= $is_checked ? 'checked' : '' ?>>
                                        <label for="chk_<?= $key ?>"><?= $label ?></label>
                                    </div>
                                    <input type="text"
                                           class="form-control facility-desc"
                                           id="desc_<?= $key ?>"
                                           name="facility_descriptions[<?= $key ?>]"
                                           value="<?= htmlspecialchars($description) ?>"
                                           placeholder="e.g. 24/7, 10 beds"
                                           <?= $is_checked ? 'required' : 'disabled' ?>>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- ===== PICTURE & STATUS ===== -->
                            <div class="form-section-title">
                                <i class="fas fa-image"></i>
                                <span>Picture & Status</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Hospital Picture</label>
                                        <div class="upload-box" onclick="document.getElementById('hospital_pic').click()">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>Click to upload picture</p>
                                            <small>JPG, PNG, GIF, WEBP (max 2MB)</small>
                                            <input type="file" id="hospital_pic" name="hospital_pic" accept="image/*" onchange="previewImage(this)">
                                        </div>

                                        <?php if ($edit_mode && !empty($hospital_data['hospital_pic'])): ?>
                                            <div class="img-preview" id="currentPreview">
                                                <img src="<?= BASE_URL ?>admin/inc/uploads/hospitals/<?= htmlspecialchars($hospital_data['hospital_pic']) ?>"
                                                     alt="Hospital" id="previewImg">
                                            </div>
                                        <?php else: ?>
                                            <div class="img-preview" id="currentPreview" style="display:none;">
                                                <img src="" alt="Preview" id="previewImg">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Active Status</label>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="statusSwitch" name="status" value="1"
                                                   <?php
                                                   if ($edit_mode) {
                                                       echo (isset($hospital_data['estatus']) && $hospital_data['estatus'] == 1) ? 'checked' : '';
                                                   } else {
                                                       echo 'checked';
                                                   }
                                                   ?>>
                                            <div class="status-text">
                                                <strong>Active / Visible</strong>
                                                <small>When active, hospital will appear on public website</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== ACTIONS ===== -->
                            <div class="row mt-4 pt-3" style="border-top: 1px solid #e2e8f0;">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-submit" id="submitBtn">
                                        <i class="fas <?= $edit_mode ? 'fa-save' : 'fa-plus-circle' ?> me-1"></i>
                                        <?= $edit_mode ? 'Update Hospital' : 'Add Hospital' ?>
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn btn-secondary ms-2">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Container-fluid ends -->
</div>

<script>
// ===== FACILITY TOGGLE =====
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.facility-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const item  = this.closest('.facility-item');
            const input = item.querySelector('.facility-desc');

            if (this.checked) {
                input.disabled = false;
                input.required = true;
                input.focus();
                item.classList.add('active');
            } else {
                input.disabled = true;
                input.required = false;
                input.value = '';
                item.classList.remove('active');
            }
        });
    });
});

// ===== IMAGE PREVIEW =====
function previewImage(input) {
    const previewWrap = document.getElementById('currentPreview');
    const previewImg = document.getElementById('previewImg');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewWrap.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ===== SELECT2 CITY SEARCH =====
$(document).ready(function() {
    if ($.fn.select2) {
        $('#cityId').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search city...',
            allowClear: true,
            width: '100%',
            matcher: function(params, data) {
                if (!params.term || params.term.trim() === '') return data;
                const term = params.term.toLowerCase();
                if (data.text.toLowerCase().indexOf(term) > -1) return data;
                return null;
            }
        });
    }
});

// ===== LIVE DUPLICATE CHECK =====
(function() {
    const usernameEl = document.getElementById('username');
    const regNoEl    = document.getElementById('reg_no');
    const userHint   = document.getElementById('username_hint');
    const regHint    = document.getElementById('reg_no_hint');
    const submitBtn  = document.getElementById('submitBtn');
    const form       = document.getElementById('hospitalForm');

    const EDIT_MODE = <?= $edit_mode ? 'true' : 'false' ?>;
    const CHECK_URL = '<?= BASE_URL ?>admin/check_hospital_duplicate.php';

    let usernameOK = EDIT_MODE;
    let regNoOK    = false;

    function updateSubmitState() {
        if (submitBtn) {
            submitBtn.disabled = !(usernameOK && regNoOK);
        }
    }

    // Username live check
    if (usernameEl && !EDIT_MODE) {
        usernameEl.addEventListener('input', function() {
            const v = this.value.trim();
            if (v === '') {
                userHint.className = 'field-hint';
                userHint.innerHTML = '<i class="fas fa-info-circle me-1"></i> Unique username for login';
                this.classList.remove('is-invalid', 'is-valid');
                usernameOK = false;
                updateSubmitState();
                return;
            }

            fetch(CHECK_URL + '?field=username&value=' + encodeURIComponent(v))
                .then(r => r.json())
                .then(data => {
                    usernameOK = !data.exists;
                    if (usernameOK) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                        userHint.className = 'field-hint is-valid';
                        userHint.innerHTML = '<i class="fas fa-check me-1"></i> Available';
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                        userHint.className = 'field-hint is-invalid';
                        userHint.innerHTML = '<i class="fas fa-times me-1"></i> Already exists';
                    }
                    updateSubmitState();
                })
                .catch(err => {
                    console.error('Username Check Error:', err);
                    usernameOK = true;
                    updateSubmitState();
                });
        });
    }

    // Reg No live check
    if (regNoEl) {
        regNoEl.addEventListener('input', function() {
            const v = this.value.trim();
            if (v === '') {
                regHint.className = 'field-hint';
                regHint.innerHTML = '<i class="fas fa-info-circle me-1"></i> Unique hospital registration number';
                this.classList.remove('is-invalid', 'is-valid');
                regNoOK = false;
                updateSubmitState();
                return;
            }

            let url = CHECK_URL + '?field=reg_no&value=' + encodeURIComponent(v);
            if (EDIT_MODE) url += '&exclude_id=<?= $edit_mode ? $hospital_id : 0 ?>';

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    regNoOK = !data.exists;
                    if (regNoOK) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                        regHint.className = 'field-hint is-valid';
                        regHint.innerHTML = '<i class="fas fa-check me-1"></i> Available';
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                        regHint.className = 'field-hint is-invalid';
                        regHint.innerHTML = '<i class="fas fa-times me-1"></i> Already exists in system';
                    }
                    updateSubmitState();
                })
                .catch(err => {
                    console.error('RegNo Check Error:', err);
                    regNoOK = true;
                    updateSubmitState();
                });
        });
    }

    // Initial state
    if (regNoEl && regNoEl.value.trim() !== '') regNoOK = true;
    updateSubmitState();

    // Final submit guard
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!usernameOK || !regNoOK) {
                e.preventDefault();
                alert('Please fix duplicate errors before submitting.');
            }
        });
    }
})();
</script>


<?php include BASE_PATH.'/admin/inc/footer.php';?>