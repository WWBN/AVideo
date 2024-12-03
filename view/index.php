<?php
//include dirname(__FILE__) . '/../view/firstPage.php';exit;
//var_dump($_SERVER);exit;
$configFile = dirname(__FILE__) . '/../videos/configuration.php';
//$doNotIncludeConfig = 1;
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    if (!file_exists('../install/index.php')) {
        forbiddenPage("No Configuration and no Installation");
    }
    header("Location: install/index.php");
    exit;
}

if(!empty($isStandAlone)){
    die('StandAlone Mode');
}

//require_once "{$global['systemRootPath']}objects/functions.php";

$paths = getIframePaths();
//var_dump(!useIframe(), isIframe());exit;
if (!useIframe() || isIframe() || !empty($_REQUEST['inMainIframe'])) {
    include $paths['path'];
    exit;
}

$postURL = $paths['url'];

$postURL = addQueryStringParameter($postURL, 'inMainIframe', 1);
//var_dump($_GET);exit;
//var_dump($postURL, $_REQUEST['inMainIframe']);exit;
//var_dump($_SERVER);exit;
?><!DOCTYPE html>
<html>
    <head>
        <script class="doNotSepareteTag" src="<?php echo getURL('view/js/swRegister.js'); ?>" type="text/javascript"></script>
        <title>Loading...</title>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
            function isASubIFrame() {
                return document.location.ancestorOrigins.length > 0 && typeof parent.isASubIFrame === 'function';
            }
            if (isASubIFrame()) {
                console.log('isASubIFrame', window.parent.document.location, document.location);
                window.parent.document.location = document.location;

            }
        </script>
        <link rel="shortcut icon" href="<?php echo getURL('videos/favicon.ico'); ?>" sizes="16x16,24x24,32x32,48x48,144x144">
        <link href="<?php echo getURL('view/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getURL('node_modules/jquery-ui-dist/jquery-ui.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getURL('node_modules/@fortawesome/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css"/>
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
            src="<?php echo getURL('view\index_loading.html'); ?>" id="mainIframe" name="mainIframe"></iframe>
        <form action="<?php echo $postURL; ?>" method="post" target="mainIframe" style="display: none;" id="mainIframeForm">
            <?php
            foreach ($_POST as $key => $value) {
                echo "<input type='hidden' name='{$key}' value=" . json_encode($value) . " />";
            }
            ?>
        </form>
        <script src="<?php echo getURL('node_modules/jquery/dist/jquery.min.js'); ?>"></script>
        <script src="<?php echo getURL('view/bootstrap/js/bootstrap.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('node_modules/sweetalert/dist/sweetalert.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('node_modules/js-cookie/dist/js.cookie.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('node_modules/jquery-toast-plugin/dist/jquery.toast.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('view/js/script.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('view/js/a2hs.js'); ?>" type="text/javascript"></script>
        <script>
            var avideoLoader = <?php echo json_encode(file_get_contents($global['systemRootPath'] . 'plugin/Layout/loaders/avideo.html'), JSON_UNESCAPED_UNICODE); ?>;
            function attachOnload() {
                var iframe = document.getElementById("mainIframe");
                var onLoadHandler = function () {
                    console.log('onLoadHandler');
                };
                iframe.contentWindow.removeEventListener("load", onLoadHandler);
                iframe.contentWindow.addEventListener("load", onLoadHandler);
            }

            function attachOnBeforeUnload() {
                var iframe = document.getElementById("mainIframe");
                var onBeforeUnloadHandler = function () {
                    console.log('onBeforeUnloadHandler');
                    modal.showPleaseWait();
                };
                iframe.contentWindow.removeEventListener("beforeunload", onBeforeUnloadHandler);
                iframe.contentWindow.addEventListener("beforeunload", onBeforeUnloadHandler);
            }

            function attachUnload() {
                var iframe = document.getElementById("mainIframe");
                var onUnLoadHandler = function () {
                    console.log('onUnLoadHandler');
                    iframeLoadIsDone();
                };
                iframe.contentWindow.removeEventListener("unload", onUnLoadHandler);
                iframe.contentWindow.addEventListener("unload", onUnLoadHandler);
            }

            attachUnload();
            attachOnload();
            attachOnBeforeUnload();
            var iframeLoadIsDoneTimeout;
            function iframeLoadIsDone() {
                clearTimeout(iframeLoadIsDoneTimeout);
                var iframe = document.getElementById("mainIframe");
                if (!iframe.contentDocument) {
                    iframeLoadIsDoneTimeout = setTimeout(function () {
                        iframeLoadIsDone();
                    }, 500);
                } else {
                    iframeLoadIsDoneTimeout = setTimeout(function () {
                        updatePageFromIframe();
                        console.log('reset Handler');
                        attachUnload();
                        attachOnload();
                        attachOnBeforeUnload();
                    }, 500);
                    modal.hidePleaseWait();
                }
            }

            function getIframeTitle() {
                return document.getElementById("mainIframe").contentDocument.title;
            }
            function getIframeSRC() {
                if (empty(document.getElementById("mainIframe").contentDocument)) {
                    return document.getElementById("mainIframe").src;
                } else {
                    return document.getElementById("mainIframe").contentDocument.location.href;
                }
            }

            function setIframeSRC(src) {
                return document.getElementById("mainIframe").src = src;
            }

            var updatePageFromIframeTimeout;
            function updatePageFromIframe() {
                clearTimeout(updatePageFromIframeTimeout);
                var title = getIframeTitle();
                var src = getIframeSRC();
                updatePage(title, src);
                //updatePageFromIframeTimeout = setTimeout(function(){updatePageFromIframe();}, 2000);
            }

            function updatePage(title, src) {
                updatePageTitle(title);
                updatePageSRC(src);
            }
            function updatePageTitle(title) {
                document.title = title;
            }
            function updatePageSRC(src) {
                var iframeSRC = getIframeSRC();
                if (src == 'about:blank') {
                    return false;
                }
                var mainPages = ['site', 'site/', 'view/index_firstPage.php'];

                for (var i in mainPages) {
                    var page = mainPages[i];
                    if (typeof page !== 'string') {
                        continue;
                    }
                    eval('var pattern = /' + replaceAll('/', '\\/') + page.replace('/', '\\/') + '.*/');
                    if (pattern.test(src)) {
                        src = webSiteRootURL;
                    }
                    if (pattern.test(iframeSRC)) {
                        iframeSRC = webSiteRootURL;
                    }
                }

                if (src !== iframeSRC) {
                    setIframeSRC(src);
                }
                src.replace(/inMainIframe=1&?/g, '');
                console.log('updatePageSRC', src, iframeSRC);
                window.history.pushState("", "", src);

                if (typeof parent.updatePageSRC == 'funciton') {
                    console.log('parent updatePageSRC', src);
                    parent.updatePageSRC(src);
                }
            }

            function getDialogWidth() {
                var suggestedMinimumSize = 800;
                var x = $(window).width();
                var suggestedSize = x - 100;
                if (suggestedSize > suggestedMinimumSize) {
                    x = suggestedSize;
                }
                return x;
            }

            function getDialogHeight() {
                var suggestedMinimumSize = 600;
                var x = $(window).height();
                var suggestedSize = x - 100;
                if (suggestedSize > suggestedMinimumSize) {
                    x = suggestedSize;
                }
                return x;
            }
            /*
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
                    //"collapsable": true,
                    "dblclick": "collapse",
                    "titlebar": "transparent",
                    "minimizeLocation": "right",
                    "icons": {
                        close: "ui-icon-close",
                        "maximize": "ui-icon-plus",
                        "minimize": "ui-icon-minus",
                        "collapse": "ui-icon-triangle-1-s",
                        "restore": "ui-icon-triangle-1-n"
                    }
                });
                if (maximize) {
                    $(dialogSelector).dialogExtend("maximize");
                }
                function iframeURLChange2(iframe, selector, callback) {
                    var onUnLoadHandler = function () {
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
                        // Remove the onLoadHandler in case it was already attached.
                        // Otherwise, the change will be dispatched twice.
                        iframe.contentWindow.removeEventListener("unload", onUnLoadHandler);
                        iframe.contentWindow.addEventListener("unload", onUnLoadHandler);
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
*/


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

            function avideoLoadPage(url) {
                modal.showPleaseWait();

                /*
                 var iframe = $('#mainIframe').clone();
                 $('#mainIframe').attr('id', 'oldMainIframe');
                 $(iframe).css({display:'none'});
                 $('body').append(iframe);

                 var oldMainIframe = $('#oldMainIframe');
                 if(oldMainIframe.length){
                 $(oldMainIframe).slideUp('fast', function () {
                 $(oldMainIframe).remove();
                 });
                 $('#mainIframe').slideDown();
                 }
                 */

                setIframeSRC(url);
            }

            $(document).ready(function () {
                $('#mainIframeForm').submit();
                //$("#window").draggable({handle: ".panel-heading", containment: "body"});
                //$("#window").resizable();
            });
        </script>
    </body>
</html>
