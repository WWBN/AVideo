<?php
if ($obj->allowDonationLink && !empty($video['users_id'])) {
    $u = new User($video['users_id']);
    $donationLink = $u->getDonationLink();
    if (!empty($donationLink)) {
        ?>
        <a class="btn btn-success no-outline" href="<?php echo $donationLink; ?>" target="_blank">
            <i class="fas fa-donate"></i> <small><?php echo __($obj->donationButtonLabel); ?> <i class="fas fa-external-link-alt"></i></small>
        </a>    
        <?php
    }
}
if ($obj->allowWalletDirectTransferDonation && !empty($video['users_id']) && class_exists("YPTWallet")) {
    if (!User::isLogged()) {
        ?>
        <a class="btn btn-warning no-outline" href="<?php echo $global['webSiteRootURL']; ?>user">
            <i class="fas fa-donate"></i> <small><?php echo __("Please login to donate"); ?></small>
        </a>    
        <?php
    } else if (class_exists("YPTWallet")) {
        $u = new User($video['users_id']);
        $uid = uniqid();
        $captcha = User::getCaptchaForm($uid);
        ?>
        <button class="btn btn-success no-outline" onclick="openDonationMoodal<?php echo $uid; ?>();">
            <i class="fas fa-donate"></i> <small><?php echo __($obj->donationWalletButtonLabel); ?></small>
        </button>   
        <div id="donationModal<?php echo $uid; ?>" class="modal fade" tabindex="-1" role="dialog" >
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><img src="<?php echo $u->getPhotoDB(); ?>" class="img img-circle img-responsive " style="height: 30px; float: left;" > <strong style="margin: 10px 0 0 10px;"><?php echo $u->getNameIdentificationBd(); ?></strong></h4>
                    </div>
                    <div class="modal-body">

                        <form id="donationForm<?php echo $uid; ?>" class="form-compact well form-horizontal">
                            <div class="form-group">
                                <div class="col-md-12 inputGroupContainer">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fas fa-donate"></i></span>
                                        <input id="donationValue<?php echo $uid; ?>" placeholder="<?php echo __("Value to donate"); ?>" class="form-control"  type="number" value="" step="<?php echo YPTWallet::getStep(); ?>" required >
                                    </div>
                                </div>
                            </div>
                            <?php
                            if (empty($obj->disableCaptchaOnWalletDirectTransferDonation)) {
                                ?>
                                <div class="form-group" id="donationCaptcha<?php echo $uid; ?>">
                                    <div class="col-md-12 ">
                                        <?php echo $captcha; ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-block" onclick="submitDonation<?php echo $uid; ?>();" ><i class="fas fa-hand-holding-usd"></i> <?php echo __("Confirm Donation"); ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <script>
            
            function openDonationMoodal<?php echo $uid; ?>() {
                $('#btnReloadCapcha<?php echo $uid; ?>').trigger('click');
                $('#donationModal<?php echo $uid; ?>').modal();
                $('#donationValue<?php echo $uid; ?>').focus();
                setTimeout(function(){$('#donationValue<?php echo $uid; ?>').focus();}, 500);
            }
            
            function submitDonation<?php echo $uid; ?>() {
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/donate.json.php',
                    data: {
                        "value": $('#donationValue<?php echo $uid; ?>').val(),
                        "videos_id": <?php echo intval(@$video['id']); ?>,
                        "users_id": <?php echo intval(@$video['users_id']); ?>,
                        "captcha": $('#captchaText<?php echo $uid; ?>').val()
                    },
                    type: 'post',
                    success: function (response) {
                        $("#btnReloadCapcha<?php echo $uid; ?>").trigger('click');
                        modal.hidePleaseWait();
                        if (response.error) {
                            avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                        } else {
                            avideoToastSuccess("<?php echo __("Thank you!"); ?>");
                            $('#donationModal<?php echo $uid; ?>').modal('hide');
                            $(".walletBalance").text(response.walletBalance);
                        }
                    }
                });
            }
            $(document).ready(function () {
                $("#donationForm<?php echo $uid; ?>").submit(function (e) {
                    e.preventDefault();
                    submitDonation<?php echo $uid; ?>();
                    return false;
                });
            });
        </script>
        <?php
    }
}
?>