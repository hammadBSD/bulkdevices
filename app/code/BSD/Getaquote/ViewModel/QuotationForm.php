<?php

declare(strict_types=1);

namespace BSD\Getaquote\ViewModel;

use Hyva\Theme\ViewModel\CurrentProduct;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class QuotationForm implements ArgumentInterface
{
    private const XML_PATH_RECAPTCHA_SITE_KEY = 'getaquote/recaptcha_settings/site_key';
    private const XML_PATH_RECAPTCHA_SCRIPT_URL = 'getaquote/recaptcha_settings/script_url';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $urlBuilder,
        private readonly CurrentProduct $currentProduct,
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
        $product = $this->currentProduct->get();
        if (!$product || !$product->getId()) {
            return '';
        }

        return (string) $product->getSku();
    }
}
