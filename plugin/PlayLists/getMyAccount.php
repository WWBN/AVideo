<?php
$autoadd_playlist = PlayLists::getAutoAddPlaylist($users_id);
//var_dump($autoadd_playlist, $users_id);exit;
?>
<div class="form-group">
    <label class="col-md-4 control-label">
        <?php echo __("Auto add new videos on PlayList"); ?>
    </label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fas fa-list"></i>
            </span>
            <select class="form-control" id="autoadd_playlist">
                <option value="0"><?php echo __('Do not add'); ?></option>
                <?php
                $pls = PlayList::getAllFromUserLight(User::getId(), false);
                foreach ($pls as $value) {
                    $selected = '';
                    
                    if($autoadd_playlist === $value['id']){
                        $selected = 'selected';
                    }
                    
                    echo "<option value=\"{$value['id']}\" {$selected}>{$value['name']}</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#autoadd_playlist').change(function (e) {
            saveAutoadd_playlist();
        });
    });

    function saveAutoadd_playlist() {
        var autoadd_playlist = $('#autoadd_playlist').val();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/PlayLists/getMyAccount.save.json.php',
            data: {autoadd_playlist: autoadd_playlist},
            type: 'post',
            success: function (response) {
                avideoResponse(response);
                modal.hidePleaseWait();
            }
        });
    }
</script>