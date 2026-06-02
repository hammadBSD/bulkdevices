<?php
declare(strict_types=1);

namespace BSD\Theme\Block\Home;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Sections extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly ImageHelper $imageHelper,
        private readonly StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Top-level categories for homepage grid.
     */
    public function getTopCategories(): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $collection = $this->categoryCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['name', 'url_key', 'image']);
        $collection->addFieldToFilter('level', 2);
        $collection->addFieldToFilter('is_active', 1);
        $collection->addFieldToFilter('include_in_menu', 1);
        $collection->setOrder('position', 'ASC');
        $collection->setPageSize(8);

        $items = [];
        foreach ($collection as $category) {
            $items[] = [
                'name' => $category->getName(),
                'url' => $category->getUrl(),
            ];
        }
        return $items;
    }

    /**
     * Newest in-stock products for promotional grid.
     */
    public function getPromoProducts(): \Magento\Catalog\Model\ResourceModel\Product\Collection
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToSelect(['name', 'price', 'small_image', 'url_key']);
        $collection->addAttributeToFilter('status', 1);
        $collection->setVisibility([
            Visibility::VISIBILITY_IN_CATALOG,
            Visibility::VISIBILITY_BOTH,
        ]);
        $collection->addMinimalPrice();
        $collection->addUrlRewrite();
        $collection->setOrder('created_at', 'desc');
        $collection->setPageSize(10);
        return $collection;
    }

    public function getHeroBannerUrl(): string
    {
        return (string) $this->getViewFileUrl('images/hero-banner.png');
    }

    public function getProductImageUrl(Product $product): string
    {
        return (string) $this->imageHelper->init($product, 'category_page_grid')->getUrl();
    }

    public function getProductPriceHtml(Product $product): string
    {
        $layout = $this->getLayout();
        $priceRender = $layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $layout->createBlock(
                Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }
        return (string) $priceRender->render(
            FinalPrice::PRICE_CODE,
            $product,
            ['zone' => Render::ZONE_ITEM_LIST, 'include_container' => true]
        );
    }
}
