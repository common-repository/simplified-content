/**
 * JSON Proxy object
 */
if (typeof (OOACore) == "undefined")
    OOACore = new function () {
    };

/**
 * Generic class definition for making JSON requests to OOACore services. If a
 * target domain name is supplied it will be assumed to be a JSONP request,
 * otherwise a JSON request is made to the origin server.
 */
OOACore.JSONServiceWrapper = function (targetDomainName) {

    // Member functions
    this.getService = getService;
    this.makeJSONRequest = makeJSONRequest;
    this.setGlobalExceptionHandler = setGlobalExceptionHandler;
    this.abortAllActiveRequests = abortAllActiveRequests;

    // Member data
    this.serviceWrappers = new Array();
    this.globalExceptionHandler = null;
    this.historicalJSONRequests = [];
    this.lastJSONRequest = null;


    /**
     * Set a global error queryEngine function to be called when an error occurs when making a service call.
     *
     *
     * @param globalErrorHandler
     */
    function setGlobalExceptionHandler(globalExceptionHandler) {
        this.globalExceptionHandler = globalExceptionHandler;
    }


    /**
     * Abort all active JSON requests as a big hammer to clear the deck
     */
    function abortAllActiveRequests() {

        while (this.historicalJSONRequests.length) {
            var req = this.historicalJSONRequests.shift();
            req.abort();

        }

    }


    /**
     * Get a service proxy object by name. This has skeletal dummy methods
     * defined to allow a more natural calling pattern.
     *
     */
    function getService(serviceName) {
        var container = this;

        // Ensure we have made a wrapper if none yet made.
        if (!this.serviceWrappers[serviceName]) {
            var descriptor = this.makeJSONRequest(serviceName);

            var newServiceWrapper = new Object();
            for (var i = 0; i < descriptor.serviceMethods.length; i++) {
                var methodName = descriptor.serviceMethods[i].methodName;

                // Inject a method which essentially calls the function using
                // the make JSON request object
                newServiceWrapper[methodName] = function () {

                    var serviceMethodArguments = new Object();
                    for (var i = 0; i < arguments.length; i++) {
                        serviceMethodArguments["param" + (i + 1)] = arguments[i];
                    }

                    return container
                        .makeJSONRequest(arguments.callee.serviceName,
                            arguments.callee.methodName,
                            serviceMethodArguments);
                };

                // Add members to the method in order to make the call
                newServiceWrapper[methodName].methodName = methodName;
                newServiceWrapper[methodName].serviceName = serviceName;

            }

            this.serviceWrappers[serviceName] = newServiceWrapper;
        }

        // Return the service wrapper for the service
        return this.serviceWrappers[serviceName];
    }

    /**
     * Make a JSON Request using supplied service, method and associative
     * parameters array. If the fourth callback argument is supplied, this
     * request will become asynchronous and the callback will be used as the
     * success function.
     *
     */
    function makeJSONRequest(serviceName, methodName, parameters, successCallback, exceptionCallback, abortable) {


        var container = this;

        if (this.lastJSONRequest && this.lastJSONRequest.abortable) {
            this.lastJSONRequest.abort();
        }
        // Construct the URL to call according to what was passed
        var url = (targetDomainName ? targetDomainName : "") + "/JSON/"
            + serviceName;

        // if method name add this to the url
        if (methodName) {
            url += "/" + methodName;
        }

        var encodedParameters = {};
        for (var key in parameters) {
            encodedParameters[key] = $.toJSON(parameters[key]);
        }


        if (targetDomainName) {
            url += "?callback=?";
        } else {
            url += "?_timestamp=" + new Date().getTime();
        }


        // Make the JSON call
        this.lastJSONRequest = $.ajax({
            url: url,
            dataType: targetDomainName ? 'jsonp' : 'json',
            data: encodedParameters,
            type: "POST",
            crossDomain: targetDomainName ? true : false,
            async: successCallback ? true : false,
            success: successCallback ? function (data) {
                successCallback(data);
            } : null,
            error: function (xhr, textStatus, errorThrown) {

                // If for some reason we have got here with a valid request, pass a blank to the callback
                if (xhr.status == 200) {
                    if (successCallback)
                        successCallback(null);
                    return;
                }


                var exception;
                try {
                    exception = $.parseJSON(xhr.responseText);
                } catch (e) {
                    exception = "An unknown error occurred";
                }


                if (exceptionCallback) {
                    exceptionCallback(exception);
                } else if (container.globalExceptionHandler) {
                    container.globalExceptionHandler(exception);
                }
            },
            timeout: 7200000
        });

        this.historicalJSONRequests.push(this.lastJSONRequest);

        // Set the abortable flag.
        this.lastJSONRequest.abortable = abortable;

        var jsonResult = this.lastJSONRequest.responseText;

        // Return the objects
        var result = null;
        if (jsonResult)
            result = $.parseJSON(jsonResult);

        if (result) {

            // If runtime exception return value, raise an exception at this
            // end.
            if (result.className == "ServiceException") {
                throw ({
                    exceptionClass: result.exceptionClass,
                    message: result.exceptionMessage
                });
            } else {
                return result;
            }
        }

    }

};

// Create the global convenience proxy wrapper for origin server access.
OOACore.JSONProxy = new OOACore.JSONServiceWrapper();
