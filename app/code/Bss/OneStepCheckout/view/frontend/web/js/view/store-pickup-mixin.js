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
    'uiComponent',
    'underscore',
    'jquery',
    'Bss_OneStepCheckout/js/model/store-pickup',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer'
], function (
    ko,
    Component,
    _,
    $,
    storePickup,
    shippingService,
    quote,
    customer
) {
    'use strict';
    var mixin = {
        /**
         * @inheritdoc
         */
        initialize: function () {
            this.elmOSC = '.bss-onestepcheckout';
            this.elmCheckStorePickup = 'check-store-pickup';
            this.elmSameAddress = '[name="billing-address-same-as-shipping"]';
            this.elemAddresList = '.bss-onestepcheckout .field.addresses';
            this.elemShippingStep = '.bss-onestepcheckout #shipping';
            this.elemHiddenStep = 'hidden-step';
            this.elmShippingMethod = '.bss-onestepcheckout #opc-shipping_method .checkout-shipping-method';
            this.elmShippingMethodStep = '.bss-onestepcheckout #opc-shipping_method';
            this.elmStorePickupMix = '.bss-onestepcheckout.check-store-pickup';
            this.elmShippingItem = '.shipping-address-item';
            this._super();
        },
        /**
         * @return {mixin}
         */
        initObservable: function () {
            this._super().observe(['isVisible']);

            this.isStorePickupSelected = ko.pureComputed(function () {
                return storePickup.isStoreShipping() || _.isMatch(quote.shippingMethod(), this.rate);
            }, this);

            this.isAvailable = ko.pureComputed(function () {
                return storePickup.isStoreShipping() || _.findWhere(this.rates(), {
                    'carrier_code': this.rate['carrier_code'],
                    'method_code': this.rate['method_code']
                });
            }, this);
            return this;
        },

        /**
         * @returns void
         */
        selectShipping: function () {
            var self = this;
            storePickup.isStoreShipping(false);
            this._super();
            // Work with OSC,when pick shipping button then show shipping address form and shipping method
            $(self.elmOSC).removeClass(self.elmCheckStorePickup);
            $(self.elmOSC).removeClass(self.elemHiddenStep);
            if ($(".bss-margin")[0]) {
                $(self.elmShippingMethodStep).removeClass('bss-margin');
            }
            $(self.elmShippingMethodStep).addClass('list-item');
            $('.opc-wrapper').removeProp('min-height');

            // Auto click on selected item
            let checkListItem = false;
            $(self.elmOSC).find(self.elmShippingItem).each(function () {
                if ($(self).hasClass('selected-item')) {
                    checkListItem = true;
                    $(self).click();
                }
            });
            if (!checkListItem && $(self.elmOSC).find(self.elmShippingItem).length) {
                $(self.elmOSC).find(self.elmShippingItem).first().click();
            }
        },

        /**
         * @returns void
         */
        selectStorePickup: function () {
            var self = this;
            storePickup.isStoreShipping(true);
            shippingService.isLoading(true);
            if (storePickup.ratesData().length) {
                shippingService.setShippingRates(storePickup.ratesData());
            }
            shippingService.isLoading(false);
            this._super();
            // Work with OSC,when pick store pickup address then hide shipping address form and shipping method
            $(self.elmOSC).addClass(self.elmCheckStorePickup);
            if (customer.isLoggedIn()) {
                $(self.elmOSC).addClass(self.elemHiddenStep);
            }
            if ($(".list-item")[0]) {
                $(self.elmShippingMethodStep).removeClass('list-item');
            }
            $(self.elmShippingMethodStep).addClass('bss-margin');

            if ($(self.elmShippingMethod + ' > div').length < 4) {
                $(self.elmShippingMethodStep).css('display', 'none');
            }
            $(self.elmSameAddress).prop("disabled", false);
            $(self.elmSameAddress).prop('checked', true);
            $(self.elmSameAddress).click();
            // Css fix
            var $payment = $(self.elmStorePickupMix).find('#payment');
            if ($payment.length) {
                $('.opc-wrapper').css('min-height', $payment.height());
            }
        },

        /**
         * @returns void
         */
        preselectLocation: function () {
            if (this.isStorePickupSelected()) {
                storePickup.isStoreShipping(true);
                shippingService.isLoading(true);
                if (storePickup.ratesData().length) {
                    shippingService.setShippingRates(storePickup.ratesData());
                }
                shippingService.isLoading(false);
            } else {
                storePickup.isStoreShipping(false);
            }

            this._super();
            // Work with OSC,when pick in store then hide shipping address form and shipping method
            if (this.isStorePickupSelected()) {
                $(this.elmOSC).addClass(this.elmCheckStorePickup);
                if (customer.isLoggedIn()) {
                    $(this.elmOSC).addClass(this.elemHiddenStep);
                }
                if ($(this.elmShippingMethod + ' > div').length < 4) {
                    $(this.elmShippingMethodStep).css('display', 'none');
                }
                $(this.elmSameAddress).prop("disabled", false);
                $(this.elmSameAddress).prop('checked', true);
                $(this.elmSameAddress).click();
            }
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
