<link href="<?php echo $global['webSiteRootURL']; ?>js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css"/>

<div class="panel panel-default">
    <div class="panel-heading">Customize Footer, About and Meta Description <div class="pull-right"><?php echo getPluginSwitch('Customize'); ?></div></div>
    <div class="panel-body">
        <?php
        if (!YouPHPTubePlugin::exists('Customize')) {
            ?>
            <div class="alert alert-info">
                Truly customize your YouPHPTube and create a more professional video sharing site experience for your visitors by removing or replacing the footer, about page and Meta Description with your own.
                <a class="btn btn-info btn-sm btn-xs" href="https://www.youphptube.com/plugins/">Buy the Customize plugin now</a>
            </div>  
            <?php
            return false;
        } else {

            require_once $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';

            $ec = new ExtraConfig();
            ?>
            <div class="row">
                <div class="col-md-12">
                    <form id="customizeForm">
                        <div class="form-group">
                            <label for="about" class="col-2 col-form-label">Text for About Page</label>
                            <div class="col-10">
                                <textarea id="about" placeholder="Enter the About text" style="width: 100%;"><?php echo $ec->getAbout(); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="footer" class="col-2 col-form-label">Text for Footer</label>
                            <div class="col-10">
                                <textarea id="footer" placeholder="Enter the footer text" style="width: 100%;"><?php echo $ec->getFooter(); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="col-2 col-form-label">MetaTag Description</label>
                            <div class="col-10">
                                <input class="form-control" type="text" placeholder="Description" id="description" value="<?php echo $ec->getDescription(); ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">Save</button>
                    </form>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.all.js" type="text/javascript"></script>
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