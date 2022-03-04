<?php
$total = 8;
$donation = User::getDonationButtons(User::getId());

$donnationArray = array();

foreach ($donation as $value) {
    $donnationArray[$value->index] = $value;
}
?>
<link href="<?php echo getCDN(); ?>vendor/mervick/emojionearea/dist/emojionearea.min.css" rel="stylesheet">
<style>
    .emojionearea-picker{
        z-index: 99;
    }
</style>
<div id="<?php echo $tabId; ?>" class="tab-pane fade in" style="padding: 10px 0;">
    <div class="row" id="saveDonationDiv">
        <?php
        for ($i = 0; $i < $total; $i++) {
            $enableDonationButtonSwitch = false;
            $donationValue = '';
            $donationLabel = '';
            $donationThankyou = '';
            $donationFlyIcon = '';
            $donationFlyIconSwitch = false;
            if (!empty($donnationArray[$i])) {
                $enableDonationButtonSwitch = 'checked';
                $donationValue = $donnationArray[$i]->value;
                $donationLabel = $donnationArray[$i]->label;
                $donationThankyou = $donnationArray[$i]->thankyou;
                $donationFlyIcon = $donnationArray[$i]->donationFlyIcon;
                $donationFlyIconSwitch = (empty($donnationArray[$i]->donationFlyIconSwitch) || $donnationArray[$i]->donationFlyIconSwitch == 'false') ? '' : 'checked';
            }
            ?>
            <div class="col-sm-3" style="z-index: <?php echo $total - $i; ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">                        
                        <div class="material-switch">
                            <?php echo __("Enable Donation Button"); ?>
                            <input class="enableDonationButtonSwitch" data-toggle="toggle" type="checkbox" value="<?php echo $i; ?>" 
                                   id="enableDonationButtonSwitch<?php echo $i; ?>" <?php echo $enableDonationButtonSwitch; ?>>
                            <label for="enableDonationButtonSwitch<?php echo $i; ?>" class="label-success"></label>
                        </div>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <div class="col-md-12 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fas fa-dollar-sign"></i></span>
                                    <input id="donationValue<?php echo $i; ?>" placeholder="<?php echo __("Value to donate"); ?>" class="form-control donationValue"  type="number" value="<?php echo $donationValue; ?>" step="<?php echo YPTWallet::getStep(); ?>" required >
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fas fa-pen"></i></span>
                                    <input id="donationLabel<?php echo $i; ?>" placeholder="<?php echo __("Label"); ?>" class="form-control donationLabel"  type="text" value="<?php echo $donationLabel; ?>" required >
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fas fa-check"></i></span>
                                    <input id="donationThankyou<?php echo $i; ?>" placeholder="{user} <?php echo __("Thank you for the donation of"); ?> {value}" class="form-control donationThankyou"  type="text" value="<?php echo $donationThankyou; ?>" required >
                                </div>
                                <small class="text-muted">Use the placeholder {user} and {value} for dinamically replacement</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fas fa-birthday-cake"></i></span>
                                    <input id="donationFlyIcon<?php echo $i; ?>" placeholder="<?php echo __("Fly Icon"); ?>" class="form-control donationFlyIcon"  type="text" value="<?php echo $donationFlyIcon; ?>" required >
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12" style="padding: 10px 20px;">
                                <div class="material-switch material-small">
                                    <?php echo __("Enable Fly Icons"); ?>
                                    <input class="donationFlyIconSwitch" data-toggle="toggle" type="checkbox" value="<?php echo $i; ?>" 
                                           id="donationFlyIconSwitch<?php echo $i; ?>" <?php echo $donationFlyIconSwitch; ?>>
                                    <label for="donationFlyIconSwitch<?php echo $i; ?>" class="label-primary"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>

    <!-- Button -->
    <div class="form-group">
        <hr>
        <div class="col-md-12">
            <center>
                <button type="button" class="btn btn-primary btn-block btn-lg" id="saveDonationBTN">
                    <span class="fa fa-save"></span> <?php echo __("Save Donation"); ?>
                </button>
            </center>
        </div>
    </div>

</div>
<script src="<?php echo getCDN(); ?>vendor/emojione/emojione/lib/js/emojione.min.js"></script>
<script src="<?php echo getCDN(); ?>vendor/mervick/emojionearea/dist/emojionearea.min.js"></script>

<script type="text/javascript">

    function getDonnationButtonValues() {
        var donationButtonsList = [];
        $('.enableDonationButtonSwitch:checked').each(function () {
            var donationButton = {};
            var index = $(this).val();
            donationButton.value = parseFloat($('#donationValue' + index).val());
            if (donationButton.value > 0) {
                donationButton.index = index;
                donationButton.label = $('#donationLabel' + index).val();
                donationButton.thankyou = $('#donationThankyou' + index).val();
                donationButton.donationFlyIcon = $('#donationFlyIcon' + index).data("emojioneArea").getText();
                donationButton.donationFlyIconSwitch = $('#donationFlyIconSwitch' + index).is(':checked');
                donationButtonsList.push(donationButton);
            }
        });
        console.log(donationButtonsList);
        return {donationButtonsList: donationButtonsList};
    }

    function saveUsersDonationInput() {
        modal.showPleaseWait();
        var data = getDonnationButtonValues();
        $.ajax({
            url: webSiteRootURL + 'plugin/CustomizeUser/View/saveDonationButtons.json.php',
            data: data,
            type: 'post',
            success: function (response) {
                modal.hidePleaseWait();
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __('Extra info saved'); ?>");
                }
            }
        });
    }
    $(document).ready(function () {
        $('#saveDonationBTN').click(function () {
            saveUsersDonationInput();
        });

        emojione.imagePathPNG = webSiteRootURL + 'vendor/emojione/assets/png/32/';

        $(".donationFlyIcon").emojioneArea({
            standalone: true,
            autocomplete: false,
            useInternalCDN: false,
            pickerPosition: "right"
        });
    });
</script>

