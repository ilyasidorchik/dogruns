<?php

header('Content-type: application/json');

$ini = parse_ini_file('../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$timetable_id = htmlspecialchars($_GET['id']);

if ($timetable_id != '') {
    $result = mysqli_query($link, "SELECT * FROM `timetable` WHERE `id` = '$timetable_id'");
    $row = mysqli_fetch_assoc($result);
    $day = $row['day'];
    $time = $row['time'];
    $playground_id = $row['playground_id'];

    if (isset($day)) {
        array_push($content, [
            'id' => $timetable_id,
            'day' => $day,
            'time' => $time,
            'playground_id' => $playground_id
        ]);
    } else {
        array_push($content, [
            'id' => $timetable_id,
            'error' => 'Не найден'
        ]);
    }
} else {
    $result = mysqli_query($link, "SELECT * FROM `timetable`");

    while ($row = mysqli_fetch_assoc($result)) {
        $timetable_id = $row['id'];
        $day = $row['day'];
        $time = $row['time'];
        $playground_id = $row['playground_id'];

        array_push($content, [
            'id' => $timetable_id,
            'day' => $day,
            'time' => $time,
            'playground_id' => $playground_id
        ]);
    }
}

if ($content) {
    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    echo $json_str;
}