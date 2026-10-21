<?php

/**
 * Administrator diagnostics and explicit webhook setup for PayPalYPT.
 * Inspection only reads: an OAuth token exchange plus GET requests. It never creates payments, plans,
 * agreements, payouts or refunds, never deletes webhooks and never changes the app's permissions.
 * Tokens, secrets, Authorization headers and payer data are never returned or logged.
 */
class PayPalConfiguration
{
    // Currencies accepted by the PayPal REST APIs; the last three do not accept decimals.
    const CURRENCIES = ['AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'ILS', 'MYR', 'MXN', 'NZD', 'NOK', 'PHP',
        'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'USD', 'HUF', 'JPY', 'TWD'];
    const NO_DECIMAL_CURRENCIES = ['HUF', 'JPY', 'TWD'];
    const WEBHOOK_LIMIT = 10;

    private $settings;
    private $webhookURL;
    private $http;
    private $token = '';
    private $scopes = [];

    /**
     * @param callable|null $http function ($method, $url, array $headers, $body) returning
     *        ['status' => int, 'body' => string]; it throws on network errors. Defaults to cURL.
     */
    public function __construct($settings, $webhookURL, ?callable $http = null)
    {
        $this->settings = $settings;
        $this->webhookURL = $webhookURL;
        $this->http = $http ?: [__CLASS__, 'curl'];
    }

    public static function curl($method, $url, array $headers, $body)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            // Fixed public PayPal endpoints: always verify TLS.
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if ($body !== null && $body !== '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new RuntimeException('PayPal network error ' . $error);
        }
        $status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        curl_close($ch);
        return ['status' => $status, 'body' => (string) $response];
    }

    private function environment()
    {
        return empty($this->settings->disableSandbox) ? 'sandbox' : 'live';
    }

    private function apiBase()
    {
        return $this->environment() === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private static function finding(&$report, $id, $label, $status, $message)
    {
        $report['checks'][] = compact('id', 'label', 'status', 'message');
        if ($status === 'fail') {
            $report['has_failures'] = true;
        }
    }

    private function credentialsPresent()
    {
        return trim((string) ($this->settings->ClientID ?? '')) !== '' && trim((string) ($this->settings->ClientSecret ?? '')) !== '';
    }

    public function isSharedDemoApp()
    {
        return trim((string) ($this->settings->ClientID ?? '')) === PayPalYPT::DEMO_CLIENT_ID;
    }

    /** Classifies a PayPal HTTP answer without exposing PayPal's message text. */
    public static function describeStatus($status)
    {
        if ($status === 401) {
            return 'PayPal rejected the credentials for this environment.';
        }
        if ($status === 403) {
            return 'PayPal denied this request: the app lacks the permission or the account lacks the product/capability.';
        }
        if ($status === 404) {
            return 'PayPal answered "not found". This proves neither access nor its absence.';
        }
        if ($status === 429 || $status >= 500) {
            return 'PayPal is temporarily unavailable or rate limited. Run the check again later.';
        }
        return 'PayPal answered HTTP ' . intval($status) . '.';
    }

    /** OAuth client-credentials exchange. The token stays in memory for this check only. */
    private function authenticate(&$report)
    {
        if (!$this->credentialsPresent()) {
            self::finding($report, 'oauth', 'OAuth authentication', 'fail', 'Save the Client ID and secret of your PayPal REST app first.');
            return false;
        }
        try {
            $response = call_user_func($this->http, 'POST', $this->apiBase() . '/v1/oauth2/token', [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode(trim($this->settings->ClientID) . ':' . trim($this->settings->ClientSecret)),
            ], 'grant_type=client_credentials');
        } catch (Throwable $exception) {
            self::finding($report, 'oauth', 'OAuth authentication', 'warning', 'The server could not reach PayPal (' . $this->apiBase() . '). Check DNS, firewall and outbound HTTPS, then run the check again.');
            return false;
        }
        $data = json_decode($response['body'], true);
        if ($response['status'] !== 200 || empty($data['access_token'])) {
            $message = $response['status'] === 401
                ? 'PayPal rejected the Client ID/secret in the ' . strtoupper($this->environment()) . ' environment. Check that both come from the same app, and that "Live payments" matches the app (Sandbox app = unchecked).'
                : self::describeStatus($response['status']);
            self::finding($report, 'oauth', 'OAuth authentication', 'fail', $message);
            return false;
        }
        $this->token = (string) $data['access_token'];
        $this->scopes = array_values(array_filter(explode(' ', (string) ($data['scope'] ?? ''))));
        $report['app_id'] = preg_replace('/[^A-Z0-9-]/i', '', (string) ($data['app_id'] ?? ''));
        $report['token_expires_in'] = intval($data['expires_in'] ?? 0);
        self::finding($report, 'oauth', 'OAuth authentication', 'pass', 'PayPal accepted the credentials in the ' . strtoupper($this->environment())
            . ' environment (app ' . $report['app_id'] . '; token valid for ' . intval($report['token_expires_in'] / 60) . ' minutes). This does not prove that each product is approved for the account.');
        return true;
    }

    private function api($method, $path, $body = null)
    {
        $headers = ['Accept: application/json', 'Authorization: Bearer ' . $this->token];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        $response = call_user_func($this->http, $method, $this->apiBase() . $path, $headers, $body);
        $response['data'] = json_decode($response['body'], true);
        return $response;
    }

    private function hasScope($pattern)
    {
        foreach ($this->scopes as $scope) {
            if ($scope === $pattern) {
                return true;
            }
        }
        return false;
    }

    /** Scopes the token reports, compared to the REST resources each enabled feature calls. */
    private function checkScopes(&$report, $features)
    {
        $map = [
            ['feature' => 'One-time payments (wallet, PPV, PPV Live)', 'api' => 'Payments v1 /v1/payments/payment', 'scope' => 'https://api.paypal.com/v1/payments/.*', 'required' => true],
            ['feature' => 'Recurring subscriptions', 'api' => 'Billing Plans/Agreements v1 /v1/payments/billing-*', 'scope' => 'https://uri.paypal.com/services/billing-agreements', 'required' => !empty($features['subscription'])],
            ['feature' => 'Webhook inspection and setup', 'api' => 'Webhooks v1 /v1/notifications/webhooks', 'scope' => 'https://uri.paypal.com/services/applications/webhooks', 'required' => false],
            ['feature' => 'Payouts (wallet withdrawals)', 'api' => 'Payouts v1 /v1/payments/payouts', 'scope' => 'https://uri.paypal.com/payments/payouts', 'required' => !empty($features['payouts'])],
        ];
        foreach ($map as $row) {
            $used = $row['required'] || $row['scope'] === 'https://uri.paypal.com/services/applications/webhooks';
            $present = $this->hasScope($row['scope']);
            if (!$used) {
                $status = 'unknown';
                $message = 'Feature not enabled; not required.';
            } elseif ($present) {
                $status = 'pass';
                $message = 'Scope present. A scope does not prove the account is approved for this product or that writes succeed.';
            } else {
                $status = $row['required'] ? 'fail' : 'warning';
                $message = $row['required'] ? 'Scope missing: enable this feature for the app in developer.paypal.com.' : 'Scope missing: inspection/setup will not work; payments do not need it if the webhook is configured manually.';
            }
            $report['permissions'][] = ['resource' => $row['feature'], 'api' => $row['api'], 'scope' => $row['scope'],
                'required' => $row['required'] ? 'Required' : 'Optional', 'status' => $status, 'message' => $message];
            if ($status === 'fail') {
                $report['has_failures'] = true;
            }
        }
    }

    private function probe(&$report, $label, $path)
    {
        try {
            $response = $this->api('GET', $path);
        } catch (Throwable $exception) {
            $report['probes'][] = ['resource' => $label, 'status' => 'warning', 'message' => 'Network error; run the check again.'];
            return;
        }
        $ok = in_array($response['status'], [200, 204], true);
        $report['probes'][] = ['resource' => $label, 'status' => $ok ? 'pass' : ($response['status'] === 404 || $response['status'] >= 500 ? 'warning' : 'fail'),
            'message' => $ok ? 'Read access confirmed. Write access is not proven by a read.' : self::describeStatus($response['status'])];
        if (!$ok && $response['status'] !== 404 && $response['status'] < 500) {
            $report['has_failures'] = true;
        }
    }

    /** All webhooks of the app (an app has at most 10 and the list API has no pages). */
    private function listWebhooks()
    {
        $response = $this->api('GET', '/v1/notifications/webhooks');
        if ($response['status'] !== 200 || !is_array($response['data'])) {
            throw new PayPalConfigurationHttpException($response['status']);
        }
        return $response['data']['webhooks'] ?? [];
    }

    private function matching(array $webhooks)
    {
        return array_values(array_filter($webhooks, function ($webhook) {
            return ($webhook['url'] ?? '') === $this->webhookURL;
        }));
    }

    private static function eventNames($webhook)
    {
        return array_values(array_map(function ($event) {
            return (string) ($event['name'] ?? '');
        }, $webhook['event_types'] ?? []));
    }

    private static function missingEvents($webhook)
    {
        $names = self::eventNames($webhook);
        return in_array('*', $names, true) ? [] : array_values(array_diff(PayPalYPT::WEBHOOK_EVENTS, $names));
    }

    public function isPublicHttpsURL()
    {
        $host = strtolower((string) parse_url($this->webhookURL, PHP_URL_HOST));
        if (parse_url($this->webhookURL, PHP_URL_SCHEME) !== 'https' || $host === '' || $host === 'localhost') {
            return false;
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return (bool) filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }
        return strpos($host, '.') !== false;
    }

    private function inspectWebhook(&$report)
    {
        try {
            $webhooks = $this->listWebhooks();
            $matches = $this->matching($webhooks);
            $report['other_webhooks'] = count($webhooks) - count($matches);
            foreach ($matches as $webhook) {
                $report['webhooks'][] = ['id' => (string) ($webhook['id'] ?? ''), 'url' => (string) ($webhook['url'] ?? ''), 'events' => self::eventNames($webhook)];
            }
            $saved = trim((string) ($this->settings->webhookId ?? ''));
            if (count($matches) === 0) {
                $full = count($webhooks) >= self::WEBHOOK_LIMIT;
                self::finding($report, 'webhook', 'Webhook for this site', 'fail', $full
                    ? 'No webhook uses this URL and the app already has ' . count($webhooks) . ' webhooks (PayPal limit ' . self::WEBHOOK_LIMIT . '). Remove an unused webhook yourself in developer.paypal.com; AVideo never deletes webhooks.'
                    : 'No webhook uses this exact URL. Configure it automatically below, or create it in developer.paypal.com with the events listed.');
                $report['can_configure'] = !$full;
            } elseif (count($matches) > 1) {
                self::finding($report, 'webhook', 'Webhook for this site', 'fail', count($matches) . ' webhooks use this URL. Keep one in developer.paypal.com and save its ID; automatic setup will not choose or delete one.');
            } else {
                $missing = self::missingEvents($matches[0]);
                self::finding($report, 'webhook', 'Webhook for this site', empty($missing) ? 'pass' : 'fail', empty($missing)
                    ? 'Webhook ' . $matches[0]['id'] . ' uses this URL with every handled event.'
                    : 'Webhook ' . $matches[0]['id'] . ' is missing: ' . implode(', ', $missing) . '. Automatic setup adds them and keeps the other events.');
                $report['can_configure'] = !empty($missing) || $saved !== (string) $matches[0]['id'];
                self::finding($report, 'webhook_id', 'Webhook ID used for signatures', $saved === (string) $matches[0]['id'] ? 'pass' : 'fail',
                    $saved === (string) $matches[0]['id'] ? 'The saved Webhook ID matches.' : 'The saved Webhook ID is ' . ($saved === '' ? 'empty' : 'different') . '. Automatic setup saves ' . $matches[0]['id'] . '; without it, each delivery needs a webhook lookup.');
            }
        } catch (PayPalConfigurationHttpException $exception) {
            self::finding($report, 'webhook', 'Webhook for this site', 'warning', self::describeStatus($exception->status)
                . ' Webhook inspection needs the webhooks scope; payments can use a manually configured webhook and its saved ID.');
        } catch (Throwable $exception) {
            self::finding($report, 'webhook', 'Webhook for this site', 'warning', 'Network error while listing webhooks; run the check again.');
        }
    }

    public function check($features = [])
    {
        $report = ['checks' => [], 'permissions' => [], 'probes' => [], 'webhooks' => [], 'has_failures' => false,
            'environment' => $this->environment(), 'api_base' => $this->apiBase(), 'webhook_url' => $this->webhookURL,
            'ipn_url' => $features['ipn_url'] ?? '', 'ipn_verification_url' => PayPalYPT::getIPNVerificationURL($this->settings),
            'events' => PayPalYPT::WEBHOOK_EVENTS, 'can_configure' => false];

        self::finding($report, 'environment', 'Environment', 'pass', $this->environment() === 'live'
            ? 'LIVE: real money. API ' . $this->apiBase() . ', buyer approval on www.paypal.com, IPN verification on ipnpb.paypal.com.'
            : 'SANDBOX: test money only. API ' . $this->apiBase() . ', buyer approval on www.sandbox.paypal.com, IPN verification on ipnpb.sandbox.paypal.com.');
        if ($this->isSharedDemoApp()) {
            self::finding($report, 'credentials', 'Credentials', 'fail', 'This is the shared public sandbox app shipped as the default. Other sites use it too, so its webhooks and notifications are not yours. Create your own app in developer.paypal.com and save its Client ID and secret. Automatic setup is disabled for this app.');
        } else {
            self::finding($report, 'credentials', 'Credentials', $this->credentialsPresent() ? 'pass' : 'fail',
                $this->credentialsPresent() ? 'Client ID and secret are saved. The environment is never guessed from the Client ID format; it comes from "Live payments".' : 'Save the Client ID and secret of your own PayPal REST app.');
        }
        foreach (['wallet' => 'YPTWallet', 'wallet_paypal' => 'Wallet PayPal payments (enablePlugin_YPTWalletPayPal)'] as $feature => $label) {
            if (array_key_exists($feature, $features)) {
                self::finding($report, $feature, $label, $features[$feature] ? 'pass' : 'fail',
                    $features[$feature] ? 'Enabled.' : 'Enable it so buyers can pay with PayPal.');
            }
        }
        $currency = strtoupper((string) ($features['currency'] ?? ''));
        if ($currency !== '') {
            $supported = in_array($currency, self::CURRENCIES, true);
            self::finding($report, 'currency', 'Wallet currency', $supported ? (in_array($currency, self::NO_DECIMAL_CURRENCIES, true) ? 'warning' : 'pass') : 'fail',
                !$supported ? "{$currency} is not a PayPal REST currency. PayPal checkout cannot be used with this wallet currency."
                    : (in_array($currency, self::NO_DECIMAL_CURRENCIES, true) ? "{$currency} does not accept decimals at PayPal: use whole-number prices." : "{$currency} is supported. The seller account must also be able to receive it (review in PayPal)."));
        }
        if (array_key_exists('checkout_table', $features)) {
            self::finding($report, 'database', 'Checkout storage', $features['checkout_table'] ? 'pass' : 'fail',
                $features['checkout_table'] ? 'Checkout table installed.' : 'Run the PayPalYPT plugin update (Plugins page) to create the PayPalYPT_checkout table; PayPal checkout is unavailable until then.');
        }
        $publicURL = $this->isPublicHttpsURL();
        self::finding($report, 'url', 'Public HTTPS URL', $publicURL ? 'pass' : 'fail', $publicURL
            ? 'The webhook and IPN URLs use HTTPS on a public host. PayPal\'s own delivery must still confirm DNS, TLS, firewall, proxy and ports.'
            : 'PayPal cannot deliver to this URL. Use the public HTTPS address of the site (with its subdirectory, if any).');

        $authenticated = $this->authenticate($report);
        if ($authenticated) {
            $this->checkScopes($report, $features);
            $this->probe($report, 'Payments v1 (read)', '/v1/payments/payment?count=1');
            if (!empty($features['subscription'])) {
                $this->probe($report, 'Billing Plans v1 (read)', '/v1/payments/billing-plans?page_size=1&status=ACTIVE');
            }
            if (!empty($features['payouts'])) {
                $report['probes'][] = ['resource' => 'Payouts v1', 'status' => 'unknown',
                    'message' => 'No read-only probe exists for Payouts. Prove it with a sandbox payout; it is never tested with real money here.'];
            }
            if ($this->isSharedDemoApp()) {
                // Its webhooks belong to other sites: do not read or report them.
                self::finding($report, 'webhook', 'Webhook for this site', 'fail', 'Not inspected: the shared sandbox app is not yours. Save your own app credentials, then run the check again.');
            } else {
                $this->inspectWebhook($report);
            }
        }
        if ($this->isSharedDemoApp()) {
            $report['can_configure'] = false;
            $report['webhooks'] = [];
        }
        $delivery = (array) ($features['webhook_delivery'] ?? []);
        self::finding($report, 'webhook_delivery', 'Signed webhook delivery', !empty($delivery['received_at']) ? 'pass' : 'unknown', !empty($delivery['received_at'])
            ? 'A signed ' . ($delivery['type'] ?? '') . ' event was verified with PayPal and handled at ' . gmdate('Y-m-d H:i:s', $delivery['received_at']) . ' UTC for the current app, URL and webhook ID. This is history: it does not prove current reachability or a purchase.'
            : 'Not proven yet. After a sandbox purchase, confirm in developer.paypal.com (Webhooks events) that the delivery got HTTP 200. Simulator events are not real purchases.');
        $ipn = (array) ($features['ipn_delivery'] ?? []);
        self::finding($report, 'ipn', 'IPN (legacy notifications)', !empty($ipn['received_at']) ? 'pass' : 'unknown', (!empty($ipn['received_at'])
            ? 'A ' . ($ipn['type'] ?? '') . ' IPN was VERIFIED by ' . $report['ipn_verification_url'] . ' and handled at ' . gmdate('Y-m-d H:i:s', $ipn['received_at']) . ' UTC. '
            : 'No verified IPN recorded yet. ')
            . 'One-time payments send IPN to ' . $report['ipn_url'] . '. Recurring payments use the IPN address of the seller account: review it in PayPal (Account settings > Notifications > Instant payment notifications). IPN VERIFIED is not webhook signature verification.');
        self::finding($report, 'checkout', 'End-to-end purchase', 'unknown',
            'Not proven by this tool. Complete a sandbox purchase with a Personal sandbox buyer: approval, HTTP 200 webhook, wallet credit, rental/subscription and playback.');
        $report['has_failures'] = $report['has_failures'] || in_array('fail', array_column($report['checks'], 'status'), true);
        return $report;
    }

    /**
     * Creates or completes this site's webhook only. Called under the setup lock with freshly read settings.
     * $save receives the webhook ID and must return true only after a confirmed write.
     */
    public function configure(callable $save)
    {
        if ($this->isSharedDemoApp()) {
            return ['status' => 'fail', 'message' => 'The shared public sandbox app cannot be configured. Save your own app credentials first.'];
        }
        if (!$this->isPublicHttpsURL()) {
            return ['status' => 'fail', 'message' => 'Use the public HTTPS site URL before configuring the webhook.'];
        }
        $report = ['checks' => [], 'has_failures' => false];
        if (!$this->authenticate($report)) {
            return ['status' => 'fail', 'message' => $report['checks'][0]['message'] ?? 'PayPal authentication failed.'];
        }
        try {
            $matches = $this->matching($webhooks = $this->listWebhooks());
            if (count($matches) > 1) {
                return ['status' => 'fail', 'message' => 'Several webhooks use this URL. Keep one in developer.paypal.com; no changes were made.'];
            }
            if (count($matches) === 1) {
                $webhook = $matches[0];
                $missing = self::missingEvents($webhook);
                $changed = false;
                if ($missing) {
                    // Replace with the union: events added by others are preserved.
                    $events = array_map(function ($name) {
                        return ['name' => $name];
                    }, array_values(array_unique(array_merge(self::eventNames($webhook), $missing))));
                    $response = $this->api('PATCH', '/v1/notifications/webhooks/' . rawurlencode($webhook['id']),
                        json_encode([['op' => 'replace', 'path' => '/event_types', 'value' => $events]]));
                    if ($response['status'] !== 200) {
                        return ['status' => 'fail', 'message' => 'PayPal refused to update the webhook events. ' . self::describeStatus($response['status'])];
                    }
                    $changed = true;
                }
                return $this->saveWebhookId($save, (string) $webhook['id'], $changed ? 'Webhook events added; existing events were kept.' : 'The webhook already had every event.');
            }
            if (count($webhooks) >= self::WEBHOOK_LIMIT) {
                return ['status' => 'fail', 'message' => 'The app already has ' . count($webhooks) . ' webhooks (PayPal limit). Remove an unused one yourself in developer.paypal.com; AVideo never deletes webhooks.'];
            }
            $events = array_map(function ($name) {
                return ['name' => $name];
            }, PayPalYPT::WEBHOOK_EVENTS);
            try {
                $response = $this->api('POST', '/v1/notifications/webhooks', json_encode(['url' => $this->webhookURL, 'event_types' => $events]));
            } catch (Throwable $exception) {
                $response = ['status' => 0, 'data' => null];
            }
            if ($response['status'] !== 201 || empty($response['data']['id'])) {
                // A lost response or a concurrent setup may still have created it: reconcile by URL.
                $matches = $this->matching($this->listWebhooks());
                if (count($matches) === 1 && !self::missingEvents($matches[0])) {
                    return $this->saveWebhookId($save, (string) $matches[0]['id'], 'Webhook found after an incomplete create response.');
                }
                $name = (string) ($response['data']['name'] ?? '');
                return ['status' => 'fail', 'message' => ($name === 'WEBHOOK_NUMBER_LIMIT_EXCEEDED'
                    ? 'PayPal reports the webhook limit. Remove an unused webhook yourself; AVideo never deletes webhooks.'
                    : 'PayPal did not create the webhook. ' . self::describeStatus(intval($response['status'])))];
            }
            return $this->saveWebhookId($save, (string) $response['data']['id'], 'Webhook created with the handled events.');
        } catch (PayPalConfigurationHttpException $exception) {
            return ['status' => 'fail', 'message' => 'Webhook setup needs the webhooks scope. ' . self::describeStatus($exception->status)];
        } catch (Throwable $exception) {
            return ['status' => 'fail', 'message' => 'Network error during setup. Run the check again before retrying; nothing is deleted.'];
        }
    }

    private function saveWebhookId(callable $save, $id, $message)
    {
        if (!PayPalYPT_checkout::isValidProviderId($id)) {
            return ['status' => 'fail', 'message' => 'PayPal returned an unexpected webhook ID.'];
        }
        if ((string) ($this->settings->webhookId ?? '') === $id) {
            return ['status' => 'pass', 'message' => $message . ' The saved Webhook ID already matches.', 'webhook_id' => $id];
        }
        if (!$save($id)) {
            return ['status' => 'fail', 'message' => $message . ' But the Webhook ID could not be saved (settings changed meanwhile or the write failed). Run the check again, or paste ' . $id . ' into webhookId.', 'webhook_id' => $id];
        }
        $this->settings->webhookId = $id;
        return ['status' => 'pass', 'message' => $message . ' Webhook ID ' . $id . ' saved.', 'webhook_id' => $id];
    }
}

class PayPalConfigurationHttpException extends RuntimeException
{
    public $status;

    public function __construct($status)
    {
        $this->status = intval($status);
        parent::__construct('PayPal HTTP ' . $this->status);
    }
}
