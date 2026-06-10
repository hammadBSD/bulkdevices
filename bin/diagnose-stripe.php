<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$om = $bootstrap->getObjectManager();
$state = $om->get(\Magento\Framework\App\State::class);
try {
    $state->setAreaCode('frontend');
} catch (\Exception $e) {
}

$scopeConfig = $om->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
$encryptor = $om->get(\Magento\Framework\Encryption\Encryptor::class);

$paths = [
    'payment/stripe_payments/active',
    'payment/stripe_payments_basic/stripe_mode',
    'payment/stripe_payments_basic/stripe_live_pk',
    'payment/stripe_payments_basic/stripe_live_sk',
];

foreach ($paths as $p) {
    $v = (string) $scopeConfig->getValue($p);
    if ($v === '') {
        echo "$p: EMPTY\n";
    } else {
        echo "$p: " . substr($v, 0, 20) . "... (len=" . strlen($v) . ")\n";
    }
}

$resource = $om->get(\Magento\Framework\App\ResourceConnection::class);
$conn = $resource->getConnection();
$row = $conn->fetchRow(
    "SELECT value FROM core_config_data WHERE path='payment/stripe_payments_basic/stripe_live_sk' AND scope='default' AND scope_id=0"
);

if ($row) {
    $raw = (string) $row['value'];
    echo "DB raw sk length: " . strlen($raw) . "\n";
    echo "DB raw sk prefix: " . substr($raw, 0, 30) . "\n";
    try {
        $dec = $encryptor->decrypt($raw);
        echo "Decrypt length: " . strlen($dec) . "\n";
        echo "Decrypt prefix: " . (strlen($dec) ? substr($dec, 0, 10) : 'EMPTY') . "\n";
    } catch (\Exception $e) {
        echo "Decrypt error: " . $e->getMessage() . "\n";
    }
}

$cfg = $om->get(\StripeIntegration\Payments\Model\Config::class);
echo "Config isEnabled: " . ($cfg->isEnabled() ? 'yes' : 'no') . "\n";
echo "Publishable key via Config: " . substr((string) $cfg->getPublishableKey(), 0, 20) . "\n";
$sk = $cfg->getSecretKey();
echo "Secret key via Config length: " . strlen((string) $sk) . "\n";

$stripeSvc = $om->get(\Hyva\CustomCheckout\Model\Checkout\StripeConfigService::class);
$params = $stripeSvc->getInitParams();
echo "Hyva initParams apiKey: " . (isset($params['apiKey']) ? substr($params['apiKey'], 0, 20) : 'MISSING') . "\n";
