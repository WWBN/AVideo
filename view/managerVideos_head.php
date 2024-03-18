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
<link href="<?php echo getURL('view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo getURL('view/js/bootstrap-fileinput/css/fileinput.min.css'); ?>" rel="stylesheet" type="text/css" />
<script src="<?php echo getURL('view/js/bootstrap-fileinput/js/fileinput.min.js'); ?>" type="text/javascript"></script>
<link href="<?php echo getURL('view/mini-upload-form/assets/css/style.css'); ?>" rel="stylesheet" />
<?php
if (AVideoPlugin::isEnabledByName("VideoTags")) {
?>
    <link href="<?php echo getURL('plugin/VideoTags/bootstrap-tagsinput/bootstrap-tagsinput.css'); ?>" rel="stylesheet" type="text/css" />
    <style>
        .tt-open {
            background-color: #FFF;
            padding: 5px;
            min-width: 100px;
            border-radius: 4px;
        }

        .tt-cursor,
        .tt-selectable:hover {
            font-weight: bold;
        }

        .tt-selectable:hover {
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

    .ui-autocomplete {
        z-index: 9999999;
    }

    .krajee-default.file-preview-frame {
        min-width: 300px;
    }

    body.edit_article .hideIfIsArticle,
    body.edit_embed .hideIfIsEmbedLink, 
    body.edit_linkAudio .hideIfIsEmbedLink, 
    body.edit_linkVideo .hideIfIsEmbedLink, 
    body.edit_video .hideIfIsVideo,
    body.edit_directUpload .hideIfIsDirectUpload,
    body.is_editing .hideIfIsEditing ,
    body.edit_image .hideIfIsImage
    body.edit_gallery .hideIfIsImage,
    .showIfIsArticle, 
    .showIfIsEmbedLink, 
    .showIfIsVideo,
    .showIfIsEditing ,
    .showIfIsImage   {
        display: none !important;
    }


    body.edit_article .showIfIsArticle, 
    body.edit_embed .showIfIsEmbedLink, 
    body.edit_linkAudio .showIfIsEmbedLink, 
    body.edit_linkVideo .showIfIsEmbedLink, 
    body.edit_video .showIfIsVideo,
    body.edit_directUpload .showIfIsDirectUpload,
    body.is_editing .showIfIsEditing ,
    body.edit_gallery .showIfIsImage,
    body.edit_image .showIfIsImage {
        display: block !important;
    }

</style>