<?php

/** Admin diagnostics. Read probes never create charges, customers or subscription plans. */
class StripeConfiguration
{
    const API_VERSION = '2022-11-15';
    const EVENTS = ['charge.succeeded', 'invoice.payment_succeeded'];
    private $settings, $url, $client;

    public function __construct($settings, $url, $client = null)
    {
        $this->settings = $settings;
        $this->url = $url;
        $this->client = $client;
    }

    private function client()
    {
        if ($this->client === null) {
            $this->client = new \Stripe\StripeClient([
                'api_key' => trim($this->settings->Restrictedkey ?? ''),
                'stripe_version' => self::API_VERSION,
                'max_network_retries' => 0,
            ]);
        }
        return $this->client;
    }

    public static function mode($key)
    {
        return preg_match('/^(?:pk|rk|sk)_(test|live)_[A-Za-z0-9]+$/D', trim((string) $key), $match) ? $match[1] : '';
    }

    private static function scope($settings, $url)
    {
        return hash('sha256', $url . '|' . trim($settings->Restrictedkey ?? ''));
    }

    public static function managedWebhook($settings, $url)
    {
        if (empty($settings->automaticWebhookSecret) || !is_object($settings->automaticWebhookSecret)
            || ($settings->automaticWebhookSecret->type ?? '') !== 'encrypted') {
            return [];
        }
        $data = Plugin::decryptIfNeed((object) ['automaticWebhookSecret' => clone $settings->automaticWebhookSecret]);
        $data = json_decode($data->automaticWebhookSecret->value ?? '', true);
        if (!is_array($data) || !hash_equals(self::scope($settings, $url), (string) ($data['scope'] ?? ''))) {
            return [];
        }
        return $data;
    }

    public static function signingSecrets($settings, $url)
    {
        $secrets = array_filter(array_map('trim', explode(',', (string) ($settings->SigningSecret ?? ''))));
        $managed = self::managedWebhook($settings, $url);
        if (!empty($managed['secret'])) {
            $secrets[] = $managed['secret'];
        }
        return implode(',', array_unique($secrets));
    }

    private static function deliveryCacheName($settings, $url)
    {
        return 'stripe-configuration-delivery/' . hash('sha256', self::scope($settings, $url) . '|' . self::signingSecrets($settings, $url));
    }

    private static function deliveryCache($settings, $url, $delivery = null)
    {
        // A webhook and an administrator have different country/login/device metadata.
        // Use the shared cache helper and one device bucket for this site-wide evidence.
        global $forceDeviceType;
        $previousDevice = $forceDeviceType;
        try {
            $forceDeviceType = 'web';
            $name = self::deliveryCacheName($settings, $url);
            if ($delivery !== null) {
                return ObjectYPT::setCacheGlobal($name, $delivery, true, true);
            }
            return ObjectYPT::getCacheGlobal($name, 7 * 86400, true, true, true);
        } finally {
            $forceDeviceType = $previousDevice;
        }
    }

    public static function recordDelivery($settings, $url, $event)
    {
        // Only call after signature verification and successful handler completion.
        // No payload, personal data, keys or signature is stored in the cache.
        if (!in_array($event->type, self::EVENTS, true)
            || (bool) $event->livemode !== (self::mode($settings->Restrictedkey ?? '') === 'live')) {
            return;
        }
        self::deliveryCache($settings, $url, ['received_at' => time(), 'type' => $event->type]);
    }

    private static function finding(&$report, $id, $label, $status, $message)
    {
        $report['checks'][] = compact('id', 'label', 'status', 'message');
        if ($status === 'fail') {
            $report['has_failures'] = true;
        }
    }

    private function validKeys()
    {
        $key = trim($this->settings->Restrictedkey ?? '');
        $public = trim($this->settings->Publishablekey ?? '');
        return preg_match('/^(?:rk|sk)_/', $key) && strpos($public, 'pk_') === 0
            && self::mode($key) !== '' && self::mode($key) === self::mode($public);
    }

    private static function failure($exception)
    {
        // Stripe error messages can include submitted credentials or customer data.
        if ($exception instanceof \Stripe\Exception\AuthenticationException) {
            return 'Stripe rejected the API key. Check that it is current and belongs to this account and mode.';
        }
        if ($exception instanceof \Stripe\Exception\PermissionException || (method_exists($exception, 'getHttpStatus') && $exception->getHttpStatus() === 403)) {
            return 'Stripe denied access. Grant this resource permission in Dashboard, and check any IP restrictions on the key.';
        }
        return 'This API request could not be verified. Check Stripe request logs for permissions, network or API errors, then run the check again.';
    }

    private function endpoints()
    {
        $matches = [];
        $parameters = ['limit' => 100];
        do {
            $page = $this->client()->webhookEndpoints->all($parameters);
            foreach ($page->data as $endpoint) {
                if ($endpoint->url === $this->url) {
                    $matches[] = $endpoint;
                }
                $parameters['starting_after'] = $endpoint->id;
            }
            if (!empty($page->has_more) && empty($page->data)) {
                throw new RuntimeException('Incomplete webhook pagination');
            }
        } while (!empty($page->has_more));
        return $matches;
    }

    private function publicEndpoint($endpoint)
    {
        // Explicit allowlist: never return Stripe objects (creation includes a signing secret).
        return [
            'id' => $endpoint->id, 'url' => $endpoint->url,
            'status' => $endpoint->status, 'api_version' => $endpoint->api_version,
            'livemode' => (bool) $endpoint->livemode,
            'enabled_events' => array_values($endpoint->enabled_events),
        ];
    }

    public function check($features = [])
    {
        $report = ['checks' => [], 'permissions' => [], 'webhooks' => [], 'has_failures' => false,
            'mode' => self::mode($this->settings->Restrictedkey ?? ''), 'webhook_url' => $this->url,
            'api_version' => self::API_VERSION, 'can_configure' => false];
        self::finding($report, 'keys', 'API keys and mode', $this->validKeys() ? 'pass' : 'fail',
            $this->validKeys() ? 'Key formats and test/live mode match. A matching prefix does not prove that the publishable key belongs to the same account.' : 'Save a publishable key and a server key from the same Stripe account and mode.');
        $publicUrl = filter_var($this->url, FILTER_VALIDATE_URL) && parse_url($this->url, PHP_URL_SCHEME) === 'https'
            && !in_array(strtolower((string) parse_url($this->url, PHP_URL_HOST)), ['localhost', '127.0.0.1', '::1'], true);
        self::finding($report, 'url', 'Public webhook URL', $publicUrl ? 'pass' : 'fail',
            $publicUrl ? 'HTTPS URL configured. External reachability and TLS must also be confirmed by a Stripe delivery.' : 'Use the public HTTPS site URL. For local testing, forward Stripe CLI events instead of registering localhost.');
        foreach (['wallet' => 'YPTWallet', 'wallet_stripe' => 'Wallet card payments'] as $feature => $label) {
            if (array_key_exists($feature, $features)) {
                self::finding($report, $feature, $label, $features[$feature] ? 'pass' : 'fail',
                    $features[$feature] ? 'Enabled.' : 'Enable this component to offer Stripe checkout.');
            }
        }
        $subscription = !empty($features['subscription']);
        if ($this->validKeys()) {
            try {
                $account = $this->client()->accounts->retrieve();
                $report['account_id'] = $account->id;
                self::finding($report, 'account', 'Stripe account', $account->charges_enabled ? 'pass' : ($report['mode'] === 'live' ? 'fail' : 'warning'),
                    $account->charges_enabled ? 'Account ' . $account->id . ' can process charges. Compare it with the account selected in Stripe Dashboard.'
                        : ($report['mode'] === 'live' ? 'Stripe reports charges disabled for account ' . $account->id . '. Resolve account requirements in Stripe Dashboard.'
                            : 'Sandbox account ' . $account->id . ' reports charges_enabled=false. Verify test payments separately; this flag does not establish live readiness. Activate and verify the live account before accepting real payments.'));
            } catch (Throwable $exception) {
                self::finding($report, 'account', 'Stripe account', 'warning',
                    'Account identity and charge capability could not be read. Check the account in Stripe Dashboard; this optional probe may need account read access. Other resource checks run independently.');
            }
        }
        $resources = ['paymentIntents' => ['PaymentIntents', 'Write'], 'charges' => ['Charges', 'Read']];
        if ($subscription) {
            $resources += ['customers' => ['Customers', 'Write'], 'paymentMethods' => ['PaymentMethods', 'Write'],
                'plans' => ['Plans / Prices', 'Write'], 'products' => ['Products', 'Write'],
                'subscriptions' => ['Subscriptions', 'Write'], 'invoices' => ['Invoices', 'Read']];
        }
        $apiFailed = false;
        foreach ($resources as $resource => $requirement) {
            $permission = ['resource' => $requirement[0], 'required' => $requirement[1], 'status' => 'unknown',
                'message' => 'Save valid keys before running API probes.'];
            if ($this->validKeys()) {
                try {
                    $parameters = ['limit' => 1];
                    if ($resource === 'paymentMethods') {
                        $parameters['type'] = 'card';
                    }
                    $this->client()->$resource->all($parameters);
                    $permission['status'] = 'pass';
                    $permission['message'] = 'Read access confirmed.';
                    if ($requirement[1] === 'Write') {
                        $permission['status'] = 'warning';
                        $permission['message'] .= ' Verify Write in Stripe Dashboard or a sandbox checkout.';
                    }
                } catch (Throwable $exception) {
                    $permission['status'] = 'fail';
                    $permission['message'] = self::failure($exception);
                    $apiFailed = true;
                }
            }
            $report['permissions'][] = $permission;
        }
        if ($apiFailed) {
            $report['has_failures'] = true;
        }
        if ($this->validKeys()) {
            try {
                $endpoints = $this->endpoints();
                $report['can_configure'] = $publicUrl && count($endpoints) <= 1;
                foreach ($endpoints as $endpoint) {
                    $report['webhooks'][] = $this->publicEndpoint($endpoint);
                }
                if (count($endpoints) === 0) {
                    self::finding($report, 'webhook', 'Webhook endpoint', 'fail', 'No endpoint matches this exact URL. Automatic setup can create it with Webhook Endpoints: Write.');
                } elseif (count($endpoints) > 1) {
                    self::finding($report, 'webhook', 'Webhook endpoint', 'fail', 'Multiple endpoints match this URL. Review duplicates in Stripe; automatic setup will not choose or delete one.');
                } else {
                    $endpoint = $endpoints[0];
                    $events = $endpoint->enabled_events;
                    $correct = $endpoint->status === 'enabled' && ((bool) $endpoint->livemode === ($report['mode'] === 'live'))
                        && (in_array('*', $events, true) || !array_diff(self::EVENTS, $events));
                    self::finding($report, 'webhook', 'Webhook endpoint', $correct ? 'pass' : 'fail',
                        $correct ? 'Enabled in the expected mode, with both required payment events.' : 'Check endpoint status, mode and required events. Automatic setup can enable it and add missing events.');
                    $versionMatches = $endpoint->api_version === self::API_VERSION;
                    self::finding($report, 'version', 'Webhook API version', $versionMatches ? 'pass' : 'fail',
                        $versionMatches ? 'Snapshot payload version matches the payment integration.' : 'Use snapshot events with API version ' . self::API_VERSION . '. A null version follows the account default and is not pinned. Review this in Stripe; automatic setup will not replace an existing endpoint.');
                    if (!$versionMatches || (bool) $endpoint->livemode !== ($report['mode'] === 'live')) {
                        $report['can_configure'] = false;
                    }
                }
            } catch (Throwable $exception) {
                self::finding($report, 'webhook', 'Webhook endpoint', 'warning', self::failure($exception)
                    . ' Webhook Endpoints: Read is needed for inspection, Write for setup. Payments can use a manually configured endpoint without these permissions.');
            }
        }
        $secrets = self::signingSecrets($this->settings, $this->url);
        $validSecrets = $secrets !== '';
        foreach (explode(',', $secrets) as $secret) {
            $validSecrets = $validSecrets && preg_match('/^whsec_[A-Za-z0-9]+$/D', $secret);
        }
        $delivery = (array) self::deliveryCache($this->settings, $this->url);
        $verified = $validSecrets && !empty($delivery['received_at']);
        self::finding($report, 'signing', 'Webhook signing secret', $verified ? 'pass' : ($validSecrets ? 'warning' : 'fail'),
            $verified ? 'A signed ' . $delivery['type'] . ' delivery completed successfully at ' . gmdate('Y-m-d H:i:s', $delivery['received_at']) . ' UTC with the current key, URL and secret configuration. This historical check does not guarantee current reachability or content access.'
                : ($validSecrets ? 'A signing secret is configured. Its format alone cannot prove it belongs to this endpoint. Confirm a signed delivery with HTTP 200 in Stripe.' : 'No valid signing secret is configured. New endpoints save it automatically; for an existing endpoint, copy its secret from Stripe into SigningSecret.'));
        self::finding($report, 'checkout', 'End-to-end payment', 'warning',
            'Run sandbox checkout for wallet, PPV, PPV Live and enabled subscriptions. Confirm payment, HTTP 200 webhook, committed access and playback. API probes do not verify 3D Secure or playback.');
        return $report;
    }

    /** Called under the site's setup lock, with freshly read settings. */
    public function configure(callable $save)
    {
        if (!$this->validKeys() || parse_url($this->url, PHP_URL_SCHEME) !== 'https'
            || in_array(strtolower((string) parse_url($this->url, PHP_URL_HOST)), ['localhost', '127.0.0.1', '::1'], true)) {
            return ['status' => 'fail', 'message' => 'Save matching keys and a public HTTPS URL before automatic setup.'];
        }
        try {
            $endpoints = $this->endpoints();
            if (count($endpoints) > 1) {
                return ['status' => 'fail', 'message' => 'Duplicate endpoints need manual review. No changes were made.'];
            }
            if ($endpoints) {
                $endpoint = $endpoints[0];
                if ($endpoint->api_version !== self::API_VERSION || (bool) $endpoint->livemode !== (self::mode($this->settings->Restrictedkey) === 'live')) {
                    return ['status' => 'fail', 'message' => 'Review the existing endpoint version and mode in Stripe. No replacement was created.'];
                }
                $events = $endpoint->enabled_events;
                $parameters = [];
                if ($endpoint->status !== 'enabled') {
                    $parameters['disabled'] = false;
                }
                if (!in_array('*', $events, true) && array_diff(self::EVENTS, $events)) {
                    $parameters['enabled_events'] = array_values(array_unique(array_merge($events, self::EVENTS)));
                }
                if ($parameters) {
                    $this->client()->webhookEndpoints->update($endpoint->id, $parameters);
                }
                if (self::signingSecrets($this->settings, $this->url) === '') {
                    return ['status' => 'warning', 'message' => 'Webhook events and status are configured. Its existing secret cannot be retrieved through the API: reveal it in Stripe and save SigningSecret.'];
                }
                return ['status' => 'pass', 'message' => $parameters ? 'Existing webhook updated. Existing events and signing secrets were preserved. Review the signing-secret delivery check below.' : 'The webhook is already configured. No changes were needed. Review the signing-secret delivery check below.'];
            }
            // No random key: a lost create response can be retried without a duplicate endpoint.
            $endpoint = $this->client()->webhookEndpoints->create([
                'url' => $this->url, 'enabled_events' => self::EVENTS, 'api_version' => self::API_VERSION,
                'description' => 'AVideo StripeYPT payments',
            ], ['idempotency_key' => 'avideo-webhook-' . self::scope($this->settings, $this->url)]);
            if (empty($endpoint->secret)) {
                return ['status' => 'fail', 'message' => 'Stripe did not return a new signing secret. Reveal it in Stripe and save SigningSecret.'];
            }
            $settings = clone $this->settings;
            $settings->automaticWebhookSecret = (object) ['type' => 'encrypted', 'value' => json_encode([
                'scope' => self::scope($settings, $this->url), 'endpoint_id' => $endpoint->id, 'secret' => $endpoint->secret,
            ])];
            $settings = Plugin::encryptIfNeed($settings);
            if (!$save($settings)) {
                // Leave an inspectable endpoint; never lose or silently overwrite an existing secret.
                return ['status' => 'fail', 'message' => 'Webhook created, but local settings could not be saved. Reveal its signing secret in Stripe and save SigningSecret before accepting payments.'];
            }
            $this->settings = $settings;
            return ['status' => 'pass', 'message' => 'Webhook created with the required events and API version. Its signing secret was saved encrypted.'];
        } catch (Throwable $exception) {
            return ['status' => 'fail', 'message' => self::failure($exception) . ' Automatic setup needs Webhook Endpoints: Write. No key permissions are changed by this tool.'];
        }
    }
}
