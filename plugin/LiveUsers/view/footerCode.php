<?php
if(empty($_GET['u'])){
    return false;
}
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
$t = LiveTransmition::getFromDbByUserName($_GET['u']);
$uuid = $t['key'];

?>
<script>
    function beat() {
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/LiveUsers/view/beat.json.php?key=<?php echo $uuid; ?>',
            success: function (response) {
                $('.liveUsersOnline_'+response.key).text(response.users.online);
                $('.liveUsersViews_'+response.key).text(response.users.views);
                setTimeout(function () {
                    beat();
                }, 1000);
            }
        });
    }

    $(document).ready(function () {
        beat();
    });
</script>