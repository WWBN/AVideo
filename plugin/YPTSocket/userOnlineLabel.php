<div class="users_id_online_label users_id_<?php echo $users_id; ?> <?php echo $class; ?>" style="<?php echo $style; ?>">
    <span class="label label-danger"><?php echo __('Offline'); ?></span>
    <span class="label label-success"><?php echo __('Online'); ?></span>
    <?php
    if(User::isLogged() && $users_id !== User::getId()){
        $shouldShowCaller = YPTSocket::shouldShowCaller();
        if ($shouldShowCaller->show) {
            ?>
            <button class="btn btn-default btn-xs callerButton" type="button" 
                    onclick="event.preventDefault(); return callUserNow(<?php echo $users_id; ?>);" 
                    data-toggle="tooltip" data-placement="bottom"  title="<?php echo __('Call'); ?>">
                <i class="fas fa-phone"></i>
            </button>
            <?php
        } else {
            echo "<!-- shouldShowCaller {$shouldShowCaller->reason} -->";
        }
    }
    ?>
</div>