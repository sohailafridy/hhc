<?php

    
    $hospital_address = "behram medical center Kachehri chowk kohat";
    $city_name = "Kohat";


    $address = urlencode($hospital_address . ', ' . $city_name . ', Pakistan');
    $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";
    $response = file_get_contents($url, false, stream_context_create([
        'http' => ['header' => "User-Agent: SehatNexa/1.0\r\n"]
    ]));
    $data = json_decode($response, true);

    if (!empty($data[0])) {
        $latitude  = $data[0]['lat'];  // e.g. "33.5869"
        $longitude = $data[0]['lon'];  // e.g. "71.4414"
    }
    


    echo "Latitude ".$latitude." & Longitude ".$longitude;


?>