/**
 * Copyright © MagestyApps. All rights reserved.
 * See LICENSE.txt for license details.
 */

var config = {
    map: {
        '*': {
            // Disable override to native Magento media uploader to avoid missing Uppy dependency
            // 'Magento_Backend/js/media-uploader': 'MagestyApps_WebImages/js/media-uploader'
        }
    },
    config: {
        mixins: {
            'Magento_Ui/js/form/element/image-uploader': {
                'MagestyApps_WebImages/js/form/element/image-uploader-mixin': true
            }
        }
    }
};
