<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Setup\Patch\Data;

use BSD\ProductAttributes\Model\Category\Attribute\Backend\PlpFilterAttributes;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCategoryPdpRelatedFilterAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'pdp_related_products_filter_attributes';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
    ) {
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        if ($eavSetup->getAttributeId(Category::ENTITY, self::ATTRIBUTE_CODE)) {
            $this->moduleDataSetup->getConnection()->endSetup();
            return;
        }

        $eavSetup->addAttribute(
            Category::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type' => 'text',
                'label' => 'PDP Related Products Attribute Filters',
                'input' => 'multiselect',
                'backend' => PlpFilterAttributes::class,
                'source' => \BSD\ProductAttributes\Model\Config\Source\ProductFilterAttributes::class,
                'required' => false,
                'user_defined' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'group' => 'Display Settings',
                'sort_order' => 130,
                'note' => 'Select which attributes are used to match related products on the product page. Only products with the same values for all selected attributes are shown.',
            ]
        );

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
