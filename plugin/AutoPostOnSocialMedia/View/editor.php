<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("AutoPostOnSocialMedia");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: AutoPostOnSocialMedia</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-sm-2">
                            <input type="checkbox" onclick="$('.scheduleTwitterPost input:checkbox').prop('checked', this.checked);" id="AutoPostOnSocialMediaCheckbox">
                            <label for="AutoPostOnSocialMediaCheckbox">
                                <?php echo __('AutoPostOnSocialMedia') ?> 
                            </label>
                        </div>
                        <div class="col-sm-8">
                            <button class="btn btn-success btn-block" onclick="saveScheduleTwitter();">
                                <i class="fas fa-save"></i>
                                <?php echo __('Save') ?> 
                            </button>
                        </div>
                        <div class="col-sm-2">
                            <div class="pull-right">
                                <?php echo AVideoPlugin::getSwitchButton("AutoPostOnSocialMedia"); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body scheduleTwitterPost">
                    <?php
                    
                    $rows = Scheduler_commands::getAllFromType(AutoPostOnSocialMedia::$scheduleType);
                    $savedValues = array();
                    foreach ($rows as $value) {
                        if(!isset($savedValues[$value['repeat_day_of_week']])){
                            $savedValues[$value['repeat_day_of_week']] = array();
                        }
                        $savedValues[$value['repeat_day_of_week']][] = $value['repeat_hour'];
                    }
                    
                    //var_dump($savedValues);
                    $weekdays = array(
                        __('Sunday'),
                        __('Monday'),
                        __('Tuesday'),
                        __('Wednsday'),
                        __('Thursday'),
                        __('Friday'),
                        __('Saturday')
                    );
                    $columns = 3;
                    foreach ($weekdays as $weekday => $value) {
                        ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <input type="checkbox" onclick="$('.scheduleTwitterPost input.form-check-input-<?php echo $value; ?>:checkbox').prop('checked', this.checked);" id="AutoPostOnSocialMediaCheckbox-<?php echo $value; ?>">
                                    <label for="AutoPostOnSocialMediaCheckbox-<?php echo $value; ?>">
                                        <?php echo $value; ?>
                                    </label>
                                </div>
                                <div class="panel-body">
                                    <?php
                                    $i = 0;
                                    for ($c = 1; $c <= $columns; $c++) {
                                        $val = 12 / $columns;
                                        $class = 'col-xs-' . $val;
                                        $time = 24 / $columns;
                                        ?>
                                        <div class="<?php echo $class; ?>">
                                            <?php
                                            for (; $i < $time * $c; $i++) {
                                                
                                                $checked = '';
                                                if(!empty($savedValues[$weekday]) && in_array($i, $savedValues[$weekday])){
                                                    $checked = 'checked="checked"';
                                                }
                                                
                                                ?>
                                                <div class="form-check">
                                                    <input class="form-check-input form-check-input-<?php echo $value; ?>" type="checkbox" <?php echo $checked; ?> 
                                                           value="<?php echo $weekday; ?>_<?php echo $i; ?>" id="flexCheckDefault<?php echo $value . $i; ?>">
                                                    <label class="form-check-label" for="flexCheckDefault<?php echo $value . $i; ?>">
                                                        <?php printf("%02d", $i); ?>H
                                                    </label>
                                                </div>    
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
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(function () {

            });
            function saveScheduleTwitter() {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'plugin/AutoPostOnSocialMedia/saveSchedule.json.php',
                    method: 'POST',
                    data: {
                        'checkedItems': getCheckedItems()
                    },
                    success: function (response) {
                        avideoResponse(response);
                        modal.hidePleaseWait();
                    }
                });
            }
            function getCheckedItems() {
                var selected = new Array();
                $("input.form-check-input:checkbox:checked").each(function () {
                    selected.push($(this).val());
                });
                console.log('selected', selected);
                return selected;
            }
        </script>
    </body>
</html>
