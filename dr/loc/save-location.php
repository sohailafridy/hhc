<?php


$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['lat'], $data['lon'])) {
    exit("Location not received");
}

$lat = $data['lat'];
$lon = $data['lon'];

$url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lon&zoom=10&addressdetails=1&accept-language=en";

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

$city =
    $address['city']
    ?? $address['town']
    ?? $address['municipality']
    ?? $address['county']
    ?? $address['state_district']
    ?? $address['village']
    ?? '';

$_SESSION['city'] = $city;

// echo $city;


