<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel;

use Hyva\Theme\ViewModel\ProductPage;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class HomePromotionalPreload implements ArgumentInterface, IdentityInterface
{
    private const PRELOAD_PRODUCT_COUNT = 1;

    public function __construct(
        private readonly CollectionFactory $productCollectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductPage $productPageViewModel,
        private readonly PromotionalProducts $promotionalProducts,
    ) {
    }

    /**
     * @return string[]
     */
    public function getPreloadImageUrls(): array
    {
        $urls = [];
        foreach ($this->getPreloadProducts() as $product) {
            $imageUrl = $this->productPageViewModel
                ->getImage($product, 'category_page_grid')
                ->getImageUrl();
            if ($imageUrl !== '') {
                $urls[] = $imageUrl;
            }
        }

        return array_values(array_unique($urls));
    }

    public function getIdentities(): array
    {
        $identities = $this->promotionalProducts->getIdentities();

        foreach ($this->getPreloadProducts() as $product) {
            if ($product instanceof IdentityInterface) {
                $identities = array_merge($identities, $product->getIdentities());
            }
        }

        return array_values(array_unique($identities));
    }

    /**
     * @return Product[]
     */
    private function getPreloadProducts(): array
    {
        $urlKeys = array_slice($this->promotionalProducts->getStaticHardDriveUrlKeys(), 0, self::PRELOAD_PRODUCT_COUNT);
        if ($urlKeys === []) {
            return [];
        }

        $storeId = (int) $this->storeManager->getStore()->getId();
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'small_image', 'thumbnail', 'image', 'url_key']);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToFilter('url_key', ['in' => $urlKeys]);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter(
            'visibility',
            ['in' => [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]]
        );
        $collection->setPageSize(count($urlKeys));

        $productsByUrlKey = [];
        foreach ($collection as $product) {
            if ($product instanceof Product) {
                $productsByUrlKey[(string) $product->getUrlKey()] = $product;
            }
        }

        $products = [];
        foreach ($urlKeys as $urlKey) {
            $product = $productsByUrlKey[$urlKey] ?? null;
            if ($product instanceof Product) {
                $products[] = $product;
            }
        }

        return $products;
    }
}
