<?php
global $global;
$global['userBootstrapLatest'] = 0;
if (empty($global['userBootstrapLatest'])) {
    ?>
    <link href="<?php echo getURL('view/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css"/>    
    <?php
} else {
        ?> 
    <link href="<?php echo getURL('node_modules/bootstrap/dist/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css"/> 
    <link href="<?php echo getURL('view/bootstrap/fixes.css'); ?>" rel="stylesheet" type="text/css"/>  
    <?php
    }
?>