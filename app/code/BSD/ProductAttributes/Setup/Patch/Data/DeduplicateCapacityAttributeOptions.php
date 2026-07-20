<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DeduplicateCapacityAttributeOptions implements DataPatchInterface
{
    private const ATTRIBUTE_CODE = 'capacity';

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
        $attributeId = (int) $eavSetup->getAttributeId(Product::ENTITY, self::ATTRIBUTE_CODE);

        if (!$attributeId) {
            $this->moduleDataSetup->getConnection()->endSetup();
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $optionTable = $this->resourceConnection->getTableName('eav_attribute_option');
        $optionValueTable = $this->resourceConnection->getTableName('eav_attribute_option_value');
        $productIntTable = $this->resourceConnection->getTableName('catalog_product_entity_int');

        $rows = $connection->fetchAll(
            $connection->select()
                ->from(['o' => $optionTable], ['option_id'])
                ->join(
                    ['v' => $optionValueTable],
                    'v.option_id = o.option_id AND v.store_id = 0',
                    ['label' => 'value']
                )
                ->where('o.attribute_id = ?', $attributeId)
                ->order('o.sort_order ASC')
        );

        $groups = [];
        foreach ($rows as $row) {
            $label = (string) $row['label'];
            $key = $this->normalizeLabel($label);
            $groups[$key][] = [
                'option_id' => (int) $row['option_id'],
                'label' => $label,
            ];
        }

        foreach ($groups as $options) {
            if (count($options) < 2) {
                continue;
            }

            $canonical = $this->pickCanonicalOption($options);
            $canonicalId = $canonical['option_id'];
            $canonicalLabel = $this->canonicalLabel($canonical['label']);

            $connection->update(
                $optionValueTable,
                ['value' => $canonicalLabel],
                [
                    'option_id = ?' => $canonicalId,
                    'store_id = ?' => 0,
                ]
            );

            foreach ($options as $option) {
                if ($option['option_id'] === $canonicalId) {
                    continue;
                }

                $connection->update(
                    $productIntTable,
                    ['value' => $canonicalId],
                    [
                        'attribute_id = ?' => $attributeId,
                        'value = ?' => $option['option_id'],
                    ]
                );

                $connection->delete($optionValueTable, ['option_id = ?' => $option['option_id']]);
                $connection->delete($optionTable, ['option_id = ?' => $option['option_id']]);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @param list<array{option_id: int, label: string}> $options
     * @return array{option_id: int, label: string}
     */
    private function pickCanonicalOption(array $options): array
    {
        usort($options, static function (array $left, array $right): int {
            $leftSpaces = substr_count($left['label'], ' ');
            $rightSpaces = substr_count($right['label'], ' ');

            if ($leftSpaces !== $rightSpaces) {
                return $leftSpaces <=> $rightSpaces;
            }

            return strlen($left['label']) <=> strlen($right['label']);
        });

        return $options[0];
    }

    private function normalizeLabel(string $label): string
    {
        return strtolower(preg_replace('/\s+/', '', trim($label)) ?? '');
    }

    private function canonicalLabel(string $label): string
    {
        return preg_replace('/\s+/', '', trim($label)) ?? '';
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
