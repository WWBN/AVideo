<!-- right menu start -->
<li class="dropdown" id="topMenuUserNotifications" data-toggle="tooltip" title="<?php echo __('Notifications'); ?>" data-placement="bottom">    
    <a href="#" class="faa-parent animated-hover btn btn-default btn-light navbar-btn" data-toggle="dropdown" data-toggle="tooltip" title="<?php echo __('Notifications'); ?>" data-placement="bottom" >
        <i class="fas fa-bell faa-ring"></i>
        <span class="badge badge-notify animate_animated animate__bounceIn">0</span>
        <b class="caret"></b>
    </a>
    <ul class="dropdown-menu dropdown-menu-right">
        <div class="btn-group btn-group-justified">
            <?php
            echo AVideoPlugin::getUserNotificationButton();
            ?>
        </div>
        <div id="userNotificationsFilterButtons">
            
        </div>
        <div class="list-group">
        <?php
        for($i=1;$i<=10;$i++){
            echo '<div class="priority priority'.$i.'"></div>';
        }
        ?>
        </div>
    </ul>
</li> 
<!-- right menu start -->
