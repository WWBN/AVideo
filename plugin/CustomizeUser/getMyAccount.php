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
            <div class="input-group largeSocialIcon">
                <span class="input-group-addon"><i class="<?php echo $details['icon']; ?>"></i></span>
                <input id="user<?php echo ucfirst($platform); ?>" platform="<?php echo $platform; ?>" 
                placeholder="<?php echo $details['placeholder']; ?>" 
                class="form-control CustomizeUserSocialMedia" 
                type="url" value="<?php echo User::getSocialMediaURL($platform); ?>">
            </div>
        </div>
    </div>
<?php
}
?>

<script>
    var prevValue = {}; // Object to store previous values for each input
    $(document).ready(function() {
        var saveTimeout;


        $('.CustomizeUserSocialMedia').on('input', function(e) {
            var platform = $(this).attr('platform');
            var currentValue = $(this).val();

            var id = $(this).attr('id');
            if(id=='userWhatsapp'){
                
            }
            
            // Check if the value actually changed
            if (currentValue !== prevValue[platform]) {
                clearTimeout(saveTimeout);

                saveTimeout = setTimeout(function() {
                    saveUserURL(platform, currentValue);
                }, 500);
            }

            // Update the stored value for the next comparison
            prevValue[platform] = currentValue;
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