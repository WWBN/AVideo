<?php
require_once '../../../videos/configuration.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <?php
        echo getHTMLTitle(__("Animations"));
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div class="row">
                <?php
                foreach (glob("{$global['systemRootPath']}plugin/Layout/animatedBackGrounds/*.php") as $file) {
                    $name = basename($file);
                    if($name === 'index.php'){
                        continue;
                    }
                    $url = str_replace($global['systemRootPath'], getCDN(), $file);
                    echo "<div class='col-sm-3'>{$name}<iframe src='{$url}' style='width:100%; height: 400px;'></iframe></div>";
                }
                ?>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
