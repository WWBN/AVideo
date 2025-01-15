<?php
if (!empty($users_id) && User::isLogged() && $users_id != User::getId() && CustomizeUser::isChannelCallEnabled()) {
    $identification = User::getNameIdentificationById($users_id);
    ?>
    <button class="btn btn-default <?php echo $class; ?> caller<?php echo $users_id; ?>" 
            type="button" onclick="avideoModalIframeFull(webSiteRootURL + 'plugin/WebRTC/call/callUser.php?users_id=<?php echo $users_id; ?>');" 
            data-toggle="tooltip" data-placement="bottom"  title="<?php echo __('Call'), ' ', $identification; ?>"
            style="display: none;" id="caller<?php echo $users_id; ?>">
        <i class="fas fa-phone"></i>
    </button>
    <script>
        $(function () {
            callerCheckUser(<?php echo $users_id; ?>);
        });
    </script>
    <?php
}