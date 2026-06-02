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
namespace Bss\OneStepCheckout\Model\Api\Data\Config;

use Bss\OneStepCheckout\Api\Data\Config\GeneralGroupInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class GeneralGroupConfig
 *
 * General group module config
 *
 * @package Bss\OneStepCheckout\Model\Api\Data\Config
 */
class GeneralGroupConfig extends AbstractSimpleObject implements GeneralGroupInterface
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
    public function setEnable($value = null)
    {
        return $this->setData(self::ENABLE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle($value = null)
    {
        return $this->setData(self::TITLE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getRouterName()
    {
        return $this->_get(self::ROUTER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setRouterName($value = null)
    {
        return $this->setData(self::ROUTER_NAME, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCreateNew()
    {
        return $this->_get(self::CREATE_NEW);
    }

    /**
     * @inheritDoc
     */
    public function setCreateNew($value = null)
    {
        return $this->setData(self::CREATE_NEW, $value);
    }
}
