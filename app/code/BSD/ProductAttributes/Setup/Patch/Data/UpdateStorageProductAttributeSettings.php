<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Setup\Patch\Data;

use BSD\ProductAttributes\Model\Config\StorageAttributeOptions;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateStorageProductAttributeSettings implements DataPatchInterface
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

        foreach (StorageAttributeOptions::getAttributeCodes() as $code) {
            if (!$eavSetup->getAttributeId(Product::ENTITY, $code)) {
                continue;
            }

            $eavSetup->updateAttribute(
                Product::ENTITY,
                $code,
                [
                    'is_searchable' => true,
                    'search_weight' => 1,
                    'is_visible_in_advanced_search' => true,
                    'is_comparable' => true,
                    'is_filterable' => true,
                    'is_filterable_in_search' => true,
                    'position' => 0,
                    'is_html_allowed_on_front' => false,
                    'is_visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'used_for_sort_by' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                ]
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [AddStorageProductAttributes::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
