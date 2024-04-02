<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage comments"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/comment.php';
$_page = new Page(array('Comments'));
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php
            include $global['systemRootPath'] . 'view/videoComments.php';
            ?>
        </div>
    </div>
</div>
<?php
$_page->print();
?>