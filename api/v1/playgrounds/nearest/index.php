<?php

header('Content-type: application/json');

$ini = parse_ini_file('../../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$MAP_API_KEY = $ini[api][map_key];

$content = [];

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$address = $input[0]['address'];
$address_googled = 'Россия, Москва, ' . $address;
$address_googled = urlencode($address_googled);

$min = 100000000000;
$closest_playground_id = '';
$closest_address = '';
$closest_latitude = '';
$closest_longitude = '';
$closest_size = '';
$closest_is_illuminated = '';
$closest_is_fenced = '';
$closest_district_id = '';
$closest_duration = '';

$result = mysqli_query($link, "SELECT * FROM `playgrounds`");

$lat_from = 0;
$lon_from = 0;
$lat_to = 0;
$lon_to = 0;
$lat_delta = 0;
$lon_delta = 0;
$angle = 0;
$i = 0;


while ($row = mysqli_fetch_assoc($result)) {
    $content_geocoder = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?key=$MAP_API_KEY&origins=$address_googled&destinations=$row[latitude],$row[longitude]&travelMode=WALKING");
    $json_geocoder = json_decode($content_geocoder, true);
    $duration = $json_geocoder["rows"][0]["elements"][0]["duration"]["value"];

    if ($duration < $min) {
        $min = $duration;

        $closest_playground_id = $row['id'];
        $closest_address = $row['address'];
        $closest_latitude = $row['latitude'];
        $closest_longitude = $row['longitude'];
        $closest_size = $row['size'];
        $closest_is_illuminated = $row['is_illuminated'];
        $closest_is_fenced = $row['is_fenced'];
        $closest_district_id = $row['district_id'];
        $closest_duration = $json_geocoder["rows"][0]["elements"][0]["duration"]["text"];
    }
}

array_push($content, [
    'result' => [
        'duration' => $closest_duration,
        'playground' => [
            'id' => $closest_playground_id,
            'address' => $closest_address,
            'latitude' => $closest_latitude,
            'longitude' => $closest_longitude,
            'size' => $closest_size,
            'is_illuminated' => $closest_is_illuminated,
            'is_fenced' => $closest_is_fenced,
            'district_id' => $closest_district_id
        ]
    ],
    'status' => 'OK'
]);

if ($content) {
    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    echo $json_str;
}