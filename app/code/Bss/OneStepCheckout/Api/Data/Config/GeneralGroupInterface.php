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
 * Interface GeneralGroupInterface
 *
 * @package Bss\OneStepCheckout\Api\Data\Config
 */
interface GeneralGroupInterface
{
    const ENABLE = 'enable';
    const TITLE = 'title';
    const ROUTER_NAME = 'router_name';
    const CREATE_NEW = 'create_new';

    /**
     * Get enable config
     *
     * @return bool
     */
    public function getEnable();

    /**
     * Set enable config
     *
     * @param bool|null $value
     * @return $this
     */
    public function setEnable($value = null);

    /**
     * Get Title config
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title config
     *
     * @param string|null $value
     * @return $this
     */
    public function setTitle($value = null);

    /**
     * Get router name config
     *
     * @return string
     */
    public function getRouterName();

    /**
     * Set Router Name config
     *
     * @param string|null $value
     * @return $this
     */
    public function setRouterName($value = null);

    /**
     * Get create config
     *
     * @return bool
     */
    public function getCreateNew();

    /**
     * Set Create New config
     *
     * @param bool|null $value
     * @return $this
     */
    public function setCreateNew($value = null);
}
