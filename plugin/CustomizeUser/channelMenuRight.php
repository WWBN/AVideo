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
    <script>
        $('#mainNavBar a.navbar-brand').on('click', function (e) {
            e.preventDefault();
            var url = $(this).attr("href");
            swal({
                title: "<?php echo __("Leaving the Channel?"); ?>",
                text: "<?php echo __("Are you sure you want to leave this Channel?"); ?>",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: 'Yes, I am sure!',
                cancelButtonText: "No, stay on this channel!",
                closeOnConfirm: false,
                closeOnCancel: false
            },
                    function (isConfirm) {
                        if (isConfirm) {
                            document.location = url+'?leaveChannel=1';
                        } else {
                            document.location = url;
                        }
                    });
        });

    </script>
    <?php
}
?>