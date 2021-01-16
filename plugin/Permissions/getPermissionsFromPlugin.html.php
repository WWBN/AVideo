<?php
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

if (!User::isAdmin()) {
    forbiddenPage("Not admin");
}

$permissions = array();
if (empty($_REQUEST['plugins_id'])) {
    die("empty plugins_id");
}

$obj = AVideoPlugin::getObjectDataIfEnabled("Permissions");
$permissions = Permissions::getPluginPermissions($_REQUEST['plugins_id']);
$userGroups = UserGroups::getAllUsersGroupsArray();
$uid = uniqid();
?>
<div class="panel panel-default">
    <div class="panel-heading tabbable-line">
        <ul class="nav nav-tabs">
            <?php
            $count = 0;
            foreach ($permissions as $key => $value) {
                $active = "";
                if (empty($count)) {
                    $active = "active";
                }
                $count++;
                echo "<li class=\"{$active}\"><a data-toggle=\"tab\" href=\"#ptab{$key}{$uid}\">{$value->name}</a></li>";
            }
            ?>
        </ul>
    </div>
    <div class="panel-body" style="padding: 15px; max-height: 80vh; overflow-y: auto;">
        <div class="tab-content">
            <?php
            $count = 0;
            foreach ($permissions as $key => $value) {
                $active = "";
                if (empty($count)) {
                    $active = " in active";
                }
                $count++;
                ?>
                <div id="ptab<?php echo $key, $uid; ?>" class="tab-pane fade<?php echo $active; ?>">
                    <div class="alert alert-info"><strong><?php echo $value->name; ?>: </strong><?php echo $value->description; ?></div>
                    <?php
                    foreach ($userGroups as $key2 => $group) {
                        $checked = "";
                        foreach ($value->groups as $authorizedGroup) {
                            if ($key2 == $authorizedGroup["users_groups_id"]) {
                                $checked = "checked";
                                break;
                            }
                        }
                        ?>
                        <div class="material-small material-switch pull-left">
                            <input name="pluginPermission<?php echo $key2; ?>" 
                                   id="pluginPermission<?php echo $key2, $uid; ?>_<?php echo $value->type; ?>" 
                                   type="checkbox" value="0" <?php echo $checked; ?>
                                   onchange="updatePluginPermission<?php echo $uid; ?>(<?php echo $key2; ?>, <?php echo $_REQUEST['plugins_id']; ?>, <?php echo $value->type; ?>)"
                                   />
                            <label for="pluginPermission<?php echo $key2, $uid; ?>_<?php echo $value->type; ?>" class="label-success"> </label>
                        </div> <?php echo $group; ?><br>
                        <?php
                    }
                    ?>
                </div>    
                <?php
            }
            ?>
        </div>
    </div>
</div>
<script>
    function updatePluginPermission<?php echo $uid; ?>(users_groups_id, plugins_id, type) {
        console.log(users_groups_id, plugins_id, type);
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Permissions/setPermission.json.php',
            data: {"users_groups_id": users_groups_id, "plugins_id": plugins_id, "type": type, "isEnabled": $('#pluginPermission'+users_groups_id+'<?php echo  $uid; ?>_'+type).is(":checked")},
            type: 'post',
            success: function (response) {
                console.log(response);
                modal.hidePleaseWait();
            }
        });
    }
</script>