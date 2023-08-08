<?php


foreach (CustomizeUser::getSocialMedia() as $platform => $details) {
    if (empty($details['isActive'])) {
        continue;
    }
?>
    <div class="form-group">
        <label class="col-md-4 control-label">
            <?php echo $details['label']; ?>
        </label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="<?php echo $details['icon']; ?>"></i></span>
                <input id="user<?php echo ucfirst($platform); ?>" platform="<?php echo $platform; ?>" placeholder="<?php echo $details['placeholder']; ?>" class="form-control CustomizeUserSocialMedia" type="url" value="<?php echo User::getSocialMediaURL($platform); ?>">
            </div>
        </div>
    </div>
<?php
}
?>

<script>
    $(document).ready(function() {
        var saveTimeout;

        $('.CustomizeUserSocialMedia').on('change keyup', function(e) {
            clearTimeout(saveTimeout); // Clear the existing timeout

            var platform = $(this).attr('platform');
            var value = $(this).val();

            saveTimeout = setTimeout(function() {
                saveUserURL(platform, value);
            }, 500);
        });
    });


    function saveUserURL(platform, val) {
        if (empty(val) || validURL(val)) {
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/CustomizeUser/getMyAccount.save.json.php',
                data: {
                    platform: platform,
                    val: val
                },
                type: 'post',
                success: function(response) {
                    avideoResponse(response);
                    modal.hidePleaseWait();
                }
            });
        }
    }
</script>