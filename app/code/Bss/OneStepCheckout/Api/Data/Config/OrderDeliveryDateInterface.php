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
namespace Bss\OneStepCheckout\Api\Data\Config;

/**
 * Interface OrderDeliveryDateInterface
 *
 * @package Bss\OneStepCheckout\Api\Data\Config
 */
interface OrderDeliveryDateInterface
{
    const ENABLE_DELIVERY_DATE = 'enable_delivery_date';
    const ENABLE_DELIVERY_COMMENT = 'enable_delivery_comment';

    /**
     * Get enable delivery date
     *
     * @return bool
     */
    public function getEnableDeliveryDate();

    /**
     * Set enable delivery date value
     *
     * @param string $val
     * @return $this
     */
    public function setEnableDeliveryDate($val);

    /**
     * Get enable delivery comment
     *
     * @return bool
     */
    public function getEnableDeliveryComment();

    /**
     * Set enable delivery comment value
     *
     * @param string $val
     * @return $this
     */
    public function setEnableDeliveryComment($val);
}
