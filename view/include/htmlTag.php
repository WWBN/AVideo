<?php
$lang = getLanguage();

if(preg_match('/.*_(.*)/', $langHTML, $mathes)){
    $langHTML = $mathes[1];
}

?>
<html lang="<?php echo $langHTML; ?>">