<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_invoices.php';
require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_payments.php';


define("BTC_MARKETPLACE_URL", "https://youphp.tube/marketplace/BTC/"); // Replace with your BTCPay server URL
//define("BTC_MARKETPLACE_URL", "http://192.168.0.2:81/youphptube.com/marketplace/BTC/"); // Replace with your BTCPay server URL

class BTCPayments extends PluginAbstract
{


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
        return "1.0";
    }

    public function updateScript()
    {
        global $global;
        /*
        if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
            sqlDal::executeFile($global['systemRootPath'] . 'plugin/PayPerView/install/updateV2.0.sql');
        }
         *
         */
        return true;
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();

        $obj->siteWallet = "";
        //$obj->siteWalletTest = "tb1qdv8zs8c9ukr636gcmznz2pa8y5zxg7u8ghufkq";
        $obj->BTCMarketPlaceKey = "";
        //$obj->useTestNet = true;
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
        $url = BTC_MARKETPLACE_URL . 'status.json.php';
        $url = addQueryStringParameter($url, 'siteUrl', $global['webSiteRootURL']);
        $url = addQueryStringParameter($url, 'siteId', getPlatformId());
        $url = addQueryStringParameter($url, 'APISecret', $api->APISecret);

        $btn  = '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/BTCPayments/View/editor.php\')" class="btn btn-primary btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
        $btn .= '<button onclick="avideoAlertAJAX(\'' . $url . '\');" class="btn btn-primary btn-xs btn-block"><i class="fa-solid fa-list-check"></i> Status</button>';
        return $btn;
    }

    /**
     * Create a BTCPay Invoice
     *
     * @param float $amount Amount in currency
     * @param string $currency Currency code (e.g., USD, EUR, BTC)
     * @param string|null $buyerEmail Buyer's email address (optional)
     * @param string|null $redirectUrl URL to redirect after payment (optional)
     * @return array Invoice details or error message
     *
     * // Creating an invoice with three wallets (splitting the payment)
     * $walletSplits = [
     *     ["destination" => "bc1qexample1", "percentage" => 50],  // 50% to wallet 1
     *     ["destination" => "bc1qexample2", "percentage" => 30],  // 30% to wallet 2
     *     ["destination" => "bc1qexample3", "percentage" => 20]   // 20% to wallet 3
     * ];
     */
    static function createBTCPayInvoice($amount, $users_id, $walletSplits = [], $metadata = array(), $redirectUrl = null)
    {
        global $global;

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
        $data["checkout"] = ["redirectURL" => $redirectUrl];

        $obj = AVideoPlugin::getDataObject('BTCPayments');

        $data["walletSplits"] = array();
        $remainPercentage = 100;
        if (!empty($walletSplits)) {
            foreach ($walletSplits as $key => $value) {
                $data["walletSplits"][] =  ["destination" => $value['destination'], "percentage" => floatval($value['percentage'])];
                $remainPercentage -= $value['percentage'];
            }
        }

        $data["walletSplits"][] =  ["destination" => $obj->siteWallet, "percentage" => floatval($remainPercentage)];

        $tmpFileName = _uniqid() . '.btc.json';

        $dir = getVideosDir() . 'BTCLog/';
        make_path($dir);

        $tmpFilePath = $dir . $tmpFileName;

        file_put_contents($tmpFilePath, json_encode($data));

        $url = BTC_MARKETPLACE_URL . 'invoice.json.php';

        $url = addQueryStringParameter($url, 'BTCMarketPlaceKey', $obj->BTCMarketPlaceKey);
        $url = addQueryStringParameter($url, 'tmpFileName', $tmpFileName);

        $content = url_get_contents($url);

        if (empty($content)) {
            forbiddenPage("Could not get the content from URL {$url}");
        }

        $json = json_decode($content, true);

        if (empty($json)) {
            forbiddenPage("Could not decode json from the content from URL {$url} {$content}");
        }

        return $json;
    }

    static public function setUpPayment($total_cost = '1.00', $users_id, $metadata = array(), $redirectUrl = null)
    {
        global $global;
        $total_cost = floatval($total_cost);
        $objWallet = AVideoPlugin::getObjectData("BTCPayments");
        $currency = $objWallet->currency;
        //return here if total is empty
        if (empty($total_cost)) {
            echo json_encode(array("error" => "Total Is Empty"));
            return false;
        }

        if (!User::isLogged()) {
            echo json_encode(array("error" => "Must be logged in"));
            return false;
        }


        $invoice = BTCPayments::createBTCPayInvoice($total_cost, $users_id, [], $metadata, $redirectUrl);
        if($invoice['error']){
            _error_log('BTC::setUpPayment '.json_encode($invoice), AVideoLog::$ERROR);
            forbiddenPage($invoice['msg']);
            exit;
        }
        $o = new Btc_invoices(0);
        $o->setInvoice_identification($invoice['id']);
        $o->setUsers_id($users_id);
        $o->setAmount_currency($invoice['amount']);
        //$o->setAmount_btc($_POST['amount_btc']);
        $o->setCurrency($currency);
        $o->setStatus('a');
        $o->setJson(json_encode($invoice));
        $invoice['Btc_invoices_id'] = $o->save();
        return $invoice;
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
}
