<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Configuration"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
            <?php
            if (User::isAdmin()) {
                ?>
                <div class="row">
                    <div class="col-xs-6 col-sm-4 col-lg-3"></div>
                    <div class="col-xs-6 col-sm-4 col-lg-6">
                        <form class="form-compact well form-horizontal"  id="updateConfigForm" onsubmit="">
                            <fieldset>
                                <legend><?php echo __("Update the site configuration"); ?></legend>

                                <div class="form-group">
                                    <label class="col-md-4 control-label"><?php echo __("Video Resolution"); ?></label>  
                                    <div class="col-md-8 inputGroupContainer">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-film"></i></span>
                                            <input aria-describedby="resolutionHelp"   id="inputVideoResolution" placeholder="<?php echo __("Video Resolution"); ?>" class="form-control"  type="text" value="<?php echo $config->getVideo_resolution(); ?>" >                                            
                                        </div>
                                        <small id="resolutionHelp" class="form-text text-muted"><?php echo __("Use one of the recommended resolutions"); ?></small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label"><?php echo __("Web site title"); ?></label>  
                                    <div class="col-md-8 inputGroupContainer">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-globe"></i></span>
                                            <input  id="inputWebSiteTitle" placeholder="<?php echo __("Web site title"); ?>" class="form-control"  type="text"  value="<?php echo $config->getWebSiteTitle(); ?>" >
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label"><?php echo __("Language"); ?></label>  
                                    <div class="col-md-8 inputGroupContainer">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-flag"></i></span>
                                            <input  id="inputLanguage" placeholder="<?php echo __("Language"); ?>" class="form-control"  type="text"  value="<?php echo $config->getLanguage(); ?>" >
                                        </div>
                                        <small id="inputLanguage" class="form-text text-muted"><?php echo __("This value must match with the language files on"); ?><code><?php echo $global['systemRootPath']; ?>locale</code></small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>  
                                    <div class="col-md-8 inputGroupContainer">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                            <input  id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" class="form-control"  type="email"  value="<?php echo $config->getContactEmail(); ?>" >
                                        </div>
                                        <small id="inputEmail" class="form-text text-muted"><?php echo __("This e-mail will be used for this web site notifications"); ?></small>
                                    </div>
                                </div>
                                <!-- Button -->
                                <div class="form-group">
                                    <label class="col-md-4 control-label"></label>
                                    <div class="col-md-8">
                                        <button type="submit" class="btn btn-primary" ><?php echo __("Save"); ?> <span class="glyphicon glyphicon-save"></span></button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>

                    </div>
                    <div class="col-xs-6 col-sm-4 col-lg-3">
                        <ul class="list-group">
                            <li class="list-group-item active">
                                <?php echo __("Recommended resolutions"); ?>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                352:240
                                <span class="badge badge-default badge-pill">(240p)(SD)</span>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                480:360 
                                <span class="badge badge-default badge-pill">(360p)</span>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                858:480 
                                <span class="badge badge-default badge-pill">(480p)</span>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                1280:720 
                                <span class="badge badge-default badge-pill">(720p)(Half HD)</span>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                1920:1080 
                                <span class="badge badge-default badge-pill">(1080p)(Full HD)</span>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                3860:2160 
                                <span class="badge badge-default badge-pill">(2160p)(Ultra-HD)(4K)</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <script>
                    $(document).ready(function () {
                        $('#updateConfigForm').submit(function (evt) {
                            evt.preventDefault();
                            modal.showPleaseWait();
                            $.ajax({
                                url: 'updateConfig',
                                data: {"video_resolution": $('#inputVideoResolution').val(), "webSiteTitle": $('#inputWebSiteTitle').val(), "language": $('#inputLanguage').val(), "contactEmail": $('#inputEmail').val()},
                                type: 'post',
                                success: function (response) {
                                    if (response.status === "1") {
                                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your configurations has been updated!"); ?>", "success");
                                    } else {
                                        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your configurations has NOT been updated!"); ?>", "error");
                                    }
                                    modal.hidePleaseWait();
                                }
                            });
                        });
                    });
                </script>
                <?php
            }
            ?>

        </div><!--/.container-->

        <?php
        include 'include/footer.php';
        ?>

    </body>
</html>
