<?php

namespace BSD\Getaquote\Block\Index;


class Index extends \Magento\Framework\View\Element\Template {

   /* public function __construct(\Magento\Catalog\Block\Product\Context $context, array $data = []) {

        parent::__construct($context, $data);

    }*/
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Catalog\Helper\Data $helper)
    {

        parent::__construct($context);
    }


    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getFormAction()
    {
        return $this->getUrl('getaquote/index', ['_secure' => true]);

    }
    public function getProduct()
    {
        if (is_null($this->_product)) {
            $this->_product = $this->helper->getProduct();
        }
        return $this->_product;
    }
}