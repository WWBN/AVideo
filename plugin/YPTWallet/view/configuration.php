<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}");
}

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletDataObject = $plugin->getDataObject();

$wallet = new Wallet(0);
$wallet->setUsers_id(User::getId());
$_page = new Page(array('Configuration'));
?>
<div class="container">
    <?php echo AVideoPlugin::getWalletConfigurationHTML(User::getId(), $wallet, $walletDataObject); ?>
</div>
<?php
$_page->print();
?>