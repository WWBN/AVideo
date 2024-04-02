<?php
require_once '../../../videos/configuration.php';
$_page = new Page(array('Animations'));
?>
<div class="container-fluid">
    <div class="row">
        <?php
        foreach (glob("{$global['systemRootPath']}plugin/Layout/animatedBackGrounds/*.php") as $file) {
            $name = basename($file);
            if ($name === 'index.php') {
                continue;
            }
            $url = str_replace($global['systemRootPath'], getCDN(), $file);
            echo "<div class='col-sm-3'>{$name}<iframe src='{$url}' style='width:100%; height: 400px;'></iframe></div>";
        }
        ?>
    </div>
</div>
<?php
$_page->print();
?>