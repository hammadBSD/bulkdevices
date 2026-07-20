<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Setup\Patch\Data;

use BSD\ProductAttributes\Model\Config\StorageAttributeOptions;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddStorageProductAttributes implements DataPatchInterface
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
        $entityTypeId = (int) $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = (int) $eavSetup->getAttributeSetId($entityTypeId, 'Default');
        $attributeGroupId = (int) $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General');

        foreach (StorageAttributeOptions::getDefinitions() as $code => $definition) {
            if ($eavSetup->getAttributeId(Product::ENTITY, $code)) {
                continue;
            }

            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                [
                    'type' => 'int',
                    'label' => $definition['label'],
                    'input' => 'select',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'required' => false,
                    'user_defined' => true,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'searchable' => true,
                    'search_weight' => 1,
                    'filterable' => true,
                    'comparable' => true,
                    'visible_in_advanced_search' => true,
                    'is_filterable_in_search' => true,
                    'position' => 0,
                    'used_for_sort_by' => true,
                    'is_html_allowed_on_front' => false,
                    'apply_to' => Type::TYPE_SIMPLE,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'option' => [
                        'values' => $definition['options'],
                    ],
                ]
            );

            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $attributeGroupId,
                $code,
                100
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
