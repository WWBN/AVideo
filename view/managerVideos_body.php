<?php
global $statusThatShowTheCompleteMenu;
require_once $global['systemRootPath'] . 'objects/video.php';

$video_id = $_REQUEST['video_id'];
if (!empty($video_id) && Video::canEdit($video_id)) {
    $editVideo = Video::getVideo($video_id, '');
}

if (empty($advancedCustom)) {
    $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
}
?>
<style>
    <?php
    if (!empty($advancedCustom->hideEditAdvancedFromVideosManager) && !User::isAdmin()) {
    ?>.command-edit {
        display: none !important;
    }

    <?php
    }
    ?>.bootgrid-table td {
        -ms-text-overflow: initial;
        -o-text-overflow: initial;
        text-overflow: initial;
    }

    .viewsDetails {
        color: #FFF;
    }

    .viewsDetails:hover {
        color: #AAF;
    }

    .progress-bar {
        -webkit-transition: width 2.5s ease;
        transition: width 2.5s ease;
    }

    .modal-dialog {
        width: 90%;
    }

    @media (max-width:767px) {
        .modal-dialog {
            width: 100vw;
            margin: 0;
        }
    }

    <?php
    if (!empty($_GET['iframe'])) {
    ?>body {
        padding: 0;
    }

    footer {
        display: none;
    }

    <?php
    }
    ?>#actionButtonsVideoManager button {
        font-size: 12px;
    }

    .controls .btn {
        margin: 5px 0;
    }

    #grid .tagsInfo span.label:not(.tagTitle) {
        display: inline-block;
        width: 70%;
        text-align: left;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    #grid .tagsInfo span.label.tagTitle {
        display: inline-block;
        width: 30%;
        overflow: hidden;
        text-align: right;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-top-left-radius: 0.25em;
        border-bottom-left-radius: 0.25em;
    }

    .titleBtn {
        white-space: break-spaces;
        display: flex;
        display: flow-root;
    }

    .groupSwitch .categoryGroupSwitch,
    .groupSwitch .categoryGroupSwitchInline {
        display: none;
    }

    .groupSwitch.categoryUserGroup {
        pointer-events: none;
    }

    .groupSwitch.categoryUserGroup .categoryGroupSwitch {
        display: block;
    }

    .groupSwitch.categoryUserGroup .categoryGroupSwitchInline {
        display: inline;
    }

    .groupSwitch.categoryUserGroup .videoGroupSwitch {
        display: none;
    }

    .typeFormat {
        display: flex;
        margin-bottom: 5px;
    }

    .typeFormat .tagTitle {
        width: 40% !important;
    }

    .typeLabels {
        display: inline-grid;
        width: 100%;
    }

    .typeLabels span {
        width: 100% !important;
    }

    body.youtube .bootgrid-table {
        table-layout: auto;
    }

    body.compact .hideIfCompact {
        display: none;
    }

    body.compact .scrollIfCompact {
        max-height: 90px;
        overflow-y: scroll;
    }

    body.compact #grid img {
        max-height: 50px !important;
    }

    body.compact #grid {
        font-size: 12px !important;
    }

    body.compact #grid .titleBtn {
        font-size: 0.8em !important;
    }

    .kv-file-rotate{
        display: none !important;
    }
</style>
<script>
    var filterStatus = '';
    var filterType = '';
    var filterCategory = '';
    var _editVideo = false;
    <?php
    if (!empty($editVideo)) {
        $json = json_encode($editVideo);
        if (!empty($json)) {
    ?>
            _editVideo = <?php echo $json; ?>;
    <?php
        }
    }
    ?>
</script>
<div class="container-fluid">
    <?php
    if (empty($_GET['iframe'])) {
    ?>
        <div class="panel panel-default ">
            <div class="panel-body">

                <div class="btn-group btn-block">
                    <?php if (Permissions::canAdminVideos()) { ?>
                        <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups" class="btn  btn-sm btn-xs btn-warning" id="userGroupsButton">
                            <span class="fa fa-users"></span> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("User Groups"); ?></span>
                        </a>
                        <a href="<?php echo $global['webSiteRootURL']; ?>users" class="btn btn-sm btn-xs btn-primary" id="usersButton">
                            <span class="fa fa-user"></span> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Users"); ?></span>
                        </a>
                    <?php } ?>
                    <a href="<?php echo $global['webSiteRootURL']; ?>charts" class="btn btn-sm btn-xs btn-info" id="videoChartButton">
                        <i class="fas fa-chart-bar"></i>
                        <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Video Chart"); ?></span>
                    </a>
                    <?php if (Permissions::canAdminVideos()) { ?>
                        <a href="<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/" class="btn btn-sm btn-xs btn-danger" id="advertisingManagerButton">
                            <span class="far fa-money-bill-alt"></span> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Advertising Manager"); ?></span>
                        </a>
                    <?php } ?>

                    <form id="formEncoderVideosM" method="post" action="<?php echo $config->getEncoderURL(); ?>" target="encoder">
                        <input type="hidden" name="webSiteRootURL" value="<?php echo $global['webSiteRootURL']; ?>" />
                        <input type="hidden" name="user" value="<?php echo User::getUserName(); ?>" />
                        <input type="hidden" name="pass" value="<?php echo User::getUserPass(); ?>" />
                    </form>
                    <a href="#" onclick="$('#formEncoderVideosM').submit(); return false;" class="btn btn-sm btn-xs btn-default" id="encodeVideoButton">
                        <span class="fa fa-cog"></span> <span class="hidden-md hidden-sm hidden-xs"><?php echo empty($advancedCustom->encoderButtonLabel) ? __("Encode video and audio") : __($advancedCustom->encoderButtonLabel); ?></span>
                    </a>

                    <?php
                    if (CustomizeAdvanced::showDirectUploadButton()) {
                    ?>
                        <button class="btn btn-sm btn-xs btn-default" onclick="newDirectUploadVideo();" id="uploadMp4Button" data-toggle="tooltip" title="<?php echo __("Upload files without encode"), ' ', implode(', ', CustomizeAdvanced::directUploadFiletypes()); ?>">
                            <span class="fa fa-upload"></span>
                            <span class="hidden-md hidden-sm hidden-xs"><?php echo empty($advancedCustom->uploadMP4ButtonLabel) ? __("Direct upload") : __($advancedCustom->uploadMP4ButtonLabel); ?></span>
                        </button>
                    <?php
                    }
                    ?>
                    <?php
                    if (empty($advancedCustom->doNotShowEmbedButton)) {
                    ?>
                        <button class="btn btn-sm btn-xs btn-default" id="embedVideoLinkButton">
                            <span class="fa fa-link"></span>
                            <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Embed a video link"); ?></span>
                        </button>
                    <?php
                    }
                    ?>
                    <?php
                    if (AVideoPlugin::isEnabledByName("Articles")) {
                    ?>
                        <button class="btn btn-sm btn-xs btn-default" id="addArticleButton" onclick="newArticle()">
                            <i class="far fa-newspaper"></i>
                            <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Add Article"); ?></span>
                        </button>
                    <?php
                    }
                    ?>

                    <button class="btn btn-sm btn-xs btn-default" id="sortVideosButton" onclick="avideoModalIframeFullScreen(webSiteRootURL+'view/managerVideosOrganize.php');">
                        <i class="fas fa-sort-amount-up-alt"></i>
                        <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Sort Videos"); ?></span>
                    </button>
                </div>

            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-body"><?php echo AVideoPlugin::getVideoManagerButton(); ?></div>
        </div>
        <small class="text-muted clearfix <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
            <?php
            $secondsTotal = getSecondsTotalVideosLength();
            $seconds = $secondsTotal % 60;
            $minutes = ($secondsTotal - $seconds) / 60;
            $totalVideos = Video::getTotalVideosFromUser(User::getId());
            $totalVideosSize = humanFileSize(Video::getTotalVideosSizeFromUser(User::getId()));
            printf(__("You are hosting %d videos total, %d minutes and %d seconds and consuming %s of disk"), $totalVideos, $minutes, $seconds, $totalVideosSize);
            ?>
        </small>
        <?php
        if (Permissions::canAdminVideos()) {
            echo diskUsageBars();
        }
        if (!empty($global['videoStorageLimitMinutes'])) {
            $secondsLimit = $global['videoStorageLimitMinutes'] * 60;
            if ($secondsLimit > $secondsTotal) {
                $percent = intval($secondsTotal / $secondsLimit * 100);
            } else {
                $percent = 100;
            }
        ?> and you have <?php echo $global['videoStorageLimitMinutes']; ?> minutes of storage
            <div class="progress">
                <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%">
                    <?php echo $percent; ?>% of your storage limit used
                </div>
            </div>
    <?php
        }
    }
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">

            <div class="btn-group" id="actionButtonsVideoManager">
                <button class="btn btn-default" id="checkBtn">
                    <i class="far fa-square" aria-hidden="true" id="chk"></i>
                </button>
                <?php if ($advancedCustom->videosManegerBulkActionButtons) {
                    $categories = Category::getAllCategories(true);
                    if (!empty($categories)) {
                        if (empty($advancedCustomUser->userCanNotChangeCategory) || Permissions::canAdminVideos()) { ?>
                            <div class="btn-group" id="categoriesBtnGroup">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" id="categoriesBtn">
                                    <i class="far fa-object-group"></i>
                                    <span class="hidden-md hidden-sm hidden-xs"><?php echo __('Categories'); ?></span>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <?php foreach ($categories as $value) {
                                        echo "<li><a href=\"#\"  onclick=\"changeCategory({$value['id']});return false;\"><i class=\"{$value['iconClass']}\"></i> {$value['hierarchyAndName']}</a></li>";
                                    } ?>
                                </ul>
                            </div>
                    <?php }
                    } ?>

                    <div class="btn-group" id="statusBtnGroup">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" id="statusBtn">
                            <i class="far fa-eye"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __('Status'); ?></span>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <?php foreach ($statusThatTheUserCanUpdate as $value) {
                                $statusIndex = $value[0];
                                $statusColor = $value[1];
                                echo "<li><a href=\"#\" onclick=\"changeStatus('" . $statusIndex . "'); return false;\" style=\"color: {$statusColor}\">"
                                    . Video::$statusIcons[$statusIndex] . ' ' . __(Video::$statusDesc[$statusIndex]) . "</a></li>";
                            } ?>
                        </ul>
                    </div>

                    <?php if (empty($advancedCustomUser->userCanNotChangeUserGroup) || Permissions::canAdminVideos()) {
                        $userGroups = UserGroups::getAllUsersGroups(); ?>

                        <div class="btn-group" id="addUserGroupBtnGroup">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" id="addUserGroupBtn">
                                <i class="fas fa-users"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __('Add User Group'); ?></span>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <?php foreach ($userGroups as $value) { ?>
                                    <li>
                                        <a href="#" onclick="userGroupSave(<?php echo $value['id']; ?>, 1); return false;">
                                            <span class="fa fa-lock"></span>
                                            <span class="label label-info"><?php echo $value['total_users'] . " "; ?><?php echo __("Users linked"); ?></span>
                                            <?php echo $value['group_name']; ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>

                        <div class="btn-group" id="removeUserGroupBtnGroup">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" id="removeUserGroupBtn">
                                <i class="fas fa-user-slash"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __('Remove User Group'); ?></span>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <?php foreach ($userGroups as $value) { ?>
                                    <li>
                                        <a href="#" onclick="userGroupSave(<?php echo $value['id']; ?>, 0); return false;">
                                            <span class="fa fa-lock"></span>
                                            <span class="label label-info"><?php echo $value['total_users'] . " " . __("Users linked"); ?></span>
                                            <?php echo $value['group_name']; ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>

                    <?php if (empty($advancedCustom->disableVideoSwap) && (empty($advancedCustom->makeSwapVideosOnlyForAdmin) || Permissions::canAdminVideos())) { ?>
                        <button class="btn btn-primary" id="swapBtn">
                            <i class="fas fa-random"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __('Swap Video File'); ?></span>
                        </button>
                    <?php } ?>

                    <?php if (Permissions::canAdminVideos()) { ?>
                        <button class="btn btn-primary" id="updateAllUsage">
                            <i class="fas fa-chart-line"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __('Update all videos disk usage'); ?></span>
                        </button>
                    <?php } ?>

                    <?php if (AVideoPlugin::isEnabledByName('CDN') && CDN::userCanMoveVideoStorage()) {
                        include $global['systemRootPath'] . 'plugin/CDN/Storage/getVideoManagerButton.php';
                    } ?>

                    <button class="btn btn-danger" id="deleteBtn">
                        <i class="fa fa-trash" aria-hidden="true"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __('Delete'); ?></span>
                    </button>
                <?php } ?>
            </div>

            <div class="pull-right">
                <?php
                echo getTourHelpButton('view/managerVideos_body.help.json', 'btn btn-default', false);
                ?>
            </div>
        </div>
        <div class="panel-heading clearfix">

            <div class="btn-group pull-right" id="filterButtonsVideoManagerCategory">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <span class="activeFilterCategory"><i class="fas fa-list"></i> <?php echo __('All Categories'); ?></span> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <!-- Adding a search input at the top of the dropdown -->
                    <li>
                        <input type="text" id="searchCategory" class="form-control" placeholder="<?php echo __('Search'); ?>" />
                    </li>

                    <li><a href="#" onclick="filterCategory = ''; $('.activeFilterCategory').html('<i class=\'fas fa-list\'></i> <?php echo __('All Categories'); ?>');
                $('.tooltip').tooltip('hide');
                $('#grid').bootgrid('reload');
                return false;"><i class="fas fa-list"></i> <?php echo __('All Categories'); ?></a></li>
                    <?php
                    $categories_edit = Category::getAllCategories(true);
                    foreach ($categories_edit as $key => $value) {
                        $text = "<i class='{$value['iconClass']}'></i> " . __($value['hierarchyAndName']);
                        echo PHP_EOL . '<li class="categoryItem"><a href="#" onclick="filterCategory=\'' . $value['clean_name'] . '\'; $(\'.activeFilterCategory\').html(\'' . addcslashes($text, "'") . '\'); $(\'.tooltip\').tooltip(\'hide\');$(\'#grid\').bootgrid(\'reload\');return false;">' . $text . '</a></li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- jQuery to filter the list -->
            <script>
                $(document).ready(function() {
                    $("#searchCategory").on("keyup", function() {
                        var value = $(this).val().toLowerCase();
                        $(".categoryItem").filter(function() {
                            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                        });
                    });
                });
            </script>

            <div class="btn-group pull-right" id="filterButtonsVideoManager">
                <div class="btn-group ">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <span class="activeFilter"><i class="fas fa-icons"></i> <?php echo __('All Statuses'); ?></span> <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <li><a href="#" onclick="filterStatus = ''; $('.activeFilter').html('<i class=\'fas fa-icons\'></i> <?php echo __('All Statuses'); ?>');
                                $('.tooltip').tooltip('hide');
                                $('#grid').bootgrid('reload');
                                return false;"><i class="fas fa-icons"></i> <?php echo __('All Statuses'); ?></a></li>
                        <?php
                        if (!isset($statusSearchFilter)) {
                            $statusSearchFilter = array();
                        }
                        if (AVideoPlugin::isEnabled('FansSubscriptions')) {
                            $statusSearchFilter[] = Video::$statusFansOnly;
                        }
                        if (AVideoPlugin::isEnabled('SendRecordedToEncoder')) {
                            $statusSearchFilter[] = Video::$statusRecording;
                        }
                        foreach (Video::$statusDesc as $key => $value) {
                            if (!in_array($key, $statusSearchFilter)) {
                                continue;
                            }
                            $text = Video::$statusIcons[$key] . ' ' . __($value);
                            echo PHP_EOL . '<li><a href="#" onclick="filterStatus=\'' . $key . '\'; $(\'.activeFilter\').html(\'' . addcslashes($text, "'") . '\'); $(\'.tooltip\').tooltip(\'hide\');$(\'#grid\').bootgrid(\'reload\');return false;">' . $text . '</a></li>';
                        }
                        ?>
                        <li><a href="#" onclick="filterStatus = 'passwordProtected'; $('.activeFilter').html('<i class=\'fas fa-lock\' ></i> <?php echo __('Password Protected'); ?>');
                                $('.tooltip').tooltip('hide');
                                $('#grid').bootgrid('reload');
                                return false;"><i class="fas fa-lock"></i> <?php echo __('Password Protected'); ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="btn-group pull-right" id="filterTypeButtonsVideoManager">
                <div class="btn-group ">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <span class="activeTypeFilter"><i class="fas fa-icons"></i> <?php echo __('All Types'); ?></span> <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <li><a href="#" onclick="filterType = ''; $('.activeTypeFilter').html('<i class=\'fas fa-icons\'></i> <?php echo __('All Types'); ?>');
                                $('.tooltip').tooltip('hide');
                                $('#grid').bootgrid('reload');
                                return false;"><i class="fas fa-icons"></i> <?php echo __('All Types'); ?></a></li>
                        <?php
                        foreach (Video::getDistinctVideoTypes() as $value) {
                            $text = __($value);
                            echo PHP_EOL . '<li><a href="#" onclick="filterType=\'' . $value . '\'; $(\'.activeTypeFilter\').html(\'' . addcslashes($text, "'") . '\'); $(\'.tooltip\').tooltip(\'hide\');$(\'#grid\').bootgrid(\'reload\');return false;">' . $text . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <div class="btn-group pull-right" id="filterSearchButtonsVideoManager">
                <div class="btn-group ">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <span><?php echo __('Search Fields'); ?></span> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu" style="min-width: 200px;">

                        <?php
                        foreach (Video::$searchFieldsNamesLabels as $key => $value) {
                            $checked = 'checked';
                            if (!empty($editVideo)) {
                                $checked = Video::$searchFieldsNames[$key] == 'v.id' ? 'checked' : '';
                            }
                        ?>
                            <li onclick="$('#grid').bootgrid('reload');event.stopPropagation();">
                                <div class="form-check" style="padding-left: 5px;">
                                    <input class="form-check-input searchFieldsNames" type="checkbox" value="<?php echo Video::$searchFieldsNames[$key]; ?>" id="searchFieldsNames<?php echo $key; ?>" <?php echo $checked; ?>>
                                    <label class="form-check-label" for="searchFieldsNames<?php echo $key; ?>">
                                        <?php echo __($value); ?>
                                    </label>
                                </div>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="material-switch pull-right" style="margin-right: 20px; margin-top: 10px;">
                <?php echo __('Compact Mode'); ?>
                <input class="" data-toggle="toggle" type="checkbox" id="compactMode">
                <label for="compactMode" class="label-success" style="margin-left: 10px;"></label>
            </div>
            <script>
                $(document).ready(function() {
                    // Check if the compact mode cookie exists and apply the class
                    if (Cookies.get('compactMode') === 'on') {
                        $('body').addClass('compact');
                        $('#compactMode').prop('checked', true);
                    }

                    // Toggle compact mode on checkbox change
                    $('#compactMode').change(function() {
                        if ($(this).is(':checked')) {
                            $('body').addClass('compact');
                            Cookies.set('compactMode', 'on', {
                                expires: 7
                            }); // Cookie expires in 7 days
                        } else {
                            $('body').removeClass('compact');
                            Cookies.set('compactMode', 'off', {
                                expires: 7
                            });
                        }
                    });
                });
            </script>
        </div>
        <div class="panel-body">

            <table id="grid" class="table table-condensed table-hover table-striped videosManager">
                <thead>
                    <tr>
                        <th data-formatter="checkbox" data-width="25px"></th>
                        <th data-column-id="title" data-formatter="titleTag" data-width="200px"><?php echo __("Title"); ?></th>
                        <th data-column-id="tags" data-formatter="tags" data-sortable="false" data-width="300px" data-header-css-class='hidden-md hidden-sm hidden-xs' data-css-class='hidden-md hidden-sm hidden-xs tagsInfo'><?php echo __("Tags"); ?></th>
                        <th style="display: none;" data-column-id="sites_id" data-formatter="sites_id" data-width="50px" data-header-css-class='hidden-xs' data-css-class='hidden-xs'>
                            <?php echo htmlentities('<i class="fas fa-hdd" aria-hidden="true" data-placement="top" data-toggle="tooltip" title="' . __("Storage") . '"></i>'); ?>
                        </th>
                        <th style="display: none;" data-column-id="likes" data-width="50px" data-header-css-class='hidden-md hidden-sm hidden-xs' data-css-class='hidden-md hidden-sm hidden-xs'>
                            <?php echo htmlentities('<i class="far fa-thumbs-up" aria-hidden="true" data-placement="top" data-toggle="tooltip" title="' . __("Likes") . '"></i>'); ?>
                        </th>
                        <th style="display: none;" data-column-id="dislikes" data-width="50px" data-header-css-class='hidden-md hidden-sm hidden-xs' data-css-class='hidden-md hidden-sm hidden-xs'>
                            <?php echo htmlentities('<i class="far fa-thumbs-down" aria-hidden="true" data-placement="top" data-toggle="tooltip" title="' . __("Dislikes") . '"></i>'); ?>
                        </th>
                        <th style="display: none;" data-column-id="duration" data-width="80px" data-header-css-class='hidden-md hidden-sm hidden-xs showOnGridDone' data-css-class='hidden-md hidden-sm hidden-xs'>
                            <?php echo htmlentities('<i class="fas fa-stopwatch" aria-hidden="true" data-placement="top" data-toggle="tooltip" title="' . __("Duration") . '"></i>'); ?>
                        </th>
                        <th style="display: none;" data-column-id="views_count" data-formatter="views_count" data-width="50px" data-header-css-class='hidden-md hidden-sm hidden-xs showOnGridDone' data-css-class='hidden-md hidden-sm hidden-xs'>
                            <?php echo htmlentities('<i class="fas fa-eye" aria-hidden="true" data-placement="top" data-toggle="tooltip" title="' . __("Views") . '"></i>'); ?>
                        </th>
                        <th style="display: none;" data-column-id="total_seconds_watching" data-formatter="total_seconds_watching" data-width="100px" data-header-css-class='hidden-sm hidden-xs showOnGridDone' data-css-class='hidden-sm hidden-xs'>
                            <?php echo htmlentities('<i class="fas fa-stopwatch" aria-hidden="true" data-placement="top" data-toggle="tooltip" title="' . __("Time Watching") . '"></i>'); ?>
                        </th>
                        <?php
                        if (Permissions::canAdminVideos()) {
                        ?>
                            <th style="display: none;" data-column-id="isSuggested" data-formatter="isSuggested" data-width="42px" data-header-css-class='hidden-xs showOnGridDone' data-css-class='hidden-xs'>
                                <?php echo htmlentities('<i class="fas fa-star" aria-hidden="true" data-placement="top" data-toggle="tooltip" title="' . __("Suggested") . '"></i>'); ?>
                            </th>
                        <?php
                        }
                        ?>
                        <th style="display: none;" data-column-id="isChannelSuggested" data-formatter="isChannelSuggested" data-width="42px" data-header-css-class='hidden-xs showOnGridDone' data-css-class='hidden-xs'>
                            <?php echo htmlentities('<i class="fa-solid fa-thumbtack" aria-hidden="true" data-placement="top" data-toggle="tooltip" title="' . __("Pin On Channel") . '"></i>'); ?>
                        </th>
                        <th data-column-id="filesize" data-formatter="filesize" data-width="100px" data-header-css-class='hidden-md hidden-sm hidden-xs' data-css-class='hidden-md hidden-sm hidden-xs'><?php echo __("Size"); ?></th>
                        <th data-column-id="created" data-order="desc" data-width="150px" data-header-css-class='hidden-sm hidden-xs' data-css-class='hidden-sm hidden-xs'><?php echo __("Created"); ?></th>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false" data-css-class='controls' data-width="200px"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div id="videoFormModal" class="modal fade" tabindex="-1" role="dialog">
        <?php
        include $global['systemRootPath'] . 'view/managerVideos_form.php';
        ?>
    </div><!-- /.modal -->
    <div id="videoViewFormModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo __("Video Views"); ?></h4>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: scroll;">
                    <div class="progress" id="progress25" style="width: 100%;">
                        <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:0">
                            0%
                        </div>
                    </div>
                    <div class="progress" id="progress50" style="width: 100%;">
                        <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0">
                            0%
                        </div>
                    </div>
                    <div class="progress" id="progress75" style="width: 100%;">
                        <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0">
                            0%
                        </div>
                    </div>
                    <div class="progress" id="progress100" style="width: 100%;">
                        <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0">
                            0%
                        </div>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <?php
    if (empty($advancedCustom->disableDownloadVideosList)) {
    ?>
        <div class="btn-group pull-right" role="group">
            <a href="<?php echo $global['webSiteRootURL']; ?>objects/videos.txt.php?type=csv" target="_blank" class="btn btn-default btn-sm">
                <i class="fas fa-download"></i> <?php echo __("Download your videos sheet"); ?> <?php echo __("(Sheet .csv file)"); ?>
            </a>
            <a href="<?php echo $global['webSiteRootURL']; ?>objects/videos.txt.php?type=seo" target="_blank" class="btn btn-default btn-sm">
                <i class="fas fa-download"></i> <?php echo __("Download your videos list"); ?> <?php echo __("(SEO .txt file)"); ?>
            </a>
            <a href="<?php echo $global['webSiteRootURL']; ?>objects/videos.txt.php" target="_blank" class="btn btn-default btn-sm">
                <i class="fas fa-download"></i> <?php echo __("Download your videos list"); ?> <?php echo __("(Permalink .txt file)"); ?>
            </a>
        </div>
    <?php
    }
    ?>
</div><!--/.container-->

<script src="<?php echo getURL('view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js'); ?>" type="text/javascript"></script>

<!-- JavaScript Includes -->
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.knob.js'); ?>"></script>

<!-- jQuery File Upload Dependencies -->
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.ui.widget.js'); ?>"></script>
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.iframe-transport.js'); ?>"></script>
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.fileupload.js'); ?>"></script>
<?php
echo AVideoPlugin::getManagerVideosJavaScripts();
if (empty($advancedCustom->disableHTMLDescription)) {
    echo getTinyMCE("inputDescription");
}
?>
<script>
    var timeOut;
    var encodingNowId = '';
    var waitToSubmit = true;
    // make sure the video was uploaded, delete in case it was not uploaded
    var videoUploaded = false;
    var videos_id = <?php echo intval(@$_GET['video_id']); ?>;
    var isArticle = 0;
    var checkProgressTimeout = [];


    function saveVideoOnPlaylist(videos_id, add, playlists_id) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'objects/playListAddVideo.json.php',
            method: 'POST',
            data: {
                'videos_id': videos_id,
                'add': add,
                'playlists_id': playlists_id
            },
            success: function(response) {
                if (response.error) {
                    avideoToastError(__('Error on playlist'));
                } else {
                    avideoToastSuccess(__('Success'));
                }
                modal.hidePleaseWait();
            }
        });
    }

    function getSelectedVideos() {
        var vals = [];
        $(".checkboxVideo").each(function(index) {
            if ($(this).is(":checked")) {
                vals.push($(this).val());
            }
        });
        return vals;
    }

    function changeStatus(status) {
        modal.showPleaseWait();
        var vals = getSelectedVideos();
        $.ajax({
            url: webSiteRootURL + 'objects/videoStatus.json.php',
            data: {
                "id": vals,
                "status": status
            },
            type: 'post',
            success: function(response) {
                modal.hidePleaseWait();
                if (!response.status) {
                    avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                } else {
                    $("#grid").bootgrid('reload');
                }
            }
        });
    }

    function changeCategory(category_id) {
        modal.showPleaseWait();
        var vals = getSelectedVideos();
        $.ajax({
            url: webSiteRootURL + 'objects/videoCategory.json.php',
            data: {
                "id": vals,
                "category_id": category_id
            },
            type: 'post',
            success: function(response) {
                modal.hidePleaseWait();
                if (!response.status) {
                    avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                } else {
                    $("#grid").bootgrid('reload');
                }
            }
        });
    }

    <?php
    if (empty($advancedCustomUser->userCanNotChangeUserGroup) || Permissions::canAdminVideos()) {
    ?>

        function userGroupSave(users_groups_id, add) {
            modal.showPleaseWait();
            var vals = getSelectedVideos();
            $.ajax({
                url: webSiteRootURL + 'objects/userGroupSave.json.php',
                data: {
                    "id": vals,
                    "users_groups_id": users_groups_id,
                    "add": add
                },
                type: 'post',
                success: function(response) {
                    modal.hidePleaseWait();
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToastSuccess('Saved');
                        $("#grid").bootgrid('reload');
                    }
                }
            });
        }
    <?php
    }
    ?>

    function checkProgress(encoderURL) {
        $.ajax({
            url: encoderURL + 'status',
            success: function(response) {
                if (response.queue_list.length) {
                    for (i = 0; i < response.queue_list.length; i++) {
                        if (webSiteRootURL !== response.queue_list[i].streamer_site) {
                            continue;
                        }
                        if (response.queue_list[i].return_vars && response.queue_list[i].return_vars.videos_id) {
                            createQueueItem(response.queue_list[i], i);
                        }
                    }

                }
                if (response.encoding && response.encoding.length) {
                    for (i = 0; i < response.encoding.length; i++) {
                        var encoding = response.encoding[i];
                        if (typeof encoding.return_vars === 'undefined') {
                            continue;
                        }
                        var id = encoding.return_vars.videos_id;
                        $("#downloadProgress" + id).slideDown();
                        var download_status = response.download_status[i];
                        var encoding_status = response.encoding_status[i];
                        if (download_status && !encoding_status.progress) {
                            $("#encodingProgress" + id).find('.progress-completed').html("<strong>" + encoding.name + " [Downloading ...] </strong> " + download_status.progress + '%');
                        } else {
                            var encodingProgressCounter = $("#encodingProgressCounter" + id).text();
                            if (isNaN(encodingProgressCounter)) {
                                encodingProgressCounter = 0;
                            } else {
                                encodingProgressCounter = parseInt(encodingProgressCounter);
                            }


                            $("#encodingProgress" + id).find('.progress-completed').html("<strong>" + encoding.name + "[" + encoding_status.from + " to " + encoding_status.to + "] </strong> <span id='encodingProgressCounter" + id + "'>" + encodingProgressCounter + "</span>%");
                            $("#encodingProgress" + id).find('.progress-bar').css({
                                'width': encoding_status.progress + '%'
                            });
                            //$("#encodingProgressComplete" + id).text(response.encoding_status.progress + '%');
                            countTo("#encodingProgressComplete" + id, encoding_status.progress);
                            countTo("#encodingProgressCounter" + id, encoding_status.progress);
                        }
                        if (download_status) {
                            $("#downloadProgress" + id).find('.progress-bar').css({
                                'width': download_status.progress + '%'
                            });
                        }
                        if (encoding_status.progress >= 100 && $("#encodingProgress" + id).length) {
                            $("#encodingProgress" + id).find('.progress-bar').css({
                                'width': '100%'
                            });
                            $("#encodingProgressComplete" + id).text('100%');
                            clearTimeout(timeOut);
                            $.toast("Encode Complete");
                            timeOut = setTimeout(function() {
                                $("#grid").bootgrid('reload');
                            }, 5000);
                        } else {

                        }
                        clearTimeout(checkProgressTimeout[encoderURL]);
                        checkProgressTimeout[encoderURL] = setTimeout(function() {
                            checkProgress(encoderURL);
                        }, 10000);
                    }
                }

            }
        });
    }

    function confirmDeleteVideo(videos_id) {
        swal({
                title: "<?php echo __("Are you sure?"); ?>",
                text: "<?php echo __("You will not be able to recover this action!"); ?>",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then(function(willDelete) {
                if (willDelete) {
                    deleteVideo(videos_id);
                }
            });
    }

    function deleteVideo(videos_id) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'objects/videoDelete.json.php',
            data: {
                "id": videos_id
            },
            type: 'post',
            complete: function(resp) {
                response = resp.responseJSON
                console.log(response);
                modal.hidePleaseWait();
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToastSuccess(response.msg);
                    $("#grid").bootgrid("reload");
                }
            },
        });
    }

    function editVideo(row) {
        resetVideoForm();
        if (!row.id) {
            row.id = videos_id;
        }
        videos_id = row.id;
        $(".externalOptions").val("");
        try {
            if (typeof row.externalOptions == 'object') {
                externalOptionsObject = row.externalOptions;
            } else {
                externalOptionsObject = JSON.parse(row.externalOptions);
            }
            for (var key in externalOptionsObject) {
                if (externalOptionsObject.hasOwnProperty(key)) {
                    //console.log('externalOptions', key, externalOptionsObject[key]);
                    $('#' + key).val(externalOptionsObject[key]);
                }
            }
        } catch (e) {

        }
        $('.nav-tabs a[href="#pmetadata"]').tab('show');
        $('body').addClass('edit_' + row.type);
        $('body').addClass('is_editing');
        if (row.type === 'article') {
            isArticle = 1;
            reloadFileInput();
        } else {
            isArticle = 0;
            if ((row.type === 'embed') || (row.type === 'linkVideo') || (row.type === 'linkAudio')) {
                $('#videoLink').val(row.videoLink);
                $('#epg_link').val(row.epg_link);
                $('#videoLinkType').val(row.type);
            }
        }


        $('#inputVideoId').val(row.id);
        $('#inputTitle').val(row.title);
        $('#inputVideoPassword').val(row.video_password);
        $('#videoStatus').val(row.status);
        $('#inputTrailer').val(row.trailer1);
        $('#inputCleanTitle').val(row.clean_title);
        $('#created').val(row.created);
        <?php
        if (empty($advancedCustom->disableHTMLDescription)) {
        ?>
            $('#inputDescription').val(row.descriptionHTML);
            if (!empty(tinymce.get('inputDescription'))) {
                try {
                    tinymce.get('inputDescription').setContent(row.descriptionHTML);
                } catch (e) {
                    console.error('inputDescription', e, typeof tinymce.get('inputDescription'));
                }

            }

        <?php
        } else {
        ?>
            $('#inputDescription').val(row.description);
        <?php
        }
        ?>
        $('#inputCategory').val(row.categories_id);
        $('#inputCategory').trigger('change');
        $('#inputRrating').val(row.rrating);
        $('#madeForKids').prop('checked', !empty(row.made_for_kids));
        <?php
        echo AVideoPlugin::getManagerVideosEdit();
        ?>

        if (row.next_id) {
            $('#inputNextVideo-poster').attr('src', "<?php echo $global['webSiteRootURL']; ?>videos/" + row.next_filename + ".jpg");
            $('#inputNextVideo').val(row.next_title);
            $('#inputNextVideoClean').val("<?php echo $global['webSiteRootURL']; ?>video/" + row.next_clean_title);
            $('#inputNextVideo-id').val(row.next_id);
        }
        if (row.next_video && row.next_video.id) {
            $('#inputNextVideo-poster').attr('src', webSiteRootURL + "videos/" + row.next_video.filename + ".jpg");
            $('#inputNextVideo').val(row.next_video.title);
            $('#inputNextVideoClean').val(webSiteRootURL + "video/" + row.next_video.clean_title);
            $('#inputNextVideo-id').val(row.next_video.id);
        } else {
            try {
                $('#removeAutoplay').trigger('click');
            } catch (e) {}
        }


        var photoURL = webSiteRootURL + 'view/img/placeholders/user.png'
        if (row.photoURL) {
            photoURL = webSiteRootURL + row.photoURL + '?rand=' + Math.random();
        }
        $("#inputUserOwner-img").attr("src", photoURL);
        $('#inputUserOwner').val(row.user);
        $('#inputUserOwner_id').val(row.users_id).trigger('change');
        $('#users_id_company').val(row.users_id_company).trigger('change');

        <?php echo $updateUserAutocomplete; ?>
        $('#views_count').val(row.views_count);
        $('.videoGroups').prop('checked', false);
        $('.categoryGroups').prop('checked', false);
        $('.groupSwitch').parent().removeClass('categoryUserGroup');
        if (row.groups.length === 0) {
            $('#public').prop('checked', true);
        } else {
            $('#public').prop('checked', false);
            for (var index in row.groups) {
                if (row.groups[index].isCategoryUserGroup) {
                    var selector = $('#groupSwitch' + row.groups[index].id);
                    selector.addClass('categoryUserGroup');
                    $('#categoryGroup' + row.groups[index].id).prop('checked', true);
                } else {
                    $('#videoGroup' + row.groups[index].id).prop('checked', true);
                }
            }
        }

        if (row.can_download) {
            $('#can_download').prop('checked', true);
        } else {
            $('#can_download').prop('checked', false);
        }

        if (row.can_share) {
            $('#can_share').prop('checked', true);
        } else {
            $('#can_share').prop('checked', false);
        }

        if (row.only_for_paid) {
            $('#only_for_paid').prop('checked', true);
        } else {
            $('#only_for_paid').prop('checked', false);
        }

        if (row.only_for_paid) {
            $('#only_for_paid').prop('checked', true);
        } else {
            $('#only_for_paid').prop('checked', false);
        }

        $('#public').trigger("change");
        reloadFileInput(row);
        $('#input-jpg, #input-gif,#input-pjpg, #input-pgif, #input-webp').on('fileuploaded', function(event, data, previewId, index) {
            $("#grid").bootgrid("reload");
        })
        $('#videoFormModal').modal();
        videoUploaded = true;
    }

    function createFileInput(selector, row, type) {
        var filename = row.filename;
        var uploadUrl = webSiteRootURL + "objects/uploadPoster.php";
        var suffix = '.' + type;
        if (type === 'pjpg') {
            suffix = '_portrait.jpg';
        } else if (type === 'pgif') {
            suffix = '_portrait.gif';
        }
        var initialPreview = "<img style='height:160px' src='" + webSiteRootURL + "videos/" + filename + "/" + filename + suffix + "'>";
        uploadUrl = addQueryStringParameter(uploadUrl, 'type', type);

        $(selector).fileinput({
            maxFileCount: 1,
            uploadUrl: uploadUrl,
            theme: 'fa6',
            initialPreview: [initialPreview],
            initialPreviewShowDelete: false,
            showRemove: false,
            showClose: false,
            allowedFileExtensions: ["jpg", "jpeg", "png", "bmp", 'gif', "webp"],
            uploadExtraData: function() {
                return {
                    video_id: videos_id,
                    type: type
                };
            }
        }).on('fileuploaderror', function(event, data, msg) {
            console.log('fileuploaderror', data, msg);

            var form = data.form,
                files = data.files,
                extra = data.extra,
                response = data.response,
                reader = data.reader,
                jqXHR = data.jqXHR;

            console.log('FormData:', form);
            console.log('Files:', files);
            console.log('Extra Data:', extra);
            console.log('Response:', response);
            console.log('FileReader:', reader);
            console.log('jqXHR:', jqXHR);

            avideoAlertError(msg || 'An error occurred during file upload.');

            if (response && response.error) {
                avideoAlertError(response.msg);
                data.context.addClass('error');
            } else {
                console.log('Unexpected error occurred without response error');
                avideoAlertError('An unexpected error occurred.');
            }
        }).on('filebatchuploaderror', function(event, data, config, tags, extraData) {
            console.log('filebatchuploaderror', data, config, tags, extraData);
            avideoAlertError(data.response.msg);
        }).on('fileerror', function(event, data, previewId, index, fileId) {
            console.log('fileerror', data);
            avideoAlertError(data.response.msg);
            modal.hidePleaseWait();
        }).on('fileuploaded', function(event, data, previewId, index, fileId) {
            console.log('fileuploaded', data, previewId, index, fileId);
        });
    }



    function reloadFileInput(row) {
        if (!row || typeof row === 'undefined') {
            row = {
                id: 0,
                filename: "filename",
                clean_title: "blank"
            };
        }
        if (!row.id && videos_id) {
            row.id = videos_id;
        }
        /*
        if (!row.id) {
            setTimeout(function() {
                reloadFileInput(row);
            }, 500);
            return false;
        }
        */
        $('#input-jpg, #input-gif, #input-pjpg, #input-pgif, #input-webp').fileinput('destroy');
        console.trace();
        createFileInput("#input-jpg", row, 'jpg');
        createFileInput("#input-gif", row, 'gif');
        createFileInput("#input-pjpg", row, 'pjpg');
        createFileInput("#input-pgif", row, 'pgif');
        createFileInput("#input-webp", row, 'webp');
    }

    var modalSaveVideo;

    function saveVideo(closeModal) {
        if (waitToSubmit) {
            return false;
        }
        waitToSubmit = true;
        var isPublic = $('#public').is(':checked');
        var selectedVideoGroups = [];
        $('.videoGroups:checked').each(function() {
            selectedVideoGroups.push($(this).val());
        });
        if (!isPublic && selectedVideoGroups.length === 0) {
            isPublic = true;
        }
        if (isPublic) {
            selectedVideoGroups = [];
        }
        if (typeof modalSaveVideo === 'undefined') {
            modalSaveVideo = getPleaseWait();
        }
        modalSaveVideo.showPleaseWait();
        var externalOptionsObject = {};
        $('.externalOptions').each(function(i, obj) {
            var name = $(this).attr('id');
            eval('externalOptionsObject.' + name + '="' + $(this).val() + '"');
        });
        var externalOptions = JSON.stringify(externalOptionsObject);
        console.trace();
        $.ajax({
            url: webSiteRootURL + 'objects/videoAddNew.json.php',
            data: {
                "externalOptions": externalOptions,
                <?php
                echo AVideoPlugin::getManagerVideosAddNew();
                ?> "id": $('#inputVideoId').val(),
                "title": $('#inputTitle').val(),
                "trailer1": $('#inputTrailer').val(),
                "video_password": $('#inputVideoPassword').val(),
                "videoStatus": $('#videoStatus').val(),
                "videoLink": $('#videoLink').val(),
                "epg_link": $('#epg_link').val(),
                "videoLinkType": $('#videoLinkType').val(),
                "clean_title": $('#inputCleanTitle').val(),
                "created": $('#created').val(),
                <?php
                if (empty($advancedCustom->disableHTMLDescription)) {
                ?> "description": tinymce.get('inputDescription').getContent(),
                <?php } else {
                ?> "description": $('#inputDescription').val(),
                <?php } ?> "categories_id": $('#inputCategory').val(),
                "rrating": $('#inputRrating').val(),
                "made_for_kids": $('#madeForKids').is(':checked'),
                "public": isPublic,
                "videoGroups": selectedVideoGroups,
                "next_videos_id": $('#inputNextVideo-id').val(),
                "users_id": $('#inputUserOwner_id').val(),
                "users_id_company": $('#users_id_company').val(),
                "can_download": $('#can_download').is(':checked'),
                "can_share": $('#can_share').is(':checked'),
                "isArticle": isArticle,
                "only_for_paid": $('#only_for_paid').is(':checked'),
                "views_count": $('#views_count').val()
            },
            type: 'post',
            success: function(response) {
                if (response.status === "1" || response.status === true) {
                    if (response.video.id) {
                        videos_id = response.video.id;
                        //videoUploaded = videos_id;
                    }
                    /**/
                    if (response.video.type === 'embed' || response.video.type === 'linkVideo' || response.video.type === 'article') {
                        videoUploaded = true;
                    }

                    if (closeModal && videoUploaded) {
                        $('#videoFormModal').modal('hide');
                    }
                    $("#grid").bootgrid("reload");
                    $('#fileUploadVideos_id').val(response.videos_id);
                    $('#inputVideoId').val(response.videos_id);
                    videos_id = response.videos_id;
                } else {
                    if (response.error) {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                    } else {
                        avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your video has NOT been saved!"); ?>", "error");
                    }
                }
                modalSaveVideo.hidePleaseWait();
                setTimeout(function() {
                    waitToSubmit = false;
                }, 3000);
            }
        });
        return false;
    }

    function resetVideoEditClasses() {
        <?php
        foreach (Video::$typeOptions as $videoType) {
            echo "$('body').removeClass('edit_{$videoType}');";
        }
        ?>

        $('body').removeClass('edit_directUpload');
        $('body').removeClass('is_editing');
    }

    function resetVideoForm() {
        isArticle = 0;
        videos_id = 0;
        waitToSubmit = false;
        resetVideoEditClasses();
        $('#upload > ul > li').remove();
        $('#fileUploadVideos_id').val(0);
        $('#inputVideoId').val(0);
        $('#inputTitle').val("");
        $('#inputTrailer').val("");
        $('#videoStartSeconds').val('00:00:00');
        $('#videoSkipIntroSecond').val('00:00:00');
        $('#inputVideoPassword').val("");
        $('#videoStatus').val('<?php echo $advancedCustom->defaultVideoStatus->value; ?>');
        $('#inputCleanTitle').val("");
        $('#created').val("");
        $('#inputDescription').val("");
        $('#videoLinkType').val('');
        $('#videoLink').val('');
        $('#epg_link').val('');


        $('#inputShortSummary').val('');
        $('#inputMetaDescription').val('');
        $('#redirectVideoURL').val('');
        $('#redirectVideoCode').val('0');
        $('#releaseDate').val('now');
        $('#releaseDateTime').val('');


        if (typeof tinymce === 'object' && tinymce.get('inputDescription')) {
            tinymce.get('inputDescription').setContent('');
        }
        $('#inputCategory').val($('#inputCategory option:first').val());
        $('#inputCategory').trigger('change');
        $('#inputRrating').val("");
        $('#madeForKids').prop('checked', false);
        $('#removeAutoplay').trigger('click');
        var photoURL = '<?php echo User::getPhoto(); ?>';
        $("#inputUserOwner-img").attr("src", photoURL);
        $('#views_count').val(0);
        $('.videoGroups').prop('checked', false);
        $('#can_download').prop('checked', false);
        $('#can_share').prop('checked', false);
        $('#only_for_paid').prop('checked', false);
        $('#public').prop('checked', true);
        $('#public').trigger("change");
        reloadFileInput();
        $('#input-jpg, #input-gif,#input-pjpg, #input-pgif').on('fileuploaded', function(event, data, previewId, index) {
            $("#grid").bootgrid("reload");
        });
        videos_id = 0;

        $('#inputUserOwner').val('<?php echo User::getUserName(); ?>');
        $('#inputUserOwner_id').val(<?php echo User::getId(); ?>).trigger('change');
        $('#users_id_company').val(0).trigger('change');
        <?php echo $updateUserAutocomplete; ?>

        <?php
        echo AVideoPlugin::getManagerVideosReset();
        ?>
        setTimeout(function() {
            waitToSubmit = false;
        }, 2000);

    }

    function resetArticleForm() {
        resetVideoForm();
        isArticle = 1;
        videos_id = 0;
        saveVideo(false);
        reloadFileInput({});
        $('.nav-tabs a[href="#pmetadata"]').tab('show');
        $('#videoLinkType').val("article");
        $('#videoFormModal').modal();
    }

    function newVideo() {
        resetVideoForm();
        isArticle = 0;
        videos_id = 0;
        saveVideo(false);
        reloadFileInput({});
        $('body').addClass('edit_video');
        $('#inputTitle').val("Video automatically booked");
        $('#videoFormModal').modal();
    }

    function newDirectUploadVideo() {
        newVideo();
        $('body').addClass('edit_directUpload');
        $('.nav-tabs a[href="#pmedia"]').tab('show');
    }

    function newArticle() {
        resetArticleForm();
        $('body').addClass('edit_article');
        $('#inputTitle').val("Article automatically booked");
    }


    function newVideoLink() {
        resetVideoForm();
        isArticle = 0;
        videos_id = 0;
        $('#videoLinkType').val("linkVideo");
        saveVideo(false);
        reloadFileInput({});
        $('#input-jpg, #input-gif, #input-pjpg, #input-pgif').fileinput('destroy');
        $('.nav-tabs a[href="#pmetadata"]').tab('show');
        $('body').addClass('edit_linkVideo');
        $('#videoFormModal').modal();
    }


    function getVManagerImageTag(url) {
        return "<img class='img img-responsive img-thumbnail' src='" + addGetParam(url, 'cacherand', Math.random()) + "'  style='max-height:80px; margin-right: 5px;'> ";
    }

    function isVManagerGoodImage(filename) {
        var defaultAudio = /audio_wave/;
        var goodImageRexp = /_[0-9]{12}_[a-z0-9]{4,5}/;
        return goodImageRexp.test(filename) && !/notfound/i.test(filename) && !defaultAudio.test(filename);
    }

    function getVManagerBestImage(row) {
        var videosURL = row.videosURL;
        var image1 = '';
        var image2 = '';
        console.log('videosURL', videosURL);
        if (typeof videosURL.pjpg != 'undefined') {
            image1 = videosURL.pjpg.url;
            if (isVManagerGoodImage(videosURL.pjpg.filename)) {
                return image1;
            }
        }
        if (typeof videosURL.jpg != 'undefined') {
            image2 = videosURL.jpg.url;
            if (isVManagerGoodImage(videosURL.jpg.filename)) {
                return image2;
            }
        }

        return empty(image1) ? image2 : image1;
    }


    function getEmbedCode(id) {
        copyToClipboard($('#embedInput' + id).val());
        $('#copied' + id).fadeIn();
        setTimeout(function() {
            $('#copied' + id).fadeOut();
        }, 2000);
    }

    function createQueueItem(queueItem, position) {
        var id = queueItem.return_vars.videos_id;
        if ($('#encodeProgress' + id).children().length) {
            return false;
        }
        var item = '<div class="clearfix"></div><div class="progress progress-striped active " id="encodingProgress' + id + '" style="margin: 0;border-bottom-right-radius: 0; border-bottom-left-radius: 0;">';
        item += '<div class="progress-bar  progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0; animation-duration: 15s;animation: 15s;transition-duration: 15s; "><span id="encodingProgressComplete' + id + '">0</span>% Complete</div>';
        item += '<span class="progress-type"><span class="badge "><?php echo __("Queue Position"); ?> ' + position + '</span></span><span class="progress-completed">' + queueItem.name + '</span>';
        item += '</div><div class="progress progress-striped active " id="downloadProgress' + id + '" style="height: 10px; border-top-right-radius: 0; border-top-left-radius: 0;"><div class="progress-bar  progress-bar-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;"></div></div> ';
        $('#encodeProgress' + id).html(item);
    }
    /*
     function viewsDetails(views_count, views_count_25, views_count_50, views_count_75, views_count_100) {
     viewsDetailsReset();
     $("#videoViewFormModal .modal-title").html("Total views: " + views_count);
     var p25 = (views_count_25 / views_count) * 100;
     var p50 = (views_count_50 / views_count) * 100;
     var p75 = (views_count_75 / views_count) * 100;
     var p100 = (views_count_100 / views_count) * 100;
     console.log('views', views_count, views_count_25, views_count_50, views_count_75, views_count_100);
     console.log('p',p25, p50, p75, p100);
     $('#videoViewFormModal').modal();
     $("#progress25 .progress-bar")
     .css("width", p25 + "%")
     .attr("aria-valuenow", p25)
     .text("25/100: " + p25 + "%");
     $("#progress50 .progress-bar")
     .css("width", p50 + "%")
     .attr("aria-valuenow", p50)
     .text("Half: " + p50 + "%");
     $("#progress75 .progress-bar")
     .css("width", p75 + "%")
     .attr("aria-valuenow", p75)
     .text("75/100: " + p75 + "%");
     $("#progress100 .progress-bar")
     .css("width", p100 + "%")
     .attr("aria-valuenow", p100)
     .text("End: " + p100 + "%");
     }

     function viewsDetailsReset() {
     $("#videoViewFormModal .modal-title").html("Loading ... ");
     $("#progress25 .progress-bar")
     .css("width", "0")
     .attr("aria-valuenow", "0")
     .text("Loading ...");
     $("#progress50 .progress-bar")
     .css("width", "0")
     .attr("aria-valuenow", "0")
     .text("Loading ...");
     $("#progress75 .progress-bar")
     .css("width", "0")
     .attr("aria-valuenow", "0")
     .text("Loading ...");
     $("#progress100 .progress-bar")
     .css("width", "0")
     .attr("aria-valuenow", "0")
     .text("Loading ...");
     }
     */


    $(document).ready(function() {

        $('#videoFormModal').on('hidden.bs.modal', function() {
            var videos_id = $('#fileUploadVideos_id').val();
            if (!videoUploaded && videos_id) {
                deleteVideo(videos_id);
            }
            videoUploaded = false;
        });
        $('#videoFormModal').on('shown.bs.modal', function() {
            $(document).off('focusin.modal');
        });
        var ul = $('#upload ul');
        $('#drop a').click(function() {
            // Simulate a click on the file input button
            // to show the file browser dialog
            $(this).parent().find('input').click();
        });
        // Initialize the jQuery File Upload plugin
        $('#upload').fileupload({
            dropZone: null,
            pasteZone: null,
            // This function is called when a file is added to the queue;
            // either via the browse button, or via drag/drop:
            add: function(e, data) {
                var tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"' +
                    ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p style="color:#AAA;" class="action">Uploading...</p><p class="filename"></p><span></span></li>');
                // Append the file name and file size
                tpl.find('p.filename').text(data.files[0].name)
                    .append('<i>' + formatFileSize(data.files[0].size) + '</i>');
                // Add the HTML to the UL element
                data.context = tpl.appendTo(ul);
                // Initialize the knob plugin
                tpl.find('input').knob();
                // Listen for clicks on the cancel icon
                tpl.find('span').click(function() {
                    if (tpl.hasClass('working')) {
                        jqXHR.abort();
                    }
                    tpl.fadeOut(function() {
                        tpl.remove();
                    });
                });
                // Extract the filename without extension
                var filenameWithoutExt = data.files[0].name.replace(/\.[^/.]+$/, "");
                // Add the filename without extension as a title parameter
                data.formData = {
                    title: filenameWithoutExt,
                    videos_id: videos_id
                };
                // Automatically upload the file once it is added to the queue
                var jqXHR = data.submit();
                videoUploaded = true;
            },
            progress: function(e, data) {
                // Calculate the completion percentage of the upload
                var progress = parseInt(data.loaded / data.total * 100, 10);
                // Update the hidden input field and trigger a change
                // so that the jQuery knob plugin knows to update the dial
                data.context.find('input').val(progress).change();
                if (progress == 100) {
                    data.context.removeClass('working');
                }
            },
            fail: function(e, data) {
                // Something has gone wrong!
                data.context.addClass('error');
            },
            done: function(e, data) {
                if (data.result.error && data.result.msg) {
                    avideoAlertError(data.result.ms);
                    data.context.addClass('error');
                    data.context.find('p.action').text("Error");
                } else if (data.result.status === "error") {
                    if (typeof data.result.msg === 'string') {
                        msg = data.result.msg;
                    } else {
                        msg = data.result.msg[data.result.msg.length - 1];
                    }
                    avideoAlertError(msg);
                    data.context.addClass('error');
                    data.context.find('p.action').text("Error");
                } else {
                    console.log('upload done', data.result);
                    videos_id = data.result.videos_id;
                    data.context.find('p.action').html("Upload done");
                    data.context.addClass('working');
                    $("#grid").bootgrid("reload");
                }
            }
        });

        // Prevent the default action when a file is dropped on the window
        $(document).on('drop dragover', function(e) {
            e.preventDefault();
        });
        // Helper function that formats the file sizes
        function formatFileSize(bytes) {
            if (typeof bytes !== 'number') {
                return '';
            }

            if (bytes >= 1000000000) {
                return (bytes / 1000000000).toFixed(2) + ' GB';
            }

            if (bytes >= 1000000) {
                return (bytes / 1000000).toFixed(2) + ' MB';
            }

            return (bytes / 1000).toFixed(2) + ' KB';
        }
        if (!empty(_editVideo)) {
            waitToSubmit = true;
            editVideo(_editVideo);
        }

        $('#embedVideoLinkButton').click(function() {
            newVideoLink();
        });
        $("#checkBtn").click(function() {
            var chk = $("#chk").hasClass('fa-check-square');
            $(".checkboxVideo").each(function(index) {
                if (chk) {
                    $("#chk").removeClass('fa-check-square');
                    $("#chk").addClass('fa-square');
                } else {
                    $("#chk").removeClass('fa-square');
                    $("#chk").addClass('fa-check-square');
                }
                $(this).prop('checked', !chk);
            });
        });
        $("#deleteBtn").click(function() {
            swal({
                    title: "<?php echo __("Are you sure?"); ?>",
                    text: "<?php echo __("You will not be able to recover this action!"); ?>",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then(function(willDelete) {
                    if (willDelete) {
                        avideoAlert("Deleted!", "", "success");
                        modal.showPleaseWait();
                        var vals = getSelectedVideos();
                        deleteVideo(vals);
                    } else {

                    }
                });
        });
        <?php
        if (empty($advancedCustom->disableVideoSwap) && (empty($advancedCustom->makeSwapVideosOnlyForAdmin) || Permissions::canAdminVideos())) {
        ?>

            $("#swapBtn").click(function() {
                var vals = getSelectedVideos();
                if (vals.length !== 2) {
                    avideoAlertError(__("You MUST select 2 videos to swap"));
                    return false;
                }
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'objects/videoSwap.json.php',
                    data: {
                        "users_id": <?php echo User::getId(); ?>,
                        "videos_id_1": vals[0],
                        "videos_id_2": vals[1]
                    },
                    type: 'post',
                    success: function(response) {
                        modal.hidePleaseWait();
                        if (response.error) {
                            avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                        } else {
                            avideoAlert("<?php echo __("Success!"); ?>", "<?php echo __("Video swapped!"); ?>", "success");
                            $("#grid").bootgrid("reload");
                        }
                    }
                });
            });
        <?php
        }
        if (Permissions::canAdminVideos()) {
        ?>

            $("#updateAllUsage").click(function() {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'objects/videoUpdateUsage.json.php',
                    success: function(response) {
                        modal.hidePleaseWait();
                        if (response.error) {
                            avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                        } else {
                            avideoAlert("<?php echo __("Success!"); ?>", "<?php echo __("Videos Updated!"); ?>", "success");
                            $("#grid").bootgrid("reload");
                        }
                    }
                });
            });
        <?php
        }
        ?>

        $('.datepicker').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            autoclose: true
        });
        $('#public').change(function() {
            if ($('#public').is(':checked')) {
                $('.non-public').slideUp();
            } else {
                $('.non-public').slideDown();
            }
        });
        $('#removeAutoplay').click(function() {
            $('#inputNextVideo-poster').attr('src', "<?php echo ImagesPlaceHolders::getVideoPlaceholder(); ?>");
            $('#inputNextVideo').val("");
            $('#inputNextVideoClean').val("");
            $('#inputNextVideo-id').val("");
        });

        function getGridCurrentPage() {
            return $("#grid").bootgrid("getCurrentPage");
            //                                       //return parseInt($('#grid-footer > div > div:nth-child(1) > ul > li.active > a').attr('data-page'));
        }

        function getGridURL() {
            var url = webSiteRootURL + "objects/videos.json.php";
            url = addQueryStringParameter(url, 'showAll', 1);
            url = addQueryStringParameter(url, 'status', filterStatus);
            url = addQueryStringParameter(url, 'type', filterType);
            url = addQueryStringParameter(url, 'catName', filterCategory);
            $('.searchFieldsNames:checked').each(function(index) {
                url = addGetParam(url, 'searchFieldsNames[' + index + ']', $(this).val());
            });
            return url;
        }

        var grid = $("#grid").bootgrid({
            padding: 4,
            labels: {
                noResults: __("No results found!"),
                all: __("All"),
                infos: "<?php echo __("Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries"); ?>",
                loading: __("Loading..."),
                refresh: __("Refresh"),
                search: __("Search"),
            },
            rowCount: <?php echo $advancedCustom->videosManegerRowCount; ?>,
            ajax: true,
            url: getGridURL,
            formatters: {
                "commands": function(column, row) {
                    var embedBtn = '';
                    <?php
                    if (empty($advancedCustom->disableCopyEmbed)) {
                    ?>
                        embedBtn += '<button type="button" class="btn btn-xs btn-default command-embed" id="embedBtn' + row.id + '"  onclick="getEmbedCode(\'' + row.id + 'C\')" data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Copy embed URL")); ?>"><i class="fa-solid fa-link"></i> <span id="copied' + row.id + 'C" style="display:none;"><?php echo str_replace("'", "\\'", __("Copied")); ?></span></button>'
                        embedBtn += '<input type="hidden" id="embedInput' + row.id + 'C" value=\'<?php echo "{$global['webSiteRootURL']}vEmbed/' + row.id + '"; ?>\'/>';

                        embedBtn += '<button type="button" class="btn btn-xs btn-default command-embed" id="embedBtn' + row.id + '"  onclick="getEmbedCode(' + row.id + ')" data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Copy embed code")); ?>"><i class="fa-solid fa-code"></i> <span id="copied' + row.id + '" style="display:none;"><?php echo str_replace("'", "\\'", __("Copied")); ?></span></button>'
                        embedBtn += '<input type="hidden" id="embedInput' + row.id + '" value=\'<?php echo str_replace(array("{embedURL}", "{videoLengthInSeconds}"), array("{$global['webSiteRootURL']}vEmbed/' + row.id + '", "' + row.duration_in_seconds + '"), str_replace("'", "\"", $advancedCustom->embedCodeTemplate)); ?>\'/>';
                    <?php
                    }
                    ?>

                    var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Edit")); ?>"><i class="fa-solid fa-pen-to-square"></i></button>'
                    var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '"  data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Delete")); ?>"><i class="fa fa-trash"></i></button>';

                    <?php
                    $totalStatusButtons = count($statusThatTheUserCanUpdate);
                    foreach ($statusThatTheUserCanUpdate as $key => $value) {
                        $index = $key + 1;
                        if ($index > $totalStatusButtons - 1) {
                            $index = 0;
                        }
                        $nextStatus = $statusThatTheUserCanUpdate[$index][0];
                        $format = __("This video is %s, click here to make it %s");
                        $statusIndex = $value[0];
                        $statusColor = $value[1];
                        $tooltip = sprintf($format, Video::$statusDesc[$statusIndex], Video::$statusDesc[$nextStatus]);

                        echo "var statusBtn_{$statusIndex} = '<button type=\"button\" style=\"color: {$statusColor}\" class=\"btn btn-default btn-xs command-statusBtn\"  data-row-id=\"' + row.id + '\" nextStatus=\"{$nextStatus}\"  data-toggle=\"tooltip\" title=" . printJSString($tooltip, true) . ">" . str_replace("'", '"', Video::$statusIcons[$statusIndex]) . "</button>';";
                    }
                    ?>

                    var status;
                    var pluginsButtons = '<?php echo AVideoPlugin::getVideosManagerListButton(); ?>';
                    var download = '';
                    var downloadhighest = '';
                    <?php
                    if (CustomizeUser::canDownloadVideos()) {
                    ?>
                        for (var k in row.videosURL) {
                            var pattern = /_thumbs/i;
                            if (pattern.test(k) === true) {
                                continue;
                            }
                            if (/.m3u8/i.test(k) === true) {
                                continue;
                            }
                            if (typeof row.videosURL[k].url === 'undefined' || !row.videosURL[k].url) {
                                continue;
                            }
                            //var url = (typeof row.videosURL[k].url_noCDN !== 'undefined')?row.videosURL[k].url_noCDN:row.videosURL[k].url;
                            var url = (typeof row.videosURL[k].url !== 'undefined') ? row.videosURL[k].url : row.videosURL[k].url;
                            var addParameters = true;
                            if (url.includes('.s3.')) {
                                addParameters = false;
                            }
                            var downloadURL = url;
                            if (addParameters) {
                                downloadURL = addGetParam(url, 'download', 1);
                            }
                            var pattern = /^m3u8/i;
                            if (pattern.test(k) === true) {
                                if (addParameters) {
                                    downloadURL = addGetParam(downloadURL, 'title', row.clean_title + '_' + k + '.mp4');
                                }
                                download += '<div class="btn-group btn-group-justified">';
                                download += '<a class="btn btn-default btn-xs" onclick="copyToClipboard(\'' + url + '\');" ><span class="fa fa-copy " aria-hidden="true"></span> ' + k + '</a>';
                                download += '<a href="' + downloadURL + '" class="btn btn-default btn-xs" target="_blank" ><span class="fa fa-download " aria-hidden="true"></span> MP4</a>';
                                download += '</div>';
                            } else {
                                if (addParameters) {
                                    downloadURL = addGetParam(downloadURL, 'title', row.clean_title + '.mp4');
                                }
                                download += '<a href="' + downloadURL + '" class="btn btn-default btn-xs btn-block" target="_blank"  ><span class="fa fa-download " aria-hidden="true"></span> ' + k + '</a>';
                            }
                            if ((/\.(mp3|mp4|webm)\?/i.test(downloadURL) === true)) {
                                downloadhighest = downloadURL;
                            }
                        }
                    <?php
                    }
                    if (Permissions::canAdminVideos()) {
                    ?>
                        download += '<button type="button" class="btn btn-default btn-xs btn-block" onclick="whyICannotDownload(' + row.id + ');"  data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Download disabled")); ?>"><span class="fa-stack" style="font-size: 0.8em;"><i class="fa fa-download fa-stack-1x"></i><i class="fas fa-ban fa-stack-2x" style="color:Tomato"></i></span></button>';
                    <?php
                    }

                    if (!isset($statusThatShowTheCompleteMenu)) {
                        $statusThatShowTheCompleteMenu = array();
                    }
                    $ifCondition = 'row.status == "' . implode('" || row.status == "', $statusThatShowTheCompleteMenu) . '"';
                    ?>
                    if (!empty(download)) {
                        download = '<button type="button" class="btn btn-default btn-xs btn-block" data-placement="left" data-toggle="tooltip" title="' + __("Download File") + '" onclick="$(\'#DownloadFiles' + row.id + '\').slideToggle();" ><span class="fa fa-download " aria-hidden="true"></span> ' + __('Download File') + '</button><div id="DownloadFiles' + row.id + '" style="display: none;">' + download + '</div>';
                    }
                    if (<?php echo $ifCondition; ?>) {
                        eval('if(typeof statusBtn_' + row.status + ' !== "undefined"){status = statusBtn_' + row.status + ';}else if("h"=="' + row.status + '"){status = \'<button type="button" class="btn btn-danger btn-xs command-releaseNow" data-row-id="' + row.id + '" data-toggle="tooltip" title="Release now"><i class="fas fa-check"></i></button>\';}else{status = ""}');
                    } else {
                        return editBtn + deleteBtn;
                    }

                    var nextIsSet = '';
                    if (row.next_video == null || row.next_video.length == 0) {
                        //nextIsSet = "<span class='label label-danger'> <?php echo __("Next video NOT set"); ?> </span>";
                    } else {
                        var nextVideoTitle;
                        if (row.next_video.title.length > 20) {
                            nextVideoTitle = row.next_video.title.substring(0, 18) + "..";
                        } else {
                            nextVideoTitle = row.next_video.title;
                        }
                        nextIsSet = "<span class='label label-success' data-toggle='tooltip' title='" + row.next_video.title + "'>Next video: " + nextVideoTitle + "</span>";
                    }

                    var suggestBtn = '';
                    var editLikes = '';
                    <?php
                    if (Permissions::canAdminVideos()) {
                    ?>
                        editLikes = '<button type="button" class="btn btn-default btn-xs command-editlikes"  data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Edit Likes")); ?>"><i class="far fa-thumbs-up"></i> <i class="far fa-thumbs-down"></i></button>';

                        var suggest = '<button style="color: #C60" type="button" class="btn btn-default btn-xs command-suggest"  data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Unsuggest")); ?>"><i class="fas fa-star" aria-hidden="true"></i></button>';
                        var unsuggest = '<button style="" type="button" class="btn btn-default btn-xs command-suggest unsuggest"  data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Suggest")); ?>"><i class="far fa-star" aria-hidden="true"></i></button>';
                        suggestBtn = unsuggest;
                        if (row.isSuggested == "1") {
                            suggestBtn = suggest;
                        }
                    <?php
                    }
                    ?>
                    var playBtn = '<button type="button" class="btn btn-default btn-xs"  onclick="avideoModalIframe(\'' + row.embedlink + '\')"  data-toggle="tooltip" title="<?php echo __('Play'); ?>"><span class="fas fa-play" aria-hidden="true"></span></button>';

                    var _edit = '<button type="button" class="btn btn-default btn-block btn-sm btn-xs edit-simple" onclick="avideoModalIframe(webSiteRootURL +\'view/managerVideosLight.php?videos_id=' + row.id + '\')"   data-toggle="tooltip" title="<?php echo __('Title and Description'); ?>"><i class="fas fa-edit"></i> <?php echo __('Title and Description'); ?></button>';
                    var _thumbnail = '<button type="button" class="btn btn-default btn-block btn-sm btn-xs edit-thumbs" onclick="avideoModalIframe(webSiteRootURL +\'view/managerVideosLight.php?image=1&videos_id=' + row.id + '\')"   data-toggle="tooltip" title="<?php echo __('Custom Thumbnail'); ?>"><i class="far fa-image"></i> <?php echo __('Custom Thumbnail'); ?></button>';
                    var _download = '';
                    if (downloadhighest) {
                        _download = '<a href=' + downloadhighest + ' class="btn btn-default btn-sm btn-xs  btn-block downloadhigest" data-toggle="tooltip" title="<?php echo __('Download'); ?>"><i class="fas fa-download"></i> <?php echo __('Download'); ?></a>';
                    }

                    var bigButtons = _edit + _thumbnail + _download;

                    return '<div class="scrollIfCompact">' + playBtn + embedBtn + editBtn + deleteBtn + status + suggestBtn + editLikes + bigButtons + pluginsButtons + download + nextIsSet + '<div>';
                },
                "tags": function(column, row) {
                    var tags = '';
                    tags += "<div class=\"clearfix\"></div><span class='label label-primary  tagTitle'>#ID</span><span class=\"label label-default \">" + row.id + "</span>";
                    <?php
                    if (Permissions::canAdminVideos()) {

                    ?>
                        var channelURL = webSiteRootURL + "view/channel.php?";
                        channelURL = addQueryStringParameter(channelURL, 'channel_users_id', row.users_id);
                        tags += "<div class=\"clearfix\"></div><span class='label label-primary  tagTitle'><?php echo __("Owner") . ":"; ?> </span><span class=\"label label-default \"><a href=\"" + channelURL + "\" target=\"_blank\" style=\"color: #FFF;\">" + row.user + "</a></span>";
                    <?php
                    }
                    ?>

                    if (row.maxResolution && row.maxResolution.resolution_string && row.maxResolution.resolution_string !== '0p') {
                        tags += "<div class=\"clearfix\"></div><span class='label label-primary  tagTitle'><?php echo __("Resolution") . ":"; ?> </span><span class=\"label label-default \">" + row.maxResolution.resolution_string + "</span>";
                    }
                    for (var i in row.tags) {
                        if (typeof row.tags[i].type == "undefined" || row.tags[i].label.length === 0) {
                            continue;
                        }
                        var text = row.tags[i].text;
                        if (typeof row.tags[i].tooltip !== "undefined" && text != row.tags[i].tooltip) {
                            text += ' ' + row.tags[i].tooltip;
                        }

                        if (typeof row.tags[i].tooltipIcon !== "undefined") {
                            text = row.tags[i].tooltipIcon + ' ' + text;
                        }

                        tags += "<div class=\"clearfix\"></div><span class='label label-primary  tagTitle'>" + row.tags[i].label + ": </span><span class=\"label label-" + row.tags[i].type + " \">" + text + "</span>";
                    }
                    tags += "<div class=\"clearfix\"></div><span class='label label-primary  tagTitle'><?php echo __("Type") . ":"; ?> </span><span class=\"label label-default \">" + row.type + "</span>";
                    //tags += "<div class=\"clearfix\"></div><span class='label label-primary  tagTitle'><?php echo __("Views") . ":"; ?> </span><span class=\"label label-default \">" + row.views_count_short + " <a href='#' class='viewsDetails' onclick='viewsDetails(" + row.views_count + ", " + row.views_count_25 + "," + row.views_count_50 + "," + row.views_count_75 + "," + row.views_count_100 + ");'>[<i class='fas fa-info-circle'></i> Details]</a></span>";
                    tags += "<div class=\"clearfix\"></div><span class=\"typeFormat\"><span class='label label-primary  tagTitle'><?php echo __("Format") . ":"; ?> </span><span class=\"typeLabels\">" + row.typeLabels + "</span></span>";
                    if (row.encoderURL) {
                        tags += "<div class=\"clearfix\"></div><span class='label label-primary  tagTitle'><?php echo __("Encoder") . ":"; ?> </span><span class=\"label label-default \">" + row.encoderURL + "</span>";
                        clearTimeout(checkProgressTimeout[row.encoderURL]);
                        checkProgressTimeout[row.encoderURL] = setTimeout(function() {
                            checkProgress(row.encoderURL);
                        }, 1000);
                    }

                    return '<div class="tagsContainer scrollIfCompact">' + tags + '</div>';
                },
                "filesize": function(column, row) {
                    return formatFileSize(row.filesize);
                },
                "sites_id": function(column, row) {
                    if (row.sites_id) {
                        return '<i class="fas fa-cloud"></i>';
                    } else {
                        return '<i class="fas fa-map-marker"></i>';
                    }
                },
                "isSuggested": function(column, row) {
                    var suggestBtn = '';
                    <?php
                    if (Permissions::canAdminVideos()) {
                    ?>
                        var suggest = '<button style="color: #C60" type="button" class="btn btn-default btn-xs command-suggest"  data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Unsuggest")); ?>"><i class="fas fa-star" aria-hidden="true"></i></button>';
                        var unsuggest = '<button style="" type="button" class="btn btn-default btn-xs command-suggest unsuggest"  data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Suggest")); ?>"><i class="far fa-star" aria-hidden="true"></i></button>';
                        suggestBtn = unsuggest;
                        if (row.isSuggested == "1") {
                            suggestBtn = suggest;
                        }
                    <?php
                    }
                    ?>
                    return suggestBtn;
                },
                "isChannelSuggested": function(column, row) {
                    var suggestBtn = '';
                    var suggest = '<button style="color: #C60" type="button" class="btn btn-default btn-xs command-Channelsuggest"  data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Unpin On Channel")); ?>"><i class="fa-solid fa-thumbtack"></i></button>';
                    var unsuggest = '<button style="" type="button" class="btn btn-default btn-xs command-Channelsuggest unsuggest"  data-toggle="tooltip" title="<?php echo str_replace("'", "\\'", __("Pin On Channel")); ?>"><i class="fa-solid fa-thumbtack-slash"></i></button>';
                    suggestBtn = unsuggest;
                    if (row.isChannelSuggested == "1") {
                        suggestBtn = suggest;
                    }
                    return suggestBtn;
                },
                "checkbox": function(column, row) {
                    var tags = "<input type='checkbox' name='checkboxVideo' class='checkboxVideo' value='" + row.id + "'>";
                    return tags;
                },
                "total_seconds_watching": function(column, row) {
                    return '<small style="white-space: normal;">' +
                        '<a href="#" onclick="avideoModalIframe(webSiteRootURL +\'view/videoViewsInfo.php?videos_id=' + (row.id.toString()) + '\');return false;">' +
                        (row.total_seconds_watching_human.toString()) +
                        '</a></small>';
                },
                "views_count": function(column, row) {
                    return row.views_count_short;
                },
                "titleTag": function(column, row) {
                    var tags = '';
                    var youTubeLink = "",
                        youTubeUpload = '';
                    yt = '';

                    if (row.status !== "a") {
                        tags += '<div id="encodeProgress' + row.id + '"></div>';
                    }
                    if (/^x.*$/gi.test(row.status) || row.status == 'e') {
                        //tags += '<div class="progress progress-striped active" style="margin:5px;"><div id="encodeProgress' + row.id + '" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0px"></div></div>';


                    } else if (row.status == 'd') {
                        tags += '<div class="progress progress-striped active" style="margin:5px;"><div id="downloadProgress' + row.id + '" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0px;"></div></div>';
                    }
                    var type, img, is_portrait;

                    if (row.type === "audio") {
                        type = "<i class='fa fa-headphones hidden-xs' style='font-size:14px;'></i> ";
                    } else {
                        type = "<i class='fa fa-film hidden-xs' style='font-size:14px;'></i> ";
                    }
                    if (typeof row.videosURL !== 'undefined') {
                        img = getVManagerImageTag(getVManagerBestImage(row));
                    } else {
                        is_portrait = (row.rotation === "90" || row.rotation === "270") ? "img-portrait" : "";
                        img = "<img class='img img-responsive " + is_portrait + " img-thumbnail pull-left rotate" + row.rotation + " imgt4' src='" + webSiteRootURL + "videos/" + row.filename + ".jpg?cache=" + Math.random() + "'  style='max-height:80px; margin-right: 5px;'> ";
                    }
                    <?php
                    if (AVideoPlugin::isEnabledByName('PlayLists')) {
                    ?>
                        var playList = "<hr class='hideIfCompact'><div class='videoPlaylist hideIfCompact' videos_id='" + row.id + "' id='videoPlaylist" + row.id + "' style='height:200px; overflow-y: scroll; padding:10px 5px;'></div>";
                    <?php
                    } else {
                    ?>
                        var playList = '';
                    <?php
                    }
                    ?>
                    //img = img + '<div class="hidden-md hidden-lg"><i class="fas fa-stopwatch"></i> ' + row.duration + '</div>';
                    var pluginsButtons = '<?php echo AVideoPlugin::getVideosManagerListButtonTitle(); ?>';
                    var buttonTitleLink = '<a href="' + row.link + '" class="btn btn-default btn-block titleBtn" style="overflow: hidden;" target="_top">' + img + '<br>' + type + row.title + '</a>';
                    return '<div>' + buttonTitleLink + tags + "<div class='clearfix hideIfCompact'></div><div class='gridYTPluginButtons hideIfCompact'>" + yt + pluginsButtons + "</div>" + playList + '</div>';
                }


            },
            post: function() {
                var page = getGridCurrentPage();
                if (!page) {
                    page = 1;
                }
                console.log('post page', page);
                var ret = {
                    current: page
                };
                return ret;
            },
        }).on("loaded.rs.jquery.bootgrid", function() {
            $(".tooltip").tooltip("hide");
            if ($('.videoPlaylist').length > 50) {
                console.log("You are listing too many videos we will not process the playlist");
            } else {
                $('.videoPlaylist').each(function(i, obj) {
                    var $this = this;
                    var videos_id = $($this).attr('videos_id');
                    playlistsFromUserVideos(videos_id);
                    //$(this).html($(this).attr('videos_id'));
                });
            }
            if (!empty(_editVideo)) {
                $(".bootgrid-header .search-field").val(_editVideo.id);
                // Opcional: Execute uma busca automaticamente com o valor padrão
                grid.bootgrid("search", _editVideo.id);
            }

            /* Executes after data is loaded and rendered */
            grid.find(".command-edit").on("click", function(e) {
                    waitToSubmit = true;
                    var row_index = $(this).closest('tr').index();
                    var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                    editVideo(row);
                }).end().find(".command-delete").on("click", function(e) {
                    var row_index = $(this).closest('tr').index();
                    var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                    confirmDeleteVideo(row.id);
                })
                .end().find(".command-refresh").on("click", function(e) {
                    var row_index = $(this).closest('tr').index();
                    var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                    modal.showPleaseWait();
                    $.ajax({
                        url: webSiteRootURL + 'objects/videoRefresh.json.php',
                        data: {
                            "id": row.id
                        },
                        type: 'post',
                        success: function(response) {
                            $("#grid").bootgrid("reload");
                            modal.hidePleaseWait();
                        }
                    });
                }).end().find(".command-statusBtn").on("click", function(e) {
                    toggleVideoStatus(this);
                }).end().find(".command-rotate").on("click", function(e) {
                    var row_index = $(this).closest('tr').index();
                    var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                    modal.showPleaseWait();
                    $.ajax({
                        url: webSiteRootURL + 'objects/videoRotate.json.php',
                        data: {
                            "id": row.id,
                            "type": $(this).attr('data-row-id')
                        },
                        type: 'post',
                        success: function(response) {
                            $("#grid").bootgrid("reload");
                            modal.hidePleaseWait();
                        }
                    });
                })
                .end().find(".command-reencode").on("click", function(e) {
                    var row_index = $(this).closest('tr').index();
                    var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                    modal.showPleaseWait();
                    $.ajax({
                        url: webSiteRootURL + 'objects/videoReencode.json.php',
                        data: {
                            "id": row.id,
                            "status": "i",
                            "type": $(this).attr('data-row-id')
                        },
                        type: 'post',
                        success: function(response) {
                            modal.hidePleaseWait();
                            if (response.error) {
                                avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                            } else {
                                $("#grid").bootgrid("reload");
                            }
                        }
                    });
                })
                /*
                .end().find(".command-uploadYoutube").on("click", function(e) {
                    var row_index = $(this).closest('tr').index();
                    var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                    modal.showPleaseWait();
                    $.ajax({
                        url: webSiteRootURL + 'objects/youtubeUpload.json.php',
                        data: {
                            "id": row.id
                        },
                        type: 'post',
                        success: function(response) {
                            modal.hidePleaseWait();
                            if (response.msg) {
                                avideoAlertInfo(response.msg);
                            }
                        }
                    });
                })*/
                .end().find(".command-releaseNow").on("click", async function(e) {
                    var row_index = $(this).closest('tr').index();
                    var row = $("#grid").bootgrid("getCurrentRows")[row_index];

                    var confirm = await avideoConfirm('Release video ' + row.title + '?');
                    if (confirm) {
                        modal.showPleaseWait();
                        var url = webSiteRootURL + 'plugin/Scheduler/releaseVideoNow.json.php';
                        $.ajax({
                            url: url,
                            data: {
                                videos_id: row.id
                            },
                            type: 'post',
                            complete: function(resp) {
                                response = resp.responseJSON
                                console.log(response);
                                modal.hidePleaseWait();
                                if (response.error) {
                                    avideoAlertError(response.msg);
                                } else {
                                    avideoToastSuccess(response.msg);
                                    $("#grid").bootgrid("reload");
                                }
                            },
                        });
                    }
                });
            <?php
            if (Permissions::canAdminVideos()) {
            ?>
                grid.find(".command-suggest").on("click", function(e) {
                    var row_index = $(this).closest('tr').index();
                    var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                    var isSuggested = $(this).hasClass('unsuggest');

                    setVideoSuggested(row.id, isSuggested).then((data) => {
                        $("#grid").bootgrid("reload");
                    }).catch((error) => {
                        console.log(error)
                    });
                });
                grid.find(".command-editlikes").on("click", function(e) {
                    var row_index = $(this).closest('tr').index();
                    var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                    avideoModalIframeSmall(webSiteRootURL + 'view/likes.edit.form.php?videos_id=' + row.id);
                });
            <?php
            }
            ?>

            grid.find(".command-Channelsuggest").on("click", function(e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                var isSuggested = $(this).hasClass('unsuggest');

                setVideoChannelSuggested(row.id, isSuggested).then((data) => {
                    $("#grid").bootgrid("reload");
                }).catch((error) => {
                    console.log(error)
                });
            });
        });
        $('#inputCleanTitle').keyup(function(evt) {
            $('#inputCleanTitle').val(clean_name($('#inputCleanTitle').val()));
        });
        $('#inputTitle').keyup(function(evt) {
            $('#inputCleanTitle').val(clean_name($('#inputTitle').val()));
        });
        $('#addCategoryBtn').click(function(evt) {
            $('#inputCategoryId').val('');
            $('#inputName').val('');
            $('#inputCleanName').val('');
            $('#videoFormModal').modal();
        });
        $('.saveVideoBtn').click(function(evt) {
            saveVideo(true);
        });

        setTimeout(function() {
            <?php
            if (!empty($_GET['link'])) {
            ?>
                $('#embedVideoLinkButton').trigger('click');
            <?php
            } elseif (!empty($_GET['article'])) {
            ?>
                $('#addArticleButton').trigger('click');
            <?php
            } elseif (!empty($_GET['upload'])) {
            ?>
                $('#uploadMp4Button').trigger('click');
            <?php
            }
            ?>
            $('.showOnGridDone').fadeIn();

        }, 1000);
    });

    function whyICannotDownload(videos_id) {
        avideoAlertAJAXHTML(webSiteRootURL + "view/downloadChecker.php?videos_id=" + videos_id);
    }

    function toggleVideoStatus(t) {
        var row_index = $(t).closest('tr').index();
        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
        var nextStatus = $(t).attr('nextStatus');
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'objects/videoStatus.json.php',
            data: {
                "id": row.id,
                "status": nextStatus
            },
            type: 'post',
            success: function(response) {
                $("#grid").bootgrid("reload");
                modal.hidePleaseWait();
            }
        });
    }

    var playlistsFromUserVideosListToRequest = [];
    var playlistsFromUserVideosListToRequestTimetout;

    function playlistsFromUserVideos(videos_id) {
        playlistsFromUserVideosListToRequest.push(videos_id);
        clearTimeout(playlistsFromUserVideosListToRequestTimetout);
        playlistsFromUserVideosListToRequestTimetout = setTimeout(function() {
            videos_ids_list = getUniqueValuesFromArray(playlistsFromUserVideosListToRequest);
            playlistsFromUserVideosListToRequest = [];
            //console.log('playlistsFromUserVideos', videos_ids_list);

            $.ajax({
                url: webSiteRootURL + 'objects/playlistsFromUserVideos.json.php',
                data: {
                    "users_id": <?php echo User::getId(); ?>,
                    "videos_id": videos_ids_list,
                },
                type: 'post',
                success: function(response) {
                    var lists = '';
                    for (var x in response) {
                        if (typeof response[x] !== 'object') {
                            continue;
                        }

                        var videoResponse = response[x];
                        v_id = videoResponse.videos_id;
                        playlists = videoResponse.playlists;

                        //console.log('playlistsFromUserVideos playlists', playlists);
                        for (var y in playlists) {
                            if (typeof playlists[y] !== 'object') {
                                continue;
                            }

                            lists += '<div class="material-small material-switch"><input onchange="saveVideoOnPlaylist(' + v_id + ', $(this).is(\':checked\'), ' +
                                playlists[y].id + ')" data-toggle="toggle" type="checkbox" id="playlistVideo' +
                                v_id + "_" + playlists[y].id + '" value="1" ' +
                                (playlists[y].isOnPlaylist ? "checked" : "") + ' videos_id="' + v_id + '" ><label for="playlistVideo' +
                                v_id + "_" +
                                playlists[y].id + '" class="label-primary"></label>  ' +
                                playlists[y].name_translated + '</div>';
                        }
                        //console.log('playlistsFromUserVideos videoPlaylist' + v_id, lists);
                        $('#videoPlaylist' + v_id).html(lists);
                        lists = '';
                    }
                }
            });

        }, 500);
    }
</script>
