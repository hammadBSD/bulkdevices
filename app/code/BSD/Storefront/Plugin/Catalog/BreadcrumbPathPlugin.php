<?php

declare(strict_types=1);

namespace BSD\Storefront\Plugin\Catalog;

use Magento\Catalog\Helper\Data as CatalogData;

class BreadcrumbPathPlugin
{
    public function afterGetBreadcrumbPath(CatalogData $subject, array $path): array
    {
        $product = $subject->getProduct();
        if ($product && isset($path['product'])) {
            $path['product']['label'] = $product->getSku();
        }

        return $path;
    }
}
