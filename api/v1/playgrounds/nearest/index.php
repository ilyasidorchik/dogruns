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
$content_geocoder = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?key=$MAP_API_KEY&address=$address_googled");
$json_geocoder = json_decode($content_geocoder, true);
$latitudeFrom = $json_geocoder["results"][0]["geometry"]["location"]["lat"];
$longitudeFrom = $json_geocoder["results"][0]["geometry"]["location"]["lng"];

$min = 100000000000;
$closest_playground_id = '';
$closest_address = '';
$closest_latitude = '';
$closest_longitude = '';
$closest_size = '';
$closest_is_illuminated = '';
$closest_is_fenced = '';
$closest_district_id = '';

$result = mysqli_query($link, "SELECT * FROM `playgrounds`");

$lat_from = 0;
$lon_from = 0;
$lat_to = 0;
$lon_to = 0;
$lat_delta = 0;
$lon_delta = 0;
$angle = 0;
$distance = 0;

while ($row = mysqli_fetch_assoc($result)) {
    // Haversine formula
    $lat_from = deg2rad($latitudeFrom);
    $lon_from = deg2rad($longitudeFrom);
    $lat_to = deg2rad($row['latitude']);
    $lon_to = deg2rad($row['longitude']);
    $lat_delta = $lat_to - $lat_from;
    $lon_delta = $lon_to - $lon_from;
    $angle = 2 * asin(sqrt(pow(sin($lat_delta / 2), 2) + cos($lat_from) * cos($lat_to) * pow(sin($lon_delta / 2), 2)));
    $distance = $angle * 6371000;

    if ($distance < $min) {
        $min = $distance;

        $closest_playground_id = $row['id'];
        $closest_address = $row['address'];
        $closest_latitude = $row['latitude'];
        $closest_longitude = $row['longitude'];
        $closest_size = $row['size'];
        $closest_is_illuminated = $row['is_illuminated'];
        $closest_is_fenced = $row['is_fenced'];
        $closest_district_id = $row['district_id'];
    }
}

array_push($content, [
    'id' => $closest_playground_id,
    'address' => $closest_address,
    'latitude' => $closest_latitude,
    'longitude' => $closest_longitude,
    'size' => $closest_size,
    'is_illuminated' => $closest_is_illuminated,
    'is_fenced' => $closest_is_fenced,
    'district_id' => $closest_district_id,
]);

if ($content) {
    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    echo $json_str;
}