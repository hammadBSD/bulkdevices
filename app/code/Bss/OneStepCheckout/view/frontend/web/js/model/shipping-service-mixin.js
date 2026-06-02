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
    'mage/utils/wrapper',
    'Bss_OneStepCheckout/js/model/store-pickup'
], function ($, _, wrapper, storePickup) {
    'use strict';

    return function (shippingService) {
        shippingService.setShippingRates = wrapper.wrapSuper(shippingService.setShippingRates, function (ratesData) {
            if ($('.bss-onestepcheckout').length) {
                var newRates = [];
                if (ratesData.length) {
                    _.each(ratesData, function (rate, idx) {
                        if (rate.carrier_code !== 'instore') {
                            newRates.push(rate);
                        }
                    });
                }

                if (!storePickup.ratesData().length) {
                    // Only cache first time to keep full available shipping method
                    // remove instore method if in normal shipping
                    storePickup.ratesData(ratesData);
                }

                if (!storePickup.isStoreShipping()) {
                    this._super(newRates);
                } else {
                    this._super(ratesData);
                }
            } else {
                this._super(ratesData);
            }
        });

        return shippingService;
    };
});
