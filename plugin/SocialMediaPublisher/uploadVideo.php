<?php
require_once '../../videos/configuration.php';


$videos_id = getVideos_id();

$_page = new Page(array('Upload Video'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css', 'view/css/social.css'));
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
            <h1>
                <?php
                echo Video::getVideosListItem($videos_id);
                ?>
            </h1>
        </div>
        <div class="panel-body">
            <?php
            include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_user_preferences/index_body.php';
            ?>
        </div>
        <div class="panel-footer">
            <button class="btn btn-success btn-lg btn-block">
                <i class="fas fa-save"></i>
                <?php echo __('Save'); ?>
            </button>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {});
</script>
<?php
$_page->print();
?>