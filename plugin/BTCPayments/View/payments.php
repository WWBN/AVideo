<?php

require_once __DIR__ . '/../../../videos/configuration.php';

if (!User::isLogged()) {
    forbiddenPage(__("You cannot do this"));
    exit;
}

// Older links open this page with ?value=: the same Bitcoin button as the Add Funds page pays that amount.
$value = round(floatval(parseToFloat(is_string($_GET['value'] ?? null) ? $_GET['value'] : 0)), 2);
$wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');

$_page = new Page(array(__('Bitcoin payments')));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css'));

?>

<div class="container-fluid">
    <?php if ($value > 0 && !empty($wallet)) { ?>
        <div class="panel panel-default">
            <div class="panel-body text-center">
                <p class="lead"><?php echo __('Amount to pay'); ?>: <strong><?php echo YPTWallet::formatCurrency($value); ?></strong></p>
                <input type="hidden" id="value" value="<?php echo $value; ?>">
                <?php
                $_GET['plans_id'] = 0;
                include $global['systemRootPath'] . 'plugin/YPTWallet/plugins/YPTWalletBTC/confirmButton.php';
                ?>
            </div>
        </div>
    <?php } ?>
    <?php require_once __DIR__ . '/profileTabContent.php'; ?>
</div>

<?php $_page->print(); ?>
