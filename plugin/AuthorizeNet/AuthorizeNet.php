<?php

/**
 * Authorize.Net plugin for AVideo
 * - Customer Profiles & Payments: Official SDK (net\authorize\api\*)
 * - Webhooks management: REST (file_get_contents) because the SDK does NOT expose webhook endpoints
 */

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AuthorizeNet/Objects/Anet_webhook_log.php';
require_once $global['systemRootPath'] . 'plugin/AuthorizeNet/Objects/Anet_pending_payment.php';

use net\authorize\api\contract\v1\GetCustomerProfileResponse;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

use net\authorize\api\contract\v1\CreateCustomerProfileResponse;
use net\authorize\api\contract\v1\GetHostedPaymentPageResponse;
use net\authorize\api\contract\v1\GetHostedProfilePageResponse;

use net\authorize\api\contract\v1\ARBSubscriptionType;
use net\authorize\api\contract\v1\PaymentScheduleType;
use net\authorize\api\contract\v1\PaymentScheduleType\IntervalAType;
use net\authorize\api\contract\v1\CustomerProfileIdType;
use net\authorize\api\contract\v1\ARBCreateSubscriptionRequest;
use net\authorize\api\contract\v1\ARBCreateSubscriptionResponse;
use net\authorize\api\contract\v1\ARBGetSubscriptionStatusRequest;
use net\authorize\api\contract\v1\ARBGetSubscriptionStatusResponse;
use net\authorize\api\controller\ARBCreateSubscriptionController;
use net\authorize\api\controller\ARBGetSubscriptionStatusController;
use net\authorize\api\contract\v1\OrderType;
use net\authorize\api\contract\v1\ARBGetSubscriptionRequest;
use net\authorize\api\contract\v1\ARBGetSubscriptionResponse;
use net\authorize\api\controller\ARBGetSubscriptionController;


class AuthorizeNet extends PluginAbstract
{

    public function getPluginDependencies() {
        return array(
            PluginDependency::create('YPTWallet', 'Credits successful payments to the user wallet balance'),
            PluginDependency::create('Subscription', 'Optional recurring subscription plan integration', false),
        );
    }

    // --- inside AuthorizeNet class ---

    /**
     * Validate ANet webhook signature (HMAC-SHA512).
     *
     * The Signature Key is used as a raw ASCII string in the HMAC — not hex-decoded.
     *
     * @param string $rawBody
     * @param array  $headers
     * @param string $signatureKey
     * @return bool
     */
    public static function verifySignature(string $rawBody, array $headers, string $signatureKey): bool
    {
        $received = $headers['X-ANET-Signature'] ?? ($headers['x-anet-signature'] ?? '');
        if (empty($signatureKey) || empty($received)) {
            return false;
        }
        $expected = 'sha512=' . strtolower(hash_hmac('sha512', $rawBody, $signatureKey));
        return hash_equals($expected, strtolower($received));
    }

    /**
     * Parse webhook JSON and extract common fields.
     *
     * @param string $rawBody
     * @param array  $headers
     * @param array  $allowedEvents
     * @return array{
     *   error:bool,
     *   msg?:string,
     *   data:array,
     *   payload:array,
     *   eventType:string,
     *   transactionId:?string,
     *   amount:float|null,
     *   currency:?string,
     *   metadata:array,
     *   users_id:?int,
     *   uniq_key:string,
     *   signatureValid:bool
     * }
     */
    public static function parseWebhookRequest(string $rawBody, array $headers, array $allowedEvents = ['net.authorize.payment.authcapture.created']): array
    {
        $cfg      = self::getConfig();
        $sigValid = self::verifySignature($rawBody, $headers, trim((string)($cfg->signatureKey ?? '')));

        $json = json_decode($rawBody, true);
        if (!is_array($json)) {
            return ['error' => true, 'msg' => 'Invalid JSON', 'signatureValid' => $sigValid];
        }

        $eventType = $json['eventType'] ?? '';
        if (!in_array($eventType, $allowedEvents)) {
            return ['error' => true, 'msg' => 'Ignored event type', 'eventType' => $eventType, 'signatureValid' => $sigValid];
        }

        $payload       = $json['payload'] ?? [];
        $transactionId = $payload['id'] ?? ($payload['transId'] ?? null);
        $amount        = isset($payload['amount']) ? (float)$payload['amount'] : (isset($payload['authAmount']) ? (float)$payload['authAmount'] : null);
        $currency      = $payload['currencyCode'] ?? ($payload['currency'] ?? null);
        $metadata      = $payload['metadata'] ?? [];
        $users_id      = isset($metadata['users_id']) ? (int)$metadata['users_id'] : null;
        $uniq_key      = sha1($eventType . ($transactionId ?? 'no-txn'));

        return [
            'error'          => false,
            'data'           => $json,
            'payload'        => $payload,
            'eventType'      => $eventType,
            'transactionId'  => $transactionId,
            'amount'         => $amount,
            'currency'       => $currency,
            'metadata'       => $metadata,
            'users_id'       => $users_id,
            'uniq_key'       => $uniq_key,
            'signatureValid' => $sigValid,
        ];
    }

    /**
     * Try to pull users_id from TransactionDetailsType->userFields.
     */
    private static function extractUsersIdFromTxnRaw($txnRaw): ?int
    {
        if (!$txnRaw || !method_exists($txnRaw, 'getUserFields')) {
            return null;
        }
        $ufList = $txnRaw->getUserFields();
        if ($ufList && method_exists($ufList, 'getUserField')) {
            foreach ($ufList->getUserField() as $uf) {
                if ($uf->getName() === 'users_id') {
                    return (int)$uf->getValue();
                }
            }
        }
        return null;
    }

    /**
     * Analyze webhook (payload + raw txn) to decide if it's subscription or single payment.
     *
     * @param array $payload
     * @param mixed $transactionRawObject
     * @return array{
     *   isASubscription:bool,
     *   subscriptionId:?string,
     *   users_id:?int,
     *   plans_id:?int,
     *   amount:float|null,
     *   currency:?string,
     *   metadata:array,
     *   isApproved:bool
     * }
     */
    public static function analyzeTransactionFromWebhook(array $payload, $transactionRawObject = null): array
    {
        $metadata        = $payload['metadata'] ?? [];
        $users_id        = isset($metadata['users_id']) ? (int)$metadata['users_id'] : null;
        $plans_id        = isset($metadata['plans_id']) ? (int)$metadata['plans_id'] : null;
        $amount          = isset($payload['amount']) ? (float)$payload['amount'] : (isset($payload['authAmount']) ? (float)$payload['authAmount'] : null);
        $currency        = $payload['currencyCode'] ?? ($payload['currency'] ?? null);

        // detect subscription
        $subscriptionId  = $payload['subscription']['id'] ?? null;
        $isASubscription = !empty($subscriptionId);

        // fallback to raw object if needed
        if (!$isASubscription && $transactionRawObject && method_exists($transactionRawObject, 'getSubscription')) {
            $sub = $transactionRawObject->getSubscription();
            if ($sub && method_exists($sub, 'getId')) {
                $subscriptionId  = $sub->getId();
                $isASubscription = !empty($subscriptionId);
            }
        }

        // For subscriptions, try to extract metadata from transaction order description
        if ($isASubscription && $transactionRawObject && method_exists($transactionRawObject, 'getOrder')) {
            $order = $transactionRawObject->getOrder();
            if ($order && method_exists($order, 'getDescription')) {
                $description = $order->getDescription();
                if (!empty($description)) {
                    $decodedMeta = json_decode($description, true);
                    if (is_array($decodedMeta)) {
                        // Merge subscription metadata with payload metadata
                        $metadata = array_merge($metadata, $decodedMeta);
                        if (!$users_id && isset($decodedMeta['users_id'])) {
                            $users_id = (int)$decodedMeta['users_id'];
                        }
                        if (!$plans_id && isset($decodedMeta['plans_id'])) {
                            $plans_id = (int)$decodedMeta['plans_id'];
                        }
                    }
                }
            }

            // Also check invoice number for plans_id
            if ($order && method_exists($order, 'getInvoiceNumber') && !$plans_id) {
                $invoiceNumber = $order->getInvoiceNumber();
                if (!empty($invoiceNumber) && is_numeric($invoiceNumber)) {
                    $plans_id = (int)$invoiceNumber;
                }
            }
        }

        // fallback users_id from raw
        if (!$users_id && $transactionRawObject) {
            $users_id = self::extractUsersIdFromTxnRaw($transactionRawObject);
        }

        // approval check
        $isApproved = false;
        if ($transactionRawObject && method_exists($transactionRawObject, 'getTransactionStatus')) {
            $status = strtolower((string)$transactionRawObject->getTransactionStatus());
            $isApproved = in_array($status, ['capturedpendingsettlement', 'settledsuccessfully'], true);
        } elseif (isset($payload['responseCode'])) {
            $isApproved = ((int)$payload['responseCode'] === 1);
        }

        return [
            'isASubscription' => $isASubscription,
            'subscriptionId'  => $subscriptionId,
            'users_id'        => $users_id,
            'plans_id'        => $plans_id,
            'amount'          => $amount,
            'currency'        => $currency,
            'metadata'        => $metadata,
            'isApproved'      => $isApproved
        ];
    }

    /**
     * Process a single (one-time) payment: credit wallet, persist log, mark processed.
     *
     * @param int    $users_id   Internal user ID to credit.
     * @param float  $amount     Amount to credit.
     * @param string $uniq_key   Unique key built from event + transactionId to avoid duplicates.
     * @param string $eventType  Webhook event type.
     * @param array  $payload    Raw payload you want to store in the log (optional).
     * @param string $description Optional wallet description.
     * @return array{error:bool,msg?:string,logId?:int}
     */
    public static function processSinglePayment(
        int $users_id,
        float $amount,
        string $uniq_key,
        string $eventType,
        array $payload = [],
        string $description = 'Authorize.Net one-time payment',
        string $transactionId = ''
    ): array {
        global $global;
        $lockKey = $transactionId ?: $uniq_key;
        try {
            if ($amount <= 0) {
                return ['error' => true, 'msg' => 'Invalid amount'];
            }
            if (empty($users_id)) {
                return ['error' => true, 'msg' => 'Missing users_id'];
            }
            if (!self::acquireProcessingLock($lockKey)) {
                return ['error' => true, 'msg' => 'Could not acquire processing lock'];
            }

            if (Anet_webhook_log::alreadyProcessed($uniq_key, $transactionId)) {
                _error_log("[Authorize.Net] Duplicate processing prevented (uniq_key={$uniq_key}, transactionId={$transactionId})");
                return ['error' => false, 'msg' => 'Already processed', 'duplicate' => true];
            }

            $existingLog = Anet_webhook_log::getExistingLog($uniq_key, $transactionId);
            if (!empty($existingLog['id'])) {
                _error_log("[Authorize.Net] Duplicate processing prevented by existing log (uniq_key={$uniq_key}, transactionId={$transactionId})");
                return ['error' => false, 'msg' => 'Already received', 'duplicate' => true, 'logId' => (int)$existingLog['id']];
            }

            $logId = Anet_webhook_log::createIfNotExists($uniq_key, $eventType, $payload, $users_id, $transactionId);
            _error_log("[Authorize.Net] Webhook log created id=$logId");

            if (empty($logId)) {
                return ['error' => true, 'msg' => 'Could not create webhook log'];
            }

            $walletPlugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
            if (!$walletPlugin) {
                return ['error' => true, 'msg' => 'YPTWallet plugin not enabled'];
            }

            _error_log("[Authorize.Net] Calling addBalance users_id=$users_id amount=$amount description=$description");
            $walletPlugin->addBalance($users_id, (float)$amount, $description);
            _error_log("[Authorize.Net] addBalance call completed for users_id=$users_id amount=$amount");

            if (!empty($logId)) {
                $global['bypassSameDomainCheck'] = 1;
                $log = new Anet_webhook_log($logId);
                $log->setProcessed(1);
                $log->setModified_php_time(time());
                $log->save();
                _error_log("[Authorize.Net] Log marked as processed id=$logId");
            }

            return ['error' => false, 'logId' => (int)$logId];
        } catch (Throwable $e) {
            _error_log('[Authorize.Net] Exception in processSinglePayment: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        } finally {
            self::releaseProcessingLock($lockKey);
        }
    }

    /**
     * Process a subscription charge: ensure subscription is active, then credit wallet and log.
     *
     * @param string $subscriptionId  Authorize.Net ARB subscription ID.
     * @param int    $users_id        Internal user ID to credit.
     * @param float  $amount          Amount to credit.
     * @param string $uniq_key        Unique key for deduplication.
     * @param string $eventType       Webhook event type.
     * @param array  $payload         Raw payload to store.
     * @param string $description     Wallet description (default = 'Authorize.Net subscription charge').
     * @return array{error:bool,msg?:string,active?:bool,status?:string,logId?:int}
     */
    public static function processSubscriptionCharge(
        string $subscriptionId,
        int $users_id,
        float $amount,
        string $uniq_key,
        string $eventType,
        array $payload = [],
        string $description = 'Authorize.Net subscription charge'
    ): array {
        try {
            if (empty($subscriptionId)) {
                return ['error' => true, 'msg' => 'Missing subscriptionId'];
            }

            // Optional: verify subscription is still active
            $statusCheck = self::isSubscriptionActive($subscriptionId);
            if ($statusCheck['error']) {
                return ['error' => true, 'msg' => 'Failed to check subscription status: ' . ($statusCheck['msg'] ?? '')];
            }
            if (!$statusCheck['active']) {
                return [
                    'error'  => true,
                    'active' => false,
                    'status' => $statusCheck['status'] ?? '',
                    'msg'    => 'Subscription is not active'
                ];
            }

            // Reuse the one-time processor for wallet + log
            $res = self::processSinglePayment($users_id, $amount, $uniq_key, $eventType, $payload, $description);
            if ($res['error']) {
                return $res;
            }

            // Attach status info for caller convenience
            $res['active'] = true;
            $res['status'] = $statusCheck['status'] ?? 'active';
            return $res;
        } catch (Throwable $e) {
            _error_log('[Authorize.Net] Exception in processSubscriptionCharge: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Process a subscription charge and associate it with a subscription plan.
     *
     * @param array $analysis Result from analyzeTransactionFromWebhook()
     * @param string $uniq_key
     * @param string $eventType
     * @param array $payload
     * @return array
     */
    public static function processSubscriptionChargeWithPlan(array $analysis, string $uniq_key, string $eventType, array $payload): array
    {
        try {
            if (empty($analysis['subscriptionId'])) {
                return ['error' => true, 'msg' => 'Missing subscriptionId'];
            }

            // First process the payment
            $result = self::processSubscriptionCharge(
                $analysis['subscriptionId'],
                $analysis['users_id'],
                $analysis['amount'],
                $uniq_key,
                $eventType,
                $payload,
                'Authorize.Net subscription charge'
            );

            if ($result['error']) {
                return $result;
            }

            // If we have a plans_id, process the subscription plan
            if (!empty($analysis['plans_id'])) {
                try {
                    // Load subscription plan
                    require_once $global['systemRootPath'] . 'plugin/Subscription/Objects/SubscriptionPlansTable.php';
                    $plan = new SubscriptionPlansTable($analysis['plans_id']);

                    if (!empty($plan->getId())) {
                        // You might want to extend user subscription here
                        // This depends on your subscription management logic
                        _error_log("[AuthorizeNet] Processing subscription charge for plan: " . $analysis['plans_id']);

                        $result['plans_id'] = $analysis['plans_id'];
                        $result['plan_name'] = $plan->getName();
                    }
                } catch (Exception $e) {
                    _error_log("[AuthorizeNet] Error processing subscription plan: " . $e->getMessage());
                    // Don't fail the entire process if plan processing fails
                }
            }

            return $result;
        } catch (Throwable $e) {
            _error_log('[AuthorizeNet] Exception in processSubscriptionChargeWithPlan: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        }
    }

    private static function buildWebhookUniqKey(string $eventType, string $transactionId): string
    {
        return sha1($eventType . $transactionId);
    }

    private static function acquireProcessingLock(string $lockKey, int $timeoutSeconds = 10): bool
    {
        if ($lockKey === '') {
            return true;
        }

        $lockName = 'anet_txn_' . sha1($lockKey);
        $res = sqlDAL::readSql("SELECT GET_LOCK(?, ?) AS locked", "si", [$lockName, $timeoutSeconds], true);
        $row = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);

        return !empty($row['locked']);
    }

    private static function releaseProcessingLock(string $lockKey): void
    {
        if ($lockKey === '') {
            return;
        }

        $lockName = 'anet_txn_' . sha1($lockKey);
        $res = sqlDAL::readSql("SELECT RELEASE_LOCK(?) AS released", "s", [$lockName], true);
        sqlDAL::close($res);
    }

    public static function generatePendingReference(): string
    {
        return substr('p' . base_convert(time(), 10, 36) . substr(md5(uniqid('', true)), 0, 10), 0, 20);
    }

    public static function createPendingPayment(int $users_id, float $amount, array $metadata = [], string $currency = 'USD'): array
    {
        if ($users_id <= 0) {
            return ['error' => true, 'msg' => 'Missing users_id'];
        }
        if ($amount <= 0) {
            return ['error' => true, 'msg' => 'Invalid amount'];
        }

        $refId = self::generatePendingReference();
        $metadata['pending_ref'] = $refId;
        $id = Anet_pending_payment::createPending($refId, $users_id, $amount, $metadata, $currency);
        if (empty($id)) {
            return ['error' => true, 'msg' => 'Could not create pending payment'];
        }

        return [
            'error'   => false,
            'id'      => $id,
            'refId'   => $refId,
            'amount'  => $amount,
            'users_id'=> $users_id,
            'metadata'=> $metadata,
        ];
    }

    private static function getPendingRecordFromTxnInfo(array $txnInfo): ?array
    {
        $refCandidates = [];
        if (!empty($txnInfo['transRefId'])) {
            $refCandidates[] = (string)$txnInfo['transRefId'];
        }
        if (!empty($txnInfo['metadata']['pending_ref'])) {
            $refCandidates[] = (string)$txnInfo['metadata']['pending_ref'];
        }
        if (!empty($txnInfo['orderDescription'])) {
            $tmp = json_decode((string)$txnInfo['orderDescription'], true);
            if (!empty($tmp['pending_ref'])) {
                $refCandidates[] = (string)$tmp['pending_ref'];
            }
        }

        foreach (array_unique(array_filter($refCandidates)) as $refId) {
            $row = Anet_pending_payment::getFromRefId($refId);
            if (!empty($row)) {
                return $row;
            }
        }

        return null;
    }

    private static function markPendingPaymentFromTxnInfo(array $txnInfo, string $transactionId): void
    {
        $pending = self::getPendingRecordFromTxnInfo($txnInfo);
        if (!empty($pending['id'])) {
            Anet_pending_payment::markProcessedById((int)$pending['id'], $transactionId);
        }
    }

    private static function createOrRenewPlanSubscription(array $analysis, string $transactionId): ?array
    {
        if (empty($analysis['plans_id'])) {
            return null;
        }

        _error_log('[Authorize.Net] Creating subscription | plans_id=' . $analysis['plans_id'] . ' | users_id=' . $analysis['users_id']);
        $existingCheck = self::checkUserActiveSubscriptions(
            (int)$analysis['users_id'],
            (string)$analysis['plans_id']
        );

        if (empty($existingCheck['error']) && !empty($existingCheck['hasActivePlanSubscription'])) {
            _error_log('[Authorize.Net] User already has active subscription for plans_id=' . $analysis['plans_id']);
            return [
                'error'                 => false,
                'subscriptionId'        => 'existing',
                'msg'                   => 'User already has active subscription for this plan',
                'existingSubscriptions' => $existingCheck['activeSubscriptions'] ?? [],
            ];
        }

        $sp = new SubscriptionPlansTable((int)$analysis['plans_id']);
        $subscription_name = $sp->getName() ?? 'Subscription';

        $subscriptionMetadata = [
            'users_id'           => (int)$analysis['users_id'],
            'plans_id'           => (int)$analysis['plans_id'],
            'subscription_name'  => $subscription_name,
            'initial_payment_id' => $transactionId,
        ];

        $bi = YPTWallet::getBillingInterval($sp->getHow_many_days() ?? 30);
        $subscriptionResult = self::createSubscription(
            (int)$analysis['users_id'],
            (float)$analysis['amount'],
            $subscriptionMetadata,
            $bi->authnetInterval,
            $bi->authnetUnit
        );

        Subscription::renew(
            (int)$analysis['users_id'],
            (int)$analysis['plans_id'],
            SubscriptionTable::$gatway_authorize,
            $subscriptionResult['subscriptionId'] ?? '',
            $subscriptionResult
        );

        if (!empty($subscriptionResult['error'])) {
            _error_log('[Authorize.Net] Subscription creation FAILED: ' . ($subscriptionResult['msg'] ?? ''));
        } else {
            _error_log('[Authorize.Net] Subscription created | subscriptionId=' . ($subscriptionResult['subscriptionId'] ?? 'n/a'));
        }

        return $subscriptionResult;
    }

    public static function processApprovedTransaction(
        string $transactionId,
        ?string $uniq_key = null,
        string $eventType = 'net.authorize.payment.authcapture.created',
        array $payload = []
    ): array {
        if (trim($transactionId) === '') {
            return ['error' => true, 'msg' => 'Missing transactionId'];
        }

        $uniq_key = $uniq_key ?: self::buildWebhookUniqKey($eventType, $transactionId);
        $txnInfo = self::getTransactionDetails($transactionId);
        if (!empty($txnInfo['error'])) {
            return ['error' => true, 'msg' => $txnInfo['msg'] ?? 'Could not fetch transaction details', 'txnInfo' => $txnInfo];
        }
        $pending = self::getPendingRecordFromTxnInfo($txnInfo);

        if (Anet_webhook_log::alreadyProcessed($uniq_key, $transactionId)) {
            if (!empty($pending['id'])) {
                Anet_pending_payment::markProcessedById((int)$pending['id'], $transactionId);
            }
            return ['error' => false, 'msg' => 'Already processed', 'duplicate' => true, 'uniq_key' => $uniq_key, 'txnInfo' => $txnInfo];
        }

        $analysis = self::analyzeTransactionFromWebhook($payload, $txnInfo['raw'] ?? null);
        if (!empty($pending['users_id']) && empty($analysis['users_id'])) {
            $analysis['users_id'] = (int)$pending['users_id'];
        }
        if (!empty($pending['plans_id']) && empty($analysis['plans_id'])) {
            $analysis['plans_id'] = (int)$pending['plans_id'];
        }
        if (isset($txnInfo['users_id']) && (int)$txnInfo['users_id'] > 0) {
            $analysis['users_id'] = (int)$txnInfo['users_id'];
        }
        if (isset($txnInfo['amount'])) {
            $analysis['amount'] = (float)$txnInfo['amount'];
        }
        if (!empty($txnInfo['currency'])) {
            $analysis['currency'] = $txnInfo['currency'];
        }
        if (array_key_exists('isApproved', $txnInfo)) {
            $analysis['isApproved'] = (bool)$txnInfo['isApproved'];
        }
        if (isset($txnInfo['plans_id']) && (int)$txnInfo['plans_id'] > 0) {
            $analysis['plans_id'] = (int)$txnInfo['plans_id'];
        }

        if (empty($analysis['users_id']) || (int)$analysis['users_id'] <= 0) {
            return ['error' => true, 'msg' => 'Missing users_id', 'analysis' => $analysis, 'txnInfo' => $txnInfo];
        }
        if (empty($analysis['amount']) || (float)$analysis['amount'] <= 0) {
            return ['error' => true, 'msg' => 'Missing amount', 'analysis' => $analysis, 'txnInfo' => $txnInfo];
        }
        if (empty($analysis['isApproved'])) {
            return ['error' => true, 'msg' => 'Transaction not approved', 'analysis' => $analysis, 'txnInfo' => $txnInfo];
        }

        $currency   = strtoupper($analysis['currency'] ?? $txnInfo['currency'] ?? 'USD');
        $amountFmt  = number_format((float)$analysis['amount'], 2);
        $txnIdShort = $transactionId;

        $description = !empty($analysis['plans_id'])
            ? "Authorize.Net subscription — plan #{$analysis['plans_id']} | txn {$txnIdShort} | {$currency} {$amountFmt}"
            : "Authorize.Net payment | txn {$txnIdShort} | {$currency} {$amountFmt}";

        $paymentResult = self::processSinglePayment(
            (int)$analysis['users_id'],
            (float)$analysis['amount'],
            $uniq_key,
            $eventType,
            $payload,
            $description,
            $transactionId
        );
        if (!empty($paymentResult['error'])) {
            return $paymentResult;
        }
        self::markPendingPaymentFromTxnInfo($txnInfo, $transactionId);

        $subscriptionResult = self::createOrRenewPlanSubscription($analysis, $transactionId);

        return [
            'error'              => false,
            'uniq_key'           => $uniq_key,
            'analysis'           => $analysis,
            'txnInfo'            => $txnInfo,
            'logId'              => $paymentResult['logId'] ?? null,
            'subscriptionResult' => $subscriptionResult,
        ];
    }

    public static function getRecentCustomerTransactions(string $customerProfileId, int $limit = 10): array
    {
        try {
            if (trim($customerProfileId) === '') {
                return ['error' => true, 'msg' => 'Missing customerProfileId'];
            }

            $merchantAuthentication = self::getMerchantAuthentication();
            $environment            = self::getEnvironment();

            $sorting = new AnetAPI\TransactionListSortingType();
            $sorting->setOrderBy('id');
            $sorting->setOrderDescending(true);

            $paging = new AnetAPI\PagingType();
            $paging->setLimit($limit);
            $paging->setOffset(1);

            $request = new AnetAPI\GetTransactionListForCustomerRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setCustomerProfileId($customerProfileId);
            $request->setSorting($sorting);
            $request->setPaging($paging);

            $controller = new AnetController\GetTransactionListForCustomerController($request);
            $response   = $controller->executeWithApiResponse($environment);

            if (
                $response instanceof AnetAPI\GetTransactionListResponse &&
                $response->getMessages() &&
                $response->getMessages()->getResultCode() === 'Ok'
            ) {
                return [
                    'error'        => false,
                    'transactions' => (array)$response->getTransactions(),
                    'total'        => (int)$response->getTotalNumInResultSet(),
                ];
            }

            return ['error' => true, 'msg' => self::extractSdkError($response), 'transactions' => []];
        } catch (Throwable $e) {
            _error_log('[Authorize.Net] Exception in getRecentCustomerTransactions: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage(), 'transactions' => []];
        }
    }

    public static function findRecentApprovedTransactionForUser(int $users_id, ?float $expectedAmount = null, int $lookbackSeconds = 1800): array
    {
        try {
            if ($users_id <= 0) {
                return ['error' => true, 'msg' => 'Missing users_id'];
            }

            $customerProfileId = self::getOrCreateCustomerProfile($users_id);
            if (empty($customerProfileId)) {
                return ['error' => true, 'msg' => 'Customer profile not found'];
            }

            $transactions = self::getRecentCustomerTransactions($customerProfileId, 10);
            if (!empty($transactions['error'])) {
                return $transactions;
            }

            $minTimestamp = time() - max(60, $lookbackSeconds);
            foreach ((array)$transactions['transactions'] as $summary) {
                if (!$summary || !method_exists($summary, 'getTransId')) {
                    continue;
                }

                $transactionId = (string)$summary->getTransId();
                if ($transactionId === '') {
                    continue;
                }

                $submitTime = method_exists($summary, 'getSubmitTimeUTC') ? $summary->getSubmitTimeUTC() : null;
                if ($submitTime instanceof DateTimeInterface && $submitTime->getTimestamp() < $minTimestamp) {
                    continue;
                }

                $txnInfo = self::getTransactionDetails($transactionId);
                if (!empty($txnInfo['error']) || empty($txnInfo['isApproved'])) {
                    continue;
                }
                if ((int)($txnInfo['users_id'] ?? 0) !== $users_id) {
                    continue;
                }
                if ($expectedAmount !== null && abs(((float)$txnInfo['amount']) - $expectedAmount) > 0.01) {
                    continue;
                }

                return [
                    'error'         => false,
                    'transactionId' => $transactionId,
                    'txnInfo'       => $txnInfo,
                ];
            }

            return ['error' => true, 'msg' => 'No recent approved transaction found'];
        } catch (Throwable $e) {
            _error_log('[Authorize.Net] Exception in findRecentApprovedTransactionForUser: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        }
    }

    public static function reconcilePendingPayment(array $pending): array
    {
        $pendingId = (int)($pending['id'] ?? 0);
        $users_id = (int)($pending['users_id'] ?? 0);
        $refId = (string)($pending['ref_id'] ?? '');
        $amount = (float)($pending['amount'] ?? 0);

        if ($pendingId <= 0 || $users_id <= 0 || $refId === '') {
            return ['error' => true, 'msg' => 'Invalid pending payment payload'];
        }

        if (($pending['status'] ?? '') === 'processed') {
            return ['error' => false, 'msg' => 'Already processed', 'pendingId' => $pendingId];
        }

        Anet_pending_payment::markChecked($pendingId, 'reconciling');

        $customerProfileId = self::getOrCreateCustomerProfile($users_id);
        if (empty($customerProfileId)) {
            Anet_pending_payment::markChecked($pendingId, 'pending', 'Customer profile not found');
            return ['error' => true, 'msg' => 'Customer profile not found', 'pendingId' => $pendingId];
        }

        $transactions = self::getRecentCustomerTransactions($customerProfileId, 15);
        if (!empty($transactions['error'])) {
            Anet_pending_payment::markChecked($pendingId, 'pending', $transactions['msg'] ?? 'Could not list transactions');
            return $transactions + ['pendingId' => $pendingId];
        }

        foreach ((array)$transactions['transactions'] as $summary) {
            if (!$summary || !method_exists($summary, 'getTransId')) {
                continue;
            }
            $transactionId = (string)$summary->getTransId();
            if ($transactionId === '') {
                continue;
            }

            $txnInfo = self::getTransactionDetails($transactionId);
            if (!empty($txnInfo['error']) || empty($txnInfo['isApproved'])) {
                continue;
            }
            if (((string)($txnInfo['transRefId'] ?? '')) !== $refId && ((string)($txnInfo['metadata']['pending_ref'] ?? '')) !== $refId) {
                continue;
            }
            if ($amount > 0 && abs(((float)$txnInfo['amount']) - $amount) > 0.01) {
                continue;
            }

            $result = self::processApprovedTransaction($transactionId);
            if (empty($result['error']) || !empty($result['duplicate'])) {
                Anet_pending_payment::markProcessedById($pendingId, $transactionId);
            } else {
                Anet_pending_payment::markChecked($pendingId, 'pending', $result['msg'] ?? 'Could not process approved transaction');
            }
            return $result + ['pendingId' => $pendingId, 'transactionId' => $transactionId];
        }

        Anet_pending_payment::markChecked($pendingId, 'pending', 'No matching approved transaction found yet');
        return ['error' => true, 'msg' => 'No matching approved transaction found yet', 'pendingId' => $pendingId];
    }

    public static function reconcilePendingPayments(int $limit = 20, int $maxAgeSeconds = 3600): array
    {
        $rows = Anet_pending_payment::getRecentOpen($limit, $maxAgeSeconds);
        $results = [];
        foreach ($rows as $row) {
            $results[] = self::reconcilePendingPayment($row);
        }
        return ['error' => false, 'total' => count($results), 'results' => $results];
    }

    public static function getDefaultPaymentProfileId(string $customerProfileId): ?string
    {
        $profile = self::getCustomerProfile($customerProfileId);
        if (empty($profile) || empty($profile['paymentProfiles'])) {
            return null;
        }
        // try default first
        foreach ($profile['paymentProfiles'] as $pp) {
            if (!empty($pp['defaultPaymentProfile'])) {
                return (string)$pp['customerPaymentProfileId'];
            }
        }
        // otherwise first one
        return (string)$profile['paymentProfiles'][0]['customerPaymentProfileId'];
    }

    /**
     * Create a recurring subscription (ARB) and store custom metadata.
     *
     * NOTE: ARB does not support arbitrary key/value pairs.
     * I stuff metadata into:
     *   - Order::invoiceNumber (20 chars) → use it for plans_id or short code
     *   - Order::description   (255 chars) → JSON-encoded metadata (trimmed)
     *
     * @param int    $users_id
     * @param float  $amount
     * @param array  $metadata            e.g. ['plans_id' => '123', 'subscription_name' => 'Premium']
     * @param int    $intervalLength      e.g. 1
     * @param string $intervalUnit        'months' or 'days'
     * @param int    $totalOccurrences    Total number of charges (9999 = indefinite)
     * @param string $startDate           Start date (default: next interval)
     * @return array{error:bool,msg?:string,subscriptionId?:string,storedMeta?:array}
     */
    public static function createSubscription(
        int $users_id,
        float $amount,
        array $metadata = [],
        int $intervalLength = 1,
        string $intervalUnit = 'days',
    ): array {
        $totalOccurrences = 9999;
        try {
            if ($amount <= 0) {
                return ['error' => true, 'msg' => 'Invalid amount'];
            }
            if ($intervalLength <= 0) {
                return ['error' => true, 'msg' => 'Invalid interval length'];
            }
            if ($intervalUnit !== 'months' && $intervalUnit !== 'days') {
                return ['error' => true, 'msg' => 'Invalid interval unit (use months or days)'];
            }

            // Ensure customer profile exists
            $profileId = self::getOrCreateCustomerProfile($users_id);
            if (empty($profileId)) {
                return ['error' => true, 'msg' => 'Customer profile not found'];
            }

            $customerPaymentProfileId = self::getDefaultPaymentProfileId($profileId);
            if (empty($customerPaymentProfileId)) {
                return ['error' => true, 'msg' => 'No payment profile on file. User must complete payment first.'];
            }

            $merchantAuthentication = self::getMerchantAuthentication();
            $environment            = self::getEnvironment();

            // Build subscription object
            $subscription = new ARBSubscriptionType();
            $subscriptionName = $metadata['subscription_name'] ?? "Subscription - User {$users_id} to plan {$metadata['plans_id']}";
            $subscription->setName($subscriptionName);

            // Interval / schedule
            $interval = new IntervalAType();
            $interval->setLength($intervalLength);
            $interval->setUnit($intervalUnit);

            $schedule = new PaymentScheduleType();
            $schedule->setInterval($interval);

            // Set start date
            $startDate = date('Y-m-d', strtotime("+{$intervalLength} {$intervalUnit}"));
            $schedule->setStartDate(new DateTime($startDate));
            $schedule->setTotalOccurrences($totalOccurrences);

            $subscription->setPaymentSchedule($schedule);
            $subscription->setAmount($amount);

            // Attach profile (payment profile must already exist)
            $profile = new CustomerProfileIdType();
            $profile->setCustomerProfileId($profileId);
            $profile->setCustomerPaymentProfileId($customerPaymentProfileId);
            $subscription->setProfile($profile);

            // ---- Metadata encoding ----
            $planId = $metadata['plans_id'] ?? ($metadata['plan_id'] ?? null);

            $order = new OrderType();
            if (!empty($planId)) {
                // invoiceNumber length limit = 20
                $order->setInvoiceNumber(substr((string)$planId, 0, 20));
            }

            // description length limit = 255
            $metaJson = substr(json_encode($metadata, JSON_UNESCAPED_UNICODE), 0, 255);
            $order->setDescription($metaJson);

            $subscription->setOrder($order);

            // Build request
            $request = new ARBCreateSubscriptionRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setSubscription($subscription);

            // Add a refId for logging (optional, 20 chars max)
            $request->setRefId(substr('sub_' . $users_id . '_' . time(), 0, 20));

            $controller = new ARBCreateSubscriptionController($request);

            /** @var ARBCreateSubscriptionResponse $response */
            $response = $controller->executeWithApiResponse($environment);

            if (
                $response &&
                $response->getMessages()->getResultCode() === 'Ok' &&
                method_exists($response, 'getSubscriptionId') &&
                $response->getSubscriptionId()
            ) {
                _error_log("[AuthorizeNet] Subscription created successfully: " . $response->getSubscriptionId());
                return [
                    'error'          => false,
                    'subscriptionId' => $response->getSubscriptionId(),
                    'storedMeta'     => ['invoiceNumber' => $order->getInvoiceNumber(), 'description' => $metaJson]
                ];
            }

            return ['error' => true, 'msg' => self::extractSdkError($response)];
        } catch (Throwable $e) {
            _error_log('[AuthorizeNet] Exception in createSubscription: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Check whether a subscription is active.
     *
     * @param string $subscriptionId
     * @return array{error:bool,active?:bool,status?:string,msg?:string}
     */
    public static function isSubscriptionActive(string $subscriptionId): array
    {
        if (trim($subscriptionId) === '') {
            return ['error' => true, 'msg' => 'Missing subscriptionId'];
        }

        try {
            $merchantAuthentication = self::getMerchantAuthentication();
            $environment            = self::getEnvironment();

            $request = new ARBGetSubscriptionStatusRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setSubscriptionId($subscriptionId);

            $controller = new ARBGetSubscriptionStatusController($request);

            /** @var ARBGetSubscriptionStatusResponse $response */
            $response = $controller->executeWithApiResponse($environment);

            if (
                $response &&
                $response->getMessages()->getResultCode() === 'Ok' &&
                method_exists($response, 'getStatus')
            ) {
                $status = (string) $response->getStatus();
                return [
                    'error'  => false,
                    'active' => ($status === 'active'),
                    'status' => $status
                ];
            }

            return ['error' => true, 'msg' => self::extractSdkError($response)];
        } catch (Throwable $e) {
            _error_log('[AuthorizeNet] Exception in isSubscriptionActive: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        }
    }
    /**
     * Generate Accept Hosted token (payment form). Returns token + redirect URL.
     * Based on official sample: PaymentTransactions/get-hosted-payment-page.php
     */
    public static function generateHostedPaymentPage(float $amount, array $metadata = [], string $currency = 'USD'): array
    {
        global $global;

        if ($amount <= 0) {
            return ['error' => true, 'msg' => 'Invalid amount'];
        }

        $merchantAuthentication = self::getMerchantAuthentication();
        $environment            = self::getEnvironment();

        $users_id          = User::getId();
        $customerProfileId = self::getOrCreateCustomerProfile($users_id);

        _error_log('[AuthorizeNet] User ID: ' . $users_id);
        _error_log('[AuthorizeNet] CustomerProfileId: ' . $customerProfileId);

        // Transaction
        $txn = new AnetAPI\TransactionRequestType();
        $txn->setTransactionType('authCaptureTransaction');
        $txn->setAmount($amount);
        $txn->setCurrencyCode($currency);

        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber(substr((string)$metadata['plans_id'], 0, 20));
        $order->setDescription(substr(json_encode($metadata, JSON_UNESCAPED_UNICODE), 0, 255));
        $txn->setOrder($order);

        foreach ($metadata as $k => $v) {
            $uf = new AnetAPI\UserFieldType();
            $uf->setName($k);
            $uf->setValue($v);
            $txn->addToUserFields($uf);
        }

        // Embed users_id in customer.id so getTransactionDetails can retrieve it.
        // This is the most reliable recovery path: getTransactionDetails returns
        // transaction.customer.id but does NOT return userFields.
        $customerType = new AnetAPI\CustomerDataType();
        $customerType->setId((string)$users_id);
        $txn->setCustomer($customerType);

        if (!empty($customerProfileId)) {
            $profilePaymentType = new AnetAPI\CustomerProfilePaymentType();
            $profilePaymentType->setCustomerProfileId($customerProfileId);
            $txn->setProfile($profilePaymentType);
            _error_log('[AuthorizeNet] Attached CustomerProfileId to transaction: ' . $customerProfileId);
        } else {
            _error_log('[AuthorizeNet] No customer profile found for user ID: ' . $users_id);
        }

        // Request
        $request = new AnetAPI\GetHostedPaymentPageRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setTransactionRequest($txn);
        if (!empty($metadata['pending_ref'])) {
            $request->setRefId(substr((string)$metadata['pending_ref'], 0, 20));
        }

        // Settings
        $settings = [];

        $returnOpt = new AnetAPI\SettingType();
        $returnOpt->setSettingName('hostedPaymentReturnOptions');
        $returnOpt->setSettingValue(json_encode([
            'showReceipt'   => false,
            'url'           => "{$global['webSiteRootURL']}plugin/AuthorizeNet/acceptHostedReturn.php",
            'urlText'       => 'Return to site',
            'cancelUrl'     => "{$global['webSiteRootURL']}plugin/AuthorizeNet/acceptHostedReturn.php?cancel=1",
            'cancelUrlText' => 'Cancel'
        ]));
        $settings[] = $returnOpt;

        $createProfileSetting = new AnetAPI\SettingType();
        $createProfileSetting->setSettingName('hostedPaymentCustomerOptions');
        $createProfileSetting->setSettingValue(json_encode([
            'showEmail' => false,
            'requiredEmail' => false,
            'addPaymentProfile' => true
        ]));
        $settings[] = $createProfileSetting;

        // Uncomment if using iframe
        /*
        $iframe = new AnetAPI\SettingType();
        $iframe->setSettingName('hostedPaymentIFrameCommunicatorUrl');
        $iframe->setSettingValue(json_encode([
            'url' => $global['webSiteRootURL'] . 'plugin/AuthorizeNet/iframeCommunicator.html'
        ]));
        $settings[] = $iframe;
        */

        $request->setHostedPaymentSettings($settings);

        // Call API
        $controller = new AnetController\GetHostedPaymentPageController($request);
        /** @var GetHostedPaymentPageResponse $response */
        $response = $controller->executeWithApiResponse($environment);
        _error_log('[AuthorizeNet] Payment Page API response: ' . json_encode($response));

        $token = (method_exists($response, 'getToken')) ? $response->getToken() : null;

        if ($response && $response->getMessages()->getResultCode() === 'Ok' && !empty($token)) {
            return [
                'error' => false,
                'token' => $token,
                'url'   => self::getHostedBaseUrl('/payment/payment'),
                'refId' => $metadata['pending_ref'] ?? null,
            ];
        }

        return ['error' => true, 'msg' => self::extractSdkError($response)];
    }

    /**
     * Generate Accept Hosted token to manage card/profile.
     */
    public static function generateManageProfileToken(): array
    {
        $merchantAuthentication = self::getMerchantAuthentication();
        $environment            = self::getEnvironment();

        $users_id          = User::getId();
        $customerProfileId = self::getOrCreateCustomerProfile($users_id);
        if (empty($customerProfileId)) {
            return ['error' => true, 'msg' => 'No customer profile found'];
        }

        $request = new AnetAPI\GetHostedProfilePageRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setCustomerProfileId($customerProfileId);

        $controller = new AnetController\GetHostedProfilePageController($request);
        /** @var GetHostedProfilePageResponse $response */
        $response = $controller->executeWithApiResponse($environment);

        $token = (method_exists($response, 'getToken')) ? $response->getToken() : null;

        if ($response && $response->getMessages()->getResultCode() === 'Ok' && !empty($token)) {
            return [
                'error' => false,
                'token' => $token,
                'url'   => self::getHostedBaseUrl('/profile/manage')
            ];
        }

        return ['error' => true, 'msg' => self::extractSdkError($response)];
    }

    public static function getCustomerProfileIdByMerchantCustomerId($merchantCustomerId)
    {
        if (empty($merchantCustomerId)) {
            return false;
        }

        $merchantAuthentication = self::getMerchantAuthentication();
        $environment            = self::getEnvironment();

        $request = new AnetAPI\GetCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setMerchantCustomerId($merchantCustomerId);

        $controller = new AnetController\GetCustomerProfileController($request);
        /** @var GetCustomerProfileResponse $response */
        $response   = $controller->executeWithApiResponse($environment);

        $profile = (method_exists($response, 'getProfile')) ? $response->getProfile() : null;

        if (
            $response &&
            $response->getMessages()->getResultCode() === 'Ok' &&
            $profile &&
            $profile->getCustomerProfileId()
        ) {
            return $profile->getCustomerProfileId();
        }

        _error_log("[AuthorizeNet] Failed to get CustomerProfileId by MerchantCustomerId: {$merchantCustomerId} | Error: " . self::extractSdkError($response));
        return false;
    }



    /* ---------- Customer Profile ---------- */

    public static function getOrCreateCustomerProfile(int $users_id)
    {
        $user = new User($users_id);
        $profileId = $user->getExternalOption('authorizeNetcustomerProfileId');
        if (!empty($profileId)) {
            _error_log("[AuthorizeNet] Using cached profileId {$profileId} for user {$users_id}");
            return $profileId;
        }

        $merchantAuthentication = self::getMerchantAuthentication();

        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setDescription('AVideo User ' . $users_id);
        $customerProfile->setEmail($user->getEmail());
        $customerProfile->setMerchantCustomerId((string)$users_id); // force string

        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setProfile($customerProfile);

        $controller = new AnetController\CreateCustomerProfileController($request);
        /** @var CreateCustomerProfileResponse $response */
        $response = $controller->executeWithApiResponse(self::getEnvironment());

        // Log everything
        _error_log('[AuthorizeNet] CreateCustomerProfile RESPONSE: ' . json_encode($response));

        if ($response instanceof CreateCustomerProfileResponse && $response->getMessages()->getResultCode() === 'Ok') {
            $profileId = $response->getCustomerProfileId();
            _error_log("[AuthorizeNet] Profile created: {$profileId} for user {$users_id}");
            if (!empty($profileId)) {
                $user->addExternalOptions('authorizeNetcustomerProfileId', $profileId);
                return $profileId;
            }
        } else {
            // Handle duplicate (E00039) or similar
            $err = self::extractSdkError($response);
            _error_log("[AuthorizeNet] CreateCustomerProfile ERROR: {$err}");

            if (stripos($err, 'E00039') !== false) { // duplicate profile
                $existing = self::getCustomerProfileIdByMerchantCustomerId((string)$users_id);
                _error_log("[AuthorizeNet] Duplicate detected. Existing profileId: {$existing}");
                if (!empty($existing)) {
                    $user->addExternalOptions('authorizeNetcustomerProfileId', $existing);
                    return $existing;
                }
            }
        }
        return false;
    }

    /**
     * Get customer profile details including payment profiles
     *
     * @param string $customerProfileId
     * @return array|null
     */
    public static function getCustomerProfile(string $customerProfileId): ?array
    {
        try {
            $merchantAuthentication = self::getMerchantAuthentication();

            $request = new AnetAPI\GetCustomerProfileRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setCustomerProfileId($customerProfileId);

            $controller = new AnetController\GetCustomerProfileController($request);

            /** @var GetCustomerProfileResponse|null $response */
            $response = $controller->executeWithApiResponse(self::getEnvironment());

            // Make the static analyzer happy + runtime safe
            if (!$response instanceof GetCustomerProfileResponse) {
                _error_log('[AuthorizeNet] Empty/invalid profile response');
                return null;
            }

            if ($response->getMessages()->getResultCode() !== 'Ok') {
                $m = $response->getMessages()->getMessage();
                $err = isset($m[0]) ? ($m[0]->getCode() . ' ' . $m[0]->getText()) : 'Unknown error';
                _error_log("[AuthorizeNet] Error getting customer profile: $err");
                return null;
            }

            $profile = $response->getProfile();
            if (!$profile) {
                return null;
            }

            $result = [
                'customerProfileId'   => $profile->getCustomerProfileId(),
                'merchantCustomerId'  => $profile->getMerchantCustomerId(),
                'email'               => $profile->getEmail(),
                'description'         => $profile->getDescription(),
                'paymentProfiles'     => [],
            ];

            $paymentProfiles = $profile->getPaymentProfiles();
            if (!empty($paymentProfiles)) {
                foreach ($paymentProfiles as $pp) {
                    $result['paymentProfiles'][] = [
                        'customerPaymentProfileId' => $pp->getCustomerPaymentProfileId(),
                        'defaultPaymentProfile'    => (bool)$pp->getDefaultPaymentProfile(),
                    ];
                }
            }

            return $result;
        } catch (Throwable $e) {
            _error_log("[AuthorizeNet] Exception getting customer profile: " . $e->getMessage());
            return null;
        }
    }

    /* ---------- Webhooks (REST) ---------- */

    public static function createWebhook(string $webhookUrl, array $eventTypes = ['net.authorize.payment.authcapture.created'])
    {
        return self::restWebhook('POST', 'webhooks', [
            'url'        => $webhookUrl,
            'eventTypes' => $eventTypes,
            'status'     => 'active',
        ]);
    }

    /**
     * Fetch notification delivery history from Authorize.Net.
     * Useful for diagnosing whether webhooks were attempted and if they succeeded.
     *
     * @param string $query  e.g. 'limit=20' or 'limit=10&deliveryStatus=Failed'
     */
    public static function checkNotificationHistory(string $query = 'limit=20', bool $fetchAllDetails = false): array
    {
        $res = self::restWebhook('GET', 'notifications?' . $query);
        if ($res['error']) {
            return $res;
        }

        $body          = $res['body'] ?? [];
        $notifications = $body['notifications'] ?? [];

        // Enrich each entry with the payload
        $enriched = [];
        foreach ($notifications as $n) {
            $entry = [
                'notificationId'  => $n['notificationId'] ?? null,
                'deliveryStatus'  => $n['deliveryStatus'] ?? null,
                'eventType'       => $n['eventType'] ?? null,
                'eventDate'       => $n['eventDate'] ?? null,
                'webhookId'       => $n['webhookId'] ?? null,
            ];

            $status = $n['deliveryStatus'] ?? '';
            $needsDetail = $fetchAllDetails || in_array($status, ['RetryPending', 'Failed'], true);

            if ($needsDetail && !empty($n['notificationId'])) {
                $detail = self::getNotificationDetail($n['notificationId']);
                if (!empty($detail['payload'])) {
                    $payload                 = $detail['payload'];
                    $entry['payload']        = $payload;
                    $entry['transactionId']  = $payload['id'] ?? null;
                    $entry['isTestPing']     = (($payload['responseCode'] ?? -1) === 0
                                                && ($payload['authAmount'] ?? -1) == 0
                                                && ($payload['invoiceNumber'] ?? '') === '0'
                                                && empty($payload['id']));
                    $entry['retryLog']       = $detail['retryLog'] ?? null;
                }
            }

            $enriched[] = $entry;
        }

        return [
            'error'         => false,
            'total'         => count($enriched),
            'notifications' => $enriched,
            'webhookUrl'    => self::getWebhookURL(),
        ];
    }

    /**
     * Fetch full detail of a single Authorize.Net notification (including payload + retry log).
     */
    public static function getNotificationDetail(string $notificationId): array
    {
        $res = self::restWebhook('GET', 'notifications/' . $notificationId);
        if ($res['error']) {
            return $res;
        }
        return $res['body'] ?? [];
    }

    public static function webhookExists(string $webhookUrl)
    {
        $res = self::restWebhook('GET', 'webhooks');
        if ($res['error']) {
            return $res; // real connection or auth error
        }

        foreach ((array) $res['body'] as $wh) {
            if (!empty($wh['url']) && $wh['url'] === $webhookUrl) {
                $wh['error']  = false;
                $wh['exists'] = true;
                return $wh;
            }
        }

        // webhook not found but not an error
        return ['error' => false, 'exists' => false];
    }

    public static function updateWebhookEventTypes(string $webhookId, array $eventTypes)
    {
        return self::restWebhook('PUT', 'webhooks/' . $webhookId, ['eventTypes' => $eventTypes]);
    }

    public static function reactivateWebhook(string $webhookId): array
    {
        $res = self::restWebhook('PUT', 'webhooks/' . $webhookId, ['status' => 'active']);
        _error_log('[AuthorizeNet] reactivateWebhook PUT result'
            . ' webhookId=' . $webhookId
            . ' httpStatus=' . ($res['status'] ?? '?')
            . ' error=' . (!empty($res['error']) ? 'true' : 'false')
            . ' body=' . ($res['raw'] ?? '')
        );
        return $res;
    }

    public static function createWebhookIfNotExists(array $eventTypes = ['net.authorize.payment.authcapture.created'])
    {
        $webhookUrl = AuthorizeNet::getWebhookURL();
        $exists = self::webhookExists($webhookUrl);
        if (!empty($exists['error'])) {
            return $exists; // real error
        }

        // Create if it does not exist
        if (empty($exists['exists'])) {
            return self::createWebhook($webhookUrl, $eventTypes);
        }

        $webhookId = $exists['webhookId'] ?? null;
        _error_log('[AuthorizeNet] createWebhookIfNotExists found webhookId=' . $webhookId . ' status=' . ($exists['status'] ?? 'n/a'));

        $wasInactive = ($exists['status'] ?? '') !== 'active';

        // Reactivate if Authorize.Net deactivated it (e.g. due to repeated endpoint errors)
        if ($wasInactive && !empty($webhookId)) {
            _error_log('[AuthorizeNet] Webhook is inactive — reactivating: ' . $webhookId);
            $reactivate = self::reactivateWebhook($webhookId);
            if (!empty($reactivate['error'])) {
                return $reactivate;
            }
        }

        // Check if event types need updating
        $existingEvents = $exists['eventTypes'] ?? [];
        sort($existingEvents);
        sort($eventTypes);

        if ($existingEvents === $eventTypes) {
            // Re-fetch current state so caller sees the post-reactivation status
            return self::webhookExists($webhookUrl) ?: $exists;
        }

        if (!empty($webhookId)) {
            return self::restWebhook('PUT', 'webhooks/' . $webhookId, [
                'eventTypes' => $eventTypes,
                'status'     => 'active',
            ]);
        }

        return ['error' => true, 'msg' => 'Webhook exists but missing ID', 'status' => 0];
    }

    private static function ensureWebhookOrDie(array $eventTypes = ['net.authorize.payment.authcapture.created']): void
    {
        $url = self::getWebhookURL();
        $res = self::createWebhookIfNotExists($eventTypes);

        if (!empty($res['error'])) {
            self::abortWebhook('Authorize.Net webhook error: ' . ($res['msg'] ?? 'unknown'));
        }
    }

    /**
     * Consulta detalhes de uma transação pelo ID.
     *
     * @param string $transactionId
     * @return array{
     *   error:bool,
     *   msg?:string,
     *   id?:string,
     *   status?:string|null,
     *   type?:string|null,
     *   amount?:float|null,
     *   currency?:string|null,
     *   responseCode?:int|string|null,
     *   authCode?:string|null,
     *   avsResponse?:string|null,
     *   email?:string|null,
     *   invoiceNumber?:string|null,
     *   submitTimeUTC?:string|null,
     *   raw?:mixed
     * }
     */
    public static function getTransactionDetails(string $transactionId): array
    {
        if (trim($transactionId) === '') {
            return ['error' => true, 'msg' => 'Missing transactionId'];
        }

        try {
            $merchantAuthentication = self::getMerchantAuthentication();
            $environment            = self::getEnvironment();

            $request = new AnetAPI\GetTransactionDetailsRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setTransId($transactionId);

            $controller = new AnetController\GetTransactionDetailsController($request);

            /** @var AnetAPI\GetTransactionDetailsResponse|null $response */
            $response = $controller->executeWithApiResponse($environment);

            if (
                $response instanceof AnetAPI\GetTransactionDetailsResponse &&
                $response->getMessages() &&
                $response->getMessages()->getResultCode() === 'Ok' &&
                method_exists($response, 'getTransaction') &&
                ($txn = $response->getTransaction())
            ) {
                /** @var \net\authorize\api\contract\v1\TransactionDetailsType $txn */
                $order        = method_exists($txn, 'getOrder') ? $txn->getOrder() : null;
                $customer     = method_exists($txn, 'getCustomer') ? $txn->getCustomer() : null;
                $submitTime   = method_exists($txn, 'getSubmitTimeUTC') ? $txn->getSubmitTimeUTC() : null;
                $responseCode = $txn->getResponseCode() ?? '';
                $status       = $txn->getTransactionStatus() ?? '';
                $isApproved   = $responseCode == 1 && in_array(strtolower($status), ['capturedpendingsettlement', 'settledsuccessfully'], true);

                // ---- NEW: get description and decode metadata ----
                $orderDescription = ($order && method_exists($order, 'getDescription')) ? (string)$order->getDescription() : null;
                $decodedMeta = [];
                if (!empty($orderDescription)) {
                    $tmp = json_decode($orderDescription, true);
                    if (is_array($tmp)) {
                        $decodedMeta = $tmp;
                    }
                }

                // Extract users_id — three fallbacks in priority order:
                // 1. Order description JSON (most explicit)
                // 2. userFields on the transaction object (set at token time, may not survive Accept Hosted)
                // 3. customer.id (set via CustomerType::setId in generateHostedPaymentPage — most reliable
                //    because getTransactionDetails always returns transaction.customer.id)
                if (isset($decodedMeta['users_id']) && (int)$decodedMeta['users_id'] > 0) {
                    $users_id_from_txn = (int)$decodedMeta['users_id'];
                    _error_log('[AuthorizeNet] users_id source: order description JSON');
                } elseif (($uid = self::extractUsersIdFromTxnRaw($txn)) !== null && $uid > 0) {
                    $users_id_from_txn = $uid;
                    _error_log('[AuthorizeNet] users_id source: userFields');
                } elseif ($customer && method_exists($customer, 'getId') && (int)$customer->getId() > 0) {
                    $users_id_from_txn = (int)$customer->getId();
                    _error_log('[AuthorizeNet] users_id source: customer.id');
                } else {
                    $users_id_from_txn = 0;
                    _error_log('[AuthorizeNet] users_id source: NOT FOUND — orderDesc=' . ($orderDescription ?? 'null'));
                }

                $plans_id_from_txn = isset($decodedMeta['plans_id']) ? (int)$decodedMeta['plans_id'] : 0;

                _error_log('[AuthorizeNet] getTransactionDetails'
                    . ' | txn=' . $transactionId
                    . ' | status=' . $status
                    . ' | responseCode=' . $responseCode
                    . ' | amount=' . $txn->getAuthAmount()
                    . ' | isApproved=' . ($isApproved ? 'true' : 'false')
                    . ' | users_id=' . $users_id_from_txn
                    . ' | plans_id=' . $plans_id_from_txn
                    . ' | orderDescription=' . ($orderDescription ?? 'null')
                );

                return [
                    'error'            => false,
                    'id'               => $transactionId,
                    'status'           => $status,
                    'type'             => $txn->getTransactionType(),
                    'amount'           => $txn->getAuthAmount(),
                    'responseCode'     => $responseCode,
                    'authCode'         => $txn->getAuthCode(),
                    'avsResponse'      => $txn->getAvsResponse(),
                    'email'            => $customer ? $customer->getEmail() : null,
                    'invoiceNumber'    => $order ? $order->getInvoiceNumber() : null,
                    'orderDescription' => $orderDescription,
                    'metadata'         => $decodedMeta,
                    'transRefId'       => method_exists($response, 'getTransrefId') ? $response->getTransrefId() : null,
                    'submitTimeUTC'    => $submitTime ? $submitTime->format('Y-m-d H:i:s') : null,
                    'customer'         => $txn->getCustomer(),
                    'users_id'         => $users_id_from_txn,
                    'plans_id'         => $plans_id_from_txn,
                    'raw'              => $txn,
                    'isApproved'       => $isApproved,
                ];
            }

            return ['error' => true, 'msg' => self::extractSdkError($response)];
        } catch (Throwable $e) {
            _error_log('[Authorize.Net] Exception in getTransactionDetails: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        }
    }



    // 4) Stop execution and return JSON error response
    private static function abortWebhook(string $msg): void
    {
        _error_log('[AuthorizeNet] ' . $msg);
        http_response_code(500);
        die(json_encode(['error' => true, 'msg' => $msg]));
    }

    /* ---------- Helpers ---------- */

    private static function getConfig()
    {
        return AVideoPlugin::getDataObject('AuthorizeNet');
    }

    private static function getMerchantAuthentication(): AnetAPI\MerchantAuthenticationType
    {
        $obj  = self::getConfig();
        $auth = new AnetAPI\MerchantAuthenticationType();
        $auth->setName(trim((string)$obj->apiLoginId));
        $auth->setTransactionKey(trim((string)$obj->transactionKey));
        return $auth;
    }

    private static function getEnvironment()
    {
        $obj = self::getConfig();
        return $obj->sandbox ? ANetEnvironment::SANDBOX : ANetEnvironment::PRODUCTION;
    }

    /**
     * Accept Hosted base (payment/profile).
     */
    private static function getHostedBaseUrl(string $path): string
    {
        $base = self::getConfig()->sandbox
            ? 'https://test.authorize.net'
            : 'https://accept.authorize.net';
        return rtrim($base, '/') . $path;
    }

    /**
     * REST base (webhooks).
     */
    private static function getRestBaseUrl(): string
    {
        return self::getConfig()->sandbox
            ? 'https://apitest.authorize.net/rest/v1/'
            : 'https://api.authorize.net/rest/v1/';
    }

    /**
     * Webhook URL in your app.
     */
    public static function getWebhookURL(): string
    {
        global $global;
        return $global['webSiteRootURL'] . 'plugin/AuthorizeNet/webhook.php';
    }

    /**
     * REST call for webhooks. Returns unified array with error flag.
     */
    private static function restWebhook(string $method, string $path, ?array $payload = null): array
    {
        $url  = self::getRestBaseUrl() . ltrim($path, '/');
        $obj  = self::getConfig();
        $apiLoginId = trim((string)$obj->apiLoginId);
        $transactionKey = trim((string)$obj->transactionKey);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($apiLoginId . ':' . $transactionKey),
        ];

        $optsHttp = [
            'method'        => strtoupper($method),
            'header'        => implode("\r\n", $headers) . "\r\n",
            'ignore_errors' => true,
        ];
        if ($payload !== null) {
            $optsHttp['content'] = json_encode($payload);
        }

        $context = stream_context_create(['http' => $optsHttp]);
        $raw     = @file_get_contents($url, false, $context);
        $status  = 0;
        $respHdr = $http_response_header ?? [];

        foreach ($respHdr as $hdr) {
            if (preg_match('#HTTP/\d\.\d\s+(\d+)#', $hdr, $m)) {
                $status = (int)$m[1];
                break;
            }
        }

        $body    = json_decode($raw, true);
        $errText = ($status < 200 || $status >= 300)
            ? self::extractRestError(['status' => $status, 'body' => $body, 'raw' => $raw])
            : '';

        return [
            'status'   => $status,
            'body'     => $body,
            'raw'      => $raw,
            'headers'  => $respHdr,
            'error'    => $status < 200 || $status >= 300,
            'msg'      => $errText,
            'errorMsg' => $errText
        ];
    }

    private static function extractSdkError($response): string
    {
        if (empty($response)) {
            return 'Empty response';
        }

        if (method_exists($response, 'getMessages') && $response->getMessages()) {
            $msgs = $response->getMessages()->getMessage();
            if (is_array($msgs) && !empty($msgs[0])) {
                $m = $msgs[0];
                return trim(($m->getCode() ?? '') . ' ' . ($m->getText() ?? ''));
            }
        }

        if (method_exists($response, 'getTransactionResponse') && $response->getTransactionResponse()) {
            $tr = $response->getTransactionResponse();
            if (method_exists($tr, 'getErrors') && $tr->getErrors()) {
                $err = $tr->getErrors()[0];
                return trim(($err->getErrorCode() ?? '') . ' ' . ($err->getErrorText() ?? ''));
            }
        }

        return 'Unknown error';
    }

    private static function extractRestError(array $res): string
    {
        $body = $res['body'] ?? [];
        if (is_array($body)) {
            if (!empty($body['message'])) return $body['message'];
            if (!empty($body['reason']))  return $body['reason'];
            if (!empty($body['errors']) && is_array($body['errors'])) {
                $first = reset($body['errors']);
                return is_array($first) ? ($first['message'] ?? json_encode($first)) : (string)$first;
            }
        }
        return $res['raw'] ?? 'Unknown error';
    }

    /* ---------- Plugin metadata ---------- */

    public function getTags()
    {
        return [PluginTags::$MONETIZATION];
    }

    public function getDescription()
    {
        return "Authorize.Net payment gateway integration for AVideo.";
    }

    public function getName()
    {
        return "AuthorizeNet";
    }

    public function getUUID()
    {
        return "authorizenet-uuid-001";
    }

    public function getPluginVersion()
    {
        return "1.2";
    }

    public function updateScript()
    {
        global $global;

        if (AVideoPlugin::compareVersion($this->getName(), "1.2") < 0) {
            $updateFile = $global['systemRootPath'] . 'plugin/AuthorizeNet/install/updateV1.2.sql';
            sqlDal::executeFile($updateFile);
            _error_log('[Authorize.Net] updateScript executed updateV1.2.sql');
        }

        return true;
    }

    public function executeEveryMinute()
    {
        self::reconcilePendingPayments(20, 7200);
    }

    public function getPluginMenu()
    {
        global $global;
        return '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/AuthorizeNet/View/editor.php\')" class="btn btn-primary btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();
        $obj->apiLoginId     = "";
        $obj->transactionKey = "";
        $obj->signatureKey   = "";
        $obj->sandbox        = true;
        $obj->subscriptionButtonLabel = "Subscribe With Credit Card";
        $obj->paymentButtonLabel = "Pay With Credit Card";

        return $obj;
    }

    /**
     * Get all subscriptions for a customer profile
     *
     * @param string $customerProfileId
     * @return array{error:bool,subscriptions:array,msg?:string}
     */
    public static function getCustomerSubscriptions(string $customerProfileId): array
    {
        try {
            if ($customerProfileId === '') {
                return ['error' => true, 'msg' => 'Missing customer profile ID', 'subscriptions' => []];
            }

            $merchantAuthentication = self::getMerchantAuthentication();
            $environment            = self::getEnvironment();

            // Build request
            $request = new AnetAPI\GetCustomerProfileRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setCustomerProfileId($customerProfileId);
            $request->setIncludeIssuerInfo(true);

            $controller = new AnetController\GetCustomerProfileController($request);

            /** @var GetCustomerProfileResponse|false $response */
            $response = $controller->executeWithApiResponse($environment);

            // Ensure type for static analyser and runtime safety
            if (!$response instanceof GetCustomerProfileResponse) {
                return ['error' => true, 'msg' => 'Invalid response type', 'subscriptions' => []];
            }

            if ($response->getMessages()->getResultCode() !== 'Ok') {
                return ['error' => true, 'msg' => self::extractSdkError($response), 'subscriptions' => []];
            }

            /** @var \net\authorize\api\contract\v1\CustomerProfileMaskedType $profile */
            $profile = $response->getProfile();
            if (!$profile) {
                return ['error' => false, 'subscriptions' => []];
            }

            // It might return an array or an object that implements Traversable – cast to array for safety
            $subscriptionIds = [];
            if (method_exists($profile, 'getSubscriptionIds')) {
                $subscriptionIds = (array) call_user_func([$profile, 'getSubscriptionIds']);
            } elseif (is_object($response) && method_exists($response, 'getSubscriptionIds')) { // older binding
                $subscriptionIds = (array) call_user_func([$response, 'getSubscriptionIds']);
            }


            $subscriptions   = [];

            foreach ($subscriptionIds as $subId) {
                $subDetails = self::getSubscriptionDetails((string)$subId);
                if (empty($subDetails['error']) && !empty($subDetails['subscription'])) {
                    // Store only the subscription array, not the whole wrapper
                    $subscriptions[] = $subDetails['subscription'];
                }
            }

            return ['error' => false, 'subscriptions' => $subscriptions];
        } catch (Throwable $e) {
            _error_log('[AuthorizeNet] Exception in getCustomerSubscriptions: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage(), 'subscriptions' => []];
        }
    }

    /**
     * Get detailed subscription information
     *
     * @param string $subscriptionId
     * @return array{error:bool,subscription?:array,msg?:string}
     */
    public static function getSubscriptionDetails(string $subscriptionId): array
    {
        try {
            if ($subscriptionId === '') {
                return ['error' => true, 'msg' => 'Missing subscription ID'];
            }

            $merchantAuthentication = self::getMerchantAuthentication();
            $environment            = self::getEnvironment();

            $request = new ARBGetSubscriptionRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setSubscriptionId($subscriptionId);

            $controller = new ARBGetSubscriptionController($request);

            /** @var ARBGetSubscriptionResponse|false $response */
            $response = $controller->executeWithApiResponse($environment);

            // Static analyser + runtime check
            if (!$response instanceof ARBGetSubscriptionResponse) {
                return ['error' => true, 'msg' => 'Invalid subscription response'];
            }

            if ($response->getMessages()->getResultCode() !== 'Ok') {
                return ['error' => true, 'msg' => self::extractSdkError($response)];
            }

            /** @var \net\authorize\api\contract\v1\ARBSubscriptionMaskedType $subscription */
            $subscription = $response->getSubscription();
            if (!$subscription) {
                return ['error' => true, 'msg' => 'Subscription not found'];
            }

            // Safely unwrap schedule and order (they can be null)
            $schedule = $subscription->getPaymentSchedule();
            $interval = $schedule ? $schedule->getInterval() : null;

            $details = [
                'subscriptionId'    => $subscriptionId,
                'name'              => $subscription->getName(),
                'status'            => $subscription->getStatus(),
                'amount'            => $subscription->getAmount(),
                'interval'          => [
                    'length' => $interval ? $interval->getLength() : null,
                    'unit'   => $interval ? $interval->getUnit()   : null,
                ],
                'startDate'         => $schedule && $schedule->getStartDate()
                    ? $schedule->getStartDate()->format('Y-m-d')
                    : null,
                'totalOccurrences'  => $schedule ? $schedule->getTotalOccurrences() : null,
                'trialOccurrences'  => $schedule ? $schedule->getTrialOccurrences() : null,
                'order'             => null,
                'metadata'          => [],
                'plans_id'          => null,
            ];

            $order = $subscription->getOrder();
            if ($order) {
                $details['order'] = [
                    'invoiceNumber' => $order->getInvoiceNumber(),
                    'description'   => $order->getDescription()
                ];

                // Try to decode metadata from description
                if ($order->getDescription()) {
                    $decodedMeta = json_decode($order->getDescription(), true);
                    if (is_array($decodedMeta)) {
                        $details['metadata'] = $decodedMeta;
                        $details['plans_id'] = $decodedMeta['plans_id'] ?? $order->getInvoiceNumber();
                    } else {
                        $details['plans_id'] = $order->getInvoiceNumber();
                    }
                } else {
                    $details['plans_id'] = $order->getInvoiceNumber();
                }
            }

            return ['error' => false, 'subscription' => $details];
        } catch (Throwable $e) {
            _error_log('[AuthorizeNet] Exception in getSubscriptionDetails: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        }
    }


    /**
     * Check if user has an active subscription for a specific plan
     *
     * @param int $users_id
     * @param string|null $plans_id Optional: check for specific plan
     * @return array{error:bool,hasActiveSubscription:bool,activeSubscriptions:array,msg?:string}
     */
    public static function checkUserActiveSubscriptions(int $users_id, ?string $plans_id = null): array
    {
        try {
            $customerProfileId = self::getOrCreateCustomerProfile($users_id);
            if (empty($customerProfileId)) {
                return ['error' => true, 'msg' => 'Customer profile not found'];
            }

            $subscriptionsResult = self::getCustomerSubscriptions($customerProfileId);
            if ($subscriptionsResult['error']) {
                return $subscriptionsResult;
            }

            $activeSubscriptions = [];
            $hasActiveSubscription = false;
            $hasActivePlanSubscription = false;

            foreach ($subscriptionsResult['subscriptions'] as $sub) {
                if (!$sub['error'] && isset($sub['subscription'])) {
                    $subscription = $sub['subscription'];

                    // Check if subscription is active
                    if (strtolower($subscription['status']) === 'active') {
                        $activeSubscriptions[] = $subscription;
                        $hasActiveSubscription = true;

                        // Check if it's for the specific plan
                        if (
                            $plans_id !== null && isset($subscription['plans_id']) &&
                            $subscription['plans_id'] == $plans_id
                        ) {
                            $hasActivePlanSubscription = true;
                        }
                    }
                }
            }

            return [
                'error' => false,
                'hasActiveSubscription' => $hasActiveSubscription,
                'hasActivePlanSubscription' => $hasActivePlanSubscription,
                'activeSubscriptions' => $activeSubscriptions,
                'totalSubscriptions' => count($subscriptionsResult['subscriptions'])
            ];
        } catch (Throwable $e) {
            _error_log('[AuthorizeNet] Exception in checkUserActiveSubscriptions: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Cancel a subscription
     *
     * @param string $subscriptionId
     * @return array{error:bool,msg?:string,status?:string}
     */
    public static function cancelSubscription(string $subscriptionId): array
    {
        try {
            if (trim($subscriptionId) === '') {
                return ['error' => true, 'msg' => 'Missing subscriptionId'];
            }

            $merchantAuthentication = self::getMerchantAuthentication();
            $environment            = self::getEnvironment();

            $request = new \net\authorize\api\contract\v1\ARBCancelSubscriptionRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setSubscriptionId($subscriptionId);

            $controller = new \net\authorize\api\controller\ARBCancelSubscriptionController($request);
            $response = $controller->executeWithApiResponse($environment);

            if (
                $response &&
                $response->getMessages()->getResultCode() === 'Ok'
            ) {
                _error_log("[AuthorizeNet] Subscription canceled successfully: " . $subscriptionId);
                return [
                    'error' => false,
                    'msg' => 'Subscription canceled successfully',
                    'status' => 'canceled'
                ];
            }

            return ['error' => true, 'msg' => self::extractSdkError($response)];
        } catch (Throwable $e) {
            _error_log('[AuthorizeNet] Exception in cancelSubscription: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Get all active subscriptions for a user from Authorize.Net API
     *
     * @param int $users_id
     * @return array{error:bool,subscriptions:array,msg?:string}
     */
    public static function getUserActiveSubscriptions(int $users_id): array
    {
        try {
            $customerProfileId = self::getOrCreateCustomerProfile($users_id);
            if (empty($customerProfileId)) {
                return ['error' => true, 'msg' => 'Customer profile not found', 'subscriptions' => []];
            }

            $subscriptionsResult = self::getCustomerSubscriptions($customerProfileId);
            if ($subscriptionsResult['error']) {
                return $subscriptionsResult;
            }

            $activeSubscriptions = [];
            foreach ($subscriptionsResult['subscriptions'] as $sub) {
                if (isset($sub['status']) && strtolower($sub['status']) === 'active') {
                    // Get detailed subscription info including current status from API
                    $detailsResult = self::getSubscriptionDetails($sub['subscriptionId']);
                    if (!$detailsResult['error'] && !empty($detailsResult['subscription'])) {
                        $activeSubscriptions[] = $detailsResult['subscription'];
                    }
                }
            }

            return ['error' => false, 'subscriptions' => $activeSubscriptions];
        } catch (Throwable $e) {
            _error_log('[AuthorizeNet] Exception in getUserActiveSubscriptions: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage(), 'subscriptions' => []];
        }
    }

    /**
     * Get subscription by ID with current status from API
     *
     * @param string $subscriptionId
     * @return array{error:bool,subscription?:array,msg?:string}
     */
    public static function getSubscriptionWithCurrentStatus(string $subscriptionId): array
    {
        try {
            // First get basic subscription details
            $detailsResult = self::getSubscriptionDetails($subscriptionId);
            if ($detailsResult['error']) {
                return $detailsResult;
            }

            // Then get current status
            $statusResult = self::isSubscriptionActive($subscriptionId);
            if ($statusResult['error']) {
                return $statusResult;
            }

            $subscription = $detailsResult['subscription'];
            $subscription['currentStatus'] = $statusResult['status'];
            $subscription['isActive'] = $statusResult['active'];

            return ['error' => false, 'subscription' => $subscription];
        } catch (Throwable $e) {
            _error_log('[AuthorizeNet] Exception in getSubscriptionWithCurrentStatus: ' . $e->getMessage());
            return ['error' => true, 'msg' => $e->getMessage()];
        }
    }
}
