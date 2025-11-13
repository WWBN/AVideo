<?php
global $global, $advancedCustom;
$objSecure = AVideoPlugin::getObjectDataIfEnabled('SecureVideosDirectory');
$search = ['{permaLink}', '{imgSRC}', '{title}', '{embedURL}', '{videoLengthInSeconds}'];
$replace = [$permaLink, $img, $title, $embedURL, $videoLengthInSeconds];
?>
<div class="<?php echo $class; ?>" id="shareDiv">
    <div class="tabbable-panel">
        <div class="tabbable-line">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link " href="#tabShare" data-toggle="tab">
                        <span class="fa fa-share"></span>
                        <?php echo __("Share"); ?>
                    </a>
                </li>

                <?php
                if (empty($objSecure->disableEmbedMode)) {
                ?>
                    <li class="nav-item">
                        <a class="nav-link " href="#tabEmbed" data-toggle="tab">
                            <span class="fa fa-code"></span>
                            <?php echo __("Share Code"); ?>
                        </a>
                    </li>
                <?php
                }
                if (empty($advancedCustom->disableEmailSharing)) {
                ?>

                    <li class="nav-item">
                        <a class="nav-link" href="#tabEmail" data-toggle="tab">
                            <span class="fa fa-envelope"></span>
                            <?php echo __("E-mail"); ?>
                        </a>
                    </li>
                <?php
                }
                if (!empty($permaLink) && $permaLink !== $URLFriendly) {
                ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#tabPermaLink" data-toggle="tab">
                            <span class="fa fa-link"></span>
                            <?php echo __("Permanent Link"); ?>
                        </a>
                    </li>
                <?php
                }
                ?>
            </ul>
            <div class="tab-content clearfix">
                <div class="tab-pane active" id="tabShare">
                    <?php
                    $url = $permaLink;
                    //$title = urlencode($title);
                    include $global['systemRootPath'] . 'view/include/social.php';
                    $type = 'animate__flipInX';
                    $loaderSequenceName = _uniqid();
                    ?>
                </div>
                <div class="tab-pane" id="tabEmbed">
                    <!-- Embed Options -->
                    <div class="panel panel-default" style="margin-bottom: 20px;">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" href="#embedOptions">
                                    <i class="fas fa-cog"></i> <?php echo __("Embed Options"); ?>
                                </a>
                            </h4>
                        </div>
                        <div id="embedOptions" class="panel-collapse collapse" style="overflow: visible;">
                            <div class="panel-body" style="overflow: visible;">
                                <?php
                                require_once $global['systemRootPath'] . 'objects/EmbedPlayerConfig.php';

                                $fieldsMetadata = EmbedPlayerConfig::getFieldsMetadata();

                                // Separate fields into columns
                                $leftColumn = [];
                                $rightColumn = [];
                                $fullWidth = [];
                                $controlsGroup = [];

                                $index = 0;
                                foreach ($fieldsMetadata as $fieldName => $fieldData) {
                                    if ($fieldData['type'] === 'hidden') {
                                        continue;
                                    }

                                    if ($fieldData['type'] === 'radio-controls') {
                                        $controlsGroup[$fieldName] = $fieldData;
                                    } elseif ($fieldData['type'] === 'select' || $fieldData['type'] === 'number') {
                                        $fullWidth[$fieldName] = $fieldData;
                                    } else {
                                        if ($index % 2 === 0) {
                                            $leftColumn[$fieldName] = $fieldData;
                                        } else {
                                            $rightColumn[$fieldName] = $fieldData;
                                        }
                                        $index++;
                                    }
                                }

                                // Function to render a checkbox field
                                function renderCheckboxField($fieldName, $fieldData)
                                {
                                ?>
                                    <div class="checkbox">
                                        <label title="<?php echo htmlspecialchars($fieldData['description'] ?? ''); ?>">
                                            <input type="checkbox"
                                                id="embedOpt_<?php echo $fieldName; ?>"
                                                class="embedOption"
                                                data-field="<?php echo $fieldName; ?>"
                                                data-type="<?php echo $fieldData['type']; ?>">
                                            <?php echo __($fieldData['name']); ?>
                                            <?php if (!empty($fieldData['description'])): ?>
                                                <i class="fa fa-question-circle"
                                                    data-toggle="tooltip"
                                                    data-placement="bottom"
                                                    title="<?php echo htmlspecialchars($fieldData['description']); ?>"></i>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php
                                }
                                ?>

                                <div class="row">
                                    <!-- Left Column -->
                                    <div class="col-md-4">
                                        <?php foreach ($leftColumn as $fieldName => $fieldData):
                                            renderCheckboxField($fieldName, $fieldData);
                                        endforeach; ?>
                                    </div>

                                    <!-- Right Column -->
                                    <div class="col-md-4">
                                        <?php foreach ($rightColumn as $fieldName => $fieldData):
                                            renderCheckboxField($fieldName, $fieldData);
                                        endforeach; ?>
                                    </div>

                                    <div class="col-md-4">
                                        <?php if (!empty($controlsGroup)): ?>
                                            <strong><?php echo __("Controls Mode"); ?>:</strong>
                                            <div class="radio">
                                                <label title="<?php echo __("Shows all standard player controls with full functionality"); ?>">
                                                    <input type="radio" name="embedControlsMode" value="" class="embedOption" checked>
                                                    <?php echo __("Normal Controls"); ?>
                                                    <i class="fa fa-question-circle"
                                                        data-toggle="tooltip"
                                                        data-placement="bottom"
                                                        title="<?php echo __("Shows all standard player controls with full functionality"); ?>"></i>
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label title="<?php echo __("Hides all player controls completely. No control bar, play button, or progress bar. User cannot pause or control the video."); ?>">
                                                    <input type="radio" name="embedControlsMode" value="disable" id="embedOpt_controls_disable" class="embedOption">
                                                    <?php echo __("Disable All Controls"); ?>
                                                    <i class="fa fa-question-circle"
                                                        data-toggle="tooltip"
                                                        data-placement="bottom"
                                                        title="<?php echo __("Hides all player controls completely. No control bar, play button, or progress bar. User cannot pause or control the video."); ?>"></i>
                                                </label>
                                            </div>
                                            <?php foreach ($controlsGroup as $fieldName => $fieldData): ?>
                                                <div class="radio">
                                                    <label title="<?php echo htmlspecialchars($fieldData['description'] ?? ''); ?>">
                                                        <input type="radio" name="embedControlsMode" value="<?php echo $fieldName; ?>"
                                                            id="embedOpt_<?php echo $fieldName; ?>" class="embedOption" data-field="<?php echo $fieldName; ?>">
                                                        <?php echo __($fieldData['name']); ?>
                                                        <?php if (!empty($fieldData['description'])): ?>
                                                            <i class="fa fa-question-circle"
                                                                data-toggle="tooltip"
                                                                data-placement="bottom"
                                                                title="<?php echo htmlspecialchars($fieldData['description']); ?>"></i>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Additional Fields (50% width on medium screens) -->
                                <div class="row">
                                    <?php foreach ($fullWidth as $fieldName => $fieldData): ?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>
                                                    <?php echo __($fieldData['name']); ?>:
                                                    <?php if (!empty($fieldData['description'])): ?>
                                                        <i class="fa fa-question-circle"
                                                            data-toggle="tooltip"
                                                            data-placement="bottom"
                                                            title="<?php echo htmlspecialchars($fieldData['description']); ?>"></i>
                                                    <?php endif; ?>
                                                </label>

                                                <?php if ($fieldData['type'] === 'select'): ?>
                                                    <select id="embedOpt_<?php echo $fieldName; ?>" class="form-control embedOption" data-field="<?php echo $fieldName; ?>">
                                                        <?php foreach ($fieldData['options'] as $value => $label): ?>
                                                            <option value="<?php echo htmlspecialchars($value); ?>"><?php echo __($label); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php elseif ($fieldData['type'] === 'number'): ?>
                                                    <input type="number" id="embedOpt_<?php echo $fieldName; ?>"
                                                        class="form-control embedOption" data-field="<?php echo $fieldName; ?>"
                                                        min="0" placeholder="0">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <strong><i class="fas fa-share-square"></i> <?php echo __("Embed"); ?> (Iframe): <?php getButtontCopyToClipboard('textAreaEmbedIframe'); ?></strong>
                    <textarea class="form-control <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?> min-width: 100%; margin: 10px 0 20px 0;" rows="5" id="textAreaEmbedIframe" readonly="readonly"><?php
                                                                                                                                                                                                                    $code = str_replace($search, $replace, $advancedCustom->embedCodeTemplate);
                                                                                                                                                                                                                    echo htmlentities($code);
                                                                                                                                                                                                                    ?>
                    </textarea>
                    <strong><i class="fas fa-share-square"></i> <?php echo __("Embed"); ?> (Object): <?php getButtontCopyToClipboard('textAreaEmbedObject'); ?></strong>
                    <textarea class="form-control <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?> min-width: 100%; margin: 10px 0 20px 0;" rows="5" id="textAreaEmbedObject" readonly="readonly"><?php
                                                                                                                                                                                                                            $code = str_replace($search, $replace, $advancedCustom->embedCodeTemplateObject);
                                                                                                                                                                                                                            echo htmlentities($code);
                                                                                                                                                                                                                            ?>
                    </textarea>
                    <strong><i class="fas fa-share-square"></i> <?php echo __("Link"); ?> (HTML): <?php getButtontCopyToClipboard('textAreaHTML'); ?></strong>
                    <textarea class="form-control <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?> min-width: 100%; margin: 10px 0 20px 0;" rows="5" id="textAreaHTML" readonly="readonly"><?php
                                                                                                                                                                                                                    $code = str_replace($search, $replace, $advancedCustom->htmlCodeTemplate);
                                                                                                                                                                                                                    echo htmlentities($code);
                                                                                                                                                                                                                    ?>
                    </textarea>
                    <strong><i class="fas fa-share-square"></i> <?php echo __("Link"); ?> (BBCode): <?php getButtontCopyToClipboard('textAreaBBCode'); ?></strong>
                    <textarea class="form-control <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?> min-width: 100%; margin: 10px 0 20px 0;" rows="5" id="textAreaBBCode" readonly="readonly"><?php
                                                                                                                                                                                                                    $code = str_replace($search, $replace, $advancedCustom->BBCodeTemplate);
                                                                                                                                                                                                                    echo htmlentities($code);
                                                                                                                                                                                                                    ?>
                    </textarea>
                    <strong><i class="fas fa-link"></i> <?php echo __("Embed URL"); ?>: <?php getButtontCopyToClipboard('textAreaEmbedURL'); ?></strong>
                    <textarea class="form-control <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?> min-width: 100%; margin: 10px 0 20px 0;" rows="2" id="textAreaEmbedURL" readonly="readonly"><?php echo $embedURL; ?></textarea>
                </div>
                <?php
                if (empty($advancedCustom->disableEmailSharing)) {
                    $loaderSequenceName = _uniqid();
                    ?>
                    <div class="tab-pane" id="tabEmail">
                        <?php if (!User::isLogged()) { ?>
                            <strong>
                                <a href="<?php echo $global['webSiteRootURL']; ?>user"><?php echo __("Sign in now!"); ?></a>
                            </strong>
                        <?php } else { ?>
                            <form class="well form-horizontal" action="<?php echo $global['webSiteRootURL']; ?>sendEmail" method="post" id="contact_form">
                                <fieldset>
                                    <!-- Text input-->
                                    <div class="form-group  <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?>">
                                        <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
                                        <div class="col-md-8 inputGroupContainer">
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                                <input name="email" placeholder="<?php echo __("E-mail Address"); ?>" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Text area -->

                                    <div class="form-group <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?>">
                                        <label class="col-md-4 control-label"><?php echo __("Message"); ?></label>
                                        <div class="col-md-8 inputGroupContainer">
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                                                <textarea class="form-control" name="comment" placeholder="<?php echo __("Message"); ?>"><?php echo __("I would like to share this video with you:"); ?> <?php echo $URLFriendly; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?>">
                                        <label class="col-md-4 control-label"><?php echo __("Type the code"); ?></label>
                                        <div class="col-md-8 inputGroupContainer">
                                            <?php
                                            $capcha = getCaptcha();
                                            echo $capcha['content'];
                                            ?>
                                        </div>
                                    </div>
                                    <!-- Button -->
                                    <div class="form-group <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?>">
                                        <label class="col-md-4 control-label"></label>
                                        <div class="col-md-8">
                                            <button type="submit" class="btn btn-primary"><?php echo __("Send"); ?> <i class="fa-regular fa-paper-plane"></i></button>
                                        </div>
                                    </div>

                                </fieldset>
                            </form>
                            <script>
                                $(document).ready(function() {
                                    $('#contact_form').submit(function(evt) {
                                        evt.preventDefault();
                                        modal.showPleaseWait();
                                        $.ajax({
                                            url: webSiteRootURL + 'objects/sendEmail.json.php',
                                            data: $('#contact_form').serializeArray(),
                                            type: 'post',
                                            success: function(response) {
                                                modal.hidePleaseWait();
                                                if (!response.error) {
                                                    avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your message has been sent!"); ?>", "success");
                                                } else {
                                                    avideoAlert("<?php echo __("Your message could not be sent!"); ?>", response.error, "error");
                                                }
                                                <?php echo $capcha['btnReloadCapcha']; ?>
                                            }
                                        });
                                        return false;
                                    });
                                });
                            </script>
                        <?php } ?>
                    </div>

                <?php
                }
                if (!empty($permaLink) && $permaLink !== $URLFriendly) {
                    $loaderSequenceName = _uniqid();
                    ?>
                    <div class="tab-pane" id="tabPermaLink">
                        <div class="form-group <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?>">
                            <label class="control-label"><?php echo __("Permanent Link") ?></label>
                            <?php getInputCopyToClipboard('linkPermanent', $permaLink); ?>
                        </div>
                        <div class="form-group <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?>">
                            <label class="control-label"><?php echo __("URL Friendly") ?> (SEO)</label>
                            <?php getInputCopyToClipboard('linkFriendly', $URLFriendly); ?>
                        </div>
                        <div class="form-group <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?>">
                            <label class="control-label"><?php echo __("Current Time") ?> (SEO)</label>
                            <?php getInputCopyToClipboard('linkCurrentTime', $URLFriendly); ?>
                        </div>
                        <?php
                        if (!empty($bitLyLink)) {
                        ?>
                            <div class="form-group <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?>">
                                <label class="control-label"><?php echo __("Bit.Ly") ?></label>
                                <?php getInputCopyToClipboard('bitLyLink', $bitLyLink); ?>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var COOKIE_NAME = 'embedOptions';
        var COOKIE_DAYS = 365;

        // Cookie utilities
        var CookieManager = {
            set: function(name, value, days) {
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            },

            get: function(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }
        };

        // Options Manager
        var OptionsManager = {
            getFieldName: function($element) {
                return $element.data('field') || $element.attr('id').replace('embedOpt_', '');
            },

            load: function() {
                var savedOptions = CookieManager.get(COOKIE_NAME);
                if (!savedOptions) return;

                try {
                    var options = JSON.parse(savedOptions);

                    // Restore checkboxes
                    $('.embedOption[type="checkbox"]').each(function() {
                        var fieldName = OptionsManager.getFieldName($(this));
                        if (options[fieldName] !== undefined) {
                            $(this).prop('checked', options[fieldName]);
                        }
                    });

                    // Restore radio buttons
                    if (options.controlsMode) {
                        $('input[name="embedControlsMode"][value="' + options.controlsMode + '"]').prop('checked', true);
                    }

                    // Restore other inputs
                    $('.embedOption').not('[type="checkbox"]').not('[type="radio"]').each(function() {
                        var fieldName = OptionsManager.getFieldName($(this));
                        if (options[fieldName] !== undefined && options[fieldName] !== '') {
                            $(this).val(options[fieldName]);
                        }
                    });
                } catch (e) {
                    console.error('Error loading embed options:', e);
                }
            },

            save: function() {
                var options = {};

                // Save checkboxes
                $('.embedOption[type="checkbox"]').each(function() {
                    options[OptionsManager.getFieldName($(this))] = $(this).is(':checked');
                });

                // Save controls mode
                options.controlsMode = $('input[name="embedControlsMode"]:checked').val();

                // Save other inputs
                $('.embedOption').not('[type="checkbox"]').not('[type="radio"]').each(function() {
                    var value = $(this).val();
                    if (value !== '') {
                        options[OptionsManager.getFieldName($(this))] = value;
                    }
                });

                CookieManager.set(COOKIE_NAME, JSON.stringify(options), COOKIE_DAYS);
            }
        };

        // Query String Builder
        var QueryBuilder = {
            controlsModeMap: {
                'disable': '0',
                'showOnlyBasicControls': '-1',
                'hideProgressBarAndUnPause': '-2'
            },

            addParam: function(params, name, value) {
                params.push(name + '=' + encodeURIComponent(value));
            },

            build: function() {
                var params = [];

                // Handle controls mode
                var controlsMode = $('input[name="embedControlsMode"]:checked').val();
                if (controlsMode && this.controlsModeMap[controlsMode]) {
                    this.addParam(params, 'controls', this.controlsModeMap[controlsMode]);
                }

                // Handle checkboxes
                $('.embedOption[type="checkbox"]:checked').each(function() {
                    var fieldName = OptionsManager.getFieldName($(this));
                    var fieldType = $(this).data('type');
                    var value = (fieldType === 'checkbox-inverted') ? '0' : '1';
                    QueryBuilder.addParam(params, fieldName, value);
                });

                // Handle other inputs
                $('.embedOption').not('[type="checkbox"]').not('[type="radio"]').each(function() {
                    var value = $(this).val();
                    if (!value) return;

                    if ($(this).attr('type') === 'number') {
                        var numValue = parseInt(value);
                        if (isNaN(numValue) || numValue <= 0) return;
                    }

                    QueryBuilder.addParam(params, OptionsManager.getFieldName($(this)), value);
                });

                return params.length > 0 ? '?' + params.join('&') : '';
            }
        };

        // Embed Code Updater
        var EmbedUpdater = {
            baseURL: '<?php echo $embedURL; ?>',

            buildURL: function(queryString) {
                if (!queryString) return this.baseURL;

                var separator = this.baseURL.indexOf('?') !== -1 ? '&' : '?';
                return this.baseURL + separator + queryString.substring(1);
            },

            replacePattern: function(code, pattern, replacement) {
                return code.replace(pattern, replacement);
            },

            update: function() {
                OptionsManager.save();

                var queryString = QueryBuilder.build();
                var newURL = this.buildURL(queryString);

                console.log('Updated Embed URL:', newURL);

                // Update Iframe embed code (works with HTML entities)
                var $iframeTextarea = $('#textAreaEmbedIframe');
                var iframeCode = $iframeTextarea.val();
                iframeCode = iframeCode.replace(/src="[^"]*"/gi, 'src="' + newURL + '"');
                $iframeTextarea.val(iframeCode);

                // Update Object embed code (works with HTML entities)
                var $objectTextarea = $('#textAreaEmbedObject');
                var objectCode = $objectTextarea.val();
                objectCode = objectCode.replace(/data="[^"]*"/gi, 'data="' + newURL + '"');
                objectCode = objectCode.replace(/value="[^"]*"/gi, 'value="' + newURL + '"');
                $objectTextarea.val(objectCode);

                // Update HTML link code (works with HTML entities)
                var $htmlTextarea = $('#textAreaHTML');
                var htmlCode = $htmlTextarea.val();
                htmlCode = htmlCode.replace(/href="[^"]*"/gi, 'href="' + newURL + '"');
                $htmlTextarea.val(htmlCode);

                // Update BBCode (no HTML encoding)
                var $bbCodeTextarea = $('#textAreaBBCode');
                var bbCode = $bbCodeTextarea.val();
                bbCode = bbCode.replace(/\[url=[^\]]*\]/gi, '[url=' + newURL + ']');
                $bbCodeTextarea.val(bbCode);

                // Update plain Embed URL
                var $embedURLTextarea = $('#textAreaEmbedURL');
                $embedURLTextarea.val(newURL);
            }
        };

        // Initialize
        function init() {
            // Load saved options
            OptionsManager.load();

            // Initialize Bootstrap tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Bind events
            $('.embedOption, input[name="embedControlsMode"]').on('change', function() {
                EmbedUpdater.update();
            });

            // Update when tab is shown
            $('a[href="#tabEmbed"]').on('shown.bs.tab', function() {
                EmbedUpdater.update();
            });

            // Initial update if embed tab is active
            if ($('#tabEmbed').hasClass('active')) {
                EmbedUpdater.update();
            }
        }

        init();
    });
</script>

<style>
    #embedOptions .checkbox,
    #embedOptions .radio {
        margin-top: 5px;
        margin-bottom: 5px;
    }

    #embedOptions .panel-body {
        max-height: 500px;
        overflow-y: auto;
    }

    #embedOptions label {
        font-weight: normal;
        cursor: pointer;
    }

    #embedOptions label .fa-question-circle {
        font-size: 0.9em;
        margin-left: 5px;
        cursor: help;
    }

    #embedOptions hr {
        margin: 15px 0;
    }

    #embedOptions .form-group {
        margin-top: 15px;
    }
</style>
