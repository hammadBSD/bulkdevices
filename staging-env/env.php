<?php
/**
 * Nexcess primary staging — https://d97f844be7.nxcli.io/
 * Copy to app/etc/env.php on the staging server (do not commit app/etc/env.php).
 */
return [
    'backend' => [
        'frontName' => 'admin',
    ],
    'remote_storage' => [
        'driver' => 'file',
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1,
    ],
    'crypt' => [
        'key' => '70a1fcf0be48febeaba2b19c79f65d0f',
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'a02265c2_598cf3',
                'username' => 'a02265c2_598cf3',
                'password' => 'StonesBribedSoughtPrison',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false,
                ],
            ],
        ],
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default',
        ],
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'lock' => [
        'provider' => 'db',
    ],
    'directories' => [
        'document_root_is_pub' => true,
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'magewire' => 1,
        'pnp_custom_cache_top_menu' => 1,
        'pnp_custom_cache_google_seo' => 1,
    ],
    'downloadable_domains' => [
        'd97f844be7.nxcli.io',
        'bulkdevices.com',
        'www.bulkdevices.com',
        'bulkdevices.local',
    ],
    'system' => [
        'default' => [
            'system' => [
                'full_page_cache' => [
                    'bfcache' => '1',
                ],
            ],
        ],
    ],
    'install' => [
        'date' => 'Thu, 10 Apr 2025 16:54:26 +0000',
    ],
    'session' => [
        'save' => 'redis',
        'redis' => [
            'host' => '10.75.114.69',
            'port' => '47403',
            'password' => '',
            'timeout' => '2.5',
            'persistent_identifier' => '',
            'database' => '2',
            'compression_threshold' => '2048',
            'compression_library' => 'gzip',
            'log_level' => '0',
            'max_concurrency' => '6',
            'break_after_frontend' => '5',
            'break_after_adminhtml' => '30',
            'first_lifetime' => '600',
            'bot_first_lifetime' => '60',
            'bot_lifetime' => '7200',
            'disable_locking' => '0',
            'min_lifetime' => '60',
            'max_lifetime' => '2592000',
            'sentinel_master' => '',
            'sentinel_servers' => '',
            'sentinel_connect_retries' => '5',
            'sentinel_verify_master' => '0',
        ],
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '5a4_',
                'backend' => 'Magento\\Framework\\Cache\\Backend\\Redis',
                'backend_options' => [
                    'server' => '10.75.114.69',
                    'database' => '0',
                    'port' => '47403',
                    'password' => '',
                    'compress_data' => '1',
                    'compression_lib' => 'gzip',
                ],
            ],
            'page_cache' => [
                'id_prefix' => '5a4_',
                'backend' => 'Magento\\Framework\\Cache\\Backend\\Redis',
                'backend_options' => [
                    'server' => '10.75.114.69',
                    'database' => '1',
                    'port' => '47403',
                    'password' => '',
                    'compress_data' => '1',
                    'compression_lib' => 'gzip',
                ],
            ],
        ],
        'allow_parallel_generation' => false,
        'graphql' => [
            'id_salt' => 'QDP46L0kOIpynNNYPomAK0pIGc9QMQMf',
        ],
    ],
    'dev' => [
        'debug' => [
            'debug_logging' => 0,
        ],
    ],
    'db_logger' => [
        'output' => 'disabled',
    ],
];
