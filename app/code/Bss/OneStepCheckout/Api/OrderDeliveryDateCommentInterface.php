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
namespace Bss\OneStepCheckout\Api;

/**
 * Interface OrderDeliveryDateCommentInterface
 *
 * @api
 * @package Bss\OneStepCheckout\Api
 */
interface OrderDeliveryDateCommentInterface
{
    /**
     * Add info to payment extension by guest
     *
     * @param int $cartId
     * @param string $shippingArrivalDate
     * @param string $shippingArrivalComments
     * @return \Bss\OneStepCheckout\Model\Api\Data\ResponseSimpleObject
     */
    public function guestAddToPaymentExtension($cartId, $shippingArrivalDate, string $shippingArrivalComments);

    /**
     * Add info to payment extension by customer
     *
     * @param int $quoteId
     * @param string $shippingArrivalDate
     * @param string $shippingArrivalComments
     * @return \Bss\OneStepCheckout\Model\Api\Data\ResponseSimpleObject
     */
    public function addToPaymentExtension($quoteId, $shippingArrivalDate, string $shippingArrivalComments);

    /**
     * Get order delivery date info from quote
     *
     * @param int $id
     * @return \Bss\OneStepCheckout\Model\Api\Data\ResponseSimpleObject
     */
    public function getFromQuote($id);
}
