<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Setup\Patch\Data;

use BSD\ProductAttributes\Model\Config\ProductAttributeRegistry;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

/**
 * Copies the admin (store 0) option label into the Default Store View (store 1)
 * for all managed attributes, mirroring how manufacturer is configured.
 */
class PopulateDefaultStoreViewOptionLabels implements DataPatchInterface
{
    private const DEFAULT_STORE_VIEW_ID = 1;

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
        private readonly ResourceConnection $resourceConnection,
    ) {
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $connection = $this->resourceConnection->getConnection();
        $optionTable = $this->resourceConnection->getTableName('eav_attribute_option');
        $optionValueTable = $this->resourceConnection->getTableName('eav_attribute_option_value');

        foreach (ProductAttributeRegistry::getAttributeCodes() as $code) {
            $attributeId = (int) $eavSetup->getAttributeId(Product::ENTITY, $code);
            if (!$attributeId) {
                continue;
            }

            $adminValues = $connection->fetchPairs(
                $connection->select()
                    ->from(['o' => $optionTable], ['option_id'])
                    ->join(
                        ['v' => $optionValueTable],
                        'v.option_id = o.option_id AND v.store_id = ' . Store::DEFAULT_STORE_ID,
                        ['value']
                    )
                    ->where('o.attribute_id = ?', $attributeId)
            );

            if ($adminValues === []) {
                continue;
            }

            $existingStoreOptionIds = $connection->fetchCol(
                $connection->select()
                    ->from(['o' => $optionTable], [])
                    ->join(
                        ['v' => $optionValueTable],
                        'v.option_id = o.option_id AND v.store_id = ' . self::DEFAULT_STORE_VIEW_ID,
                        ['option_id']
                    )
                    ->where('o.attribute_id = ?', $attributeId)
            );
            $existingStoreOptionIds = array_flip(array_map('intval', $existingStoreOptionIds));

            $inserts = [];
            foreach ($adminValues as $optionId => $value) {
                if (isset($existingStoreOptionIds[(int) $optionId])) {
                    continue;
                }

                $inserts[] = [
                    'option_id' => (int) $optionId,
                    'store_id' => self::DEFAULT_STORE_VIEW_ID,
                    'value' => (string) $value,
                ];
            }

            if ($inserts !== []) {
                $connection->insertMultiple($optionValueTable, $inserts);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [
            AddStorageProductAttributes::class,
            AddAttributeWithValue02ProductAttributes::class,
            MergeSharedAttributeOptionAdditions::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
