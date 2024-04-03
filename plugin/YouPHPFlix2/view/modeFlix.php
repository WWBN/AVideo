<?php
$isFirstPage = 1;
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once __DIR__ . '/../../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/category.php';

if (AVideoPlugin::isEnabledByName('PlayLists')) {
    PlayLists::loadScripts();
}
$obj = AVideoPlugin::getObjectData("YouPHPFlix2");
$_page = new Page(array(''));
$_page->setIncludeInHead(array('plugin/YouPHPFlix2/view/modeFlixHead.php'));
?>
<div class="container-fluid nopadding flickity-area" id="mainContainer" style="display:none;">
    <?php
    include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/modeFlixBody.php';
    ?>
</div>

<?php
include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/modeFlixFooter.php';
?>
<?php
$_page->print();
?>