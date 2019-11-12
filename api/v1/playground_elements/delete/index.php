<?php

$ini = parse_ini_file('../../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$playground_element_id = htmlspecialchars($_GET['id']);

$apiKey = htmlspecialchars($_GET['API_KEY']);

if (isset($playground_element_id)) {
    header('Content-type: application/json');

    if ($apiKey == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT * FROM `playground_elements` WHERE `id` = '$playground_element_id'");
        $row = mysqli_fetch_assoc($result);
        $playground_id = $row['playground_id'];
        $element_id = $row['element_id'];

        if (isset($playground_id)) {
            mysqli_query($link, "DELETE FROM `playground_elements` WHERE `id` = '$playground_element_id'");

            array_push($content, [
                'success' => true,
                'result' => [
                    'id' => $playground_element_id,
                    'playground_id' => $playground_id,
                    'element_id' => $element_id
                ]
            ]);
        } else {
            array_push($content, [
                'success' => false,
                'error' => "Отсутствует id, равный $playground_element_id"
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