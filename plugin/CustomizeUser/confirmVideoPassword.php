<?php
require_once '../videos/configuration.php';
setIsConfirmationPage();
$images = Video::getImageFromFilename($video['filename']);
$img = $images->poster;
if (!empty($images->posterPortrait) && !ImagesPlaceHolders::isDefaultImage($images->posterPortrait)) {
    $img = $images->posterPortrait;
}
$imgw = 1280;
$imgh = 720;
$ogURL = Video::getLinkToVideo($video['id'], $video['clean_title'], false, false);
$_page = new Page(array('Confirm Password'));
$_page->setIncludeNavbar(false);
?>
<style>
    body {
        padding-top: 0;
    }

    footer {
        display: none;
    }

    #bg {
        position: fixed;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo $images->poster; ?>');
        background-size: cover;
        opacity: 0.3;
        filter: alpha(opacity=30);
        /* For IE8 and earlier */
    }
</style>
<div id="bg"></div>

<!-- Modal -->
<div id="myModal" class="modal fade in" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title">
                    <center>
                        <i class="fas fa-lock"></i> <?php echo $video['title']; ?> <?php echo __("is Private"); ?>
                    </center>
                </h1>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6">
                        <img src="<?php echo $img; ?>" class="img img-responsive" />
                    </div>
                    <div class="col-sm-6">
                        <center>
                            <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                <?php
                                if (!empty($_REQUEST['video_password'])) {
                                ?>
                                    <div class="alert alert-danger"><?php echo __("Your password does not match!"); ?></div>
                                <?php
                                }
                                ?>
                                <div class="form-group">
                                    <label for="video_password"><?php echo __("This Video Requires a Password"); ?></label>
                                    <?php
                                    echo getInputPassword('video_password', 'class="form-control"', __("Password"));
                                    ?>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-check-circle"></i> <?php echo __("Confirm"); ?></button>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="<?php echo getHomePageURL(); ?>" class="btn btn-danger  btn-block"><i class="fas fa-times-circle"></i> <?php echo __("Cancel"); ?></a>
                                    </div>
                                </div>
                            </form>
                        </center>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">
    $(window).on('load', function() {
        $('#myModal').modal('show');
    });
</script>
<?php
$_page->print();
?>