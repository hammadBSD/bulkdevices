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
 * @copyright Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'mage/utils/wrapper',
    'Bss_OneStepCheckout/js/model/checkoutcom-payment'
], function (wrapper, checkoutComPayment) {
    'use strict';

    var mixin = {

        /**
         * Pass variable to place order component
         */
        initialize: function () {
            this._super();
            this.allowPlaceOrder.subscribe(function (status) {
                checkoutComPayment(status);
            });
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
