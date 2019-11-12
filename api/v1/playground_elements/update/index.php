<?php

$ini = parse_ini_file('../../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$playground_element_id = htmlspecialchars($_GET['id']);
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$new_playground_id = $input[0]['playground_id'];
$new_element_id = $input[0]['element_id'];

$api_key = htmlspecialchars($_GET['API_KEY']);

if (isset($playground_element_id)) {
    header('Content-type: application/json');

    if ($api_key == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT * FROM playground_elements WHERE `id` = '$playground_element_id'");
        $row = mysqli_fetch_assoc($result);
        $old_playground_id = $row['playground_id'];
        $old_element_id = $row['element_id'];

        if (isset($old_playground_id)) {
            $result = mysqli_query($link, "SELECT `id` FROM playground_elements WHERE `playground_id` = '$new_playground_id'
                                                                                         AND `element_id` = '$new_element_id'");
            $row = mysqli_fetch_assoc($result);
            $other_id = $row['id'];

            if (empty($other_id)) {
                mysqli_query($link, "UPDATE `playground_elements` SET `playground_id` = '$new_playground_id', `element_id` = '$new_element_id' WHERE `id` = '$playground_element_id'");

                array_push($content, [
                    'success' => true,
                    'id' => $playground_element_id,
                    'result' => [
                        'old' => [
                            'playground_id' => $old_playground_id,
                            'element_id' => $old_element_id
                        ],
                        'new' => [
                            'playground_id' => $new_playground_id,
                            'element_id' => $new_element_id
                        ]
                    ]
                ]);
            } else {
                array_push($content, [
                    'success' => false,
                    'error' => "Площадка $new_playground_id с $new_element_id уже есть в таблице"
                ]);
            }
        } else {
            array_push($content, [
                'success' => false,
                'error' => "Отсутствует id, равный $playground_element_id"
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