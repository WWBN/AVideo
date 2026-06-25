<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}

$startDate = !empty($_GET['start_date']) ? preg_replace('/[^0-9\-]/', '', $_GET['start_date']) : date('Y-m-d', strtotime('-1 year'));
$endDate = !empty($_GET['end_date']) ? preg_replace('/[^0-9\-]/', '', $_GET['end_date']) : date('Y-m-d');

$_page = new Page(array('Wallet Exports'));
?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Wallet Reports Export'); ?></h3>
        </div>
        <div class="panel-body">
            <p><?php echo __('This export includes only data stored in wallet and wallet_log tables, plus user and email from users table.'); ?></p>

            <form method="GET" class="form-inline" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label for="start_date"><?php echo __('Start Date'); ?></label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="form-group" style="margin-left: 10px;">
                    <label for="end_date"><?php echo __('End Date'); ?></label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <button type="submit" class="btn btn-default" style="margin-left: 10px;"><i class="fas fa-filter"></i> <?php echo __('Apply'); ?></button>
            </form>

            <div class="list-group">
                <a class="list-group-item" href="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/exportDownload.php?report=transactions&format=csv&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" target="_blank">
                    <i class="fas fa-file-csv"></i> <?php echo __('Download Wallet Transactions CSV'); ?>
                </a>
                <a class="list-group-item" href="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/exportDownload.php?report=transactions&format=pdf&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" target="_blank">
                    <i class="fas fa-file-pdf"></i> <?php echo __('Download Wallet Transactions PDF'); ?>
                </a>
                <a class="list-group-item" href="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/exportDownload.php?report=balances&format=csv" target="_blank">
                    <i class="fas fa-file-csv"></i> <?php echo __('Download Wallet Balances CSV'); ?>
                </a>
                <a class="list-group-item" href="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/exportDownload.php?report=balances&format=pdf" target="_blank">
                    <i class="fas fa-file-pdf"></i> <?php echo __('Download Wallet Balances PDF'); ?>
                </a>
                <a class="list-group-item" href="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/exportDownload.php?report=combined&format=pdf&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" target="_blank">
                    <i class="fas fa-file-pdf"></i> <?php echo __('Download Combined Wallet Report PDF'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
