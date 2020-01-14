<html>
    <head>
        <style>
            html, body{
                height: 100%;
                margin: 0;
                padding: 0;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        global $global, $config;
        if(!isset($global['systemRootPath'])){
            require_once '../videos/configuration.php';
        }
        echo $config->getAdsense();
        ?>
    </body>
</html>
