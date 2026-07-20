<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Plugin\Layer;

use BSD\ProductAttributes\Model\Config\ProductAttributeRegistry;
use BSD\ProductAttributes\Setup\Patch\Data\AddCategoryPlpFilterAttribute;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;

class FilterableAttributeListPlugin
{
    public function __construct(
        private readonly LayerResolver $layerResolver,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(FilterableAttributeList $subject, Collection $result): Collection
    {
        $layer = $this->layerResolver->get();
        $category = $layer->getCurrentCategory();

        if (!$category instanceof Category || !$category->getId()) {
            return $result;
        }

        $allowedCodes = $this->getAllowedCodesFromAncestors($category);
        $managedCodes = ProductAttributeRegistry::getAttributeCodes();

        foreach ($result as $key => $attribute) {
            $code = (string) $attribute->getAttributeCode();
            if (in_array($code, $managedCodes, true) && !in_array($code, $allowedCodes, true)) {
                $result->removeItemByKey($key);
            }
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    private function getAllowedCodesFromAncestors(Category $category): array
    {
        $current = $category;

        while ($current && $current->getId()) {
            $codes = $this->parseAllowedCodes($current);
            if ($codes !== []) {
                return $codes;
            }

            $parentId = (int) $current->getParentId();
            if ($parentId <= 1) {
                break;
            }

            $current = $current->getParentCategory();
        }

        return [];
    }

    /**
     * @return list<string>
     */
    private function parseAllowedCodes(Category $category): array
    {
        $raw = $category->getData(AddCategoryPlpFilterAttribute::ATTRIBUTE_CODE);

        if (($raw === null || $raw === '') && $category->getId()) {
            $raw = $category->getResource()->getAttributeRawValue(
                (int) $category->getId(),
                AddCategoryPlpFilterAttribute::ATTRIBUTE_CODE,
                (int) $category->getStoreId()
            );
        }

        if (is_array($raw)) {
            return array_values(array_filter(array_map('strval', $raw)));
        }

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
