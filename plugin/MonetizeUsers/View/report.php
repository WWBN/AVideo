<?php

$YPTWallet = AVideoPlugin::isEnabledByName('YPTWallet');

$users_id = User::getId();
if(User::isAdmin() && !empty($_REQUEST['users_id'])){
  $users_id = $_REQUEST['users_id'];
}

$data = MonetizeUsers::getRewards($users_id, date('Y-m-d H:i:s', strtotime('-7 days')), date('Y-m-d H:i:s', strtotime('+1 day')), MonetizeUsers::$GetRewardModeGrouped);

?>
<script src="<?php echo getURL('node_modules/chart.js/dist/chart.umd.js'); ?>" type="text/javascript"></script>
<?php
include $global['systemRootPath'] . 'plugin/MonetizeUsers/View/reportPerVideo.php';
include $global['systemRootPath'] . 'plugin/MonetizeUsers/View/reportTimeline.php';