# Bulk Devices

Magento 2.4.6 revamp of the Bulk Devices site.

## Requirements

- PHP 8.1+
- MySQL/MariaDB
- Elasticsearch 7.17+ or OpenSearch 2.x
- Composer 2.x

## Setup

```bash
composer install
cp app/etc/env.php.example app/etc/env.php   # configure DB and search
bin/magento setup:install # or setup:upgrade if DB exists
bin/magento indexer:reindex
```

## Search

Configure OpenSearch/Elasticsearch at `localhost:9200` in `app/etc/env.php` or via Admin → Stores → Configuration → Catalog → Catalog Search.
