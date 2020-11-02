<?php
$objSecure = AVideoPlugin::getObjectDataIfEnabled('SecureVideosDirectory');
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
                            <?php echo __("Embed"); ?>
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
                    $title = urlencode($title);
                    include $global['systemRootPath'] . 'view/include/social.php';
                    ?>
                </div>
                <div class="tab-pane" id="tabEmbed">
                    <h4><span class="glyphicon glyphicon-share"></span> <?php echo __("Share Video"); ?> (Iframe): <?php echo getButtontCopyToClipboard('textAreaEmbed'); ?></h4> 
                    <textarea class="form-control" style="min-width: 100%" rows="5" id="textAreaEmbed" readonly="readonly"><?php
                    $code = str_replace("{embedURL}", $embedURL, $advancedCustom->embedCodeTemplate);
                    echo htmlentities($code);
                    ?>
                    </textarea>
                    <h4><span class="glyphicon glyphicon-share"></span> <?php echo __("Share Video"); ?> (Object): <?php echo getButtontCopyToClipboard('textAreaEmbedObject'); ?></h4>
                    <textarea class="form-control" style="min-width: 100%" rows="5" id="textAreaEmbedObject" readonly="readonly"><?php
                        $code = str_replace("{embedURL}", $embedURL, $advancedCustom->embedCodeTemplateObject);
                        echo htmlentities($code);
                    ?>
                    </textarea>
                </div>
                <?php
                if (empty($advancedCustom->disableEmailSharing)) {
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
                                    <div class="form-group">
                                        <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
                                        <div class="col-md-8 inputGroupContainer">
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                                <input name="email" placeholder="<?php echo __("E-mail Address"); ?>" class="form-control"  type="text">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Text area -->

                                    <div class="form-group">
                                        <label class="col-md-4 control-label"><?php echo __("Message"); ?></label>
                                        <div class="col-md-8 inputGroupContainer">
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                                                <textarea class="form-control" name="comment" placeholder="<?php echo __("Message"); ?>"><?php echo __("I would like to share this video with you:"); ?> <?php echo $URLFriendly; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label"><?php echo __("Type the code"); ?></label>
                                        <div class="col-md-8 inputGroupContainer">
                                            <div class="input-group">
                                                <span class="input-group-addon"><img src="<?php echo $global['webSiteRootURL']; ?>captcha?<?php echo time(); ?>" id="captcha"></span>
                                                <span class="input-group-addon"><span class="btn btn-xs btn-success" id="btnReloadCapcha"><span class="glyphicon glyphicon-refresh"></span></span></span>
                                                <input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="captchaText">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Button -->
                                    <div class="form-group">
                                        <label class="col-md-4 control-label"></label>
                                        <div class="col-md-8">
                                            <button type="submit" class="btn btn-primary" ><?php echo __("Send"); ?> <span class="glyphicon glyphicon-send"></span></button>
                                        </div>
                                    </div>

                                </fieldset>
                            </form>
                            <script>
                                $(document).ready(function () {
                                    $('#btnReloadCapcha').click(function () {
                                        $('#captcha').attr('src', '<?php echo $global['webSiteRootURL']; ?>captcha?' + Math.random());
                                        $('#captchaText').val('');
                                    });
                                    $('#contact_form').submit(function (evt) {
                                        evt.preventDefault();
                                        modal.showPleaseWait();
                                        $.ajax({
                                            url: '<?php echo $global['webSiteRootURL']; ?>objects/sendEmail.json.php',
                                            data: $('#contact_form').serializeArray(),
                                            type: 'post',
                                            success: function (response) {
                                                modal.hidePleaseWait();
                                                if (!response.error) {
                                                    avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your message has been sent!"); ?>", "success");
                                                } else {
                                                    avideoAlert("<?php echo __("Your message could not be sent!"); ?>", response.error, "error");
                                                }
                                                $('#btnReloadCapcha').trigger('click');
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
                    ?>
                    <div class="tab-pane" id="tabPermaLink">
                        <div class="form-group">
                            <label class="control-label"><?php echo __("Permanent Link") ?></label>
                            <?php
                            getInputCopyToClipboard('linkPermanent', $permaLink);
                            ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?php echo __("URL Friendly") ?> (SEO)</label>
                            <?php
                            getInputCopyToClipboard('linkFriendly', $URLFriendly);
                            ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?php echo __("Current Time") ?> (SEO)</label>
                            <?php
                            getInputCopyToClipboard('linkCurrentTime', $URLFriendly);
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>   
