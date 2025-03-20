<?php

require_once __DIR__ . '/../../../videos/configuration.php';

if (!User::isLogged()) {
    forbiddenPage(__("You cannot do this"));
    exit;
}

// Get the parameters from the request
$value = isset($_GET['value']) ? $_GET['value'] : '0';
$orderId = isset($_GET['orderId']) ? $_GET['orderId'] : '';
$description = isset($_GET['description']) ? $_GET['description'] : 'New Payment';
$redirectUrl = isset($_GET['redirectUrl']) ? $_GET['redirectUrl'] : $global['webSiteRootURL'];

$url = $global['webSiteRootURL'].'plugin/BTCPayments/invoice.php';
$url = addQueryStringParameter($url, 'value', $value);
$url = addQueryStringParameter($url, 'description', $description);
$url = addQueryStringParameter($url, 'redirectUrl', $redirectUrl);
$url = addQueryStringParameter($url, 'orderId', $orderId);

$_page = new Page(array(__('BTC Payments')));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css'));

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <a href="<?php echo $url; ?>" onclick="modal.showPleaseWait();"
                class="btn btn-warning btn-block btn-lg">
                <i class="fab fa-bitcoin"></i> <?php echo __("Make a New Payment of"); ?> <?php echo YPTWallet::formatCurrency($_GET['value'], true) ?>
            </a>
        </div>
        <div class="col-md-12">

            <?php require_once __DIR__ . '/profileTabContent.php'; ?>
        </div>
    </div>
</div>


<?php $_page->print(); ?>
