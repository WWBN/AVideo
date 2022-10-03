<!-- right menu start -->
<li class="dropdown hasNothingToDelete hasNothingToShow" id="topMenuUserNotifications">    
    <a href="#" class="faa-parent animated-hover btn btn-default btn-light navbar-btn hideWhenHasNothingToShow" data-toggle="dropdown" >
        <i class="fas fa-bell faa-ring" data-toggle="tooltip" title="<?php echo __('Notifications'); ?>" data-placement="bottom" ></i>
        <span class="badge animate_animated animate__bounceIn">0</span>
    </a>
    <div class="hideWhenHasSomethingToShow">
        <a href="#" class="btn btn-default btn-light navbar-btn" data-toggle="dropdown" >
            <i class="fas fa-bell-slash text-muted" data-toggle="tooltip" title="<?php echo __('There are no notifications'); ?>" data-placement="bottom" ></i>
        </a>
    </div>
    <ul class="dropdown-menu dropdown-menu-right dropdown-menu-arrow hideWhenHasNothingToShow">
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
