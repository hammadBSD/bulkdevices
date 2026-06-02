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
    'ko',
    'Magento_Customer/js/model/customer',
    'Bss_OneStepCheckout/js/model/store-pickup'
], function (ko, customer, storePickup) {
    'use strict';

    var mixin = {
        defaults: {
            fadeCss: false
        },
        /** @inheritdoc */
        initObservable: function () {
            var self = this;
            this._super().observe(['fadeCss']);
            this.fadeCss = ko.computed(function () {
                var finalClass = '';
                if (self.isSelected()) {
                    finalClass = 'selected-item';
                } else {
                    finalClass = 'not-selected-item';
                }
                if (customer.isLoggedIn() &&
                    customer.getShippingAddressList().length &&
                    storePickup.isStoreShipping()) {
                    finalClass += ' shipping-list-unable';
                } else {
                    finalClass += ' not-shipping-list-unable';
                }
                return finalClass;
            });
            return this;
        }
    };
    return function (shippingAddressDefault) {
        return shippingAddressDefault.extend(mixin);
    };
});
