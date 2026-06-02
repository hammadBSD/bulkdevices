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
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/error-processor'
], function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor) {
    'use strict';

    return function (address) {
        shippingService.isLoading(true);
        return storage.post(
            resourceUrlManager.getUrlForEstimationShippingMethodsByAddressId(),
            JSON.stringify({
                addressId: address.customerAddressId
            }),
            false
        ).done(function (result) {
            rateRegistry.set(address.getKey(), result);
            shippingService.setShippingRates(result);
        }).fail(function (response) {
            shippingService.setShippingRates([]);
            errorProcessor.process(response);
        }).always(function () {
                shippingService.isLoading(false);
            }
        );
    }
});
