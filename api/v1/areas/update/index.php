<?php

$ini = parse_ini_file('../../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$area_id = htmlspecialchars($_GET['id']);
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$new_name = $input[0]['name'];

$api_key = htmlspecialchars($_GET['API_KEY']);

if (isset($area_id)) {
    header('Content-type: application/json');

    if ($api_key == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT `name` FROM `areas` WHERE `id` = '$area_id'");
        $row = mysqli_fetch_assoc($result);
        $old_name = $row['name'];

        if (isset($old_name)) {
            $result = mysqli_query($link, "SELECT `id` FROM `areas` WHERE `name` = '$new_name'");
            $row = mysqli_fetch_assoc($result);
            $other_id = $row['id'];

            if (empty($other_id)) {
                mysqli_query($link, "UPDATE `areas` SET `name` = '$new_name' WHERE `id` = '$area_id'");

                array_push($content, [
                    'success' => true,
                    'id' => $area_id,
                    'result' => [
                        'old' => [
                            'name' => $old_name
                        ],
                        'new' => [
                            'name' => $new_name
                        ]
                    ]
                ]);
            } else {
                array_push($content, [
                    'success' => false,
                    'error' => "$new_name уже есть в таблице"
                ]);
            }
        } else {
            array_push($content, [
                'success' => false,
                'error' => "Отсутствует id, равный $area_id"
            ]);
        }
    } else {
        $errorMessage = ($api_key != '') ? 'Неверный ключ' : 'Не хватает ключа';
        $errorMessage .= '. Обратитесь к администратору: ilya@sidorchik.ru';

        array_push($content, [
            'success' => false,
            'error' => $errorMessage
        ]);
    }

    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    echo $json_str;
}