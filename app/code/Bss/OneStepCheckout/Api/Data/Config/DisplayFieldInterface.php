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
 * Interface DisplayFieldInterface
 *
 * @package Bss\OneStepCheckout\Api\Data\Config
 */
interface DisplayFieldInterface
{
    const ENABLE_ORDER_CMT = 'enable_order_comment';
    const ENABLE_DISCOUNT_CODE = 'enable_discount_code';

    /**
     * Get enable order comment config
     *
     * @return bool
     */
    public function getEnableOrderComment();

    /**
     * Set enable order comment value
     *
     * @param bool|null $val
     * @return $this
     */
    public function setEnableOrderComment($val = null);

    /**
     * Get enable discount code config
     *
     * @return bool
     */
    public function getEnableDiscountCode();

    /**
     * Set enable discount code value
     *
     * @param bool|null $val
     * @return $this
     */
    public function setEnableDiscountCode($val = null);
}
