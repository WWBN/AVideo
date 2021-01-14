<?php
require_once '../../../videos/configuration.php';
if (!User::isAdmin()) {
    forbiddenPage("Must be admin");
}
?>
<div class="panel panel-default">
    <div class="panel-heading"><i class="fas fa-user"></i> <?php echo __('Extra Info') ?></div>
    <div class="panel-body" style="text-align: left;">
        <?php
        if (User::isAdmin()) {
            echo "<a href='{$global['webSiteRootURL']}plugin/CustomizeUser/View/editor.php' class='btn btn-default btn-block'>" . __('Add more fields') . "</a>";
        }
        $u = new User($_REQUEST['users_id']);
        echo "<b>id:</b> " . $u->getBdId() . "<br>";
        echo "<b>User:</b> " . $u->getUser() . "<br>";
        echo "<b>Name:</b> " . $u->getBdName() . "<br>";
        echo "<b>Email:</b> " . $u->getEmail() . "<br>";
        $rows = Users_extra_info::getAllActive($_REQUEST['users_id']);
        foreach ($rows as $value) {
            $val = nl2br($value['current_value']);
            echo "<b>{$value['field_name']}:</b> {$val}<br>";
        }
        ?>
    </div>
</div>
