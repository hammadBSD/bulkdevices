<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel;

use Magento\Catalog\Model\Category;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class PromotionalProducts implements ArgumentInterface, IdentityInterface
{
    private const DEFAULT_PRODUCT_COUNT = 10;

    /**
     * Hard Drive tab — fixed product order (url_key), rendered statically on first paint.
     *
     * @return string[]
     */
    public function getStaticHardDriveUrlKeys(): array
    {
        return [
            '455019-001caddy-hp-250gb-5400rpm-sata-1-5gb-s-8mb-cache-9-5mm-2-5-inch-hard-drive',
            'st31000524as-pcb-seagate-barracuda-1tb-7200rpm-sata-6s-32mb-cache-3-5-inch-internal-hard-drive',
            '24p3879-ibm-40gb-5400rpm-ide-2-5-inch-internal-hard-disk-drive',
            'k2q15a-hp-d6000-w-35-6tb-6g-sas-7-2k-lff-3-5-inch-dual-port-mdl-hdd-210tb-bundle',
            'qr676c-hp-4-x-1tb-7200rpm-sata-hard-drive-magazine',
            '00yd794-ibm-2-5-inch-front-hot-swappable-drive-cage',
            '673646-b21-hp-1-2tb-multi-level-cell-mlc-g2-pci-express-iodrive-for-proliant-servers',
            '110-00138-netapp-512gb-flash-cache-pci-express-card',
            'ntf-00001-xbox-360-250gb-internal-hard-drive',
            'stab1000400-seagate-freeagent-goflex-1-tb-2-5-internal-hard-drive-black-sata',
        ];
    }

    /**
     * @return array{loading: string, fetchpriority?: string}
     */
    public function getFeaturedCardImageAttributes(int $index): array
    {
        if ($index === 0) {
            return ['loading' => 'eager', 'fetchpriority' => 'high'];
        }

        if ($index === 1) {
            return ['loading' => 'eager'];
        }

        return ['loading' => 'lazy'];
    }

    /**
     * @return array<string, array{id: string, label: string, category_id: int}>
     */
    public function getTabs(): array
    {
        return [
            'hard-drive' => [
                'id' => 'hard-drive',
                'label' => 'Hard Drive',
                'category_id' => 64,
            ],
            'memory' => [
                'id' => 'memory',
                'label' => 'Memory',
                'category_id' => 35,
            ],
            'motherboards' => [
                'id' => 'motherboards',
                'label' => 'Motherboards',
                'category_id' => 131,
            ],
            'networking' => [
                'id' => 'networking',
                'label' => 'Networking',
                'category_id' => 69,
            ],
            'power-supplies' => [
                'id' => 'power-supplies',
                'label' => 'Power Supplies',
                'category_id' => 179,
            ],
            'printers' => [
                'id' => 'printers',
                'label' => 'Printers',
                'category_id' => 152,
            ],
        ];
    }

    /**
     * @return array{id: string, label: string, category_id: int}
     */
    public function getDefaultTab(): array
    {
        $tabs = $this->getTabs();

        return reset($tabs) ?: [
            'id' => 'hard-drive',
            'label' => 'Hard Drive',
            'category_id' => 64,
        ];
    }

    /**
     * @return int[]
     */
    public function getAllowedCategoryIds(): array
    {
        return array_values(array_map(
            static fn (array $tab): int => (int) $tab['category_id'],
            $this->getTabs()
        ));
    }

    public function isAllowedCategoryId(int $categoryId): bool
    {
        return in_array($categoryId, $this->getAllowedCategoryIds(), true);
    }

    public function getDefaultProductCount(): int
    {
        return self::DEFAULT_PRODUCT_COUNT;
    }

    public function getIdentities(): array
    {
        $identities = [];

        foreach ($this->getAllowedCategoryIds() as $categoryId) {
            $identities[] = Category::CACHE_TAG . '_' . $categoryId;
        }

        return $identities;
    }
}
