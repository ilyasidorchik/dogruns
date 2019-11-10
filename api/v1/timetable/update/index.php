<?php

$ini = parse_ini_file('../../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$timetable_id = htmlspecialchars($_GET['id']);
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$new_day = $input[0]['day'];
$new_time = $input[0]['time'];
$new_playground_id = $input[0]['playground_id'];

$api_key = htmlspecialchars($_GET['API_KEY']);

if (isset($timetable_id)) {
    header('Content-type: application/json');

    if ($api_key == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT * FROM `timetable` WHERE `id` = '$timetable_id'");
        $row = mysqli_fetch_assoc($result);
        $old_day = $row['day'];
        $old_time = $row['time'];
        $old_playground_id = $row['playground_id'];

        if (isset($old_day)) {
            if ($old_day != $new_day
                || $old_time != $new_time
                || $old_playground_id != $new_playground_id) {
                mysqli_query($link, "UPDATE `timetable` SET `day` = '$new_day', `time` = '$new_time', `playground_id` = '$new_playground_id' WHERE `id` = '$timetable_id'");

                array_push($content, [
                    'success' => true,
                    'result' => [
                        'old' => [
                            'id' => $timetable_id,
                            'day' => $old_day,
                            'time' => $old_time,
                            'playground_id' => $old_playground_id
                        ],
                        'new' => [
                            'id' => $timetable_id,
                            'day' => $new_day,
                            'time' => $new_time,
                            'playground_id' => $new_playground_id
                        ]
                    ]
                ]);
            } else {
                array_push($content, [
                    'success' => false,
                    'error' => "В таблице уже есть указанная информация"
                ]);
            }
        } else {
            array_push($content, [
                'success' => false,
                'error' => "Отсутствует id, равный $timetable_id"
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