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
    'Magento_Checkout/js/checkout-data',
], function ($, checkoutData) {
    'use strict';

    var mixin = {
        initialize: function () {
            this._super();
            this.isReviewRequired.subscribe(function (status) {
                var btnPlaceOrder = $('#braintree_paypal_place_order');
                if (status) {
                    btnPlaceOrder.show();
                } else {
                    btnPlaceOrder.hide();
                }
            });
            return this;
        }

    };

    return function (target) {
        return target.extend(mixin);
    };
});
