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
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Paypal/js/in-context/express-checkout-smart-buttons',
    'Bss_OneStepCheckout/js/action/validate-shipping-information',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate'
], function ($, registry, quote, additionalValidators, checkoutSmartButtons, validateShippingInformationAction, selectBillingAddress, fullScreenLoader) {
    'use strict';
    var buttonId = 'paypay-in-context-button',
        errorId = 'paypay-in-context-validate-error',
        html = '<p id="' + errorId + '" style="display:none">' + $.mage.__('Please Fill All Require Field!') + '</p><p class="action primary" id="' + buttonId + '" style="display:none">' + $.mage.__('Continue Paypal') + '</p>',
        mixin = {

            /**
             * Listens element on change and validate it.
             *
             * @param {HTMLElement} context
             */
            initListeners: function (context) {
                $(context).find('.payment-method-content').append(html);
                var seft = this;
                
                if (quote.paymentMethod()) {
                    $('#' + buttonId).parent().find('.actions-toolbar').html('');
                    $('#' + buttonId).show();
                    $('#' + buttonId).removeClass('disable');
                }
                quote.billingAddress.subscribe(function (address) {
                    if (quote.isVirtual()) {
                        if (address !== null && quote.paymentMethod() != null) {
                            $('#' + buttonId).removeClass('disable');
                            return;
                        }
                    } else {
                        if (address !== null && quote.paymentMethod() != null && quote.shippingMethod() != null) {
                            $('#' + buttonId).removeClass('disable');
                            return;
                        }
                    }
                    if (!$('#' + buttonId).hasClass('disable')) {
                        $('#' + buttonId).addClass('disable');
                    }
                }, this);

                $('#' + buttonId).click(function () {
                    if (!$(this).hasClass('disable')) {
                        $(this).addClass('disable');
                        seft.customValidate(seft);
                    }
                });
                this.fieldChange();
            },

            /**
             *  Validates Smart Buttons
             */
            validate: function () {
                if (this.actions) {
                    this.actions.enable();
                }
            },

            fieldChange: function () {
                $('input[type=radio], input[type=checkbox], select').change(function () {
                    $('#' + buttonId).parent().find('.actions-toolbar').html('');
                    $('#' + buttonId).show();
                    $('#' + errorId).hide();
                    $('#' + buttonId).removeClass('disable');
                    if ($(this).val() == 'paypal_express') {
                        $('#' + buttonId).trigger('click');
                    }
                });
                $("input[type=text], textarea").keyup(function(){
                    $('#' + buttonId).parent().find('.actions-toolbar').html('');
                    $('#' + buttonId).show();
                    $('#' + buttonId).removeClass('disable');
                });
                $("input[type=text], textarea").keydown(function(){
                    $('#' + buttonId).parent().find('.actions-toolbar').html('');
                    $('#' + buttonId).show();
                    $('#' + buttonId).removeClass('disable');
                });
            },

            customValidate: function (seft) {
                var shippingAddressComponent = registry.get('checkout.steps.shipping-step.shippingAddress');
                if (additionalValidators.validate() == true) {
                    if (!quote.isVirtual()) {
                        if (shippingAddressComponent.validateShippingInformation()) {
                            validateShippingInformationAction().done(
                                function () {
                                    $('#' + buttonId).hide();
                                    $('#' + errorId).hide();
                                    checkoutSmartButtons(seft.prepareClientConfig(), window.paypalElement);
                                }
                            ).fail(
                                function () {
                                    $('#' + buttonId).show();
                                    $('#' + errorId).show();
                                    $('#' + buttonId).removeClass('disable');
                                    fullScreenLoader.stopLoader();
                                }
                            );
                            return;
                        }
                    }
                }
                $('#' + buttonId).show();
                $('#' + errorId).show();
                $('#' + buttonId).removeClass('disable');
            }
        };

    return function (target) {
        return target.extend(mixin);
    };
});