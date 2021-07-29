<?php 

function converteData($data, $formato){
    return date($formato, strtotime($data));
}

function clearSpecialCaracters($string){
    $string = str_replace(" ","-", $string);
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
}

function setToDecimal($value){
    //remove pontos de milhas
    $value = str_replace('.','',$value);
    //altera virgulas decimais por ponto e converte em float
    return floatVal(str_replace(',','.',$value));
} 

function convertValorReal($number){
    return "R$ ".number_format($number, 2, ',','.');
}