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
namespace Bss\OneStepCheckout\Block\Sales\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\DataObjectFactory;

/**
 * Class GiftWrap
 * @package Bss\OneStepCheckout\Block\Sales\Order
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
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initTotals()
    {
        $source = $this->getParentBlock()->getSource();
        $giftWrap = $source->getOscGiftWrap();
        $order = $this->getParentBlock()->getOrder();

        if ($this->giftCardConfig->isGiftWrapEnable($order->getStoreId()) ||
            ($giftWrap !== null && $source->getOscGiftWrapType() !== null)) {
            $total = $this->dataObjectFactory->create();
            $total->setCode('osc_gift_wrap')
                ->setValue($giftWrap)
                ->setLabel(__('Gift Wrap'));
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }
        return $this;
    }
}
