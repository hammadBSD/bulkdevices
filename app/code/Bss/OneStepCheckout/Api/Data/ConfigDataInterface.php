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
 * Interface ConfigDataInterface
 *
 * @package Bss\OneStepCheckout\Api\Data
 */
interface ConfigDataInterface
{
    const GENERAL = 'general';
    const DISPLAY_FIELD = 'display_field';
    const NEWSLETTER = 'newsletter';
    const ORDER_DELIVERY_DATE = 'order_delivery_date';
    const GIFT_WRAP = 'gift_wrap';
    const CUSTOM_CSS = 'custom_css';
    const AUTO_COMPLETE = 'auto_complete';
    const GIFT_MESSAGE = 'gift_message';

    /**
     * Get General config section
     *
     * @return \Bss\OneStepCheckout\Api\Data\Config\GeneralGroupInterface
     */
    public function getGeneral();

    /**
     * Set general config section
     *
     * @param \Bss\OneStepCheckout\Api\Data\Config\GeneralGroupInterface $configs
     * @return $this
     */
    public function setGeneral($configs = null);

    /**
     * Get display field config
     *
     * @return \Bss\OneStepCheckout\Api\Data\Config\DisplayFieldInterface
     */
    public function getDisplayField();

    /**
     * Set display field config
     *
     * @param string[] $configs
     * @return $this
     */
    public function setDisplayField(array $configs = null);

    /**
     * Get newsletter config
     *
     * @return \Bss\OneStepCheckout\Api\Data\Config\NewsLetterInterface|null
     */
    public function getNewsLetter();

    /**
     * Set newsletter config
     *
     * @param string[]|null $configs
     * @return $this
     */
    public function setNewsLetter(array $configs = null);

    /**
     * Get order delivery date configs
     *
     * @return \Bss\OneStepCheckout\Api\Data\Config\OrderDeliveryDateInterface
     */
    public function getOrderDeliveryDate();

    /**
     * Set order delivery date config
     *
     * @param string[]|null $configs
     * @return $this
     */
    public function setOrderDeliveryDate(array $configs = null);

    /**
     * Get gift wrap config
     *
     * @return \Bss\OneStepCheckout\Api\Data\Config\GiftWrapInterface
     */
    public function getGiftWrap();

    /**
     * Set gift wrap config
     *
     * @param string[]|null $configs
     * @return $this
     */
    public function setGiftWrap(array $configs = null);

    /**
     * Get custom css configs
     *
     * @return \Bss\OneStepCheckout\Api\Data\Config\CustomCssInterface|null
     */
    public function getCustomCss();

    /**
     * Set custom css config
     *
     * @param string[]|null $configs
     * @return $this
     */
    public function setCustomCss(array $configs = null);

    /**
     * Get auto compete google configs
     *
     * @return \Bss\OneStepCheckout\Api\Data\Config\AutoCompleteInterface|null
     */
    public function getAutoComplete();

    /**
     * Set auto complete google config
     *
     * @param string[]|null $configs
     * @return $this
     */
    public function setAutoComplete(array $configs = null);

    /**
     * Get gift message configs
     *
     * @return \Bss\OneStepCheckout\Api\Data\Config\GiftMessageInterface|null
     */
    public function getGiftMessage();

    /**
     * Set gift message config
     *
     * @param string[]|null $configs
     * @return $this
     */
    public function setGiftMessage(array $configs = null);
}
