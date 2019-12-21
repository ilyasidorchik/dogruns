<?php

header('Content-type: application/json');

$ini = parse_ini_file('../../../../app.ini', true);
$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]);

$content = [];
$successMessage = false;
$resultField = 'error';
$resultMessage = '';

if (mysqli_connect_errno()) {
    $resultMessage = 'Соединение с базой данных не удалось';
    goto next;
}

mysqli_set_charset($link, 'utf8');

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$address = $input[0]['address'];
$latitude = $input[0]['latitude'];
$longitude = $input[0]['longitude'];
$size = $input[0]['size'];
$is_illuminated = $input[0]['is_illuminated'];
$is_fenced = $input[0]['is_fenced'];
$district_id = $input[0]['district_id'];

// Validation
$address = str_replace("'", "", $address);

$apiKey = htmlspecialchars($_GET['API_KEY']);

if (isset($address) && isset($latitude) && isset($longitude) && isset($size) && isset($is_illuminated) && isset($is_fenced) && isset($district_id)) {
    if ($apiKey == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT `id` FROM `playgrounds` WHERE `address` = '$address'");
        $row = mysqli_fetch_assoc($result);
        $playground_id = $row['id'];

        if (empty($playground_id)) {
            mysqli_query($link, "INSERT INTO `playgrounds` (`id`, `address`, `latitude`, `longitude`, `size`, `is_illuminated`, `is_fenced`, `district_id`)
                                                           VALUES ('NULL', '$address', '$latitude', '$longitude', '$size', '$is_illuminated', '$is_fenced', '$district_id')");
            $result = mysqli_query($link, "SELECT `id` FROM `playgrounds` WHERE `address` = '$address'");
            $row = mysqli_fetch_assoc($result);
            $playground_id = $row['id'];

            $successMessage = true;
            $resultField = 'result';
            $resultMessage = [
                'id' => $playground_id,
                'address' => $address,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'size' => $size,
                'is_illuminated' => $is_illuminated,
                'is_fenced' => $is_fenced,
                'district_id' => $district_id
            ];
        } else {
            $resultMessage = "Повторное добавление $address";
        }
    } else {
        $resultMessage = ($apiKey != '') ? 'Неверный ключ' : 'Не хватает ключа';
        $resultMessage .= '. Обратитесь к администратору: ilya@sidorchik.ru';
    }
}

output:

array_push($content, [
    'success' => $successMessage,
    $resultField => $resultMessage
]);

if ($content) {
    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo $json_str;
}