<?php
require_once '../../videos/configuration.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}
forbidIfNotPost();
forbidIfInvalidToken();

$response = ['error' => true, 'msg' => __('An error occurred')];
$setupLock = false;
try {
    $action = $_POST['action'] ?? 'check';
    if (!is_string($action) || !in_array($action, ['check', 'configure'], true)) {
        throw new InvalidArgumentException('Invalid configuration action');
    }
    $paypal = AVideoPlugin::loadPluginIfEnabled('PayPalYPT');
    if (empty($paypal)) {
        throw new RuntimeException('PayPalYPT must be enabled');
    }
    require_once __DIR__ . '/Objects/PayPalConfiguration.php';
    if ($action === 'configure') {
        // Stable file shared by PHP workers; never unlink a flock file.
        $directory = $global['systemRootPath'] . 'videos/paypal-locks/';
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create PayPal configuration lock directory');
        }
        $setupLock = @fopen($directory . 'configuration-' . hash('sha256', $global['webSiteRootURL']) . '.lock', 'c+');
        if (!$setupLock || !flock($setupLock, LOCK_EX | LOCK_NB)) {
            throw new RuntimeException('Another PayPal setup is in progress');
        }
    }
    // Read the stored settings fresh (under the setup lock), bypassing the request-local plugin cache.
    unset($GLOBALS['getPluginByUUID'][$paypal->getUUID()]);
    $row = Plugin::getPluginByUUID($paypal->getUUID(), true);
    $stored = !empty($row['object_data']) ? json_decode(stripslashes($row['object_data'])) : null;
    if (!is_object($stored) && !empty($row['object_data'])) {
        $stored = json_decode($row['object_data']);
    }
    $settings = (object) array_merge((array) $paypal->getEmptyDataObject(), (array) $stored);
    $checker = new PayPalConfiguration($settings, PayPalYPT::getWebhookURL());
    if ($action === 'configure') {
        if (!$paypal->canEditPlugin()) {
            // Demo/locked installations must also protect this alternative settings writer.
            throw new RuntimeException('Advanced configuration changes are disabled');
        }
        $response['setup'] = $checker->configure(function ($webhookId) use ($paypal, $row, $stored) {
            // Compare-and-save: another administrator may have changed the settings during the PayPal calls.
            $newSettings = is_object($stored) ? clone $stored : new stdClass();
            $newSettings->webhookId = $webhookId;
            $encoded = addcslashes(json_encode($newSettings, JSON_THROW_ON_ERROR), '\\');
            $saved = sqlDAL::writeSql('UPDATE ' . Plugin::getTableName() . " SET object_data = ? WHERE id = ? AND SHA2(COALESCE(object_data, ''), 256) = ?",
                'sis', [$encoded, intval($row['id']), hash('sha256', $row['object_data'] ?? '')]);
            if (!$saved) {
                return false;
            }
            // sqlDAL closes its prepared statement, so affected_rows/ROW_COUNT() are not usable: read it back.
            $result = sqlDAL::readSql('SELECT object_data FROM ' . Plugin::getTableName() . ' WHERE id = ?', 'i', [intval($row['id'])], true);
            $current = sqlDAL::fetchAssoc($result);
            sqlDAL::close($result);
            if (!isset($current['object_data']) || !hash_equals($encoded, $current['object_data'])) {
                return false;
            }
            Plugin::deletePluginCache($paypal->getUUID());
            unset($GLOBALS['getPluginByUUID'][$paypal->getUUID()]);
            unset($GLOBALS['pluginGetDataObject']['PayPalYPT']);
            unset(PluginAbstract::$dataObject[$paypal->getUUID()]);
            return true;
        });
    }
    $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
    $walletSettings = empty($wallet) ? null : $wallet->getDataObject();
    $response['report'] = $checker->check([
        'wallet' => !empty($wallet),
        'wallet_paypal' => !empty($walletSettings->enablePlugin_YPTWalletPayPal),
        'currency' => $walletSettings->currency ?? '',
        'subscription' => AVideoPlugin::isEnabledByName('Subscription'),
        'payouts' => !empty($walletSettings->enableAutoWithdrawFundsPagePaypal),
        'checkout_table' => PayPalYPT_checkout::isInstalled(),
        'ipn_url' => PayPalYPT::getIPNURL(),
        'webhook_delivery' => PayPalYPT::getRecordedDelivery('webhook', $settings),
        'ipn_delivery' => PayPalYPT::getRecordedDelivery('ipn', $settings),
    ]);
    if (!$paypal->canEditPlugin()) {
        $response['report']['can_configure'] = false;
        $response['report']['checks'][] = ['id' => 'configuration_lock', 'label' => 'Configuration changes',
            'status' => 'warning', 'message' => 'Advanced configuration changes are disabled for this installation. Read-only checks are available.'];
    }
    $response['error'] = false;
    $response['msg'] = __('Checks completed');
} catch (Throwable $exception) {
    // Never log exception text here: HTTP client errors can include credentials.
    _error_log('PayPal configuration check failed: ' . get_class($exception), AVideoLog::$ERROR);
    if ($exception instanceof RuntimeException && in_array($exception->getMessage(), ['Another PayPal setup is in progress', 'Advanced configuration changes are disabled', 'PayPalYPT must be enabled'], true)) {
        $response['msg'] = __($exception->getMessage());
    }
} finally {
    if (is_resource($setupLock)) {
        flock($setupLock, LOCK_UN);
        fclose($setupLock);
    }
}
echo json_encode($response);
