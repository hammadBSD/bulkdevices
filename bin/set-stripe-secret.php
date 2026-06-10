<?php
/**
 * Re-encrypt and store Stripe secret API keys using this environment's crypt key.
 *
 * Usage:
 *   php bin/set-stripe-secret.php live sk_live_xxxxxxxx
 *   php bin/set-stripe-secret.php test sk_test_xxxxxxxx
 */

use Magento\Framework\App\Bootstrap;

if ($argc < 3) {
    fwrite(STDERR, "Usage: php bin/set-stripe-secret.php <live|test> <sk_...>\n");
    exit(1);
}

$mode = $argv[1];
$secretKey = trim($argv[2]);

if (!in_array($mode, ['live', 'test'], true)) {
    fwrite(STDERR, "Mode must be 'live' or 'test'.\n");
    exit(1);
}

if (!preg_match('/^sk_(live|test)_[A-Za-z0-9]+$/', $secretKey)) {
    fwrite(STDERR, "Invalid Stripe secret key format.\n");
    exit(1);
}

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$om = $bootstrap->getObjectManager();
$state = $om->get(\Magento\Framework\App\State::class);
try {
    $state->setAreaCode('adminhtml');
} catch (\Exception $e) {
}

$path = "payment/stripe_payments_basic/stripe_{$mode}_sk";
$writer = $om->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);
$writer->save($path, $secretKey);

$cache = $om->get(\Magento\Framework\App\Cache\TypeListInterface::class);
$cache->cleanType('config');

$config = $om->get(\StripeIntegration\Payments\Model\Config::class);
$enabled = $config->isEnabled();
$skLen = strlen((string) $config->getSecretKey());

echo "Saved {$path}\n";
echo "Secret key length after save: {$skLen}\n";
echo "Stripe isEnabled: " . ($enabled ? 'yes' : 'no') . "\n";

if (!$enabled || $skLen === 0) {
    fwrite(STDERR, "Stripe still not enabled — check publishable key and payment/stripe_payments/active.\n");
    exit(2);
}

exit(0);
