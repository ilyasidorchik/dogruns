<?php

header('Content-type: application/json');

$ini = parse_ini_file('../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];

$element_id = htmlspecialchars($_GET['id']);

if ($element_id != '') {
    $result = mysqli_query($link, "SELECT `name` FROM `elements` WHERE `id` = '$element_id'");
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];

    if (isset($name)) {
        array_push($content, [
            'id' => $element_id,
            'name' => $name
        ]);
    } else {
        array_push($content, [
            'id' => $element_id,
            'error' => 'Не найден'
        ]);
    }
} else {
    $result = mysqli_query($link, "SELECT * FROM `elements`");

    while ($row = mysqli_fetch_assoc($result)) {
        $element_id = $row['id'];
        $name = $row['name'];

        array_push($content, [
            'id' => $element_id,
            'name' => $name
        ]);
    }
}

if ($content) {
    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    echo $json_str;
}