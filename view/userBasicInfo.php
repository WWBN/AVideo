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
    <label class="col-md-4 control-label"><?php echo __("User"); ?></label>
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
    </div>
</div>

<?php
AVideoPlugin::getMyAccount(User::getId());
?>
<div class="row">
    <div class="col-sm-3">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __("Profile Photo"); ?><br><small><?php echo __("You must click save to confirm"); ?></small></div>
            <div class="panel-body">
                <div class="form-group">
                    <div class="col-md-12 ">
                        <div id="croppie"></div>
                        <center>
                            <a id="upload-btn" class="btn btn-primary"><i class="fa fa-upload"></i> <?php echo __("Upload a Photo"); ?></a>
                        </center>
                        <div class="alert alert-info">
                            <?php echo __("Make sure you click on the Save button after change the photo"); ?>
                        </div>
                    </div>
                    <input type="file" id="upload" value="Choose a file" accept="image/*" style="display: none;" />
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-9">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __("Channel Art"); ?><br>
                <small><?php echo __("For the best results, please Use this image as a guide to create your Channel Art"); ?></small>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-7">
                        <input id="input-jpg" type="file" class="file-loading" accept="image/*">
                    </div>
                    <div class="col-sm-5">
                        <img src="<?php echo $global['webSiteRootURL']; ?>view/img/sampleGuide.png" class="img img-responsive">
                        <hr>
                        <b><?php echo __("minImageWidth"); ?>:</b> 2048px<br>
                        <b><?php echo __("minImageHeight"); ?>:</b> 1152px<br>
                        <b><?php echo __("maxImageWidth"); ?>:</b> 2560px<br>
                        <b><?php echo __("maxImageHeight"); ?>:</b> 1440px<br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var uploadCrop;
    function isAnalytics() {
        return true;
        str = $('#analyticsCode').val();
        return str === '' || (/^ua-\d{4,9}-\d{1,4}$/i).test(str.toString());
    }
    function readFile(input, crop) {
        console.log(input);
        console.log($(input)[0]);
        console.log($(input)[0].files);
        if ($(input)[0].files && $(input)[0].files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                crop.croppie('bind', {
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

    function updateUserFormSubmit() {

        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/userUpdate.json.php?do_not_login=1',
            data: {
                "user": $('#inputUser').val(),
                "pass": $('#inputPassword').val(),
                "email": $('#inputEmail').val(),
                "name": $('#inputName').val(),
                "about": $('#textAbout').val(),
                "channelName": $('#channelName').val(),
                "donationLink": $('#donationLink').val(),
                "analyticsCode": $('#analyticsCode').val()
            },
            type: 'post',
            success: function (response) {
                if (response.status > "0") {
                    uploadCrop.croppie('result', {
                        type: 'canvas',
                        size: 'viewport'
                    }).then(function (resp) {
                        console.log("userSavePhoto");
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $global['webSiteRootURL']; ?>objects/userSavePhoto.php",
                            data: {
                                imgBase64: resp
                            },
                            success: function () {
                                console.log("SavePersonal");
                                modal.hidePleaseWait();
<?php if (empty($advancedCustomUser->disablePersonalInfo)) { ?>
                                    savePersonalInfo();
<?php } ?>
                            }
                        });
                    });
                } else if (response.error) {
                    avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                    modal.hidePleaseWait();
                } else {
                    avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been updated!"); ?>", "error");
                    modal.hidePleaseWait();
                }
            }
        });
    }
    $(document).ready(function () {

        $("#input-jpg").fileinput({
            uploadUrl: "<?php echo $global['webSiteRootURL']; ?>objects/uploadChannelArt.php",
            autoReplace: true,
            overwriteInitial: true,
            showUploadedThumbs: false,
            showPreview: true,
            maxFileCount: 1,
            initialPreview: [
                "<img class='img img-responsive' src='<?php echo $global['webSiteRootURL'], User::getBackgroundURLFromUserID(User::getId()); ?>'>",
            ],
            initialCaption: 'channelArt.jpg',
            initialPreviewShowDelete: false,
            showRemove: false,
            showClose: false,
            layoutTemplates: {actionDelete: ''}, // disable thumbnail deletion
            allowedFileExtensions: ["jpg", "jpeg", "png"],
            //minImageWidth: 2048,
            //minImageHeight: 1152,
            //maxImageWidth: 2560,
            //maxImageHeight: 1440
        });

        $('#upload').on('change', function () {
            readFile(this, uploadCrop);
        });
        $('#upload-btn').on('click', function (ev) {
            $('#upload').trigger("click");
        });
<?php
if (!empty($advancedCustomUser->forceLoginToBeTheEmail)) {
    ?>
            $('#inputUser').on('keyup', function () {
                $('#inputEmail').val($(this).val());
            });
    <?php
}
?>


        uploadCrop = $('#croppie').croppie({
            url: '<?php echo $user->getPhoto(); ?>',
            enableExif: true,
            enforceBoundary: false,
            mouseWheelZoom: false,
            viewport: {
                width: 150,
                height: 150
            },
            boundary: {
                width: 150,
                height: 150
            }
        });
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
            // password dont match
            if (pass1 != '' && pass1 != pass2) {
                modal.hidePleaseWait();
                avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your password does not match!"); ?>", "error");
                return false;
            } else {
                setTimeout(function () {
                    updateUserFormSubmit();
                }, 1000);
                return false;
            }
        });
    });
</script>