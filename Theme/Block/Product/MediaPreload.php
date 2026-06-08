<?php
declare(strict_types=1);

namespace BSD\Theme\Block\Product;

use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;

class MediaPreload extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getLcpImageUrl(): string
    {
        $product = $this->registry->registry('current_product');
        if (!$product) {
            return '';
        }

        $file = (string) $product->getData('image');
        if ($file === '' || $file === 'no_selection') {
            return '';
        }

        $mediaBase = rtrim(
            $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
            '/'
        );
        $path = ltrim(str_replace('\\', '/', $file), '/');

        return $mediaBase . '/catalog/product/' . $path;
    }

    public function getMediaOrigin(): string
    {
        $url = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $parts = parse_url($url);

        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        return $parts['scheme'] . '://' . $parts['host'];
    }
}
