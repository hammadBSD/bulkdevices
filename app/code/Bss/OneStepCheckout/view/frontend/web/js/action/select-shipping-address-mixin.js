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
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list'
], function (
    $,
    wrapper,
    quote,
    customer,
    addressList
) {
    'use strict';

    return function (proceedSelectShippingAddressFunction) {
        return wrapper.wrap(proceedSelectShippingAddressFunction, function (originalProceedSelectShippingAddressFunction, shippingAddress) {
            if (!window.checkoutConfig.isEnabledOsc) {
                return originalProceedSelectShippingAddressFunction(shippingAddress);
            }
            if (!(customer.isLoggedIn() &&
                addressList().length > 0 &&
                addressList().indexOf(shippingAddress) === -1) || $('.check-store-pickup.bss-onestepcheckout').length) {
                quote.shippingAddress(shippingAddress);
            } else if (undefined !== window.checkoutConfig.isEnabledOsc && !window.checkoutConfig.isEnabledOsc) {
                originalProceedSelectShippingAddressFunction(shippingAddress);
            }
        });
    };
});
