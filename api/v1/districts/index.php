<?php

header('Content-type: application/json');

$ini = parse_ini_file('../../../app.ini', true);
$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]);

$content = [];
$successMessage = false;
$resultField = 'error';
$resultMessage = [];

if (mysqli_connect_errno()) {
    $resultMessage = 'Соединение с базой данных не удалось';
    goto output;
}

mysqli_set_charset($link, 'utf8');

$district_id = htmlspecialchars($_GET['id']);

if ($district_id != '') {
    $result = mysqli_query($link, "SELECT `name` FROM `districts` WHERE `id` = '$district_id'");
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];

    if (isset($name)) {
        $successMessage = true;
        $resultField = 'result';
        $resultMessage = [
            'id' => $district_id,
            'name' => $name
        ];
    } else {
        $resultMessage = "Не найден id, равный $district_id";
    }
} else {
    $result = mysqli_query($link, "SELECT * FROM `districts`");

    $successMessage = true;
    $resultField = 'result';

    while ($row = mysqli_fetch_assoc($result)) {
        $district_id = $row['id'];
        $name = $row['name'];

        array_push($resultMessage, [
            'id' => $district_id,
            'name' => $name
        ]);
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