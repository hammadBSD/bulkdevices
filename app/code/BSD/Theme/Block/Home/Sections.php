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
use Magento\Framework\UrlInterface;
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

    private const PROMO_ROOT_CATEGORY_ID = 9969;

    /**
     * Brand tabs for the promotional products row (matches production).
     *
     * @return array<int, array{label: string, id: int, url: string}>
     */
    public function getPromoFilters(): array
    {
        $activeId = (int) $this->getRequest()->getParam('promo_brand', 0);
        $homeUrl = $this->getUrl('');

        $filters = [
            ['label' => (string) __('All'), 'id' => 0],
            ['label' => 'HP', 'id' => 9970],
            ['label' => 'Cisco', 'id' => 9971],
            ['label' => 'Seagate', 'id' => 9973],
            ['label' => 'Dell', 'id' => 9974],
            ['label' => (string) __('Western Digital'), 'id' => 9972],
        ];

        $out = [];
        foreach ($filters as $filter) {
            $id = $filter['id'];
            $out[] = [
                'label' => $filter['label'],
                'id' => $id,
                'active' => ($activeId === 0 && $id === 0) || ($activeId > 0 && $activeId === $id),
                'url' => $id === 0 ? $homeUrl : $homeUrl . '?promo_brand=' . $id,
            ];
        }
        return $out;
    }

    /**
     * Products from the Promotional Products category (and brand subcategories).
     */
    public function getPromoProducts(): \Magento\Catalog\Model\ResourceModel\Product\Collection
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $brandId = (int) $this->getRequest()->getParam('promo_brand', 0);
        $categoryIds = $brandId > 0
            ? [$brandId]
            : [self::PROMO_ROOT_CATEGORY_ID, 9970, 9971, 9972, 9973, 9974];

        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToSelect(['name', 'price', 'small_image', 'image', 'url_key']);
        $collection->addCategoriesFilter(['in' => $categoryIds]);
        $collection->addAttributeToFilter('status', 1);
        $collection->setVisibility([
            Visibility::VISIBILITY_IN_CATALOG,
            Visibility::VISIBILITY_BOTH,
        ]);
        $collection->addMinimalPrice();
        $collection->addUrlRewrite();
        $collection->setOrder('entity_id', 'desc');
        $collection->setPageSize(10);
        return $collection;
    }

    public function getPlaceholderImageUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product/placeholder/default/placeholder_1.webp';
    }

    public function getHeroBannerUrl(): string
    {
        return (string) $this->getViewFileUrl('images/hero-banner.webp');
    }

    public function getTrustIconUrl(string $name): string
    {
        return (string) $this->getViewFileUrl('images/trust/' . $name . '.png');
    }

    public function getProductImageUrl(Product $product): string
    {
        $image = (string) $product->getSmallImage();
        if ($image === '' || $image === 'no_selection') {
            $image = (string) $product->getImage();
        }
        if ($image === '' || $image === 'no_selection') {
            return $this->getPlaceholderImageUrl();
        }

        $url = (string) $this->imageHelper->init($product, 'category_page_grid')->getUrl();
        if ($url === '' || str_contains($url, 'placeholder/.') || str_contains($url, '/placeholder/.')) {
            return $this->getPlaceholderImageUrl();
        }

        return $url;
    }

    public function getFormattedPrice(Product $product): string
    {
        $price = $product->getFinalPrice();
        if ($price === null || $price === '') {
            return '';
        }
        return (string) $this->storeManager->getStore()->getCurrentCurrency()->format(
            (float) $price,
            [],
            false
        );
    }

    public function isQuoteProduct(Product $product): bool
    {
        return (float) $product->getFinalPrice() <= 0;
    }

    public function getAddToCartUrl(Product $product): string
    {
        return $this->getUrl('checkout/cart/add', [
            'product' => (int) $product->getId(),
            '_secure' => $this->getRequest()->isSecure(),
        ]);
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
