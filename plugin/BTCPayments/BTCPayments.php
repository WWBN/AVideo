<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_invoices.php';
require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_payments.php';


define("BTC_MARKETPLACE_URL", "https://streamphp.com/marketplace/BTC/"); // Replace with your BTCPay server URL
//define("BTC_MARKETPLACE_URL", "http://192.168.0.2:81/streamphp.com/marketplace/BTC/"); // Replace with your BTCPay server URL

class BTCPayments extends PluginAbstract
{

    public function getPluginDependencies() {
        return array(
            PluginDependency::create('API', 'Uses the API plugin object data for invoice/webhook processing'),
            PluginDependency::create('YPTWallet', 'Credits confirmed BTC payments to the user wallet balance', false),
        );
    }

    public function getTags()
    {
        return array(
            PluginTags::$MONETIZATION,
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
            PluginTags::$UNDERDEVELOPMENT,
        );
    }

    public function getDescription()
    {
        $desc = "A low-cost, secure way to accept Bitcoin on AVideo.
        With instant settlements, no chargebacks, and fee-free micropayments,
        it’s a simple and reliable payment solution.";
        $desc .= $this->isReadyLabel(array('YPTWallet'));
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/BTCPayments-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $desc . $help;
    }

    public function getName()
    {
        return "BTCPayments";
    }

    public function getUUID()
    {
        return "BTCPayments-67c06d156f49f";
    }

    public function getPluginVersion()
    {
        return "1.1";
    }

    public function updateScript()
    {
        global $global;
        return true;
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();

        $obj->siteWallet = "";
        $obj->siteWalletTest = "tb1qdv8zs8c9ukr636gcmznz2pa8y5zxg7u8ghufkq";
        $obj->BTCMarketPlaceKey = "";
        $obj->useTestNet = true;
        /*
        $obj->textSample = "text";
        $obj->checkboxSample = true;
        $obj->numberSample = 5;

        $o = new stdClass();
        $o->type = array(0=>__("Default"))+array(1,2,3);
        $o->value = 0;
        $obj->selectBoxSample = $o;

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->textareaSample = $o;
        */
        return $obj;
    }

    public function getPluginMenu()
    {
        global $global;

        $api = AVideoPlugin::getObjectData('API');
        $url = self::getMarketplaceURL('status.json.php');
        $url = addQueryStringParameter($url, 'siteUrl', $global['webSiteRootURL']);
        $url = addQueryStringParameter($url, 'siteId', getPlatformId());
        $url = addQueryStringParameter($url, 'APISecret', $api->APISecret);

        $btn  = '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/BTCPayments/View/editor.php\')" class="btn btn-primary btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
        $btn .= '<button onclick="avideoAlertAJAX(\'' . $url . '\');" class="btn btn-primary btn-xs btn-block"><i class="fa-solid fa-list-check"></i> Status</button>';
        return $btn;
    }

    static function getMarketplaceURL($page)
    {
        $url = BTC_MARKETPLACE_URL . $page;

        $obj = AVideoPlugin::getDataObject('BTCPayments');
        if ($obj->useTestNet) {
            $url = addQueryStringParameter($url, 'useTestNet', 1);
        }
        return $url;
    }

    static function getBitcoinNetwork(string $address): string {
        // Base58 Address Prefixes
        $mainnetPrefixes = ['1', '3', 'bc1']; // Legacy (P2PKH), SegWit (P2SH, Bech32)
        $testnetPrefixes = ['m', 'n', '2', 'tb1']; // Testnet (P2PKH, P2SH, Bech32)
        $signetPrefixes  = ['tb1q']; // Signet (Bech32)

        if (preg_match('/^(' . implode('|', $mainnetPrefixes) . ')/', $address)) {
            return 'Mainnet';
        } elseif (preg_match('/^(' . implode('|', $testnetPrefixes) . ')/', $address)) {
            return 'Testnet';
        } elseif (preg_match('/^(' . implode('|', $signetPrefixes) . ')/', $address)) {
            return 'Signet';
        } else {
            return 'Unknown';
        }
    }

    static function isValidAddress(string $address): bool {
        global $_errorMessageBTCIsValid;
        $_errorMessageBTCIsValid = '';
        if(empty($address)){
            $_errorMessageBTCIsValid = 'Address is empty';
            return false;
        }
        $obj = AVideoPlugin::getDataObject('BTCPayments');
        $network = self::getBitcoinNetwork($address);
        if($obj->useTestNet){
            $resp = $network  === 'Testnet';
            if(!$resp){
                $_errorMessageBTCIsValid = "You selected to use the Mainnet, but the provided address [$address] is for {$network} network";
            }
        }else{
            $resp = $network  === 'Mainnet';
            if(!$resp){
                $_errorMessageBTCIsValid = "You selected to use the Testnet, but the provided address [$address] is for {$network} network";
            }
        }
        return $resp;
    }

    /**
     * Create a BTCPay Invoice
     *
     * @param float $amount Amount in currency
     * @param string $currency Currency code (e.g., USD, EUR, BTC)
     * @param string|null $buyerEmail Buyer's email address (optional)
     * @param string|null $redirectUrl URL to redirect after payment (optional)
     * @param bool $throwOnError true: an unreachable/unreadable marketplace throws BTCPaymentsCheckoutRefused
     *                           (AJAX checkout) instead of ending the request with forbiddenPage()
     * @return array Invoice details or error message
     *
     * // Creating an invoice with three wallets (splitting the payment)
     * $walletSplits = [
     *     ["destination" => "bc1qexample1", "percentage" => 50],  // 50% to wallet 1
     *     ["destination" => "bc1qexample2", "percentage" => 30],  // 30% to wallet 2
     *     ["destination" => "bc1qexample3", "percentage" => 20]   // 20% to wallet 3
     * ];
     */
    static function createBTCPayInvoice($amount, $users_id, $walletSplits = [], $metadata = array(), $redirectUrl = null, $throwOnError = false)
    {
        global $global, $_errorMessageBTCIsValid;

        $objWallet = AVideoPlugin::getObjectData('YPTWallet');

        $data = [
            "amount" => floatval($amount),
            "currency" => $objWallet->currency,
            "metadata" => $metadata
        ];

        $data["metadata"]["users_id"] = $users_id;

        if (empty($redirectUrl)) {
            $redirectUrl = "{$global['webSiteRootURL']}plugin/BTCPayments/payment-complete.php";
        }
        $data["checkout"] = ["redirectUrl" => $redirectUrl];

        $obj = AVideoPlugin::getDataObject('BTCPayments');

        $data["walletSplits"] = array();
        $remainPercentage = 100;
        if (!empty($walletSplits)) {
            foreach ($walletSplits as $key => $value) {
                if(empty($value['destination'])){
                    continue;
                }
                if(!self::isValidAddress($value['destination'])){
                    forbiddenPage($_errorMessageBTCIsValid);
                }
                $data["walletSplits"][] =  ["destination" => $value['destination'], "percentage" => floatval($value['percentage'])];
                $remainPercentage -= $value['percentage'];
            }
        }

        if($obj->useTestNet){
            $siteWallet = $obj->siteWalletTest;
        }else{
            $siteWallet = $obj->siteWallet;
        }

        $data["walletSplits"][] =  ["destination" => $siteWallet, "percentage" => floatval($remainPercentage)];

        $tmpFileName = _uniqid() . '.btc.json';

        $dir = getVideosDir() . 'BTCLog/';
        make_path($dir);

        $tmpFilePath = $dir . $tmpFileName;

        file_put_contents($tmpFilePath, json_encode($data));

        $url = self::getMarketplaceURL('invoice.json.php');
        $url = addQueryStringParameter($url, 'BTCMarketPlaceKey', $obj->BTCMarketPlaceKey);
        $url = addQueryStringParameter($url, 'tmpFileName', $tmpFileName);

        _error_log('BTC::createBTCPayInvoice - Starting marketplace request', AVideoLog::$DEBUG);
        _error_log('BTC::createBTCPayInvoice - Marketplace URL: ' . preg_replace('/BTCMarketPlaceKey=[^&]+/', 'BTCMarketPlaceKey=***REDACTED***', $url), AVideoLog::$DEBUG);
        _error_log('BTC::createBTCPayInvoice - Amount: ' . $amount . ', Currency: ' . $objWallet->currency . ', UseTestNet: ' . ($obj->useTestNet ? 'true' : 'false'), AVideoLog::$DEBUG);
        _error_log('BTC::createBTCPayInvoice - Request Data: ' . json_encode($data), AVideoLog::$DEBUG);

        // The marketplace is a fixed public HTTPS server: verified unless $global['verifySSLCertificates'] = 0.
        $content = url_get_contents($url, '', 0, false, false, false, shouldVerifySSLCertificates(true));

        if (empty($content)) {
            _error_log('BTC::createBTCPayInvoice - ERROR: Empty response from marketplace', AVideoLog::$ERROR);
            _error_log('BTC::createBTCPayInvoice - URL was: ' . preg_replace('/BTCMarketPlaceKey=[^&]+/', 'BTCMarketPlaceKey=***REDACTED***', $url), AVideoLog::$ERROR);
            // The URL carries the BTCMarketPlaceKey: it goes only to the (redacted) log above, never to the buyer.
            if ($throwOnError) {
                throw new BTCPaymentsCheckoutRefused(__('Bitcoin payments are not available right now. You were not charged. Please try again.'));
            }
            forbiddenPage('Could not reach the BTC marketplace. Please try again later.');
        }

        _error_log('BTC::createBTCPayInvoice - Raw response: ' . substr($content, 0, 500), AVideoLog::$DEBUG);

        $json = json_decode($content, true);

        if (empty($json)) {
            _error_log('BTC::createBTCPayInvoice - ERROR: Could not decode JSON response', AVideoLog::$ERROR);
            _error_log('BTC::createBTCPayInvoice - Response content: ' . $content, AVideoLog::$ERROR);
            if ($throwOnError) {
                throw new BTCPaymentsCheckoutRefused(__('Bitcoin payments are not available right now. You were not charged. Please try again.'));
            }
            forbiddenPage('The BTC marketplace returned an invalid response. Please try again later.');
        }

        // Check for top-level marketplace error
        if (!empty($json['error'])) {
            _error_log('BTC::createBTCPayInvoice - ERROR from marketplace', AVideoLog::$ERROR);
            _error_log('BTC::createBTCPayInvoice - Error details: ' . json_encode($json), AVideoLog::$ERROR);
            return $json;
        }

        // Check for nested BTCPay API error in invoice object
        if (!empty($json['invoice']) && !empty($json['invoice']['error'])) {
            _error_log('BTC::createBTCPayInvoice - ERROR from BTCPay API (nested)', AVideoLog::$ERROR);
            _error_log('BTC::createBTCPayInvoice - Status: ' . (!empty($json['invoice']['status']) ? $json['invoice']['status'] : 'unknown'), AVideoLog::$ERROR);
            _error_log('BTC::createBTCPayInvoice - API Error: ' . $json['invoice']['error'], AVideoLog::$ERROR);
            _error_log('BTC::createBTCPayInvoice - Response: ' . json_encode($json['invoice']['response']), AVideoLog::$ERROR);
            if (!empty($json['invoice']['lastBtcpayRequest'])) {
                _error_log('BTC::createBTCPayInvoice - BTCPay Request URL: ' . $json['invoice']['lastBtcpayRequest']['url'], AVideoLog::$ERROR);
                _error_log('BTC::createBTCPayInvoice - BTCPay Request Code: ' . $json['invoice']['lastBtcpayRequest']['code'], AVideoLog::$ERROR);
            }
            // Return error to caller
            $json['error'] = true;
            $json['msg'] = 'BTCPay API Error: ' . $json['invoice']['error'] . ' (Status: ' . (!empty($json['invoice']['status']) ? $json['invoice']['status'] : 'unknown') . ')';
            return $json;
        }

        _error_log('BTC::createBTCPayInvoice - SUCCESS: Invoice created', AVideoLog::$DEBUG);
        _error_log('BTC::createBTCPayInvoice - Invoice ID: ' . (!empty($json['id']) ? $json['id'] : (!empty($json['invoice']['id']) ? $json['invoice']['id'] : 'N/A')), AVideoLog::$DEBUG);

        return $json;
    }

    static public function setUpPayment($total_cost = '1.00', $users_id, $metadata = array(), $redirectUrl = null)
    {
        global $global;
        $total_cost = floatval($total_cost);
        $objWallet = AVideoPlugin::getObjectData("BTCPayments");
        $currency = $objWallet->currency;

        _error_log('BTC::setUpPayment - Starting payment setup for user: ' . $users_id . ', amount: ' . $total_cost . ' ' . $currency, AVideoLog::$DEBUG);

        //return here if total is empty
        if (empty($total_cost)) {
            _error_log('BTC::setUpPayment - ERROR: Total is empty', AVideoLog::$ERROR);
            echo json_encode(array("error" => "Total Is Empty"));
            return false;
        }

        if (!User::isLogged()) {
            _error_log('BTC::setUpPayment - ERROR: User not logged in', AVideoLog::$ERROR);
            echo json_encode(array("error" => "Must be logged in"));
            return false;
        }

        _error_log('BTC::setUpPayment - Calling createBTCPayInvoice', AVideoLog::$DEBUG);
        $invoice = BTCPayments::createBTCPayInvoice($total_cost, $users_id, [], $metadata, $redirectUrl);

        if (!empty($invoice['error'])) {
            _error_log('BTC::setUpPayment - ERROR from marketplace', AVideoLog::$ERROR);
            _error_log('BTC::setUpPayment - Full error response: ' . json_encode($invoice), AVideoLog::$ERROR);
            if (!empty($invoice['msg'])) {
                forbiddenPage($invoice['msg']);
            } else {
                forbiddenPage('Unknown error from BTCPay marketplace');
            }
            exit;
        }

        _error_log('BTC::setUpPayment - Invoice created, attempting to save to database', AVideoLog::$DEBUG);

        // Handle both old and new response formats for backward compatibility
        if (isset($invoice['invoice'])) {
            // New format: invoice data is nested under 'invoice' key
            $invoiceData = $invoice['invoice'];
            _error_log('BTC::setUpPayment - Using nested invoice format', AVideoLog::$DEBUG);
        } else {
            // Old format: invoice data is at root level
            $invoiceData = $invoice;
            _error_log('BTC::setUpPayment - Using root-level invoice format', AVideoLog::$DEBUG);
        }

        if (empty($invoiceData['id'])) {
            _error_log('BTC::setUpPayment - ERROR: Invoice ID is empty in response', AVideoLog::$ERROR);
            _error_log('BTC::setUpPayment - Invoice response: ' . json_encode($invoice), AVideoLog::$ERROR);
            forbiddenPage('Invalid invoice response from marketplace: missing invoice ID');
            exit;
        }

        //var_dump($invoice);exit;
        $o = new Btc_invoices(0);
        $o->setInvoice_identification($invoiceData['id']);
        $o->setUsers_id($users_id);
        $o->setAmount_currency($invoiceData['amount']);
        // amount_btc starts at 0, will be updated by webhook with totalBTCPaid when payment is received
        $o->setAmount_btc(0);
        // Use currency from invoice response if available, fallback to local currency
        $o->setCurrency(!empty($invoiceData['currency']) ? $invoiceData['currency'] : $currency);
        $o->setStatus('a');
        // Same shape the webhook saves (the invoice itself), which is what the history and checkout pages read.
        $o->setJson(json_encode($invoiceData));

        _error_log('BTC::setUpPayment - Saving invoice to database with ID: ' . $invoiceData['id'], AVideoLog::$DEBUG);

        $saved_id = $o->save();
        if (empty($saved_id)) {
            _error_log('BTC::setUpPayment - ERROR: Failed to save invoice to database', AVideoLog::$ERROR);
            forbiddenPage('Failed to save invoice to database');
            exit;
        }

        _error_log('BTC::setUpPayment - SUCCESS: Invoice saved with ID: ' . $saved_id, AVideoLog::$DEBUG);
        $invoice['Btc_invoices_id'] = $saved_id;
        return $invoice;
    }

    /**
     * Process an invoice only after its contents have been verified through the marketplace.
     * $notifyUser: send the 'BTCPayments' socket message (webhook). The checkout page polls instead.
     */
    public static function processVerifiedInvoice($invoice, $notifyUser = true)
    {
        if (empty($invoice->id)) {
            throw new RuntimeException('Missing verified BTCPay invoice ID');
        }
        $resp = new stdClass();
        $resp->status = $invoice->status;
        $users_id = intval($invoice->metadata->users_id ?? 0);
        if ($users_id <= 0) {
            $resp->msg = 'Ignored: invoice without AVideo users_id';
            return $resp;
        }
        $walletObject = AVideoPlugin::getObjectData('YPTWallet');
        if ($invoice->currency !== $walletObject->currency) {
            _error_log('BTC::webhook - Ignored currency mismatch for invoice ' . $invoice->id, AVideoLog::$WARNING);
            $resp->msg = 'Ignored: currency mismatch';
            return $resp;
        }
        $saveInvoice = function () use ($invoice, $resp, $users_id, $notifyUser) {
            $btcInvoice = new Btc_invoices(Btc_invoices::getIdOr0($invoice->id));
            $btcInvoice->setInvoice_identification($invoice->id);
            $btcInvoice->setAmount_currency($invoice->amount);
            $btcInvoice->setAmount_btc($invoice->totalBTCPaid);
            $btcInvoice->setCurrency($invoice->currency);
            $btcInvoice->setStatus('a');
            $btcInvoice->setUsers_id($users_id);
            $btcInvoice->setJson($invoice);
            $resp->btc_invoices_id = $btcInvoice->save($notifyUser);
            if (!$resp->btc_invoices_id) {
                throw new RuntimeException('Cannot save BTCPay invoice');
            }
        };
        if ($invoice->status !== 'Settled') {
            $saveInvoice();
            $resp->msg = 'Status not found to process ' . $invoice->status;
            return $resp;
        }

        $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
        if (empty($wallet)) {
            // Historical duplicates need no wallet operation, even while the wallet is disabled.
            $resp->btc_invoices_id = Btc_invoices::getIdOr0($invoice->id);
            $resp->paymentSaved = empty($resp->btc_invoices_id) ? 0 : Btc_payments::getIdOr0($resp->btc_invoices_id);
            if (!empty($resp->paymentSaved)) {
                $resp->msg = 'Payment already processed';
                return $resp;
            }
            throw new RuntimeException('YPTWallet is disabled; retry the BTCPay payment after enabling it');
        }
        $processed = false;
        $wallet->processPaymentOnce('btcpay', [(string) $invoice->id], $users_id,
            function () use ($invoice, $resp, $saveInvoice, $wallet, $walletObject, $users_id, &$processed) {
                $saveInvoice();
                // Set by BTCPayments::startCheckout() when the invoice was created; older invoices only fund the wallet.
                $purpose = (string) ($invoice->metadata->purpose ?? '');
                $selection = (array) ($invoice->metadata->selection ?? []);
                $fulfillment = 'credited';
                if ($invoice->currency == $walletObject->currency) {
                    $url = $invoice->checkoutLink . '/receipt';
                    $description = number_format($invoice->totalBTCPaid, 8, '.', '')
                        . " BTC from invoice <a href='{$url}' target='_blank'>" . $invoice->id . '</a>';
                    if (!empty($selection['description'])) {
                        // The wallet history shows descriptions as HTML; the video title comes from its uploader.
                        $description .= ': ' . htmlspecialchars(mb_substr(strip_tags((string) $selection['description']), 0, 150, 'UTF-8'), ENT_QUOTES, 'UTF-8');
                    }
                    if (($invoice->metadata->BTCPAY_NETWORK ?? '') !== 'Mainnet') {
                        $description = "[TEST MODE] {$description}";
                    }
                    $wallet->addBalance($users_id, $invoice->amount, $description, json_encode($invoice));
                    // Same as the PayPal/Stripe one-time payments: the savepoint undoes only the purchase (debit,
                    // split, rental/access) when it fails; the paid credit stays so the buyer can finish it later.
                    if ($purpose === 'ppv') {
                        $fulfillment = $wallet->runPaymentStep(function () use ($users_id, $selection) {
                            $ppv = AVideoPlugin::loadPluginIfEnabled('PayPerView');
                            return !empty($ppv) && method_exists($ppv, 'completeWalletCheckout')
                                && $ppv->completeWalletCheckout($users_id, $selection['ppv_plans_id'] ?? 0, $selection['videos_id'] ?? 0,
                                    $selection['ppv_value'] ?? '', $selection['rental_hours'] ?? 0) === true;
                        }, 'Bitcoin PPV rental') ? 'rented' : 'benefit_failed';
                    } elseif ($purpose === 'ppvlive') {
                        $fulfillment = $wallet->runPaymentStep(function () use ($users_id, $selection) {
                            $live = AVideoPlugin::loadPluginIfEnabled('PayPerViewLive');
                            return !empty($live) && method_exists($live, 'completeWalletCheckout')
                                && $live->completeWalletCheckout($users_id, $selection['ppvlive_schedule_id'] ?? 0,
                                    $selection['ppvlive_value'] ?? '', $selection['live_ends'] ?? '') === true;
                        }, 'Bitcoin PPV Live access') ? 'live_ready' : 'benefit_failed';
                    }
                    if ($fulfillment === 'benefit_failed') {
                        _error_log("BTC::webhook - payment credited, but the {$purpose} purchase was not completed; the balance stays in the wallet invoice=" . $invoice->id, AVideoLog::$ERROR);
                    }
                }
                $payment = new Btc_payments(0);
                $payment->setBtc_invoices_id($resp->btc_invoices_id);
                $payment->setTransaction_identification(uniqid());
                $payment->setAmount_received_btc($invoice->totalBTCPaid);
                $payment->setConfirmations(0);
                $payment->setStore($invoice->storeId);
                // The payment row is written once, so it keeps what the checkout page reports after the credit.
                $paymentJson = clone $invoice;
                $paymentJson->avideo_fulfillment = $fulfillment;
                $payment->setJson($paymentJson);
                $resp->paymentSaved = $payment->save();
                if (!$resp->paymentSaved) {
                    throw new RuntimeException('Cannot save BTCPay payment');
                }
                $resp->fulfillment = $fulfillment;
                $processed = true;
                return true;
            }, ['legacyCheck' => function () use ($invoice) {
                $id = Btc_invoices::getIdOr0($invoice->id);
                return !empty($id) && !empty(Btc_payments::getIdOr0($id));
            }]);
        // Both the wallet marker and a pre-marker btc_payments row acknowledge a duplicate.
        $resp->btc_invoices_id = Btc_invoices::getIdOr0($invoice->id);
        $resp->paymentSaved = Btc_payments::getIdOr0($resp->btc_invoices_id);
        if (!$processed) {
            $resp->msg = 'Payment already processed';
        }
        return $resp;
    }

    public static function profileTabName($users_id)
    {
        $p = AVideoPlugin::loadPlugin("BTCPayments");
        $obj = $p->getDataObject();
        return '<li><a data-toggle="tab" href="#proftab' . $p->getUUID() . '"><i class="fa-brands fa-btc"></i> ' . __('BTC History') . '</a></li>';
    }

    public static function profileTabContent($users_id)
    {
        global $global;
        $p = AVideoPlugin::loadPlugin("BTCPayments");
        $obj = $p->getDataObject();
        $tabId = 'proftab' . $p->getUUID();
        include $global['systemRootPath'] . 'plugin/BTCPayments/View/profileTabContent.php';
        return "";
    }

    /** The AVideo page that follows one Bitcoin invoice: what to pay, live status and the next step. */
    public static function getCheckoutPageURL($invoiceId)
    {
        global $global;
        return $global['webSiteRootURL'] . 'plugin/BTCPayments/checkout.php?invoice=' . rawurlencode($invoiceId);
    }

    /**
     * Starts a one-time Bitcoin payment with the same rules as PayPalYPT::startOneTimeCheckout(): wallet credit,
     * or a PPV rental / PPV Live event priced on the server, the wallet balance paying first.
     * Returns ['invoice' => ..., 'message' => ...] (send the buyer to getCheckoutPageURL()); throws
     * BTCPaymentsCheckoutRefused with a buyer-facing message for expected refusals.
     */
    public static function startCheckout($usersId, array $request)
    {
        global $config, $global;
        $usersId = intval($usersId);
        $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
        if ($usersId < 1 || empty($wallet)) {
            throw new BTCPaymentsCheckoutRefused(__('Please sign in to pay.'));
        }
        $ready = self::checkIfIsAllGood();
        if (!empty($ready->error)) {
            _error_log('BTC checkout: Bitcoin payments are not configured: ' . $ready->msg, AVideoLog::$ERROR);
            throw new BTCPaymentsCheckoutRefused(__('Bitcoin payments are not available right now. You were not charged. Please try again.'));
        }
        $currency = strtoupper((string) $wallet->getDataObject()->currency);
        $balance = round(floatval($wallet->getBalance($usersId)), 2);
        $plugin = (string) ($request['plugin'] ?? '');
        $siteTitle = mb_substr(trim(strip_tags((string) $config->getWebSiteTitle())), 0, 60, 'UTF-8');
        $ppv = $plugin === 'PayPerView' ? AVideoPlugin::loadPluginIfEnabled('PayPerView') : false;
        $live = $plugin === 'PayPerViewLive' ? AVideoPlugin::loadPluginIfEnabled('PayPerViewLive') : false;
        $videosId = intval($request['videos_id'] ?? 0);
        // Only one movie can be rented automatically; whole-plan purchases and older PPV/PPV Live versions
        // fund the wallet, then the buyer completes the purchase with the balance.
        $autoRental = !empty($ppv) && method_exists($ppv, 'getPaymentDetails') && method_exists($ppv, 'completeWalletCheckout') && $videosId > 0;
        $autoLive = !empty($live) && method_exists($live, 'getPaymentDetails') && method_exists($live, 'completeWalletCheckout');
        if ($autoRental) {
            $plansId = intval($request['plans_id'] ?? 0);
            $details = $ppv->getPaymentDetails($plansId, $videosId);
            if (empty($details) || floatval($details['value']) <= 0) {
                throw new BTCPaymentsCheckoutRefused(__('This rental is not available for Bitcoin checkout.'));
            }
            if (class_exists('PPV_Plans_Users_Videos') && PPV_Plans_Users_Videos::userCanWatchVideo($usersId, $videosId)) {
                throw new BTCPaymentsCheckoutRefused(__('You already have access to this video. No payment is needed.'));
            }
            $price = round(floatval($details['value']), 2);
            $purpose = 'ppv';
            $selection = ['ppv_plans_id' => $plansId, 'videos_id' => $videosId,
                'rental_hours' => intval($details['metadata']['rental_hours']), 'ppv_value' => number_format($price, 2, '.', ''),
                'video_title' => $details['metadata']['video_title'] ?? '', 'plan_name' => $details['metadata']['plan_name'] ?? '',
                'description' => $details['description']];
        } elseif ($autoLive) {
            $scheduleId = intval($request['ppvlive_schedule_id'] ?? 0);
            $details = $live->getPaymentDetails($scheduleId);
            if (empty($details)) {
                throw new BTCPaymentsCheckoutRefused(__('This event is not available for purchase.'));
            }
            $purchase = $live->getSubscriptionToPlan($usersId, $scheduleId);
            if (!empty($purchase['id']) && $purchase['status'] === 'a') {
                throw new BTCPaymentsCheckoutRefused(__('You already have access to this event. No payment is needed.'));
            }
            $price = round(floatval($details['value']), 2);
            $purpose = 'ppvlive';
            $selection = ['ppvlive_schedule_id' => $scheduleId, 'ppvlive_value' => number_format($price, 2, '.', ''),
                'live_starts' => $details['metadata']['live_starts'], 'live_ends' => $details['metadata']['live_ends'],
                'event_name' => $details['metadata']['event_name'], 'live_owner' => intval($details['owner']),
                'description' => $details['description']];
        } else {
            $price = round(floatval(parseToFloat($request['value'] ?? 0)), 2);
            if ($price <= 0 || $price > 100000) {
                throw new BTCPaymentsCheckoutRefused(__('Invalid Value'));
            }
            $purpose = 'wallet';
            $selection = ['description' => $siteTitle . ' wallet credit'];
            $balance = 0;
            // Back to the page that asked for the funds (PPV plan, event, plans page), never to another site.
            $returnURL = (string) YPTWallet::getAddFundsSuccessRedirectURL();
            if ($plugin !== '' && $returnURL !== '' && isSameDomainAsMyAVideo($returnURL)) {
                $selection['return_url'] = $returnURL;
            }
        }
        // The wallet balance pays first; Bitcoin pays only the rest.
        $amount = round(max(0, $price - $balance), 2);
        if ($amount <= 0) {
            throw new BTCPaymentsCheckoutRefused(__('Your wallet balance already covers this purchase. Reload the page and pay with your balance; no Bitcoin payment is needed.'));
        }
        $selection['price'] = number_format($price, 2, '.', '');
        $selection['balance_applied'] = number_format($price - $amount, 2, '.', '');
        $network = !empty(AVideoPlugin::getDataObject('BTCPayments')->useTestNet) ? 'Testnet' : 'Mainnet';
        $hash = sha1(json_encode([$usersId, $purpose, $selection, number_format($amount, 2, '.', ''), $currency, $network]));
        $previous = self::findOpenCheckout($usersId, $hash);
        if (!empty($previous)) {
            return $previous;
        }
        $orderId = $usersId . '-' . date('YmdHis') . '-' . substr(_uniqid(), -6);
        $metadata = ['description' => $selection['description'], 'orderId' => $orderId, 'purpose' => $purpose,
            'selection' => $selection, 'selectionHash' => $hash];
        // BTCPay's "Return to store" button; the order finds this invoice again (payment-complete.php).
        $redirectUrl = $global['webSiteRootURL'] . 'plugin/BTCPayments/payment-complete.php?order=' . rawurlencode($orderId);
        $response = self::createBTCPayInvoice($amount, $usersId, [], $metadata, $redirectUrl, true);
        $invoiceData = (isset($response['invoice']) && is_array($response['invoice'])) ? $response['invoice'] : $response;
        if (!empty($response['error']) || !empty($invoiceData['error']) || empty($invoiceData['id'])) {
            _error_log("BTC checkout: the marketplace did not create the invoice users_id={$usersId} purpose={$purpose}", AVideoLog::$ERROR);
            throw new BTCPaymentsCheckoutRefused(__('Bitcoin payments are not available right now. You were not charged. Please try again.'));
        }
        $row = new Btc_invoices(0);
        $row->setInvoice_identification($invoiceData['id']);
        $row->setUsers_id($usersId);
        $row->setAmount_currency($invoiceData['amount'] ?? $amount);
        $row->setAmount_btc(0);
        $row->setCurrency(!empty($invoiceData['currency']) ? $invoiceData['currency'] : $currency);
        $row->setStatus('a');
        $row->setJson(json_encode($invoiceData));
        if (empty($row->save())) {
            _error_log("BTC checkout: invoice {$invoiceData['id']} was created but could not be saved users_id={$usersId}", AVideoLog::$ERROR);
            throw new BTCPaymentsCheckoutRefused(__('Bitcoin payments are not available right now. You were not charged. Please try again.'));
        }
        _error_log("BTC checkout created invoice={$invoiceData['id']} users_id={$usersId} purpose={$purpose} amount={$amount} {$currency}");
        return ['invoice' => (string) $invoiceData['id'], 'message' => ''];
    }

    /**
     * An earlier invoice for the same purchase: reuse it while it can still be paid (double click, refresh,
     * second tab), and never start a second one while a payment is being confirmed (the buyer would pay twice).
     */
    private static function findOpenCheckout($usersId, $hash)
    {
        $checks = 0;
        foreach (Btc_invoices::getRecentFromUser($usersId, 10) as $row) {
            $data = self::getInvoiceData($row);
            if (($data['metadata']['selectionHash'] ?? '') !== $hash) {
                continue;
            }
            $status = self::getPublicInvoiceStatus($row);
            $raw = strtolower((string) ($data['status'] ?? ''));
            if ($status['credited'] || in_array($raw, ['expired', 'invalid'], true)) {
                continue;
            }
            if ($status['status'] === 'new' && $status['expires_in'] > 120) {
                return ['invoice' => $status['invoice'], 'message' => ''];
            }
            if ($checks++ >= 3) {
                break;
            }
            // Saved as unpaid or still confirming: BTCPay may know more (a payment sent just before it expired).
            try {
                $status = self::getPublicInvoiceStatus(self::reconcileInvoice($row));
            } catch (\Throwable $th) {
                _error_log('BTC checkout: could not check the earlier invoice ' . $row['invoice_identification'] . ': ' . $th->getMessage(), AVideoLog::$WARNING);
                if ($raw === 'new') {
                    continue;
                }
            }
            if (!$status['final'] && $status['status'] !== 'new') {
                return ['invoice' => $status['invoice'], 'message' => __('Your previous Bitcoin payment is still being confirmed. No new payment was created.')];
            }
            if ($status['status'] === 'new' && $status['expires_in'] > 120) {
                return ['invoice' => $status['invoice'], 'message' => ''];
            }
        }
        return false;
    }

    /** The invoice saved in btc_invoices.json. Rows saved before 2026-10-22 hold the whole marketplace answer. */
    public static function getInvoiceData($row)
    {
        $json = json_decode((string) ($row['json'] ?? ''), true);
        if (!is_array($json)) {
            return [];
        }
        if (!isset($json['status']) && isset($json['invoice']) && is_array($json['invoice'])) {
            return $json['invoice'];
        }
        return $json;
    }

    /**
     * Reads one invoice through the marketplace, the same check the webhook relies on. Returns the invoice object
     * (status, metadata, payment methods) or throws when it cannot be verified.
     */
    public static function fetchVerifiedInvoice($invoiceId)
    {
        $invoiceId = (string) $invoiceId;
        if (!preg_match('/^[A-Za-z0-9_-]{4,64}$/', $invoiceId)) {
            throw new RuntimeException('Invalid BTCPay invoice ID');
        }
        $obj = AVideoPlugin::getDataObject('BTCPayments');
        $url = self::getMarketplaceURL('invoice.verify.json.php');
        $url = addQueryStringParameter($url, 'invoiceId', $invoiceId);
        $url = addQueryStringParameter($url, 'BTCMarketPlaceKey', $obj->BTCMarketPlaceKey);
        // The marketplace is a fixed public HTTPS server: verified unless $global['verifySSLCertificates'] = 0.
        $content = url_get_contents($url, '', 0, false, false, false, shouldVerifySSLCertificates(true));
        $json = empty($content) ? null : json_decode($content);
        if (empty($json->invoice->id) || (string) $json->invoice->id !== $invoiceId) {
            _error_log("BTC::fetchVerifiedInvoice - could not verify invoice {$invoiceId} (response " . strlen((string) $content) . ' bytes)', AVideoLog::$ERROR);
            throw new RuntimeException('Could not verify the BTCPay invoice');
        }
        return $json->invoice;
    }

    /**
     * Applies the current invoice (credit and purchase when settled), like the webhook does, and returns the
     * updated btc_invoices row. Lets the checkout page finish a payment whose notification never arrived.
     */
    public static function reconcileInvoice($row, $notifyUser = false)
    {
        $invoice = self::fetchVerifiedInvoice($row['invoice_identification']);
        if (intval($invoice->metadata->users_id ?? 0) !== intval($row['users_id'])) {
            throw new RuntimeException('The BTCPay invoice ' . $row['invoice_identification'] . ' belongs to another user');
        }
        self::processVerifiedInvoice($invoice, $notifyUser);
        $fresh = Btc_invoices::getFromInvoice_identification($row['invoice_identification']);
        return empty($fresh) ? $row : $fresh;
    }

    /** The on-chain Bitcoin payment method of an invoice: address, amount due and bitcoin: link. */
    private static function getBitcoinPaymentMethod(array $invoice)
    {
        foreach ((array) ($invoice['payments'] ?? []) as $method) {
            $method = (array) $method;
            $id = (string) ($method['paymentMethodId'] ?? ($method['paymentMethod'] ?? ''));
            if (in_array($id, ['BTC-CHAIN', 'BTC', 'BTC-OnChain'], true)) {
                return $method;
            }
        }
        return [];
    }

    /** The buyer's view of one invoice: no marketplace, store or wallet internals. */
    public static function getPublicInvoiceStatus($row)
    {
        global $global;
        $invoice = self::getInvoiceData($row);
        $metadata = (array) ($invoice['metadata'] ?? []);
        $selection = (array) ($metadata['selection'] ?? []);
        $purpose = (string) ($metadata['purpose'] ?? 'wallet');
        $status = strtolower((string) ($invoice['status'] ?? 'new'));
        if (!in_array($status, ['new', 'processing', 'settled', 'expired', 'invalid'], true)) {
            $status = 'new';
        }
        $expiration = intval($invoice['expirationTime'] ?? 0);
        if ($status === 'new' && $expiration > 0 && $expiration <= time()) {
            // Unpaid when it was last saved; BTCPay confirms it on the next check.
            $status = 'expired';
        }
        $payment = Btc_payments::getFromInvoice_identification(intval($row['id']));
        $credited = !empty($payment['id']);
        $fulfillment = '';
        if ($credited) {
            $paymentJson = json_decode((string) ($payment['json'] ?? ''), true);
            $fulfillment = (string) ($paymentJson['avideo_fulfillment'] ?? 'credited');
        }
        $usersId = intval($row['users_id']);
        $network = (string) ($metadata['BTCPAY_NETWORK'] ?? '');
        $description = (string) ($selection['description'] ?? ($metadata['description'] ?? ''));
        $btcPaid = floatval($invoice['totalBTCPaid'] ?? ($row['amount_btc'] ?? 0));
        $checkoutLink = (string) ($invoice['checkoutLink'] ?? '');
        if (!preg_match('#^https://[^\s"\'<>]+$#i', $checkoutLink)) {
            $checkoutLink = '';
        }
        $public = [
            'invoice' => (string) $row['invoice_identification'],
            'status' => $status,
            'detail' => (string) ($invoice['additionalStatus'] ?? ''),
            'credited' => $credited,
            'fulfillment' => $fulfillment,
            'final' => $credited || $status === 'expired' || $status === 'invalid',
            'purpose' => $purpose,
            'description' => $description === '' ? __('Wallet credit') : mb_substr(strip_tags($description), 0, 200, 'UTF-8'),
            'amount' => YPTWallet::formatCurrency(floatval($invoice['amount'] ?? ($row['amount_currency'] ?? 0))),
            'price' => isset($selection['price']) ? YPTWallet::formatCurrency($selection['price']) : '',
            'balance_applied' => floatval($selection['balance_applied'] ?? 0) > 0 ? YPTWallet::formatCurrency($selection['balance_applied']) : '',
            'test' => $network === '' ? !empty(AVideoPlugin::getDataObject('BTCPayments')->useTestNet) : $network !== 'Mainnet',
            'expires_in' => $status === 'new' ? max(0, $expiration - time()) : 0,
            'expires_total' => max(0, $expiration - intval($invoice['createdTime'] ?? 0)),
            'created' => intval($row['created_php_time'] ?? ($invoice['createdTime'] ?? 0)),
            'btc_paid' => $btcPaid > 0 ? number_format($btcPaid, 8, '.', '') : '',
            'payment' => null,
            'checkout_url' => '',
            'receipt_url' => '',
            'access_until' => '',
            'continue_url' => '',
            'continue_label' => '',
            'retry_url' => '',
        ];
        if ($status === 'new') {
            $method = self::getBitcoinPaymentMethod($invoice);
            $address = (string) ($method['destination'] ?? '');
            $due = $method['due'] ?? ($method['amount'] ?? '');
            if (preg_match('/^[A-Za-z0-9]{14,100}$/', $address) && is_numeric($due) && floatval($due) > 0) {
                $btcAmount = number_format(floatval($due), 8, '.', '');
                $link = (string) ($method['paymentLink'] ?? '');
                $public['payment'] = [
                    'address' => $address,
                    'btc_amount' => $btcAmount,
                    // It becomes an href: only a bitcoin: link of this address is accepted.
                    'uri' => stripos($link, 'bitcoin:' . $address) === 0 && !preg_match('/[\s"\'<>]/', $link)
                        ? $link : 'bitcoin:' . $address . '?amount=' . $btcAmount,
                ];
            }
            $public['checkout_url'] = $checkoutLink;
        } elseif (($status === 'settled' || $credited) && $checkoutLink !== '') {
            $public['receipt_url'] = $checkoutLink . '/receipt';
        }
        $videosId = intval($selection['videos_id'] ?? 0);
        if ($purpose === 'ppv') {
            $public['retry_url'] = class_exists('PayPerView') ? PayPerView::getBuyURL($videosId) : $global['webSiteRootURL'];
            if ($credited && $fulfillment === 'rented') {
                $public['continue_url'] = $global['webSiteRootURL'] . 'video/' . $videosId . '?autoplay=1';
                $public['continue_label'] = __('Watch Now');
                $rental = class_exists('PPV_Plans_Users_Videos') ? PPV_Plans_Users_Videos::getActivePlan($usersId, $videosId) : false;
                $public['access_until'] = !empty($rental['expiration']) ? (string) $rental['expiration']
                    : sprintf(__('Valid for %d hours'), intval($selection['rental_hours'] ?? 0));
            } else {
                $public['continue_url'] = $public['retry_url'];
                $public['continue_label'] = __('Complete your rental with your wallet balance');
            }
        } elseif ($purpose === 'ppvlive') {
            $eventURL = $global['webSiteRootURL'] . 'plugin/PayPerViewLive/View/buy.php?users_id=' . intval($selection['live_owner'] ?? 0)
                . '&ppvlive_schedule_id=' . intval($selection['ppvlive_schedule_id'] ?? 0);
            $public['retry_url'] = $eventURL;
            $public['continue_url'] = $eventURL;
            if ($credited && $fulfillment === 'live_ready') {
                $started = strtotime(($selection['live_starts'] ?? '') . ' UTC') <= time();
                $public['access_until'] = (string) ($selection['live_ends'] ?? '') . ' UTC';
                $public['live_starts'] = (string) ($selection['live_starts'] ?? '') . ' UTC';
                $public['live_started'] = $started;
                if ($started && class_exists('Live')) {
                    $public['continue_url'] = Live::getLinkToLiveFromUsers_id(intval($selection['live_owner'] ?? 0));
                    $public['continue_label'] = __('Watch Now');
                } else {
                    $public['continue_label'] = __('View event details');
                }
            } else {
                $public['continue_label'] = __('Complete your event purchase with your wallet balance');
            }
        } elseif (!empty($selection['return_url']) && isSameDomainAsMyAVideo($selection['return_url'])) {
            $public['retry_url'] = $selection['return_url'];
            $public['continue_url'] = $selection['return_url'];
            $public['continue_label'] = __('Continue with your purchase');
        } else {
            $public['retry_url'] = $global['webSiteRootURL'] . 'plugin/YPTWallet/view/addFunds.php';
            $public['continue_url'] = $public['retry_url'];
            $public['continue_label'] = __('View my wallet');
        }
        $public['balance'] = YPTWallet::formatCurrency(YPTWallet::getUserBalance($usersId));
        return $public;
    }

    static function checkIfIsAllGood()
    {
        $obj = AVideoPlugin::getDataObject('BTCPayments');
        $resp = new stdClass();
        $resp->error = true;
        $resp->msg = '';

        if (empty($obj->BTCMarketPlaceKey)) {
            $resp->msg = 'BTCMarketPlaceKey is empty';
            return $resp;
        }

        $resp->error = false;
        return $resp;
    }

    public function getHeadCode()
    {
        global $global, $config;
        $js = '';
        // Texts of the payment notifications shown by script.js on any page.
        $js .= '<script>var BTCPaymentsTexts = ' . json_encode([
            'processing' => __('We received your Bitcoin payment. It is being confirmed by the network; do not pay again.'),
            'settled' => __('Your Bitcoin payment is confirmed.'),
        ]) . ';</script>';
        $js .= '<script src="' . getURL('plugin/BTCPayments/script.js') . '" type="text/javascript"></script>';
        return $js;
    }
}

/** An expected refusal of a Bitcoin checkout; its message is shown to the buyer. */
class BTCPaymentsCheckoutRefused extends RuntimeException
{
}
