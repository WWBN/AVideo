<?php
$obj = AVideoPlugin::getDataObjectIfEnabled('Scheduler');
if (empty($obj->disableReleaseDate)) {
    ?>
    <br>
    <div class="clearfix"></div>       
    <div class="row">
        <div class="col-sm-6">
            <label for="releaseDate" ><?php echo __("Release Date"); ?></label>
            <select class="form-control last" id="releaseDate" >
                <option value='now'><?php echo __('Now'); ?></option>
                <option value='in-1-hour'><?php echo __('1 hour'); ?></option>
                <option value='datetime'><?php echo __('Specific date and time'); ?></option>
            </select>
        </div>
        <div class="col-sm-6" style="display: none;" id="releaseDateTimeDiv">
            <label for="releaseDateTime"><?php echo __("Date and time"); ?></label>
            <input type="text" id="releaseDateTime" class="form-control" placeholder="<?php echo __("YYYY-MM-DD HH:MM:SS"); ?>">
            <small>YYYY-MM-DD hh:mm:ss</small>
        </div>
        <script>
            $(document).ready(function () {
                setupMySQLInput('#releaseDateTime');
                $('#releaseDate').change(function(){
                    if($(this).val() == 'datetime'){
                        $('#releaseDateTimeDiv').slideDown();
                    }else{
                        $('#releaseDateTimeDiv').slideUp();
                    }
                });
            });
        </script>
    </div>
    <br>
    <div class="clearfix"></div>
    <?php
}
?>