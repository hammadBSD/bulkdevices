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

use Bss\OneStepCheckout\Api\Data\Config\AutoCompleteInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class AutoCompleteGroupConfig
 *
 * @package Bss\OneStepCheckout\Model\Api\Data\Config
 */
class AutoCompleteGroupConfig extends AbstractSimpleObject implements AutoCompleteInterface
{
    /**
     * @inheritDoc
     */
    public function getEnable()
    {
        return $this->_get(self::ENABLE);
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
    public function getGoogleApiKey()
    {
        return $this->_get(self::GOOGLE_API_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setGoogleApiKey($val = null)
    {
        return $this->setData(self::GOOGLE_API_KEY, $val);
    }

    /**
     * @inheritDoc
     */
    public function getAllowSpecific()
    {
        return $this->_get(self::ALLOW_SPECIFIC);
    }

    /**
     * @inheritDoc
     */
    public function setAllowSpecific($val)
    {
        return $this->setData(self::ALLOW_SPECIFIC, $val);
    }

    /**
     * @inheritDoc
     */
    public function getSpecificCountries()
    {
        return $this->_get(self::SPECIFIC_COUNTRIES);
    }

    /**
     * @inheritDoc
     */
    public function setSpecificCountries($val)
    {
        return $this->setData(self::SPECIFIC_COUNTRIES, $val);
    }
}
