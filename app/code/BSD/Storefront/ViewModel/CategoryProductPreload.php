<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel;

use Hyva\Theme\ViewModel\ProductPage;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CategoryProductPreload implements ArgumentInterface, IdentityInterface
{
    private const PRELOAD_COUNT = 2;

    public function __construct(
        private readonly LayerResolver $layerResolver,
        private readonly ProductPage $productPageViewModel,
        private readonly Registry $registry,
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

        return array_slice(array_values(array_unique($urls)), 0, self::PRELOAD_COUNT);
    }

    public function getIdentities(): array
    {
        $identities = [];
        $category = $this->registry->registry('current_category');
        if ($category instanceof IdentityInterface) {
            $identities = array_merge($identities, $category->getIdentities());
        }

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
        $category = $this->registry->registry('current_category');
        if (!$category || !$category->getId()) {
            return [];
        }

        try {
            $layerCollection = $this->layerResolver->get()->getProductCollection();
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $preloadCollection */
            $preloadCollection = clone $layerCollection;
            $preloadCollection->clear();
            $preloadCollection->setPageSize(self::PRELOAD_COUNT);
            $preloadCollection->setCurPage(1);
            $preloadCollection->addAttributeToSelect(['small_image', 'thumbnail', 'image']);
        } catch (\Throwable) {
            return [];
        }

        $products = [];
        foreach ($preloadCollection as $product) {
            if ($product instanceof Product) {
                $products[] = $product;
            }
        }

        return $products;
    }
}
