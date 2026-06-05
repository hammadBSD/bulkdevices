<?php

namespace BSD\Getaquote\Block\Index;


class ModalOverlay extends \Magento\Framework\View\Element\Template {
    /**
     * @var \Magento\Catalog\Model\Product|null
     */
    private $_product;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Catalog\Helper\Data $helper)
    {
        $this->helper = $helper;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }


    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getFormAction()
    {
        return $this->getUrl('getaquote/index/formpost', ['_secure' => true]);

    }

    public function getProduct()
    {
        if (is_null($this->_product)) {
            $this->_product = $this->helper->getProduct();
        }
        return $this->_product;
    }

}