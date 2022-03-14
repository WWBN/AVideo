<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if(!User::isLogged()){
    header("Location: {$global['webSiteRootURL']}");
}

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletDataObject = $plugin->getDataObject();

$wallet = new Wallet(0);
$wallet->setUsers_id(User::getId());
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Configuration") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <br>
        <div class="container">
            <?php echo AVideoPlugin::getWalletConfigurationHTML(User::getId(), $wallet, $walletDataObject); ?>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
