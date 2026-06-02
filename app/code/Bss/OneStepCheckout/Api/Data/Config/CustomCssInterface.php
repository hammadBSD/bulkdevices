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
 * Interface CustomCssInterface
 *
 * @package Bss\OneStepCheckout\Api\Data\Config
 */
interface CustomCssInterface
{
    const STEP_NUMBER_COLOR = 'step_number_color';
    const STEP_BACKGROUND_COLOR = 'step_bgr_color';
    const CSS_CODE = 'css_code';

    /**
     * Get Checkout Step Number Color
     *
     * @return string
     */
    public function getStepNumberColor();

    /**
     * Set Checkout Step Number Color
     *
     * @param string|null $val
     * @return $this
     */
    public function setStepNumberColor($val = null);

    /**
     * Get Checkout Step Background Color
     *
     * @return string
     */
    public function getStepBgColor();

    /**
     * Set Checkout Step Background Color
     *
     * @param string|null $val
     * @return $this
     */
    public function setStepBgColor($val = null);

    /**
     * Get custom css code
     *
     * @return string
     */
    public function getCssCode();

    /**
     * Set custom css code
     *
     * @param string|null $val
     * @return $this
     */
    public function setCssCode($val = null);
}
