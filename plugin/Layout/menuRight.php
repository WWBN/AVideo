<script src="<?php echo getCDN(); ?>plugin/Layout/notifications.js" type="text/javascript"></script>
<style>
    #LayoutNotification .navbar-btn .LayoutNotificationCount{
        background: rgba(255,0,0,1); color: #FFF;
    }
    #LayoutNotificationItems li{
        margin-right: 0;
    }
    #LayoutNotificationItems .notificationLink{
        display: inline-flex;
        padding: 5px;
    }
    #LayoutNotificationItems .notificationLink img{
        margin: 2px 10px 2px 5px;
        max-width: 38px;
    }
</style>
<li class="dropdown" id="LayoutNotification">
    <a href="#" class=" btn btn-default navbar-btn" data-toggle="dropdown">
        <i class="fa fa-bell"></i>
        <span class="badge LayoutNotificationCount" >0</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-right notify-drop" id="LayoutNotificationItems"></ul>
</li>