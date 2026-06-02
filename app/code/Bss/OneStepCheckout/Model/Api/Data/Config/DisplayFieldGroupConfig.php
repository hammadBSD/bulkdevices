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
use Bss\OneStepCheckout\Api\Data\Config\DisplayFieldInterface;

/**
 * Class DisplayFieldGroupConfig
 *
 * @package Bss\OneStepCheckout\Model\Api\Data\Config
 */
class DisplayFieldGroupConfig extends AbstractSimpleObject implements DisplayFieldInterface
{

    /**
     * @inheritDoc
     */
    public function getEnableOrderComment()
    {
        return $this->_get(self::ENABLE_ORDER_CMT);
    }

    /**
     * @inheritDoc
     */
    public function setEnableOrderComment($val = null)
    {
        return $this->setData(self::ENABLE_ORDER_CMT, $val);
    }

    /**
     * @inheritDoc
     */
    public function getEnableDiscountCode()
    {
        return $this->_get(self::ENABLE_DISCOUNT_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setEnableDiscountCode($val = null)
    {
        return $this->setData(self::ENABLE_DISCOUNT_CODE, $val);
    }
}
