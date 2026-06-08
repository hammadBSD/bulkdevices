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

    /** Featured Product row on homepage (production category_id=253). */
    private const FEATURED_CATEGORY_ID = 253;

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

    /**
     * Featured products (production Featured Product section).
     */
    public function getFeaturedProducts(): \Magento\Catalog\Model\ResourceModel\Product\Collection
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToSelect(['name', 'price', 'small_image', 'image', 'url_key']);
        $collection->addCategoriesFilter(['in' => [self::FEATURED_CATEGORY_ID]]);
        $collection->addAttributeToFilter('status', 1);
        $collection->setVisibility([
            Visibility::VISIBILITY_IN_CATALOG,
            Visibility::VISIBILITY_BOTH,
        ]);
        $collection->addMinimalPrice();
        $collection->addUrlRewrite();
        $collection->setOrder('entity_id', 'desc');
        $collection->setPageSize(5);

        return $collection;
    }

    /**
     * Top Categories row (production feature-cat section).
     *
     * @return array<int, array{
     *     title: \Magento\Framework\Phrase,
     *     image: string,
     *     see_all_url: string,
     *     links: array<int, array{label: \Magento\Framework\Phrase, url: string}>
     * }>
     */
    public function getTopCategoryBlocks(): array
    {
        return [
            [
                'title' => __('Hard Drive'),
                'image' => 'tc-hdrive-wb.webp',
                'see_all_url' => 'storage-devices',
                'links' => [
                    [
                        'label' => __('Solid State Drives'),
                        'url' => 'storage-devices/internal-storage/solid-state-drives',
                    ],
                    [
                        'label' => __('Desktop Storage'),
                        'url' => 'storage-devices/internal-storage/desktop-hard-drive',
                    ],
                    [
                        'label' => __('Server Storage'),
                        'url' => 'storage-devices/internal-storage/server-hard-drive',
                    ],
                ],
            ],
            [
                'title' => __('CPUS'),
                'image' => 'tc-cpus-wb.webp',
                'see_all_url' => 'processors',
                'links' => [
                    [
                        'label' => __('Server Motherboards'),
                        'url' => 'motherboards/motherboards-classification/server-motherboards',
                    ],
                    [
                        'label' => __('Desktop Motherboards'),
                        'url' => 'motherboards/motherboards-classification/desktop-motherboards',
                    ],
                    [
                        'label' => __('Laptop Motherboards'),
                        'url' => 'motherboards/motherboards-classification/laptop-motherboards',
                    ],
                ],
            ],
            [
                'title' => __('Memory'),
                'image' => 'tc-ram-wb.webp',
                'see_all_url' => 'memory',
                'links' => [
                    [
                        'label' => __('Server Memory'),
                        'url' => 'memory/memory-classification/server-memory',
                    ],
                    [
                        'label' => __('Desktop Memory'),
                        'url' => 'memory/memory-classification/desktop-memory',
                    ],
                    [
                        'label' => __('Laptop Memory'),
                        'url' => 'memory/memory-classification/laptop-memory',
                    ],
                ],
            ],
            [
                'title' => __('Motherboard'),
                'image' => 'tc-motherboard-wb.webp',
                'see_all_url' => 'motherboards',
                'links' => [
                    [
                        'label' => __('Network Switches'),
                        'url' => 'networking-devices/switches/network-switches',
                    ],
                    [
                        'label' => __('Wireless Routers'),
                        'url' => 'networking-devices/wireless-products/wireless-router',
                    ],
                    [
                        'label' => __('KVM Switches'),
                        'url' => 'networking-devices/switches/kvm-switches',
                    ],
                    [
                        'label' => __('Network Adaptors'),
                        'url' => 'networking-devices/network-products/network-adapters',
                    ],
                ],
            ],
        ];
    }

    public function getTopCategoryImageUrl(string $filename): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $themeWebp = BP . '/app/design/frontend/BSD/bulkdevices/web/images/top-categories/' . $base . '.webp';
        if (is_readable($themeWebp)) {
            return (string) $this->getViewFileUrl('images/top-categories/' . $base . '.webp');
        }

        $media = $this->getWysiwygMediaUrl($filename);
        if ($media !== '') {
            return $media;
        }

        return '';
    }

    /**
     * Why Bulk Devices section (production whyus block).
     *
     * @return array<int, \Magento\Framework\Phrase>
     */
    public function getWhyUsBullets(): array
    {
        return [
            __('Dedicated Account Manager'),
            __('Procurement & Purchasing Partner'),
            __('Special ICT Support Services'),
            __('Certified Professional Support'),
        ];
    }

    public function getWhyUsImageUrl(): string
    {
        $themeWebp = BP . '/app/design/frontend/BSD/bulkdevices/web/images/why-us-nimg.webp';
        if (is_readable($themeWebp)) {
            return (string) $this->getViewFileUrl('images/why-us-nimg.webp');
        }

        $media = $this->getWysiwygMediaUrl('why-us-nimg.webp');
        if ($media !== '') {
            return $media;
        }

        return '';
    }

    public function getHpContactBackgroundUrl(): string
    {
        $themeWebp = BP . '/app/design/frontend/BSD/bulkdevices/web/images/hp-contact-bg.webp';
        if (is_readable($themeWebp)) {
            return (string) $this->getViewFileUrl('images/hp-contact-bg.webp');
        }

        $media = $this->getWysiwygMediaUrl('hp-contact-bg.webp');
        if ($media !== '') {
            return $media;
        }

        return '';
    }

    public function getContactFormAction(): string
    {
        return $this->getUrl('contact/index/post', ['_secure' => $this->getRequest()->isSecure()]);
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

    public function getHeroMobileBannerUrl(): string
    {
        return (string) $this->getViewFileUrl('images/hero-banner-mobile.webp');
    }

    public function getTrustIconUrl(string $name): string
    {
        return (string) $this->getViewFileUrl('images/trust/' . $name . '.png');
    }

    /**
     * Industries We Serve — matches production homepage (serve-industry).
     *
     * @return array<int, array{key: string, title: \Magento\Framework\Phrase, description: \Magento\Framework\Phrase, icon: string}>
     */
    public function getIndustries(): array
    {
        return [
            [
                'key' => 'enterprise',
                'title' => __('Enterprise Business'),
                'description' => __('Empower your enterprise with top-tier IT products from renowned manufacturers'),
                'icon' => 'icon-enterprise-business.webp',
            ],
            [
                'key' => 'government',
                'title' => __('Government'),
                'description' => __('Our IT solutions are tailored to address the specific needs of Government agencies in various Sectors.'),
                'icon' => 'icon-federal-government.webp',
            ],
            [
                'key' => 'healthcare',
                'title' => __('Healthcare'),
                'description' => __('Elevate healthcare excellence with our tailored IT solutions'),
                'icon' => 'icon-healthcare.webp',
            ],
            [
                'key' => 'education',
                'title' => __('Education'),
                'description' => __('Enhance education standards with our tailored IT solutions and revolutionize the learning experience.'),
                'icon' => 'icon-government.webp',
            ],
            [
                'key' => 'finance',
                'title' => __('Finance'),
                'description' => __('Boost financial and banking excellence with our specialized IT solutions'),
                'icon' => 'icon-finance.webp',
            ],
            [
                'key' => 'retail',
                'title' => __('Retail'),
                'description' => __('Transform the retail landscape with our specialized IT solutions'),
                'icon' => 'icon-retail.webp',
            ],
        ];
    }

    public function getIndustriesBackgroundUrl(): string
    {
        $media = $this->getWysiwygMediaUrl('industry-serve-bg.webp');
        if ($media !== '') {
            return $media;
        }

        return (string) $this->getViewFileUrl('images/industry-serve-bg.webp');
    }

    public function getIndustryIconUrl(string $key): string
    {
        $mediaFiles = [
            'enterprise' => 'icon-enterprise-business.webp',
            'government' => 'icon-federal-government.webp',
            'healthcare' => 'icon-healthcare.webp',
            'education' => 'icon-government.webp',
            'finance' => 'icon-finance.webp',
            'retail' => 'icon-retail.webp',
        ];

        if (isset($mediaFiles[$key])) {
            $media = $this->getWysiwygMediaUrl($mediaFiles[$key]);
            if ($media !== '') {
                return $media;
            }
        }

        return (string) $this->getViewFileUrl('images/industries/' . $key . '.svg');
    }

    private function getWysiwygMediaUrl(string $filename): string
    {
        $relative = 'wysiwyg/' . ltrim($filename, '/');
        $file = BP . '/pub/media/' . $relative;
        if (!is_readable($file)) {
            return '';
        }

        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $relative;
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

        if (!$this->isCatalogImageAvailable($image)) {
            return $this->getPlaceholderImageUrl();
        }

        $url = (string) $this->imageHelper->init($product, 'bd_home_product')->getUrl();
        if ($url === '' || str_contains($url, 'placeholder/.') || str_contains($url, '/placeholder/.')) {
            return $this->getPlaceholderImageUrl();
        }

        return $url;
    }

    public function isPlaceholderImageUrl(string $url): bool
    {
        return $url === '' || str_contains($url, '/placeholder/');
    }

    public function getHomeProductImageSizes(): string
    {
        return '(max-width: 639px) 42vw, (max-width: 1023px) 28vw, 200px';
    }

    private function isCatalogImageAvailable(string $imagePath): bool
    {
        $relative = ltrim($imagePath, '/');
        $file = BP . '/pub/media/catalog/product/' . $relative;

        return is_readable($file);
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
