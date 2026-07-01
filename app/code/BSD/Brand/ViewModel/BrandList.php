<?php

declare(strict_types=1);

namespace BSD\Brand\ViewModel;

use BSD\Brand\Model\Brand;
use BSD\Brand\Model\BrandRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BrandList implements ArgumentInterface
{
    public function __construct(
        private readonly BrandRepository $brandRepository,
    ) {
    }

    /**
     * @return Brand[]
     */
    public function getBrands(): array
    {
        return $this->brandRepository->getAllBrands();
    }

    public function getBrandUrl(Brand $brand): string
    {
        return $this->brandRepository->getBrandUrl($brand);
    }
}
