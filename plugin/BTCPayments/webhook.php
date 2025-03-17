<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
header('Content-Type: application/json');
$global['bypassSameDomainCheck'] = 1;
$obj = AVideoPlugin::getDataObjectIfEnabled('BTCPayments');
if (empty($obj)) {
    forbiddenPage('Plugin is disabled');
}

$log = getVideosDir() . 'btc_webhook.log';

if (empty($_REQUEST['invoiceId'])) {
    forbiddenPage('Empty Invoice ID');
}

$resp = new stdClass();

$resp->url = BTCPayments::getMarketplaceURL('invoice.verify.json.php');
$resp->url = addQueryStringParameter($resp->url, 'invoiceId', $_REQUEST['invoiceId']);
$resp->url = addQueryStringParameter($resp->url, 'BTCMarketPlaceKey', $obj->BTCMarketPlaceKey);

$resp->content = url_get_contents($resp->url);
if (empty($resp->content)) {
    forbiddenPage('Empty Content');
}

$resp->json = json_decode($resp->content);
if (!empty($resp->json)) {
    unset($resp->content);
}

file_put_contents($log, '[' . date('Y/m/d H:i:s') . '] ' . json_encode($resp) . PHP_EOL, FILE_APPEND);

$invoice = $resp->json->invoice;

if (empty($invoice)) {
    forbiddenPage('Empty invoice');
}

$id = Btc_invoices::getIdOr0($invoice->id);

$btcInvoice = new Btc_invoices($id);
$btcInvoice->setInvoice_identification($invoice->id);
$btcInvoice->setAmount_currency($invoice->amount);
$btcInvoice->setAmount_btc($invoice->totalBTCPaid);
$btcInvoice->setCurrency($invoice->currency);
$btcInvoice->setStatus('a');
$btcInvoice->setUsers_id($invoice->metadata->users_id);
$btcInvoice->setJson($invoice);

$resp->btc_invoices_id = $btcInvoice->save(true);
$resp->status = $invoice->status;
if ($resp->btc_invoices_id) {
    switch ($invoice->status) {
        case 'Settled':
            $resp->paymentSaved = Btc_payments::getIdOr0($resp->btc_invoices_id);
            if (empty($resp->paymentSaved)) {
                file_put_contents($log, '[' . date('Y/m/d H:i:s') . '] Save Payment' . PHP_EOL, FILE_APPEND);
                $o = new Btc_payments(0);
                $o->setBtc_invoices_id($resp->btc_invoices_id);
                $o->setTransaction_identification(uniqid());
                $o->setAmount_received_btc($invoice->totalBTCPaid);
                $o->setConfirmations(0);
                $o->setStore($invoice->storeId);
                $o->setJson($invoice);

                $resp->paymentSaved = $o->save();
                if ($resp->paymentSaved) {
                    $obj = AVideoPlugin::getObjectData('YPTWallet');
                    if ($invoice->currency == $obj->currency) {
                        file_put_contents($log, '[' . date('Y/m/d H:i:s') . '] ADD balance ' . $invoice->amount . PHP_EOL, FILE_APPEND);
                        $plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
                        $url = $invoice->checkoutLink.'/receipt';
                        $description = number_format($invoice->totalBTCPaid, 8, '.', '') . " BTC from invoice <a href='{$url}' target='_blank'>" . $invoice->id . '</a>';
                        if ($invoice->metadata->BTCPAY_NETWORK !== 'Mainnet') {
                            $description = "[TEST MODE] {$description}";
                        }

                        $plugin->addBalance($invoice->metadata->users_id, $invoice->amount, $description, json_encode($invoice));
                    }
                }
            } else {
                $resp->msg = 'Payment already processed';
            }
            break;

        default:
            file_put_contents($log, '[' . date('Y/m/d H:i:s') . '] Status not found to process ' . $invoice->status . PHP_EOL, FILE_APPEND);

            $resp->msg = 'Status not found to process ' . $invoice->status;
            break;
    }
}

echo json_encode($resp);
