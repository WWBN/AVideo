<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../videos/configuration.php';
}
$metaDescription = "Themes Page";
$_page = new Page(array('Themes Page'));
?>
<div class="container-fluid">
    <div class="row">
        <?php
        $themes = getThemes();
        foreach ($themes as $value) {
        ?>
            <div class=" col-sm-4 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-body" style="padding: 5px;">
                        <iframe frameBorder="0" width="100%" height="250px" src="<?php echo getCDN(); ?>view/css/custom/theme.php?theme=<?php echo $value; ?>"></iframe>
                    </div>
                </div>

            </div>
        <?php
        }
        ?>
    </div>

</div><!--/.container-->
<?php
$_page->print();
?>