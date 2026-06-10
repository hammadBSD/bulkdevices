<?php
/**
 * Restore Stripe secret key from production's encrypted DB value.
 * Requires production crypt key in app/etc/env.php.
 *
 * Usage:
 *   php bin/restore-stripe-encrypted.php live '0:3:...encrypted_blob...'
 *   php bin/restore-stripe-encrypted.php test '0:3:...encrypted_blob...'
 */

use Magento\Framework\App\Bootstrap;

if ($argc < 3) {
    fwrite(STDERR, "Usage: php bin/restore-stripe-encrypted.php <live|test> '0:3:...'\n");
    exit(1);
}

$mode = $argv[1];
$encrypted = trim($argv[2]);

if (!in_array($mode, ['live', 'test'], true)) {
    fwrite(STDERR, "Mode must be 'live' or 'test'.\n");
    exit(1);
}

if (!preg_match('/^0:\d+:/', $encrypted)) {
    fwrite(STDERR, "Value must be a Magento encrypted blob (starts with 0:N:).\n");
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
$encryptor = $om->get(\Magento\Framework\Encryption\EncryptorInterface::class);
$plain = $encryptor->decrypt($encrypted);

if ($plain === '' || !str_starts_with($plain, 'sk_')) {
    fwrite(STDERR, "Decrypt failed or result is not a Stripe secret key.\n");
    fwrite(STDERR, "Ensure app/etc/env.php uses the production crypt key.\n");
    exit(2);
}

$conn = $om->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
$conn->update(
    'core_config_data',
    ['value' => $encrypted],
    ['path = ?' => $path, 'scope = ?' => 'default', 'scope_id = ?' => 0]
);

$om->get(\Magento\Framework\App\Cache\TypeListInterface::class)->cleanType('config');

$config = $om->get(\StripeIntegration\Payments\Model\Config::class);
echo "Restored {$path}\n";
echo "Secret decrypts to sk_* : yes (" . strlen($plain) . " chars)\n";
echo "Stripe isEnabled: " . ($config->isEnabled() ? 'yes' : 'no') . "\n";

exit($config->isEnabled() ? 0 : 3);
