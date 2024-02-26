<style>
    .file-caption {
        padding: 6px 12px !important;
    }

    .file-preview-frame,
    .krajee-default.file-preview-frame .kv-file-content {
        width: 95%;
        height: auto;
    }

    #updateUserForm>div>div>div>span {
        min-width: 50px;
        ;
    }
</style>
<form class="form-compact well form-horizontal" id="updateUserForm" onsubmit="">
    <?php
    $bgURL = User::getBackgroundURLFromUserID(User::getId());
    ?>
    <div class="form-group">
        <label class="col-md-4 control-label"><?php echo __("Name"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                <input id="inputName" placeholder="<?php echo __("Name"); ?>" class="form-control" type="text" value="<?php echo $user->getName(); ?>" required>
            </div>
        </div>
    </div>
    <?php
    include $global['systemRootPath'] . 'view/userBasicInfoUserAndEmail.php';
    ?>
    <div class="form-group">
        <label class="col-md-4 control-label"><?php echo __("New Password"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <?php
            getInputPassword("inputPassword", 'class="form-control"  autocomplete="off"', __("New Password"));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label"><?php echo __("Confirm New Password"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <?php
            getInputPassword("inputPasswordConfirm", 'class="form-control"  autocomplete="off"', __("Confirm New Password"));
            ?>
        </div>
    </div>

    <div class="form-group <?php
                            if (!empty($advancedCustomUser->doNotShowPhone)) {
                                echo " hidden ";
                            }
                            ?>">
        <label class="col-md-4 control-label"><?php echo __("Phone"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-phone"></i></span>
                <input id="phone" placeholder="<?php echo __("Phone"); ?>" class="form-control" type="text" value="<?php echo $user->getPhone(); ?>">
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label"><?php echo __("Birth Date"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa-solid fa-cake-candles"></i></span>
                <input id="inputBirth" placeholder="<?php echo __("Birth Date"); ?>" class="form-control" type="date" value="<?php echo $user->getBirth_date(); ?>">
            </div>
        </div>
    </div>

    <div class="form-group <?php
                            if (!empty($advancedCustomUser->doNotShowMyChannelNameOnBasicInfo)) {
                                echo " hidden ";
                            }
                            ?>">
        <label class="col-md-4 control-label"><?php echo __("Channel Name"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-play-circle"></i></span>
                <input id="channelName" placeholder="<?php echo __("Channel Name"); ?>" class="form-control" type="text" value="<?php echo $user->getChannelName(); ?>">
            </div>
        </div>
    </div>

    <?php
    if (User::canUpload() || User::canStream()) {
        $embedURL = "{$global['webSiteRootURL']}channel/" . $user->getChannelName() . '/liveNow';
        $embedURL = addQueryStringParameter($embedURL, 'muted', 1);
        $search = ['{embedURL}', '{videoLengthInSeconds}'];
        $replace = [$embedURL, 0];
    ?>
        <div class="form-group">
            <label class="col-md-4 control-label">
                <?php echo __("Embed Player"); ?>
                <br>
                <?php getButtontCopyToClipboard('textAreaEmbed'); ?>
            </label>
            <div class="col-md-8 inputGroupContainer">
                <textarea class="form-control min-width: 100%; margin: 10px 0 20px 0;" rows="2" id="textAreaEmbed" readonly="readonly"><?php
                                                                                                                                        $code = str_replace($search, $replace, $advancedCustom->embedCodeTemplate);
                                                                                                                                        echo htmlentities($code);
                                                                                                                                        ?></textarea>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="form-group <?php
                            if (empty($advancedCustomUser->allowDonationLink)) {
                                echo " hidden ";
                            }
                            ?>">
        <label class="col-md-4 control-label"><?php echo __("Donation Link"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-donate"></i></span>
                <input id="donationLink" placeholder="<?php echo __("Donation Link"); ?>" class="form-control" type="url" value="<?php echo $user->getDonationLink(); ?>">
            </div>
        </div>
    </div>

    <div class="form-group <?php
                            if (!empty($advancedCustomUser->doNotShowMyAnalyticsCodeOnBasicInfo)) {
                                echo " hidden ";
                            }
                            ?>">
        <label class="col-md-4 control-label"><?php echo __("Analytics Code"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-chart-line"></i></span>
                <input id="analyticsCode" placeholder="UA-123456789-1" class="form-control" type="text" value="<?php echo $user->getAnalyticsCode(); ?>">
            </div>
            <small><?php echo __("Track your videos with Google analytics"); ?></small>
        </div>
    </div>

    <div class="form-group <?php
                            if (!empty($advancedCustomUser->doNotShowMyAboutOnBasicInfo)) {
                                echo " hidden ";
                            }
                            ?> ">
        <label class="col-md-4 control-label"><?php echo __("About"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <textarea id="textAbout" placeholder="<?php echo __("About"); ?>" class="form-control"><?php echo $user->getAbout(); ?></textarea>
            <?php
            echo getTinyMCE("textAbout", true);
            ?>
        </div>
    </div>

    <?php
    AVideoPlugin::getMyAccount(User::getId());
    ?>
    <div class="row">
        <div class="col-sm-3">
            <?php
            include $global['systemRootPath'] . 'view/userPhotoUploadInclude.php';
            ?>
        </div>
        <div class="col-sm-9">
            <?php
            $channelArtRelativePath = User::getBackgroundURLFromUserID(User::getId());

            $finalWidth = 2560;
            $finalHeight = 1440;
            if (isMobile()) {
                $screenWidth = 640;
                $screenHeight = 360;
            } else {
                $screenWidth = 960;
                $screenHeight = 540;
            }
            $factorW = $screenWidth / $finalWidth;
            include $global['systemRootPath'] . 'view/userChannelArtUploadInclude.php';
            ?>
        </div>
    </div>

    <!-- Button -->
    <div class="form-group">
        <hr>
        <div class="col-md-12">
            <center>
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <span class="fa fa-save"></span> <?php echo __("Save"); ?>
                </button>
            </center>
        </div>
    </div>
    <script>
        var uploadCrop;

        function isAnalytics() {
            return true;
            str = $('#analyticsCode').val();
            return str === '' || (/^ua-\d{4,9}-\d{1,4}$/i).test(str.toString());
        }

        function updateUserFormSubmit() {
            var content;
            if (window.tinyMCE) {
                content = tinyMCE.get('textAbout') ? tinyMCE.get('textAbout').getContent() : '';
            } else {
                content = $('#textAbout').val();
            }

            $.ajax({
                url: webSiteRootURL + 'objects/userUpdate.json.php?do_not_login=1',
                data: {
                    "user": $('#inputUser').val(),
                    "pass": $('#inputPassword').val(),
                    "email": $('#inputEmail').val(),
                    "phone": $('#phone').val(),
                    "birth_date": $('#inputBirth').val(),
                    "name": $('#inputName').val(),
                    "about": content,
                    "channelName": $('#channelName').val(),
                    "donationLink": $('#donationLink').val(),
                    "analyticsCode": $('#analyticsCode').val(),
                },
                type: 'post',
                success: function(response) {
                    avideoResponse(response);
                    modal.hidePleaseWait();
                }
            });
        }
        $(document).ready(function() {
            $('#updateUserForm').submit(function(evt) {
                evt.preventDefault();
                if (!isAnalytics()) {
                    avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your analytics code is wrong"); ?>", "error");
                    $('#inputAnalyticsCode').focus();
                    return false;
                }
                $('#aBasicInfo').tab('show');
                modal.showPleaseWait();
                var pass1 = $('#inputPassword').val();
                var pass2 = $('#inputPasswordConfirm').val();
                // Password doesn't match
                if (pass1 != '' && pass1 != pass2) {
                    modal.hidePleaseWait();
                    avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your password does not match!"); ?>", "error");
                    return false;
                } else {
                    updateUserFormSubmit();
                    return false;
                }
            });
        });
    </script>
</form>