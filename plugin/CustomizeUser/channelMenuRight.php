<?php
if (!empty($_SESSION['channelName'])) {
    unset($_GET['channelName']);
    $params = array_merge($_GET, ['leaveChannel' => '1']);
    $_GET['channelName'] = $_SESSION['channelName'];
    $new_query_string = http_build_query($params);
    $request_uri = explode("?", $_SERVER['REQUEST_URI']);
    $leaveLink = $request_uri[0] . '?' . $new_query_string;
    if (!empty($obj->showLeaveChannelButton)) {
        ?>
    <li>
        <a href="<?php echo $leaveLink; ?>"  class="btn btn-default navbar-btn" data-toggle="tooltip" title="<?php echo __("Leave Channel"); ?>" data-placement="bottom" >
            <span class="fa fa-times"></span>  <span class="hidden-md hidden-sm hidden-mdx"><?php echo $_GET['channelName']; ?></span>
        </a>
    </li>
    <?php
    } ?>
    <script>
        $('#mainNavbarLogo').on('click', function (e) {
            e.preventDefault();
            var url = $(this).attr("href");
            document.location = url+'?leaveChannel=1';
        });
    </script>
    <?php
}
?>