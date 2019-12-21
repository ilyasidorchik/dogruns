<?php

header('Content-type: application/json');

$ini = parse_ini_file('../../../app.ini', true);
$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');

$content = [];
$successMessage = false;
$resultField = 'error';
$resultMessage = '';

if (mysqli_connect_errno()) {
    $resultMessage = 'Соединение с базой данных не удалось';
    goto next;
}

mysqli_set_charset($link, 'utf8');

$area_id = htmlspecialchars($_GET['id']);

if ($area_id != '') {
    $result = mysqli_query($link, "SELECT `name` FROM `areas` WHERE `id` = '$area_id'");
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];

    if (isset($name)) {
        $successMessage = true;
        $resultField = 'result';
        $resultMessage = [
            'id' => $area_id,
            'name' => $name
        ];
    } else {
        $resultMessage = "Не найден id = $area_id";
    }
} else {
    $result = mysqli_query($link, "SELECT * FROM `areas`");
    $successMessage = true;
    $resultField = 'result';
    $resultMessage = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $area_id = $row['id'];
        $name = $row['name'];

        array_push($resultMessage, [
            'id' => $area_id,
            'name' => $name
        ]);
    }
}

next:

array_push($content, [
    'success' => $successMessage,
    $resultField => $resultMessage
]);

if ($content) {
    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo $json_str;
}