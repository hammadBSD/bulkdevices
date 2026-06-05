<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_OneStepCheckout
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Model;

use Bss\OneStepCheckout\Api\Data\ConfigDataInterfaceFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class GetConfig
 *
 * @api
 * @package Bss\OneStepCheckout\Model
 */
class GetConfig implements \Bss\OneStepCheckout\Api\ConfigInterface
{
    const XML_BSS_OSC_GENERAL_CONFIG = 'onestepcheckout/general';
    const XML_BSS_OSC_DISPLAY_FIELD_CONFIG = 'onestepcheckout/display_field';
    const XML_BSS_OSC_AUTO_COMPLETE_CONFIG = 'onestepcheckout/auto_complete';
    const XML_BSS_OSC_NEWSLETTER_CONFIG = 'onestepcheckout/newsletter';
    const XML_BSS_OSC_ORDER_DELIVERY_DATE_CONFIG = 'onestepcheckout/order_delivery_date';
    const XML_BSS_OSC_GIFT_WRAP_CONFIG = 'onestepcheckout/gift_wrap';
    const XML_BSS_OSC_CUSTOM_CSS_CONFIG = 'onestepcheckout/custom_css';
    const XML_BSS_OSC_GIFT_MESSAGE_CONFIG = 'onestepcheckout/gift_message';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigDataInterfaceFactory
     */
    private $configData;

    /**
     * GetConfig constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param ConfigDataInterfaceFactory $configData
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ConfigDataInterfaceFactory $configData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configData = $configData;
    }

    /**
     * @inheritDoc
     */
    public function getAllConfig($storeId = null)
    {
        $configData = $this->configData->create();

        $configData->setGeneral($this->getGroupConfig($storeId, self::XML_BSS_OSC_GENERAL_CONFIG));
        $configData->setDisplayField($this->getGroupConfig($storeId, self::XML_BSS_OSC_DISPLAY_FIELD_CONFIG));
        $configData->setAutoComplete($this->getGroupConfig($storeId, self::XML_BSS_OSC_AUTO_COMPLETE_CONFIG));
        $configData->setNewsLetter($this->getGroupConfig($storeId, self::XML_BSS_OSC_NEWSLETTER_CONFIG));
        $configData->setOrderDeliveryDate(
            $this->getGroupConfig($storeId, self::XML_BSS_OSC_ORDER_DELIVERY_DATE_CONFIG)
        );
        $configData->setGiftWrap($this->getGroupConfig($storeId, self::XML_BSS_OSC_GIFT_WRAP_CONFIG));
        $configData->setCustomCss($this->getGroupConfig($storeId, self::XML_BSS_OSC_CUSTOM_CSS_CONFIG));
        $configData->setGiftMessage($this->getGroupConfig($storeId, self::XML_BSS_OSC_GIFT_MESSAGE_CONFIG));
        return $configData;
    }

    /**
     * Get config
     *
     * @param int $storeId
     * @param string $path
     * @return array
     */
    protected function getGroupConfig($storeId, $path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
