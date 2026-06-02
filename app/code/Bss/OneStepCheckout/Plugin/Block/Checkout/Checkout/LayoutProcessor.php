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

namespace Bss\OneStepCheckout\Plugin\Block\Checkout\Checkout;

use Bss\OneStepCheckout\Helper\Config;
use Bss\OneStepCheckout\Helper\Data;

/**
 * Class LayoutProcessor
 *
 * @package Bss\OneStepCheckout\Plugin\Block\Checkout\Checkout
 */
class LayoutProcessor
{
    /**
     * One step checkout helper
     *
     * @var Config
     */
    protected $configHelper;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * LayoutProcessor constructor.
     * @param Config $configHelper
     * @param Data $dataHelper
     */
    public function __construct(
        Config $configHelper,
        Data $dataHelper
    ) {
        $this->configHelper = $configHelper;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        if (!$this->configHelper->isEnabled()) {
            return $jsLayout;
        }
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['afterMethods']['children']['billing-address-form'])) {
            $component = $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['afterMethods']['children']
            ['billing-address-form'];
            unset(
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            );
            $component['component'] = 'Bss_OneStepCheckout/js/view/billing-address';
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list']['children']
            ['billing-address-form-shared'] = $component;
        }

        $jsLayout = $this->orderDeliveryDate($jsLayout);

        if (!$this->configHelper->isDisplayField('enable_order_comment')) {
            unset(
                $jsLayout['components']['checkout']['children']['sidebar']['children']
                ['bss_osc_order_comment']
            );
        }

        $jsLayout = $this->newsletter($jsLayout);

        if (!$this->configHelper->isGiftMessageField('enable_gift_message') ||
            !$this->configHelper->isMessagesAllowed()) {
            unset(
                $jsLayout['components']['checkout']['children']['sidebar']['children']
                ['giftmessage']
            );
        }

        if ($this->configHelper->getGiftWrapFee() === false) {
            unset(
                $jsLayout['components']['checkout']['children']['sidebar']['children']['gift_wrap']
            );
        }

        $jsLayout = $this->discountCode($jsLayout);

        $jsLayout = $this->addPlaceholdersAndHideLabels($jsLayout);

        $jsLayout = $this->removeComponent($jsLayout);
        return $jsLayout;
    }

    /**
     * Add placeholders and hide labels for address fields
     *
     * @param array $jsLayout
     * @return array
     */
    protected function addPlaceholdersAndHideLabels($jsLayout)
    {
        // Shipping address fields
        $shippingFieldset = 'components/checkout/children/steps/children/shipping-step/children/shippingAddress/children/shipping-address-fieldset/children';
        
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {
            
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
            
            $fieldMapping = [
                'firstname' => 'First Name',
                'lastname' => 'Last Name',
                'company' => 'Company',
                'street' => 'Street Address',
                'city' => 'City',
                'region_id' => 'State/Province',
                'postcode' => 'Zip/Postal Code',
                'country_id' => 'Country',
                'telephone' => 'Phone Number'
            ];
            
            foreach ($fieldMapping as $fieldName => $labelText) {
                if (isset($fields[$fieldName])) {
                    // Special handling for street address (multi-line field)
                    if ($fieldName === 'street' && isset($fields[$fieldName]['children'])) {
                        // Hide main street label
                        $fields[$fieldName]['config']['labelVisible'] = false;
                        
                        // Add placeholders to each street line
                        $streetPlaceholder = 'Street Address *';
                        $streetPlaceholder2 = 'Apartment, suite, etc. (optional)';
                        
                        // Handle street[0] - first line (required)
                        if (isset($fields[$fieldName]['children'][0])) {
                            $fields[$fieldName]['children'][0]['config']['labelVisible'] = false;
                            $fields[$fieldName]['children'][0]['config']['placeholder'] = $streetPlaceholder;
                        }
                        
                        // Handle street[1] - second line (optional)
                        if (isset($fields[$fieldName]['children'][1])) {
                            $fields[$fieldName]['children'][1]['config']['labelVisible'] = false;
                            $fields[$fieldName]['children'][1]['config']['placeholder'] = $streetPlaceholder2;
                        }
                        
                        // Handle any additional street lines
                        if (isset($fields[$fieldName]['children']) && is_array($fields[$fieldName]['children'])) {
                            foreach ($fields[$fieldName]['children'] as $index => &$streetChild) {
                                if ($index >= 2) {
                                    $streetChild['config']['labelVisible'] = false;
                                    $streetChild['config']['placeholder'] = $streetPlaceholder2;
                                }
                            }
                        }
                    } else {
                        // Hide label for other fields
                        $fields[$fieldName]['config']['labelVisible'] = false;
                        
                        // Add placeholder
                        $placeholderText = $labelText;
                        // Check if field is required
                        $isRequired = false;
                        if (isset($fields[$fieldName]['validation']['required-entry']) && 
                            $fields[$fieldName]['validation']['required-entry'] === true) {
                            $isRequired = true;
                            $placeholderText .= ' *';
                        } elseif (isset($fields[$fieldName]['required']) && 
                                  $fields[$fieldName]['required'] === true) {
                            $isRequired = true;
                            $placeholderText .= ' *';
                        }
                        
                        // Set placeholder in config
                        $fields[$fieldName]['config']['placeholder'] = $placeholderText;
                        
                        // For input elements, also set in the element's config
                        if (isset($fields[$fieldName]['config']['elementTmpl'])) {
                            // Input field - placeholder will be applied via attr binding
                            if (!isset($fields[$fieldName]['config']['additionalClasses'])) {
                                $fields[$fieldName]['config']['additionalClasses'] = '';
                            }
                        }
                        
                        // For select fields (region_id, country_id), update caption
                        if ($fieldName === 'region_id' || $fieldName === 'country_id') {
                            if (isset($fields[$fieldName]['config']['caption'])) {
                                $fields[$fieldName]['config']['caption'] = $placeholderText;
                            } else {
                                $fields[$fieldName]['config']['caption'] = $placeholderText;
                            }
                        }
                    }
                }
            }
        }
        
        // Billing address fields (if they exist separately)
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list']['children'])) {
            
            $paymentList = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['payments-list']['children'];
            
            foreach ($paymentList as $paymentCode => &$paymentMethod) {
                if (isset($paymentMethod['children']['form-fields']['children'])) {
                    $billingFields = &$paymentMethod['children']['form-fields']['children'];
                    
                    $fieldMapping = [
                        'firstname' => 'First Name',
                        'lastname' => 'Last Name',
                        'company' => 'Company',
                        'street' => 'Street Address',
                        'city' => 'City',
                        'region_id' => 'State/Province',
                        'postcode' => 'Zip/Postal Code',
                        'country_id' => 'Country',
                        'telephone' => 'Phone Number'
                    ];
                    
                    foreach ($fieldMapping as $fieldName => $labelText) {
                        if (isset($billingFields[$fieldName])) {
                            // Special handling for street address (multi-line field)
                            if ($fieldName === 'street' && isset($billingFields[$fieldName]['children'])) {
                                // Hide main street label
                                $billingFields[$fieldName]['config']['labelVisible'] = false;
                                
                                // Add placeholders to each street line
                                $streetPlaceholder = 'Street Address *';
                                $streetPlaceholder2 = 'Apartment, suite, etc. (optional)';
                                
                                // Handle street[0] - first line (required)
                                if (isset($billingFields[$fieldName]['children'][0])) {
                                    $billingFields[$fieldName]['children'][0]['config']['labelVisible'] = false;
                                    $billingFields[$fieldName]['children'][0]['config']['placeholder'] = $streetPlaceholder;
                                }
                                
                                // Handle street[1] - second line (optional)
                                if (isset($billingFields[$fieldName]['children'][1])) {
                                    $billingFields[$fieldName]['children'][1]['config']['labelVisible'] = false;
                                    $billingFields[$fieldName]['children'][1]['config']['placeholder'] = $streetPlaceholder2;
                                }
                                
                                // Handle any additional street lines
                                if (isset($billingFields[$fieldName]['children']) && is_array($billingFields[$fieldName]['children'])) {
                                    foreach ($billingFields[$fieldName]['children'] as $index => &$streetChild) {
                                        if ($index >= 2) {
                                            $streetChild['config']['labelVisible'] = false;
                                            $streetChild['config']['placeholder'] = $streetPlaceholder2;
                                        }
                                    }
                                }
                            } else {
                                // Hide label for other fields
                                $billingFields[$fieldName]['config']['labelVisible'] = false;
                                
                                // Add placeholder
                                $placeholderText = $labelText;
                                // Check if field is required
                                if (isset($billingFields[$fieldName]['validation']['required-entry']) && 
                                    $billingFields[$fieldName]['validation']['required-entry'] === true) {
                                    $placeholderText .= ' *';
                                } elseif (isset($billingFields[$fieldName]['required']) && 
                                          $billingFields[$fieldName]['required'] === true) {
                                    $placeholderText .= ' *';
                                }
                                
                                // Set placeholder in config
                                $billingFields[$fieldName]['config']['placeholder'] = $placeholderText;
                                
                                // For select fields, update caption
                                if ($fieldName === 'region_id' || $fieldName === 'country_id') {
                                    if (isset($billingFields[$fieldName]['config']['caption'])) {
                                        $billingFields[$fieldName]['config']['caption'] = $placeholderText;
                                    } else {
                                        $billingFields[$fieldName]['config']['caption'] = $placeholderText;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $jsLayout;
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    protected function orderDeliveryDate($jsLayout)
    {
        if (!$this->configHelper->isOrderDeliveryField('enable_delivery_date') ||
            $this->dataHelper->isModuleInstall('Bss_OrderDeliveryDate')
        ) {
            unset(
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['before-shipping-method-form']['children']
                ['bss_osc_delivery_date']
            );
        }

        if (!$this->configHelper->isOrderDeliveryField('enable_delivery_comment') ||
            $this->dataHelper->isModuleInstall('Bss_OrderDeliveryDate')
        ) {
            unset(
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['before-shipping-method-form']['children']
                ['bss_osc_delivery_comment']
            );
        }
        return $jsLayout;
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    protected function removeComponent($jsLayout)
    {
        unset(
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['afterMethods']['children']['discount']
        );

        unset(
            $jsLayout['components']['checkout']['children']['sidebar']['children']['shipping-information']
        );

        unset(
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list']['children']['before-place-order']
            ['children']['agreements']
        );

        unset($jsLayout['components']['checkout']['children']['progressBar']);
        return $jsLayout;
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    protected function newsletter($jsLayout)
    {
        if (!$this->configHelper->isNewletterField('enable_subscribe_newsletter')) {
            unset(
                $jsLayout['components']['checkout']['children']['sidebar']['children']
                ['subscribe']
            );
        } else {
            $checked = (bool)$this->configHelper->isNewletterField('newsletter_default');
            $jsLayout['components']['checkout']['children']['sidebar']['children']
            ['subscribe']['config']['checked'] = $checked;
        }
        return $jsLayout;
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    protected function discountCode($jsLayout)
    {
        if ($this->configHelper->isDisplayField('enable_discount_code')) {
            $jsLayout['components']['checkout']['children']['sidebar']['children']['discount'] =
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['afterMethods']['children']['discount'];

            $jsLayout['components']['checkout']['children']['sidebar']['children']['discount']
            ['displayArea'] = 'summary';
            $jsLayout['components']['checkout']['children']['sidebar']['children']['discount']
            ['template'] = 'Bss_OneStepCheckout/payment/discount';

            $jsLayout['components']['checkout']['children']['sidebar']['children']['discount']
            ['sortOrder'] = 230;
        }
        return $jsLayout;
    }
}
