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
    'underscore',
    'uiComponent',
    'jquery',
    'Bss_OneStepCheckout/js/model/store-pickup',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service'
], function (
    ko,
    _,
    Component,
    $,
    storePickup,
    quote,
    shippingService
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bss_OneStepCheckout/bss-store-selector',
            storePickupElem: '#store-pickup',
            btnNormalShip: '.action-select-shipping',
            btnStorePickup: '.action-select-store-pickup',
            isStorePickupSelected: false,
            isAvailable: false,
            rate: {
                'carrier_code': 'instore',
                'method_code': 'pickup'
            },
            rates: shippingService.getShippingRates()
        },
        /**
         * @return {mixin}
         */
        initObservable: function () {
            this._super();
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
            $(self.storePickupElem).find(self.btnNormalShip).click();
            storePickup.isStoreShipping(false);
            if ($("#opc-shipping_method").css("display") === 'none') {
                $("#opc-shipping_method").show();
            }
        },
        /**
         * @returns void
         */
        selectStorePickup: function () {
            var self = this;
            storePickup.isStoreShipping(true);
            $(self.storePickupElem).find(self.btnStorePickup).click();
        }
    });
});
