<?php

require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
if(empty($_SESSION['language'])){
    $_SESSION['language'] = $config->getLanguage();
}
if(!empty($_GET['lang'])){
    $_SESSION['language'] = $config->getLanguage() = $_GET['lang'];
}
@include_once "{$global['systemRootPath']}locale/{$_SESSION['language']}.php";
function __($str){
    global $t;
    if(empty($t[$str])){
        return $str;
    }else{
        return $t[$str];
    }
    
}