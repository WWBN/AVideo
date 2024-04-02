<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (empty($_REQUEST['users_id'])) {
    forbiddenPage('Invalid users_id');
}

if (!User::isLogged()) {
    gotToLoginAndComeBackHere('');
}

$row = Subscribe::getSubscribeFromID(User::getId(), $_REQUEST['users_id'], '');

if (empty($row)) {
    forbiddenPage('Invalid subscription');
}

$subscribe = new Subscribe($row['id']);
$subscribe->setNotify(0);
$subscribe->save();
$_page = new Page(array('Unsubscribe'));

?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php
            echo Video::getCreatorHTML($_REQUEST['users_id']);
            ?>
        </div>
        <div class="panel-body">
            <h1><?php echo __("You've unsubscribed"); ?></h1>
            <?php echo __("You'll no longer receive emails from us"); ?>
        </div>
    </div>
</div>
<?php
$_page->print();
?>