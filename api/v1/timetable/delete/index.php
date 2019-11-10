<?php

$ini = parse_ini_file('../../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$timetable_id = htmlspecialchars($_GET['id']);
$apiKey = htmlspecialchars($_GET['API_KEY']);

if (isset($timetable_id)) {
    header('Content-type: application/json');

    if ($apiKey == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT * FROM `timetable` WHERE `id` = '$timetable_id'");
        $row = mysqli_fetch_assoc($result);
        $day = $row['day'];
        $time = $row['time'];
        $playground_id = $row['playground_id'];

        if (isset($day)) {
            mysqli_query($link, "DELETE FROM `timetable` WHERE `id` = '$timetable_id'");

            array_push($content, [
                'success' => true,
                'result' => [
                    'id' => $timetable_id,
                    'day' => $day,
                    'time' => $time,
                    'playground_id' => $playground_id
                ]
            ]);
        } else {
            array_push($content, [
                'success' => false,
                'error' => "Отсутствует id, равный $timetable_id"
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