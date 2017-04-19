<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("User"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
            <div class="alert alert-danger">
                <h1><?php echo __("You need to install"); ?> youtube-dl</h1>
                <?php echo __("We use youtube-dl to download videos from youtube.com or other video platforms"); ?><br>
                <?php echo __("To installations instructions try this link: "); ?><a href="https://github.com/rg3/youtube-dl/blob/master/README.md#installation">Youtube-dl</a><br><br>
                <?php echo __("To install it right away for all UNIX users (Linux, OS X, etc.), type: "); ?>
                <pre><code>sudo curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl
sudo chmod a+rx /usr/local/bin/youtube-dl</code></pre>
                <br>
                <?php echo __("If you do not have curl, you can alternatively use a recent wget: "); ?>
                <pre><code>sudo wget https://yt-dl.org/downloads/latest/youtube-dl -O /usr/local/bin/youtube-dl
sudo chmod a+rx /usr/local/bin/youtube-dl</code></pre>

            </div>
            <div class="row">
                <div class="col-xs-6 col-sm-4 col-lg-3"></div>
                <div class="col-xs-6 col-sm-4 col-lg-6">
                    <form class="form-compact well form-horizontal"  id="updateUserForm" onsubmit="">
                        <fieldset>
                            <legend><?php echo __("Download Video"); ?></legend>

                            <div class="form-group">
                                <label class="col-md-4 control-label"><?php echo __("Video URL"); ?></label>  
                                <div class="col-md-8 inputGroupContainer">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-film"></i></span>
                                        <input  id="inputVideoURL" placeholder="<?php echo __("Video URL"); ?>" class="form-control"  type="text" value="" required >
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label"><?php echo __("Audio only"); ?></label>  
                                <div class="col-md-8 inputGroupContainer">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-headphones"></i></span>
                                        <input  id="inputAudioOnly" class="form-control"  type="checkbox" value="1" >
                                    </div>
                                </div>
                            </div>

                            <!-- Button -->
                            <div class="form-group">
                                <label class="col-md-4 control-label"></label>
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-primary" ><?php echo __("Download"); ?> <span class="glyphicon glyphicon-download"></span></button>
                                </div>
                            </div>
                        </fieldset>
                    </form>

                </div>
                <div class="col-xs-6 col-sm-4 col-lg-3"></div>
            </div>
            <script>
                $(document).ready(function () {
                    $('#updateUserForm').submit(function (evt) {
                        evt.preventDefault();
                        //modal.showPleaseWait();
                        $.ajax({
                            url: 'downloadNow',
                            data: {"videoURL": $('#inputVideoURL').val(), "audioOnly": $('#inputAudioOnly').is(":checked")},
                            type: 'post',
                            success: function (response) {
                                if (response.status > 0) {
                                    swal({
                                        title: "<?php echo __("Congratulations!"); ?>",
                                        text: "<?php echo __("Your user has been created!"); ?>",
                                        type: "success"
                                    },
                                            function () {
                                                window.location.href = '<?php echo $global['webSiteRootURL']; ?>user';
                                            });
                                } else {
                                    if (response.error) {
                                        swal("<?php echo __("Sorry!"); ?>", response.error, "error");
                                    } else {
                                        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been created!"); ?>", "error");
                                    }
                                }
                                modal.hidePleaseWait();
                            }
                        });
                    });
                });
            </script>
        </div><!--/.container-->

        <?php
        include 'include/footer.php';
        ?>

    </body>
</html>
