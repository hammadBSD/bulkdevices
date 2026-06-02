<?php

namespace BSD\Getaquote\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0){

		$installer->run('CREATE TABLE `getaquote` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT \'ID\',
	`name` VARCHAR(255) NULL DEFAULT NULL COMMENT \'Name\' COLLATE \'utf8_general_ci\',
	`phone` VARCHAR(255) NULL DEFAULT NULL COMMENT \'Phone\' COLLATE \'utf8_general_ci\',
	`email` VARCHAR(255) NULL DEFAULT NULL COMMENT \'Email\' COLLATE \'utf8_general_ci\',
	`price` VARCHAR(255) NULL DEFAULT NULL COMMENT \'Price\' COLLATE \'utf8_general_ci\',
	`quantity` VARCHAR(255) NULL DEFAULT NULL COMMENT \'Quantity\' COLLATE \'utf8_general_ci\',
	`product_sku` VARCHAR(255) NULL DEFAULT NULL COMMENT \'Product Sku\' COLLATE \'utf8_general_ci\',
	`request_type` VARCHAR(255) NULL DEFAULT NULL COMMENT \'Request Type\' COLLATE \'utf8_general_ci\',
	`create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'Creation Date\',	
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE=\'utf8_general_ci\'
ENGINE=InnoDB');


		//demo
//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//$scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
//$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/updaterates.log');
//$logger = new \Zend\Log\Logger();
//$logger->addWriter($writer);
//$logger->info('updaterates');
//demo 

		}

        $installer->endSetup();

    }
}