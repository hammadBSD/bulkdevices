<?php

declare(strict_types=1);

namespace Snowdog\CategoryAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCategoryFaqsAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'category_faqs';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
    ) {
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        if (!$eavSetup->getAttributeId(Category::ENTITY, self::ATTRIBUTE_CODE)) {
            $eavSetup->addAttribute(
                Category::ENTITY,
                self::ATTRIBUTE_CODE,
                [
                    'type' => 'text',
                    'label' => 'FAQs',
                    'input' => 'textarea',
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'wysiwyg_enabled' => true,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'General Information',
                ]
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
