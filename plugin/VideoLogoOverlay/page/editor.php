<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin add logo"));
    exit;
}
$o = AVideoPlugin::getObjectData("VideoLogoOverlay");
$_page = new Page(array("Customize"));
?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            Create an Video Overlay Button
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12 ">
                    <?php
                    $croppie = getCroppie(__('Upload a logo'), 'saveVideoLogo', 250, 70, 0, 20);
                    echo $croppie['html'];
                    ?>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12 ">
                    <div class="form-group">
                        <div class="col-md-2 text-right">
                            <label for="position"><?php echo __("Position"); ?>:</label>
                        </div>
                        <div class="col-md-10 ">
                            <select class="form-control" id="position">
                                <?php
                                foreach ($o->position->type as $value) {
                                    echo "<option " . ($value == $o->position->value ? "selected='selected'" : "") . ">{$value}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12 ">
                    <div class="form-group">
                        <div class="col-md-2 text-right">
                            <label for="url"><?php echo __("URL"); ?>:</label>
                        </div>
                        <div class="col-md-10 ">
                            <input type="url" id="url" class="form-control" value="<?php echo $o->url; ?>">
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12 ">
                    <div class="form-group">
                        <div class="col-md-2 text-right">
                            <label for="opacity"><?php echo __("Opacity"); ?>:</label>
                        </div>
                        <div class="col-md-10 ">
                            <input type="number" id="opacity" class="form-control" value="<?php echo $o->opacity; ?>">
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12 text-center">
                    <button class="btn btn-success btn-block" id="save" onclick="<?php echo $croppie['getCroppieFunction']; ?>"><i class="fa fa-save"></i> <?php echo _('Save'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function saveVideoLogo(image) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/VideoLogoOverlay/page/editorSave.php',
            data: {
                "image": image,
                "position": $('#position').val(),
                "opacity": $('#opacity').val(),
                "url": $('#url').val()
            },
            type: 'post',
            success: function(response) {
                avideoResponse(response);
                modal.hidePleaseWait();
            }
        });
    }
    $(document).ready(function() {
        <?php
        echo $croppie['restartCroppie'] . "('" . getURL('videos/logoOverlay.png') . "');";
        ?>
    });
</script>
<?php
$_page->print();
?>