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
    'mage/utils/wrapper',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    $,
    wrapper,
    customer,
    checkoutData,
    fullScreenLoader
) {
    'use strict';

    /**
     * Mixin Magento_Checkout/js/model/error-processor
     * @param {Object} response
     * @param {Object} messageContainer
     */
    return function (errorProcessor) {
        if (!window.checkoutConfig.isEnabledOsc) {
            return errorProcessor;
        }
        errorProcessor.process = wrapper.wrapSuper(errorProcessor.process, function (response, messageContainer) {
            var isNewCustomerRegister = checkoutData.getShippingAddressFromData();
            var emailValidationResult = true,
                loginFormSelector = 'form[data-role=email-with-possible-login]';
            if (!customer.isLoggedIn()) {
                var emailGuest = $(loginFormSelector + ' input[name=username]').val();
                emailValidationResult = $.mage.isEmptyNoTrim(emailGuest) && /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(emailGuest);//eslint-disable-line max-len
            } else {
                emailValidationResult = isNewCustomerRegister ? true : false;
            }
            if (emailValidationResult) {
                this._super(response, messageContainer);
            }
            fullScreenLoader.stopLoader();
        });

        return errorProcessor;
    };
});
