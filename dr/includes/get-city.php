<?php
session_start();

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['lat'], $data['lon'])) {
    exit("Location not received");
}

$lat = $data['lat'];
$lon = $data['lon'];

// ZOOM=10 ko badal kar ZOOM=13 kar diya hai taake City ka naam mile (Tehsil ka nahi)
$url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lon&zoom=13&addressdetails=1&accept-language=en";

$opts = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: SehatMand/1.0 (contact@sehatmand.com)\r\n"
    ]
];

$context = stream_context_create($opts);
$response = file_get_contents($url, false, $context);

$result = json_decode($response, true);
$address = $result['address'] ?? [];

// City dhoondhne ka behtar tareeqa
$city =
    $address['city']
    ?? $address['town']
    ?? $address['city_district'] // Yeh naya add kiya hai
    ?? $address['municipality']
    ?? $address['county']
    ?? $address['state_district']
    ?? $address['village']
    ?? '';

$_SESSION['city'] = $city;

// Debug: Log session setting
error_log("City set in session: " . $city);
error_log("Session ID: " . session_id());

echo $city;