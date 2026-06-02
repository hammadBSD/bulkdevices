<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Api\Data\Config;

/**
 * Interface GiftMessageInterface
 *
 * @package Bss\OneStepCheckout\Api\Data\Config
 */
interface GiftMessageInterface
{
    const ENABLE = 'enable_gift_message';

    /**
     * Get enable gift message
     *
     * @return bool
     */
    public function getEnable();

    /**
     * Set enable
     *
     * @param string $val
     * @return $this
     */
    public function setEnable($val);
}
