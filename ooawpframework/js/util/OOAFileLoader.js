$ = jQuery;

/**
 * Created with JetBrains PhpStorm.
 * User: mark
 * Date: 14/06/2012
 * Time: 13:48
 * To change this template use File | Settings | File Templates.
 */
OOAFileLoader = new function () {

    // Member functions
    this.setVersionString = setVersionString;
    this.appendVersionStringToPath = appendVersionStringToPath;

    this.loadFile = loadFile;
    this.loadScript = loadScript;
    this.loadScriptSet = loadScriptSet;
    this.loadCSS = loadCSS;

    // Member data
    this.cachedFiles = [];
    this.loadedScriptPaths = [];
    this.loadedScriptHandlers = [];
    this.versionString = new Date().getTime();

    var SCRIPT_LOADING = 1;
    var SCRIPT_LOADED = 2;

    /**
     * Set a version string to use instead of the default timestamp
     *
     * @param versionString
     */
    function setVersionString(versionString) {
        this.versionString = versionString;
    }


    /**
     * Load a file - if it's already been loaded we simply use the cached version
     * Call the loaded queryEngine with the file contents once loaded or return the value synchronously
     * if no load queryEngine supplied
     *
     * @param filePath
     * @param loadedHandler
     */
    function loadFile(filePath, loadedHandler) {


        var cachedFiles = this.cachedFiles;

        if (this.cachedFiles[filePath]) {

            if (loadedHandler) {
                loadedHandler(this.cachedFiles[filePath]);
            } else {
                return this.cachedFiles[filePath];
            }


        } else {


            var modifiedFilePath = this.appendVersionStringToPath(filePath);

            request = $.ajax({
                url:modifiedFilePath,
                dataType:'json',
                type:"GET",
                crossDomain:false,
                async:loadedHandler ? true : false,
                complete:function (result) {

                    if (loadedHandler) {
                        cachedFiles[filePath] = result.responseText;
                        loadedHandler(result.responseText);
                    }
                },
                timeout:120000
            });


            if (!loadedHandler) {
                this.cachedFiles[filePath] = request.responseText;
                return request.responseText;
            }


        }


    }


    /**
     * Load a script - if it's already been loaded we simply call the loaded queryEngine
     * otherwise, call this once the load has finished.
     *
     * @param filePath
     * @param loadedHandler
     */
    function loadScript(filePath, loadedHandler) {

        var container = this;

        // If we aren't loaded yet, load the script.
        if (container.loadedScriptPaths[filePath] != SCRIPT_LOADED) {

            // Ensure we have an array of handlers and push this one onto the stack.
            if (!container.loadedScriptHandlers[filePath]) {
                container.loadedScriptHandlers[filePath] = [];
            }

            container.loadedScriptHandlers[filePath].push(loadedHandler);

            // If we are not loading, load.
            if (container.loadedScriptPaths[filePath] != SCRIPT_LOADING) {

                container.loadedScriptPaths[filePath] = SCRIPT_LOADING;

                $.ajaxSetup({cache:true});

                // Load the script
                $.getScript(container.appendVersionStringToPath(filePath),
                    function () {

                        var handlers = container.loadedScriptHandlers[filePath];

                        for (var i = 0; i < handlers.length; i++) {
                            handlers[i]();
                        }

                        container.loadedScriptPaths[filePath] = SCRIPT_LOADED;
                        container.loadedScriptHandlers[filePath] = null;

                    });


            }

        } else if (loadedHandler) {
            loadedHandler();
        }


    }


    /**
     * Load a set of scripts and fire the queryEngine once complete
     * useful for multiple dependency trees.
     *
     * @param filePaths
     * @param loadedHandler
     */
    function loadScriptSet(filePaths, loadedHandler) {

        if (filePaths.length > 0) {

            var container = this;

            this.loadScript(filePaths.shift(), function () {
                container.loadScriptSet(filePaths, loadedHandler);
            });

        } else {
            if (loadedHandler) {
                loadedHandler();
            }
        }

    }


    /**
     * Load a css file.
     *
     * @param filePath
     * @param loadedHandler
     */
    function loadCSS(filePath) {

        if (!this.loadedScriptPaths[filePath]) {
            $("head").append("<link>");
            var css = $("head").children(":last");
            css.attr({
                rel:"stylesheet",
                type:"text/css",
                href:this.appendVersionStringToPath(filePath)
            });
        }

    }


    /**
     * Append the defined version string to the supplied path.
     *
     * @param path
     */
    function appendVersionStringToPath(path) {

        if (this.versionString) {

            // Add a timestamp parameter to the file path as a param
            if (path.indexOf("?") > 0) {
                path += "&_=" + this.versionString;
            } else {
                path += "?_=" + this.versionString;
            }

        }

        return path;

    }


};