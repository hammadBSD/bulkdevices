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
namespace Bss\OneStepCheckout\Api\Data;

/**
 * Interface ResponseSimpleObjectInterface
 *
 * @package Bss\OneStepCheckout\Api\Data
 */
interface ResponseSimpleObjectInterface
{
    const STATUS = 'status';
    const MESSAGE = 'message';
    const DATA = 'data';

    /**
     * Get status
     *
     * @return bool|int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string|int|bool $value
     * @return $this
     */
    public function setStatus($value);

    /**
     * Get response data
     *
     * @api
     * @return mixed[]
     */
    public function getResponseData();

    /**
     * Set response data
     *
     * @api
     * @param mixed[] $data
     * @return $this
     */
    public function setResponseData($data);

    /**
     * Get response message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set response message
     *
     * @param string $msg
     * @return $this
     */
    public function setMessage($msg);
}
