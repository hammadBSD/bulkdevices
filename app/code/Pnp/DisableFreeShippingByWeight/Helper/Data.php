<?php
namespace Pnp\DisableFreeShippingByWeight\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLE = 'carriers/freeshipping/disable_under_weight_enabled';
    const XML_PATH_MIN_WEIGHT = 'carriers/freeshipping/min_weight';

    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMinWeight($storeId = null)
    {
        return (float) $this->scopeConfig->getValue(self::XML_PATH_MIN_WEIGHT, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
