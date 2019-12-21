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

$playground_id = htmlspecialchars($_GET['id']);
$apiKey = htmlspecialchars($_GET['API_KEY']);

if (isset($playground_id)) {
    header('Content-type: application/json');

    if ($apiKey == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT * FROM `playgrounds` WHERE `id` = '$playground_id'");
        $row = mysqli_fetch_assoc($result);
        $address = $row['address'];
        $latitude = $row['latitude'];
        $longitude = $row['longitude'];
        $size = $row['size'];
        $is_illuminated = $row['is_illuminated'];
        $is_fenced = $row['is_fenced'];
        $district_id = $row['district_id'];

        if (isset($address)) {
            mysqli_query($link, "DELETE FROM `playgrounds` WHERE `id` = '$playground_id'");

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
            $resultMessage = "Отсутствует id, равный $playground_id";
        }
    } else {
        $resultMessage = ($apiKey != '') ? 'Неверный ключ' : 'Не хватает ключа';
        $resultMessage .= '. Обратитесь к администратору: ilya@sidorchik.ru';
    }
}

next:

array_push($content, [
    'success' => $successMessage,
    $resultField => $resultMessage
]);

if ($content) {
    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo $json_str;
}