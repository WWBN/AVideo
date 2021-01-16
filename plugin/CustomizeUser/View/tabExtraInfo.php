<?php
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_extra_info.php';
$rows = Users_extra_info::getAllActive(User::getId());

?>
<div id="<?php echo $tabId; ?>" class="tab-pane fade in" style="padding: 10px 0;">
    <?php
    if(User::isAdmin()){
        echo "<a href='{$global['webSiteRootURL']}plugin/CustomizeUser/View/editor.php' class='btn btn-default btn-block'>".__('Add more fields')."</a>";
    }
    ?>
    <div class="row" id="saveExtraInfoDiv">
        <?php
        foreach ($rows as $value) {
            $class = "col-sm-4";
            if ($value['field_type'] == 'textarea') {
                $class = "col-sm-12";
            }
            ?>
            <div class="<?php echo $class; ?>">
                <?php
                echo Users_extra_info::typeToHTML($value);
                ?>
            </div>
            <?php
        }
        ?>
    </div>

    <!-- Button -->
    <div class="form-group">
        <hr>
        <div class="col-md-12">
            <center>
                <button type="button" class="btn btn-primary btn-block btn-lg" id="saveExtraInfoBTN">
                    <span class="fa fa-save"></span> <?php echo __("Save Extra Info"); ?>
                </button>
            </center>
        </div>
    </div>

</div>

<script type="text/javascript">

    function saveUsersExtraInfoInput() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/CustomizeUser/View/Users_extra_info/save.json.php',
            data: $('.usersExtraInfoInput').serialize(),
            type: 'post',
            success: function (response) {
                modal.hidePleaseWait();
                if(response.error){
                    avideoAlertError(response.msg);
                }else{
                    avideoToast("<?php echo __('Extra info saved'); ?>");
                }
            }
        });
    }
    $(document).ready(function () {
        $('#saveExtraInfoBTN').click(function () {
            saveUsersExtraInfoInput();
        });
    });
</script>

