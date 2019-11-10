<?php

require 'remotetypograf.php';

function typograf($str) {
    $remoteTypograf = new RemoteTypograf('UTF-8');

    $remoteTypograf->htmlEntities();
    $remoteTypograf->br (false);
    $remoteTypograf->p (false);
    $remoteTypograf->nobr (3);
    $remoteTypograf->quotA ('laquo raquo');
    $remoteTypograf->quotB ('bdquo ldquo');

    $strTypografed = $remoteTypograf->processText($str);

    return $strTypografed;
}