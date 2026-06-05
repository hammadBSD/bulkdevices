define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    function main(config, element) {
        var $element = $(element);
        var AjaxUrl = config.AjaxUrl;

        var dataForm = $('#contact-form');
        dataForm.mage('validation', {});

        $(document).on('click','.submit',function() {

            if (dataForm.valid()){
                event.preventDefault();
                var param = dataForm.serialize();
                alert(param);
                $.ajax({
                    showLoader: true,
                    url: AjaxUrl,
                    data: param,
                    type: "POST"
                }).done(function (data) {
                    $('.note').html(data);
                    $('.note').css('color', 'red');
                    document.getElementById("contact-form").reset();
                    return true;
                });
            }
        });
    };
    return main;


});