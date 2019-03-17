<div class="row">
    <?php
    $savedTheme = $config->getTheme();
    foreach (glob("{$global['systemRootPath']}view/css/custom/*.css") as $filename) {
        //echo "$filename size " . filesize($filename) . "\n";
        $file = basename($filename);         // $file is set to "index.php"
        $fileEx = basename($filename, ".css"); // $file is set to "index"
        ?>
        <div class="col-xs-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo ucfirst($fileEx); ?>
                    <div class="material-switch pull-right">
                        <input class="themeSwitch" data-toggle="toggle" type="checkbox" value="<?php echo ($fileEx); ?>" id="themeSwitch<?php echo ($fileEx); ?>" <?php echo ($fileEx == $savedTheme) ? "checked" : ""; ?>>
                        <label for="themeSwitch<?php echo ($fileEx); ?>" class="label-primary"></label>
                    </div>
                </div>
                <div class="panel-body">
                    <img src="<?php echo $global['webSiteRootURL'], "view/css/custom/", $fileEx, ".png"; ?>" class="img-responsive">
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<script>
    function checkSwitch() {
        var defaultSwitch = $('#defaultSwitch').is(":checked");
        var netflixSwitch = $('#netflixSwitch').is(":checked");
        var gallerySwitch = $('#gallerySwitch').is(":checked");
        if (!defaultSwitch && !netflixSwitch && !gallerySwitch) {
            $('#netflixSwitch').prop('checked', false);
            $('#gallerySwitch').prop('checked', false);
            $('#defaultSwitch').prop('checked', true);
        }
    }
    $(document).ready(function () {
        $('.themeSwitch').change(function (e) {
            modal.showPleaseWait();
            $('.themeSwitch').not(this).prop('checked', false);
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>admin/themeUpdate.json.php',
                data: {"theme": $(this).val()},
                type: 'post',
                success: function (response) {
                    modal.hidePleaseWait();
                }
            });

        });
    });
</script>