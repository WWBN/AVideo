<?php

namespace Tests\Integration\PaymentWalletMySQL;

use AVideoTests\PaymentWalletMySQL\StripeIds;
use AVideoTests\PaymentWalletMySQL\WalletLog;
use AVideoTests\PaymentWalletMySQL\YPTWallet;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function AVideoTests\PaymentWalletMySQL\connect;
use function AVideoTests\PaymentWalletMySQL\deliver;

/**
 * Real InnoDB transactions, savepoints, GET_LOCK and the real payment SQL. Run ONLY against a disposable
 * server: AVIDEO_PAYMENT_MYSQL_TESTS=1 AVIDEO_TEST_DB_PORT=3399 vendor/bin/phpunit tests/Integration/PaymentWalletMySQLIntegrationTest.php
 * Each test creates and drops its own random database.
 */
class PaymentWalletMySQLIntegrationTest extends TestCase
{
    private $db;
    private $database;
    private $savedGlobals = [];

    protected function setUp(): void
    {
        if (getenv('AVIDEO_PAYMENT_MYSQL_TESTS') !== '1') {
            $this->markTestSkipped('Set AVIDEO_PAYMENT_MYSQL_TESTS=1 to use a disposable test MySQL/MariaDB server.');
        }
        require_once dirname(__DIR__) . '/fixtures/payment-wallet-mysql.php';
        foreach (['global', 'mysqlDatabase', 'paymentSimLogs', 'paymentFixtureCLI', 'readSqlCached', 'disableMysqlNdMethods', 'mysql_connect_was_closed'] as $name) {
            $this->savedGlobals[$name] = $GLOBALS[$name] ?? null;
        }
        $this->database = 'avideo_payment_test_' . bin2hex(random_bytes(6));
        $this->db = connect();
        $this->db->query("CREATE DATABASE `{$this->database}`");
        $this->db->select_db($this->database);
        $this->db->query('CREATE TABLE users (id INT NOT NULL PRIMARY KEY) ENGINE = InnoDB');
        $this->db->query('INSERT INTO users (id) VALUES (7), (8)');
        // The real plugin schema, so column sizes and constraints match production.
        $schema = file_get_contents(dirname(__DIR__, 2) . '/plugin/YPTWallet/install/install.sql');
        $this->db->multi_query($schema);
        while ($this->db->more_results() && $this->db->next_result()) {
        }
        $this->db->query(file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/install/install.sql'));
        $this->db->multi_query(file_get_contents(dirname(__DIR__, 2) . '/plugin/BTCPayments/install/install.sql'));
        while ($this->db->more_results() && $this->db->next_result()) {
        }
        $this->db->query('CREATE TABLE renewals (id INT AUTO_INCREMENT PRIMARY KEY, users_id INT) ENGINE = InnoDB');
        $GLOBALS['global'] = ['mysqli' => $this->db, 'webSiteRootURL' => 'https://payment.example.test/'];
        $GLOBALS['mysqlDatabase'] = $this->database;
        $GLOBALS['paymentSimLogs'] = [];
        $GLOBALS['readSqlCached'] = [];
        $GLOBALS['disableMysqlNdMethods'] = false;
        \AVideoTests\PaymentWalletMySQL\AVideoPlugin::$ppv = false;
        \AVideoTests\PaymentWalletMySQL\PpvRentalModel::$fail = false;
        \AVideoTests\PaymentWalletMySQL\TestStripeCharge::$amount = 199;
    }

    protected function tearDown(): void
    {
        if ($this->db) {
            $this->db->query("DROP DATABASE IF EXISTS `{$this->database}`");
            $this->db->close();
        }
        foreach ($this->savedGlobals as $name => $value) {
            $GLOBALS[$name] = $value;
        }
    }

    private function scalar($sql)
    {
        $row = $this->db->query($sql)->fetch_row();
        return $row[0];
    }

    private function balance($users_id)
    {
        return (float) $this->scalar('SELECT IFNULL(SUM(balance), 0) FROM wallet WHERE users_id = ' . intval($users_id));
    }

    public function testPpvWebhookCommitsCreditDebitAndRentalOnce()
    {
        $this->db->query('CREATE TABLE test_ppv_rentals (id INT AUTO_INCREMENT PRIMARY KEY, users_id INT, ppv_plans_id INT, videos_id INT) ENGINE=InnoDB');
        \AVideoTests\PaymentWalletMySQL\AVideoPlugin::$ppv = new \AVideoTests\PaymentWalletMySQL\PpvRentalModel();
        $stripe = new \AVideoTests\PaymentWalletMySQL\StripeYPT();
        $event = $this->ppvEvent('evt_rental', 'ch_rental', 'pi_rental', 199, '1.99');
        $this->assertTrue($stripe->processSinglePaymentIPN($event));
        $this->assertTrue($stripe->processSinglePaymentIPN($event));
        $this->assertSame(0.0, $this->balance(7));
        $this->assertSame(1.99, $this->balance(8));
        $this->assertSame('1', $this->scalar('SELECT COUNT(*) FROM test_ppv_rentals WHERE users_id=7 AND videos_id=3419 AND ppv_plans_id=2'));
        $this->assertSame('1', $this->scalar("SELECT COUNT(*) FROM wallet_log WHERE type='addBalance'"));
    }

    public function testFailedPpvRentalKeepsTheCardCreditAndUndoesOnlyTheRentalWithARealSavepoint()
    {
        $this->db->query('CREATE TABLE test_ppv_rentals (id INT AUTO_INCREMENT PRIMARY KEY, users_id INT, ppv_plans_id INT, videos_id INT) ENGINE=InnoDB');
        \AVideoTests\PaymentWalletMySQL\AVideoPlugin::$ppv = new \AVideoTests\PaymentWalletMySQL\PpvRentalModel();
        \AVideoTests\PaymentWalletMySQL\PpvRentalModel::$fail = true; // fails after its debit and INSERT
        $stripe = new \AVideoTests\PaymentWalletMySQL\StripeYPT();
        $event = $this->ppvEvent('evt_failed', 'ch_failed', 'pi_failed', 199, '1.99');
        $this->assertTrue($stripe->processSinglePaymentIPN($event));
        \AVideoTests\PaymentWalletMySQL\PpvRentalModel::$fail = false;
        $this->assertTrue($stripe->processSinglePaymentIPN($event), 'Redelivery is a duplicate, not a second credit');
        $this->assertSame(1.99, $this->balance(7));
        $this->assertSame(0.0, $this->balance(8));
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM test_ppv_rentals'));
        $this->assertSame('1', $this->scalar("SELECT COUNT(*) FROM wallet_log WHERE type='addBalance'"));
        $this->assertSame('0', $this->scalar("SELECT COUNT(*) FROM wallet_log WHERE type LIKE 'transferBalance%'"));
        $this->assertTrue(WalletLog::hasProcessedStripePaymentForUser('pi_failed', 7));
    }

    public function testPpvShortfallSpentElsewhereIsCreditedInsteadOfRetriedForever()
    {
        $this->db->query('CREATE TABLE test_ppv_rentals (id INT AUTO_INCREMENT PRIMARY KEY, users_id INT, ppv_plans_id INT, videos_id INT) ENGINE=InnoDB');
        \AVideoTests\PaymentWalletMySQL\AVideoPlugin::$ppv = new \AVideoTests\PaymentWalletMySQL\PpvRentalModel();
        // The card paid the 1.99 shortfall of a 3.98 rental, but the old 1.99 was spent in another tab.
        $event = $this->ppvEvent('evt_spent', 'ch_spent', 'pi_spent', 199, '3.98');
        $stripe = new \AVideoTests\PaymentWalletMySQL\StripeYPT();
        $this->assertTrue($stripe->processSinglePaymentIPN($event));
        $this->assertTrue($stripe->processSinglePaymentIPN($event));
        $this->assertSame(1.99, $this->balance(7));
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM test_ppv_rentals'));
    }

    private function ppvEvent($event, $charge, $intent, $amount, $value)
    {
        return json_decode('{"id":"' . $event . '","type":"charge.succeeded","data":{"object":{"id":"' . $charge . '","payment_intent":"' . $intent
            . '","amount":' . $amount . ',"currency":"usd","metadata":{"users_id":"7","singlePayment":"1","plugin":"PayPerView","ppv_auto_rent":"1",'
            . '"ppv_plans_id":"2","videos_id":"3419","ppv_value":"' . $value . '","rental_hours":"72"}}}}');
    }

    public function testPpvCardShortfallUsesExistingBalanceToFinishRental()
    {
        $this->db->query('CREATE TABLE test_ppv_rentals (id INT AUTO_INCREMENT PRIMARY KEY, users_id INT, ppv_plans_id INT, videos_id INT) ENGINE=InnoDB');
        (new YPTWallet())->addBalance(7, 1.00, 'Existing wallet balance');
        \AVideoTests\PaymentWalletMySQL\AVideoPlugin::$ppv = new \AVideoTests\PaymentWalletMySQL\PpvRentalModel();
        \AVideoTests\PaymentWalletMySQL\TestStripeCharge::$amount = 99;
        $event = json_decode('{"id":"evt_partial","type":"charge.succeeded","data":{"object":{"id":"ch_partial","payment_intent":"pi_partial","amount":99,"currency":"usd","metadata":{"users_id":"7","singlePayment":"1","plugin":"PayPerView","ppv_auto_rent":"1","ppv_plans_id":"2","videos_id":"3419","ppv_value":"1.99","rental_hours":"72"}}}}');
        $stripe = new \AVideoTests\PaymentWalletMySQL\StripeYPT();
        $this->assertTrue($stripe->processSinglePaymentIPN($event));
        $this->assertTrue($stripe->processSinglePaymentIPN($event));
        $this->assertSame(0.0, $this->balance(7));
        $this->assertSame(1.99, $this->balance(8));
        $this->assertSame('1', $this->scalar('SELECT COUNT(*) FROM test_ppv_rentals'));
    }

    public function testPartialLegacyMigrationIsRepairedBeforeRetryAndStatusIsScopedToPayer()
    {
        $this->db->query('ALTER TABLE wallet_log DROP COLUMN json_data');
        $wallet = new YPTWallet();
        try {
            deliver($wallet, ['ch_legacy', 'evt_legacy', 'pi_legacy']);
            $this->fail('The missing ledger column must reject the webhook');
        } catch (\Throwable $error) {
            $this->assertStringContainsString('json_data', $error->getMessage());
        }
        $this->assertSame(0.0, $this->balance(7));
        $GLOBALS['global']['systemRootPath'] = dirname(__DIR__, 2) . '/';
        $this->assertTrue(WalletLog::upgradePaymentLogSchema());
        $this->assertTrue(WalletLog::upgradePaymentLogSchema(), 'The repair must be idempotent');
        $this->assertFalse(WalletLog::hasProcessedStripePaymentForUser('pi_legacy', 7));
        $this->assertTrue(deliver($wallet, ['ch_legacy', 'evt_legacy', 'pi_legacy']));
        $this->assertTrue(deliver($wallet, ['ch_legacy', 'evt_legacy', 'pi_legacy']));
        $this->assertSame(10.0, $this->balance(7));
        $this->assertTrue(WalletLog::hasProcessedStripePaymentForUser('pi_legacy', 7));
        $this->assertFalse(WalletLog::hasProcessedStripePaymentForUser('pi_legacy', 8));
        $this->assertSame('1', $this->scalar("SELECT COUNT(*) FROM wallet_log WHERE type = 'addBalance'"));
    }

    public function testCreditStatusRecognizesOlderCommittedPayloadAfterAnEarlierPendingRead()
    {
        $this->assertFalse(WalletLog::hasProcessedStripePaymentForUser('pi_old', 7));
        $wallet = new YPTWallet();
        $wallet->processPaymentOnce('stripe', ['ch_old'], 7, function () use ($wallet) {
            $wallet->addBalance(7, 10, 'Stripe single payment: old', '{"payment_intent":"pi_old"}');
            return true;
        });
        $this->assertTrue(WalletLog::hasProcessedStripePaymentForUser('pi_old', 7));
        $this->assertFalse(WalletLog::hasProcessedStripePaymentForUser('pi_old', 8));
    }

    private function renewal(YPTWallet $wallet, $price)
    {
        // Same shape as Subscription::renew(): debit the payer, then write the subscription row.
        return function () use ($price) {
            if (YPTWallet::transferBalance(7, 8, $price, 'Renew', true) === false) {
                return false;
            }
            $this->db->query('INSERT INTO renewals (users_id) VALUES (7)');
            return $price < 100; // a price >= 100 simulates a renewal that fails after writing
        };
    }

    public function testMysqliRollbackNameIsNotASavepointRollback()
    {
        // php.net: the $name of mysqli::rollback() only adds a comment, it rolls back the whole transaction.
        $this->db->begin_transaction();
        $this->db->query('INSERT INTO renewals (users_id) VALUES (1)');
        $this->db->query('SAVEPOINT avideo_payment_step');
        $this->db->query('INSERT INTO renewals (users_id) VALUES (2)');
        $this->db->rollback(0, 'avideo_payment_step');
        $this->db->commit();
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM renewals'));

        $this->db->begin_transaction();
        $this->db->query('INSERT INTO renewals (users_id) VALUES (1)');
        $this->db->query('SAVEPOINT avideo_payment_step');
        $this->db->query('INSERT INTO renewals (users_id) VALUES (2)');
        $this->db->query('ROLLBACK TO SAVEPOINT avideo_payment_step');
        $this->db->commit();
        $this->assertSame('1', $this->scalar('SELECT GROUP_CONCAT(users_id) FROM renewals'));
    }

    public function testDuplicateDeliveryCreditsOnceAndMarkersStayHidden()
    {
        $invoice = json_decode('{"id":"evt_1","type":"invoice.payment_succeeded","data":{"object":{"id":"in_1","charge":"ch_1","customer":"cus_1"}}}');
        $charge = json_decode('{"id":"evt_2","type":"charge.succeeded","data":{"object":{"id":"ch_1","invoice":"in_1","customer":"cus_1"}}}');
        $wallet = new YPTWallet();
        $this->assertTrue(deliver($wallet, StripeIds::getPaymentIds($invoice)));
        $this->assertTrue(deliver($wallet, StripeIds::getPaymentIds($charge)));
        $this->assertTrue(deliver($wallet, StripeIds::getPaymentIds($invoice)));
        $this->assertSame(10.0, $this->balance(7));
        $this->assertSame('3', $this->scalar("SELECT COUNT(*) FROM wallet_log WHERE type = 'paymentProcessed'"));
        // WalletLog::getAllFromWallet() lists only value != 0 rows by default.
        $this->assertSame('1', $this->scalar('SELECT COUNT(*) FROM wallet_log WHERE value != 0.0'));
        $this->assertSame('1', $this->scalar("SELECT COUNT(*) FROM wallet_log WHERE type = 'paymentProcessed' AND description = 'Processed stripe payment in_1'"));
        // A later invoice is a different payment.
        $this->assertTrue(deliver($wallet, ['in_2', 'ch_2', 'evt_3']));
        $this->assertSame(20.0, $this->balance(7));
    }

    public function testFailedDeliveryLeavesNoTraceAndRetrySucceeds()
    {
        $wallet = new YPTWallet();
        try {
            $wallet->processPaymentOnce('stripe', ['in_fail'], 7, function () use ($wallet) {
                $wallet->addBalance(7, 10, 'Stripe recurrent: x', '{}');
                throw new RuntimeException('Simulated database failure');
            });
            $this->fail('A failed delivery must be retried by the provider');
        } catch (RuntimeException $error) {
            $this->assertSame('Simulated database failure', $error->getMessage());
        }
        $this->assertSame(0.0, $this->balance(7));
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM wallet_log'));
        $this->assertNotEmpty(preg_grep('/FAILED and rolled back.*payment_ids=in_fail.*Simulated database failure/', $GLOBALS['paymentSimLogs']));
        $this->assertTrue(deliver($wallet, ['in_fail']));
        $this->assertSame(10.0, $this->balance(7));
    }

    private function payPalProcess($plugin, $dedup_key)
    {
        $users_id = 7;
        $row = ['subscriptions_plans_id' => 3];
        $payment_amount = 10;
        $logContext = 'PayPal integration fixture';
        // Execute the production callback, including the PayPal log insert and wallet credit.
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/ipn.php');
        $start = strpos($source, 'function () use ($plugin, $users_id, $row, $dedup_key, $payment_amount, $logContext)');
        $end = strpos($source, "}, ['legacyCheck'", $start);
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        eval('namespace AVideoTests\\PaymentWalletMySQL; use RuntimeException; $process = '
            . substr($source, $start, $end - $start + 1) . ';');
        return $process;
    }

    private function payPalWallet()
    {
        return new class extends YPTWallet {
            // These tests isolate the PayPal log/credit; renewal savepoints are covered separately.
            public function runPaymentStep(callable $step, $label = 'payment step') { return false; }
        };
    }

    public function testPayPalSignatureFallbackFitsTheLogAndCreditsOnce()
    {
        $this->assertStringContainsString('STRICT_TRANS_TABLES', $this->scalar('SELECT @@SESSION.sql_mode'));
        $wallet = $this->payPalWallet();
        $id = str_repeat('s', 56); // verify_sign can exceed recurring_payment_id VARCHAR(45).
        $this->assertTrue($wallet->processPaymentOnce('paypal', [$id], 7, $this->payPalProcess($wallet, $id)));
        $this->assertTrue($wallet->processPaymentOnce('paypal', [$id], 7, $this->payPalProcess($wallet, $id)));
        $this->assertSame(10.0, $this->balance(7));
        $this->assertSame('1', $this->scalar('SELECT COUNT(*) FROM PayPalYPT_log'));
        $this->assertSame('1', $this->scalar("SELECT COUNT(*) FROM wallet_log WHERE type = 'paymentProcessed'"));
        $this->assertLessThanOrEqual(45, (int) $this->scalar('SELECT CHAR_LENGTH(recurring_payment_id) FROM PayPalYPT_log'));
        $nextId = str_repeat('s', 45) . str_repeat('t', 11);
        $this->assertTrue($wallet->processPaymentOnce('paypal', [$nextId], 7, $this->payPalProcess($wallet, $nextId)));
        $this->assertSame(20.0, $this->balance(7), 'Signatures with the same log prefix are distinct payments');
        $this->assertSame('2', $this->scalar('SELECT COUNT(*) FROM PayPalYPT_log'));
    }

    public function testPayPalLogAndCreditRollBackTogether()
    {
        $wallet = $this->payPalWallet();
        $process = $this->payPalProcess($wallet, 'txn_rollback');
        try {
            $wallet->processPaymentOnce('paypal', ['txn_rollback'], 7, function () use ($process) {
                $process();
                throw new RuntimeException('Simulated failure after PayPal log and credit');
            });
            $this->fail('A failed delivery must roll back its PayPal log');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Simulated failure', $error->getMessage());
        }
        $this->assertSame(0.0, $this->balance(7));
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM PayPalYPT_log'));
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM wallet_log'));
    }

    public function testDifferentGatewaysCanUseTheSamePaymentId()
    {
        $wallet = new YPTWallet();
        foreach (['paypal', 'btcpay', 'another_gateway'] as $provider) {
            for ($delivery = 0; $delivery < 2; $delivery++) {
                $this->assertTrue($wallet->processPaymentOnce($provider, ['same_id'], 7, function () use ($wallet) {
                    $wallet->addBalance(7, 10);
                    return true;
                }));
            }
        }
        $this->assertSame(30.0, $this->balance(7));
        $this->assertSame('3', $this->scalar("SELECT COUNT(*) FROM wallet_log WHERE type = 'paymentProcessed'"));
    }

    public function testCustomGatewayLegacyPatternsUseTheRealLedgerQuery()
    {
        $wallet = new YPTWallet();
        $wallet->addBalance(7, 10, 'AnotherGateway payment', '{"id":"old_id"}');
        $this->assertTrue($wallet->processPaymentOnce('another_gateway', ['old_id'], 7,
            function () { $this->fail('A historical credit must not run twice'); },
            ['legacyDescriptions' => ['AnotherGateway %']]));
        $this->assertSame(10.0, $this->balance(7));
    }

    private function btcInvoice($id = 'btc_invoice_1')
    {
        return (object)['id' => $id, 'status' => 'Settled', 'amount' => 10, 'currency' => 'USD',
            'totalBTCPaid' => 0.0001, 'storeId' => 'store_1', 'checkoutLink' => 'https://example.test/invoice',
            'metadata' => (object)['users_id' => 7, 'BTCPAY_NETWORK' => 'Mainnet']];
    }

    public function testBtcPayOwnHistoryAndWalletMarkerAreCommittedOnce()
    {
        $invoice = $this->btcInvoice();
        \AVideoTests\PaymentWalletMySQL\BTCPayments::processVerifiedInvoice($invoice);
        $duplicate = \AVideoTests\PaymentWalletMySQL\BTCPayments::processVerifiedInvoice($invoice);
        $this->assertSame('Payment already processed', $duplicate->msg);
        $this->assertSame(10.0, $this->balance(7));
        $this->assertSame('1', $this->scalar('SELECT COUNT(*) FROM btc_invoices'));
        $this->assertSame('1', $this->scalar('SELECT COUNT(*) FROM btc_payments'));
        $this->assertSame('1', $this->scalar("SELECT COUNT(*) FROM wallet_log WHERE type = 'paymentProcessed'"));
        // A pre-upgrade btc_payments row must still suppress the credit without a wallet marker.
        $this->db->query("DELETE FROM wallet_log WHERE type = 'paymentProcessed'");
        \AVideoTests\PaymentWalletMySQL\BTCPayments::processVerifiedInvoice($invoice);
        $this->assertSame(10.0, $this->balance(7));
        $this->assertSame('1', $this->scalar('SELECT COUNT(*) FROM btc_payments'));
    }

    public function testBtcPayPaymentLogSqlFailureRollsBackInvoiceAndCredit()
    {
        $invoice = $this->btcInvoice();
        $invoice->storeId = str_repeat('x', 256); // btc_payments.store is VARCHAR(255).
        try {
            \AVideoTests\PaymentWalletMySQL\BTCPayments::processVerifiedInvoice($invoice);
            $this->fail('The failing gateway log must abort the payment');
        } catch (\mysqli_sql_exception $error) {
            $this->assertStringContainsString('store', $error->getMessage());
        }
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM btc_invoices'));
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM btc_payments'));
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM wallet_log'));
        $this->assertSame(0.0, $this->balance(7));
        $invoice->storeId = 'store_1';
        \AVideoTests\PaymentWalletMySQL\BTCPayments::processVerifiedInvoice($invoice);
        $this->assertSame(10.0, $this->balance(7));
    }

    public function testFailedRenewalUndoesOnlyTheRenewalOnARealSavepoint()
    {
        $wallet = new YPTWallet();
        $this->assertTrue($wallet->processPaymentOnce('stripe', ['in_renew'], 7, function () use ($wallet) {
            $wallet->addBalance(7, 150, 'Stripe recurrent: x', '{}');
            $this->assertFalse($wallet->runPaymentStep($this->renewal($wallet, 150), 'renewal'));
            return true;
        }));
        $this->assertSame(150.0, $this->balance(7), 'The paid credit stays');
        $this->assertSame(0.0, $this->balance(8), 'The renewal transfer was undone');
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM renewals'), 'The subscription write was undone');
        $this->assertSame('1', $this->scalar("SELECT COUNT(*) FROM wallet_log WHERE type = 'paymentProcessed'"));

        $this->assertTrue($wallet->processPaymentOnce('stripe', ['in_ok'], 7, function () use ($wallet) {
            $wallet->addBalance(7, 10, 'Stripe recurrent: x', '{}');
            $this->assertTrue($wallet->runPaymentStep($this->renewal($wallet, 10), 'renewal'));
            return true;
        }));
        $this->assertSame(150.0, $this->balance(7));
        $this->assertSame(10.0, $this->balance(8));
        $this->assertSame('1', $this->scalar('SELECT COUNT(*) FROM renewals'));
    }

    public function testTransferCreatesTheMissingReceiverWallet()
    {
        // Outside a payment transferBalance() owns its transaction; user 8 never had a wallet.
        $this->db->query('INSERT INTO wallet (balance, users_id) VALUES (20, 7)');
        $this->assertNotFalse(YPTWallet::transferBalance(7, 8, 5, 'Transfer', true));
        $this->assertSame(15.0, $this->balance(7));
        $this->assertSame(5.0, $this->balance(8));
        $this->assertSame('1', $this->scalar('SELECT COUNT(*) FROM wallet WHERE users_id = 8'));
    }

    public function testInsufficientBalanceRenewalDoesNotAbortThePayment()
    {
        $this->db->query('INSERT INTO wallet (balance, users_id) VALUES (0, 8)');
        $wallet = new YPTWallet();
        $this->assertTrue($wallet->processPaymentOnce('stripe', ['in_partial'], 7, function () use ($wallet) {
            $wallet->addBalance(7, 4, 'Stripe recurrent: x', '{}');
            $this->assertFalse($wallet->runPaymentStep($this->renewal($wallet, 10), 'renewal'));
            return true;
        }));
        $this->assertSame(4.0, $this->balance(7));
        $this->assertSame(0.0, $this->balance(8));
    }

    public function testLongDescriptionAndPayloadDoNotAbortThePayment()
    {
        $this->assertStringContainsString('STRICT_TRANS_TABLES', $this->scalar('SELECT @@SESSION.sql_mode'));
        $wallet = new YPTWallet();
        $payload = json_encode(['id' => 'evt_big', 'padding' => str_repeat('é', 40000)]);
        $this->assertGreaterThan(65535, strlen($payload));
        $this->assertTrue($wallet->processPaymentOnce('stripe', ['in_big'], 7, function () use ($wallet, $payload) {
            $wallet->addBalance(7, 10, 'Stripe recurrent: ' . str_repeat('<long description> ', 40), $payload);
            return true;
        }));
        $this->assertSame(10.0, $this->balance(7));
        $row = $this->db->query("SELECT CHAR_LENGTH(description) AS d, LENGTH(json_data) AS j FROM wallet_log WHERE type = 'addBalance'")->fetch_assoc();
        $this->assertSame(255, (int) $row['d']);
        $this->assertLessThanOrEqual(65535, (int) $row['j']);
        // A truncated legacy row still identifies the payment: the event id is near the start of the JSON.
        $this->assertTrue(WalletLog::hasProcessedPayment('stripe', ['evt_big'], (int) $this->scalar('SELECT id FROM wallet WHERE users_id = 7')));
    }

    public function testCreditFromBeforeTheUpgradeIsRecognized()
    {
        $this->db->query('INSERT INTO wallet (id, balance, users_id) VALUES (1, 10, 7)');
        $legacy = $this->db->real_escape_string(json_encode(['id' => 'evt_old', 'data' => ['object' => ['id' => 'ch_old']]]));
        $this->db->query("INSERT INTO wallet_log (value, description, wallet_id, status, type, json_data) VALUES (10, 'Stripe single payment: old', 1, 'success', 'addBalance', '{$legacy}')");
        $this->assertTrue(deliver(new YPTWallet(), ['ch_old', 'evt_retry']));
        $this->assertSame(10.0, $this->balance(7));
        $this->assertNotEmpty(preg_grep('/duplicate delivery ignored/', $GLOBALS['paymentSimLogs']));
    }

    public function testBusyLockFailsWithoutWritingAnything()
    {
        $other = connect($this->database);
        $lock = 'avideo-payment-' . substr(hash('sha256', $this->database), 0, 48);
        $this->assertSame('1', (string) $other->query("SELECT GET_LOCK('{$lock}', 0)")->fetch_row()[0]);
        try {
            deliver(new YPTWallet(), ['in_busy']);
            $this->fail('A busy payment lock must make the provider retry');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Payment is busy', $error->getMessage());
        } finally {
            $other->close(); // closing the connection releases its lock
        }
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM wallet_log'));
        $this->assertTrue(deliver(new YPTWallet(), ['in_busy']));
    }

    public function testSocketStyleCloseCannotReleaseThePaymentTransaction()
    {
        $GLOBALS['paymentFixtureCLI'] = false;
        $wallet = new YPTWallet();
        $this->assertTrue($wallet->processPaymentOnce('stripe', ['in_socket'], 7, function () use ($wallet) {
            $wallet->addBalance(7, 10, 'Stripe single payment: socket', '{}');
            \AVideoTests\PaymentWalletMySQL\_mysql_close();
            $this->assertTrue($this->db->ping());
            return true;
        }));
        $this->assertSame(10.0, $this->balance(7));
        $this->assertArrayNotHasKey('mysqlTransactionConnection', $GLOBALS['global']);
    }

    /** @dataProvider driverErrorModes */
    public function testDatabaseFailureDuringRenewalRollsBackTheEntirePayment($strict)
    {
        mysqli_report($strict ? MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT : MYSQLI_REPORT_OFF);
        $wallet = new YPTWallet();
        try {
            $wallet->processPaymentOnce('stripe', ['in_db_fail'], 7, function () use ($wallet) {
                $wallet->addBalance(7, 10, 'Stripe recurrent: failed renewal', '{}');
                $wallet->runPaymentStep(function () {
                    \AVideoTests\PaymentWalletMySQL\sqlDAL::writeSql('INSERT INTO missing_renewal_table (users_id) VALUES (?)', 'i', [7]);
                    return false;
                }, 'database failure');
                return true;
            });
            $this->fail('A database failure must trigger a provider retry, not consume the payment');
        } catch (\mysqli_sql_exception $error) {
            $this->assertStringContainsString('missing_renewal_table', $error->getMessage());
        } finally {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        }
        $this->assertSame(0.0, $this->balance(7));
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM wallet_log'));
        $this->assertTrue(deliver($wallet, ['in_db_fail']));
        $this->assertSame(10.0, $this->balance(7));
    }

    /** @dataProvider readErrorModes */
    public function testCaughtReadExecutionFailureStillRollsBackThePayment($strict, $mysqlnd)
    {
        mysqli_report($strict ? MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT : MYSQLI_REPORT_OFF);
        $GLOBALS['disableMysqlNdMethods'] = !$mysqlnd;
        $wallet = new YPTWallet();
        try {
            $wallet->processPaymentOnce('stripe', ['in_read_fail'], 7, function () use ($wallet) {
                $wallet->addBalance(7, 10, 'Stripe recurrent: failed read', '{}');
                $wallet->runPaymentStep(function () {
                    try {
                        $result = \AVideoTests\PaymentWalletMySQL\sqlDAL::readSql('SELECT (SELECT 1 UNION ALL SELECT 2) AS value');
                        \AVideoTests\PaymentWalletMySQL\sqlDAL::fetchAssoc($result);
                    } catch (\mysqli_sql_exception $error) {
                        // A plugin may catch the error; the payment must still fail.
                    }
                    return false;
                }, 'read failure');
                return true;
            });
            $this->fail('A caught database error must not consume the payment');
        } catch (\mysqli_sql_exception $error) {
            $this->assertNotEmpty($error->getMessage());
        } finally {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $GLOBALS['disableMysqlNdMethods'] = false;
        }
        $this->assertSame(0.0, $this->balance(7));
        $this->assertSame('0', $this->scalar('SELECT COUNT(*) FROM wallet_log'));
        $this->assertTrue(deliver($wallet, ['in_read_fail']));
        $this->assertSame(10.0, $this->balance(7));
    }

    public function readErrorModes()
    {
        return ['strict mysqlnd' => [true, true], 'nonthrowing mysqlnd' => [false, true],
            'strict fallback' => [true, false], 'nonthrowing fallback' => [false, false]];
    }

    public function driverErrorModes() { return [[true], [false]]; }

    public function testConcurrentDuplicateDeliveriesCreditOnce()
    {
        $fixture = dirname(__DIR__) . '/fixtures/payment-wallet-mysql.php';
        $processes = [];
        foreach ([3, 0] as $seconds) {
            $process = proc_open([PHP_BINARY, $fixture, 'deliver', $this->database, 'in_concurrent', (string) $seconds],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $this->assertSame("ready\n", fgets($pipes[1]));
            $processes[] = [$process, $pipes];
            usleep(500000); // the slow delivery takes the lock first
        }
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = json_decode(stream_get_contents($pipes[1]), true);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $errors);
        }
        $this->assertTrue($results[0]['ok'] && $results[1]['ok'], json_encode($results));
        $this->assertFalse($results[0]['duplicate']);
        $this->assertTrue($results[1]['duplicate'], 'The second delivery waited for the lock and saw the committed marker');
        $this->assertSame(10.0, $this->balance(7));
    }
}
