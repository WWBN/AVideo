<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload()) {
    header("location: {$global['webSiteRootURL']}user");
    exit;
}

function isYoutubeDl() {
    return trim(shell_exec('which youtube-dl'));
}
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
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
            <?php
            if (!isYoutubeDl()) {
                ?>
                <div class="alert alert-danger">
                    <h1><?php echo __("Sorry you not able to download videos right now!"); ?></h1>
                    <h2><?php echo __("You need to install"); ?> youtube-dl</h2>
                    <?php echo __("We use youtube-dl to download videos from youtube.com or other video platforms"); ?><br>
                    <?php echo __("To installations instructions try this link: "); ?><a href="https://github.com/rg3/youtube-dl/blob/master/README.md#installation">Youtube-dl</a><br><br>
                    <?php echo __("youtube-dl uses Python and some servers does not came with python as dafault, to install Python type:"); ?>
                    <pre><code>sudo apt-get install python</code></pre>
                    <br>
                    <?php echo __("To install it right away for all UNIX users (Linux, OS X, etc.), type: "); ?>
                    <pre><code>sudo curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl && sudo chmod a+rx /usr/local/bin/youtube-dl</code></pre>
                    <br>
                    <?php echo __("If you do not have curl, you can alternatively use a recent wget: "); ?>
                    <pre><code>sudo wget https://yt-dl.org/downloads/latest/youtube-dl -O /usr/local/bin/youtube-dl && sudo chmod a+rx /usr/local/bin/youtube-dl</code></pre>
                </div>
                <?php
            } else {
                ?>
                <div class="row">
                    <div class="col-xs-1 col-sm-1 col-lg-2"></div>
                    <div class="col-xs-10 col-sm-10 col-lg-8">
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
                                <!-- TODO audio only -->
                                <div class="form-group" style="display: none">
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
                        <div class="progress progress-striped active">
                            <div id="downloadProgress" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0px"></div>
                        </div>
                    </div>
                    <div class="col-xs-1 col-sm-1 col-lg-2">
                        <?php
                        if (!empty($global['videoStorageLimitMinutes'])) {
                            $minutesTotal = getMinutesTotalVideosLength();
                            ?>
                        <div class="alert alert-warning">
                            <?php printf(__("Make sure that the video you are going to download has a duration of less than %d minute(s)!"), ($global['videoStorageLimitMinutes']-$minutesTotal)); ?>
                        </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <script>
                    $(document).ready(function () {
                        $('#updateUserForm').submit(function (evt) {
                            evt.preventDefault();
                            modal.showPleaseWait();
                            $.ajax({
                                url: 'downloadNow',
                                data: {"videoURL": $('#inputVideoURL').val(), "audioOnly": $('#inputAudioOnly').is(":checked")},
                                type: 'post',
                                success: function (response) {
                                    if (response.error) {
                                        swal({
                                            title: response.title,
                                            text: response.text,
                                            type: response.type,
                                            html: true
                                        });
                                    } else {
                                        swal({
                                            title: "<?php echo __("Congratulations!"); ?>",
                                            text: "<?php echo __("Your video is downloading now"); ?>",
                                            type: "success",
                                            showCancelButton: true,
                                            confirmButtonColor: "#DD6B55",
                                            confirmButtonText: "<?php echo __("Go to manager videos page!"); ?>",
                                            closeOnConfirm: false
                                        },
                                                function () {
                                                    window.location.href = '<?php echo $global['webSiteRootURL']; ?>mvideos';
                                                });
                                        if (response.filename) {
                                            checkProgress(response.filename);
                                        }
                                    }
                                    modal.hidePleaseWait();
                                }
                            });
                        });
                    });
                    function checkProgress(filename) {
                        console.log(filename);
                        $.ajax({
                            url: 'getDownloadProgress',
                            data: {"filename": filename},
                            type: 'post',
                            success: function (response) {
                                $("#downloadProgress").css({'width': response.progress + '%'});
                                if (response.progress < 100) {
                                    setTimeout(function () {
                                        checkProgress(filename);
                                    }, 1000);
                                } else if (response.progress == 100) {
                                    $("#downloadProgress").css({'width': '100%'});
                                    swal({
                                        title: "<?php echo __("Congratulations!"); ?>",
                                        text: "<?php echo __("Your video download is complete, it is encoding now"); ?>",
                                        type: "success"
                                    },
                                            function () {
                                                window.location.href = '<?php echo $global['webSiteRootURL']; ?>mvideos';
                                            });
                                }
                            }
                        });
                    }
                </script>
                <?php
            }
            ?>
        </div><!--/.container-->

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

    </body>
</html>
