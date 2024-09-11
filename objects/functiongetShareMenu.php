<?php
global $global, $advancedCustom;
$objSecure = AVideoPlugin::getObjectDataIfEnabled('SecureVideosDirectory');
$search = ['{permaLink}', '{imgSRC}', '{title}', '{embedURL}', '{videoLengthInSeconds}'];
$replace = [$permaLink, $img, $title, $embedURL, $videoLengthInSeconds];
?>
<div class="<?php echo $class; ?>" id="shareDiv">
    <div class="tabbable-panel">
        <div class="tabbable-line text-muted">
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
                    $loaderSequenceName = uniqid();
                    ?>
                </div>
                <div class="tab-pane" id="tabEmbed">
                    <strong><i class="fas fa-share-square"></i> <?php echo __("Embed"); ?> (Iframe): <?php getButtontCopyToClipboard('textAreaEmbed'); ?></strong>
                    <textarea class="form-control <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?> min-width: 100%; margin: 10px 0 20px 0;" rows="5" id="textAreaEmbed" readonly="readonly"><?php
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
                </div>
                <?php
                if (empty($advancedCustom->disableEmailSharing)) {
                    $loaderSequenceName = uniqid();
                    ?>
                    <div class="tab-pane" id="tabEmail">
                        <?php if (!User::isLogged()) { ?>
                            <strong>
                                <a href="<?php echo $global['webSiteRootURL']; ?>user"><?php echo __("Sign in now!"); ?></a>
                            </strong>
                        <?php } else { ?>
                            <form class="well form-horizontal" action="<?php echo $global['webSiteRootURL']; ?>sendEmail" method="post"  id="contact_form">
                                <fieldset>
                                    <!-- Text input-->
                                    <div class="form-group  <?php echo getCSSAnimationClassAndStyle($type, $loaderSequenceName); ?>">
                                        <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
                                        <div class="col-md-8 inputGroupContainer">
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                                <input name="email" placeholder="<?php echo __("E-mail Address"); ?>" class="form-control"  type="text">
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
                                            <button type="submit" class="btn btn-primary" ><?php echo __("Send"); ?> <i class="fa-regular fa-paper-plane"></i></button>
                                        </div>
                                    </div>

                                </fieldset>
                            </form>
                            <script>
                                $(document).ready(function () {
                                    $('#contact_form').submit(function (evt) {
                                        evt.preventDefault();
                                        modal.showPleaseWait();
                                        $.ajax({
                                            url: webSiteRootURL + 'objects/sendEmail.json.php',
                                            data: $('#contact_form').serializeArray(),
                                            type: 'post',
                                            success: function (response) {
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
                    $loaderSequenceName = uniqid();
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
