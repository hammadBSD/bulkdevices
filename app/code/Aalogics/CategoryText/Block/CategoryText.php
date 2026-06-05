<?php
namespace Aalogics\CategoryText\Block;

use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;

class CategoryText extends Template
{
    protected $filterProvider;
    protected $registry;

    public function __construct(
        Template\Context $context,
        FilterProvider $filterProvider,
        Registry $registry,
        array $data = []
    ) {
        $this->filterProvider = $filterProvider;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getCategoryTextBottom()
    {
        $category = $this->getCurrentCategory();
        if ($category) {
            $categoryTextBottom = $category->getData('category_text_bottom');
            if (!empty($categoryTextBottom)) {
                return $this->filterProvider->getPageFilter()->filter($categoryTextBottom);
            }
        }
        return '';
    }

    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    public function getFilterProvider()
    {
        return $this->filterProvider;
    }
}