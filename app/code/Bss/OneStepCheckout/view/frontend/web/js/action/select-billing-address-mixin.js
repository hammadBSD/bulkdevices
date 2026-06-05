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
 * @copyright Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (selectBillingAddressAction) {
        return wrapper.wrap(selectBillingAddressAction, function (selectBillingAddress, billingAddress) {
            if (!window.checkoutConfig.isEnabledOsc) {
                return selectBillingAddress(billingAddress);
            }
            if (window.isAddressSameAsShipping == undefined) {
                window.isAddressSameAsShipping = true;
            }
            if (quote.shippingAddress() && !billingAddress && quote.billingAddress) {
                quote.billingAddress(quote.shippingAddress());
                billingAddress = quote.shippingAddress();
            }
            if (quote.shippingAddress() && billingAddress && billingAddress.getCacheKey() != quote.shippingAddress().getCacheKey() && window.isAddressSameAsShipping) {
                billingAddress = quote.shippingAddress();
            }
            return selectBillingAddress(billingAddress);
        });
    };
});
