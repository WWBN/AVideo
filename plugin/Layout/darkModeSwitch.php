<?php
$checked = '';
if (!empty($_COOKIE['themeMode'])) {
    $checked = 'checked';
}
?>
<div class="clearfix" style="padding: 5px;">
    <label for="themeMode" class="row-label singleLineMenu hideIfCompressed pull-left">
        <?php
        if ($config->isDefaultThemeDark()) {
            echo '<i class="fa-solid fa-sun"></i> ';
            echo __('Light Mode');
        } else {
            echo '<i class="fa-solid fa-moon"></i> ';
            echo __('Dark Mode');
        }
        ?>
    </label>
    <div class="material-switch pull-right">
        <input type="checkbox" value="1" id="themeMode" <?php echo $checked; ?> onchange="toogleThemeMode();">
        <label for="themeMode" class="label-success"></label>
    </div>
</div>
<script>
    function toogleThemeMode() {
        var themeMode = Cookies.get('themeMode');
        var isEmptythemeMode = empty(themeMode);
        Cookies.set('themeMode', isEmptythemeMode ? 1 : 0, {
            path: '/',
            expires: 365
        });

        loadTheme();
    }

    function loadTheme() {
        var themeMode = Cookies.get('themeMode');
        var isEmptythemeMode = empty(themeMode);
        var customCSSCookie = Cookies.get('customCSS');
        var defaultTheme = '<?php echo $config->getDefaultTheme(); ?>';
        var alternativeTheme = '<?php echo $config->getAlternativeTheme(); ?>';
        var themeName = defaultTheme;
        if (!isEmptythemeMode) {
            themeName = alternativeTheme;
        }
        // customCSS cookie (set by the theme switcher) takes priority,
        // matching the same logic as getCurrentTheme() on the PHP side.
        if (!empty(customCSSCookie)) {
            themeName = customCSSCookie;
        }
        $('#themeMode').prop('checked', !isEmptythemeMode);
        $('#customCSS').attr('href', webSiteRootURL + 'view/css/custom/' + themeName + '.css');

        // Manage the dark.css overlay separately (uses id="darkThemeCSS")
        var darkOverlayHref = webSiteRootURL + 'view/css/dark.css';
        if (!isEmptythemeMode) {
            if ($('#darkThemeCSS').length === 0) {
                $('head').append('<link id="darkThemeCSS" rel="stylesheet" type="text/css" href="' + darkOverlayHref + '" />');
            } else {
                $('#darkThemeCSS').attr('href', darkOverlayHref);
            }
        } else {
            $('#darkThemeCSS').attr('href', '');
        }
    }
    $(document).ready(function() {
        loadTheme();
    });

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            loadTheme();
        }
    });
</script>
