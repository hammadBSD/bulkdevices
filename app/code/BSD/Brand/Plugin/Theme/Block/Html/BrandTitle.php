<?php

declare(strict_types=1);

namespace BSD\Brand\Plugin\Theme\Block\Html;

use BSD\Brand\Model\Brand;
use Magento\Framework\Registry;
use Magento\Theme\Block\Html\Title;

class BrandTitle
{
    public function __construct(
        private readonly Registry $registry,
    ) {
    }

    public function afterGetPageHeading(Title $subject, string $result): string
    {
        $brand = $this->getCurrentBrand();
        return $brand !== null ? $brand->getLabel() : $result;
    }

    public function afterGetPageTitle(Title $subject, string $result): string
    {
        $brand = $this->getCurrentBrand();
        return $brand !== null ? $brand->getLabel() : $result;
    }

    private function getCurrentBrand(): ?Brand
    {
        $brand = $this->registry->registry('current_brand');
        return $brand instanceof Brand ? $brand : null;
    }
}
