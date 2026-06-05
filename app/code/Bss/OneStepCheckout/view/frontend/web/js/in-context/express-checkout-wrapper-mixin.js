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
    'Magento_Paypal/js/in-context/express-checkout-smart-buttons'
], function (checkoutSmartButtons) {
    'use strict';
    window.paypalElement = false;
    return function(target){
        if (!window.checkoutConfig.isEnabledOsc) {
            return target;
        }
        target.renderPayPalButtons = function (element) {
            if (window.paypalElement == false) {
                window.paypalElement = element;
            }
            if (typeof window.checkoutConfig === "undefined") {
                checkoutSmartButtons(this.prepareClientConfig(), element);
            }
        }
        return target;
    };
});
