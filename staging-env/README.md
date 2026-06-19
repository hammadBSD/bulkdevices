# Nexcess staging deploy (`d97f844be7.nxcli.io`)

## `config.php`

Copy `staging-env/config.php` → `app/etc/config.php` on the server **after** `composer install`, **before** `setup:upgrade`.

This file is the Hyvä `app/etc/config.php` plus staging-only modules:

| Module | Status | Notes |
|--------|--------|-------|
| `Smartwave_Porto`, `Smartwave_Core`, `Smartwave_Megamenu`, `Smartwave_Filterproducts` | **0** | Luma/Porto — disabled for Hyvä |
| `Bss_OneStepCheckout` | **0** | Replaced by `Hyva_CustomCheckout` |
| `Hyva_*`, `Magewirephp_*`, `StripeIntegration_Payments`, `BSD_Storefront`, etc. | **1** | Hyvä stack |
| `Aitoc_Core`, `Aitoc_Smtp`, `BSD_EmailMarketing`, `Sivaschenko_CleanMedia` | **1** | Kept from legacy staging (only if code still in `app/code/`) |

`setup:upgrade` does **not** auto-disable Porto or enable Hyvä — this file (or `module:enable` / `module:disable` CLI) is required.

---

## `env.php`

Copy `staging-env/env.php` → `app/etc/env.php` on the server.

### Added vs your original staging `env.php`

| Item | Why |
|------|-----|
| `cache.frontend.default` (Redis DB 0) | Was missing — only `page_cache` was configured |
| `compression_lib` => `gzip` on page cache | Was empty string |
| `magewire` cache type | Required for Hyvä checkout (from Hyvä `app/etc/env.php`) |
| `system.full_page_cache.bfcache` | Performance (from live / dev env) |
| `downloadable_domains` | Staging hostname + production domains for downloadable products |
| `allow_parallel_generation` => `false` | Matches live config |

### Intentionally NOT copied from dev `app/etc/env.php`

| Item | Why |
|------|-----|
| Elasticsearch block (`127.0.0.1` / `aws_bdus`) | Dev-only; staging search should use **Nexcess ES** settings already in the **database** (Stores → Config → Catalog → Search). Only add an ES block here if Nexcess gives you env-level overrides. |
| Dev DB credentials | Staging uses `a02265c2_598cf3` |

If catalog search is empty after deploy, check Admin → Stores → Configuration → Catalog → Catalog Search, or ask Nexcess for the staging Elasticsearch hostname.

### Optional: admin URL

If your current staging site uses `admin_bulkuk` (live), change `backend.frontName` to match so bookmarks still work.

---

## Keeping the entire `pub/` folder

You can keep **all of `pub/`** from the existing staging site. Deploy Hyvä **code only** and do not rsync `pub/` from git.

### Deploy rule

```text
Deploy everything EXCEPT pub/
Then copy staging-env/env.php → app/etc/env.php
And staging-env/config.php → app/etc/config.php (after composer install)
```

### What stays untouched in `pub/`

- `pub/media/` — product images, WYSIWYG
- `pub/static/` — existing Luma static (left as-is; Hyvä adds new paths below)
- `pub/.htaccess`, `pub/index.php`, `pub/errors/`, sitemaps, verification files, etc.

### Hyvä static (required addition, not a full pub replace)

Hyvä needs files under:

```text
pub/static/frontend/BulkDevices/hyva/en_US/
```

Run **after** code deploy (this only writes under `pub/static/`, it does not delete `pub/media`):

```bash
php -d memory_limit=2G bin/magento setup:static-content:deploy en_US -f --theme BulkDevices/hyva
```

Old Luma paths under `pub/static/frontend/Magento/` can remain (harmless extra disk).

### rsync example (from build machine)

```bash
rsync -avz --delete \
  --exclude 'pub/' \
  --exclude 'app/etc/env.php' \
  --exclude 'var/' \
  --exclude 'generated/' \
  --exclude 'auth.json' \
  ./  user@host:~/html/
```

Then on server:

```bash
composer install --no-dev --optimize-autoloader
cp staging-env/env.php app/etc/env.php      # or scp separately
cp staging-env/config.php app/etc/config.php
php -d memory_limit=2G bin/magento setup:upgrade
php -d memory_limit=2G bin/magento setup:di:compile
php -d memory_limit=2G bin/magento setup:static-content:deploy en_US -f --theme BulkDevices/hyva
php -d memory_limit=2G bin/magento indexer:reindex
php bin/magento cache:flush
```

### Base URL (staging)

```bash
php bin/magento config:set web/unsecure/base_url 'https://d97f844be7.nxcli.io/'
php bin/magento config:set web/secure/base_url 'https://d97f844be7.nxcli.io/'
php bin/magento config:set web/secure/use_in_frontend 1
php bin/magento config:set web/secure/use_in_adminhtml 1
php bin/magento cache:flush
```

### Theme

Admin → Content → Design → Configuration → **BulkDevices Hyvä**
