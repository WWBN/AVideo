<?php
$savedTheme = $config->getThemes();
$delay = 0.2;
$themes = getThemesSeparated();
?>
<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php echo __('Dark is Default'); ?>
                <div class="material-switch pull-right">
                    <input class="defaultTheme" data-toggle="toggle" type="checkbox" value="" id="defaultThemeDark" <?php echo ($savedTheme->defaultTheme == 'dark') ? "checked" : ""; ?>>
                    <label for="defaultThemeDark" class="label-primary"></label>
                </div>
            </div>
            <div class="panel-body">
                <?php
                foreach ($themes['dark'] as $fileEx) {
                ?>
                    <div class="col-xs-6  <?php echo getCSSAnimationClassAndStyle('animate__fadeInUp', 'themess', $delay); ?>">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?php echo ucfirst($fileEx); ?>
                                <div class="material-switch pull-right">
                                    <input class="themeSwitch isDarkTheme" data-toggle="toggle" type="checkbox" value="<?php echo ($fileEx); ?>" id="themeSwitch<?php echo ($fileEx); ?>" <?php echo ($fileEx == $savedTheme->dark) ? "checked" : ""; ?>>
                                    <label for="themeSwitch<?php echo ($fileEx); ?>" class="label-success"></label>
                                </div>
                            </div>
                            <div class="panel-body">
                                <img src="<?php echo $global['webSiteRootURL'], "view/css/custom/", $fileEx, ".png"; ?>" class="img-responsive">
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php echo __('Light is Default'); ?>
                <div class="material-switch pull-right">
                    <input class="defaultTheme" data-toggle="toggle" type="checkbox" value="" id="defaultThemeLight" <?php echo ($savedTheme->defaultTheme == 'light') ? "checked" : ""; ?>>
                    <label for="defaultThemeLight" class="label-primary"></label>
                </div>
            </div>
            <div class="panel-body">
                <?php
                foreach ($themes['light'] as $fileEx) {
                ?>
                    <div class="col-xs-6  <?php echo getCSSAnimationClassAndStyle('animate__fadeInUp', 'themess', $delay); ?>">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?php echo ucfirst($fileEx); ?>
                                <div class="material-switch pull-right">
                                    <input class="themeSwitch isLightTheme" data-toggle="toggle" type="checkbox" value="<?php echo ($fileEx); ?>" id="themeSwitch<?php echo ($fileEx); ?>" <?php echo ($fileEx == $savedTheme->light) ? "checked" : ""; ?>>
                                    <label for="themeSwitch<?php echo ($fileEx); ?>" class="label-success"></label>
                                </div>
                            </div>
                            <div class="panel-body">
                                <img src="<?php echo $global['webSiteRootURL'], "view/css/custom/", $fileEx, ".png"; ?>" class="img-responsive">
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
<script>
    function switchThemeDark(t) {
        $('.themeSwitch.isDarkTheme').not(t).prop('checked', false);
        saveTheme();
    }

    function switchThemeLight(t) {
        $('.themeSwitch.isLightTheme').not(t).prop('checked', false);
        saveTheme();
    }

    function saveTheme() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'admin/themeUpdate.json.php',
            data: {
                "themeLight": $('.themeSwitch.isLightTheme:checked').val(),
                "themeDark": $('.themeSwitch.isDarkTheme:checked').val(),
                "defaultTheme": $('#defaultThemeDark').is(':checked')?'dark':'light',
            },
            type: 'post',
            success: function(response) {
                avideoResponse(response);
                modal.hidePleaseWait();
            }
        });
    }

    $(document).ready(function() {
        $('.themeSwitch.isDarkTheme').change(function(e) {
            switchThemeDark(this);
        });
        $('.themeSwitch.isLightTheme').change(function(e) {
            switchThemeLight(this);
        });
        $('.defaultTheme').change(function(e) {
            $('.defaultTheme').not(this).prop('checked', false);
            saveTheme();
        });
    });
</script>