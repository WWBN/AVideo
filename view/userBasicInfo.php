<form class="form-compact well form-horizontal"  id="updateUserForm" onsubmit="">
    <?php
    $bgURL = User::getBackgroundURLFromUserID(User::getId());
    ?>
    <style>
        .file-caption{
            padding: 6px 12px !important;
        }
        .file-preview-frame,.krajee-default.file-preview-frame .kv-file-content {
            width: 95%;
            height: auto;
        }
    </style>
    <div class="form-group">
        <label class="col-md-4 control-label"><?php echo __("Name"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                <input  id="inputName" placeholder="<?php echo __("Name"); ?>" class="form-control"  type="text" value="<?php echo $user->getName(); ?>" required >
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label"><?php echo!empty($advancedCustomUser->forceLoginToBeTheEmail) ? __("E-mail") : __("User"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                <input  id="inputUser" placeholder="<?php echo!empty($advancedCustomUser->forceLoginToBeTheEmail) ? "me@example.com" : __("User"); ?>" class="form-control"  type="<?php echo empty($advancedCustomUser->forceLoginToBeTheEmail) ? "text" : "email" ?>" value="<?php echo $user->getUser(); ?>" required <?php echo (AVideoPlugin::isEnabledByName("LoginLDAP") || empty($advancedCustomUser->userCanChangeUsername)) ? "readonly" : ""; ?>  >
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
        <div class="col-md-6 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                <input  id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" class="form-control"  type="email" value="<?php echo $user->getEmail(); ?>" required
                <?php
                if (!empty($advancedCustomUser->forceLoginToBeTheEmail)) {
                    echo "readonly";
                }
                ?>    >
            </div>
        </div>
        <div class="col-md-2">
            <?php
            if ($user->getEmailVerified()) {
                ?>
                <span class="btn btn-success"><i class="fa fa-check"></i> <?php echo __("E-mail Verified"); ?></span>
                <?php
            } else {
                ?>
                <button class="btn btn-warning" id="verifyEmail"><i class="fa fa-envelope"></i> <?php echo __("Verify e-mail"); ?></button>

                <script>
                    $(document).ready(function () {

                        $('#verifyEmail').click(function (e) {
                            e.preventDefault();
                            modal.showPleaseWait();
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $global['webSiteRootURL'] ?>objects/userVerifyEmail.php?users_id=<?php echo $user->getBdId(); ?>"
                                            }).done(function (response) {
                                                if (response.error) {
                                                    avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                                } else {
                                                    avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Verification Sent"); ?>", "success");
                                                }
                                                modal.hidePleaseWait();
                                            });
                                        });

                                    });
                </script>
                <?php
            }
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
                <input  id="phone" placeholder="<?php echo __("Phone"); ?>" class="form-control"  type="text" value="<?php echo $user->getPhone(); ?>" >
            </div>
        </div>
    </div>

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
    if (!empty($advancedCustomUser->doNotShowMyChannelNameOnBasicInfo)) {
        echo " hidden ";
    }
    ?>">
        <label class="col-md-4 control-label"><?php echo __("Channel Name"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-play-circle"></i></span>
                <input  id="channelName" placeholder="<?php echo __("Channel Name"); ?>" class="form-control"  type="text" value="<?php echo $user->getChannelName(); ?>" >
            </div>
        </div>
    </div>

    <div class="form-group <?php
    if (empty($advancedCustomUser->allowDonationLink)) {
        echo " hidden ";
    }
    ?>">
        <label class="col-md-4 control-label"><?php echo __("Donation Link"); ?></label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-donate"></i></span>
                <input  id="donationLink" placeholder="<?php echo __("Donation Link"); ?>" class="form-control"  type="url" value="<?php echo $user->getDonationLink(); ?>" >
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
                <input  id="analyticsCode" placeholder="UA-123456789-1" class="form-control"  type="text" value="<?php echo $user->getAnalyticsCode(); ?>" >
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
            <textarea id="textAbout" placeholder="<?php echo __("About"); ?>" class="form-control"  ><?php echo $user->getAbout(); ?></textarea>
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

            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/userUpdate.json.php?do_not_login=1',
                data: {
                    "user": $('#inputUser').val(),
                    "pass": $('#inputPassword').val(),
                    "email": $('#inputEmail').val(),
                    "phone": $('#phone').val(),
                    "name": $('#inputName').val(),
                    "about": $('#textAbout').val(),
                    "channelName": $('#channelName').val(),
                    "donationLink": $('#donationLink').val(),
                    "analyticsCode": $('#analyticsCode').val(),
                },
                type: 'post',
                success: function (response) {
                    avideoResponse(response);
                    modal.hidePleaseWait();
                }
            });
        }
        $(document).ready(function () {

<?php
if (!empty($advancedCustomUser->forceLoginToBeTheEmail)) {
    ?>
                $('#inputUser').on('keyup', function () {
                    $('#inputEmail').val($(this).val());
                });
    <?php
}
?>


            
            $('#updateUserForm').submit(function (evt) {
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