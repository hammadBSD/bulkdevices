<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel;

use Hyva\Theme\ViewModel\ProductPage;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class HomePromotionalPreload implements ArgumentInterface
{
    private const PROMOTIONAL_SKUS = [
        'FPR1010-NGFW-K9',
        'PWR-IE65W-PC-DC',
    ];

    public function __construct(
        private readonly CollectionFactory $productCollectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductPage $productPageViewModel,
    ) {
    }

    /**
     * @return string[]
     */
    public function getPreloadImageUrls(): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'small_image', 'thumbnail', 'image']);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToFilter('sku', ['in' => self::PROMOTIONAL_SKUS]);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter(
            'visibility',
            ['in' => [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]]
        );
        $collection->setPageSize(count(self::PROMOTIONAL_SKUS));

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

        return array_slice(array_values(array_unique($urls)), 0, 2);
    }
}
