<?php
$notifications = array();
//$notifications = CustomizeUser::getNotifications();
?>
<!-- CustomizeUser Footer -->
<script>
    $(document).ready(function () {
<?php
foreach ($notifications as $value) {
    if (!empty($value['js'])) {
        echo $value['js'];
    }
}
?>
    });


<?php
if (Permissions::canAdminUsers() || User::isSwapBackActive()) {
    ?>
        function swapUser(users_id) {
            var url = webSiteRootURL + 'plugin/CustomizeUser/swapUser.json.php';
            url = addQueryStringParameter(url, 'users_id', users_id);
            modal.showPleaseWait();
            $.ajax({
                url: url,
                success: function (response) {
                    modal.showPleaseWait();
                    if (response.error) {
                        modal.hidePleaseWait();
                        avideoAlertError(response.msg);
                    } else {
                        var url =  webSiteRootURL;
                        if(response.canAdminUser){
                            url += 'users';
                        }
                        url = addQueryStringParameter(url, 'toast', response.msg);
                        window.top.document.location = url;
                    }
                }
            });
        }
    <?php
}
?>
</script>