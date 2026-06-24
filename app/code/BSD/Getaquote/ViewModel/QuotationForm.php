<?php

declare(strict_types=1);

namespace BSD\Getaquote\ViewModel;

use Hyva\Theme\ViewModel\CurrentProduct;
use Hyva\Theme\ViewModel\ProductPage;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class QuotationForm implements ArgumentInterface, IdentityInterface
{
    private const XML_PATH_RECAPTCHA_SITE_KEY = 'getaquote/recaptcha_settings/site_key';
    private const XML_PATH_RECAPTCHA_SCRIPT_URL = 'getaquote/recaptcha_settings/script_url';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $urlBuilder,
        private readonly CurrentProduct $currentProduct,
        private readonly ProductPage $productPage,
        private readonly RequestInterface $request,
    ) {
    }

    public function getSaveUrl(): string
    {
        return $this->urlBuilder->getUrl('getaquote/index/save', ['_secure' => true]);
    }

    public function getImageUrl(): string
    {
        return $this->urlBuilder->getUrl('getaquote/index/getimagepath', ['_secure' => true]);
    }

    public function getRecaptchaSiteKey(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_SITE_KEY, ScopeInterface::SCOPE_STORE);
    }

    public function getRecaptchaScriptUrl(): string
    {
        $configured = (string) $this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_SCRIPT_URL, ScopeInterface::SCOPE_STORE);
        if ($configured !== '') {
            return $configured;
        }

        $siteKey = $this->getRecaptchaSiteKey();
        return $siteKey !== ''
            ? 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode($siteKey)
            : '';
    }

    public function isRecaptchaEnabled(): bool
    {
        return $this->getRecaptchaSiteKey() !== '';
    }

    public function getDefaultProductSku(): string
    {
        if (!$this->currentProduct->exists()) {
            return '';
        }

        return (string) $this->currentProduct->get()->getSku();
    }

    /**
     * PDP gallery thumbnail — same image id as hidden gallery thumbs.
     */
    public function getDefaultProductImageUrl(): string
    {
        if (!$this->currentProduct->exists()) {
            return '';
        }

        return $this->productPage
            ->getImage($this->currentProduct->get(), 'product_page_image_small')
            ->getImageUrl();
    }

    public function isCallForPriceProduct(): bool
    {
        if (!$this->currentProduct->exists()) {
            return false;
        }

        $product = $this->currentProduct->get();
        if (!$product instanceof Product) {
            return false;
        }

        $finalPrice = (float) $product->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE)
            ->getValue();

        return $finalPrice <= 0;
    }

    /**
     * Floating RFQ trigger — not on PDP, homepage, or category PLP.
     */
    public function shouldShowFloatingTrigger(): bool
    {
        if ($this->currentProduct->exists()) {
            return false;
        }

        return !in_array($this->request->getFullActionName(), [
            'cms_index_index',
            'catalog_category_view',
        ], true);
    }

    public function getIdentities(): array
    {
        if (!$this->currentProduct->exists()) {
            return [];
        }

        $product = $this->currentProduct->get();

        return $product instanceof IdentityInterface
            ? $product->getIdentities()
            : [];
    }
}
