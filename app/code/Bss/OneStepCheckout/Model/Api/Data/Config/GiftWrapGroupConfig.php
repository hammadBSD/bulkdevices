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
use Bss\OneStepCheckout\Api\Data\Config\GiftWrapInterface;

/**
 * Class GiftWrapGroupConfig
 *
 * @package Bss\OneStepCheckout\Model\Api\Data\Config
 */
class GiftWrapGroupConfig extends AbstractSimpleObject implements GiftWrapInterface
{

    /**
     * @inheritDoc
     */
    public function getEnable()
    {
        return (bool) $this->_get(self::ENABLE);
    }

    /**
     * @inheritDoc
     */
    public function setEnable($val)
    {
        return $this->setData(self::ENABLE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->_get(self::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setType($val = null)
    {
        return $this->setData(self::TYPE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getFee()
    {
        return $this->_get(self::FEE);
    }

    /**
     * @inheritDoc
     */
    public function setFee($val = null)
    {
        return $this->setData(self::FEE, $val);
    }
}
