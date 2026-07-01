<?php

declare(strict_types=1);

namespace BSD\Brand\ViewModel;

use BSD\Brand\Model\Brand;
use BSD\Brand\Model\BrandRepository;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BrandPage implements ArgumentInterface
{
    public function __construct(
        private readonly Registry $registry,
        private readonly BrandRepository $brandRepository,
    ) {
    }

    public function getBrand(): ?Brand
    {
        $brand = $this->registry->registry('current_brand');
        return $brand instanceof Brand ? $brand : null;
    }

    public function getPageTitle(): string
    {
        return (string) ($this->getBrand()?->getLabel() ?? '');
    }

    public function getBrandRepository(): BrandRepository
    {
        return $this->brandRepository;
    }
}
