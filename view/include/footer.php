<?php
$footerjs = "";
$fileUpdates = thereIsAnyUpdate();
if (!empty($fileUpdates)) {
    $footerjs .= "$.toast({
    heading: 'Update required',
    text: '<a href=\"" . $global['webSiteRootURL'] . "update\">" . __('You have a new version to install') . "</a>',
    showHideTransition: 'plain',
    icon: 'error',
    hideAfter: 20000
});";
    //$footerjs .= 'var filesToUpdate='.json_encode($fileUpdates).';';
}
if (empty($advancedCustom)) {
    $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
}
?>
<div class="clearfix"></div>
<footer style="<?php echo $advancedCustom->footerStyle; ?> display: none;" id="mainFooter">
    <?php
    $custom = "";
    $extraPluginFile = $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';
    if (file_exists($extraPluginFile) && AVideoPlugin::isEnabled("c4fe1b83-8f5a-4d1b-b912-172c608bf9e3")) {
        require_once $extraPluginFile;
        $ec = new ExtraConfig();
        $custom = $ec->getFooter();
    }
    if (empty($custom)) {
        ?>
        <ul class="list-inline">
            <li>
                Powered by AVideo ® Platform v<?php echo $config->getVersion(); ?>
            </li>
        </ul>
        <?php
    } else {
        echo $custom;
    }
    ?>
</footer>
<script>
    $(function () {
<?php
showAlertMessage();
?>
    });
</script>
<script src="<?php echo getURL('view/js/jquery.lazy/jquery.lazy.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('view/js/jquery.lazy/jquery.lazy.plugins.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('view/js/script.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('node_modules/jquery-ui-dist/jquery-ui.min.js'); ?>" type="text/javascript"></script>
<?php
include $global['systemRootPath'] . 'view/include/bootstrap.js.php';
?>
<?php
$jsFiles = array();
//$jsFiles[] = "view/js/jquery.lazy/jquery.lazy.min.js";
//$jsFiles[] = "view/js/jquery.lazy/jquery.lazy.plugins.min.js";
//$jsFiles[] = "view/js/script.js";
$jsFiles[] = "node_modules/sweetalert/dist/sweetalert.min.js";
$jsFiles[] = "view/js/bootpag/jquery.bootpag.min.js";
$jsFiles[] = "view/js/bootgrid/jquery.bootgrid.js";
$jsFiles[] = "view/bootstrap/bootstrapSelectPicker/js/bootstrap-select.min.js";
//$jsFiles[] = "view/js/bootstrap-toggle/bootstrap-toggle.min.js";
$jsFiles[] = "view/js/jquery.bootstrap-autohidingnavbar.min.js";
$jsFiles[] = "view/css/flagstrap/js/jquery.flagstrap.min.js";
$jsFiles[] = "view/js/webui-popover/jquery.webui-popover.min.js";
$jsFiles[] = "view/js/bootstrap-list-filter/bootstrap-list-filter.min.js";
$jsFiles[] = "view/js/js-cookie/js.cookie.js";
$jsFiles[] = "view/js/jquery-toast/jquery.toast.min.js";
$jsFiles[] = "view/bootstrap/jquery-bootstrap-scrolling-tabs/jquery.scrolling-tabs.min.js";
$jsFiles[] = "view/js/BootstrapMenu.min.js";

$jsFiles = array_merge($jsFiles, AVideoPlugin::getJSFiles());
echo combineFilesHTML($jsFiles, "js", true);
?>
<div id="pluginFooterCode" >
    <?php
    if (!isForbidden()) {
        echo AVideoPlugin::getFooterCode();
    }
    ?>
</div>
<?php
if (isset($_SESSION['savedQuerys'])) {
    echo "<!-- Saved querys: " . $_SESSION['savedQuerys'] . " -->";
}
if (!empty($advancedCustom->footerHTMLCode->value)) {
    echo $advancedCustom->footerHTMLCode->value;
}

if (isFirstPage()) {
    echo '<script src="' . (getCDN() . 'view/js/a2hs.js?' . filectime("{$global['systemRootPath']}view/js/a2hs.js")) . '" type="text/javascript"></script>';
}
?>
<script>
    var checkFooterTimout;
    $(function () {
        checkFooter();

        $(window).scroll(function () {
            clearTimeout(checkFooterTimout);
            checkFooterTimout = setTimeout(function () {
                checkFooter();
            }, 100);
        });
        $(window).resize(function () {
            clearTimeout(checkFooterTimout);
            checkFooterTimout = setTimeout(function () {
                checkFooter();
            }, 100);
        });

        $(window).mouseup(function () {
            clearTimeout(checkFooterTimout);
            checkFooterTimout = setTimeout(function () {
                checkFooter();
            }, 100);
        });

<?php echo $footerjs; ?>

    });
    function checkFooter() {
        $("#mainFooter").fadeIn();
        if (getPageHeight() <= $(window).height()) {
            clearTimeout(checkFooterTimout);
            checkFooterTimout = setTimeout(function () {
                checkFooter();
            }, 1000);
            $("#mainFooter").css("position", "fixed");
        } else {
            $("#mainFooter").css("position", "relative");
        }
    }


    function getPageHeight() {
        var mainNavBarH = 0;
        if ($('#mainNavBar').length) {
            mainNavBarH = $('#mainNavBar').height();
        }
        var mainFooterH = 0;
        if ($('#mainFooter').length) {
            mainFooterH = $('#mainFooter').height();
        }
        var containerH = getLargerContainerHeight();
        return mainNavBarH + mainFooterH + containerH;
    }

    function getLargerContainerHeight() {
        var conteiners = $('body > .container,body >  .container-fluid');
        var height = 0;
        for (var item in conteiners) {
            if (isNaN(item)) {
                continue;
            }
            var h = $(conteiners[item]).height();
            if (h > height) {
                height = h;
            }
        }
        return height;
    }

</script>
<!--
<?php
/*
  if (User::isAdmin() && !empty($getCachesProcessed) && is_array($getCachesProcessed)) {
  arsort($getCachesProcessed);
  echo "Total cached methods " . PHP_EOL;
  foreach ($getCachesProcessed as $key => $value) {
  echo "$key => $value" . PHP_EOL;
  }
  }
 * 
 */
if (!empty($config) && is_object($config)) {
    echo PHP_EOL . 'v:' . $config->getVersion() . PHP_EOL;
}
if (!empty($global['rowCount'])) {
    echo PHP_EOL . "rowCount: {$global['rowCount']}";
}
?>
-->