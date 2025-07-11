<?php

function getVideoTagsFromVideo($video, $type)
{
    global $advancedCustom, $advancedCustomUser;
    $tags = [];
    if (empty($type) || $type === VideoTags::$TagTypePinned) {
        if ($video->getOrder()) {
            $objTag = new stdClass();
            $objTag->label = __("Pinned");
            $objTag->type = "default";
            $objTag->text = '<i class="fas fa-thumbtack"></i>';
            $tags[] = $objTag;
            $objTag = new stdClass();
        }
    }
    if (empty($type) || $type === VideoTags::$TagTypePaid) {
        $objTag = new stdClass();
        $objTag->label = __("Paid Content");
        if (!empty($advancedCustom->paidOnlyShowLabels)) {
            if (!empty($video->getOnly_for_paid())) {
                $objTag->type = "warning";
                $objTag->text = '<i class="fas fa-lock"></i>';
                $objTag->tooltip = $advancedCustom->paidOnlyLabel;
            } else {
                /*
                   $objTag->type = "success";
                   $objTag->text = '<i class="fas fa-lock-open"></i>';
                   $objTag->tooltip = $advancedCustom->paidOnlyFreeLabel;
                  */
            }
        } else {
            $ppv = AVideoPlugin::getObjectDataIfEnabled("PayPerView");
            if ($video->getStatus() === Video::STATUS_FANS_ONLY) {
                $objTag->type = "warning";
                $objTag->text = '<i class="fas fa-star" ></i>';
                $objTag->tooltip = __("Fans Only");
            } elseif ($advancedCustomUser->userCanProtectVideosWithPassword && !empty($video->getVideo_password())) {
                $objTag->type = "danger";
                $objTag->text = '<i class="fas fa-lock" ></i>';
                $objTag->tooltip = __("Password Protected");
            } elseif (!empty($video->getOnly_for_paid())) {
                $objTag->type = "warning";
                $objTag->text = '<i class="fas fa-lock"></i>';
                $objTag->tooltip = $advancedCustom->paidOnlyLabel;
            } elseif ($ppv && PayPerView::isVideoPayPerView($video->getId())) {
                if (!empty($ppv->showPPVLabel)) {
                    $objTag->type = "warning";
                    $objTag->text = "PPV";
                    $objTag->tooltip = __("Pay Per View");
                } else {
                    $objTag->type = "warning";
                    $objTag->text = '<i class="fas fa-lock"></i>';
                    $objTag->tooltip = __("Private");
                }
            } elseif (!Video::isPublic($video->getId())) {
                $objTag->type = "warning";
                $objTag->text = '<i class="fas fa-lock"></i>';
                $objTag->tooltip = __("Private");
            } else {
                /*
                   $objTag->type = "success";
                   $objTag->text = '<i class="fas fa-lock-open"></i>';
                   $objTag->tooltip = $advancedCustom->paidOnlyFreeLabel;
                  */
            }
        }
        $tags[] = $objTag;
        $objTag = new stdClass();
    }
    return $tags;
}

function getVideoTagVideoStatus($video, $type)
{
    $tags = [];
    $timeName3 = TimeLogStart("video::getTags_ status {$video->getId()}, $type");
    if (empty($type) || $type === VideoTags::$TagTypeStatus) {
        $objTag = new stdClass();
        $objTag->label = __("Status");
        /**
         * @var string $status
         */
        $status = $video->getStatus();
        $objTag->text = __(Video::$statusDesc[$status]);
        switch ($status) {
            case Video::STATUS_ACTIVE:
                $objTag->type = "success";
                break;
            case Video::STATUS_ACTIVE_AND_ENCODING:
                $objTag->type = "success";
                break;
            case Video::STATUS_INACTIVE:
                $objTag->type = "warning";
                break;
            case Video::STATUS_ENCODING:
                $objTag->type = "info";
                break;
            case Video::STATUS_DOWNLOADING:
                $objTag->type = "info";
                break;
            case Video::STATUS_UNLISTED:
                $objTag->type = "info";
                break;
            case Video::STATUS_UNLISTED_BUT_SEARCHABLE:
                $objTag->type = "info";
                break;
            case Video::STATUS_RECORDING:
                $objTag->type = "danger isRecording isRecordingIcon";
                break;
            case Video::STATUS_DRAFT:
                $objTag->type = "primary";
                break;
            default:
                $objTag->type = "danger";
                break;
        }
        $objTag->text = $objTag->text;
        $tags[] = $objTag;
        $objTag = new stdClass();
    }
    return $tags;
}

function getVideoTagsGroups($video, $type)
{
    $tags = [];
    if (empty($type) || $type === VideoTags::$TagTypeUserGroups) {
        $groups = UserGroups::getVideosAndCategoriesUserGroups($video->getId());
        $objTag = new stdClass();
        $objTag->label = __("Group");
        if (empty($groups)) {
            $status = $video->getStatus();
            if ($status == 'u') {
                $objTag->type = "info";
                $objTag->text = '<i class="far fa-eye-slash"></i>';
                $objTag->tooltip = __("Unlisted");
                $tags[] = $objTag;
                $objTag = new stdClass();
            } else {
                //$objTag->type = "success";
                //$objTag->text = __("Public");
            }
        } else {
            $groupNames = [];
            foreach ($groups as $value) {
                $groupNames[] = $value['group_name'];
            }
            $totalUG = count($groupNames);
            if (!empty($totalUG)) {
                $objTag = new stdClass();
                $objTag->label = __("Group");
                $objTag->type = "info";
                if ($totalUG > 1) {
                    $objTag->text = '<i class="fas fa-users"></i> ' . ($totalUG);
                } else {
                    $objTag->text = '<i class="fas fa-users"></i>';
                }
                $objTag->tooltip = implode(', ', $groupNames);
                $tags[] = $objTag;
                $objTag = new stdClass();
            }
        }
    }
    return $tags;
}

function getVideosTagsCategory($video, $type)
{
    $tags = [];
    if (empty($type) || $type === VideoTags::$TagTypeCategory) {
        require_once 'category.php';
        $sort = null;
        if (!empty($_POST['sort']['title'])) {
            $sort = $_POST['sort'];
            unset($_POST['sort']);
        }
        $category = Category::getCategory($video->getCategories_id());
        if (!empty($sort)) {
            $_POST['sort'] = $sort;
        }
        $objTag = new stdClass();
        $objTag->label = __("Category");
        if (!empty($category)) {
            $objTag->type = "default";
            $objTag->text = $category['name'];
            $tags[] = $objTag;
            $objTag = new stdClass();
        }
    }
    return $tags;
}

function getVideoTagsSource($video, $type)
{
    $tags = [];
    if (empty($type) || $type === VideoTags::$TagTypeSource) {
        $url = $video->getVideoDownloadedLink();
        if (!empty($url)) {
            $parse = parse_url($url);
            $objTag = new stdClass();
            $objTag->label = __("Source");
            if (!empty($parse['host'])) {
                $objTag->type = "danger";
                $objTag->text = $parse['host'];
                $tags[] = $objTag;
                $objTag = new stdClass();
            } else {
                $objTag->type = "info";
                $objTag->text = __("Local File");
                $tags[] = $objTag;
                $objTag = new stdClass();
            }
        }
    }
    return $tags;
}

function getVideosTagsRating($video)
{
    $tags = [];
    if (!empty($video->getRrating())) {
        $rating = $video->getRrating();
        $objTag = new stdClass();
        $objTag->label = __("Rating");
        $objTag->type = "default";
        $objTag->text = strtoupper($rating);
        $objTag->tooltip = __(Video::$rratingOptionsText[$rating]);
        $objTag->tooltipIcon = "[{$objTag->text}]";
        $tags[] = $objTag;
        //var_dump($tags);exit;
    }
    return $tags;
}

function getVideoTags($videos_id, $type = '')
{
    global $advancedCustom, $advancedCustomUser, $getTags_;
    $tolerance = 0.1;
    $tags = [];

    $cacheSuffix = "getTags_{$type}";
    $videoCache = new VideoCacheHandler('', $videos_id);
    $oneToFiveHours = rand(3600, 18000); // 1 to 5 hours
    $getTags_ = $videoCache->getCache($cacheSuffix, $oneToFiveHours);
    //$index = "getTags_{$video_id}_{$type}";
    //$getTags_ = ObjectYPT::getCache($index, 3600);

    if (!empty($getTags_)) {
        return $getTags_;
    }

    $timeName1 = TimeLogStart("getVideoTags {$videos_id}, $type");
    if (empty($advancedCustomUser)) {
        $advancedCustomUser = AVideoPlugin::getObjectData("CustomizeUser");
    }
    if (empty($advancedCustom)) {
        $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
    }
    $currentPage = getCurrentPage();
    $rowCount = getRowCount();
    unsetCurrentPage();
    $_REQUEST['rowCount'] = 1000;

    $video = new Video("", "", $videos_id);

    $timeName2 = TimeLogStart("getVideoTagsFromVideo {$videos_id}, $type");
    $newTags = getVideoTagsFromVideo($video, $type);
    $tags = array_merge($tags, $newTags);
    TimeLogEnd($timeName2, __LINE__, $tolerance);

    $timeName2 = TimeLogStart("getVideoTagVideoStatus {$videos_id}, $type");
    $newTags = getVideoTagVideoStatus($video, $type);
    $tags = array_merge($tags, $newTags);
    TimeLogEnd($timeName2, __LINE__, $tolerance);

    $timeName2 = TimeLogStart("getVideoTagsGroups {$videos_id}, $type");
    $newTags = getVideoTagsGroups($video, $type);
    $tags = array_merge($tags, $newTags);
    TimeLogEnd($timeName2, __LINE__, $tolerance);

    $timeName2 = TimeLogStart("getVideosTagsCategory {$videos_id}, $type");
    $newTags = getVideosTagsCategory($video, $type);
    $tags = array_merge($tags, $newTags);
    TimeLogEnd($timeName2, __LINE__, $tolerance);

    $timeName2 = TimeLogStart("getVideoTagsSource {$videos_id}, $type");
    $newTags = getVideoTagsSource($video, $type);
    $tags = array_merge($tags, $newTags);
    TimeLogEnd($timeName2, __LINE__, $tolerance);

    $timeName2 = TimeLogStart("getVideosTagsRating {$videos_id}");
    $newTags = getVideosTagsRating($video);
    $tags = array_merge($tags, $newTags);
    TimeLogEnd($timeName2, __LINE__, $tolerance);

    $timeName2 = TimeLogStart("AVideoPlugin::getVideoTags {$videos_id}");
    $newTags = AVideoPlugin::getVideoTags($videos_id);
    if (is_array($newTags)) {
        $tags = array_merge($tags, $newTags);
    }
    TimeLogEnd($timeName2, __LINE__, $tolerance);

    TimeLogEnd($timeName1, __LINE__, $tolerance * 2);
    $_REQUEST['current'] = $currentPage;
    $_REQUEST['rowCount'] = $rowCount;

    $videoCache->setCache($tags);
    //ObjectYPT::setCache($index, $tags);
    return $tags;
}
