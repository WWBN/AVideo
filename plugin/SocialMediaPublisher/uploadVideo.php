<?php
require_once '../../videos/configuration.php';


if (!User::canUpload()) {
    forbiddenPage('You cannot upload');
}
$videos_id = getVideos_id();

$_page = new Page(array('Upload Video'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css', 'view/css/social.css', 'plugin/Gallery/style.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js', 'plugin/SocialMediaPublisher/script.js'));

$video = new Video('', '', $videos_id);
$title = $video->getTitle();
$description = $video->getDescription();
$categories_id = $video->getCategories_id();
$videos_id = getVideos_id();
if (empty($videos_id)) {
    forbiddenPage('Videos ID is empty');
}
?>
<style>
    .social-network .btn {
        margin: 20px 0;
    }
</style>
<div class="container-fluid">
    <div class="panel panel-default ">
        <div class="panel-heading clearfix ">
            <div class="row">
                <div class="col-sm-6">
                    <?php

                    $value = Video::getVideoLight($videos_id);
                    $thumbsImage = Video::getVideoImagewithHoverAnimationFromVideosId($value);
                    echo $thumbsImage;
                    //echo Video::getVideosListItem($videos_id);
                    ?>
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="control-label" for="socialUploadtitle"><?php echo __('Title'); ?></label>
                            <input type="text" id="socialUploadtitle" class="form-control" placeholder="<?php echo __('Title'); ?>" value="<?php echo $value['title']; ?>">
                        </div>
                        <div class="col-sm-6">
                            <label for="socialUploadvisibility"><?php echo __('Visibility'); ?></label>
                            <select class="form-control last" id="socialUploadvisibility">
                                <option value="private"><?php echo __('Private'); ?></option>
                                <option value="unlisted"><?php echo __('Unlisted'); ?></option>
                                <option value="public"><?php echo __('Public'); ?></option>
                            </select>
                        </div>
                        <div class="col-sm-12">
                            <label class="control-label" for="socialUploaddescription"><?php echo __('Description'); ?></label>
                            <textarea id="socialUploaddescription" class="form-control" placeholder="<?php echo __('Description'); ?>"><?php echo strip_tags($value['description']); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <?php
                    include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_video_publisher_logs/index_body_videos_id.php';
                    ?>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_user_preferences/index_body.php';
            ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {});

</script>
<?php
$_page->print();
?>