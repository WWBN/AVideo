<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin customize"));
    exit;
}
require_once $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';

$ec = new ExtraConfig();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Customize</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

        <link href="<?php echo $global['webSiteRootURL']; ?>js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css"/>   
        <style>
        </style>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">


            <form id="customizeForm">
                <div class="form-group row">
                    <label for="about" class="col-2 col-form-label">About</label>
                    <div class="col-10">
                        <textarea id="about" placeholder="Enter the About text" style="width: 100%;"><?php echo $ec->getAbout(); ?></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="footer" class="col-2 col-form-label">Footer</label>
                    <div class="col-10">
                        <textarea id="footer" placeholder="Enter the footer text" style="width: 100%;"><?php echo $ec->getFooter(); ?></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="description" class="col-2 col-form-label">Description</label>
                    <div class="col-10">
                        <input class="form-control" type="text" placeholder="Description" id="description" value="<?php echo $ec->getDescription(); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Save</button>
            </form>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                $('#about, #footer').wysihtml5({toolbar: {
                        "html": true,
                        "color": true
                    }
                });


                $("#customizeForm").submit(function (event) {
                    event.preventDefault();
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/Customize/page/editorSave.php',
                        data: {"about": $('#about').val(), "footer": $('#footer').val(), "description": $('#description').val()},
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                            console.log(response);
                        }
                    });
                });
            });
        </script>
    </body>
</html>
