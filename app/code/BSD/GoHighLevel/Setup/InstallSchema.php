<?php

namespace BSD\GoHighLevel\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Table 1: bsd_ghl_contacts
        $tableContacts = $installer->getConnection()->newTable(
            $installer->getTable('bsd_ghl_contacts')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Email Address'
        )->addColumn(
            'firstname',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'First Name'
        )->addColumn(
            'lastname',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Last Name'
        )->addColumn(
            'phone',
            Table::TYPE_TEXT,
            50,
            ['nullable' => true],
            'Phone'
        )->addColumn(
            'address',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Address'
        )->addColumn(
            'city',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'City'
        )->addColumn(
            'state',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'State'
        )->addColumn(
            'postal_code',
            Table::TYPE_TEXT,
            50,
            ['nullable' => true],
            'Postal Code'
        )->addColumn(
            'country',
            Table::TYPE_TEXT,
            100,
            ['nullable' => true],
            'Country'
        )->addColumn(
            'ghl_contact_id',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'GHL Contact ID'
        )->addColumn(
            'synced_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Synced At'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addIndex(
            $installer->getIdxName('bsd_ghl_contacts', ['email']),
            ['email'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('bsd_ghl_contacts', ['ghl_contact_id']),
            ['ghl_contact_id']
        )->addIndex(
            $installer->getIdxName('bsd_ghl_contacts', ['created_at']),
            ['created_at']
        )->setComment(
            'BSD GoHighLevel Contacts Table'
        );

        $installer->getConnection()->createTable($tableContacts);

        // Table 2: bsd_ghl_opportunities
        $tableOpportunities = $installer->getConnection()->newTable(
            $installer->getTable('bsd_ghl_opportunities')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'contact_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Contact ID'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Order ID'
        )->addColumn(
            'source',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Source'
        )->addColumn(
            'ghl_opportunity_id',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'GHL Opportunity ID'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            500,
            ['nullable' => true],
            'Title'
        )->addColumn(
            'monetary_value',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true],
            'Monetary Value'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            50,
            ['nullable' => true],
            'Status'
        )->addColumn(
            'pipeline_id',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Pipeline ID'
        )->addColumn(
            'pipeline_stage_id',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Pipeline Stage ID'
        )->addColumn(
            'custom_fields',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Custom Fields (JSON)'
        )->addColumn(
            'synced_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Synced At'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addIndex(
            $installer->getIdxName('bsd_ghl_opportunities', ['contact_id']),
            ['contact_id']
        )->addIndex(
            $installer->getIdxName('bsd_ghl_opportunities', ['order_id']),
            ['order_id']
        )->addIndex(
            $installer->getIdxName('bsd_ghl_opportunities', ['ghl_opportunity_id']),
            ['ghl_opportunity_id']
        )->addIndex(
            $installer->getIdxName('bsd_ghl_opportunities', ['source']),
            ['source']
        )->addIndex(
            $installer->getIdxName('bsd_ghl_opportunities', ['created_at']),
            ['created_at']
        )->addForeignKey(
            $installer->getFkName('bsd_ghl_opportunities', 'contact_id', 'bsd_ghl_contacts', 'id'),
            'contact_id',
            $installer->getTable('bsd_ghl_contacts'),
            'id',
            Table::ACTION_CASCADE
        )->setComment(
            'BSD GoHighLevel Opportunities Table'
        );

        $installer->getConnection()->createTable($tableOpportunities);

        // Table 3: bsd_ghl_sync_log
        $tableSyncLog = $installer->getConnection()->newTable(
            $installer->getTable('bsd_ghl_sync_log')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'entity_type',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Entity Type'
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Entity ID'
        )->addColumn(
            'sync_type',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Sync Type'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Status'
        )->addColumn(
            'error_message',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Error Message'
        )->addColumn(
            'request_data',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Request Data (JSON)'
        )->addColumn(
            'response_data',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Response Data (JSON)'
        )->addColumn(
            'http_status_code',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'HTTP Status Code'
        )->addColumn(
            'retry_count',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            'Retry Count'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addIndex(
            $installer->getIdxName('bsd_ghl_sync_log', ['entity_type', 'entity_id']),
            ['entity_type', 'entity_id']
        )->addIndex(
            $installer->getIdxName('bsd_ghl_sync_log', ['status']),
            ['status']
        )->addIndex(
            $installer->getIdxName('bsd_ghl_sync_log', ['sync_type']),
            ['sync_type']
        )->addIndex(
            $installer->getIdxName('bsd_ghl_sync_log', ['created_at']),
            ['created_at']
        )->setComment(
            'BSD GoHighLevel Sync Log Table'
        );

        $installer->getConnection()->createTable($tableSyncLog);

        $installer->endSetup();
    }
}
