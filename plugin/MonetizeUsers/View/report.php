<?php

$YPTWallet = AVideoPlugin::isEnabledByName('YPTWallet');

$users_id = User::getId();
if (User::isAdmin() && !empty($_REQUEST['users_id'])) {
  $users_id = $_REQUEST['users_id'];
}
// Default dates
$start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
$end_date = date('Y-m-d H:i:s', strtotime('+1 day'));

// If dates are provided via form input and are not empty
if (!empty($_REQUEST['start_date'])) {
  $start_date = $_REQUEST['start_date'] . ' 00:00:00'; // Beginning of the day
}
if (!empty($_REQUEST['end_date'])) {
  $end_date = $_REQUEST['end_date'] . ' 23:59:59'; // End of the day
}

$data = MonetizeUsers::getRewards($users_id, $start_date, $end_date, MonetizeUsers::$GetRewardModeGrouped);

?>
<script src="<?php echo getURL('node_modules/chart.js/dist/chart.umd.js'); ?>" type="text/javascript"></script>
<div class="container">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <form>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="start_date" class="control-label"><?php echo __('Start Date'); ?>:</label>
              <input type="date" class="form-control" name="start_date" id="start_date" value="<?php echo date('Y-m-d', strtotime($start_date)); ?>">
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="end_date" class=" control-label"><?php echo __('End Date'); ?>:</label>
              <input type="date" class="form-control" name="end_date" id="end_date" value="<?php echo date('Y-m-d', strtotime($end_date)); ?>">
            </div>
          </div>
          <div class="col-sm-12">
            <input type="submit" class="btn btn-default btn-block">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php
include $global['systemRootPath'] . 'plugin/MonetizeUsers/View/reportPerVideo.php';
include $global['systemRootPath'] . 'plugin/MonetizeUsers/View/reportTimeline.php';
?>