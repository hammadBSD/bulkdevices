<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Setup\Patch\Data;

use BSD\ProductAttributes\Model\Category\Attribute\Backend\PlpFilterAttributes;
use Magento\Catalog\Model\Category;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class FixCategoryPlpFilterAttributeBackend implements DataPatchInterface
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
        $attributeId = $eavSetup->getAttributeId(Category::ENTITY, AddCategoryPlpFilterAttribute::ATTRIBUTE_CODE);

        if ($attributeId) {
            $eavSetup->updateAttribute(
                Category::ENTITY,
                AddCategoryPlpFilterAttribute::ATTRIBUTE_CODE,
                'backend_model',
                PlpFilterAttributes::class
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [AddCategoryPlpFilterAttribute::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
