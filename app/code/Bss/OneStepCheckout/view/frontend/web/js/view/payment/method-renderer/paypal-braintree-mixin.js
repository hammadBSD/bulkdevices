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
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list'
], function ($, _, quote, customer, addressList) {
    'use strict';

    var mixin = {
        getShippingAddress: function () {
            var address = quote.shippingAddress(),
                isLoggedIn = customer.isLoggedIn,
                hasDefaultAddress = false,
                addresses = addressList();
            _.each(addresses, function (e, i) {
                if (e.isDefaultShipping()) {
                    hasDefaultAddress = true;
                }
            });
            if (isLoggedIn() &&
                addressList().length &&
                !address &&
                !hasDefaultAddress) {
                return false;
            }
            return this._super();
        },

    };

    return function (target) {
        return target.extend(mixin);
    };
});
