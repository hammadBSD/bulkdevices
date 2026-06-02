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
 * @category   BSS
 * @package    Bss_OneStepCheckout
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'underscore'
], function (
    $,
    _
) {
    'use strict';

    var mixin = {
        /**
         * @param {Object} group
         * @returns {Boolean}
         */
        showFormShared: function (group) {
            var isShow = false,
                self = this;
            if (self.paymentGroupsList().length) {
                if (_.first(self.paymentGroupsList()).alias == group().alias) {
                    isShow = true;
                }
            }
            return isShow;
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});