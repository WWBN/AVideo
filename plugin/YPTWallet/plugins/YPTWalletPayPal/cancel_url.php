<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");