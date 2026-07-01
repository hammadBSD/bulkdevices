<?php

declare(strict_types=1);

namespace BSD\Brand\Model;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\UrlInterface;

class BrandRepository
{
    private const MANUFACTURER_ATTRIBUTE = 'manufacturer';

    /**
     * Optional brand logos keyed by manufacturer option ID.
     *
     * @var array<int, string>
     */
    private const BRAND_IMAGES = [
        5596 => 'https://bulkdevices.com/media/wysiwyg/home-dell.webp',
        5434 => 'https://bulkdevices.com/media/wysiwyg/home-hp.webp',
        5605 => 'https://bulkdevices.com/media/wysiwyg/home-lenovo.webp',
        5597 => 'https://bulkdevices.com/media/wysiwyg/home-cisco.webp',
        5480 => 'https://bulkdevices.com/media/wysiwyg/home-d-link.webp',
        5455 => 'https://bulkdevices.com/media/wysiwyg/home-asus.webp',
        5544 => 'https://bulkdevices.com/media/wysiwyg/home-netgear.webp',
        5517 => 'https://bulkdevices.com/media/wysiwyg/home-intel.webp',
        5546 => 'https://bulkdevices.com/media/wysiwyg/home-nvidia.webp',
        5573 => 'https://bulkdevices.com/media/wysiwyg/home-sun.webp',
        5667 => 'https://bulkdevices.com/media/wysiwyg/home-tp-link.webp',
        5594 => 'https://bulkdevices.com/media/wysiwyg/home-ibm.webp',
        5563 => 'https://bulkdevices.com/media/wysiwyg/home-seagate.webp',
        5600 => 'https://bulkdevices.com/media/wysiwyg/home-juniper.webp',
        5767 => 'https://bulkdevices.com/media/wysiwyg/home-ruckus.webp',
        5631 => 'https://bulkdevices.com/media/wysiwyg/home-polycom.webp',
        5645 => 'https://bulkdevices.com/media/wysiwyg/home-broadcom.webp',
    ];

    /** @var array<string, Brand>|null */
    private ?array $brandsBySlug = null;

    /** @var Brand[]|null */
    private ?array $allBrands = null;

    public function __construct(
        private readonly EavConfig $eavConfig,
        private readonly Slugifier $slugifier,
        private readonly UrlInterface $urlBuilder,
    ) {
    }

    /**
     * @return Brand[]
     */
    public function getAllBrands(): array
    {
        if ($this->allBrands !== null) {
            return $this->allBrands;
        }

        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, self::MANUFACTURER_ATTRIBUTE);
        $options = $attribute->getSource()->getAllOptions(false);
        $brands = [];

        foreach ($options as $option) {
            $optionId = (int) ($option['value'] ?? 0);
            $label = trim((string) ($option['label'] ?? ''));
            if ($optionId <= 0 || $label === '') {
                continue;
            }

            $slug = $this->slugifier->slugify($label);
            if ($slug === '') {
                continue;
            }

            $brands[] = $this->createBrand($optionId, $label, $slug);
        }

        usort(
            $brands,
            static fn (Brand $a, Brand $b): int => strcasecmp($a->getLabel(), $b->getLabel())
        );

        $this->allBrands = $brands;
        $this->brandsBySlug = [];
        foreach ($brands as $brand) {
            $this->brandsBySlug[$brand->getSlug()] = $brand;
        }

        return $this->allBrands;
    }

    public function getBySlug(string $slug): ?Brand
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return null;
        }

        $this->getAllBrands();

        return $this->brandsBySlug[$slug] ?? null;
    }

    public function getBrandUrl(Brand $brand): string
    {
        return $this->urlBuilder->getUrl('', ['_direct' => 'brand/' . $brand->getSlug()]);
    }

    private function createBrand(int $optionId, string $label, string $slug): Brand
    {
        return new Brand([
            'option_id' => $optionId,
            'label' => $label,
            'slug' => $slug,
            'image_url' => self::BRAND_IMAGES[$optionId] ?? null,
        ]);
    }
}
