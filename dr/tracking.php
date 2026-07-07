<?php

// User IP
$ip = $_SERVER['REMOTE_ADDR'];


$page = $_SERVER['REQUEST_URI'];

$referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';

$user_agent = $_SERVER['HTTP_USER_AGENT'];



// Location API
$api_url = "http://ip-api.com/json/$ip";
$response = @file_get_contents($api_url);
$data = json_decode($response, true);

if ($data && $data['status'] == 'success') {
    $country = $data['country'];
    $city    = $data['city'];
    $region  = $data['regionName'];
} else {
    $country = $city = $region = 'Unknown';
}

// Check IP exists
$check = "SELECT id FROM history WHERE ip_address='$ip' LIMIT 1";
$result = mysqli_query($con, $check);

// 🔹 INSERT (first visit)
    $insert = "INSERT INTO history 
    (ip_address, country, city, region, visit, visit_date,referrer,page_url,browser)
    VALUES 
    ('$ip', '$country', '$city', '$region', 1, NOW(), '$referrer','$page','$user_agent')";

    mysqli_query($con, $insert);


// if (mysqli_num_rows($result) == 0) {

//     // 🔹 INSERT (first visit)
//     $insert = "INSERT INTO history 
//     (ip_address, country, city, region, visit, visit_date,referrer,page_url,browser)
//     VALUES 
//     ('$ip', '$country', '$city', '$region', 1, NOW(), '$referrer','$page','$user_agent')";

//     mysqli_query($con, $insert);

// } else {

//     // 🔹 UPDATE (repeat visit)
//     $update = "UPDATE history 
//     SET visit = visit + 1, visit_date = NOW()
//     WHERE ip_address = '$ip'";

//     mysqli_query($con, $update);
// }
?>
