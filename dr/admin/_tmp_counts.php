<?php
require __DIR__ . '/../config.php';
$qs = [
    'pending_doctors' => "SELECT COUNT(*) as total FROM doctors WHERE approve = 0",
    'pending_hospitals' => "SELECT COUNT(*) as total FROM hospitals WHERE approve = 0",
    'pending_labs' => "SELECT COUNT(*) as total FROM laboratories WHERE approve = 0",
    'pending_bb' => "SELECT COUNT(*) as total FROM blood_bank WHERE approve = 0",
    'feedback' => "SELECT COUNT(*) as total FROM feedback WHERE status = 1",
    'recycle' => "SELECT COUNT(*) as total FROM users WHERE status = 0",
    'doctors' => "SELECT COUNT(*) as total FROM doctors d LEFT JOIN users u ON u.user_id = d.user_id WHERE u.status = 1 AND d.approve = 1",
    'recent_fb' => "SELECT commenter_name, comment, created_at FROM feedback ORDER BY created_at DESC LIMIT 3",
];
foreach ($qs as $k => $sql) {
    $r = mysqli_query($con, $sql);
    echo "$k: ";
    if (!$r) { echo 'ERR '.mysqli_error($con)."\n"; continue; }
    if (stripos($sql, 'LIMIT') !== false) {
        while ($row = mysqli_fetch_assoc($r)) print_r($row);
    } else {
        $row = mysqli_fetch_assoc($r);
        echo $row['total']."\n";
    }
}
