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
namespace Bss\OneStepCheckout\Model\Api\Data;

use Bss\OneStepCheckout\Api\Data\ResponseSimpleObjectInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class ResponseSimpleObject
 *
 * @package Bss\OneStepCheckout\Model
 */
class ResponseSimpleObject extends AbstractSimpleObject implements ResponseSimpleObjectInterface
{

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * @inheritDoc
     */
    public function getResponseData()
    {
        return $this->_get(self::DATA);
    }

    /**
     * @inheritDoc
     */
    public function setResponseData($data)
    {
        return $this->setData(self::DATA, ['responseData' => [$data]]);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->_get(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage($msg)
    {
        return $this->setData(self::MESSAGE, $msg);
    }
}
