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

namespace Bss\OneStepCheckout\Model\Api\Data\Config;

use Magento\Framework\Api\AbstractSimpleObject;
use Bss\OneStepCheckout\Api\Data\Config\CustomCssInterface;

/**
 * Class CustomCssGroupConfig
 *
 * @package Bss\OneStepCheckout\Model\Api\Data\Config
 */
class CustomCssGroupConfig extends AbstractSimpleObject implements CustomCssInterface
{
    /**
     * @inheritDoc
     */
    public function getStepNumberColor()
    {
        return $this->_get(self::STEP_NUMBER_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setStepNumberColor($val = null)
    {
        return $this->setData(self::STEP_NUMBER_COLOR, $val);
    }

    /**
     * @inheritDoc
     */
    public function getStepBgColor()
    {
        return $this->_get(self::STEP_BACKGROUND_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setStepBgColor($val = null)
    {
        return $this->setData(self::STEP_BACKGROUND_COLOR, $val);
    }

    /**
     * @inheritDoc
     */
    public function getCssCode()
    {
        return $this->_get(self::CSS_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCssCode($val = null)
    {
        return $this->setData(self::CSS_CODE, $val);
    }
}
