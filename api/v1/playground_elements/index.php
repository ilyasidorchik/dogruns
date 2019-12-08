<?php

header('Content-type: application/json');

$ini = parse_ini_file('../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$content = [];
$content_result = [];
$content_key_result = 'result';
$content_status = 'OK';

$playground_element_id = htmlspecialchars($_GET['id']);
$input_playground_id = htmlspecialchars($_GET['playground_id']);

if ($playground_element_id != '') {
    $result = mysqli_query($link, "SELECT * FROM `playground_elements` WHERE `id` = '$playground_element_id'");
    $row = mysqli_fetch_assoc($result);
    $playground_id = $row['playground_id'];
    $element_id = $row['element_id'];

    if (isset($playground_id)) {
        array_push($content_result, [
            'id' => $playground_element_id,
            'playground_id' => $playground_id,
            'element_id' => $element_id
        ]);
    } else {
        $content_key_result = 'error';
        $content_result = 'Не найдено';
        $content_status = 'Not Found';

        goto output;
    }

    goto output;
}


if ($input_playground_id != '') {
    $result = mysqli_query($link, "SELECT * FROM `playground_elements` WHERE `playground_id` = '$input_playground_id'");

    if (!$row = mysqli_fetch_assoc($result)) {
        $content_key_result = 'error';
        $content_result = 'Не найдено';
        $content_status = 'Not Found';

        goto output;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $playground_element_id = $row['id'];
        $element_id = $row['element_id'];

        array_push($content_result, [
            'id' => $playground_element_id,
            'element_id' => $element_id
        ]);
    }

    goto output;
}


$result = mysqli_query($link, "SELECT * FROM `playground_elements`");

while ($row = mysqli_fetch_assoc($result)) {
    $playground_element_id = $row['id'];
    $playground_id = $row['playground_id'];
    $element_id = $row['element_id'];

    array_push($content_result, [
        'id' => $playground_element_id,
        'playground_id' => $playground_id,
        'element_id' => $element_id
    ]);
}


output:

array_push($content, [
    $content_key_result => $content_result,
    'status' => $content_status
]);

if ($content) {
    $json_str = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    echo $json_str;
}