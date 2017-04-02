<?php
if(empty($_SESSION['language'])){
    $_SESSION['language'] = $global['language'];
}
if(!empty($_GET['lang'])){
    $_SESSION['language'] = $global['language'] = $_GET['lang'];
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