/**
 * Mobile PDP: keep static gallery image for Speed Index; init Fotorama on first tap.
 */
define([
    'jquery',
    'matchMedia',
    'mage/apply/main'
], function ($, mediaCheck, mage) {
    'use strict';

    return function (config, element) {
        var $el = $(element);

        function startGallery(onReady) {
            if ($el.data('bdGalleryDeferDone')) {
                if (typeof onReady === 'function') {
                    onReady();
                }
                return;
            }

            $el.data('bdGalleryDeferDone', true);
            $el.off('click.bdGalleryDefer');

            $el.one('gallery:loaded', function () {
                if (typeof onReady === 'function') {
                    onReady();
                }
            });

            mage.applyFor(element, config, 'mage/gallery/gallery');
        }

        function bindMobileTapToFullscreen() {
            $el.on('click.bdGalleryDefer', '.gallery-placeholder__image', function (e) {
                e.preventDefault();
                startGallery(function () {
                    var gallery = $el.data('gallery');

                    if (gallery && typeof gallery.openFullScreen === 'function') {
                        gallery.openFullScreen();
                    }
                });
            });
        }

        mediaCheck({
            media: '(max-width: 768px)',
            entry: bindMobileTapToFullscreen,
            exit: function () {
                startGallery();
            }
        });
    };
});
