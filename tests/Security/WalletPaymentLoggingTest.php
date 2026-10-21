<?php

namespace Tests\Security\WalletPaymentLogging;

use PHPUnit\Framework\TestCase;

// Run the production Wallet::save() wrapper without configuration, a database or a socket server.
class ObjectYPT
{
    public static $saved = false;
    public static $logs = [];
    public static function clearSessionCache() {}
    public function save() { return self::$saved; }
}

class AVideoPlugin
{
    public static function getObjectData($name) { return (object) ['decimalPrecision' => 2]; }
}

class YPTWallet
{
    public static function formatCurrency($value, $html) { return number_format($value, 2); }
}

function _error_log($message) { ObjectYPT::$logs[] = $message; }
function sendSocketMessageToUsers_id($message, $user, $event) {}

$walletLoggingSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/YPTWallet/Objects/Wallet.php');
$walletLoggingSource = substr($walletLoggingSource, strpos($walletLoggingSource, 'class Wallet extends'));
eval('namespace ' . __NAMESPACE__ . '; ' . $walletLoggingSource);
unset($walletLoggingSource);

class WalletPaymentLoggingTest extends TestCase
{
    public static function persistenceResults(): array { return [[false], [7]]; }

    /** @dataProvider persistenceResults */
    public function testWalletSaveDoesNotLogWebhookArguments($saved): void
    {
        ObjectYPT::$saved = $saved;
        ObjectYPT::$logs = [];
        $payload = (object) ['billing_details' => ['email' => 'billing-marker@example.invalid'],
            'client_secret' => 'test-client-secret-marker'];
        $this->assertSame($saved, $this->saveFromWebhook($payload));
        $log = implode("\n", ObjectYPT::$logs);
        $this->assertStringContainsString('saveFromWebhook', $log, 'Keep stack locations for diagnostics');
        $this->assertStringNotContainsString('billing-marker@example.invalid', $log);
        $this->assertStringNotContainsString('test-client-secret-marker', $log);
        $this->assertStringNotContainsString('"args"', $log);
    }

    private function saveFromWebhook($payload)
    {
        $wallet = new Wallet();
        $wallet->setBalance(1.99);
        return $wallet->save();
    }
}
