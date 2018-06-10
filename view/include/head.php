<?php
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
$head = YouPHPTubePlugin::getHeadCode();
$custom = "The Best YouTube Clone Ever - YouPHPTube";
if (YouPHPTubePlugin::isEnabled("c4fe1b83-8f5a-4d1b-b912-172c608bf9e3")) {
    require_once $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';
    $ec = new ExtraConfig();
    $custom = $ec->getDescription();
}
$theme = $config->getTheme();
?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?php echo $custom; ?>">
<link rel="icon" href="<?php echo $global['webSiteRootURL']; ?>img/favicon.png">
<link href="<?php echo $global['webSiteRootURL']; ?>bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>css/font-awesome-5.0.10/web-fonts-with-css/css/fontawesome-all.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>css/flagstrap/css/flags.css" rel="stylesheet" type="text/css"/>
<?php
$cssFiles = array();
//$cssFiles[] = "{$global['webSiteRootURL']}bootstrap/css/bootstrap.css";
$cssFiles[] = "view/js/seetalert/sweetalert.css";
$cssFiles[] = "view/bootstrap/bootstrapSelectPicker/css/bootstrap-select.min.css";
//$cssFiles[] = "{$global['webSiteRootURL']}css/flagstrap/css/flags.css";
$cssFiles[] = "view/js/bootgrid/jquery.bootgrid.css";
$cssFiles[] = "view/css/custom/{$theme}.css";
$cssFiles[] = "view/css/main.css";
//$cssFiles[] = "{$global['webSiteRootURL']}css/font-awesome-4.7.0/css/font-awesome.min.css";
$cssFiles[] = "view/js/bootstrap-toggle/bootstrap-toggle.min.css";
$cssFiles = array_merge($cssFiles, YouPHPTubePlugin::getCSSFiles());
$cssURL = combineFiles($cssFiles, "css");
?>
<link href="<?php echo $cssURL; ?>" rel="stylesheet" type="text/css"/>
<script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-3.3.1.min.js"></script>
<script>
    var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
</script>
<?php
if (!$config->getDisable_analytics()) {
    ?>
    <script>
        // YouPHPTube Analytics
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

        ga('create', 'UA-96597943-1', 'auto', 'youPHPTube');
        ga('youPHPTube.send', 'pageview');
    </script>
    <?php
}
echo $config->getHead();
echo $head;
if (!empty($video)) {
    $user = new User($video['users_id']);
    echo $user->getAnalytics();
}
?>
