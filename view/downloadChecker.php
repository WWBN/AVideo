<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
$metaDescription = "Download checker";

function getChecked($text, $isChecked, $help = array()) {
    $helpTXT = "";
    if (!empty($help)) {
        $helpTXT = '<ul class="list-group">';
        foreach ($help as $value) {
            $helpTXT .= '<li class="list-group-item" style="font-size: 0.7em;">' . $value . '</li>';
        }
        $helpTXT .= '</ul>';
    }
    if ($isChecked) {
        return '<li class="list-group-item list-group-item-success"><i class="far fa-check-square"></i> ' . $text . $helpTXT . '</li>';
    } else {
        return '<li class="list-group-item list-group-item-danger"><i class="far fa-square"></i> ' . $text . $helpTXT . '</li>';
    }
}
?>
<div class="panel panel-default">
    <div class="panel-heading">
        Site Download Configuration
    </div>
    <div class="panel-body">
        <ul class="list-group">
            <?php
            $globallyDownload = CustomizeUser::canDownloadVideos();
            $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
            if (User::isAdmin()) {
                $help = array();
                $help[] = "Site Configurations menu / Advanced Configuration / Allow download video";
            }
            if (!$globallyDownload) {
                if (empty($config->getAllow_download())) {
                    echo getChecked(__("Your Site Configurations is set to NOT Allow Download"), false, $help);
                } else {
                    echo getChecked(__("Your Site Configurations is set to Allow Download"), true, $help);
                }
                if (User::isAdmin()) {
                    $help = array();
                    $help[] = "Plugins menu / CustomizeUser / nonAdminCannotDownload";
                }
                if ($obj->nonAdminCannotDownload) {
                    echo getChecked(__("Non admin users can download videos"), true, $help);
                } else {
                    echo getChecked(__("Non admin users can NOT download videos"), false, $help);
                }
            }else{
                echo getChecked(__("This site configuration allow download"), true, $help);
                
                if ($obj->nonAdminCannotDownload) {
                    $help[] = "Plugins menu / CustomizeUser / nonAdminCannotDownload";
                    echo getChecked(__("But only admin can download"), true, $help);
                } 
            }
            ?>
        </ul>
    </div>
</div>
<?php
if (!empty($_REQUEST['videos_id'])) {
    $video = new Video('', '', $_REQUEST['videos_id']);
    $users_id = $video->getUsers_id();
    $user = new User($users_id);
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            User Download Configuration (<?php echo User::getNameIdentificationById($users_id); ?>)
        </div>
        <div class="panel-body">
            <ul class="list-group">
                <?php
                if (User::isAdmin()) {
                    $help = array();
                    $help[] = "Plugins menu / CustomizeUser / userCanAllowFilesDownload";
                }else{
                    $help = array();
                }
                if (!empty($obj->userCanAllowFilesDownload)) {
                    $help[] = "My Videos menu / Allow Download My Videos";
                    if (!empty($user->getExternalOption('userCanAllowFilesDownload'))) {
                        if (!empty($obj->userCanAllowFilesDownloadSelectPerVideo)) {
                            echo getChecked(__("This user do allow download selected videos"), true, $help);
                        } else {
                            echo getChecked(__("This user do allow download all his files"), true, $help);
                        }
                    } else {
                        echo getChecked(__("This user do NOT allow download his files"), false, $help);
                    }
                } else {
                    echo getChecked(__("The download is controlled by the system, there is nothing to check on the user"), true, $help);
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            Video Download Configuration (<?php echo $video->getTitle(); ?>)
        </div>
        <div class="panel-body">
            <ul class="list-group">
                <?php
                $canDownloadVideoFromVideo = CustomizeUser::canDownloadVideosFromVideo($video->getId());
                if ($canDownloadVideoFromVideo) {
                    echo getChecked(__("This video can be downloaded"), true);
                } else {
                    $category = new Category($video->getCategories_id());
                    $help = array();
                    $help[] = "Categories menu / Edit a category / Meta Data / Allow Download";
                    if (is_object($category) && !$category->getAllow_download()) {
                        echo getChecked(__("This category do not allow download"), false, $help);
                    } else {
                        echo getChecked(__("This category allow download"), true, $help);
                    }
                    if (!empty($obj->userCanAllowFilesDownloadSelectPerVideo)) {
                        $help = array();
                        $help[] = "My Videos menu / Edit a video / Allow Download This media";
                        if (empty($video->getCan_download())) {
                            echo getChecked(__("User must allow each video individually, but this video is not marked for download"), false, $help);
                        } else {
                            echo getChecked(__("This video checked for download"), true, $help);
                        }
                    } else {
                        echo getChecked(__("The download permission is site wide, so there is nothing to check on the video"), true);
                    }
                }
                ?>
            </ul>
        </div>
    </div>

    <?php
}
?>