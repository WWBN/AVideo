<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin add logo"));
    exit;
}
$o = YouPHPTubePlugin::getObjectData("VideoLogoOverlay");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Customize</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/Croppie/croppie.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/Croppie/croppie.min.js" type="text/javascript"></script>

    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container"> 
            <h1>Create an Video Overlay Button</h1>
            <div class="row">
                <div class="col-md-12 ">
                    <div id="croppieLogo"></div>
                    <a id="logo-btn" class="btn btn-light btn-sm btn-block"><?php echo __("Upload a logo"); ?></a>
                    <input type="file" id="logo" value="Choose a Logo" accept="image/*" style="display: none;" />
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
                                foreach ($o->position_options as $value) {
                                    echo "<option ".($value==$o->position?"selected='selected'":"").">{$value}</option>";
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
                            <input type="url" id="url"  class="form-control" value="<?php echo $o->url; ?>">
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
                            <input type="number" id="opacity"  class="form-control" value="<?php echo $o->opacity; ?>">
                        </div>                    
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12 text-center">  
                    <button class="btn btn-success" id="save"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            var logoCrop;
            function readFile(input, c) {
                console.log("read file");
                if ($(input)[0].files && $(input)[0].files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        c.croppie('bind', {
                            url: e.target.result
                        }).then(function () {
                            console.log('jQuery bind complete');
                        });
                    }
                    reader.readAsDataURL($(input)[0].files[0]);
                } else {
                    swal("Sorry - you're browser doesn't support the FileReader API");
                }
            }

            var logoImgBase64;

            $(document).ready(function () {
                // start croppie logo
                $('#logo').on('change', function () {
                    readFile(this, logoCrop);
                });
                $('#logo-btn').on('click', function (ev) {
                    $('#logo').trigger("click");
                });
                $('#logo-result-btn').on('click', function (ev) {
                    logoCrop.croppie('result', {
                        type: 'canvas',
                        size: 'viewport'
                    }).then(function (resp) {

                    });
                });

                logoCrop = $('#croppieLogo').croppie({
                    url: '<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png',
                    enableExif: true,
                    enforceBoundary: false,
                    mouseWheelZoom: false,
                    viewport: {
                        width: 250,
                        height: 70
                    },
                    boundary: {
                        width: 300,
                        height: 120
                    }
                });
                
                setTimeout(function () {
                    logoCrop.croppie('setZoom', 1);
                }, 1000);
                // END croppie logo

                $('#save').click(function (evt) {
                    modal.showPleaseWait();
                    logoCrop.croppie('result', {
                        type: 'canvas',
                        size: 'viewport'
                    }).then(function (resp) {
                        logoImgBase64 = resp;

                        $.ajax({
                            url: '<?php echo $global['webSiteRootURL']; ?>plugin/VideoLogoOverlay/page/editorSave.php',
                            data: {
                                "logoImgBase64": logoImgBase64,
                                "position": $('#position').val(),
                                "opacity": $('#opacity').val(),
                                "url": $('#url').val()
                            },
                            type: 'post',
                            success: function (response) {
                                if (response.saved) {
                                    swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your configurations has been updated!"); ?>", "success");
                                } else {
                                    swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your configurations has NOT been updated!"); ?>", "error");
                                }
                                modal.hidePleaseWait();
                            }
                        });
                    });
                });

            });
        </script>
    </body>
</html>
