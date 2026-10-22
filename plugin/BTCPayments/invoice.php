<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isLogged()) {
    forbiddenPage("You can not do this");
}
// Older links (cached pages, other plugins): a wallet top-up of ?value=, followed on the Bitcoin payment page.
if (empty(AVideoPlugin::loadPluginIfEnabled('BTCPayments')) || empty(AVideoPlugin::loadPluginIfEnabled('YPTWallet'))) {
    forbiddenPage(__('Bitcoin payments are not available right now. You were not charged. Please try again.'));
}
$result = false;
try {
    $result = BTCPayments::startCheckout(User::getId(), ['value' => is_string($_GET['value'] ?? null) ? $_GET['value'] : '']);
} catch (BTCPaymentsCheckoutRefused $refused) {
    forbiddenPage($refused->getMessage());
} catch (\Throwable $th) {
    _error_log('BTC::invoice.php - could not start the payment: ' . $th->getMessage(), AVideoLog::$ERROR);
    forbiddenPage(__('Bitcoin payments are not available right now. You were not charged. Please try again.'));
}
header('Location: ' . BTCPayments::getCheckoutPageURL($result['invoice']));
