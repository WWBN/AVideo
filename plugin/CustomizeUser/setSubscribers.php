<?php
require_once '../../videos/configuration.php';

if (!User::isAdmin()) {
    forbiddenPage('Must be admin');
}
$users_id = intval(@$_REQUEST['users_id']);
if (empty($users_id)) {
    forbiddenPage('Empty users_id');
}
$count = User::getExtraSubscribers($users_id);
$_page = new Page(array('Set subscribers'));

?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php
            echo Video::getCreatorHTML($users_id);
            ?>
        </div>
        <div class="panel-body">
            Add extra <input type="number" step="1" id="ExtraSubscribers" value="<?php echo $count; ?>" /> subscribers on his subscription counter.

        </div>
        <div class="panel-footer">
            <button class="btn btn-success btn-lg btn-block" onclick="setSubscribers();">
                <i class="fas fa-save"></i> <?php echo __('Save'); ?>
            </button>
        </div>
    </div>
    <script type="text/javascript">
        function setSubscribers() {
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/CustomizeUser/setSubscribers.json.php',
                method: 'POST',
                data: {
                    users_id: <?php echo $users_id; ?>,
                    ExtraSubscribers: $('#ExtraSubscribers').val()
                },
                success: function(response) {
                    modal.hidePleaseWait();
                    avideoResponse(response);
                }
            });
        }
    </script>
</div>
<?php
$_page->print();
?>