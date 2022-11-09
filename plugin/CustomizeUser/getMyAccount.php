<?php
if (User::canUpload()) {
    ?>

    <div class="form-group">
        <label class="col-md-4 control-label">
            <?php echo __("Website"); ?>
        </label>
        <div class="col-md-8 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-globe"></i></span>
                <input id="userWebsite" placeholder="<?php echo __("Website"); ?>" class="form-control" type="url" value="<?php echo User::getWebsite(); ?>">
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#userWebsite').change(function (e) {
                saveUserSite();
            });
        });

        function saveUserSite() {
            var userWebsite = $('#userWebsite').val();
            if(empty(userWebsite) || validURL(userWebsite)){
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'plugin/CustomizeUser/getMyAccount.save.json.php',
                    data: {userWebsite: userWebsite},
                    type: 'post',
                    success: function (response) {
                        avideoResponse(response);
                        modal.hidePleaseWait();
                    }
                });
            }
        }
    </script>
    <?php
}
?>