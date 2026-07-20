<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Model\Category;

use BSD\ProductAttributes\Model\Config\ProductAttributeRegistry;
use BSD\ProductAttributes\Model\Config\StorageAttributeOptions;
use BSD\ProductAttributes\Setup\Patch\Data\AddCategoryPdpRelatedFilterAttribute;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class PdpRelatedAttributeResolver
{
    public function __construct(
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getMatchAttributeCodes(ProductInterface $product): array
    {
        $categoryIds = array_filter(array_map('intval', (array) $product->getCategoryIds()));
        if ($categoryIds === []) {
            return StorageAttributeOptions::getAttributeCodes();
        }

        $categories = $this->categoryCollectionFactory->create();
        $categories->addAttributeToSelect('level');
        $categories->addAttributeToSelect(AddCategoryPdpRelatedFilterAttribute::ATTRIBUTE_CODE);
        $categories->addIdFilter($categoryIds);
        $categories->setOrder('level', 'DESC');

        foreach ($categories as $category) {
            $codes = $this->getAllowedCodesFromAncestors($category);
            if ($codes !== []) {
                return $codes;
            }
        }

        return StorageAttributeOptions::getAttributeCodes();
    }

    public function hasConfiguredMatchAttributes(ProductInterface $product): bool
    {
        $categoryIds = array_filter(array_map('intval', (array) $product->getCategoryIds()));
        if ($categoryIds === []) {
            return false;
        }

        $categories = $this->categoryCollectionFactory->create();
        $categories->addAttributeToSelect('level');
        $categories->addAttributeToSelect(AddCategoryPdpRelatedFilterAttribute::ATTRIBUTE_CODE);
        $categories->addIdFilter($categoryIds);
        $categories->setOrder('level', 'DESC');

        foreach ($categories as $category) {
            if ($this->getAllowedCodesFromAncestors($category) !== []) {
                return true;
            }
        }

        return false;
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
        $raw = $category->getData(AddCategoryPdpRelatedFilterAttribute::ATTRIBUTE_CODE);

        if (($raw === null || $raw === '') && $category->getId()) {
            $raw = $category->getResource()->getAttributeRawValue(
                (int) $category->getId(),
                AddCategoryPdpRelatedFilterAttribute::ATTRIBUTE_CODE,
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
