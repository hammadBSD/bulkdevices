<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel;

use Magento\Catalog\Model\Category;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class PromotionalProducts implements ArgumentInterface, IdentityInterface
{
    private const DEFAULT_PRODUCT_COUNT = 8;

    /**
     * @return array<string, array{id: string, label: string, category_id: int}>
     */
    public function getTabs(): array
    {
        return [
            'all' => [
                'id' => 'all',
                'label' => 'All',
                'category_id' => 9969,
            ],
            'hp' => [
                'id' => 'hp',
                'label' => 'HP',
                'category_id' => 9970,
            ],
            'cisco' => [
                'id' => 'cisco',
                'label' => 'Cisco',
                'category_id' => 9971,
            ],
            'seagate' => [
                'id' => 'seagate',
                'label' => 'Seagate',
                'category_id' => 9973,
            ],
            'dell' => [
                'id' => 'dell',
                'label' => 'Dell',
                'category_id' => 9974,
            ],
            'wd' => [
                'id' => 'wd',
                'label' => 'Western Digital',
                'category_id' => 9972,
            ],
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
