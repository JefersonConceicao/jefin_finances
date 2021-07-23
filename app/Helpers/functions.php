<?php 

function converteData($data, $formato){
    return date($formato, strtotime($data));
}

function clearSpecialCaracters($string){
    $string = str_replace(" ","-", $string);
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
}