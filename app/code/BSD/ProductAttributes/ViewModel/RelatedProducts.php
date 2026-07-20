<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\ViewModel;

use BSD\ProductAttributes\Model\Category\PdpRelatedAttributeResolver;
use BSD\ProductAttributes\Model\Config\StorageAttributeOptions;
use BSD\ProductAttributes\Model\SimilarProductsByAttributes;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class RelatedProducts implements ArgumentInterface
{
    private const MAX_RESULTS = 8;

    public function __construct(
        private readonly SimilarProductsByAttributes $similarProductsByAttributes,
        private readonly PdpRelatedAttributeResolver $pdpRelatedAttributeResolver,
    ) {
    }

    /**
     * @return ProductInterface[]
     */
    public function getAttributeBasedRelatedProducts(Product $product): array
    {
        return $this->similarProductsByAttributes->getSimilarProducts($product, self::MAX_RESULTS);
    }

    public function hasManagedAttributes(Product $product): bool
    {
        $codes = $this->pdpRelatedAttributeResolver->hasConfiguredMatchAttributes($product)
            ? $this->pdpRelatedAttributeResolver->getMatchAttributeCodes($product)
            : StorageAttributeOptions::getAttributeCodes();

        foreach ($codes as $code) {
            $value = $product->getData($code);
            if ($value !== null && $value !== '' && $value !== '0') {
                return true;
            }
        }

        return false;
    }
}
