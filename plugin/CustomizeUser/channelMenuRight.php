<?php
if (!empty($_GET['channelName'])) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['channelName'] = $_GET['channelName'];
}
if (!empty($_GET['leaveChannel'])) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    unset($_SESSION['channelName']);
}
if (!empty($_SESSION['channelName'])) {
    unset($_GET['channelName']);
    $params = array_merge($_GET, array('leaveChannel' => '1'));
    $_GET['channelName'] = $_SESSION['channelName'];
    $new_query_string = http_build_query($params);
    $request_uri = explode("?", $_SERVER['REQUEST_URI']);
    $leaveLink = $request_uri[0] . '?' . $new_query_string;
    ?>
    <li>
        <a href="<?php echo $leaveLink; ?>"  class="btn btn-default navbar-btn" data-toggle="tooltip" title="<?php echo __("Leave Channel"); ?>" data-placement="bottom" >
            <span class="fa fa-times"></span>  <span class="hidden-md hidden-sm"><?php echo $_GET['channelName']; ?></span>
        </a>
    </li>
    <?php
}
?>