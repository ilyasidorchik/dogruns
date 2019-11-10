<?php

$ini = parse_ini_file('../../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$day = $input[0]['day'];
$time = $input[0]['time'];
$playground_id = $input[0]['playground_id'];

$apiKey = htmlspecialchars($_GET['API_KEY']);

if (isset($day) && isset($time) && isset($playground_id)) {
    header('Content-type: application/json');

    if ($apiKey == $ini[api][key]) {
        $result = mysqli_query($link, "SELECT `id` FROM `timetable` WHERE `day` = '$day'");
        $row = mysqli_fetch_assoc($result);
        $timetable_id = $row['id'];

        if (empty($timetable_id)) {
            mysqli_query($link, "INSERT INTO `timetable` (`id`, `day`, `time`, `playground_id`) VALUES ('NULL', '$day', '$time', '$playground_id')");

            $result = mysqli_query($link, "SELECT `id` FROM `timetable` WHERE `day` = '$day'");
            $row = mysqli_fetch_assoc($result);
            $timetable_id = $row['id'];

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
                'error' => "Повторное добавление $day"
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