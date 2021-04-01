<link href="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css"/>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo __("Email All Users"); ?></div>
    <div class="panel-body">

        <div class="row">
            <label class="col-md-4" style="text-align: right;">
                <?php echo __('Sent only to this email'); ?>:
            </label>
            <div class="col-md-8">
                <input class="form-control" type="email" id="email" placeholder="test@email.com">
                <small><?php echo __('Leave it blank to send to all users'); ?> <?php echo __('or user group selected below'); ?></small>
            </div>
        </div>
        <div class="row">
            <label class="col-md-4" style="text-align: right;">
                <?php echo __('Filter users'); ?>:
            </label>
            <div class="col-md-8">
                <label class="radio-inline"><input type="radio" name="userGroup" value="0" checked><?php echo __("All"); ?></label>
                <?php
                $userGroups = UserGroups::getAllUsersGroupsArray();
                foreach ($userGroups as $key => $value) {
                    echo '<label class="radio-inline"><input type="radio" name="userGroup" value="' . $key . '">' . $value . '</label>';
                }
                ?>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-lg-12">
                <textarea id="emailMessage" placeholder="<?php echo __("Enter text"); ?> ..." style="width: 100%;"></textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <button type="button" class="btn btn-success btn-block" id="sendSubscribeBtn">
                    <i class="fas fa-envelope-square"></i> <?php echo __("Send Email"); ?>
                </button>
            </div>
        </div>

    </div>
</div>

<script src="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
<script>
    function notify() {
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/emailAllUsers.json.php',
            method: 'POST',
            data: {
                'message': $('#emailMessage').val(),
                'email': $('#email').val(),
                'users_groups_id': $('input[name="userGroup"]:checked').val()
            },
            success: function (response) {
                console.log(response);
                if (response.error) {
                    avideoAlert("<?php echo __("Sorry!"); ?>", response.msg[0], "error");
                } else {
                    avideoAlert("<?php echo __("Success"); ?>", "You have sent " + response.count + " emails", "success");
                }
                modal.hidePleaseWait();
            }
        });
    }
    $(document).ready(function () {
        $('#emailMessage').wysihtml5();
        $("#sendSubscribeBtn").click(function () {
            notify();
        });

    });

</script>
