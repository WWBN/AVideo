<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/Channel.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
require_once $global['systemRootPath'] . 'objects/functionInfiniteScroll.php';

if (isset($_SESSION['channelName'])) {
    _session_start();
    unset($_SESSION['channelName']);
}

$user_groups_id = intval(@$_REQUEST['user_groups_id']);

// Get user group name if user_groups_id is provided
$userGroupName = '';
if (!empty($user_groups_id)) {
    $userGroup = new UserGroups($user_groups_id);
    $userGroupName = $userGroup->getGroup_name();
}

$totalChannels = Channel::getTotalChannels(true, $user_groups_id);

$users_id_array = VideoStatistic::getUsersIDFromChannelsWithMoreViews();

$_REQUEST['rowCount'] = 10;
$channels = Channel::getChannels(true, "u.id, '" . implode(",", $users_id_array) . "'", [], $user_groups_id);

$totalPages = ceil($totalChannels / $_REQUEST['rowCount']);
//var_dump($channels, $totalPages, $totalChannels, $_REQUEST['rowCount']);exit;
$_page = new Page(array('Channels'));
$_page->setExtraStyles(
    array(
        'plugin/Gallery/style.css'
    )
);
?>
<style>
    #custom-search-input {
        padding: 3px;
        border: solid 1px #E4E4E4;
        border-radius: 6px;
        background-color: #fff;
    }

    #custom-search-input input {
        border: 0;
        box-shadow: none;
    }

    #custom-search-input button {
        margin: 2px 0 0 0;
        background: none;
        box-shadow: none;
        border: 0;
        color: #666666;
        padding: 0 8px 0 10px;
        border-left: solid 1px #ccc;
    }

    #custom-search-input button:hover {
        border: 0;
        box-shadow: none;
        border-left: solid 1px #ccc;
    }

    #custom-search-input .glyphicon-search {
        font-size: 23px;
    }
</style>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php if (!empty($user_groups_id) && !empty($userGroupName)): ?>
                <h3 class="panel-title" style="margin-bottom: 10px;">
                    <i class="fa fa-users"></i> <strong><?php echo htmlspecialchars($userGroupName); ?></strong>
                </h3>
            <?php endif; ?>
            <form id="search-form" name="search-form" action="<?php echo $global['webSiteRootURL']; ?>channels" method="get">
                <div id="custom-search-input">
                    <div class="input-group col-md-12">
                        <input type="search" name="searchPhrase" class="form-control input-lg" placeholder="<?php echo __("Search Channels"); ?>"
                        value="<?php echo @htmlentities(@$_GET['searchPhrase']); unsetSearch(); ?>" />
                        <input type="hidden" name="user_groups_id" value="<?php echo $user_groups_id; ?>" />
                        <span class="input-group-btn">
                            <button class="btn btn-info btn-lg" type="submit">
                                <i class="glyphicon glyphicon-search"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </form>
        </div>
        <div class="panel-body">
            <?php
            foreach ($channels as $value) {
                User::getChannelPanel($value['id']);
            }

            echo getPagination($totalPages, "{$global['webSiteRootURL']}channels?user_groups_id={$user_groups_id}&page=_pageNum_");
            ?>
        </div>
    </div>
</div>

<?php
$_page->print();
?>
