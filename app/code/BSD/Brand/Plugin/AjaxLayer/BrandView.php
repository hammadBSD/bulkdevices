<?php

declare(strict_types=1);

namespace BSD\Brand\Plugin\AjaxLayer;

use BSD\Brand\Controller\View\Index as BrandViewController;
use Mageplaza\AjaxLayer\Helper\Data as LayerData;

class BrandView
{
    public function __construct(
        private readonly LayerData $moduleHelper,
    ) {
    }

    /**
     * @param BrandViewController $action
     * @param mixed $page
     * @return mixed
     */
    public function afterExecute(BrandViewController $action, $page)
    {
        if ($this->moduleHelper->ajaxEnabled() && $action->getRequest()->isAjax()) {
            $navigation = $page->getLayout()->getBlock('catalog.leftnav');
            $products = $page->getLayout()->getBlock('category.products');
            $result = [
                'products' => $products ? $products->toHtml() : '',
                'navigation' => $navigation ? $navigation->toHtml() : '',
            ];
            if ($this->moduleHelper->getConfigValue('mpquickview/general/enabled')) {
                $quickView = $page->getLayout()->getBlock('mpquickview.quickview');
                $result['quickview'] = $quickView ? $quickView->toHtml() : '';
            }
            $action->getResponse()->representJson(LayerData::jsonEncode($result));
            return null;
        }

        return $page;
    }
}
