<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (!User::isLogged()) {
    gotToLoginAndComeBackHere('');
}

$_page = new Page(array('Channel Art'));
$_page->setExtraStyles(['view/css/image-editor.css']);
$_page->setExtraScripts(['view/js/channelImages.js']);
?>
<div class="container">
    <?php
    include $global['systemRootPath'] . 'view/userChannelArtUploadInclude.php';
    ?>
</div>
<?php
$_page->print();
?>
