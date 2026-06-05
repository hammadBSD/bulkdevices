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
 * Interface GiftWrapInterface
 *
 * @package Bss\OneStepCheckout\Api\Data\Config
 */
interface GiftWrapInterface
{
    const ENABLE = 'enable';
    const TYPE = 'type';
    const FEE = 'fee';

    /**
     * Get enable
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

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Set type
     *
     * @param string $val
     * @return $this
     */
    public function setType($val = null);

    /**
     * Get fee
     *
     * @return string
     */
    public function getFee();

    /**
     * Set fee
     *
     * @param string $val
     * @return $this
     */
    public function setFee($val = null);
}
