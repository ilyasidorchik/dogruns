<?php

header('Content-type: application/json');

$ini = parse_ini_file('../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$district_id = htmlspecialchars($_GET['id']);

if ($district_id != '') {
    $result = mysqli_query($link, "SELECT `name` FROM `districts` WHERE `id` = '$district_id'");
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];

    if (isset($name)) {
        array_push($content, [
            'id' => $district_id,
            'name' => $name
        ]);
    } else {
        array_push($content, [
            'id' => $district_id,
            'error' => 'Не найден'
        ]);
    }
} else {
    $result = mysqli_query($link, "SELECT * FROM `districts`");

    while ($row = mysqli_fetch_assoc($result)) {
        $district_id = $row['id'];
        $name = $row['name'];

        array_push($content, [
            'id' => $district_id,
            'name' => $name
        ]);
    }
}

if ($content) {
    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    echo $json_str;
}