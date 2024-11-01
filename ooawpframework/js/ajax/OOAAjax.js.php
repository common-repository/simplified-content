/**
 * Javascript Ajax Dispatcher for interacting with the standard OOAWPServiceDispatcher
 *
 * @param string pluginName
 * @param string serviceName
 * @param string serviceMethod
 * @param object methodParameters
 * @param function successCallback
 * @param function errorCallback
 */
function OOAAjax(pluginName, serviceName, serviceMethod, methodParameters, successCallback, errorCallback) {

    var passedData = {
        action: "service_dispatcher",
        plugin: pluginName,
        service: serviceName,
        serviceMethod: serviceMethod
    };

    if (methodParameters && $.isPlainObject(methodParameters)) {
        for (var i = 0; i < Object.keys(methodParameters).length; i++) {
            var key = Object.keys(methodParameters)[i];
            passedData[key] = JSON.stringify(methodParameters[key]);
        }
    }


    $.ajax({
        url: "<?php echo esc_url($_REQUEST["ajaxURL"]); ?>",
        data: passedData,
        type: "POST",
        dataType: "json",
        success: successCallback,
        error: function (xhr, status, error) {

            var errorObject = $.parseJSON(xhr.responseText);

            if (errorCallback) {
                errorCallback(errorObject);
            } else if (errorObject.className && errorObject.className == "ServiceException") {
                console.log(errorObject.exceptionMessage);
            }

        },
        timeout: 10000
    });


};
