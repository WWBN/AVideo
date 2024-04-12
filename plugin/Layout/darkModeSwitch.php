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
        var themeName = '<?php echo $config->getDefaultTheme(); ?>';
        if (!isEmptythemeMode) {
            themeName = '<?php echo $config->getAlternativeTheme(); ?>';
        }

        $('#themeMode').prop('checked', !isEmptythemeMode);
        $('#customCSS').attr('href', webSiteRootURL + 'view/css/custom/' + themeName + '.css');
    }
    $(document).ready(function() {
        loadTheme();
    });
</script>