<?php
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

$obj = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($obj)) {
    die("Plugin disabled");
}

if (!User::canCreateMeet()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
$userCredentials = User::loginFromRequestToGet();
?>

<div class="row">
    <div class="col-sm-5">
        <div class="panel panel-default" id="roomConfiguration">
            <div class="panel-heading">
                <i class="fas fa-plus"></i> <?php echo __("Create Room"); ?>
            </div>
            <div class="panel-body">

                <form id="formMeetManager">
                    <input type="hidden" id="meet_schedule_id" name="id" value="0">
                    <div class="form-group col-sm-6">
                        <label for="RoomTopic"><?php echo __("Meet Topic"); ?>:</label>
                        <input type="text" id="RoomTopic" name="RoomTopic" class="form-control input-sm" placeholder="<?php echo __("Meet Topic"); ?>">
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="RoomPasswordNew"><?php echo __("Meet Password"); ?>:</label>
                        <?php
                        getInputPassword("RoomPasswordNew");
                        ?>
                    </div>
                    <div class="form-group col-sm-6 hidden">
                        <label for="live_streamNew"><?php echo __("Auto Transmit Live"); ?>:</label>
                        <select class="form-control input-sm" name="live_stream" id="live_streamNew">
                            <option value="0"><?php echo __("No"); ?></option>
                            <option value="1"><?php echo __("Yes"); ?></option>
                        </select>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="publicNew"><?php echo __("Public"); ?>:</label>
                        <select class="form-control input-sm" name="public" id="publicNew">
                            <option value="2"><?php echo __("Public"); ?></option>
                            <option value="1"><?php echo __("Logged Users Only"); ?></option>
                            <option value="0"><?php echo __("Specific User Groups"); ?></option>
                        </select>

                        <div class="publicNewOption" style="display: none; overflow: hidden;">
                            <?php
                            $userGroups = UserGroups::getAllUsersGroups();
                            foreach ($userGroups as $value) {
                                ?>
                                <div class="form-check" style="white-space: nowrap;">
                                    <input class="form-check-input userGroups" type="checkbox" value="<?php echo $value['id']; ?>" name="userGroups[]" id="userGroupsCheck<?php echo $value['id']; ?>">
                                    <label class="form-check-label" for="defaultCheck<?php echo $value['id']; ?>">
                                        <?php echo $value['group_name']; ?>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="whenNew"><?php echo __("When"); ?>:</label>
                        <select class="form-control input-sm" name="when" id="whenNew">
                            <option value="1"><?php echo __("Now"); ?></option>
                            <option value="0"><?php echo __("Schedule"); ?></option>
                        </select>
                        <div class="whenNewOption" style="display: none;">
                            <label for="Meet_schedule2starts"><?php echo __("Starts"); ?>:</label>
                            <input type="text" id="Meet_schedule2starts" name="starts" class="form-control input-sm" placeholder="<?php echo __("Starts"); ?>" autocomplete="off">
                        </div>

                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group col-sm-12">
                        <div class="btn-group justified">
                            <span class="btn btn-success" id="newMeet_scheduleLink" onclick="clearMeetForm(true)"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
                            <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>
    <div class="col-sm-7">
        <div class="panel panel-default" id="scheduleList" >
            <div class="panel-heading">
                <i class="far fa-calendar-alt"></i> <?php echo __("Schedule"); ?>
            </div>
            <div class="panel-body tabbable-line" id="manageTabs">

                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#mToday"><?php echo __("Today"); ?></a></li>
                    <li><a data-toggle="tab" href="#mUpcoming"><?php echo __("Upcoming"); ?></a></li>
                    <li><a data-toggle="tab" href="#mPast"><?php echo __("Past"); ?></a></li>
                </ul>

                <div class="tab-content">
                    <div id="mToday" class="tab-pane fade in active" style="padding: 10px;" url="<?php
                            echo $global['webSiteRootURL'] . 'plugin/Meet/meet_scheduled.php?meet_scheduled=today&manageMeetings=1&'.$userCredentials;
                            ?>"><div class="loader"></div></div>
                    <div id="mUpcoming" class="tab-pane fade" style="padding: 10px;" url="<?php
                    echo $global['webSiteRootURL'] . 'plugin/Meet/meet_scheduled.php?meet_scheduled=upcoming&manageMeetings=1&'.$userCredentials;
                            ?>"><div class="loader"></div></div>
                    <div id="mPast" class="tab-pane fade" style="padding: 10px;" url="<?php
                    echo $global['webSiteRootURL'] . 'plugin/Meet/meet_scheduled.php?meet_scheduled=past&manageMeetings=1&'.$userCredentials;
                            ?>"><div class="loader"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>


    function clearMeetForm(triggerChange) {
        $('#meet_schedule_id').val('');
        $('#RoomTopic').val('');
        $('#RoomPasswordNew').val('');
        $('#live_streamNew').val('');
        $('#publicNew').val('2');
        $('input.userGroups:checkbox').removeAttr('checked');
        $('#whenNew').val('1');
        $('#Meet_schedule2starts').val('');
        $('#formMeetManager')[0].reset();
        if (triggerChange) {
            $('#publicNew, #whenNew').trigger("change");
        }
    }


    function startMeetNow() {
        $('#whenNew').val('1');
        $('#Meet_schedule2starts').val('');
        $('#formMeetManager').submit();
    }

    $(document).ready(function () {
        $('#formMeetManager').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/saveMeet.json.php?<?php echo $userCredentials; ?>',
                data: $('#formMeetManager').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                        modal.hidePleaseWait();
                    } else {
                        if ($("#whenNew").val() == "1") {
                            document.location = response.link;
                        } else {
                            avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                            try {Meet_schedule2today1tableVar.ajax.reload();} catch (e) {}                            
                            try {Meet_schedule2upcoming1tableVar.ajax.reload();} catch (e) {}                         
                            try {Meet_schedule2past1tableVar.ajax.reload();} catch (e) {}                            
                            
                            clearMeetForm(true);
                            modal.hidePleaseWait();
                        }
                    }
                }
            });
        });

        $('#Meet_schedule2starts').datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true});
        $('#publicNew').change(function () {
            if ($(this).val() == '0') {
                $(".publicNewOption").slideDown();
            } else {
                $(".publicNewOption").slideUp();
            }
        });
        $('#whenNew').change(function () {
            if ($(this).val() == '1') {
                $(".whenNewOption").slideUp();
            } else {
                $(".whenNewOption").slideDown();
            }
        });

        $('#publicNew, #whenNew').trigger("change");


        $('#manageTabs .nav-tabs a').click(function (e) {
            var now_tab = e.target // activated tab

            // get the div's id
            var divid = $(now_tab).attr('href').substr(1);
            var url = $("#" + divid).attr('url');
            $("#" + divid).attr('url', '');
            if (url) {
                $.ajax({
                    url: url,
                    success: function (response) {
                        $("#" + divid).html(response);
                    }
                });
            }
        });
        $('#manageTabs .nav-tabs a').first().trigger("click");

    });
</script>