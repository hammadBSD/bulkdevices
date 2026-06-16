<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel;

use Hyva\Theme\ViewModel\ProductPage;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class CategoryProductPreload implements ArgumentInterface
{
    private const PRELOAD_COUNT = 2;

    public function __construct(
        private readonly CollectionFactory $productCollectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductPage $productPageViewModel,
        private readonly Registry $registry,
    ) {
    }

    /**
     * @return string[]
     */
    public function getPreloadImageUrls(): array
    {
        $category = $this->registry->registry('current_category');
        if (!$category || !$category->getId()) {
            return [];
        }

        $storeId = (int) $this->storeManager->getStore()->getId();
        $collection = $this->productCollectionFactory->create();
        $collection->addCategoryFilter($category);
        $collection->addAttributeToSelect(['name', 'small_image', 'thumbnail', 'image']);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter(
            'visibility',
            ['in' => [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]]
        );
        $collection->setPageSize(self::PRELOAD_COUNT);

        $urls = [];
        foreach ($collection as $product) {
            if (!$product instanceof Product) {
                continue;
            }
            $imageUrl = $this->productPageViewModel
                ->getImage($product, 'category_page_grid')
                ->getImageUrl();
            if ($imageUrl !== '') {
                $urls[] = $imageUrl;
            }
        }

        return array_slice(array_values(array_unique($urls)), 0, self::PRELOAD_COUNT);
    }
}
