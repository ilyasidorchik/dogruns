<?php

header('Content-type: application/json');

$ini = parse_ini_file('../../../../app.ini', true);
$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');

$content = [];
$successMessage = false;
$resultField = 'error';
$resultMessage = '';

if (mysqli_connect_errno()) {
    $resultMessage = 'Соединение с базой данных не удалось';
    goto output;
}

mysqli_set_charset($link, 'utf8');

$area_id = htmlspecialchars($_GET['id']);
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$new_name = $input[0]['name'];

// Validation
$new_name = str_replace("'", "", $new_name);

$api_key = htmlspecialchars($_GET['API_KEY']);

if (isset($area_id)) {
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

                $successMessage = true;
                $resultField = 'result';
                $resultMessage = [
                    'id' => $area_id,
                    'old' => [
                        'name' => $old_name
                    ],
                    'new' => [
                        'name' => $new_name
                    ]
                ];
            } else {
                $resultMessage = "$new_name уже есть в таблице";
            }
        } else {
            $resultMessage = "Отсутствует id, равный $area_id";
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