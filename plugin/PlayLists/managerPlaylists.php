<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
$maxItemsInPlaylist = 5;
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isLogged()) {
    gotToLoginAndComeBackHere('');
}
_session_write_close();
$global['laodPlaylistScript'] = 1;
function getPlaylistOwnerUsersId()
{
    if (!empty($_REQUEST['PlaylistOwnerUsersId'])) {
        return intval($_REQUEST['PlaylistOwnerUsersId']);
    } else if (User::isAdmin() && !empty($_REQUEST['users_id'])) {
        return intval($_REQUEST['users_id']);
    }
    return User::getId();
}
$timeName = "managerPlaylists.php";
TimeLogStart($timeName);
$users_id = getPlaylistOwnerUsersId();
TimeLogEnd($timeName, __LINE__);
require_once $global['systemRootPath'] . 'objects/functionInfiniteScroll.php';
$infinityScrollGetFromSelector = 'managerPlaylists';
setRowCount(8);
setDefaultSort('created', 'DESC');

$pl = PlayList::getAllFromUser($users_id, false);
//var_dump(count($pl));exit;
$total = PlayList::getTotalFromUser($users_id, false);
$totalPages = ceil($total / getRowCount());

$_page = new Page(array('Manage playlist'));
?>
<link rel="stylesheet" href="<?php echo getURL('plugin/PlayLists/managerPlaylists.css'); ?>" />
<div class="container-fluid playlistManager">
    <div class="panel panel-default">
        <div class="panel-heading">
            <ul class="nav nav-pills playlistManagerToolbar">
                <li class="active pl_filter" onclick="pl_filter('all', $(this)); return false;" data-toggle="tooltip" title="<?php echo __('Show all types'); ?>">
                    <a href="#"><i class="fas fa-layer-group"></i>
                        <i class="fas fa-list"></i>
                        <i class="fas fa-film"></i> <?php echo __('All'); ?></a>
                </li>
                <li class="pl_filter" onclick="pl_filter('serie', $(this)); return false;" data-toggle="tooltip" title="<?php echo __('Show all programs that are listed in your video library'); ?>">
                    <a href="#"><span class="label label-success"><i class="fas fa-list"></i>
                            <?php echo __('Series'); ?></span></a>
                </li>
                <li class="pl_filter" onclick="pl_filter('collection', $(this)); return false;" data-toggle="tooltip" title="<?php echo __('Show all that is a collection of programs'); ?>">
                    <a href="#"><span class="label label-primary"><i class="fas fa-layer-group"></i>
                            <?php echo __('Collections'); ?></span></a>
                </li>
                <li class="pl_filter" onclick="pl_filter('videos', $(this)); return false;" data-toggle="tooltip" title="<?php echo __('Show all that include a list of videos'); ?>">
                    <a href="#">
                        <span class="label label-default">
                            <i class="fas fa-film"></i>
                            <?php echo __('Videos'); ?>
                        </span>
                    </a>
                </li>
                <li class="pull-right">
                    <button type="button" class="btn btn-default pull-right" data-toggle="tooltip" title="<?php echo __('New'); ?>" onclick="createNewProgram();">
                        <i class="fas fa-plus"></i>
                    </button>
                </li>
                <?php
                $p = AVideoPlugin::loadPluginIfEnabled('VideoPlaylistScheduler');
                if (!empty($p) && VideoPlaylistScheduler::canUseCalendar()) {
                ?>
                    <li>
                        <button onclick="avideoModalIframeLarge(webSiteRootURL+'plugin/VideoPlaylistScheduler/calendar.php')" class="btn btn-default">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo __('Schedule to play live'); ?>
                        </button>
                    </li>
                <?php
                }
                TimeLogEnd($timeName, __LINE__);
                if (PlayLists::canManageAllPlaylists()) {
                    TimeLogEnd($timeName, __LINE__);
                ?>
                    <li class="pull-right">
                        <?php
                        $autocomplete = Layout::getUserAutocomplete(getPlaylistOwnerUsersId(), 'User_playlist_owner', array(), 'updatePlaylistOwner()');
                        ?>
                    </li>
                <?php
                    TimeLogEnd($timeName, __LINE__);
                }
                TimeLogEnd($timeName, __LINE__);
                $PlaylistOwnerUsersId = $users_id;
                ?>
                <li class="pull-right ">
                    <form class="navbar-form form-inline input-group" role="search" id="searchFormPlaylist" method="get">
                        <input type="search" id="searchPlaylist" name="searchPlaylist" placeholder="<?php echo __('Search Playlist'); ?>" class="form-control" value="<?php echo htmlspecialchars($_REQUEST['searchPlaylist'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
                        <input type="hidden" name="PlaylistOwnerUsersId" value="<?php echo $PlaylistOwnerUsersId; ?>">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit" id="buttonSearchPlaylist">
                                <i class="fas fa-search faa-shake"></i>
                            </button>
                        </span>
                    </form>
                </li>
            </ul>
        </div>
        <div class="panel-body">
            <div id="<?php echo $infinityScrollGetFromSelector; ?>">
                <div class="row playlistManagerGrid">
                    <?php
                    TimeLogEnd($timeName, __LINE__);
                    //var_dump($total);exit;
                    $count = 0;
                    TimeLogEnd($timeName, __LINE__);
                    foreach ($pl as $value) {
                        $count++;
                        //var_dump($value);

                        $rowsSubPlaylists = PlayList::getAllSubPlayLists($value["id"]);
                        if (empty($rowsSubPlaylists)) {
                            $rowsSubPlaylists = array();
                        }
                        $totalSubPlaylists = count($rowsSubPlaylists);


                        $rowsNOTSubPlaylists = PlayList::getAllNOTSubPlayLists($value["id"]);
                        $totalNOTSubPlaylists = count($rowsNOTSubPlaylists);

                        $resp = PlayList::getTotalDurationAndTotalVideosFromPlaylist($value["id"]);
                        $durationInSeconds = $resp['duration_in_seconds'];
                        $totalVideos = $resp['totalVideos'];

                        $classes = array();
                        $isASerie = PlayLists::isPlayListASerie($value["id"]);
                        if ($isASerie) {
                            $classes[] = 'pl_serie';
                        }
                        if ($totalSubPlaylists) {
                            $classes[] = 'pl_collection';
                        }
                        if ($totalNOTSubPlaylists) {
                            $classes[] = 'pl_videos';
                        }
                    ?>
                        <div class="col-sm-6 col-md-4 col-lg-3 pl pl<?php echo $value["id"]; ?> <?php echo implode(' ', $classes) ?>">
                            <div class="panel panel-<?php echo $totalSubPlaylists ? 'primary' : 'default'; ?>">
                                <div class="panel-heading clearfix playlistCardHeading">
                                    <div class="playlistCardTitle">
                                    <?php
                                    echo "[{$value["id"]}] ";
                                    if (!empty($totalSubPlaylists)) {
                                        echo '<i class="fas fa-layer-group"></i> ';
                                    } else {
                                        echo '<i class="fas fa-film"></i> ';
                                    }
                                    echo $value['name_translated'];
                                    ?>

                                    </div>
                                    <div class="btn-group pull-right playlistCardActions" playlists_id="<?php echo $value["id"]; ?>">
                                        <button type="button" class="btn btn-default btn-xs pull-right" data-toggle="tooltip" title="<?php echo __('Delete'); ?>" onclick="deleteProgram(<?php echo $value["id"]; ?>);">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button type="button" class="btn btn-default btn-xs pull-right" data-toggle="tooltip" title="<?php echo __('Play'); ?>" onclick="avideoModalIframe('<?php echo PlayLists::getLink($value["id"], true); ?>');">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <button type="button" class="btn btn-default btn-xs editBtn " onclick="editPlayList(<?php echo $value['id']; ?>);" data-toggle="tooltip" title="<?php echo __('Edit'); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-default btn-xs cloneBtn " onclick="clonePlayList(<?php echo $value['id']; ?>);" data-toggle="tooltip" title="<?php echo __('Clone'); ?>">
                                            <i class="fa-regular fa-clone"></i>
                                        </button>
                                        <?php
                                        echo PlayLists::scheduleLiveButton($value['id'], false);
                                        ?>
                                    </div>

                                </div>
                                <div class="panel-body playLists">

                                    <ul class="nav nav-pills">
                                        <?php
                                        $active = 'active';
                                        if (!empty($totalSubPlaylists)) {
                                        ?>
                                            <li class="<?php echo $active; ?>">
                                                <a data-toggle="tab" href="#seasons<?php echo $value["id"]; ?>"><i class="fas fa-list"></i> <?php echo __('Seasons'); ?>
                                                    <span class="badge playlistItemCount">
                                                        <?php
                                                        echo $totalSubPlaylists;
                                                        ?>
                                                    </span>
                                                </a>
                                            </li>
                                        <?php
                                            $active = '';
                                        }
                                        if (!empty($totalNOTSubPlaylists)) {
                                        ?>
                                            <li class="<?php echo $active; ?>">
                                                <a data-toggle="tab" href="#videos<?php echo $value["id"]; ?>"><i class="fas fa-video"></i> <?php echo __('Videos'); ?>
                                                    <span class="badge playlistItemCount">
                                                        <?php
                                                        echo $totalNOTSubPlaylists;
                                                        ?>
                                                    </span>
                                                </a>
                                            </li>
                                        <?php
                                            $active = '';
                                        }
                                        ?>
                                    </ul>

                                    <div class="tab-content">
                                        <?php
                                        $active = ' in active';
                                        if (!empty($totalSubPlaylists)) {
                                        ?>
                                            <div id="seasons<?php echo $value["id"]; ?>" class="tab-pane fade <?php echo $active; ?>">
                                                <ul class="list-group">
                                                    <?php
                                                    if ($totalSubPlaylists > 0) {
                                                        $countItemsInPlaylist = 0;
                                                        foreach ($rowsSubPlaylists as $row) {
                                                            $countItemsInPlaylist++;
                                                            if ($countItemsInPlaylist > $maxItemsInPlaylist) {
                                                    ?>
                                                                <li class="list-group-item">
                                                                    <button type="button" class="btn btn-default btn-xs btn-block " onclick="editPlayList(<?php echo $value['id']; ?>);" data-toggle="tooltip" title="<?php echo __('Edit'); ?>">
                                                                        <?php
                                                                        echo __('More');
                                                                        ?>
                                                                        <i class="fas fa-ellipsis-h"></i>
                                                                    </button>
                                                                </li>
                                                            <?php
                                                                break;
                                                            }
                                                            ?>
                                                            <li class="list-group-item" id="videos_id_<?php echo $row["id"]; ?>_playlists_id_<?php echo $value["id"]; ?>">
                                                                <div class="ellipsis videoTitle">
                                                                    <?php
                                                                    echo $row['title'];
                                                                    ?>
                                                                </div>
                                                                <button type="button" class="btn btn-default btn-xs pull-right" data-toggle="tooltip" title="<?php echo __('Remove Serie'); ?>" onclick="removeFromSerie(<?php echo $value["id"]; ?>, <?php echo $row["id"]; ?>);">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-default btn-xs pull-right" data-toggle="tooltip" title="<?php echo __('Play Video'); ?>" onclick="avideoModalIframe('<?php echo Video::getPermaLink($row["id"], true); ?>');">
                                                                    <i class="fas fa-play"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-default btn-xs pull-right" data-toggle="tooltip" title="<?php echo __('Edit Video'); ?>" onclick="avideoModalIframe(webSiteRootURL+'view/managerVideosLight.php?avideoIframe=1&videos_id=<?php echo $row['id']; ?>');">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </li>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        <?php
                                            $active = '';
                                        }
                                        if (!empty($totalNOTSubPlaylists)) {
                                        ?>
                                            <div id="videos<?php echo $value["id"]; ?>" class="tab-pane fade <?php echo $active; ?>">
                                                <ul class="list-group">
                                                    <?php
                                                    $countItemsInPlaylist = 0;
                                                    foreach ($rowsNOTSubPlaylists as $row) {
                                                        $countItemsInPlaylist++;
                                                        if ($countItemsInPlaylist > $maxItemsInPlaylist) {
                                                    ?>
                                                            <li class="list-group-item">
                                                                <button type="button" class="btn btn-default btn-xs btn-block " onclick="editPlayList(<?php echo $value['id']; ?>);" data-toggle="tooltip" title="<?php echo __('Edit'); ?>">
                                                                    <?php
                                                                    echo __('More');
                                                                    ?>
                                                                    <i class="fas fa-ellipsis-h"></i>
                                                                </button>
                                                            </li>
                                                        <?php
                                                            break;
                                                        }
                                                        if ($totalNOTSubPlaylists > 0) {
                                                        ?>
                                                            <li class="list-group-item" id="videos_id_<?php echo $row["id"]; ?>_playlists_id_<?php echo $value["id"]; ?>">
                                                                <div class="ellipsis videoTitle">
                                                                    <?php
                                                                    echo $row['title'];
                                                                    ?>
                                                                </div>
                                                                <button type="button" class="btn btn-default btn-xs pull-right" data-toggle="tooltip" title="<?php echo __('Remove Video'); ?>" onclick="removeFromSerie(<?php echo $value["id"]; ?>, <?php echo $row["id"]; ?>);">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-default btn-xs pull-right" data-toggle="tooltip" title="<?php echo __('Play Video'); ?>" onclick="avideoModalIframe('<?php echo Video::getPermaLink($row["id"], true); ?>');">
                                                                    <i class="fas fa-play"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-default btn-xs pull-right" data-toggle="tooltip" title="<?php echo __('Edit Video'); ?>" onclick="avideoModalIframe(webSiteRootURL+'view/managerVideosLight.php?avideoIframe=1&videos_id=<?php echo $row['id']; ?>');">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </li>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        <?php
                                            $active = '';
                                        }
                                        ?>
                                    </div>


                                </div>
                                <div class="panel-footer text-right">
                                    <?php
                                    if ($isASerie) {
                                        echo '<div class="pull-left" style="margin-right:5px;">' . Video::getChangeVideoStatusButton($isASerie['id']) . '</div> ';
                                        echo '<span class="label label-success"><i class="fas fa-list"></i> ' . $isASerie['title'];
                                        echo '</span>';
                                    }
                                    if ($totalSubPlaylists) {
                                        echo '<span class="label label-primary"><i class="fas fa-layer-group"></i> ' . __('Collections') . '</span>';
                                    }
                                    if ($totalNOTSubPlaylists) {
                                        echo '<span class="label label-default"><i class="fas fa-film"></i> ' . __('Videos') . '</span>';
                                    }
                                    ?>
                                    <span class="label label-default">
                                        <i class="far fa-clock"></i> <?php echo seconds2human($durationInSeconds); ?>
                                    </span>
                                    <span class="label label-default">
                                        <i class="fa-solid fa-layer-group"></i> <?php echo $totalVideos; ?> <?php echo __('total videos'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php
                        if ($count % 2 == 0) {
                            echo '<div class="clearfix visible-sm"></div>';
                        }
                        if ($count % 3 == 0) {
                            echo '<div class="clearfix visible-md"></div>';
                        }
                        if ($count % 4 == 0) {
                            echo '<div class="clearfix visible-lg"></div>';
                        }
                    }
                    if (empty($count)) {
                        if (getCurrentPage() <= 1) {
                        ?>
                            <div class="col-sm-12">
                                <div class="alert alert-info">
                                    <?php echo __('Sorry you do not have any playlist yet'); ?>
                                </div>
                            </div>
                    <?php
                        }
                    }
                    TimeLogEnd($timeName, __LINE__);
                    ?>

                </div>
            </div>
            <?php

            $url = "{$global['webSiteRootURL']}plugin/PlayLists/managerPlaylists.php";
            $url = addQueryStringParameter($url, 'PlaylistOwnerUsersId', $users_id);
            if (!empty($_REQUEST['searchPlaylist'])) {
                $url = addQueryStringParameter($url, 'searchPlaylist', $_REQUEST['searchPlaylist']);
            }
            echo getPagination($totalPages, $url, 10, "#{$infinityScrollGetFromSelector}", "#{$infinityScrollGetFromSelector}");
            echo getPagination($totalPages, $url, 10);
            ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var playlistResults = document.getElementById('managerPlaylists');
        if (playlistResults) {
            new MutationObserver(function() {
                applyPlaylistFilter();
            }).observe(playlistResults, {childList: true, subtree: true});
        }
        $("#searchFormPlaylist").submit(function(event) {
            modal.showPleaseWait();
            var searchInput = $("#searchPlaylist").val();
            if (empty(searchInput)) {
                // Prevent the form from submitting
                //event.preventDefault();
            }
        });
    });


    function updatePlaylistOwner() {
        modal.showPleaseWait();
        var url = window.location.href;
        url = addQueryStringParameter(url, 'PlaylistOwnerUsersId', $('#User_playlist_owner').val());
        url = addQueryStringParameter(url, 'current', 1);
        url = addQueryStringParameter(url, 'page', 1);
        window.location.href = url;
    }

    var activePlaylistFilter = 'all';
    function pl_filter(filter, t) {
        $('.pl_filter').removeClass('active');
        t.addClass('active');
        activePlaylistFilter = filter;
        applyPlaylistFilter();
    }

    function applyPlaylistFilter() {
        var cards = $('#managerPlaylists .pl');
        if (activePlaylistFilter === 'all') {
            cards.show();
        } else {
            var selector = '.pl_' + activePlaylistFilter;
            cards.filter(selector).show();
            cards.not(selector).hide();
        }
    }

    function editPlayList(playlists_id) {
        avideoModalIframe(webSiteRootURL + 'viewProgram/' + playlists_id);
    }

    function clonePlayList(playlists_id) {
        var url = 'plugin/PlayLists/clone.json.php';
        var data = {
            "playlists_id": playlists_id
        };
        var pleaseWait = true;
        var returnFunction = function(response) {
            console.log('returnFunction', response);
            if (!response.error) {
                avideoToastSuccess(__('Playlist cloned') + ' #' + response.new_playlist_id);
                editPlayList(response.new_playlist_id);
            }
        };
        avideoAjaxWithResponse(url, data, pleaseWait, returnFunction);
    }

    function removeFromSerie(playlists_id, videos_id) {
        swal({
                title: "<?php echo __('Are you sure?'); ?>",
                text: "<?php echo __('You will not be able to recover this action!'); ?>",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then(function(willDelete) {
                if (willDelete) {
                    addVideoToPlayList(videos_id, false, playlists_id).done(function(response) {
                        if (!response.error) {
                            location.reload();
                        }
                    });
                } else {

                }
            });
    }

    function deleteProgram(playlists_id) {
        swal({
                title: "<?php echo __('Are you sure?'); ?>",
                text: "<?php echo __('You will not be able to recover this action!'); ?>",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then(function(willDelete) {
                if (willDelete) {
                    modal.showPleaseWait();
                    $.ajax({
                        url: webSiteRootURL + 'objects/playlistRemove.php',
                        data: {
                            "playlist_id": playlists_id,
                            "globalToken": '<?php echo getToken(300); ?>'
                        },
                        type: 'post',
                        success: function(response) {
                            if (response && Number(response.status) > 0) {
                                $('.pl' + playlists_id).remove();
                                avideoToastSuccess(__('Deleted'));
                            } else {
                                avideoToastError(__('An error occurred'));
                            }
                        },
                        error: function() {
                            avideoToastError(__('An error occurred'));
                        },
                        complete: function() {
                            modal.hidePleaseWait();
                        }
                    });
                } else {

                }
            });
    }

    var createNewProgramIsEditing = false;

    function createNewProgram() {
        if (createNewProgramIsEditing) {
            return false;
        }
        createNewProgramIsEditing = true;
        swal({
            title: "<?php echo __('New program'); ?>",
            text: "<?php echo __('Type your program title'); ?>",
            content: {
                element: "input",
                attributes: {
                    placeholder: "<?php echo __('Program title'); ?>",
                    type: "text",
                },
            },
            buttons: true
        }).then((inputValue) => {
            if (typeof inputValue !== 'string') {
                createNewProgramIsEditing = false;
                return;
            }
            inputValue = inputValue.trim();
            if (!inputValue) {
                createNewProgramIsEditing = false;
                avideoToastError(__('Please provide a title'));
                return;
            }
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'objects/playlistAddNew.json.php',
                method: 'POST',
                data: {
                    'status': "public",
                    'name': inputValue,
                    'users_id': '<?php echo $users_id; ?>'
                },
                success: function(response) {
                    if (response.status > 0) {
                        location.reload();
                    } else {
                        avideoToastError(__('An error occurred'));
                    }
                },
                error: function() {
                    avideoToastError(__('An error occurred'));
                },
                complete: function() {
                    modal.hidePleaseWait();
                    createNewProgramIsEditing = false;
                }
            });
        });
    }
</script>
<?php
$_page->print();
?>
