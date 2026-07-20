<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Setup\Patch\Data;

use BSD\ProductAttributes\Model\Config\SharedAttributeOptionAdditions;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MergeSharedAttributeOptionAdditions implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
    ) {
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach (SharedAttributeOptionAdditions::getAdditions() as $code => $options) {
            $attributeId = (int) $eavSetup->getAttributeId(Product::ENTITY, $code);
            if (!$attributeId || $options === []) {
                continue;
            }

            $eavSetup->addAttributeOption([
                'attribute_id' => $attributeId,
                'values' => array_values($options),
            ]);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [
            AddStorageProductAttributes::class,
            AddAttributeWithValue02ProductAttributes::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
