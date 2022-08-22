<?php
if(!empty($_SESSION['language'])){
    $lang = $_SESSION['language'];
} else if(!empty($config)){
    $lang = $config->getLanguage();
}
if(empty($lang)){
    $lang = 'en';
}
$lang = strtolower($lang);
if(preg_match('/.*_(.*)/', $lang, $mathes)){
    $lang = $mathes[1];
}

?>
<html lang="<?php echo $lang; ?>">