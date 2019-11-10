<?php

$ini = parse_ini_file('../../../app.ini', true);

$link = mysqli_connect($ini[database][host], $ini[database][user], $ini[database][password], $ini[database][name]) or die('Ошибка');
mysqli_set_charset($link, 'utf8');

$resultAreaByName = mysqli_query($link, "SELECT `id` FROM areas WHERE name = 'СВАО'");
$row = mysqli_fetch_assoc($resultAreaByName);
$area_id = $row['id'];

echo $area_id;

//    mysqli_query($link, "INSERT INTO `playgrounds` (`id`, `address`, `latitude`, `longitude`, `size`, `is_illuminated`, `is_fenced`, `district_id`) VALUES ('NULL', '$address', '$latitude', '$longitude', '$size', '$is_illuminated', '$is_fenced', '0')");