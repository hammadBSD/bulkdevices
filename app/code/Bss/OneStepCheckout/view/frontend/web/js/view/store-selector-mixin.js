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

define([
    'jquery',
    'underscore',
    'uiComponent',
    'uiRegistry',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/checkout-data'
], function (
    $,
    _,
    Component,
    registry,
    modal,
    quote,
    customer,
    stepNavigator,
    addressConverter,
    setShippingInformationAction,
    checkoutData
) {
    'use strict';
    var mixin = {
        /**
         * Set shipping information handler
         */
        setPickupInformation: function () {
            var shippingAddress = quote.shippingAddress();

            if (this.validatePickupInformation()) {
                shippingAddress = addressConverter.quoteAddressToFormAddressData(shippingAddress);
                checkoutData.setShippingAddressFromData(shippingAddress);
                setShippingInformationAction().done(function () {
                    //Work with OSC,if OSC module disable then process as default
                    if(!$('.bss-onestepcheckout').length) {
                        stepNavigator.next();
                    }
                });
            }
        },
        /**
         * @param {Object} location
         * @returns void
         */
        selectPickupLocation: function (location) {
            this._super();
            // trigger pickup infomation when click ship here
            if ($('.bss-onestepcheckout').length) {
                this.setPickupInformation();
            }
        },

        /**
         * @returns {Boolean}
         */
        validatePickupInformation: function () {
            var emailValidationResult,
                loginFormSelector = this.loginFormSelector;
            // if OSC active, replace login form from store pickup to shipping address
            if ($('.bss-onestepcheckout').length) {
                loginFormSelector = '.bss-onestepcheckout form[data-role=email-with-possible-login]';
            }
            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = $(loginFormSelector + ' input[name=username]').valid() ? true : false;

                if (!emailValidationResult) {
                    $(this.loginFormSelector + ' input[name=username]').focus();

                    return false;
                }
            }
            return true;
        }
    };


    return function (target) {
        return target.extend(mixin);
    };
});
