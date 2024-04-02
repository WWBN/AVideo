<?php
require_once '../../videos/configuration.php';

$plugin = AVideoPlugin::loadPluginIfEnabled('LiveLinks');

if (empty($plugin) || !$plugin->canAddLinks()) {
    forbiddenPage(__("You can not do this"));
}
$_page = new Page(array('Live Links'));
?>
<div class="container">
    <?php
    include_once './view/panel.php';
    ?>
</div>
<?php
$_page->print();
?>