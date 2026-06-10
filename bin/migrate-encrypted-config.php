<?php
/**
 * Re-encrypt Magento config values using production's crypt key.
 *
 * 1. Upload production env.php to: app/etc/env.php.production
 * 2. Run: php bin/migrate-encrypted-config.php
 */

$baseDir = dirname(__DIR__);
$productionEnvPath = $baseDir . '/app/etc/env.php.production';
$envPath = $baseDir . '/app/etc/env.php';

if (!is_readable($productionEnvPath)) {
    fwrite(STDERR, "Missing {$productionEnvPath}\n");
    fwrite(STDERR, "Copy production env.php there (e.g. scp from your Mac), then re-run.\n");
    exit(1);
}

$productionEnv = include $productionEnvPath;
$revampEnv = include $envPath;

$productionKey = trim((string) ($productionEnv['crypt']['key'] ?? ''));
$revampKey = trim((string) ($revampEnv['crypt']['key'] ?? ''));

if ($productionKey === '' || $revampKey === '') {
    fwrite(STDERR, "Could not read crypt keys from env files.\n");
    exit(1);
}

if ($productionKey === $revampKey) {
    echo "Production and revamp crypt keys are identical.\n";
}

function writeCryptKey(string $envPath, string $key): void
{
    $env = include $envPath;
    $env['crypt']['key'] = $key;
    $export = var_export($env, true);
    file_put_contents($envPath, "<?php\nreturn {$export};\n");
}

function clearConfigCache(string $baseDir): void
{
    @unlink($baseDir . '/var/config.cache');
}

function bootstrapMagento(string $baseDir)
{
    require $baseDir . '/app/bootstrap.php';

    return \Magento\Framework\App\Bootstrap::create($baseDir, $_SERVER)->getObjectManager();
}

function setAdminArea($om): void
{
    $state = $om->get(\Magento\Framework\App\State::class);
    try {
        $state->setAreaCode('adminhtml');
    } catch (\Exception $e) {
    }
}

function fetchStripeFromProductionDb(array $productionEnv, $productionEncryptor): array
{
    $result = [];
    $prodDb = $productionEnv['db']['connection']['default'] ?? null;
    if (!$prodDb || empty($prodDb['host']) || empty($prodDb['dbname'])) {
        return $result;
    }

    $stripePaths = [
        'payment/stripe_payments_basic/stripe_live_sk',
        'payment/stripe_payments_basic/stripe_test_sk',
    ];

    try {
        $prodDsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $prodDb['host'],
            $prodDb['dbname']
        );
        $prodPdo = new PDO($prodDsn, $prodDb['username'], $prodDb['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        foreach ($stripePaths as $path) {
            $encrypted = $prodPdo->query(
                "SELECT value FROM core_config_data
                 WHERE path = " . $prodPdo->quote($path) . "
                 AND scope = 'default' AND scope_id = 0"
            )->fetchColumn();

            if (!$encrypted) {
                continue;
            }

            $plain = $productionEncryptor->decrypt((string) $encrypted);
            if ($plain !== '') {
                $result[$path] = [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'value' => $plain,
                ];
            }
        }
    } catch (\Throwable $e) {
        echo "Production DB unavailable: {$e->getMessage()}\n";
    }

    return $result;
}

$envBackupPath = $envPath . '.crypt-migration.bak';
if (!is_readable($envBackupPath)) {
    copy($envPath, $envBackupPath);
    echo "Backed up env.php to env.php.crypt-migration.bak\n";
}

// Decrypt using production crypt key.
writeCryptKey($envPath, $productionKey);
clearConfigCache($baseDir);

$om = bootstrapMagento($baseDir);
setAdminArea($om);

$encryptor = $om->get(\Magento\Framework\Encryption\EncryptorInterface::class);
$resource = $om->get(\Magento\Framework\App\ResourceConnection::class);
$conn = $resource->getConnection();

$rows = $conn->fetchAll(
    "SELECT scope, scope_id, path, value
     FROM core_config_data
     WHERE value LIKE '0:%:%'
     ORDER BY path"
);

$toSave = [];

foreach ($rows as $row) {
    $plain = $encryptor->decrypt((string) $row['value']);
    if ($plain === '') {
        echo "SKIP {$row['path']}: decrypt returned empty\n";
        continue;
    }

    $toSave[] = [
        'scope' => $row['scope'],
        'scope_id' => (int) $row['scope_id'],
        'path' => $row['path'],
        'value' => $plain,
    ];
    echo "DECRYPTED {$row['path']}\n";
}

foreach (fetchStripeFromProductionDb($productionEnv, $encryptor) as $path => $item) {
    $localLen = (int) $conn->fetchOne(
        'SELECT LENGTH(value) FROM core_config_data WHERE path = ? AND scope = ? AND scope_id = ?',
        [$path, 'default', 0]
    );
    if ($localLen > 0) {
        echo "SKIP {$path}: already present locally\n";
        continue;
    }

    $toSave[] = [
        'scope' => $item['scope'],
        'scope_id' => $item['scope_id'],
        'path' => $path,
        'value' => $item['value'],
    ];
    echo "DECRYPTED {$path} (from production DB)\n";
}

// Re-encrypt using revamp crypt key.
writeCryptKey($envPath, $revampKey);
clearConfigCache($baseDir);

$om = bootstrapMagento($baseDir);
setAdminArea($om);

$writer = $om->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);

foreach ($toSave as $item) {
    $writer->save($item['path'], $item['value'], $item['scope'], $item['scope_id']);
    echo "SAVED {$item['path']}\n";
}

$cache = $om->get(\Magento\Framework\App\Cache\TypeListInterface::class);
$cache->cleanType('config');

$config = $om->get(\StripeIntegration\Payments\Model\Config::class);
$enabled = $config->isEnabled();
$skLen = strlen((string) $config->getSecretKey());

echo "\nStripe isEnabled: " . ($enabled ? 'yes' : 'no') . "\n";
echo "Stripe secret key length: {$skLen}\n";

if (!$enabled || $skLen === 0) {
    echo "\nStripe still needs a secret key. Either:\n";
    echo "  - Ensure production DB in env.php.production is reachable, or\n";
    echo "  - Run: php bin/set-stripe-secret.php live 'sk_live_...'\n";
    exit(2);
}

exit(0);
