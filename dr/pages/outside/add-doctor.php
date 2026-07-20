<?php
include '../../config.php';

$success_msg = '';
$error_msg = '';

$form_data = [
    'city_id' => '',
    'hospital_id' => '',
    'doctor_name' => '',
    'specialization' => '',
    'specialization_txt' => '',
    'experience_years' => '',
    'doctor_phone' => '',
    'short_detail' => '',
    'other' => '',
    'doctor_email' => '',
    'gender' => '',
    'static_clinical_info' => '',
    'doctor_type' => '1',
    'clinic_name' => '',
    'clinic_address' => '',
    'if_not_available' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form_data as $key => $value) {
        $form_data[$key] = isset($_POST[$key]) ? trim($_POST[$key]) : $value;
    }

    $city_id = (int) $form_data['city_id'];
    $hospital_id = (int) $form_data['hospital_id'];
    $doctor_name = mysqli_real_escape_string($con, $form_data['doctor_name']);
    $short_detail = mysqli_real_escape_string($con, $form_data['short_detail']);
    $experience_years = mysqli_real_escape_string($con, $form_data['experience_years']);
    $doctor_phone = mysqli_real_escape_string($con, $form_data['doctor_phone']);
    $doctor_email = mysqli_real_escape_string($con, $form_data['doctor_email']);
    $doctor_type = $form_data['doctor_type'] === '2' ? 2 : 1;
    $gender = mysqli_real_escape_string($con, $form_data['gender']);
    $other = mysqli_real_escape_string($con, $form_data['other']);
    $static_clinical_info = mysqli_real_escape_string($con, $form_data['static_clinical_info']);
    $clinic_name = mysqli_real_escape_string($con, $form_data['clinic_name']);
    $clinic_address = mysqli_real_escape_string($con, $form_data['clinic_address']);
    $doctor_pic = '';
    $status = 1;
    $approve = 0;

    $errors = [];

    if ($city_id <= 0) {
        $errors[] = 'City select karna zaroori hai.';
    }

    if ($doctor_name === '') {
        $errors[] = 'Doctor name required hai.';
    }

    if ($gender === '') {
        $errors[] = 'Gender select karna zaroori hai.';
    }

    $specialization = 0;
    $add_new_specialization = isset($_POST['if_not_available']) && $_POST['if_not_available'] === '1';

    if ($add_new_specialization) {
        if ($form_data['specialization_txt'] === '') {
            $errors[] = 'Nayi specialization likhna zaroori hai.';
        }
    } else {
        $specialization = (int) $form_data['specialization'];
        if ($specialization <= 0) {
            $errors[] = 'Specialization select karna zaroori hai.';
        }
    }

    if ($doctor_type === 1) {
        if ($hospital_id <= 0) {
            $errors[] = 'Hospital doctor ke liye hospital select karna zaroori hai.';
        }
        $clinic_name = '';
        $clinic_address = '';
    } else {
        if ($form_data['clinic_name'] === '') {
            $errors[] = 'Clinic name required hai.';
        }
        if ($form_data['clinic_address'] === '') {
            $errors[] = 'Clinic address required hai.';
        }
        $hospital_id = 0;
    }

    if (empty($errors)) {
        mysqli_begin_transaction($con);

        try {
            if ($add_new_specialization) {
                $new_type = mysqli_real_escape_string($con, $form_data['specialization_txt']);
                $new_cat_query = "INSERT INTO dr_cat_types (dr_cat_id, type) VALUES (12, '$new_type')";
                if (!mysqli_query($con, $new_cat_query)) {
                    throw new Exception(mysqli_error($con));
                }
                $specialization = (int) mysqli_insert_id($con);
            }

            $generate_ent_it = "INSERT INTO entities (entity_type, created_at) VALUES ('doctor', CURDATE())";
            if (!mysqli_query($con, $generate_ent_it)) {
                throw new Exception(mysqli_error($con));
            }

            $entity_id = (int) mysqli_insert_id($con);
            $hospital_id_value = $doctor_type === 1 ? $hospital_id : "NULL";

            $insert_query = "INSERT INTO doctors (
                                entity_id,
                                city_id,
                                hospital_id,
                                doctor_name,
                                short_detail,
                                cat_type_id,
                                experience_years,
                                doctor_phone,
                                doctor_email,
                                doctor_type,
                                clinic_name,
                                clinic_address,
                                doctor_pic,
                                static_clinical_info,
                                status,
                                approve,
                                gender,
                                other,
                                created_at
                            ) VALUES (
                                $entity_id,
                                '$city_id',
                                $hospital_id_value,
                                '$doctor_name',
                                '$short_detail',
                                '$specialization',
                                '$experience_years',
                                '$doctor_phone',
                                '$doctor_email',
                                '$doctor_type',
                                '$clinic_name',
                                '$clinic_address',
                                '$doctor_pic',
                                '$static_clinical_info',
                                $status,
                                $approve,
                                '$gender',
                                '$other',
                                NOW()
                            )";

            if (!mysqli_query($con, $insert_query)) {
                throw new Exception(mysqli_error($con));
            }

            $doctor_id = (int) mysqli_insert_id($con);

            if ($doctor_type === 1) {
                $doctor_hospital_query = "INSERT INTO doctor_in_hospital (doctor_id, hospital_id) VALUES ($doctor_id, $hospital_id)";
            } else {
                $doctor_hospital_query = "INSERT INTO doctor_in_hospital (doctor_id, if_clinic, hospital_id) VALUES ($doctor_id, 1, 0)";
            }

            if (!mysqli_query($con, $doctor_hospital_query)) {
                throw new Exception(mysqli_error($con));
            }

            mysqli_commit($con);

            $success_msg = 'Doctor details successfully save ho gaye.';
            $form_data = [
                'city_id' => '',
                'hospital_id' => '',
                'doctor_name' => '',
                'specialization' => '',
                'specialization_txt' => '',
                'experience_years' => '',
                'doctor_phone' => '',
                'short_detail' => '',
                'other' => '',
                'doctor_email' => '',
                'gender' => '',
                'static_clinical_info' => '',
                'doctor_type' => '1',
                'clinic_name' => '',
                'clinic_address' => '',
                'if_not_available' => '',
            ];
        } catch (Exception $e) {
            mysqli_rollback($con);
            $error_msg = 'Data save nahi ho saka: ' . $e->getMessage();
        }
    } else {
        $error_msg = implode('<br>', $errors);
    }
}

$cities_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

$hospitals_query = "SELECT hospital_id, hospital_name, city_id 
FROM hospitals 
LEFT JOIN entities e ON e.entity_id = hospitals.entity_id
WHERE e.status = 1 ORDER BY hospital_name ASC";
$hospitals_result = mysqli_query($con, $hospitals_query);

$categories_query = "SELECT dc.dr_cat_id, dc.cat_name, dct.dr_cat_type_id, dct.type
                     FROM dr_categories dc
                     LEFT JOIN dr_cat_types dct ON dc.dr_cat_id = dct.dr_cat_id
                     ORDER BY dc.cat_name, dct.type";
$categories_result = mysqli_query($con, $categories_query);

$categories_data = [];
if ($categories_result) {
    while ($row = mysqli_fetch_assoc($categories_result)) {
        if (!isset($categories_data[$row['dr_cat_id']])) {
            $categories_data[$row['dr_cat_id']] = [
                'cat_name' => $row['cat_name'],
                'types' => [],
            ];
        }

        if (!empty($row['dr_cat_type_id'])) {
            $categories_data[$row['dr_cat_id']]['types'][] = [
                'dr_cat_type_id' => $row['dr_cat_type_id'],
                'type' => $row['type'],
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Doctor - <?php echo SITE_TITLE; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0d6efd;
            --primary-dark: #0b5ed7;
            --primary-soft: rgba(13, 110, 253, 0.12);
            --accent: #16a34a;
            --accent-soft: rgba(22, 163, 74, 0.12);
            --light: #f8fafc;
            --dark: #0f172a;
            --muted: #64748b;
            --border: #d9e3f0;
            --card-bg: rgba(255, 255, 255, 0.88);
            --gradient: linear-gradient(135deg, #0d6efd 0%, #4f46e5 52%, #16a34a 100%);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(79, 70, 229, 0.14), transparent 28%),
                radial-gradient(circle at top right, rgba(22, 163, 74, 0.12), transparent 26%),
                linear-gradient(180deg, #edf4ff 0%, #f5fbff 48%, #f8fffb 100%);
            color: var(--dark);
            position: relative;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 380px;
            height: 380px;
            border-radius: 999px;
            filter: blur(70px);
            opacity: 0.35;
            pointer-events: none;
            z-index: 0;
        }

        body::before {
            top: 80px;
            left: -120px;
            background: rgba(13, 110, 253, 0.25);
        }

        body::after {
            bottom: 40px;
            right: -120px;
            background: rgba(22, 163, 74, 0.20);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 22px;
            font-weight: 800;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 600;
            color: var(--dark) !important;
            margin: 0 8px;
            position: relative;
            transition: color 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary) !important;
        }

        .btn-login {
            background: var(--gradient);
            color: white !important;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
        }

        .fixit-btn {
            background: black !important;
            color: #ff6b35 !important;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            border: 2px solid #ff6b35;
        }

        .fixit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(255, 107, 53, 0.4);
            background: #ff6b35 !important;
            color: black !important;
        }

        .navbar-toggler {
            border: none;
            box-shadow: none !important;
        }

        .hero-section,
        .py-5 {
            position: relative;
            z-index: 1;
        }

        .hero-section {
            padding: 135px 0 90px;
            background:
                linear-gradient(135deg, rgba(8, 47, 73, 0.12), rgba(255, 255, 255, 0.02)),
                var(--gradient);
            color: #fff;
            overflow: hidden;
        }

        .hero-section::before,
        .hero-section::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
        }

        .hero-section::before {
            width: 240px;
            height: 240px;
            top: -40px;
            right: 10%;
        }

        .hero-section::after {
            width: 140px;
            height: 140px;
            bottom: 20px;
            left: 8%;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.18);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 18px;
            backdrop-filter: blur(10px);
        }

        .hero-eyebrow i {
            color: #fde68a;
        }

        .hero-title {
            font-size: clamp(2.4rem, 4vw, 4.2rem);
            line-height: 1.06;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 18px;
        }

        .hero-description {
            max-width: 760px;
            margin: 0 auto;
            font-size: 1.06rem;
            color: rgba(255, 255, 255, 0.92);
        }

        .hero-card,
        .form-card {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.58);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.10);
            backdrop-filter: blur(16px);
        }

        .hero-card {
            color: var(--dark);
            margin-top: -62px;
            overflow: hidden;
        }

        .hero-card .card-body {
            position: relative;
        }

        .hero-card .card-body::after {
            content: "";
            position: absolute;
            inset: auto -80px -90px auto;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.12), rgba(22, 163, 74, 0.10));
            pointer-events: none;
        }

        .section-title {
            font-size: 1.9rem;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.03em;
        }

        .section-subtitle {
            color: var(--muted);
            margin-bottom: 0;
            max-width: 620px;
        }

        .feature-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }

        .feature-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: #fff;
            border: 1px solid rgba(217, 227, 240, 0.95);
            color: #1e293b;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .feature-pill i {
            color: var(--primary);
        }

        .hero-side {
            display: flex;
            justify-content: end;
            position: relative;
            z-index: 1;
        }

        .hero-stats {
            width: min(100%, 290px);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(245, 249, 255, 0.94));
            border: 1px solid rgba(217, 227, 240, 0.85);
            border-radius: 24px;
            padding: 18px;
            box-shadow: 0 20px 40px rgba(13, 110, 253, 0.10);
        }

        .hero-stats-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .hero-stats-icon {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 1.2rem;
        }

        .hero-stats small,
        .section-note,
        .field-hint {
            color: var(--muted);
        }

        .stat-list {
            display: grid;
            gap: 12px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 14px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid rgba(217, 227, 240, 0.7);
        }

        .stat-item strong {
            display: block;
            font-size: 0.98rem;
        }

        .stat-item span {
            display: block;
            font-size: 0.84rem;
            color: var(--muted);
        }

        .stat-dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            box-shadow: 0 0 0 6px rgba(13, 110, 253, 0.10);
            flex-shrink: 0;
        }

        .modern-alert {
            border: 0;
            border-left: 4px solid transparent;
            border-radius: 18px;
            padding: 16px 18px;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
        }

        .modern-alert.alert-success {
            background: rgba(22, 163, 74, 0.10);
            border-left-color: var(--accent);
        }

        .modern-alert.alert-danger {
            background: rgba(239, 68, 68, 0.10);
            border-left-color: #ef4444;
        }

        .form-card {
            overflow: hidden;
        }

        .form-card .card-body {
            padding: 30px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
        }

        .section-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.14), rgba(79, 70, 229, 0.12));
            color: var(--primary);
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .section-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .mode-box {
            border: 1px solid rgba(217, 227, 240, 0.9);
            border-radius: 24px;
            padding: 20px 22px;
            background: linear-gradient(135deg, rgba(248, 251, 255, 0.96), rgba(243, 255, 248, 0.92));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
        }

        .mode-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 700;
            border: 1px solid rgba(217, 227, 240, 0.9);
            margin-bottom: 12px;
        }

        .form-check-input {
            cursor: pointer;
        }

        .form-switch .form-check-input {
            width: 3.4rem;
            height: 1.85rem;
            border: 0;
            background-color: rgba(100, 116, 139, 0.35);
            box-shadow: inset 0 2px 8px rgba(15, 23, 42, 0.12);
        }

        .form-switch .form-check-input:checked {
            background-color: var(--primary);
        }

        .form-switch .form-check-label {
            font-weight: 700;
            color: var(--dark);
        }

        .form-label {
            font-weight: 700;
            font-size: 0.86rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 10px;
        }

        .input-shell {
            position: relative;
        }

        .input-shell i {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.95rem;
            pointer-events: none;
            z-index: 2;
        }

        .input-shell.textarea-shell i {
            top: 19px;
            transform: none;
        }

        .form-control,
        .form-select {
            min-height: 54px;
            border-radius: 16px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.92);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
        }

        .input-shell .form-control,
        .input-shell .form-select {
            padding-left: 46px;
        }

        textarea.form-control {
            min-height: 132px;
            padding-top: 16px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(13, 110, 253, 0.58);
            box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.12);
            transform: translateY(-1px);
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--bootstrap-5 .select2-selection {
            min-height: 54px;
            border-radius: 16px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.92);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .select2-container--bootstrap-5 .select2-selection--single {
            padding-left: 34px;
            padding-top: 0.45rem;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--dark);
            line-height: 2.2;
            padding-left: 0;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8;
        }

        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: rgba(13, 110, 253, 0.58);
            box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.12);
            transform: translateY(-1px);
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border: 1px solid rgba(13, 110, 253, 0.18);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 24px 40px rgba(15, 23, 42, 0.12);
        }

        .select2-container--bootstrap-5 .select2-search {
            padding: 12px;
            background: #f8fbff;
        }

        .select2-container--bootstrap-5 .select2-search__field {
            min-height: 44px;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 10px 12px;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background: linear-gradient(135deg, var(--primary), #4f46e5);
        }

        .highlight-box {
            padding: 14px 16px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.07), rgba(79, 70, 229, 0.06));
            border: 1px dashed rgba(13, 110, 253, 0.25);
        }

        .check-card {
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 16px;
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(217, 227, 240, 0.8);
        }

        .submit-btn {
            min-width: 245px;
            min-height: 58px;
            border-radius: 999px;
            font-weight: 800;
            letter-spacing: 0.02em;
            background: var(--gradient);
            border: 0;
            box-shadow: 0 18px 35px rgba(13, 110, 253, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .submit-btn:hover {
            opacity: 0.98;
            transform: translateY(-2px);
            box-shadow: 0 24px 40px rgba(13, 110, 253, 0.25);
        }

        .required-star {
            color: #dc3545;
        }

        @media (max-width: 991px) {
            .hero-card {
                margin-top: -48px;
            }

            .hero-side {
                justify-content: start;
            }
        }

        @media (max-width: 767px) {
            .hero-section {
                padding: 120px 0 78px;
            }

            .form-card .card-body,
            .hero-card .card-body {
                padding: 22px;
            }

            .section-header {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<?php include BASE_PATH . '/includes/menu.php'; ?>

<section class="hero-section">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-9">
                <div class="hero-eyebrow">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Smart Doctor Registration</span>
                </div>
                <h1 class="hero-title">Add Doctor Profile With A Cleaner, Modern Experience</h1>
                <p class="hero-description">Doctor ki basic details, clinic ya hospital mode, specialization aur contact information ek premium layout me fill karein. Submit par data `entities` aur `doctors` table me save hoga.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="card hero-card mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <h2 class="section-title">Doctor Registration Form</h2>
                        <p class="section-subtitle">Hospital doctor aur personal clinic dono mode support karta hai, with better sectioning and cleaner visual flow.</p>
                        <div class="feature-pills">
                            <span class="feature-pill"><i class="fa-solid fa-building-user"></i> Hospital / Clinic Mode</span>
                            <span class="feature-pill"><i class="fa-solid fa-shield-heart"></i> Approval Ready</span>
                            <span class="feature-pill"><i class="fa-solid fa-layer-group"></i> Better Form Hierarchy</span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-side">
                            <div class="hero-stats">
                                <div class="hero-stats-header">
                                    <div class="hero-stats-icon">
                                        <i class="fa-solid fa-user-doctor"></i>
                                    </div>
                                    <div>
                                        <strong>Submission Overview</strong>
                                        <small>Quick checklist before submit</small>
                                    </div>
                                </div>
                                <div class="stat-list">
                                    <div class="stat-item">
                                        <div>
                                            <strong>City & Practice</strong>
                                            <span>Location fields complete rakhein</span>
                                        </div>
                                        <span class="stat-dot"></span>
                                    </div>
                                    <div class="stat-item">
                                        <div>
                                            <strong>Doctor Profile</strong>
                                            <span>Name, gender, specialization add karein</span>
                                        </div>
                                        <span class="stat-dot"></span>
                                    </div>
                                    <div class="stat-item">
                                        <div>
                                            <strong>Clinical Info</strong>
                                            <span>Optional details se listing strong hoti hai</span>
                                        </div>
                                        <span class="stat-dot"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($success_msg !== ''): ?>
            <div class="alert alert-success modern-alert"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if ($error_msg !== ''): ?>
            <div class="alert alert-danger modern-alert"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="doctor_type" id="doctor_type" value="<?php echo htmlspecialchars($form_data['doctor_type']); ?>">

            <div class="card form-card mb-4">
                <div class="card-body">
                    <div class="mode-box d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <div class="mode-chip">
                                <i class="fa-solid fa-sliders"></i>
                                <span>Practice Switch</span>
                            </div>
                            <h3 class="h5 mb-1">Practice Mode</h3>
                            <p class="mb-0 text-muted">Toggle on karein agar doctor personal clinic chalata hai, warna hospital doctor mode active rahega.</p>
                        </div>
                        <div class="form-check form-switch fs-5 mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="personal_clinic_toggle" <?php echo $form_data['doctor_type'] === '2' ? 'checked' : ''; ?>>
                            <label class="form-check-label ms-2" for="personal_clinic_toggle">Personal Clinic</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card form-card h-100">
                        <div class="card-body">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <h3>Location & Workplace</h3>
                                    <div class="section-note">City, hospital aur clinic details yahan manage hongi.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">City <span class="required-star">*</span></label>
                                <div class="input-shell">
                                    <i class="fa-solid fa-city"></i>
                                    <select class="form-select" id="cityId" name="city_id" required>
                                        <option value="">Select City</option>
                                        <?php if ($cities_result): ?>
                                            <?php while ($city = mysqli_fetch_assoc($cities_result)): ?>
                                                <option value="<?php echo $city['city_id']; ?>" <?php echo $form_data['city_id'] == $city['city_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($city['city_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="field-hint mt-2">Doctor ki listing correct city ke andar show hogi.</div>
                            </div>

                            <div class="mb-3" id="hospital_section">
                                <label class="form-label">Hospital <span class="required-star">*</span></label>
                                <div class="input-shell">
                                    <i class="fa-solid fa-hospital"></i>
                                    <select class="form-select" id="hospitalId" name="hospital_id">
                                        <option value="">Select Hospital</option>
                                        <?php if ($hospitals_result): ?>
                                            <?php while ($hospital = mysqli_fetch_assoc($hospitals_result)): ?>
                                                <option value="<?php echo $hospital['hospital_id']; ?>" data-city="<?php echo $hospital['city_id']; ?>" <?php echo $form_data['hospital_id'] == $hospital['hospital_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($hospital['hospital_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div id="clinic_section" style="display:none;">
                                <div class="mb-3">
                                    <label class="form-label">Clinic Name <span class="required-star">*</span></label>
                                    <div class="input-shell">
                                        <i class="fa-solid fa-house-chimney-medical"></i>
                                        <input type="text" class="form-control" id="clinicName" name="clinic_name" value="<?php echo htmlspecialchars($form_data['clinic_name']); ?>" placeholder="Enter clinic name">
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Clinic Address <span class="required-star">*</span></label>
                                    <div class="input-shell textarea-shell">
                                        <i class="fa-solid fa-map-location-dot"></i>
                                        <textarea class="form-control" id="clinicAddress" name="clinic_address" placeholder="Enter clinic address"><?php echo htmlspecialchars($form_data['clinic_address']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card form-card h-100">
                        <div class="card-body">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fa-solid fa-id-card"></i>
                                </div>
                                <div>
                                    <h3>Personal Details</h3>
                                    <div class="section-note">Doctor profile ki core information yahan add karein.</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Doctor Name <span class="required-star">*</span></label>
                                    <div class="input-shell">
                                        <i class="fa-solid fa-user-doctor"></i>
                                        <input type="text" class="form-control" name="doctor_name" value="<?php echo htmlspecialchars($form_data['doctor_name']); ?>" placeholder="Dr. John Doe" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Specialization <span class="required-star">*</span></label>
                                    <div class="highlight-box">
                                        <div class="input-shell">
                                            <i class="fa-solid fa-stethoscope"></i>
                                            <select class="form-select" name="specialization" id="specialization">
                                                <option value="">Select Specialization</option>
                                                <?php foreach ($categories_data as $category): ?>
                                                    <optgroup label="<?php echo htmlspecialchars($category['cat_name']); ?>">
                                                        <?php foreach ($category['types'] as $type): ?>
                                                            <option value="<?php echo $type['dr_cat_type_id']; ?>" <?php echo $form_data['specialization'] == $type['dr_cat_type_id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($type['type']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="input-shell mt-2">
                                            <i class="fa-solid fa-pen"></i>
                                            <input type="text" class="form-control" name="specialization_txt" id="specialization_txt" value="<?php echo htmlspecialchars($form_data['specialization_txt']); ?>" placeholder="Enter specialization" style="display:none;">
                                        </div>
                                        <div class="check-card mt-2">
                                            <input class="form-check-input mt-0" type="checkbox" value="1" id="if_not_available" name="if_not_available" <?php echo $form_data['if_not_available'] === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label text-danger fw-semibold" for="if_not_available">If specialization list me available nahi hai</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Experience (Years)</label>
                                    <div class="input-shell">
                                        <i class="fa-regular fa-clock"></i>
                                        <input type="number" class="form-control" name="experience_years" min="0" value="<?php echo htmlspecialchars($form_data['experience_years']); ?>" placeholder="e.g. 5">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <div class="input-shell">
                                        <i class="fa-solid fa-phone-volume"></i>
                                        <input type="text" class="form-control" name="doctor_phone" value="<?php echo htmlspecialchars($form_data['doctor_phone']); ?>" placeholder="Contact number">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Short Detail</label>
                                    <div class="input-shell">
                                        <i class="fa-solid fa-award"></i>
                                        <input type="text" class="form-control" name="short_detail" value="<?php echo htmlspecialchars($form_data['short_detail']); ?>" placeholder="MBBS / FCPS / etc">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Other</label>
                                    <div class="input-shell">
                                        <i class="fa-solid fa-notes-medical"></i>
                                        <input type="text" class="form-control" name="other" value="<?php echo htmlspecialchars($form_data['other']); ?>" placeholder="Incharge / DHQ etc">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-shell">
                                        <i class="fa-solid fa-envelope"></i>
                                        <input type="email" class="form-control" name="doctor_email" value="<?php echo htmlspecialchars($form_data['doctor_email']); ?>" placeholder="doctor@example.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender <span class="required-star">*</span></label>
                                    <div class="input-shell">
                                        <i class="fa-solid fa-venus-mars"></i>
                                        <select class="form-select" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?php echo $form_data['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo $form_data['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo $form_data['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Clinical Info Detail</label>
                                    <div class="input-shell textarea-shell">
                                        <i class="fa-solid fa-file-waveform"></i>
                                        <textarea class="form-control" name="static_clinical_info" placeholder="Doctor ke clinical info details"><?php echo htmlspecialchars($form_data['static_clinical_info']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center py-5">
                <button type="submit" class="btn btn-primary submit-btn">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Save Doctor Details
                </button>
            </div>
        </form>
    </div>
</section>

<?php include BASE_PATH . '/includes/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('personal_clinic_toggle');
    const hospitalSection = document.getElementById('hospital_section');
    const clinicSection = document.getElementById('clinic_section');
    const hospitalId = document.getElementById('hospitalId');
    const clinicName = document.getElementById('clinicName');
    const clinicAddress = document.getElementById('clinicAddress');
    const doctorTypeInput = document.getElementById('doctor_type');
    const citySelect = document.getElementById('cityId');
    const specializationCheckbox = document.getElementById('if_not_available');
    const specializationSelect = document.getElementById('specialization');
    const specializationText = document.getElementById('specialization_txt');

    function updateDoctorMode() {
        if (toggle.checked) {
            doctorTypeInput.value = '2';
            hospitalSection.style.display = 'none';
            clinicSection.style.display = 'block';
            hospitalId.required = false;
            clinicName.required = true;
            clinicAddress.required = true;
        } else {
            doctorTypeInput.value = '1';
            hospitalSection.style.display = 'block';
            clinicSection.style.display = 'none';
            hospitalId.required = true;
            clinicName.required = false;
            clinicAddress.required = false;
        }
    }

    function filterHospitals() {
        const selectedCity = citySelect.value;
        const options = hospitalId.querySelectorAll('option');

        options.forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = selectedCity !== '' && option.dataset.city !== selectedCity;
        });

        if (hospitalId.selectedOptions.length && hospitalId.selectedOptions[0].hidden) {
            hospitalId.value = '';
        }
    }

    function toggleSpecializationFields() {
        if (specializationCheckbox.checked) {
            specializationSelect.style.display = 'none';
            specializationSelect.required = false;
            specializationText.style.display = 'block';
            specializationText.required = true;
        } else {
            specializationSelect.style.display = 'block';
            specializationSelect.required = true;
            specializationText.style.display = 'none';
            specializationText.required = false;
        }
    }

    toggle.addEventListener('change', updateDoctorMode);
    citySelect.addEventListener('change', filterHospitals);
    specializationCheckbox.addEventListener('change', toggleSpecializationFields);

    updateDoctorMode();
    filterHospitals();
    toggleSpecializationFields();
});

$(function () {
    $('#cityId').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search city...',
        allowClear: true,
        width: '100%'
    });

    $('#cityId').on('change.select2', function () {
        this.dispatchEvent(new Event('change', { bubbles: true }));
    });
});
</script>
</body>
</html>
