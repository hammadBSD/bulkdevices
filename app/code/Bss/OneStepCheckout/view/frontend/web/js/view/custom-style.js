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
 * @copyright Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery'
], function ($) {
    "use strict";
    $('.page-header').addClass('page-header navbar-fixed-top');
    if( $(window).width() >= 768 ) {
        $(window).scroll(function() {
            if ($(window).scrollTop() > $('.page-header').offset().top && !($('.page-header').hasClass('sticky'))) {
                $('.page-header').append($('#checkout > [data-role="checkout-messages"]'));
            } else if ($(window).scrollTop() === 0) {
                $('.page-header .messages').remove();
            }
        });
    } else {
        $(window).scroll(function() {
            if ($(window).scrollTop() > $('.page-header').offset().top && !($('.page-header').hasClass('sticky'))) {
                $('.page-header').append($('#checkout > [data-role="checkout-messages"]'));
            } else if ($(window).scrollTop() === 0) {
                $('.page-header .messages').remove();
            }
        });
    }

    //add class bss-store-pickup to body after element bss-store-pickup-selector rendered
    $(document).ready(function() {
        var existCondition = setInterval(function() {
            if ($('#bss-store-pickup-selector').length) {
                clearInterval(existCondition);
                $('#html-body').addClass('bss-store-pickup');
            }
        }, 100);
    });
});
