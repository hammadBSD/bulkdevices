<?php

declare(strict_types=1);

namespace BSD\Storefront\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\LayeredNavigation\Block\Navigation\FilterRenderer as MagentoFilterRenderer;

class FilterRenderer extends MagentoFilterRenderer
{
    public function render(FilterInterface $filter)
    {
        $this->setTemplate('Magento_LayeredNavigation::layer/filter.phtml');
        $this->assign('filter', $filter);
        $this->assign('filterItems', $filter->getItems());
        $html = $this->_toHtml();
        $this->assign('filterItems', []);
        $this->assign('filter', null);

        return $html;
    }
}
