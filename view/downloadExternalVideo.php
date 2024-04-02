<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload()) {
    header("location: {$global['webSiteRootURL']}user");
    exit;
}

function isYoutubeDl()
{
    return trim(shell_exec('which youtube-dl'));
}
$_page = new Page(array('Download External Video'));
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
            <?php echo __("youtube-dl uses Python and some servers do not come with python by default. To install Python type:"); ?>
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
                <form class="form-compact well form-horizontal" id="updateUserForm" onsubmit="">
                    <fieldset>
                        <legend><?php echo __("Download Video"); ?></legend>

                        <div class="form-group">
                            <label class="col-md-4 control-label"><?php echo __("Video URL"); ?></label>
                            <div class="col-md-8 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa-solid fa-film"></i>
                                    </span>
                                    <input id="inputVideoURL" placeholder="<?php echo __("Video URL"); ?>" class="form-control" type="text" value="" required>
                                </div>
                            </div>
                        </div>
                        <!-- TODO audio only -->
                        <div class="form-group" style="display: none">
                            <label class="col-md-4 control-label"><?php echo __("Audio only"); ?></label>
                            <div class="col-md-8 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-headphones"></i></span>
                                    <input id="inputAudioOnly" class="form-control" type="checkbox" value="1">
                                </div>
                            </div>
                        </div>

                        <!-- Button -->
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-primary"><?php echo __("Download"); ?> <i class="fa-solid fa-cloud-arrow-down"></i></button>
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
                    $minutesTotal = getMinutesTotalVideosLength(); ?>
                    <div class="alert alert-warning">
                        <?php printf(__("Make sure that the video you are going to download has a duration of less than %d minute(s)!"), ($global['videoStorageLimitMinutes'] - $minutesTotal)); ?>
                    </div>
                <?php
                } ?>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $('#updateUserForm').submit(function(evt) {
                    evt.preventDefault();
                    modal.showPleaseWait();
                    $.ajax({
                        url: 'downloadNow',
                        data: {
                            "videoURL": $('#inputVideoURL').val(),
                            "audioOnly": $('#inputAudioOnly').is(":checked")
                        },
                        type: 'post',
                        success: function(response) {
                            if (response.error) {
                                swal({
                                    title: response.title,
                                    text: response.text,
                                    type: response.type,
                                    html: true
                                });
                            } else {

                                swal({
                                        title: __("Are you sure ?"),
                                        text: __("You will not be able to recover this action!"),
                                        icon: "warning",
                                        buttons: true,
                                        dangerMode: true,
                                    })
                                    .then(function(willDelete) {
                                        if (willDelete) {
                                            window.location.href = webSiteRootURL + 'mvideos';
                                        }
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
                    data: {
                        "filename": filename
                    },
                    type: 'post',
                    success: function(response) {
                        $("#downloadProgress").css({
                            'width': response.progress + '%'
                        });
                        if (response.progress < 100) {
                            setTimeout(function() {
                                checkProgress(filename);
                            }, 1000);
                        } else if (response.progress == 100) {
                            $("#downloadProgress").css({
                                'width': '100%'
                            });
                            swal({
                                    title: __("Congratulations!"),
                                    text: __("Your video download is complete, it is encoding now"),
                                    type: "success"
                                },
                                function() {
                                    window.location.href = webSiteRootURL + 'mvideos';
                                });
                        }
                    }
                });
            }
        </script>
    <?php
    }
    ?>
</div>
<?php
$_page->print();
?>