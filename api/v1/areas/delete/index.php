<?php

$ini = parse_ini_file('../../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$area_id = htmlspecialchars($_GET['id']);
$apiKey = htmlspecialchars($_GET['API_KEY']);

if (isset($area_id)) {
    header('Content-type: application/json');

    if ($apiKey == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT `name` FROM areas WHERE id = '$area_id'");
        $row = mysqli_fetch_assoc($result);
        $name = $row['name'];

        if (isset($name)) {
            mysqli_query($link, "DELETE FROM `areas` WHERE id = '$area_id'");

            array_push($content, [
                'success' => true,
                'result' => [
                    'id' => $area_id,
                    'name' => $name
                ]
            ]);
        } else {
            array_push($content, [
                'success' => false,
                'error' => "Отсутствует id, равный $area_id"
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