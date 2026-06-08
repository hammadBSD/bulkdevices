<?php
declare(strict_types=1);

namespace BSD\Theme\ViewModel;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Plp implements ArgumentInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly StoreManagerInterface $storeManager,
        private readonly ImageHelper $imageHelper,
        private readonly FilterProvider $filterProvider
    ) {
    }

    public function getStorePhone(): string
    {
        $phone = (string) $this->scopeConfig->getValue(
            'general/store_information/phone',
            ScopeInterface::SCOPE_STORE
        );

        return $phone !== '' ? $phone : '(903) 677-4333';
    }

    public function getStorePhoneTel(): string
    {
        return preg_replace('/\D+/', '', $this->getStorePhone()) ?? '';
    }

    public function isCallForPrice(Product $product): bool
    {
        $final = (float) $product->getPriceInfo()->getPrice('final_price')->getValue();

        return $final <= 0.0;
    }

    public function getDisplayPrice(Product $product): string
    {
        $final = (float) $product->getPriceInfo()->getPrice('final_price')->getValue();

        return $this->priceCurrency->format(
            $final,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION
        );
    }

    public function filterCategoryContent(string $content): string
    {
        return (string) $this->filterProvider->getPageFilter()->filter($content);
    }

    public function getBrandName(Product $product): string
    {
        $text = $product->getAttributeText('manufacturer');
        if (is_string($text) && $text !== '') {
            return $text;
        }
        if (is_array($text) && $text !== []) {
            return (string) reset($text);
        }

        return '';
    }

    /**
     * PLP image URL — direct path on remote media host (avoids local cache/placeholder).
     */
    public function getListingImageUrl(Product $product): string
    {
        $file = (string) ($product->getSmallImage() ?: $product->getImage());
        if ($file === '' || $file === 'no_selection') {
            return $this->imageHelper->getDefaultPlaceholderUrl('image');
        }

        if ($this->usesRemoteMediaHost()) {
            $mediaBase = rtrim(
                (string) $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
                '/'
            );
            $file = '/' . ltrim(str_replace('\\', '/', $file), '/');

            return $mediaBase . '/catalog/product' . $file;
        }

        return $this->imageHelper
            ->init($product, 'category_page_grid')
            ->constrainOnly(true)
            ->keepAspectRatio(true)
            ->keepFrame(false)
            ->resize(240, 240)
            ->getUrl();
    }

    private function usesRemoteMediaHost(): bool
    {
        $store = $this->storeManager->getStore();
        $storeHost = parse_url((string) $store->getBaseUrl(), PHP_URL_HOST);
        $mediaHost = parse_url((string) $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), PHP_URL_HOST);

        return is_string($storeHost)
            && is_string($mediaHost)
            && $storeHost !== ''
            && $mediaHost !== ''
            && strtolower($storeHost) !== strtolower($mediaHost);
    }
}
