define([
    'ko'
], function (ko) {
    'use strict';

    return {
        isStoreShipping: ko.observable(false),
        ratesData: ko.observableArray([])
    };
});