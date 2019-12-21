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
    goto output;
}

mysqli_set_charset($link, 'utf8');

$playground_id = htmlspecialchars($_GET['id']);
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$new_address = $input[0]['address'];
$new_latitude = $input[0]['latitude'];
$new_longitude = $input[0]['longitude'];
$new_size = $input[0]['size'];
$new_is_illuminated = $input[0]['is_illuminated'];
$new_is_fenced = $input[0]['is_fenced'];
$new_district_id = $input[0]['district_id'];

// Validation
$new_address = str_replace("'", "", $new_address);

$api_key = htmlspecialchars($_GET['API_KEY']);

if (isset($playground_id)) {
    if ($api_key == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT * FROM `playgrounds` WHERE `id` = '$playground_id'");
        $row = mysqli_fetch_assoc($result);
        $old_address = $row['address'];
        $old_latitude = $row['latitude'];
        $old_longitude = $row['longitude'];
        $old_size = $row['size'];
        $old_is_illuminated = $row['is_illuminated'];
        $old_is_fenced = $row['is_fenced'];
        $old_district_id = $row['district_id'];

        if (isset($old_address)) {
            if ($old_address != $new_address
                || $old_latitude != $new_latitude
                || $old_longitude != $new_longitude
                || $old_size != $new_size
                || $old_is_illuminated != $new_is_illuminated
                || $old_is_fenced != $new_is_fenced
                || $old_district_id != $new_district_id) {

                mysqli_query($link, "UPDATE `playgrounds` SET `address` = '$new_address',
                                                                     `latitude` = '$new_latitude',
                                                                     `longitude` = '$new_longitude',
                                                                     `size` = '$new_size',
                                                                     `is_illuminated` = '$new_is_illuminated',
                                                                     `is_fenced` = '$new_is_fenced',
                                                                     `district_id` = '$new_district_id'
                                                                 WHERE `id` = '$playground_id'");

                $successMessage = true;
                $resultField = 'result';
                $resultMessage = [
                    'id' => $playground_id,
                    'old' => [
                        'address' => $old_address,
                        'latitude' => $old_latitude,
                        'longitude' => $old_longitude,
                        'size' => $old_size,
                        'is_illuminated' => $old_is_illuminated,
                        'is_fenced' => $old_is_fenced,
                        'district_id' => $old_district_id
                    ],
                    'new' => [
                        'address' => $new_address,
                        'latitude' => $new_latitude,
                        'longitude' => $new_longitude,
                        'size' => $new_size,
                        'is_illuminated' => $new_is_illuminated,
                        'is_fenced' => $new_is_fenced,
                        'district_id' => $new_district_id
                    ]
                ];
            } else {
                $resultMessage = "В таблице уже есть указанная информация";
            }
        } else {
            $resultMessage = "Отсутствует id, равный $playground_id";
        }
    } else {
        $resultMessage = ($api_key != '') ? 'Неверный ключ' : 'Не хватает ключа';
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