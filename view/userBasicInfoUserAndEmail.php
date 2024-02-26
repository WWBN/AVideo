<?php
$userText = __("User");
$userPlaceholder = $userText;
$userType = 'text';
$userReadOnly = '';
$emailDivClass = '';
$emailReadOnly = '';
$userVerifyEmailBtnDivClass = 'hidden';
$userColSize = 'col-md-8';
$users_id = $user->getId();

$emailVerified = $user->getEmailVerified();

if ($advancedCustomUser->forceLoginToBeTheEmail) {
    $userText = __("E-mail");
    $userPlaceholder = 'me@example.com';
    $userType = 'email';
    $emailReadOnly = 'readonly';
    $emailDivClass = 'hidden';
    $userVerifyEmailBtnDivClass = '';
    $userColSize = 'col-md-5';
}
if (AVideoPlugin::isEnabledByName("LoginLDAP") || empty($advancedCustomUser->userCanChangeUsername)) {
    $userReadOnly = 'readonly';
}

function getVerifyEmailButton($emailVerified, $class=''){
    ?>
    <div class="col-md-3 <?php echo $class; ?>">
        <?php
        if ($emailVerified) {
        ?>
            <span class="btn btn-success btn-lg btn-block"><i class="fa fa-check"></i> <?php echo __("E-mail Verified"); ?></span>
        <?php
        } else {
        ?>
            <button class="btn btn-warning btn-lg btn-block verifyEmailBtn"><i class="fa fa-envelope"></i> <?php echo __("Verify e-mail"); ?></button>
        <?php
        }
        ?>
    </div>
    <?php
}

?>

<div class="form-group">
    <label class="col-md-4 control-label"><?php echo $userText; ?></label>
    <div class="<?php echo $userColSize; ?> inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            <input id="inputUser" placeholder="<?php echo $userPlaceholder; ?>" class="form-control" type="<?php echo $userType; ?>" value="<?php echo $user->getUser(); ?>" required <?php echo $userReadOnly; ?>>
        </div>
    </div>
    <?php
        getVerifyEmailButton($emailVerified, $userVerifyEmailBtnDivClass);
    ?>
</div>

<div class="form-group <?php echo $emailDivClass; ?>">
    <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
    <div class="col-md-5 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
            <input id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" class="form-control" type="email" value="<?php echo $user->getEmail(); ?>" required <?php echo $emailReadOnly; ?>>
        </div>
    </div>
    <?php
        getVerifyEmailButton($emailVerified);
    ?>
</div>
<script>
    $(document).ready(function() {
        <?php
        if (!empty($advancedCustomUser->forceLoginToBeTheEmail)) {
        ?>
            $('#inputUser').on('keyup', function() {
                $('#inputEmail').val($(this).val());
            });
        <?php
        }
        if (!$emailVerified) {
        ?>
            $('.verifyEmailBtn').click(function(e) {
                e.preventDefault();
                modal.showPleaseWait();
                $.ajax({
                    type: "POST",
                    url: webSiteRootURL+"objects/userVerifyEmail.php?users_id=<?php echo $users_id; ?>"
                }).done(function(response) {
                    avideoResponse(response);
                    modal.hidePleaseWait();
                });
            });
        <?php
        }
        ?>
    });
</script>