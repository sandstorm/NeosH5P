H5P.jQuery(function ($) {

    function onXAPIPostError(xhr, message) {
        console.error("xapi post error");
        console.error(xhr.responseText);
        console.error(message, xhr.status);
    }

    function onXAPIPostSuccess(res, textStatus, xhr) {
        console.log("xapi post success");
        console.log(xhr.responseText);
        console.log(message, xhr.status);
    }

    /**
     * This function is called for every xAPI statement that is created by H5P content.
     * At this point, you can send the statements off to your LRS or do any other things
     * with them.
     */
    function onXAPI(event) {
        console.log('An xAPI event was fired.');
        console.log('To get rid of this message, refer to the "xAPI" section in the Settings.yaml of the Sandstorm.NeosH5P package.');
        console.log(event);
        // $.ajax({
        //     type: "POST",
        //     // This is the example taken from the config - define your own integration settings here, they will be
        //     // injected as a JSON object.
        //     url: window.NeosH5PxAPI.yourLRSEndpoint,
        //     data: JSON.stringify(event.data.statement),
        //     dataType: "json",
        //     success: onXAPIPostSuccess,
        //     error: onXAPIPostError
        // });
    }

    $(document).ready(function () {
        if (H5P.externalDispatcher) {
            H5P.externalDispatcher.on('xAPI', onXAPI);
        }
    });
});
