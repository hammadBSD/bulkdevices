<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel\Schema;

use Aalogics\CategoryText\ViewModel\CategoryFaqs;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class StructuredData implements ArgumentInterface
{
    private const ORG_EMAIL = 'support@bulkdevices.com';
    private const ORG_PHONE = '+1-903-677-4333';
    private const SAME_AS = [
        'https://www.facebook.com/bulkdevices',
        'https://www.linkedin.com/company/bulk-devices-us',
    ];
    private const STORE_STREET = '13207 Lone Creek';
    private const STORE_CITY = 'Pearland';
    private const STORE_REGION = 'TX';
    private const STORE_POSTAL = '77548';
    private const STORE_COUNTRY = 'US';

    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ImageHelper $imageHelper,
        private readonly Registry $registry,
        private readonly CategoryFaqs $categoryFaqs,
        private readonly PageConfig $pageConfig,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getHomepageGraph(): array
    {
        $pageTitle = (string) $this->pageConfig->getTitle()->get();
        $pageDescription = (string) $this->pageConfig->getDescription();

        $pageUrl = $this->getStoreBaseUrl();
        $graph = [
            $this->getOrganizationNode(),
            $this->getWebsiteNode(),
            $this->getStoreNode(),
            [
                '@type' => 'WebPage',
                '@id' => $pageUrl . '#webpage',
                'url' => $pageUrl,
                'name' => $pageTitle !== '' ? $pageTitle : 'BulkDevices',
                'description' => $pageDescription !== '' ? $pageDescription : $this->getDefaultHomeDescription(),
                'isPartOf' => ['@id' => $this->getWebsiteId()],
                'about' => ['@id' => $this->getOrganizationId()],
                'primaryImageOfPage' => [
                    '@type' => 'ImageObject',
                    'url' => $this->getLogoUrl(),
                ],
                'inLanguage' => $this->getLanguageCode(),
            ],
        ];

        return $graph;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCategoryGraph(?ProductCollection $productCollection): ?array
    {
        $category = $this->registry->registry('current_category');
        if (!$category instanceof Category) {
            return null;
        }

        $pageUrl = $this->absoluteUrl((string) $category->getUrl());
        $breadcrumbId = $pageUrl . '#breadcrumb';
        $itemListId = $pageUrl . '#itemlist';

        $graph = [
            $this->getOrganizationNode(),
            $this->getWebsiteNode(),
            [
                '@type' => 'CollectionPage',
                '@id' => $pageUrl . '#webpage',
                'url' => $pageUrl,
                'name' => (string) $category->getName(),
                'description' => $this->getCategoryDescription($category),
                'isPartOf' => ['@id' => $this->getWebsiteId()],
                'breadcrumb' => ['@id' => $breadcrumbId],
                'mainEntity' => ['@id' => $itemListId],
                'inLanguage' => $this->getLanguageCode(),
            ],
            [
                '@type' => 'BreadcrumbList',
                '@id' => $breadcrumbId,
                'itemListElement' => $this->buildBreadcrumbListElements($pageUrl),
            ],
        ];

        $itemList = $this->buildItemList($productCollection, $itemListId);
        if ($itemList !== null) {
            $graph[] = $itemList;
        }

        $faqNode = $this->buildFaqNode($pageUrl);
        if ($faqNode !== null) {
            $graph[] = $faqNode;
        }

        return $graph;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getProductGraph(): ?array
    {
        $product = $this->registry->registry('current_product');
        if (!$product instanceof Product) {
            return null;
        }

        $pageUrl = $this->absoluteUrl((string) $product->getProductUrl());
        $productId = $pageUrl . '#product';
        $breadcrumbId = $pageUrl . '#breadcrumb';

        $productNode = $this->buildProductNode($product, $productId, $pageUrl);
        if ($productNode === null) {
            return null;
        }

        return [
            $this->getOrganizationNode(),
            $this->getWebsiteNode(),
            $productNode,
            [
                '@type' => 'BreadcrumbList',
                '@id' => $breadcrumbId,
                'itemListElement' => $this->buildBreadcrumbListElements($pageUrl),
            ],
            [
                '@type' => 'WebPage',
                '@id' => $pageUrl . '#webpage',
                'url' => $pageUrl,
                'name' => (string) $product->getName(),
                'isPartOf' => ['@id' => $this->getWebsiteId()],
                'breadcrumb' => ['@id' => $breadcrumbId],
                'mainEntity' => ['@id' => $productId],
                'inLanguage' => $this->getLanguageCode(),
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $graph
     */
    public function toJson(array $graph): string
    {
        if ($graph === []) {
            return '';
        }

        return (string) json_encode(
            [
                '@context' => 'https://schema.org',
                '@graph' => array_values($graph),
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getOrganizationNode(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => $this->getOrganizationId(),
            'name' => $this->getBusinessName(),
            'url' => $this->getStoreBaseUrl(),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $this->getLogoUrl(),
            ],
            'email' => self::ORG_EMAIL,
            'telephone' => self::ORG_PHONE,
            'sameAs' => self::SAME_AS,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getWebsiteNode(): array
    {
        $baseUrl = $this->getStoreBaseUrl();

        return [
            '@type' => 'WebSite',
            '@id' => $this->getWebsiteId(),
            'url' => $baseUrl,
            'name' => $this->getBusinessName(),
            'publisher' => ['@id' => $this->getOrganizationId()],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $baseUrl . 'catalogsearch/result/?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getStoreNode(): array
    {
        return [
            '@type' => 'Store',
            '@id' => $this->getStoreId(),
            'name' => $this->getBusinessName(),
            'url' => $this->getStoreBaseUrl(),
            'image' => $this->getLogoUrl(),
            'telephone' => self::ORG_PHONE,
            'email' => self::ORG_EMAIL,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => self::STORE_STREET,
                'addressLocality' => self::STORE_CITY,
                'addressRegion' => self::STORE_REGION,
                'postalCode' => self::STORE_POSTAL,
                'addressCountry' => self::STORE_COUNTRY,
            ],
            'openingHoursSpecification' => [
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => [
                        'Monday',
                        'Tuesday',
                        'Wednesday',
                        'Thursday',
                        'Friday',
                    ],
                    'opens' => '08:00',
                    'closes' => '17:00',
                ],
            ],
            'parentOrganization' => ['@id' => $this->getOrganizationId()],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildBreadcrumbListElements(string $pageUrl): array
    {
        $elements = [];
        $position = 1;

        $elements[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => $this->getStoreBaseUrl(),
        ];

        $category = $this->registry->registry('current_category');
        if ($category instanceof Category) {
            foreach ($this->getCategoryTrail($category) as $trailCategory) {
                $elements[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => (string) $trailCategory->getName(),
                    'item' => $this->absoluteUrl((string) $trailCategory->getUrl()),
                ];
            }

            return $elements;
        }

        $product = $this->registry->registry('current_product');
        if ($product instanceof Product) {
            $productCategory = $this->getPrimaryProductCategory($product);
            if ($productCategory instanceof Category) {
                foreach ($this->getCategoryTrail($productCategory) as $trailCategory) {
                    $elements[] = [
                        '@type' => 'ListItem',
                        'position' => $position++,
                        'name' => (string) $trailCategory->getName(),
                        'item' => $this->absoluteUrl((string) $trailCategory->getUrl()),
                    ];
                }
            }

            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => (string) $product->getSku(),
                'item' => $pageUrl,
            ];
        }

        return $elements;
    }

    /**
     * @return array<int, Category>
     */
    private function getCategoryTrail(Category $category): array
    {
        $trail = [];
        $pathIds = array_reverse(explode(',', (string) $category->getPathInStore()));
        $parents = $category->getParentCategories();

        foreach ($pathIds as $categoryId) {
            if (!isset($parents[$categoryId])) {
                continue;
            }

            $name = (string) $parents[$categoryId]->getName();
            if ($name === '') {
                continue;
            }

            $trail[] = $parents[$categoryId];
        }

        return $trail;
    }

    private function getPrimaryProductCategory(Product $product): ?Category
    {
        $category = $this->registry->registry('current_category');
        if ($category instanceof Category) {
            return $category;
        }

        $collection = $product->getCategoryCollection()
            ->addAttributeToSelect(['name', 'url_key', 'url_path', 'path', 'level'])
            ->addIsActiveFilter()
            ->setOrder('level', 'DESC');

        $deepest = $collection->getFirstItem();

        return $deepest->getId() ? $deepest : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildItemList(?ProductCollection $collection, string $itemListId): ?array
    {
        if ($collection === null || $collection->count() === 0) {
            return null;
        }

        $elements = [];
        $position = 1;

        foreach ($collection as $product) {
            if (!$product instanceof Product) {
                continue;
            }

            $snippet = $this->buildListingProductNode($product);
            if ($snippet === null) {
                continue;
            }

            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'item' => $snippet,
            ];
            $position++;

            if ($position > 24) {
                break;
            }
        }

        if ($elements === []) {
            return null;
        }

        return [
            '@type' => 'ItemList',
            '@id' => $itemListId,
            'numberOfItems' => (int) $collection->getSize(),
            'itemListElement' => $elements,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildListingProductNode(Product $product): ?array
    {
        $price = $this->getProductFinalPrice($product);
        if ($price === null) {
            return null;
        }

        $imageUrl = $this->getProductImageUrl($product, 'category_page_grid');
        $productUrl = $this->absoluteUrl((string) $product->getProductUrl());

        $node = [
            '@type' => 'Product',
            'name' => (string) $product->getName(),
            'url' => $productUrl,
            'sku' => (string) $product->getSku(),
            'offers' => [
                '@type' => 'Offer',
                'url' => $productUrl,
                'priceCurrency' => $this->getCurrencyCode(),
                'price' => $this->formatPrice($price),
                'availability' => $this->getAvailabilityUrl($product),
            ],
        ];

        if ($imageUrl !== '') {
            $node['image'] = $imageUrl;
        }

        return $node;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildProductNode(Product $product, string $productId, string $pageUrl): ?array
    {
        $price = $this->getProductFinalPrice($product);

        $node = [
            '@type' => 'Product',
            '@id' => $productId,
            'name' => (string) $product->getName(),
            'url' => $pageUrl,
            'sku' => (string) $product->getSku(),
        ];

        $description = $this->getProductDescription($product);
        if ($description !== '') {
            $node['description'] = $description;
        }

        $images = $this->getProductImageUrls($product);
        if ($images !== []) {
            $node['image'] = count($images) === 1 ? $images[0] : $images;
        }

        $brand = $product->getAttributeText('manufacturer');
        if (is_string($brand) && $brand !== '') {
            $node['brand'] = [
                '@type' => 'Brand',
                'name' => $brand,
            ];
        }

        $mpn = (string) ($product->getData('mpn') ?: $product->getSku());
        if ($mpn !== '') {
            $node['mpn'] = $mpn;
        }

        if ($price !== null) {
            $node['offers'] = [
                '@type' => 'Offer',
                'url' => $pageUrl,
                'priceCurrency' => $this->getCurrencyCode(),
                'price' => $this->formatPrice($price),
                'availability' => $this->getAvailabilityUrl($product),
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => ['@id' => $this->getOrganizationId()],
            ];
        }

        return $node;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildFaqNode(string $pageUrl): ?array
    {
        $parsed = $this->categoryFaqs->getParsedContent();
        if ($parsed === null || empty($parsed['faqItems'])) {
            return null;
        }

        $mainEntity = [];
        foreach ($parsed['faqItems'] as $faqItem) {
            $question = trim(strip_tags((string) ($faqItem['title'] ?? '')));
            $answer = trim(strip_tags((string) ($faqItem['content'] ?? '')));

            if ($question === '' || $answer === '') {
                continue;
            }

            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        if ($mainEntity === []) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            '@id' => $pageUrl . '#faq',
            'mainEntity' => $mainEntity,
        ];
    }

    private function getCategoryDescription(Category $category): string
    {
        $meta = trim((string) $category->getMetaDescription());
        if ($meta !== '') {
            return $this->truncate($meta, 500);
        }

        $description = trim(strip_tags((string) $category->getDescription()));

        return $this->truncate($description !== '' ? $description : (string) $category->getName(), 500);
    }

    private function getProductDescription(Product $product): string
    {
        $short = trim(strip_tags((string) $product->getShortDescription()));
        if ($short !== '') {
            return $this->truncate($short, 5000);
        }

        return $this->truncate(trim(strip_tags((string) $product->getDescription())), 5000);
    }

    /**
     * @return string[]
     */
    private function getProductImageUrls(Product $product): array
    {
        $urls = [];
        $gallery = $product->getMediaGalleryImages();
        if ($gallery) {
            foreach ($gallery as $image) {
                if (!$image instanceof DataObject) {
                    continue;
                }

                $url = (string) $image->getData('url');
                if ($url !== '') {
                    $urls[] = $url;
                }
            }
        }

        if ($urls === []) {
            $fallback = $this->getProductImageUrl($product, 'product_page_image_large');
            if ($fallback !== '') {
                $urls[] = $fallback;
            }
        }

        return array_values(array_unique($urls));
    }

    private function getProductImageUrl(Product $product, string $imageId): string
    {
        return (string) $this->imageHelper->init($product, $imageId)->getUrl();
    }

    private function getProductFinalPrice(Product $product): ?float
    {
        $price = (float) $product->getFinalPrice();
        if ($price <= 0) {
            return null;
        }

        return $price;
    }

    private function getAvailabilityUrl(Product $product): string
    {
        return $product->isSalable()
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock';
    }

    private function formatPrice(float $price): string
    {
        return number_format($price, 2, '.', '');
    }

    private function getOrganizationId(): string
    {
        return $this->getStoreBaseUrl() . '#organization';
    }

    private function getWebsiteId(): string
    {
        return $this->getStoreBaseUrl() . '#website';
    }

    private function getStoreId(): string
    {
        return $this->getStoreBaseUrl() . '#store';
    }

    private function getStoreBaseUrl(): string
    {
        return rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB), '/') . '/';
    }

    private function absoluteUrl(string $url): string
    {
        if ($url === '') {
            return $this->getStoreBaseUrl();
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim($this->getStoreBaseUrl(), '/') . '/' . ltrim($url, '/');
    }

    private function getLogoUrl(): string
    {
        $logo = (string) $this->scopeConfig->getValue(
            'design/header/logo_src',
            ScopeInterface::SCOPE_STORE
        );

        if ($logo === '') {
            return $this->getStoreBaseUrl() . 'media/logo/stores/1/bdlogo.webp';
        }

        return rtrim(
            $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
            '/'
        ) . '/' . ltrim($logo, '/');
    }

    private function getBusinessName(): string
    {
        $name = (string) $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE
        );

        return $name !== '' ? $name : 'BulkDevices';
    }

    private function getCurrencyCode(): string
    {
        return (string) $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    private function getLanguageCode(): string
    {
        return str_replace('_', '-', (string) $this->storeManager->getStore()->getConfig('general/locale/code'));
    }

    private function getDefaultHomeDescription(): string
    {
        return 'BulkDevices is a trusted supplier of wholesale IT hardware, servers, networking equipment, storage devices, processors, memory, laptops, printers and computer components from leading brands.';
    }

    private function truncate(string $value, int $maxLength): string
    {
        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return rtrim(substr($value, 0, $maxLength - 1)) . '…';
    }
}
