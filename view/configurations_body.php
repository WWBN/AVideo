<?php
if (User::isAdmin()) {
    ?>

    <form class="form-compact form-horizontal"  id="updateConfigForm" onsubmit="">
        <div class="panel panel-default "">
            <div class="panel-heading tabbable-line">

                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link " href="#tabTheme" data-toggle="tab">
                            <span class="fa fa-cog"></span>
                            <?php echo __("Themes"); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="#tabCompatibility" data-toggle="tab">
                            <span class="fa fa-cog"></span>
                            <?php echo __("Compatibility Check"); ?>
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link " href="#tabRegular" id="tabRegularLink" data-toggle="tab">
                            <span class="fa fa-cog"></span>
                            <?php echo __("Regular Configuration"); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="#tabAdvanced" data-toggle="tab">
                            <span class="fa fa-cogs"></span>
                            <?php echo __("Advanced Configuration"); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="#tabHead" data-toggle="tab">
                            <span class="fa fa-code"></span>
                            <?php echo __("Script Code"); ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="panel-body">
                <div class="tab-content clearfix">
                    <div class="tab-pane" id="tabTheme">
                        <div class="panel panel-default">
                            <div class="panel-heading"><h2><?php echo __("Themes"); ?></h2></div>
                            <div class="panel-body">

                                <h1 class="alert alert-warning">
                                    <span class="fa fa-warning"></span>
                                    <?php echo __("Do not forget to save after choose your theme"); ?>
                                </h1>
                                <div class="alert alert-info">
                                    <span class="fa fa-info-circle"></span>
                                    <?php echo __("We would like to thanks http://bootswatch.com/"); ?>
                                </div>
                                <div class="row">
                                    <div class="col-xs-4">
                                        <div class="panel panel-success">
                                            <div class="panel-heading"><?php echo __('Customize Your site colors'); ?> <div class="pull-right"><?php echo getPluginSwitch('Customize'); ?></div></div>
                                            <div class="panel-body">
                                                <img src="<?php echo $global['webSiteRootURL'], "view/css/custom/customize.png"; ?>" class="img-responsive">
                                                <?php
                                                if (!AVideoPlugin::exists('Customize')) {
                                                    ?>
                                                    <a href="https://youphp.tube/plugins/" class="btn btn-success btn-radio btn-block btn-xs" id=""> <i class="fas fa-palette"></i>  Purchase the Customize Plugin</a>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <a href="<?php echo $global['webSiteRootURL']; ?>admin/?page=design_colors" class="btn btn-success btn-radio btn-block btn-xs" id=""> <i class="fas fa-palette"></i>  Customize Colors</a>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>

                                    </div>
                                    <?php
                                    $themes = getThemes();
                                    $savedTheme = $config->getTheme();
                                    foreach ($themes as $fileEx) {
                                        if ($fileEx == $savedTheme) {
                                            ?>
                                            <script>
                                                $(document).ready(function () {
                                                    setTimeout(function () {
                                                        $("#btn<?php echo ($fileEx); ?>").trigger("click");
                                                    }, 1000);
                                                });
                                            </script>
                                            <?php
                                        }
                                        ?>
                                        <div class="col-xs-4">
                                            <div class="panel panel-default">
                                                <div class="panel-heading"><?php echo ucfirst($fileEx); ?></div>
                                                <div class="panel-body">

                                                    <img src="<?php echo $global['webSiteRootURL'], "view/css/custom/", $fileEx, ".png"; ?>" class="img-responsive img-radio">
                                                    <button type="button" class="btn btn-default btn-radio btn-block btn-xs" id="btn<?php echo ($fileEx); ?>"><?php echo ucfirst($fileEx); ?></button>
                                                    <input type="checkbox" value="<?php echo ($fileEx); ?>"  class="hidden left-item">
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
                    <div class="tab-pane" id="tabCompatibility">
                        <div class="alert alert-success">
                            <span class="fa fa-film"></span>
                            <strong><?php
                                $secondsTotal = getSecondsTotalVideosLength();
                                $seconds = $secondsTotal % 60;
                                $minutes = ($secondsTotal - $seconds) / 60;
                                printf(__("You are hosting %d minutes and %d seconds of video"), $minutes, $seconds);
                                ?></strong>
                            <?php
                            if (!empty($global['videoStorageLimitMinutes'])) {
                                $secondsLimit = $global['videoStorageLimitMinutes'] * 60;
                                if ($secondsLimit > $secondsTotal) {

                                    $percent = intval($secondsTotal / $secondsLimit * 100);
                                } else {
                                    $percent = 100;
                                }
                                ?> and you have <?php echo $global['videoStorageLimitMinutes']; ?> minutes of storage
                                <div class="progress">
                                    <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"
                                         aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%">
                                        <?php echo $percent; ?>% of your storage limit used
                                    </div>
                                </div>
                                <?php
                            }
                            ?>

                        </div>
                        <?php
                        if (isApache()) {
                            ?>
                            <div class="alert alert-success">
                                <span class="glyphicon glyphicon-check"></span>
                                <strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?> is Present</strong>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger">
                                <span class="glyphicon glyphicon-unchecked"></span>
                                <strong>Your server is <?php echo $_SERVER['SERVER_SOFTWARE']; ?>, you must install Apache</strong>
                            </div>
                            <?php
                        }
                        ?>


                        <?php
                        if (isPHP("5.6")) {
                            ?>
                            <div class="alert alert-success">
                                <span class="glyphicon glyphicon-check"></span>
                                <strong>PHP <?php echo PHP_VERSION; ?> is Present</strong>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger">
                                <span class="glyphicon glyphicon-unchecked"></span>
                                <strong>Your PHP version is <?php echo PHP_VERSION; ?>, you must install PHP 5.6.x or greater</strong>
                            </div>
                            <?php
                        }
                        ?>

                        <?php
                        if (checkVideosDir()) {
                            ?>
                            <div class="alert alert-success">
                                <span class="glyphicon glyphicon-check"></span>
                                <strong>Your videos directory is writable</strong>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger">
                                <span class="glyphicon glyphicon-unchecked"></span>
                                <strong>Your videos directory must be writable</strong>
                                <details>
                                    <?php
                                    $dir = getPathToApplication() . "videos";
                                    if (!file_exists($dir)) {
                                        ?>
                                        The video directory does not exists, AVideo had no permition to create it, you must create it manualy!
                                        <br>
                                        <pre><code>sudo mkdir <?php echo $dir; ?></code></pre>
                                        <?php
                                    }
                                    ?>
                                    <br>
                                    Then you can set the permissions.
                                    <br>
                                    <pre><code>sudo chmod -R 777 <?php echo $dir; ?></code></pre>
                                </details>
                            </div>
                            <?php
                        }
                        $pathToPHPini = php_ini_loaded_file();
                        if (empty($pathToPHPini)) {
                            $pathToPHPini = "/etc/php/7.0/cli/php.ini";
                        }
                        ?>

                        <?php
                        if (check_post_max_size()) {
                            ?>
                            <div class="alert alert-success">
                                <span class="glyphicon glyphicon-check"></span>
                                <strong>Your post_max_size is <?php echo ini_get('post_max_size'); ?></strong>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger">
                                <span class="glyphicon glyphicon-unchecked"></span>
                                <strong>Your post_max_size is <?php echo ini_get('post_max_size'); ?>, it must be at least 100M</strong>

                                <details>
                                    Edit the <code>php.ini</code> file
                                    <br>
                                    <pre><code>sudo nano <?php echo $pathToPHPini; ?></code></pre>
                                </details>
                            </div>
                            <?php
                        }
                        ?>

                        <?php
                        if (check_upload_max_filesize()) {
                            ?>
                            <div class="alert alert-success">
                                <span class="glyphicon glyphicon-check"></span>
                                <strong>Your upload_max_filesize is <?php echo ini_get('upload_max_filesize'); ?></strong>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger">
                                <span class="glyphicon glyphicon-unchecked"></span>
                                <strong>Your upload_max_filesize is <?php echo ini_get('upload_max_filesize'); ?>, it must be at least 100M</strong>

                                <details>
                                    Edit the <code>php.ini</code> file
                                    <br>
                                    <pre><code>sudo nano <?php echo $pathToPHPini; ?></code></pre>
                                </details>
                            </div>
                            <?php
                        }
                        ?>

                    </div>
                    <div class="tab-pane  active" id="tabRegular">

                        <div class="row">
                            <div class="col-md-6">

                                <div class="panel panel-default">
                                    <div class="panel-heading"><h2><?php echo __("Basic"); ?></h2></div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label"><?php echo __("Language"); ?></label>
                                            <div class="col-md-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-flag"></i></span>
                                                    <?php
                                                    echo Layout::getLangsSelect('inputLanguage', $config->getLanguage(), 'inputLanguage');
                                                    ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
                                            <div class="col-md-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                                    <input  id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" class="form-control"  type="email"  value="<?php echo $config->getContactEmail(); ?>" >
                                                </div>
                                                <small class="form-text text-muted"><?php echo __("This e-mail will be used for this web site notifications"); ?></small>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-4 control-label"><?php echo __("Authenticated users can upload videos"); ?></label>
                                            <div class="col-md-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fas fa-cloud-upload-alt"></i></span>
                                                    <select class="form-control" id="authCanUploadVideos" >
                                                        <option value="1" <?php echo ($config->getAuthCanUploadVideos() == 1) ? "selected" : ""; ?>><?php echo __("Yes"); ?></option>
                                                        <option value="0" <?php echo ($config->getAuthCanUploadVideos() == 0) ? "selected" : ""; ?>><?php echo __("No"); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-4 control-label"><?php echo __("Authenticated users can view chart"); ?></label>
                                            <div class="col-md-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fas fa-chart-bar"></i></span>
                                                    <select class="form-control" id="authCanViewChart" >
                                                        <option value="0" <?php echo ($config->getAuthCanViewChart() == 0) ? "selected" : ""; ?>><?php echo __("For uploaders"); ?></option>
                                                        <option value="1" <?php echo ($config->getAuthCanViewChart() == 1) ? "selected" : ""; ?>><?php echo __("For selected, admin view"); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-4 control-label"><?php echo __("Authenticated users can comment videos"); ?></label>
                                            <div class="col-md-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fas fa-comments"></i></span>

                                                    <select class="form-control" id="authCanComment"  >
                                                        <option value="1" <?php echo ($config->getAuthCanComment() == 1) ? "selected" : ""; ?>><?php echo __("Yes"); ?></option>
                                                        <option value="0" <?php echo ($config->getAuthCanComment() == 0) ? "selected" : ""; ?>><?php echo __("No"); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-4  control-label">
                                                <?php echo __("Autoplay Video on Load Page"); ?>
                                                <a href="https://github.com/WWBN/AVideo/wiki/Autoplay-and-Browser-Policies"><?php echo __("Help"); ?></a>
                                            </label>
                                            <div class="col-md-8">
                                                <div class="material-switch">
                                                    <input data-toggle="toggle" type="checkbox" name="autoplay" id="autoplay" value="1" <?php
                                                    if (!empty($config->getAutoplay())) {
                                                        echo "checked";
                                                    }
                                                    ?> >
                                                    <label for="autoplay" class="label-primary"></label>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><h2><?php echo __("Logo and Title"); ?></h2></div>
                                    <div class="panel-body">

                                        <div class="form-group">
                                            <label class="col-md-4 control-label"><?php echo __("Web site title"); ?></label>
                                            <div class="col-md-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-globe"></i></span>
                                                    <input  id="inputWebSiteTitle" placeholder="<?php echo __("Web site title"); ?>" class="form-control"  type="text"  value="<?php echo $config->getWebSiteTitle(); ?>" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">
                                                <?php echo __("Your Logo"); ?> (250x70)
                                            </label>
                                            <div class="col-md-8 ">
                                                <div id="croppieLogo"></div>
                                                <a id="logo-btn" class="btn btn-default btn-xs btn-block"><?php echo __("Choose a logo"); ?></a>
                                            </div>
                                            <input type="file" id="logo" value="Choose a Logo" accept="image/*" style="display: none;" />
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">
                                                <?php echo __("Favicon"); ?> (180x180)
                                            </label>
                                            <div class="col-md-8 ">
                                                <div id="croppieFavicon"></div>
                                                <a id="favicon-btn" class="btn btn-default btn-xs btn-block"><?php echo __("Choose a favicon"); ?></a>
                                            </div>
                                            <input type="file" id="favicon" value="Choose a favicon" accept="image/*" style="display: none;" />
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tabAdvanced">
                        <?php
                        if (empty($global['disableAdvancedConfigurations'])) {
                            ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><h2><?php echo __("Advanced Configuration"); ?></h2></div>
                                        <div class="panel-body">

                                            <div class="form-group">
                                                <div class="col-md-12">
                                                    <button class="btn btn-danger" id="clearCache">
                                                        <i class="fa fa-trash"></i> <?php echo __("Clear Cache Directory"); ?>
                                                    </button>
                                                    <button class="btn btn-primary" id="generateSiteMap">
                                                        <i class="fa fa-sitemap"></i> <?php echo __("Generate Sitemap"); ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Encoder URL"); ?></label>
                                                <div class="col-md-8">
                                                    <input id="encoder_url" aria-describedby="encoder_urlHelp" class="form-control"  type="url" value="<?php echo $config->_getEncoderURL(); ?>" >
                                                    <small id="encoder_urlHelp" class="form-text text-muted">
                                                        <?php echo __("You need to set up an encoder server"); ?><br>
                                                        <?php echo __("You can use our public encoder on"); ?>: https://encoder1.avideo.com/ or
                                                        <a href="https://github.com/WWBN/AVideo-Encoder" class="btn btn-default btn-xs" target="_blank" rel="noopener noreferrer"><?php echo __("For faster encode, download your own encoder"); ?></a>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Session Timeout in seconds"); ?></label>
                                                <div class="col-md-8">
                                                    <input id="session_timeout" class="form-control"  type="number" value="<?php echo $config->getSession_timeout(); ?>" >
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Disable AVideo Google Analytics"); ?></label>
                                                <div class="col-md-8">
                                                    <div class="material-switch">
                                                        <input data-toggle="toggle" type="checkbox" name="disable_analytics" id="disable_analytics" value="1" <?php
                                                        if (!empty($config->getDisable_analytics())) {
                                                            echo "checked";
                                                        }
                                                        ?>  aria-describedby="disable_analyticsHelp">
                                                        <label for="disable_analytics" class="label-success"></label>
                                                    </div>
                                                    <small id="disable_analyticsHelp" class="form-text text-muted"><?php echo __("This help us to track and dettect errors"); ?></small>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Disable Youtube-Upload"); ?></label>
                                                <div class="col-md-8">

                                                    <div class="material-switch">
                                                        <input data-toggle="toggle" type="checkbox" name="disable_youtubeupload" id="disable_youtubeupload" value="1" <?php
                                                        if (!empty($config->getDisable_youtubeupload())) {
                                                            echo "checked";
                                                        }
                                                        ?> >
                                                        <label for="disable_youtubeupload" class="label-success"></label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Allow download video"); ?></label>
                                                <div class="col-md-8">
                                                    <div class="material-switch">
                                                        <input data-toggle="toggle" type="checkbox" name="disable_rightclick" id="allow_download" value="1" <?php
                                                        if (!empty($config->getAllow_download())) {
                                                            echo "checked";
                                                        }
                                                        ?> aria-describedby="allow_downloadHelp">
                                                        <label for="allow_download" class="label-success"></label>
                                                    </div>
                                                    <small id="allow_downloadHelp" class="form-text text-muted"><?php echo __("This creates a download-button under your video, suggest you title.mp4 as download-name."); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-6">

                                    <div class="panel panel-default">
                                        <div class="panel-heading"><h1><i class="fas fa-at"></i> Email Configuration</h1></div>
                                        <div class="panel-body">

                                            <div class="alert alert-warning">
                                                <h3>
                                                    <i class="fas fa-info-circle"></i>
                                                    <?php echo __('If you are not sure how to configure your email'); ?>,
                                                    <?php echo __('please try'); ?> <a href="https://github.com/WWBN/AVideo/wiki/Setting-up-AVideo-Platform-to-send-emails" target="_blank" rel="noopener noreferrer" ><?php echo __('this help'); ?></a>
                                                </h3>
                                            </div>


                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Enable SMTP"); ?></label>
                                                <div class="col-md-8">
                                                    <div class="material-switch">
                                                        <input data-toggle="toggle" type="checkbox" name="enableSmtp" id="enableSmtp" value="1" <?php
                                                        if (!empty($config->getSmtp())) {
                                                            echo "checked";
                                                        }
                                                        ?> >
                                                        <label for="enableSmtp" class="label-success"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Enable SMTP Auth"); ?></label>
                                                <div class="col-md-8">
                                                    <div class="material-switch">
                                                        <input data-toggle="toggle" type="checkbox" name="enableSmtpAuth" id="enableSmtpAuth" value="1" <?php
                                                        if (!empty($config->getSmtpAuth())) {
                                                            echo "checked";
                                                        }
                                                        ?> >
                                                        <label for="enableSmtpAuth" class="label-success"></label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("SMTP Secure"); ?></label>
                                                <div class="col-md-8">
                                                    <input id="smtpSecure" class="form-control"  type="text" value="<?php echo $config->getSmtpSecure(); ?>" placeholder="<?php echo __('tls OR ssl'); ?>" aria-describedby="smtpSecureHelp"    >
                                                    <small id="smtpSecureHelp" class="form-text text-muted"><?php echo __("Use tls OR ssl"); ?></small>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("SMTP Port"); ?></label>
                                                <div class="col-md-8">
                                                    <input id="smtpPort" class="form-control"  type="number" value="<?php echo $config->getSmtpPort(); ?>" placeholder="<?php echo __('465 OR 587'); ?>" aria-describedby="smtpPortHelp"    >
                                                    <small id="smtpPortHelp" class="form-text text-muted"><?php echo __("465 OR 587"); ?></small>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("SMTP Host"); ?></label>
                                                <div class="col-md-8">
                                                    <input id="smtpHost" class="form-control"  type="text" value="<?php echo $config->getSmtpHost(); ?>" placeholder="smtp.gmail.com" >
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("SMTP Username"); ?></label>
                                                <div class="col-md-8">
                                                    <input id="smtpUsername" class="form-control"  type="text" value="<?php echo $config->getSmtpUsername(); ?>" placeholder="email@gmail.com" >
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("SMTP Password"); ?></label>
                                                <div class="col-md-8">
                                                    <?php
                                                    getInputPassword("smtpPassword", 'class="form-control" value="' . $config->getSmtpPassword() . '"', __("SMTP Password"));
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Test your email"); ?></label>
                                                <div class="col-md-8">
                                                    <span class="btn btn-warning btn-block" id="testEmail" ><?php echo __("Test Email"); ?> <span class="glyphicon glyphicon-send"></span></span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>


                            <?php
                        } else {
                            ?>
                            <h2 class="alert alert-danger"><?php echo __("Advanced configurations are disabled"); ?></h2>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="tab-pane" id="tabHead">
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo __("Head Code"); ?></label>
                            <div class="col-md-10">
                                <link rel="stylesheet" href="<?php echo getCDN(); ?>view/js/codemirror/lib/codemirror.css">
                                <script src="<?php echo getCDN(); ?>view/js/codemirror/lib/codemirror.js"></script>
                                <script src="<?php echo getCDN(); ?>view/js/codemirror/mode/xml/xml.js"></script>
                                <script src="<?php echo getCDN(); ?>view/js/codemirror/mode/css/css.js"></script>
                                <script src="<?php echo getCDN(); ?>view/js/codemirror/mode/javascript/javascript.js"></script>
                                <script src="<?php echo getCDN(); ?>view/js/codemirror/mode/htmlmixed/htmlmixed.js"></script>
                                <script>
                                    (function($) {
										$(document).ready(function() {

											var editor,
											    head = document.getElementById("head");

											$("li a[href='#tabHead']").on("click", function() {
												if (!editor && head) {
													setTimeout(function() {
														editor = CodeMirror.fromTextArea(head, {
															lineNumbers: true,
															mode: "htmlmixed"
														});
														editor.on('change', function() {
															editor.save();
														});
													}, 10);
												}
											});

										});
                                    })(jQuery);
                                </script>
                                <textarea id="head" class="form-control" type="text" rows="20" ><?php echo $config->getHead(); ?></textarea>
                                <small><?php echo __('For Google Analytics code'); ?>: <a href='https://analytics.google.com'  target="_blank" rel="noopener noreferrer">https://analytics.google.com</a></small><br>
                                <small><?php echo __('Leave blank for native code'); ?></small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo __("Google Ad Sense"); ?></label>
                            <div class="col-md-10">
                                <input type="hidden" value="" id="adsense"/>
                                <div class="alert alert-info">
                                    <?php echo __('Google AD Sense and any other Ads provider are moved to the'); ?> <a href='<?php echo $global['webSiteRootURL']; ?>plugins'><?php echo __('ADs plugin'); ?> </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-block btn-primary btn-lg" ><?php echo __("Save"); ?> <span class="fa fa-save"></span></button>
            </div>
        </div>

    </form>

    <script>
        var logoCrop;
        var logoSmallCrop;
        var theme;
        function readFile(input, c) {
            console.log("read file");
            if ($(input)[0].files && $(input)[0].files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    c.croppie('bind', {
                        url: e.target.result
                    }).then(function () {
                        console.log('jQuery bind complete');
                    });

                }

                reader.readAsDataURL($(input)[0].files[0]);
            } else {
                avideoAlert("Sorry - you're browser doesn't support the FileReader API");
            }
        }

        var logoImgBase64;
        var logoSmallImgBase64;

        $(document).ready(function () {

            $('#btnReloadCapcha').click(function () {
                $('#captcha').attr('src', '<?php echo $global['webSiteRootURL']; ?>captcha?' + Math.random());
                $('#captchaText').val('');
            });

            $('#testEmail').click(function (evt) {
                evt.preventDefault();
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/sendEmail.json.php',
                    data: {
                        captcha: $('#captchaText').val(),
                        first_name: "Your Site test",
                        email: "<?php echo $config->getContactEmail(); ?>",
                        website: "www.avideo.com",
                        comment: "Teste of comment"
                    },
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                        if (!response.error) {
                            avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your message has been sent!"); ?>", "success");

                            $("#contact_form").hide();
                            $("#messageSuccess").fadeIn();
                        } else {
                            avideoAlert("<?php echo __("Your message could not be sent!"); ?>", response.error, "error");
                        }
                        $('#btnReloadCapcha').trigger('click');
                    }
                });
                return false;
            });

            // start croppie logo
            $('#logo').on('change', function () {
                readFile(this, logoCrop);
            });
            $('#logo-btn').on('click', function (ev) {
                $('#logo').trigger("click");
            });

            // start croppie logo
            $('#favicon').on('change', function () {
                readFile(this, faviconCrop);
            });
            $('#favicon-btn').on('click', function (ev) {
                $('#favicon').trigger("click");
            });

            $('#logo-result-btn').on('click', function (ev) {
                logoCrop.croppie('result', {
                    type: 'canvas',
                    size: 'viewport'
                }).then(function (resp) {

                });
            });

            logoCrop = $('#croppieLogo').croppie({
                url: '<?php echo $global['webSiteRootURL'], $config->getLogo(true); ?>',
                enableExif: true,
                enforceBoundary: false,
                mouseWheelZoom: false,
                viewport: {
                    width: 250,
                    height: 70
                },
                boundary: {
                    width: 250,
                    height: 70
                }
            });
            setTimeout(function () {
                logoCrop.croppie('setZoom', 1);
            }, 1000);


            $('#favicon-result-btn').on('click', function (ev) {
                faviconCrop.croppie('result', {
                    type: 'canvas',
                    size: 'viewport'
                }).then(function (resp) {

                });
            });

            faviconCrop = $('#croppieFavicon').croppie({
                url: '<?php echo $config->getFavicon(true); ?>',
                enableExif: true,
                enforceBoundary: false,
                mouseWheelZoom: false,
                viewport: {
                    width: 180,
                    height: 180
                },
                boundary: {
                    width: 180,
                    height: 180
                }
            });
            setTimeout(function () {
                faviconCrop.croppie('setZoom', 1);
            }, 1000);



            $('#updateConfigForm').submit(function (evt) {
                evt.preventDefault();
                modal.showPleaseWait();
                $('#tabRegularLink').tab('show');

                logoCrop.croppie('result', {
                    type: 'canvas',
                    size: 'viewport'
                }).then(function (resp) {
                    logoImgBase64 = resp;

                    faviconCrop.croppie('result', {
                        type: 'canvas',
                        size: 'viewport'
                    }).then(function (resp) {
                        faviconBase64 = resp;

                        $.ajax({
                            url: '<?php echo $global['webSiteRootURL']; ?>objects/configurationUpdate.json.php',
                            data: {
                                "logoImgBase64": logoImgBase64,
                                "faviconBase64": faviconBase64,
                                "video_resolution": $('#inputVideoResolution').val(),
                                "webSiteTitle": $('#inputWebSiteTitle').val(),
                                "language": $('#inputLanguage').val(),
                                "contactEmail": $('#inputEmail').val(),
                                "authCanUploadVideos": $('#authCanUploadVideos').val(),
                                "authCanViewChart": $('#authCanViewChart').val(),
                                "authCanComment": $('#authCanComment').val(),
                                "head": $('#head').val(),
                                "adsense": $('#adsense').val(),
                                "disable_analytics": $('#disable_analytics').prop("checked"),
                                "disable_youtubeupload": $('#disable_youtubeupload').prop("checked"),
                                "allow_download": $("#allow_download").prop("checked"),
                                "session_timeout": $('#session_timeout').val(),
                                "autoplay": $('#autoplay').prop("checked"),
                                "theme": theme,
                                "smtp": $('#enableSmtp').prop("checked"),
                                "smtpAuth": $('#enableSmtpAuth').prop("checked"),
                                "smtpSecure": $('#smtpSecure').val(),
                                "smtpHost": $('#smtpHost').val(),
                                "smtpUsername": $('#smtpUsername').val(),
                                "smtpPassword": $('#smtpPassword').val(),
                                "smtpPort": $('#smtpPort').val(),
                                "encoder_url": $('#encoder_url').val(),
                            },
                            type: 'post',
                            success: function (response) {
                                if (response.status === "1") {
                                    avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your configurations has been updated!"); ?>", "success");
                                } else {
                                    avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your configurations has NOT been updated!"); ?>", "error");
                                }
                                modal.hidePleaseWait();
                            }
                        });
                    });
                });

            });

            $('.btn-radio').click(function (e) {
                $('.btn-radio').not(this).removeClass('active')
                        .siblings('input').prop('checked', false)
                        .siblings('.img-radio').css('opacity', '0.5');
                $(this).addClass('active')
                        .siblings('input').prop('checked', true)
                        .siblings('.img-radio').css('opacity', '1');
                var cssName = $(this).addClass('active').siblings('input').val();
                $("#theme").attr("href", "<?php echo $global['webSiteRootURL'] ?>css/custom/" + cssName + ".css");
                $('.btn-radio').parent("div").removeClass('bg-success');
                $(this).addClass('active').parent("div").addClass("bg-success");
                theme = cssName;
            });
        });
    </script>
    <?php
}
?>
