/**
 * Debug mode script for xAPI that simply logs every xAPI event to the console.
 */
H5P.jQuery(function ($) {
    $(document).ready(function () {
        if (H5P.externalDispatcher) {
            H5P.externalDispatcher.on('xAPI', e => console.log(e));
        }
    });
});
