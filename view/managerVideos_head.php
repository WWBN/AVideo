<?php
require_once $global['systemRootPath'] . 'objects/category.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/userGroups.php';
$userGroups = UserGroups::getAllUsersGroups();

unset($_SESSION['type']);
if (!empty($_GET['video_id'])) {
    if (Video::canEdit($_GET['video_id'])) {
        $row = Video::getVideo($_GET['video_id'], "");
    }
}
?>
<link href="<?php echo getCDN(); ?>view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo getCDN(); ?>view/js/bootstrap-fileinput/css/fileinput.min.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo getCDN(); ?>view/js/bootstrap-fileinput/js/fileinput.min.js" type="text/javascript"></script>
<link href="<?php echo getCDN(); ?>view/mini-upload-form/assets/css/style.css" rel="stylesheet" />
<?php
if(AVideoPlugin::isEnabledByName("VideoTags")){
?>
<link href="<?php echo getCDN(); ?>plugin/VideoTags/bootstrap-tagsinput/bootstrap-tagsinput.css" rel="stylesheet" type="text/css"/>
<style>
    .tt-open{
        background-color: #FFF;
        padding: 5px;
        min-width: 100px;
        border-radius: 4px;
    }
    .tt-cursor, .tt-selectable:hover{
        font-weight: bold;
    }
    .tt-selectable:hover{
        cursor: pointer;
    }
</style>
<?php
}
?>
<style>
    #inputNextVideo-poster {
        height: 90px;
        width: 160px;
    }
    .ui-autocomplete{
        z-index: 9999999;
    }
    .krajee-default.file-preview-frame {
        min-width: 300px;
    }
</style>
