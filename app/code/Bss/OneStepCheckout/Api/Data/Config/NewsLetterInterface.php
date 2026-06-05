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
 * Interface NewsLetterInterface
 *
 * @package Bss\OneStepCheckout\Api\Data\Config
 */
interface NewsLetterInterface
{
    const ENABLE = 'enable_subscribe_newsletter';
    const AUTO_CHECK_NEWSLETTER = 'newsletter_default';

    /**
     * Get enable
     *
     * @return bool
     */
    public function getEnable();

    /**
     * Set enable
     *
     * @param bool $val
     * @return $this
     */
    public function setEnable($val);

    /**
     * Get auto check newsletter sign-up box
     *
     * @return bool
     */
    public function getNewsLetterDefault();

    /**
     * Set auto check newsletter sign-up box value
     *
     * @param bool $val
     * @return $this
     */
    public function setNewsLetterDefault($val);
}
