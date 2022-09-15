<?php
include dirname(__FILE__) . '/../view/firstPage.php';exit;

//var_dump($_SERVER);exit;
$configFile = dirname(__FILE__) . '/../videos/configuration.php';
$doNotIncludeConfig = 1;
require_once $configFile;
require_once "{$global['systemRootPath']}objects/functions.php";
if (isIframe()) {
    include "{$global['systemRootPath']}view/firstPage.php";
    exit;
}
//var_dump($_SERVER);exit;
?><!DOCTYPE html>
<html>
    <head>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
        </script>
        <title>Loading...</title>
        <link rel="shortcut icon" href="<?php echo $global['webSiteRootURL']; ?>videos/favicon.ico" sizes="16x16,24x24,32x32,48x48,144x144">
        <link href="<?php echo $global['webSiteRootURL']; ?>node_modules/jquery-ui-dist/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>node_modules/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css"/>
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
            src="<?php echo $global['webSiteRootURL']; ?>site" id="mainIframe"></iframe>
        <script src="<?php echo $global['webSiteRootURL']; ?>node_modules/jquery/dist/jquery.min.js"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>node_modules/jquery-ui-dist/jquery-ui.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-dialogextend/build/jquery.dialogextend.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>node_modules/sweetalert/dist/sweetalert.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>node_modules/js-cookie/dist/js.cookie.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>node_modules/jquery-toast-plugin/dist/jquery.toast.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/script.js" type="text/javascript"></script>
        <script>


            function iframeURLChange(iframe, callback) {
                var unloadHandler = function () {
                    // Timeout needed because the URL changes immediately after
                    // the `unload` event is dispatched.
                    setTimeout(function () {
                        console.log('iframe 1', iframe, iframe.contentWindow.location.href, iframe.contentDocument.title);
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

                if (src === webSiteRootURL + 'site' || src === webSiteRootURL + 'site/') {
                    src = webSiteRootURL;
                }

                window.history.pushState("", "", src);
            });

            function getDialogWidth() {
                var suggestedMinimumSize = 800;
                var x = $(window).width();
                var suggestedSize = x-100;
                if (suggestedSize > suggestedMinimumSize) {
                    x = suggestedSize;
                }
                return x;
            }

            function getDialogHeight() {
                var suggestedMinimumSize = 600;
                var x = $(window).height();
                var suggestedSize = x-100;
                if (suggestedSize > suggestedMinimumSize) {
                    x = suggestedSize;
                }
                return x;
            }

            var windowCount = 0;
            function openWindow(url, iframeAllowAttributes, title, maximize) {
                var id = 'window' + windowCount;
                var dialogSelector = '#' + id;
                windowCount++;
                var html = '<div id="' + id + '" title="' + title + '">';
                var iframeId = 'avideoWindowIframe' + id;
                html += '<iframe id="' + iframeId + '" name="' + iframeId + '" frameBorder="0" class="animate__animated animate__bounceInDown" src="' + url + '"  ' + iframeAllowAttributes + ' ></iframe>';
                html += '</div>';
                $('body').append(html);
                var w = getDialogWidth();
                var h = getDialogHeight();
                $(dialogSelector).dialog({
                    draggable: true,
                    autoOpen: true,
                    modal: false,
                    responsive: true,
                    width: w,
                    height: h,
                    minWidth: w / 2,
                    minHeight: h / 2,
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
                        close: "ui-icon-circle-close",
                        //close: "far fa-times-circle",
                        "maximize": "ui-icon-circle-plus",
                        "minimize": "ui-icon-circle-minus",
                        "collapse": "ui-icon-triangle-1-s",
                        "restore": "ui-icon-bullet"
                    }
                });
                if (maximize) {
                    $(dialogSelector).dialogExtend("maximize");
                }
                function iframeURLChange2(iframe, selector, callback) {
                    var unloadHandler = function () {
                        // Timeout needed because the URL changes immediately after
                        // the `unload` event is dispatched.
                        setTimeout(function () {
                            //console.log('iframe 1', iframe);
                            if (iframe.contentDocument) {
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

                iframeURLChange2(document.getElementById(iframeId), dialogSelector, function (selector, title) {
                    $(selector).dialog('option', 'title', title);
                });
                return iframeId;
                //addButtons($("." + id));
            }



            function openWindowWithPost(url, iframeAllowAttributes, params) {
                var name = openWindow("about:blank", iframeAllowAttributes, '', true);
                var form = document.createElement("form");
                form.setAttribute("method", "post");
                form.setAttribute("action", url);
                form.setAttribute("target", name);
                console.log('openWindowWithPost', name);
                for (var i in params) {
                    if (params.hasOwnProperty(i)) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = i;
                        input.value = params[i];
                        form.appendChild(input);
                    }
                }
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }

            $(document).ready(function () {
                //$("#window").draggable({handle: ".panel-heading", containment: "body"});
                //$("#window").resizable();      
            });
        </script>
    </body>
</html>
