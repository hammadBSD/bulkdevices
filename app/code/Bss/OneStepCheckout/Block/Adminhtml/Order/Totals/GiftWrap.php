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
namespace Bss\OneStepCheckout\Block\Adminhtml\Order\Totals;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\DataObjectFactory;

/**
 * Class GiftWrap
 * @package Bss\OneStepCheckout\Block\Adminhtml\Order\Totals
 */
class GiftWrap extends Template
{
    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Bss\OneStepCheckout\Helper\Config
     */
    protected $giftCardConfig;

    /**
     * GiftWrap constructor.
     * @param Context $context
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataObjectFactory $dataObjectFactory,
        \Bss\OneStepCheckout\Helper\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->giftCardConfig = $config;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initTotals()
    {
        $source = $this->getParentBlock()->getSource();
        $orderStoreId = $this->getOrder()->getStoreId();
        $baseGiftWrap = $source->getBaseOscGiftWrap();
        $giftWrap = $source->getOscGiftWrap();

        if ($this->giftCardConfig->isGiftWrapEnable($orderStoreId) ||
            ($giftWrap !== null && $this->getOrder()->getOscGiftWrapType() !== null)) {
            $total = $this->dataObjectFactory->create();
            $total->setCode('osc_gift_wrap')
                ->setBaseValue($baseGiftWrap)
                ->setValue($giftWrap)
                ->setLabel(__('Gift Wrap'));
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }
        return $this;
    }
}
