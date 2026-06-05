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
define(
    [
        'jquery',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'mage/storage',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/recollect-shipping-rates',
        'Magento_Checkout/js/action/get-totals',
    ],
    function ($, urlManager, errorProcessor, storage, getPaymentInformationAction, totals, fullScreenLoader, recollectShippingRates, getTotalsAction) {
        'use strict';

        return function (action) {
            var url = urlManager.build('rest/V1/bss-osc/giftwrap/apply/' + action);
            fullScreenLoader.startLoader();
            return storage.post(
                url,
                {},
                false
            ).done(
                function (response) {
                    var res = JSON.parse(response);
                    if (res.status) {
                        var deferred = $.Deferred();
                        totals.isLoading(true);
                        recollectShippingRates();
                        getPaymentInformationAction(deferred);
                        if (res.status == 'virtual') {
                            $('#giftwrap-checkbox').remove();
                        } else {
                            $('#giftwrap-checkbox label span').text(res.gift_wrap_label);
                        }
                        $.when(deferred).done(function () {
                            fullScreenLoader.stopLoader();
                            totals.isLoading(false);
                        });
                    }
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                    getTotalsAction([], deferred);
                }
            ).fail(
                function (response) {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                }
            );
        };
    }
);
