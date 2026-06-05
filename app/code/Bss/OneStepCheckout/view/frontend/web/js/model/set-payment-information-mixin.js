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
    'Bss_OneStepCheckout/js/model/additional-data',
    'Bss_OneStepCheckout/js/model/agreements-assigner',
    'underscore',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, additionalData, agreementsAssigner, _, checkoutData, quote) {
    'use strict';

    return function (placeOrderAction) {

        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(placeOrderAction, function (originalAction, messageContainer, paymentData) {
            if (!_.isUndefined(window.checkoutConfig.bssOsc)) {
                additionalData(paymentData);
            }
            agreementsAssigner(paymentData);

            var shippingAddress = quote.shippingAddress();
            // Set payment in case customer is logged in and
            // His/her address available
            // Or he/she have no addresses, and he/she must fill to input new his/her address
            // In this case, customer dont have to fill his/her email address. So the method checkoutData.getValidatedEmailValue() will be returned an empty result
            if (window.checkoutConfig.isCustomerLoggedIn &&
                (!_.isEmpty(checkoutData.getSelectedShippingAddress()) || !_.isUndefined(checkoutData.getShippingAddressFromData()) || shippingAddress)) {
                return originalAction(messageContainer, paymentData);
            }

            // Set payment in case: customer is guest, he/she have no addresses.
            // And he/she must fill email/address to input to continue place order.
            if (!_.isEmpty(checkoutData.getValidatedEmailValue())) {
                return originalAction(messageContainer, paymentData);
            }
        });
    };
});
