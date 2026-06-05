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
 * @category   BSS
 * @package    Bss_OneStepCheckout
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Model\Checkout\Orderlines;

use Klarna\Core\Api\BuilderInterface;
use Klarna\Core\Model\Checkout\Orderline\AbstractLine;

class GiftWrapper extends AbstractLine
{
    /**
     * Checkout item type
     */
    const ITEM_TYPE_GIFT_WRAP = 'physical';

    /**
     * {@inheritdoc}
     */
    public function collect(BuilderInterface $checkout)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $checkout->getObject();
        $totals = $quote->getTotals();

        if (!is_array($totals) || !isset($totals['osc_gift_wrap'])) {
            return $this;
        }

        $baseGiftWrap = $quote->getBaseOscGiftWrap();
        $giftWrap = $quote->getOscGiftWrap();

        if (!isset($totals['osc_gift_wrap'])) {
            $totals['osc_gift_wrap'] = $this->dataObjectFactory->create([
                'data' => [
                    'value' => $giftWrap,
                    'title' => 'Osc Gift Wrap',
                    'code'  => 'oscgiftwrap',
                ]
            ]);
        }
        if (isset($totals['osc_gift_wrap'])) {
            $total = $totals['osc_gift_wrap'];
            $value = $this->helper->toApiFloat($total->getValue());
            $checkout->addData([
                'gift_wrap_unit_price' => $value,
                'gift_wrap_tax_rate' => 0,
                'gift_wrap_total_amount' => $value,
                'gift_wrap_tax_amount' => 0,
                'gift_wrap_title' => $total->getTitle(),
                'gift_wrap_reference' => $total->getCode()
            ]);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(BuilderInterface $checkout)
    {
        if ($checkout->getGiftWrapTotalAmount()) {
            $checkout->addOrderLine([
                'type'             => self::ITEM_TYPE_GIFT_WRAP,
                'reference'        => $checkout->getGiftWrapReference(),
                'name'             => $checkout->getGiftWrapTitle(),
                'quantity'         => 1,
                'unit_price'       => $checkout->getGiftWrapUnitPrice(),
                'tax_rate'         => $checkout->getGiftWrapTaxRate(),
                'total_amount'     => $checkout->getGiftWrapTotalAmount(),
                'total_tax_amount' => $checkout->getGiftWrapTaxAmount(),
            ]);
        }
        return $this;
    }
}
