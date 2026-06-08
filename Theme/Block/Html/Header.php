<?php
declare(strict_types=1);

namespace BSD\Theme\Block\Html;

use Magento\Framework\View\Element\Template;

class Header extends Template
{
    public function getPhone(): string
    {
        return (string) $this->_scopeConfig->getValue(
            'general/store_information/phone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?: '(800) 577-4333';
    }
}
