<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Model;

use BSD\ProductAttributes\Model\Category\PdpRelatedAttributeResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class SimilarProductsByAttributes
{
    private const MAX_RESULTS = 8;

    public function __construct(
        private readonly CollectionFactory $productCollectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly PdpRelatedAttributeResolver $pdpRelatedAttributeResolver,
    ) {
    }

    /**
     * @return ProductInterface[]
     */
    public function getSimilarProducts(ProductInterface $product, int $limit = self::MAX_RESULTS): array
    {
        $filters = $this->getActiveAttributeFilters($product);
        if ($filters === []) {
            return [];
        }

        $collection = $this->buildCollection($product, $filters, $limit);
        return array_values($collection->getItems());
    }

    /**
     * @return array<string, int|string>
     */
    private function getActiveAttributeFilters(ProductInterface $product): array
    {
        $allowedCodes = $this->pdpRelatedAttributeResolver->getMatchAttributeCodes($product);
        $filters = [];

        foreach ($allowedCodes as $code) {
            $value = $product->getData($code);
            if ($value !== null && $value !== '' && $value !== '0') {
                $filters[$code] = $value;
            }
        }

        return $filters;
    }

    /**
     * @param array<string, int|string> $filters
     */
    private function buildCollection(ProductInterface $product, array $filters, int $limit): Collection
    {
        /** @var Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId((int) $this->storeManager->getStore()->getId());
        $collection->addStoreFilter();
        $collection->addAttributeToSelect(['name', 'sku', 'small_image', 'price', 'special_price']);
        $collection->addAttributeToFilter('entity_id', ['neq' => (int) $product->getId()]);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('visibility', [
            'in' => [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH],
        ]);

        foreach ($filters as $code => $value) {
            $collection->addAttributeToFilter($code, ['eq' => $value]);
        }

        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $collection->addCategoriesFilter(['in' => $categoryIds]);
        }

        $collection->setPageSize($limit);
        $collection->setCurPage(1);

        return $collection;
    }
}
