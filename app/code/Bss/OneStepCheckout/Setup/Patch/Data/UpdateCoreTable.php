<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OneStepCheckout
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\OneStepCheckout\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateCoreTable implements DataPatchInterface
{
    /**
     * @var \Bss\OneStepCheckout\Model\ConvertFileName
     */
    private $convertGiftWrapper;

    /**
     * @var \Magento\Framework\Module\ModuleResource
     */
    private $moduleResource;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Framework\Module\ModuleResource $moduleResource
     * @param \Bss\OneStepCheckout\Model\ConvertFileName $convertGiftWrapper
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Framework\Module\ModuleResource $moduleResource,
        \Bss\OneStepCheckout\Model\ConvertFileName $convertGiftWrapper
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->convertGiftWrapper = $convertGiftWrapper;
        $this->moduleResource = $moduleResource;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        //convert file gift wrapper from txt to php
        $this->convertGiftWrapper->Convert();

        $this->moduleDataSetup->startSetup();
        if (version_compare($this->moduleResource->getDbVersion('Bss_OneStepCheckout'), '2.1.3', '<=')) {
            $tables = ['sales_order', 'sales_order_grid', 'quote'];
            $connection = $this->moduleDataSetup->getConnection('core_write');
            foreach ($tables as $table) {
                $table = $this->moduleDataSetup->getTable($table);
                if ($connection->tableColumnExists($table, 'shipping_arrival_date')) {
                    $connection->update(
                        $table,
                        ['shipping_arrival_date' => null],
                        ['shipping_arrival_date = ?' => '0000-00-00 00:00:00']
                    );
                }
            }

            $configDataTable = $this->moduleDataSetup->getTable('core_config_data');
            $updateList = [
                'display_field/enable_delivery_date' => 'order_delivery_date/enable_delivery_date',
                'display_field/enable_delivery_comment' => 'order_delivery_date/enable_delivery_comment',
                'display_field/enable_gift_message' => 'gift_message/enable_gift_message',
                'display_field/enable_subscribe_newsletter' => 'newsletter/enable_subscribe_newsletter',
                'general/newsletter_default' => 'newsletter/newsletter_default',
                'general/tilte' => 'general/title'
            ];
            foreach ($updateList as $oldPath => $newPath) {
                $this->moduleDataSetup->getConnection('core_write')->update(
                    $configDataTable,
                    ['path' => 'onestepcheckout/' . $newPath],
                    ['path = ?' => 'onestepcheckout/' . $oldPath]
                );
            }
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
