<?php
header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    $configFile = '../../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}
$objM = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->meet_schedule_id = 0;
$obj->html = "";

if (empty($objM)) {
    $obj->msg = "Plugin disabled";
    die(json_encode($obj));
}

$obj->meet_schedule_id = intval($_REQUEST['meet_schedule_id']);
if (empty($obj->meet_schedule_id)) {
    $obj->msg = "meet_schedule_id cannot be empty";
    die(json_encode($obj));
}

$ms = new Meet_schedule($obj->meet_schedule_id);

if (!$ms->canManageSchedule()) {
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$obj->error = false;

ob_end_clean();
ob_start();

$domain = $_REQUEST['domain'];


if ($_REQUEST['meet_scheduled'] !== "past") {
    $invitation = Meet::getInvitation($obj->meet_schedule_id);

    ?>
    <div class="row">
        <div class="form-group col-sm-9">
            <label for="RoomLink"><?php echo __("Meet Link"); ?>:</label>
            <?php
            // getInputCopyToClipboard("RoomLink", $ms->getMeetLink());
            ?>
            <div class="input-group">
                <input id="RoomLink" type="text"  placeholder="" class="form-control" readonly="readonly" value="<?php echo str_replace("https://avideo.com",$domain,$ms->getMeetLink()) ?>" >
                    <span class="input-group-addon" style="cursor: pointer;" id="copyToClipboard_RoomLink"  data-toggle="tooltip" data-placement="left" title="<?php echo __('Copy to Clipboard'); ?>"><i class="fa fa-clipboard"></i></span>
            </div>
            <script>
                var timeOutCopyToClipboard_RoomLink;
                $(document).ready(function () {
                    $('#copyToClipboard_RoomLink').click(function () {
                        clearTimeout(timeOutCopyToClipboard_RoomLink);
                        $('#copyToClipboard_RoomLink').find('i').removeClass("fa-clipboard");
                        $('#copyToClipboard_RoomLink').find('i').addClass("text-success");
                        $('#copyToClipboard_RoomLink').addClass('bg-success');
                        $('#copyToClipboard_RoomLink').find('i').addClass("fa-clipboard-check");
                        timeOutCopyToClipboard_RoomLink = setTimeout(function () {
                            $('#copyToClipboard_RoomLink').find('i').removeClass("fa-clipboard-check");
                            $('#copyToClipboard_RoomLink').find('i').removeClass("text-success");
                            $('#copyToClipboard_RoomLink').removeClass('bg-success');
                            $('#copyToClipboard_RoomLink').find('i').addClass("fa-clipboard");
                        }, 3000);
                        copyToClipboard($('#RoomLink').val());
                    })
                });
            </script>
        </div>
        <div class="form-group col-sm-3">
            <label for="RoomInvitation"><?php echo __("Invitation"); ?>:</label>
            <textarea id="RoomInvitation" name="RoomInvitation" class="form-control input-sm hidden" placeholder="<?php echo __("Meet Invitation"); ?>" readonly><?php echo $invitation; ?></textarea>
            <?php
            // getButtontCopyToClipboard("RoomInvitation", 'class="btn btn-default btn-block "', __("Copy"));
            
            $ri_id = "getButtontCopyToClipboard" . uniqid();
            ?>
            <button id="<?php echo $ri_id; ?>" class="btn btn-default btn-block " data-toggle="tooltip" data-placement="left" title="<?php echo __("Copy"); ?>"><i class="fa fa-clipboard"></i> <?php echo __("Copy"); ?></button>
            <script>
                var timeOutCopyToClipboard_<?php echo $ri_id; ?>;
                $(document).ready(function () {
                    $('#<?php echo $ri_id; ?>').click(function () {
                        clearTimeout(timeOutCopyToClipboard_<?php echo $ri_id; ?>);
                        $('#<?php echo $ri_id; ?>').find('i').removeClass("fa-clipboard");
                        $('#<?php echo $ri_id; ?>').find('i').addClass("text-success");
                        $('#<?php echo $ri_id; ?>').addClass('bg-success');
                        $('#<?php echo $ri_id; ?>').find('i').addClass("fa-clipboard-check");
                        timeOutCopyToClipboard_<?php echo $ri_id; ?> = setTimeout(function () {
                            $('#<?php echo $ri_id; ?>').find('i').removeClass("fa-clipboard-check");
                            $('#<?php echo $ri_id; ?>').find('i').removeClass("text-success");
                            $('#<?php echo $ri_id; ?>').removeClass('bg-success');
                            $('#<?php echo $ri_id; ?>').find('i').addClass("fa-clipboard");
                        }, 3000);
                        copyToClipboard($('textarea#RoomInvitation').val());
                    })
                });
            </script>
        </div>
    </div>
    <?php
}
?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __("Participants"); ?></div>
            <div class="panel-body">
                <ul class="list-group">
                    <?php
                    $count = 0;
                    $list = Meet_join_log::getAllFromSchedule($obj->meet_schedule_id);
                    foreach ($list as $value) {
                        $count++;
                        echo '<li class="list-group-item">#' . $count . " - " . User::getNameIdentificationById($value['users_id']) . ' <span class="badge">' . $value['created'] . '</span><br><small class="text-muted">' . $value['user_agent'] . '</small></li>';
                    }
                    if (empty($count)) {
                        echo '<li class="list-group-item">There are no participants on this Meet</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
if(!$ms->getPublic()){
?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __("User Groups"); ?></div>
            <div class="panel-body">
                <ul class="list-group">
                    <?php
                    $count = 0;
                    $list = Meet_schedule_has_users_groups::getAllFromSchedule($obj->meet_schedule_id);
                    foreach ($list as $value) {
                        $count++;
                        echo '<li class="list-group-item">#' . $count . " - " . ($value['group_name']) . '</li>';
                    }
                    if (empty($count)) {
                        echo '<li class="list-group-item">There are no user groups selected for this Meet</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
}
$obj->html = ob_get_clean();
die(json_encode($obj));
?>