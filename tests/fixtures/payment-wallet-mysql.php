<?php
// Optional integration fixture: run ONLY against a disposable MySQL/MariaDB instance.
// It loads the real payment methods (YPTWallet, Wallet, WalletLog, transactions) and replaces only the
// framework pieces that need a full installation: ObjectYPT INSERT/UPDATE delegates to the real DAL,
// while request, audit and logging helpers are fixture stubs.
// Child mode, used for concurrent deliveries:
//   php payment-wallet-mysql.php deliver <database> <payment_id> <seconds_inside_transaction>

namespace AVideoTests\PaymentWalletMySQL;

use RuntimeException;

if (!function_exists(__NAMESPACE__ . '\extractFunctions')) {

    /** Returns the source of the named functions/methods (with their modifiers) from a PHP file. */
    function extractFunctions($file, array $names)
    {
        $tokens = token_get_all(\file_get_contents($file));
        $modifiers = [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_FINAL, T_ABSTRACT];
        $found = [];
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) {
                continue;
            }
            $j = $i + 1;
            while ($j < $count && (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE || $tokens[$j] === '&')) {
                $j++;
            }
            if (!is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING || !in_array($tokens[$j][1], $names, true)) {
                continue;
            }
            $name = $tokens[$j][1];
            $start = $i;
            for ($k = $i - 1; $k >= 0; $k--) {
                if (is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) {
                    continue;
                }
                if (is_array($tokens[$k]) && in_array($tokens[$k][0], $modifiers, true)) {
                    $start = $k;
                    continue;
                }
                break;
            }
            $depth = 0;
            $source = '';
            for ($k = $start; $k < $count; $k++) {
                $text = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
                $source .= $text;
                if ($tokens[$k] === '{' || (is_array($tokens[$k]) && in_array($tokens[$k][0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                    $depth++;
                } elseif ($tokens[$k] === '}') {
                    $depth--;
                    if ($depth === 0) {
                        break;
                    }
                }
            }
            $found[$name] = $source;
            $i = $k;
        }
        $missing = array_diff($names, array_keys($found));
        if ($missing) {
            throw new RuntimeException('Not found in ' . basename($file) . ': ' . implode(', ', $missing));
        }
        return implode("\n\n", $found);
    }

    // Fixture-only variable names, unset below: this file shares the scope of whoever includes it, and a
    // generic name like $root once overwrote a caller's variable and redirected its writes into the repository.
    $paymentFixtureRoot = dirname(__DIR__, 2);
    $paymentFixturePrefix = 'namespace ' . __NAMESPACE__ . '; use RuntimeException; use Throwable;';

    eval($paymentFixturePrefix .extractFunctions($paymentFixtureRoot . '/objects/functionsMySQL.php', ['mysqlBeginTransaction', 'mysqlRollback', 'mysqlCommit', 'canCloseConnection', '_mysql_close', '_mysql_connect']));

    class AVideoLog
    {
        public static $ERROR = 'error';
        public static $WARNING = 'warning';
        public static $SECURITY = 'security';
    }

    function _error_log($message, $level = '')
    {
        $GLOBALS['paymentSimLogs'][] = $message;
    }
    function getSelfURI() { return 'cli'; }
    function _mysql_is_open() {
        try { return ($GLOBALS['global']['mysqli'] ?? null) instanceof \mysqli && $GLOBALS['global']['mysqli']->ping(); }
        catch (\Throwable $error) { return false; }
    }
    function isCommandLineInterface() { return $GLOBALS['paymentFixtureCLI'] ?? true; }
    function getRealIpAddr() { return '203.0.113.10'; }
    function log_error($message) { _error_log($message); }
    function TimeLogStart($name) {}
    function TimeLogEnd($name, $type, $seconds) {}
    class AVideoPlugin {
        public static $ppv = false;
        public static function loadPluginIfEnabled($name) { return $name === 'YPTWallet' ? new YPTWallet() : ($name === 'PayPerView' ? self::$ppv : false); }
        public static function getObjectData($name) { return (object)['currency' => 'USD']; }
    }
    function sendSocketMessageToUsers_id($message, $users_id, $event) {}
    function xss_esc($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
    function _json_encode($value) { return json_encode($value); }

    class User
    {
        public static function isAdmin() { return true; }
        public static function idExists($id) { return $id > 0; }
        public static function getNameIdentificationById($id) { return "User {$id}"; }
    }

    class ObjectYPT
    {
        public static function clearSessionCache() {}
    }

    // Run the real DAL, including its reconnect and non-throwing-driver error paths.
    $paymentFixtureDALSource = \file_get_contents($paymentFixtureRoot . '/objects/mysql_dal.php');
    $paymentFixtureResultStart = strpos($paymentFixtureDALSource, 'class iimysqli_result');
    $paymentFixtureResultEnd = strpos($paymentFixtureDALSource, 'global $disableMysqlNdMethods;', $paymentFixtureResultStart);
    eval($paymentFixturePrefix . substr($paymentFixtureDALSource, $paymentFixtureResultStart, $paymentFixtureResultEnd - $paymentFixtureResultStart));
    unset($paymentFixtureResultStart, $paymentFixtureResultEnd);
    $paymentFixtureDALStart = strpos($paymentFixtureDALSource, 'class sqlDAL');
    $paymentFixtureDALEnd = strpos($paymentFixtureDALSource, 'function log_error(', $paymentFixtureDALStart);
    eval($paymentFixturePrefix . ' use Exception; use mysqli_sql_exception; '
        . substr($paymentFixtureDALSource, $paymentFixtureDALStart, $paymentFixtureDALEnd - $paymentFixtureDALStart));
    unset($paymentFixtureDALSource, $paymentFixtureDALStart, $paymentFixtureDALEnd);
    $GLOBALS['disableMysqlNdMethods'] = false;
    $GLOBALS['readSqlCached'] = [];

    function simWrite($sql, $types, array $values) {
        return sqlDAL::writeSql($sql, $types, $values);
    }

    // Keep the real PayPal log setters/save wrapper; replace only ObjectYPT's framework persistence.
    class PayPalLogWriter {
        public function __construct($id = 0) {}
        public function save() {
            return simWrite('INSERT INTO PayPalYPT_log (users_id, recurring_payment_id, value, json) VALUES (?, ?, ?, ?)',
                'isds', [$this->getUsers_id(), $this->getRecurring_payment_id(), $this->getValue(), $this->getJson()]);
        }
    }
    $paymentFixturePayPalSource = \file_get_contents($paymentFixtureRoot . '/plugin/PayPalYPT/Objects/PayPalYPT_log.php');
    $paymentFixturePayPalSource = substr($paymentFixturePayPalSource, strpos($paymentFixturePayPalSource, 'class PayPalYPT_log'));
    eval($paymentFixturePrefix . str_replace('extends ObjectYPT', 'extends PayPalLogWriter', $paymentFixturePayPalSource));
    unset($paymentFixturePayPalSource);

    class BtcModelWriter {
        public function __construct($id = 0) {
            $this->id = (int) $id;
            if ($this->id) {
                $result = sqlDAL::readSql('SELECT * FROM ' . static::getTableName() . ' WHERE id = ?', 'i', [$this->id], true);
                $row = sqlDAL::fetchAssoc($result);
                sqlDAL::close($result);
                foreach ((array) $row as $field => $value) { $this->$field = $value; }
            }
        }
        public function save() {
            $table = static::getTableName();
            $columns = $table === 'btc_invoices'
                ? ['invoice_identification', 'users_id', 'amount_currency', 'amount_btc', 'currency', 'status', 'json']
                : ['btc_invoices_id', 'transaction_identification', 'amount_received_btc', 'confirmations', 'store', 'json'];
            $values = [];
            foreach ($columns as $column) { $values[] = $this->$column; }
            $types = str_repeat('s', count($values));
            if ($this->id) {
                $assignments = array_map(function ($column) { return "`{$column}` = ?"; }, $columns);
                $values[] = $this->id;
                $ok = simWrite("UPDATE {$table} SET " . implode(', ', $assignments) . ' WHERE id = ?', $types . 'i', $values);
            } else {
                $ok = simWrite("INSERT INTO {$table} (`" . implode('`,`', $columns) . '`) VALUES ('
                    . implode(', ', array_fill(0, count($columns), '?')) . ')', $types, $values);
            }
            return $ok ? ($this->id ?: $GLOBALS['global']['mysqli']->insert_id) : false;
        }
    }
    foreach (['Btc_invoices', 'Btc_payments'] as $paymentFixtureBtcClass) {
        $paymentFixtureBtcSource = \file_get_contents($paymentFixtureRoot . '/plugin/BTCPayments/Objects/' . $paymentFixtureBtcClass . '.php');
        $paymentFixtureBtcSource = substr($paymentFixtureBtcSource, strpos($paymentFixtureBtcSource, 'class ' . $paymentFixtureBtcClass));
        // Production classes are global; the fixture evaluates them in its isolated namespace.
        $paymentFixtureBtcSource = str_replace("class_exists('sqlDAL')", "class_exists(__NAMESPACE__ . '\\\\sqlDAL')", $paymentFixtureBtcSource);
        eval($paymentFixturePrefix . str_replace('extends ObjectYPT', 'extends BtcModelWriter', $paymentFixtureBtcSource));
    }
    eval($paymentFixturePrefix . 'use stdClass; class BTCPayments {'
        . extractFunctions($paymentFixtureRoot . '/plugin/BTCPayments/BTCPayments.php', ['processVerifiedInvoice']) . '}');
    unset($paymentFixtureBtcClass, $paymentFixtureBtcSource);

    eval($paymentFixturePrefix .'class Wallet {
        protected $id, $balance, $users_id, $crypto_wallet_address, $created, $modified, $information;
        public static function getTableName() { return "wallet"; }
        public function __construct($id = 0) {
            global $global;
            if (!empty($id)) {
                $row = $global["mysqli"]->query("SELECT * FROM wallet WHERE id = " . intval($id))->fetch_assoc();
                foreach ((array) $row as $key => $value) { $this->$key = $value; }
            }
        }
        public function save() {
            if (empty($this->id)) {
                return simWrite("INSERT INTO wallet (balance, users_id, created, modified) VALUES (?, ?, NOW(), NOW())", "di", [floatval($this->balance), intval($this->users_id)]);
            }
            return simWrite("UPDATE wallet SET balance = ?, modified = NOW() WHERE id = ?", "di", [floatval($this->balance), intval($this->id)]) ? $this->id : false;
        }
        ' . extractFunctions($paymentFixtureRoot . '/plugin/YPTWallet/Objects/Wallet.php', ['getBalance', 'getId', 'setBalance', 'setUsers_id', 'setCrypto_wallet_address', 'loadFromUser', 'getFromUser', 'getFromUserForUpdate', 'getOrCreateFromUser']) . '
    }');

    eval($paymentFixturePrefix .'class WalletLog {
        protected $id, $wallet_id, $value, $description, $json_data, $status, $type, $information, $previous_wallet_balance;
        public function __construct($id = 0) {}
        public function save() {
            return simWrite("INSERT INTO wallet_log (created, modified, value, description, wallet_id, status, type, information, json_data, previous_wallet_balance) VALUES (NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?)",
                "dsissssd", [floatval($this->value), $this->description, intval($this->wallet_id), $this->status, $this->type, $this->information, $this->json_data, floatval($this->previous_wallet_balance)]);
        }
        ' . extractFunctions($paymentFixtureRoot . '/plugin/YPTWallet/Objects/Wallet_log.php', ['setValue', 'setDescription', 'setWallet_id', 'setJson_data', 'setStatus', 'setType', 'setInformation', 'setPrevious_wallet_balance', 'hasProcessedPayment', 'addProcessedPayment', 'addLog', 'upgradePaymentLogSchema', 'hasProcessedStripePaymentForUser']) . '
    }');

    $paymentFixtureWalletSource = \file_get_contents($paymentFixtureRoot . '/plugin/YPTWallet/YPTWallet.php');
    if (!preg_match('/^\s*private static \$paymentTransactionActive = false;/m', $paymentFixtureWalletSource, $paymentFixtureProperty)) {
        throw new RuntimeException('paymentTransactionActive property not found');
    }
    eval($paymentFixturePrefix . 'class YPTWallet {' . $paymentFixtureProperty[0] . '
        public function getDataObject() { return (object) ["virtual_currency_enable" => false, "currency" => "USD"]; }
        ' . extractFunctions($paymentFixtureRoot . '/plugin/YPTWallet/YPTWallet.php', ['processPaymentOnce', 'runPaymentStep', 'addBalance', 'transferBalance', 'getOrCreateWallet']) . '
    }');

    eval($paymentFixturePrefix .'class StripeIds {' . extractFunctions($paymentFixtureRoot . '/plugin/StripeYPT/StripeYPT.php', ['getPaymentIds']) . '}');
    // Real Stripe delivery + wallet transaction; only the provider response and rental model are doubles.
    $paymentFixtureStripe = extractFunctions($paymentFixtureRoot . '/plugin/StripeYPT/StripeYPT.php',
        ['getSinglePaymentAmountStillPaid', 'processSinglePaymentIPN', 'getPaymentIds', 'getPaymentLogContext', 'addDot']);
    $paymentFixtureStripe = str_replace(['\\Stripe\\Charge', '\\Stripe\\Exception\\PermissionException'], ['TestStripeCharge', 'TestChargePermissionException'], $paymentFixtureStripe);
    eval($paymentFixturePrefix . 'class StripeYPT {' . $paymentFixtureStripe . '}');
    unset($paymentFixtureStripe);
    class TestChargePermissionException extends RuntimeException {}
    class TestStripeCharge {
        public static $amount = 199;
        public static function retrieve($id) { return (object) ['amount' => self::$amount, 'amount_refunded' => 0, 'status' => 'succeeded']; }
    }
    class PpvRentalModel {
        public static $fail = false;
        public function completeWalletCheckout($user, $plan, $video, $value, $hours) {
            if (!YPTWallet::transferBalance($user, 8, $value, 'PPV rental', true)) { return false; }
            $ok = simWrite('INSERT INTO test_ppv_rentals (users_id, ppv_plans_id, videos_id) VALUES (?, ?, ?)', 'iii', [$user, $plan, $video]);
            return $ok && !self::$fail;
        }
    }
    unset($paymentFixtureRoot, $paymentFixturePrefix, $paymentFixtureWalletSource, $paymentFixtureProperty);

    /** Opens a connection to the disposable server configured by AVIDEO_TEST_DB_* (default 127.0.0.1:3306 root). */
    function connect($database = '')
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        return new \mysqli(
            getenv('AVIDEO_TEST_DB_HOST') ?: '127.0.0.1',
            getenv('AVIDEO_TEST_DB_USER') ?: 'root',
            getenv('AVIDEO_TEST_DB_PASSWORD') ?: '',
            $database,
            (int) (getenv('AVIDEO_TEST_DB_PORT') ?: 3306)
        );
    }

    /** A verified delivery that credits 10 and optionally stays inside the transaction for a while. */
    function deliver(YPTWallet $wallet, array $paymentIds, $seconds = 0)
    {
        return $wallet->processPaymentOnce('stripe', $paymentIds, 7, function () use ($wallet, $seconds) {
            $wallet->addBalance(7, 10, 'Stripe recurrent: simulation', '{"id":"evt_sim"}');
            if ($seconds > 0) {
                sleep($seconds);
            }
            return true;
        });
    }
}

if (PHP_SAPI === 'cli' && isset($argv[1]) && $argv[1] === 'deliver' && realpath($argv[0]) === __FILE__) {
    $GLOBALS['paymentSimLogs'] = [];
    $GLOBALS['mysqlDatabase'] = $argv[2];
    $GLOBALS['global'] = ['mysqli' => connect($argv[2]), 'webSiteRootURL' => 'https://payment.example.test/'];
    echo "ready\n";
    fflush(STDOUT);
    try {
        deliver(new YPTWallet(), [$argv[3]], (int) $argv[4]);
        $result = ['ok' => true];
    } catch (\Throwable $error) {
        $result = ['ok' => false, 'error' => $error->getMessage()];
    }
    $result['duplicate'] = (bool) preg_grep('/duplicate delivery ignored/', $GLOBALS['paymentSimLogs']);
    echo json_encode($result), "\n";
}
