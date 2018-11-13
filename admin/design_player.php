<div class="row">
    <?php
    $obj = YouPHPTubePlugin::getObjectData('PlayerSkins');
    $dir = $global['systemRootPath'] . 'plugin/PlayerSkins/skins/';
    foreach (glob($dir . '*.css') as $filename) {
        //echo "$filename size " . filesize($filename) . "\n";
        $file = basename($filename);         // $file is set to "index.php"
        $fileEx = basename($filename, ".css"); // $file is set to "index"
        ?>
        <div class="col-xs-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo ucfirst($fileEx); ?>
                    <div class="material-switch pull-right">
                        <input class="playerSwitch" data-toggle="toggle" type="checkbox" value="<?php echo ($fileEx); ?>" id="themeSwitch<?php echo ($fileEx); ?>" <?php echo ($fileEx == $obj->skin) ? "checked" : ""; ?>>
                        <label for="themeSwitch<?php echo ($fileEx); ?>" class="label-primary"></label>
                    </div>
                </div>
                <div class="panel-body" style="padding: 0;">
                    <iframe fameBorder="0" 
                            src="<?php echo $global['webSiteRootURL']; ?>plugin/PlayerSkins/playerSample.php?playerSkin=<?php echo ($fileEx); ?>" 
                            style="width: 100%; height: 300px; border: 0;"></iframe>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<script>
    $(document).ready(function () {
        $('.playerSwitch').change(function (e) {
            modal.showPleaseWait();
            $('.playerSwitch').not(this).prop('checked', false);
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>admin/playerUpdate.json.php',
                data: {"skin": $(this).val()},
                type: 'post',
                success: function (response) {
                    modal.hidePleaseWait();
                }
            });

        });
    });
</script>