<?php

$ini = parse_ini_file('../../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$playground_id = $input[0]['playground_id'];
$element_id = $input[0]['element_id'];

$apiKey = htmlspecialchars($_GET['API_KEY']);

if (isset($playground_id) && isset($element_id)) {
    header('Content-type: application/json');

    if ($apiKey == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT `id` FROM `playground_elements` WHERE `playground_id` = '$playground_id'
                                                                                       AND `element_id`    = '$element_id'");
        $row = mysqli_fetch_assoc($result);
        $playground_element_id = $row['id'];

        if (empty($playground_element_id)) {
            mysqli_query($link, "INSERT INTO `playground_elements` (`id`, `playground_id`, `element_id`) VALUES ('NULL', '$playground_id', '$element_id')");

            $result = mysqli_query($link, "SELECT `id` FROM `playground_elements` WHERE `playground_id` = '$playground_id'
                                                                                           AND `element_id`    = '$element_id'");
            $row = mysqli_fetch_assoc($result);
            $playground_element_id = $row['id'];

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
                'error' => "Повторное добавление площадки $playground_id c элементом $element_id"
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