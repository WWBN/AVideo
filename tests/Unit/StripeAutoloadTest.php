<?php

use PHPUnit\Framework\TestCase;

class StripeAutoloadTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPluginLoadsStripeClassesOnlyWhenUsed()
    {
        $repository = dirname(__DIR__, 2);
        $fixture = sys_get_temp_dir() . '/avideo-stripe-autoload-' . bin2hex(random_bytes(8));
        mkdir($fixture . '/plugin', 0777, true);
        mkdir($fixture . '/objects');
        mkdir($fixture . '/vendor/stripe/stripe-php', 0777, true);
        file_put_contents($fixture . '/plugin/Plugin.abstract.php', '<?php class PluginAbstract {}');
        file_put_contents($fixture . '/objects/autoload.php', '<?php require_once ' . var_export($repository . '/objects/autoload.php', true) . ';');
        file_put_contents($fixture . '/vendor/stripe/stripe-php/init.php', '<?php require_once ' . var_export($repository . '/vendor/stripe/stripe-php/init.php', true) . ';');
        $previousGlobal = $GLOBALS['global'] ?? [];

        try {
            $GLOBALS['global'] = ['systemRootPath' => $fixture . '/'];
            require $repository . '/plugin/StripeYPT/StripeYPT.php';
            require $repository . '/plugin/StripeYPT/init.php';

            $this->assertSame('StripeYPT', (new StripeYPT())->getName());
            $this->assertNotContains($repository . '/vendor/stripe/stripe-php/init.php', get_included_files());
            $stripeClasses = array_filter(get_declared_classes(), function ($class) {
                return strpos($class, 'Stripe\\') === 0;
            });
            $this->assertCount(0, $stripeClasses, 'Browsing must not load the whole Stripe SDK.');

            $client = new \Stripe\StripeClient('sk_test_autoload_only');
            $this->assertInstanceOf(\Stripe\Service\PaymentIntentService::class, $client->paymentIntents);
            $intent = \Stripe\PaymentIntent::constructFrom(['id' => 'pi_autoload_test', 'object' => 'payment_intent']);
            $this->assertSame('pi_autoload_test', $intent->id);

            $payload = json_encode(['id' => 'evt_autoload_test', 'object' => 'event', 'type' => 'charge.succeeded']);
            $timestamp = time();
            $secret = 'whsec_autoload_test_only';
            $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
            $event = \Stripe\Webhook::constructEvent($payload, 't=' . $timestamp . ',v1=' . $signature, $secret);
            $this->assertSame('evt_autoload_test', $event->id);
        } finally {
            $GLOBALS['global'] = $previousGlobal;
            unlink($fixture . '/plugin/Plugin.abstract.php');
            unlink($fixture . '/objects/autoload.php');
            unlink($fixture . '/vendor/stripe/stripe-php/init.php');
            rmdir($fixture . '/plugin');
            rmdir($fixture . '/objects');
            rmdir($fixture . '/vendor/stripe/stripe-php');
            rmdir($fixture . '/vendor/stripe');
            rmdir($fixture . '/vendor');
            rmdir($fixture);
        }
    }
}
