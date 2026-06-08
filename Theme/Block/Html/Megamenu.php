<?php
declare(strict_types=1);

namespace BSD\Theme\Block\Html;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Megamenu extends Template
{
    /** @var array<int, array<string, mixed>>|null */
    private ?array $menuTree = null;

    public function __construct(
        Template\Context $context,
        private readonly CollectionFactory $categoryCollectionFactory,
        private readonly StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Level-2 categories with nested level-3 / level-4 children for mega panels.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMenuTree(): array
    {
        if ($this->menuTree !== null) {
            return $this->menuTree;
        }

        $storeId = (int) $this->storeManager->getStore()->getId();
        $rootId = (int) $this->storeManager->getStore()->getRootCategoryId();

        $level2 = $this->categoryCollectionFactory->create();
        $level2->setStoreId($storeId);
        $level2->addAttributeToSelect(['name', 'url_key']);
        $level2->addFieldToFilter('level', 2);
        $level2->addFieldToFilter('path', ['like' => "1/{$rootId}/%"]);
        $level2->addFieldToFilter('is_active', 1);
        $level2->addFieldToFilter('include_in_menu', 1);
        $level2->addUrlRewriteToResult();
        $level2->setOrder('position', 'ASC');

        $tree = [];
        foreach ($level2 as $category) {
            $columns = [];
            foreach ($this->getChildCategories($category, $storeId) as $columnCat) {
                $links = [];
                foreach ($this->getChildCategories($columnCat, $storeId) as $linkCat) {
                    $links[] = [
                        'name' => $linkCat->getName(),
                        'url' => $linkCat->getUrl(),
                    ];
                }
                $columns[] = [
                    'name' => $columnCat->getName(),
                    'url' => $columnCat->getUrl(),
                    'links' => $links,
                ];
            }
            $tree[] = [
                'id' => (int) $category->getId(),
                'name' => $category->getName(),
                'url' => $category->getUrl(),
                'columns' => $columns,
            ];
        }

        $this->menuTree = $tree;
        return $this->menuTree;
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function getQuickLinks(): array
    {
        $links = [
            ['label' => 'Hard Drive', 'path' => 'storage-devices'],
            ['label' => 'Memory', 'path' => 'memory'],
            ['label' => 'CPU', 'path' => 'cpus'],
            ['label' => 'Power Supply', 'path' => 'power'],
            ['label' => 'Motherboards', 'path' => 'motherboards'],
            ['label' => 'Switches', 'path' => 'networking-devices/switches'],
        ];
        $out = [];
        foreach ($links as $link) {
            $out[] = [
                'label' => $link['label'],
                'url' => $this->getUrl($link['path']),
            ];
        }
        return $out;
    }

    public function getPromotionUrl(): string
    {
        return $this->getUrl('promotional-products');
    }

    /**
     * @return Category[]
     */
    private function getChildCategories(Category $parent, int $storeId): array
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['name', 'url_key']);
        $collection->addFieldToFilter('parent_id', (int) $parent->getId());
        $collection->addFieldToFilter('is_active', 1);
        $collection->addFieldToFilter('include_in_menu', 1);
        $collection->addUrlRewriteToResult();
        $collection->setOrder('position', 'ASC');

        $items = [];
        foreach ($collection as $category) {
            $items[] = $category;
        }
        return $items;
    }
}
