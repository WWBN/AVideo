<?php
//var_dump($_SERVER);exit;
$configFile = dirname(__FILE__) . '/../../../videos/configuration.php';
$doNotIncludeConfig = 1;
require_once $configFile;

$uuid = uniqid();
?><!DOCTYPE html>
<html>
    <head>
        <script>
            function isASubIFrame() {
                return document.location.ancestorOrigins.length > 0 && whoAmI() !== parent.whoAmI() && window.parent !== window.top;
            }
            if (isASubIFrame()) {
                console.log('isASubIFrame', window.parent.document.location, document.location);
                window.parent.document.location = document.location;
                
            }
        </script>
        <title>Loading...</title>
        <link rel="shortcut icon" href="<?php echo $global['webSiteRootURL']; ?>videos/favicon.ico" sizes="16x16,24x24,32x32,48x48,144x144">
        <link href="<?php echo $global['webSiteRootURL']; ?>node_modules/jquery-ui-dist/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <style>
            html{
                overflow: auto;
            }

            html, body, iframe {
                margin: 0px;
                padding: 0px;
                height: 100%;
                border: none;
            }
            html, body, iframe, .ui-dialog .ui-dialog-content {
                margin: 0px !important;
                padding: 0px !important;
            }
            .ui-dialog{
                box-shadow: 2px 2px 15px black;
            }
            iframe{
                display: block;
                width: 100%;
                border: none;
                overflow-y: auto;
                overflow-x: hidden;
                /*border: 4px solid green;
                margin: 5px;*/
            }

        </style>
    </head>
    <body>
        <iframe 
            frameborder="0" 
            marginheight="0" 
            marginwidth="0" 
            width="100%" 
            height="100%" 
            scrolling="auto"
            src="<?php echo $global['webSiteRootURL']; ?>?inMainIframe=1" id="mainIframe"></iframe>
        <script src="<?php echo $global['webSiteRootURL']; ?>node_modules/jquery/dist/jquery.min.js"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>node_modules/jquery-ui-dist/jquery-ui.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-dialogextend/build/jquery.dialogextend.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>node_modules/sweetalert/dist/sweetalert.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>node_modules/js-cookie/dist/js.cookie.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>node_modules/jquery-toast-plugin/dist/jquery.toast.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/script.js" type="text/javascript"></script>
        <script>

            var uuid = '<?php echo $uuid; ?>';

            function whoAmI() {
                return uuid;
            }

            function iframeURLChange(iframe, callback) {
                var unloadHandler = function () {
                    // Timeout needed because the URL changes immediately after
                    // the `unload` event is dispatched.
                    setTimeout(function () {
                        console.log('iframe 1', iframe);
                        callback(iframe.contentWindow.location.href, iframe.contentDocument.title);
                    }, 1000);
                };

                function attachUnload() {
                    // Remove the unloadHandler in case it was already attached.
                    // Otherwise, the change will be dispatched twice.
                    iframe.contentWindow.removeEventListener("unload", unloadHandler);
                    iframe.contentWindow.addEventListener("unload", unloadHandler);
                }

                iframe.addEventListener("load", attachUnload);
                attachUnload();
            }

            iframeURLChange(document.getElementById("mainIframe"), function (src, title) {
                console.log("URL changed 1:", src, title);
                document.title = title;
                window.history.pushState("", "", src.replace('?inMainIframe=1', ''));
            });

            var windowCount = 0;
            function openWindow(url, iframeAllowAttributes, title) {
                var id = 'window' + windowCount;
                windowCount++;
                var html = '<div id="' + id + '" title="' + title + '">';
                html += '<iframe id="avideoWindowIframe' + id + '" frameBorder="0" class="animate__animated animate__bounceInDown" src="' + url + '"  ' + iframeAllowAttributes + ' ></iframe>';
                html += '</div>';
                $('body').append(html);
                $('#' + id).dialog({
                    draggable: true,
                    autoOpen: true,
                    modal: false,
                    responsive: true,
                    width: 800,
                    height: 600,
                    minHeight: 300,
                    minWidth: 300,
                    close: function (event, ui) {
                        $(this).remove();
                    }
                }).dialogExtend({
                    "closable": true,
                    "maximizable": true,
                    "minimizable": true,
                    "collapsable": true,
                    "dblclick": "collapse",
                    "titlebar": "transparent",
                    "minimizeLocation": "right",
                    "icons": {
                        "close": "ui-icon-circle-close",
                        "maximize": "ui-icon-circle-plus",
                        "minimize": "ui-icon-circle-minus",
                        "collapse": "ui-icon-triangle-1-s",
                        "restore": "ui-icon-bullet"
                    }
                });
                function iframeURLChange2(iframe, selector, callback) {
                    var unloadHandler = function () {
                        // Timeout needed because the URL changes immediately after
                        // the `unload` event is dispatched.
                        setTimeout(function () {
                            //console.log('iframe 1', iframe);
                            if(iframe.contentDocument){
                                callback(selector, iframe.contentDocument.title);
                            }
                        }, 1000);
                    };

                    function attachUnload() {
                        // Remove the unloadHandler in case it was already attached.
                        // Otherwise, the change will be dispatched twice.
                        iframe.contentWindow.removeEventListener("unload", unloadHandler);
                        iframe.contentWindow.addEventListener("unload", unloadHandler);
                    }

                    iframe.addEventListener("load", attachUnload);
                    attachUnload();
                }

                iframeURLChange2(document.getElementById('avideoWindowIframe' + id), '#' + id, function (selector, title) {
                    $(selector).dialog('option', 'title', title);
                });
                //addButtons($("." + id));
            }

            $(document).ready(function () {
                //$("#window").draggable({handle: ".panel-heading", containment: "body"});
                //$("#window").resizable();      
            });
        </script>
    </body>
</html>
