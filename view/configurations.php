<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
//var_dump($config);exit;
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Configuration"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/Croppie/croppie.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/Croppie/croppie.min.js" type="text/javascript"></script>
        <style>
            .img-radio {
                opacity: 0.5;
                margin-bottom: 5px;
            }
        </style>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
            <?php
            if (User::isAdmin()) {
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <form class="form-compact well form-horizontal"  id="updateConfigForm" onsubmit="">

                            <div class="tabbable-panel">
                                <div class="tabbable-line">
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
                                    <div class="tab-content clearfix">
                                        <div class="tab-pane" id="tabTheme">
                                            <fieldset>
                                                <legend><?php echo __("Themes"); ?></legend>
                                                <h1 class="alert alert-warning">
                                                    <span class="fa fa-warning"></span>
                                                    <?php echo __("Do not forget to save after choose your theme"); ?>
                                                </h1>
                                                <div class="alert alert-info">
                                                    <span class="fa fa-info-circle"></span>
                                                    <?php echo __("We would like to thanks http://bootswatch.com/"); ?>
                                                </div>
                                                <?php
                                                foreach (glob("{$global['systemRootPath']}view/css/custom/*.css") as $filename) {
                                                    //echo "$filename size " . filesize($filename) . "\n";
                                                    $file = basename($filename);         // $file is set to "index.php"
                                                    $fileEx = basename($filename, ".css"); // $file is set to "index"
                                                    $savedTheme = $config->getTheme();
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
                                                    <div class="col-xs-4" style="padding: 10px;">
                                                        <img src="<?php echo $global['webSiteRootURL'], "view/css/custom/", $fileEx, ".png"; ?>" class="img-responsive img-radio">
                                                        <button type="button" class="btn btn-default btn-radio btn-block btn-xs" id="btn<?php echo ($fileEx); ?>"><?php echo ucfirst($fileEx); ?></button>
                                                        <input type="checkbox" value="<?php echo ($fileEx); ?>"  class="hidden left-item">
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </fieldset>
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
                                            if (modRewriteEnabled()) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong>Mod Rewrite module is Present</strong>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>Mod Rewrite is not enabled</strong>
                                                    <details>
                                                        In order to use mod_rewrite you can type the following command in the terminal:<br>
                                                        <pre><code>a2enmod rewrite</code></pre><br>
                                                        Restart apache2 after<br>
                                                        <pre><code>/etc/init.d/apache2 restart</code></pre>
                                                    </details>
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
                                                            The video directory does not exists, YouPHPTube had no permition to create it, you must create it manualy!
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
                                            <fieldset>
                                                <legend><?php echo __("Update the site configuration"); ?></legend>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">
                                                        <?php echo __("Your Logo"); ?>
                                                    </label>
                                                    <div class="col-md-8 ">
                                                        <div id="croppieLogo"></div>
                                                        <a id="logo-btn" class="btn btn-default btn-xs btn-block"><?php echo __("Upload a logo"); ?></a>
                                                    </div>
                                                    <input type="file" id="logo" value="Choose a Logo" accept="image/*" style="display: none;" />
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">
                                                        <?php echo __("Your Small Logo"); ?>  (32x32)
                                                    </label>
                                                    <div class="col-md-8 ">
                                                        <div id="croppieLogoSmall"></div>
                                                        <a id="logoSmall-btn" class="btn btn-default btn-xs btn-block"><?php echo __("Upload a small logo"); ?></a>
                                                    </div>
                                                    <input type="file" id="logoSmall" value="Choose a Small Logo" accept="image/*" style="display: none;" />
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("First Page Mode"); ?></label>
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="fa fa-sitemap"></i></span>
                                                            <select class="form-control" id="mode" >
                                                                <option value="Youtube" <?php echo ($config->getMode() == "Youtube") ? "selected" : ""; ?>><?php echo __("Youtube"); ?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

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
                                                    <label class="col-md-4 control-label"><?php echo __("Language"); ?></label>
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="glyphicon glyphicon-flag"></i></span>
                                                            <input  id="inputLanguage" placeholder="<?php echo __("Language"); ?>" class="form-control"  type="text"  value="<?php echo $config->getLanguage(); ?>" >
                                                        </div>
                                                        <small class="form-text text-muted"><?php echo __("This value must match with the language files on"); ?><code><?php echo $global['systemRootPath']; ?>locale</code></small>
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
                                                            <span class="input-group-addon"><i class="fa fa-cloud-upload"></i></span>
                                                            <select class="form-control" id="authCanUploadVideos" >
                                                                <option value="1" <?php echo ($config->getAuthCanUploadVideos() == 1) ? "selected" : ""; ?>><?php echo __("Yes"); ?></option>
                                                                <option value="0" <?php echo ($config->getAuthCanUploadVideos() == 0) ? "selected" : ""; ?>><?php echo __("No"); ?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Authenticated users can comment videos"); ?></label>
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="fa fa-commenting"></i></span>

                                                            <select class="form-control" id="authCanComment"  >
                                                                <option value="1" <?php echo ($config->getAuthCanComment() == 1) ? "selected" : ""; ?>><?php echo __("Yes"); ?></option>
                                                                <option value="0" <?php echo ($config->getAuthCanComment() == 0) ? "selected" : ""; ?>><?php echo __("No"); ?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4  control-label"><?php echo __("Autoplay Video on Load Page"); ?></label>
                                                    <div class="col-md-8">
                                                        <input data-toggle="toggle" type="checkbox" name="autoplay" id="autoplay" value="1" <?php
                                                        if (!empty($config->getAutoplay())) {
                                                            echo "checked";
                                                        }
                                                        ?>>
                                                    </div>
                                                </div>

                                            </fieldset>
                                        </div>
                                        <div class="tab-pane" id="tabAdvanced">
                                            <?php
                                            if (empty($global['disableAdvancedConfigurations'])) {
                                                ?>
                                                <fieldset>
                                                    <legend><?php echo __("Advanced configuration"); ?></legend>

                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("Encoder URL"); ?></label>
                                                        <div class="col-md-10">
                                                            <input id="encoder_url" aria-describedby="encoder_urlHelp" class="form-control"  type="url" value="<?php echo $config->getEncoderURL(); ?>" >
                                                            <small id="encoder_urlHelp" class="form-text text-muted">
                                                                <?php echo __("You need to set up an encoder server"); ?><br>
                                                                <?php echo __("You can use our public encoder on"); ?>: https://encoder.youphptube.com/ or
                                                                <a href="https://github.com/DanielnetoDotCom/YouPHPTube-Encoder" class="btn btn-default btn-xs" target="_blank"><?php echo __("For faster encode, download your own encoder"); ?></a>
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("Session Timeout in seconds"); ?></label>
                                                        <div class="col-md-10">
                                                            <input id="session_timeout" class="form-control"  type="number" value="<?php echo $config->getSession_timeout(); ?>" >
                                                        </div>
                                                    </div>


                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("Disable YouPHPTube Google Analytics"); ?></label>
                                                        <div class="col-md-10">
                                                            <input data-toggle="toggle" type="checkbox" name="disable_analytics" id="disable_analytics" value="1" <?php
                                                            if (!empty($config->getDisable_analytics())) {
                                                                echo "checked";
                                                            }
                                                            ?>  aria-describedby="disable_analyticsHelp">
                                                            <small id="disable_analyticsHelp" class="form-text text-muted"><?php echo __("This help us to track and dettect errors"); ?></small>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("Disable right-click-prevention on video"); ?></label>
                                                        <div class="col-md-10">
                                                            <input data-toggle="toggle" type="checkbox" name="disable_rightclick" id="disable_rightclick" value="1" <?php
                                                            if (!empty($config->getDisable_rightclick())) {
                                                                echo "checked";
                                                            }
                                                            ?> >
                                                        </div>
                                                    </div>


                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("Enable SMTP"); ?></label>
                                                        <div class="col-md-10">
                                                            <input data-toggle="toggle" type="checkbox" name="enableSmtp" id="enableSmtp" value="1" <?php
                                                            if (!empty($config->getSmtp())) {
                                                                echo "checked";
                                                            }
                                                            ?> >
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("Enable SMTP Auth"); ?></label>
                                                        <div class="col-md-10">
                                                            <input data-toggle="toggle" type="checkbox" name="enableSmtpAuth" id="enableSmtpAuth" value="1" <?php
                                                            if (!empty($config->getSmtpAuth())) {
                                                                echo "checked";
                                                            }
                                                            ?> >
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("SMTP Secure"); ?></label>
                                                        <div class="col-md-10">
                                                            <input id="smtpSecure" class="form-control"  type="text" value="<?php echo $config->getSmtpSecure(); ?>" placeholder="tls OR ssl" aria-describedby="smtpSecureHelp"    >
                                                            <small id="smtpSecureHelp" class="form-text text-muted"><?php echo __("Use tls OR ssl"); ?></small>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("SMTP Port"); ?></label>
                                                        <div class="col-md-10">
                                                            <input id="smtpPort" class="form-control"  type="number" value="<?php echo $config->getSmtpPort(); ?>" placeholder="465 OR 587" aria-describedby="smtpPortHelp"    >
                                                            <small id="smtpPortHelp" class="form-text text-muted"><?php echo __("465 OR 587"); ?></small>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("SMTP Host"); ?></label>
                                                        <div class="col-md-10">
                                                            <input id="smtpHost" class="form-control"  type="text" value="<?php echo $config->getSmtpHost(); ?>" placeholder="smtp.gmail.com" >
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("SMTP Username"); ?></label>
                                                        <div class="col-md-10">
                                                            <input id="smtpUsername" class="form-control"  type="text" value="<?php echo $config->getSmtpUsername(); ?>" placeholder="email@gmail.com" >
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-md-2"><?php echo __("SMTP Password"); ?></label>
                                                        <div class="col-md-10">
                                                            <input id="smtpPassword" class="form-control"  type="password" value="<?php echo $config->getSmtpPassword(); ?>" >
                                                        </div>
                                                    </div>


                                                </fieldset>
                                                <?php
                                            } else {
                                                ?>
                                                <h2 class="alert alert-danger"><?php echo __("Advanced configurations are disabled"); ?></h2>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="tab-pane" id="tabHead">
                                            <fieldset>
                                                <legend><?php echo __("Script Code"); ?></legend>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("Head Code"); ?></label>
                                                    <div class="col-md-10">
                                                        <textarea id="head" class="form-control" type="text" rows="20" ><?php echo $config->getHead(); ?></textarea>
                                                        <small>For Google Analytics code: <a href='https://analytics.google.com'  target="_blank">https://analytics.google.com</a></small><br>
                                                        <small>Leave blank for native code</small>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("Google Ad Sense"); ?></label>
                                                    <div class="col-md-10">
                                                        <textarea id="adsense" class="form-control" type="text" rows="20" ><?php echo $config->getAdsense(); ?></textarea>
                                                        <small>For Google AdSense code: <a href='https://www.google.com/adsense'  target="_blank">https://www.google.com/adsense</a></small><br>
                                                        <small>Leave blank for native code</small>
                                                    </div>
                                                </div>

                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                                <!-- Button -->
                                <div class="form-group">
                                    <label class="col-md-4 control-label"></label>
                                    <div class="col-md-8">
                                        <button type="submit" class="btn btn-primary" ><?php echo __("Save"); ?> <span class="glyphicon glyphicon-save"></span></button>
                                    </div>
                                </div>
                            </div>



                        </form>

                    </div>
                </div>
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
                            swal("Sorry - you're browser doesn't support the FileReader API");
                        }
                    }

                    var logoImgBase64;
                    var logoSmallImgBase64;

                    $(document).ready(function () {
                        // start croppie logo
                        $('#logo').on('change', function () {
                            readFile(this, logoCrop);
                        });
                        $('#logo-btn').on('click', function (ev) {
                            $('#logo').trigger("click");
                        });
                        $('#logo-result-btn').on('click', function (ev) {
                            logoCrop.croppie('result', {
                                type: 'canvas',
                                size: 'viewport'
                            }).then(function (resp) {

                            });
                        });

                        logoCrop = $('#croppieLogo').croppie({
                            url: '<?php echo $global['webSiteRootURL'], $config->getLogo(); ?>',
                            enableExif: true,
                            viewport: {
                                width: 250,
                                height: 70
                            },
                            boundary: {
                                width: 300,
                                height: 120
                            }
                        });
                        // END croppie logo
                        // start croppie logoSmall
                        $('#logoSmall').on('change', function () {
                            readFile(this, logoSmallCrop);
                        });
                        $('#logoSmall-btn').on('click', function (ev) {
                            $('#logoSmall').trigger("click");
                        });
                        $('#logoSmall-result-btn').on('click', function (ev) {
                            logoSmallCrop.croppie('result', {
                                type: 'canvas',
                                size: 'viewport'
                            }).then(function (resp) {

                            });
                        });

                        logoSmallCrop = $('#croppieLogoSmall').croppie({
                            url: '<?php echo $global['webSiteRootURL'], $config->getLogo_small(); ?>',
                            enableExif: true,
                            viewport: {
                                width: 32,
                                height: 32
                            },
                            boundary: {
                                width: 60,
                                height: 60
                            }
                        });

                        // END croppie logoSmall

                        $('#updateConfigForm').submit(function (evt) {
                            evt.preventDefault();
                            modal.showPleaseWait();
                            $('#tabRegularLink').tab('show');
                            logoSmallCrop.croppie('result', {
                                type: 'canvas',
                                size: 'viewport'
                            }).then(function (resp) {
                                logoSmallImgBase64 = resp;
                                logoCrop.croppie('result', {
                                    type: 'canvas',
                                    size: 'viewport'
                                }).then(function (resp) {
                                    logoImgBase64 = resp;

                                    $.ajax({
                                        url: 'updateConfig',
                                        data: {
                                            "logoSmallImgBase64": logoSmallImgBase64,
                                            "logoImgBase64": logoImgBase64,
                                            "video_resolution": $('#inputVideoResolution').val(),
                                            "webSiteTitle": $('#inputWebSiteTitle').val(),
                                            "language": $('#inputLanguage').val(),
                                            "contactEmail": $('#inputEmail').val(),
                                            "authCanUploadVideos": $('#authCanUploadVideos').val(),
                                            "authCanComment": $('#authCanComment').val(),
                                            "head": $('#head').val(),
                                            "adsense": $('#adsense').val(),
                                            "mode": $('#mode').val(),
                                            "disable_analytics": $('#disable_analytics').prop("checked"),
                                            "disable_rightclick": $("#disable_rightclick").prop("checked"),
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
                                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your configurations has been updated!"); ?>", "success");
                                            } else {
                                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your configurations has NOT been updated!"); ?>", "error");
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
                            $("#theme").attr("href", "<?php echo $global['webSiteRootURL']?>css/custom/"+cssName+".css");
                            $('.btn-radio').parent("div").removeClass('bg-success');
                            $(this).addClass('active').parent("div").addClass("bg-success");
                            theme = cssName;
                        });
                    });
                </script>
                <?php
            }
            ?>

        </div><!--/.container-->

        <?php
        include 'include/footer.php';
        ?>

    </body>
</html>
