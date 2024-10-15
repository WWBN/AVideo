<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
$global['doNotLoadPlayer'] = 1;
setIsConfirmationPage();
$images = Video::getImageFromFilename($video['filename']);
$img = $images->poster;
if (!empty($images->posterPortrait) && !ImagesPlaceHolders::isDefaultImage($images->posterPortrait)) {
    $img = $images->posterPortrait;
}
$imgw = 1280;
$imgh = 720;
$metaDescription = $title = getSEOTitle($video['title']);
$ogURL = Video::getLinkToVideo($video['id'], $video['clean_title'], false, false);

$_page = new Page(array('Confirm Rating'));


//$video['rrating'] = 'nc-17';

?>
<style>
    #bg {
        position: fixed;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo $images->poster; ?>');
        background-size: cover;
        opacity: 0.3;
        filter: alpha(opacity=30);
        z-index: -1;
        /* For IE8 and earlier */
    }
</style>
<div id="bg"></div>
<div class="container">
    <div class="panel panel-danger">
        <div class="panel-heading">
            <h1>
                <?php echo $video['title']; ?>
            </h1>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-2">
                    <?php
                    echo  Video::getRratingIMG($video['rrating']);
                    ?>
                </div>
                <div class="col-sm-4">
                    <img src="<?php echo $img; ?>" class="img img-responsive" />
                </div>
                <div class="col-sm-6 text-center">
                    <?php
                    echo  Video::getRratingText($video['rrating']);
                    ?>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <?php
            $canAccept = true;
            if ($video['rrating'] == 'ma') { // if is mature
                if (!User::isLogged()) {
                    $canAccept = false;
            ?>
                    <div class="alert alert-warning">
                        <strong><?php echo __('Sign In Required'); ?></strong>
                        <p><?php echo __('This content is for viewers 18+'); ?></p>
                        <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary btn-block">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <?php echo __('Sign In'); ?>
                        </a>
                    </div>
                    <?php
                } else {
                    $birth_date = User::getBirthIfIsSet();
                    if (empty($birth_date)) {
                        $canAccept = false;
                    ?>
                        <div class="alert alert-danger">
                            <strong><?php echo __('Age Confirmation Needed'); ?></strong>
                            <p><?php echo __('Please update your birth date in your profile to access this content'); ?></p>
                            <?php
                            if (User::isLogged()) {
                            ?>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa-solid fa-cake-candles"></i></span>
                                            <input id="inputBirth" placeholder="<?php echo __("Birth Date"); ?>" class="form-control" type="date" value="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <button class="btn btn-primary btn-block" onclick="saveBirthDate()">
                                            <i class="fa-solid fa-clipboard-check"></i>
                                            <?php echo __('Update Birth Date'); ?>
                                        </button>
                                    </div>
                                </div>
                                <script>
                                    function saveBirthDate() {
                                        var url = webSiteRootURL + 'objects/userUpdateBirth.json.php';
                                        modal.showPleaseWait();
                                        $.ajax({
                                            url: url,
                                            method: 'POST',
                                            data: {
                                                'birth_date': $('#inputBirth').val()
                                            },
                                            success: function(response) {
                                                avideoResponse(response);
                                                location.reload();
                                            }
                                        });
                                    }
                                </script>
                            <?php
                            } else {
                            ?>
                                <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary btn-block">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                    <?php echo __('Update Profile'); ?>
                                </a>
                            <?php
                            }
                            ?>
                        </div>
                    <?php
                    } else if (!User::isOver18()) {
                        $canAccept = false;
                    ?>
                        <div class="alert alert-info">
                            <strong><?php echo __('Content Not Available'); ?></strong>
                            <p><?php echo __('This content is restricted to viewers 18 and over'); ?></p>
                            <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary btn-block">
                                <i class="fa-solid fa-house"></i>
                                <?php echo __('Home'); ?>
                            </a>
                        </div>
                <?php
                    }
                }
            }
            if ($canAccept) {
                ?>
                <div class="btn-group btn-group-justified">
                    <a href="<?php echo getHomePageURL(); ?>" class="btn btn-danger">
                        <i class="fas fa-times-circle"></i> <?php echo __("Cancel"); ?>
                    </a>
                    <a href="<?php echo $_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "?") === false ? "?" : "&"; ?>rrating=1" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> <?php echo __("Confirm"); ?>
                    </a>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>
<?php
$_page->print();
?>