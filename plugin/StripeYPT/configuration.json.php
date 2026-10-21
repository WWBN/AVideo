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
    $stripe = AVideoPlugin::loadPluginIfEnabled('StripeYPT');
    if (empty($stripe)) {
        throw new RuntimeException('StripeYPT must be enabled');
    }
    require_once __DIR__ . '/Objects/StripeConfiguration.php';
    $stripeHttp = new \Stripe\HttpClient\CurlClient();
    $stripeHttp->setConnectTimeout(3);
    $stripeHttp->setTimeout(8);
    \Stripe\ApiRequestor::setHttpClient($stripeHttp);
    if ($action === 'configure') {
        // Stable file shared by PHP workers; never unlink a flock file.
        $directory = $global['systemRootPath'] . 'videos/stripe-payment-locks/';
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create Stripe configuration lock directory');
        }
        $setupLock = @fopen($directory . 'configuration-' . hash('sha256', $global['webSiteRootURL']) . '.lock', 'c+');
        if (!$setupLock || !flock($setupLock, LOCK_EX | LOCK_NB)) {
            throw new RuntimeException('Another Stripe setup is in progress');
        }
    }
    // Read fresh stored values under the setup lock; do not overwrite other plugin settings.
    // getPluginByUUID also has a request-local cache. Refresh it after taking the lock.
    unset($GLOBALS['getPluginByUUID'][$stripe->getUUID()]);
    $row = Plugin::getPluginByUUID($stripe->getUUID(), true);
    $stored = !empty($row['object_data']) ? json_decode(stripslashes($row['object_data'])) : null;
    if (!is_object($stored) && !empty($row['object_data'])) {
        $stored = json_decode($row['object_data']);
    }
    $settings = (object) array_merge((array) $stripe->getEmptyDataObject(), (array) $stored);
    $checker = new StripeConfiguration($settings, $global['webSiteRootURL'] . 'plugin/StripeYPT/ipn.php');
    if ($action === 'configure') {
        if (!$stripe->canEditPlugin()) {
            // Demo/locked configuration must also protect this alternative settings writer.
            throw new RuntimeException('Advanced configuration changes are disabled');
        }
        $response['setup'] = $checker->configure(function ($newSettings) use ($stripe, $row) {
            // Compare-and-save: an administrator may have edited the keys while Stripe
            // was responding. Do not overwrite that newer configuration with this snapshot.
            $encoded = addcslashes(json_encode($newSettings, JSON_THROW_ON_ERROR), '\\');
            $saved = sqlDAL::writeSql('UPDATE ' . Plugin::getTableName() . " SET object_data = ? WHERE id = ? AND SHA2(COALESCE(object_data, ''), 256) = ?",
                'sis', [$encoded, intval($row['id']), hash('sha256', $row['object_data'] ?? '')]);
            if (!$saved) {
                return false;
            }
            // sqlDAL closes its prepared statement, invalidating affected_rows/ROW_COUNT().
            // Read back this unique encrypted value instead of trusting the write boolean.
            $result = sqlDAL::readSql('SELECT object_data FROM ' . Plugin::getTableName() . ' WHERE id = ?', 'i', [intval($row['id'])], true);
            $current = sqlDAL::fetchAssoc($result);
            sqlDAL::close($result);
            if (!isset($current['object_data']) || !hash_equals($encoded, $current['object_data'])) {
                return false;
            }
            Plugin::deletePluginCache($stripe->getUUID());
            unset($GLOBALS['getPluginByUUID'][$stripe->getUUID()]);
            unset($GLOBALS['pluginGetDataObject']['StripeYPT']);
            unset(PluginAbstract::$dataObject[$stripe->getUUID()]);
            return true;
        });
    }
    $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
    $walletSettings = empty($wallet) ? null : $wallet->getDataObject();
    $response['report'] = $checker->check([
        'wallet' => !empty($wallet),
        'wallet_stripe' => !empty($walletSettings->enablePlugin_YPTWalletStripe),
        'subscription' => AVideoPlugin::isEnabledByName('Subscription'),
    ]);
    if (!$stripe->canEditPlugin()) {
        $response['report']['can_configure'] = false;
        $response['report']['checks'][] = ['id' => 'configuration_lock', 'label' => 'Configuration changes',
            'status' => 'warning', 'message' => 'Advanced configuration changes are disabled for this installation. Read-only checks are available.'];
    }
    $response['webhook'] = $response['report']['webhooks'][0] ?? false;
    $response['error'] = false;
    $response['msg'] = __('Checks completed');
} catch (Throwable $exception) {
    // Do not log exception text: Stripe authentication errors can include credentials.
    _error_log('Stripe configuration check failed: ' . get_class($exception), AVideoLog::$ERROR);
} finally {
    if (is_resource($setupLock)) {
        flock($setupLock, LOCK_UN);
        fclose($setupLock);
    }
}
echo json_encode($response);
