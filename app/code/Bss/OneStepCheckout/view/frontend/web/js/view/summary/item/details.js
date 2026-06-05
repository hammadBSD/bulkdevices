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
    'Magento_Checkout/js/view/summary/item/details',
    'mage/translate',
    'ko',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Bss_OneStepCheckout/js/action/update-item',
    'Magento_Checkout/js/model/quote',
    'mage/url'
], function ($, Component, $t, ko, _, customerData, updateItemAction, quote, url) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bss_OneStepCheckout/summary/item/details'
        },

        titleQtyBox: ko.observable($t('Qty')),
        number: null,

        /**
         * @param {Object} item
         * @returns void
         */
        updateQty: function (item, data) {
            var elementDecrease = this.getDecreaseInput(item.item_id);
            if (item.qty <= 0 && !window.checkRemoveItemOsc) {
                var magentoVersion = window.checkoutConfig.magento_version,
                    message = $t('Please enter the number greater than 0!');
                $(".error-message[itemId = '" + item.item_id + "']").text(message);
                elementDecrease.addClass('disabled-decrease');
                return;
            }
            window.checkRemoveItemOsc = false;
            if (parseFloat(item.qty) != item.qty) {
                $(".error-message[itemId = '" + item.item_id + "']").text($t('Please enter number!'));
                elementDecrease.addClass('disabled-decrease');
                return;
            }
            elementDecrease.removeClass('disabled-decrease');
            $(".error-message[itemId = '" + item.item_id + "']").text($t(''));
            updateItemAction(item).done(
                function (response) {
                    var totals = response.totals,
                        data = JSON.parse(this.data),
                        itemId = data.itemId,
                        itemsOrigin = [],
                        quoteItemData = window.checkoutConfig.quoteItemData;
                    if (!response.status && undefined !== response.qty_before && response.qty_before) {
                        var qtyInput = $('[itemId = ' + itemId + ']').parent().parent().find('input');
                        qtyInput.val(response.qty_before);
                        this.number = setTimeout(function () {
                            qtyInput.trigger('change');
                        }, 500);
                    } else {
                        customerData.reload(['cart']);
                    }
                }
            );
        },

        /**
         * Remove item in CheckOut Page
         * @param data
         * @param event
         */
        removeItemProduct: function (data, event) {
            var element = event.target,
                itemId = element.getAttribute('itemId'),
                qtyInput = $('[itemId = ' + itemId + ']').parent().parent().find('input');
            window.checkRemoveItemOsc = true;
            qtyInput.val(0);
            qtyInput.trigger('change');
        },

        /**
         * @param data
         * @param event
         */
        updateQtyButton: function (data, event) {
            var element = event.target,
                action = element.getAttribute('action'),
                itemId = element.getAttribute('itemId'),
                qtyInput = $('[itemId = ' + itemId + ']').parent().parent().find('input'),
                self = this;
            if (typeof action === "undefined" || typeof itemId === "undefined" || typeof qtyInput === "undefined") {
                return;
            }
            var currentQty = parseFloat(qtyInput.val()),
                elementDecrease = self.getDecreaseInput(itemId);

            elementDecrease.removeClass('disabled-decrease');
            currentQty = Math.round(currentQty * 100);
            if (this.number != null && currentQty >= 100) {
                clearTimeout(this.number);
            }
            if (action == 'increase') {
                var nextQty = (currentQty + 100)/100;
                nextQty = +nextQty.toFixed(2);
                if (nextQty <= 0) {
                    elementDecrease.addClass('disabled-decrease');
                    return false;
                }
                qtyInput.val(nextQty);
                this.number = setTimeout(function () {
                    qtyInput.trigger('change');
                }, 1000);
            } else {
                if (currentQty >= 100) {
                    var nextQty = (currentQty - 100)/100;
                    nextQty = +nextQty.toFixed(2);
                    if (nextQty <= 0) {
                        elementDecrease.addClass('disabled-decrease');
                        return false;
                    }
                    qtyInput.val(nextQty);
                    this.number = setTimeout(function () {
                        qtyInput.trigger('change');
                    }, 1000);
                }
            }
        },

        /**
         * @param {*} itemId
         * @returns {String}
         */
        getProductUrl: function (itemId) {
            if (_.isUndefined(customerData.get('cart')())) {
                customerData.reload(['cart']);
            }
            var productUrl = 'javascript:void(0)',
                cartData = customerData.get('cart')(),
                items = cartData.items;

            var item = _.find(items, function (item) {
                return item.item_id == itemId;
            });

            if (!_.isUndefined(item) && item.product_has_url) {
                productUrl = item.product_url;
            }
            return productUrl;
        },

        /**
         * Get element decrease
         * @param itemId
         * @returns {boolean|*|jQuery}
         */
        getDecreaseInput: function (itemId) {
            var qtyInput = $('span[itemId = ' + itemId + '].decrease');
            if (typeof qtyInput === "undefined" ||
                !qtyInput.length) {
                return false;
            }
            return qtyInput;
        },

        /**
         * If it is 2.3.0
         * @return {boolean}
         */
        is230Ver: function () {
            return this._getMageVersion() == '2.3.0';
        },

        /**
         * Get version Magento
         * @returns {string}
         */
        _getMageVersion: function () {
            return window.checkoutConfig.magento_version;
        }
    });
});
