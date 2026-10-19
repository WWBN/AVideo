<?php
require_once '../../videos/configuration.php';


if (!User::canUpload()) {
    forbiddenPage('You cannot upload');
}
$videos_id = getVideos_id();
if (empty($videos_id)) {
    forbiddenPage('Videos ID is empty');
}
// publishing needs it (uploadVideo.json.php); also keeps the title/description of other users' videos private
if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}
AVideoPlugin::loadPlugin('SocialMediaPublisher');

$_page = new Page(array('Publish on social media'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css', 'view/css/social.css', 'plugin/Gallery/style.css', 'plugin/SocialMediaPublisher/style.css'));
// plugin/SocialMediaPublisher/script.js is included by linkSocialMediasButtons.php
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js'));

$value = Video::getVideoLight($videos_id);
$toText = function ($text) {
    return htmlspecialchars(trim(YouTubePublisher::htmlToText($text)), ENT_QUOTES, 'UTF-8');
};
$tags = array();
if (AVideoPlugin::isEnabledByName('VideoTags') && class_exists('VideoTags')) {
    $tags = VideoTags::getArrayFromVideosId($videos_id);
}
?>
<div class="container-fluid socialPublisher">
    <div class="row">
        <div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fas fa-pen"></i> <?php echo __('Video details'); ?>
                </div>
                <div class="panel-body">
                    <div class="socialPublisherVideoThumb">
                        <?php
                        echo Video::getVideoImagewithHoverAnimationFromVideosId($value);
                        ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="socialUploadtitle"><?php echo __('Title'); ?></label>
                        <input type="text" id="socialUploadtitle" class="form-control" maxlength="<?php echo YouTubePublisher::TITLE_MAX_CHARS; ?>" placeholder="<?php echo __('Title'); ?>" value="<?php echo $toText($value['title']); ?>">
                        <p class="help-block"><?php echo __('YouTube accepts up to 100 characters.'); ?></p>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="socialUploaddescription"><?php echo __('Description'); ?></label>
                        <textarea id="socialUploaddescription" class="form-control" rows="6" maxlength="<?php echo YouTubePublisher::DESCRIPTION_MAX_BYTES; ?>" placeholder="<?php echo __('Description'); ?>"><?php echo $toText($value['description']); ?></textarea>
                        <p class="help-block"><?php echo __('YouTube accepts up to 5000 characters. HTML is removed.'); ?></p>
                    </div>
                    <div class="form-group">
                        <label for="socialUploadvisibility"><?php echo __('Privacy'); ?></label>
                        <select class="form-control" id="socialUploadvisibility">
                            <option value="private"><?php echo __('Private - only you can watch it'); ?></option>
                            <option value="unlisted"><?php echo __('Unlisted - anyone with the link can watch it'); ?></option>
                            <option value="public"><?php echo __('Public - everyone can find and watch it'); ?></option>
                        </select>
                    </div>
                    <div class="form-group socialPublisherYouTubeOnly">
                        <label class="control-label" for="socialUploadtags"><i class="fab fa-youtube"></i> <?php echo __('Tags'); ?></label>
                        <input type="text" id="socialUploadtags" class="form-control" placeholder="<?php echo __('tag one, tag two'); ?>" value="<?php echo $toText(implode(', ', $tags)); ?>">
                        <p class="help-block"><?php echo __('Separate the tags with commas. Used only on YouTube.'); ?></p>
                    </div>
                    <div class="form-group socialPublisherYouTubeOnly hidden" id="socialUploadcategoryGroup">
                        <label class="control-label" for="socialUploadcategory"><i class="fab fa-youtube"></i> <?php echo __('YouTube category'); ?></label>
                        <select class="form-control" id="socialUploadcategory">
                            <option value=""><?php echo __('Let YouTube choose'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <?php
            include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_user_preferences/index_body.php';
            include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_video_publisher_logs/index_body_videos_id.php';
            ?>
        </div>
    </div>
</div>
<?php
$_page->print();
?>
