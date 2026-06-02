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
 * Interface AutoCompleteInterface
 *
 * @package Bss\OneStepCheckout\Api\Data\Config
 */
interface AutoCompleteInterface
{
    const ENABLE = 'enable_auto_complete';
    const GOOGLE_API_KEY = 'google_api_key';
    const ALLOW_SPECIFIC = 'allowspecific';
    const SPECIFIC_COUNTRIES = 'specificcountry';

    /**
     * Get enable config
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getEnable();

    /**
     * Set enable
     *
     * @param bool $val
     * @return $this
     */
    public function setEnable($val);

    /**
     * Get google api key
     *
     * @return string
     */
    public function getGoogleApiKey();

    /**
     * Set google api key
     *
     * @param string|null $val
     * @return $this
     */
    public function setGoogleApiKey($val = null);

    /**
     * Get allow specific
     *
     * @return string
     */
    public function getAllowSpecific();

    /**
     * Set allow specific
     *
     * @param string $val
     * @return $this
     */
    public function setAllowSpecific($val);

    /**
     * Get specific countries
     *
     * @return mixed
     */
    public function getSpecificCountries();

    /**
     * Set specific countries
     *
     * @param array $val
     * @return $this
     */
    public function setSpecificCountries($val);
}
