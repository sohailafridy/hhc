<?php
require __DIR__ . '/../config.php';
$tables = ['users','doctors','hospitals','laboratories','blood_bank','feedback','cities'];
foreach ($tables as $t) {
    echo "===== $t =====\n";
    $r = mysqli_query($con, "SHOW COLUMNS FROM `$t`");
    if (!$r) {
        echo mysqli_error($con) . "\n";
        continue;
    }
    while ($row = mysqli_fetch_assoc($r)) {
        echo $row['Field'] . ' ' . $row['Type'] . "\n";
    }
}
