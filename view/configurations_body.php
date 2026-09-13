<?php
if (User::isAdmin()) {
    $logoWidth = 500;
    $logoHeight = 140;
    $faviconWidth = 1024;
    $faviconHeight = $faviconWidth;
?>
    <div class="container-fluid site-configurations">
        <form class="form-compact form-horizontal" id="updateConfigForm" novalidate data-saved-email="<?php echo htmlspecialchars($config->getContactEmail(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="panel panel-default ">
                <div class="panel-heading tabbable-line">

                    <ul class="nav nav-tabs">
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
                        <li class="nav-item">
                            <a class="nav-link " href="#tabCompatibility" data-toggle="tab">
                                <span class="fas fa-notes-medical"></span>
                                <?php echo __("Health Check"); ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content clearfix">
                        <div class="tab-pane" id="tabCompatibility">
                            <div id="configurationHealthCheck" data-url="<?php echo $global['webSiteRootURL']; ?>view/configurations.php?healthCheck=1" aria-live="polite">
                                <p class="health-check-status"><?php echo __('Health Check'); ?></p>
                                <button type="button" class="btn btn-default health-check-retry"><?php echo __('Retry'); ?></button>
                            </div>
                        </div>
                        <div class="tab-pane  active" id="tabRegular">

                            <div class="row">
                                <div class="col-md-6">

                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h2><?php echo __("Basic"); ?></h2>
                                        </div>
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Language"); ?></label>
                                                <div class="col-md-8 inputGroupContainer">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="glyphicon glyphicon-flag"></i></span>

                                                        <select class="form-control" id="inputLanguage" name="inputLanguage">
                                                            <?php
                                                            $selectedLang = $config->getLanguage();
                                                            $flags = Layout::getAvailableFlags();
                                                            //var_dump($selectedLang, $flags);exit;
                                                            foreach ($flags as $key => $value) {
                                                                $info = json_decode($value[0]);
                                                            ?>
                                                                <option value="<?php echo $key; ?>" <?php echo ($selectedLang == $key) ? "selected" : ""; ?>><?php echo $info->text; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
                                                <div class="col-md-8 inputGroupContainer">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                                        <input id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" class="form-control" type="email" value="<?php echo htmlspecialchars((string) $config->getContactEmail(), ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                    <small class="form-text text-muted"><?php echo __("This e-mail will be used for this web site notifications"); ?></small>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Authenticated users can upload videos"); ?></label>
                                                <div class="col-md-8 inputGroupContainer">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="fas fa-cloud-upload-alt"></i></span>
                                                        <select class="form-control" id="authCanUploadVideos">
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
                                                        <select class="form-control" id="authCanViewChart">
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

                                                        <select class="form-control" id="authCanComment">
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
                                                        <input data-toggle="toggle" type="checkbox" name="autoplaySwitch" id="autoplaySwitch" value="1" <?php
                                                                                                                                                        if (!empty($config->getAutoplay())) {
                                                                                                                                                            echo "checked";
                                                                                                                                                        }
                                                                                                                                                        ?>>
                                                        <label for="autoplaySwitch" class="label-primary"></label>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h2><?php echo __("Logo and Title"); ?></h2>
                                        </div>
                                        <div class="panel-body">
                                            <?php
                                            include $global['systemRootPath'] . 'view/ImageMagick.check.php';
                                            ?>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label"><?php echo __("Web site title"); ?></label>
                                                <div class="col-md-8 inputGroupContainer">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="glyphicon glyphicon-globe"></i></span>
                                                        <input id="inputWebSiteTitle" placeholder="<?php echo __("Web site title"); ?>" class="form-control" type="text" value="<?php echo htmlspecialchars((string) $config->getWebSiteTitle(), ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group hidden">
                                                <label class="col-md-4 control-label"><?php echo __("Description"); ?></label>
                                                <div class="col-md-8 inputGroupContainer">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="glyphicon glyphicon-globe"></i></span>
                                                        <input id="inputWebSiteDescription" placeholder="<?php echo __("Description"); ?>" class="form-control" type="text" value="<?php echo htmlspecialchars((string) $config->getDescription(), ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-4 control-label">
                                                    <?php echo __("Your Logo"); ?> (500 &times; 140)
                                                </label>
                                                <div class="col-md-8 ">
                                                    <div id="croppieLogo" data-image-url="<?php echo htmlspecialchars($global['webSiteRootURL'] . $config->getLogo(true), ENT_QUOTES, 'UTF-8'); ?>"></div>
                                                    <button type="button" id="logo-btn" class="btn btn-default btn-block"><?php echo __("Choose a logo"); ?></button>
                                                    <button type="button" id="logo-fit" class="btn btn-default btn-block" disabled><i class="fas fa-expand" aria-hidden="true"></i> <?php echo __('Fit image'); ?></button>
                                                    <p id="logo-status" class="help-block" role="status"></p>
                                                </div>
                                                <input type="file" id="logo" value="Choose a Logo" accept="image/*" style="display: none;" />
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">
                                                    <?php echo __("Favicon"); ?> (512 &times; 512)
                                                </label>
                                                <div class="col-md-8 ">
                                                    <div id="croppieFavicon" data-image-url="<?php echo htmlspecialchars($config->getFavicon(true), ENT_QUOTES, 'UTF-8'); ?>"></div>
                                                    <button type="button" id="favicon-btn" class="btn btn-default btn-block"><?php echo __("Choose a favicon"); ?></button>
                                                    <button type="button" id="favicon-fit" class="btn btn-default btn-block" disabled><i class="fas fa-expand" aria-hidden="true"></i> <?php echo __('Fit image'); ?></button>
                                                    <p id="favicon-status" class="help-block" role="status"></p>
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
                                            <div class="panel-heading">
                                                <h2><?php echo __("Advanced Configuration"); ?></h2>
                                            </div>
                                            <div class="panel-body">

                                                <div class="form-group">
                                                    <div class="col-md-12">
                                                        <button type="button" class="btn btn-danger" id="clearCache">
                                                            <i class="fa fa-trash"></i> <?php echo __("Clear Cache Directory"); ?>
                                                        </button>
                                                        <button type="button" class="btn btn-primary" id="generateSiteMap">
                                                            <i class="fa fa-sitemap"></i> <?php echo __("Generate Sitemap"); ?>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Encoder URL"); ?></label>
                                                    <div class="col-md-8">
                                                        <input id="encoder_url" aria-describedby="encoder_urlHelp" class="form-control" type="url" value="<?php echo htmlspecialchars((string) $config->_getEncoderURL(), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <small id="encoder_urlHelp" class="form-text text-muted">
                                                            <?php echo __("You need to set up an encoder server"); ?><br>
                                                            <?php echo __("You can use our public encoder on"); ?>: https://encoder1.wwbn.net/ or
                                                            <a href="https://github.com/WWBN/AVideo-Encoder" class="btn btn-default btn-xs" target="_blank" rel="noopener noreferrer"><?php echo __("For faster encode, download your own encoder"); ?></a>
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Session Timeout in seconds"); ?></label>
                                                    <div class="col-md-8">
                                                        <input id="session_timeout" class="form-control" type="number" value="<?php echo htmlspecialchars((string) $config->getSession_timeout(), ENT_QUOTES, 'UTF-8'); ?>">
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
                                                                                                                                                                    ?> aria-describedby="disable_analyticsHelp">
                                                            <label for="disable_analytics" class="label-success"></label>
                                                        </div>
                                                        <small id="disable_analyticsHelp" class="form-text text-muted"><?php echo __("This help us to track and detect errors"); ?></small>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Allow download video"); ?></label>
                                                    <div class="col-md-8">
                                                        <div class="material-switch">
                                                            <input data-toggle="toggle" type="checkbox" name="allow_download" id="allow_download" value="1" <?php
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
                                            <div class="panel-heading">
                                                <h2><i class="fas fa-at"></i> <?php echo __("Email Configuration"); ?></h2>
                                            </div>
                                            <div class="panel-body">

                                                <div class="alert alert-warning">
                                                    <h3>
                                                        <i class="fas fa-info-circle"></i>
                                                        <?php echo __('If you are not sure how to configure your email'); ?>,
                                                        <?php echo __('please try'); ?> <a href="https://github.com/WWBN/AVideo/wiki/Setting-up-AVideo-Platform-to-send-emails" target="_blank" rel="noopener noreferrer"><?php echo __('this help'); ?></a>
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
                                                                                                                                                    ?>>
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
                                                                                                                                                            ?>>
                                                            <label for="enableSmtpAuth" class="label-success"></label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("SMTP Secure"); ?></label>
                                                    <div class="col-md-8">
                                                        <select id="smtpSecure" class="form-control" aria-describedby="smtpSecureHelp">
                                                            <option value="" <?php echo empty($config->getSmtpSecure()) ? 'selected' : ''; ?>><?php echo __('None'); ?></option>
                                                            <option value="tls" <?php echo $config->getSmtpSecure() == 'tls' ? 'selected' : ''; ?>>TLS (<?php echo __('Use this for Gmail'); ?>)</option>
                                                            <option value="ssl" <?php echo $config->getSmtpSecure() == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                                        </select>
                                                        <small id="smtpSecureHelp" class="form-text text-muted"><?php echo __("Use tls OR ssl"); ?></small>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("SMTP Port"); ?></label>
                                                    <div class="col-md-8">
                                                        <input id="smtpPort" class="form-control" type="number" value="<?php echo htmlspecialchars((string) $config->getSmtpPort(), ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo __('465 OR 587'); ?>" aria-describedby="smtpPortHelp">
                                                        <small id="smtpPortHelp" class="form-text text-muted"><?php echo __("465 OR 587"); ?></small>
                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("SMTP Host"); ?></label>
                                                    <div class="col-md-8">
                                                        <input id="smtpHost" class="form-control" type="text" value="<?php echo htmlspecialchars((string) $config->getSmtpHost(), ENT_QUOTES, 'UTF-8'); ?>" placeholder="smtp.gmail.com">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("SMTP Username"); ?></label>
                                                    <div class="col-md-8">
                                                        <input id="smtpUsername" autocomplete="off" class="form-control" type="text" value="<?php echo htmlspecialchars((string) $config->getSmtpUsername(), ENT_QUOTES, 'UTF-8'); ?>" placeholder="email@gmail.com">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("SMTP Password"); ?></label>
                                                    <div class="col-md-8">
                                                        <?php getInputPassword("smtpPassword", 'autocomplete="new-password" class="form-control" value="' . htmlspecialchars((string) $config->getSmtpPassword(), ENT_QUOTES, 'UTF-8') . '"', __("SMTP Password")); ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Test your email"); ?></label>
                                                    <div class="col-md-8">
                                                        <button type="button" class="btn btn-default btn-block" id="testEmail"><?php echo __("Test Email"); ?> <i class="fa-regular fa-paper-plane"></i></button>
                                                        <small class="help-block"><?php echo __("Save your changes before testing email."); ?></small>
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
                            <?php }
                            ?>
                        </div>
                        <div class="tab-pane" id="tabHead">
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo __("Head Code"); ?></label>
                                <div class="col-md-10">
                                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/codemirror.min.css">
                                    </link>
                                    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/codemirror.min.js" doNotSepareteTag></script>
                                    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/mode/javascript/javascript.min.js" doNotSepareteTag></script>
                                    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/mode/xml/xml.js" doNotSepareteTag></script>
                                    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/mode/css/css.js" doNotSepareteTag></script>
                                    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/mode/htmlmixed/htmlmixed.js" doNotSepareteTag></script>
                                    <script>
                                        (function($) {
                                            $(document).ready(function() {

                                                var editor,
                                                    head = document.getElementById("head");

                                                $("li a[href='#tabHead']").on("shown.bs.tab", function() {
                                                    if (!editor && head && window.CodeMirror) {
                                                        editor = CodeMirror.fromTextArea(head, {
                                                            lineNumbers: true,
                                                            mode: "htmlmixed"
                                                            });
                                                        editor.on('change', function() {
                                                                editor.save();
                                                                $(head).trigger('change');
                                                            });
                                                    }
                                                    if (editor) editor.refresh();
                                                });

                                            });
                                        })(jQuery);
                                    </script>
                                    <textarea id="head" class="form-control" type="text" rows="20"><?php echo htmlentities($config->getHead()); ?></textarea>
                                    <small><?php echo __('For Google Analytics code'); ?>: <a href='https://analytics.google.com' target="_blank" rel="noopener noreferrer">https://analytics.google.com</a></small><br>
                                    <small><?php echo __('Leave blank for native code'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="panel-footer configuration-actions">
                    <?php echo getTourHelpButton('view/configurations.help.json', 'btn btn-default', true); ?>
                    <span id="configurationSaveStatus" role="status" aria-live="polite"></span>
                    <button type="submit" class="btn btn-primary configuration-save"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
                </div>
            </div>

        </form>
    </div>
    <script src="<?php echo getURL('view/js/siteConfigurations.js'); ?>"></script>
<?php
}
?>
