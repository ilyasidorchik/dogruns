<?php

$ini = parse_ini_file('../../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$name = $input[0]['name'];
$area_id = $input[0]['area_id'];

$apiKey = htmlspecialchars($_GET['API_KEY']);

if (isset($name)) {
    header('Content-type: application/json');

    if ($apiKey == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT `id` FROM `districts` WHERE `name` = '$name'");
        $row = mysqli_fetch_assoc($result);
        $district_id = $row['id'];

        if (empty($district_id)) {
            mysqli_query($link, "INSERT INTO `districts` (`id`, `name`, `area_id`) VALUES ('NULL', '$name', '$area_id')");

            $result = mysqli_query($link, "SELECT `id` FROM `districts` WHERE `name` = '$name'");
            $row = mysqli_fetch_assoc($result);
            $district_id = $row['id'];

            array_push($content, [
                'success' => true,
                'result' => [
                    'id' => $district_id,
                    'name' => $name
                ]
            ]);
        } else {
            array_push($content, [
                'success' => false,
                'error' => "Повторное добавление $name"
            ]);
        }
    } else {
        $errorMessage = ($apiKey != '') ? 'Неверный ключ' : 'Не хватает ключа';
        $errorMessage .= '. Обратитесь к администратору: ilya@sidorchik.ru';

        array_push($content, [
            'success' => false,
            'error' => $errorMessage
        ]);
    }

    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    echo $json_str;
}