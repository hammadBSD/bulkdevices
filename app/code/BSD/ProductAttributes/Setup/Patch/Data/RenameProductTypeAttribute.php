<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Renames the custom product attribute code "product_type" to
 * "storage_product_type". The original code collides with Magento's reserved
 * import/export column (Product::COL_TYPE = 'product_type'), which breaks all
 * native product imports. The attribute id, options and product values are
 * preserved because only the attribute_code changes.
 */
class RenameProductTypeAttribute implements DataPatchInterface
{
    private const OLD_CODE = 'product_type';
    private const NEW_CODE = 'storage_product_type';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
    ) {
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $oldId = (int) $eavSetup->getAttributeId(Product::ENTITY, self::OLD_CODE);
        $newId = (int) $eavSetup->getAttributeId(Product::ENTITY, self::NEW_CODE);

        if ($oldId && !$newId) {
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $oldId,
                'attribute_code',
                self::NEW_CODE
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
