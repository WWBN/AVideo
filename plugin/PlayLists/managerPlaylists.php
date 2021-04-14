<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isLogged()) {
    gotToLoginAndComeBackHere('');
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Users") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        include $global['systemRootPath'] . 'view/managerUsers_head.php';
        ?>
        <style>
            .playLists li{
                min-height: 45px;
            }
            .playLists .list-group{
                height: 221px;
                overflow: auto;
            }

            .videoTitle.ellipsis{
                width: calc(100% - 90px);
                float: left;
            }

            .playLists .tab-content{
                min-height: 250px;
            }

            .playLists {
                min-height: 330px;
            }
            .pl .panel-footer{
                min-height: 42px;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <br>

            <div class="panel">
                <div class="panel-heading">
                    <ul class="nav nav-tabs">
                        <li class="active pl_filter" onclick="pl_filter('all', $(this));" data-toggle="tooltip" 
                            title="<?php echo __('Show all types'); ?>">
                            <a href="#"><i class="fas fa-layer-group"></i> 
                                <i class="fas fa-list"></i> 
                                <i class="fas fa-film"></i> <?php echo __('All'); ?></a></li>
                        <li class="pl_filter" onclick="pl_filter('serie', $(this));" data-toggle="tooltip" 
                            title="<?php echo __('Show all programs that are listed in your video library'); ?>">
                            <a href="#"><span class="label label-success"><i class="fas fa-list"></i> 
                                    <?php echo __('Series'); ?></span></a></li>
                        <li class="pl_filter" onclick="pl_filter('collection', $(this));" data-toggle="tooltip" 
                            title="<?php echo __('Show all that is a collection of programs'); ?>">
                            <a href="#"><span class="label label-primary"><i class="fas fa-layer-group"></i> 
                                    <?php echo __('Collections'); ?></span></a></li>
                        <li class="pl_filter" onclick="pl_filter('videos', $(this));" data-toggle="tooltip" 
                            title="<?php echo __('Show all that include a list of videos'); ?>">
                            <a href="#"><span class="label label-default"><i class="fas fa-film"></i> 
                                    <?php echo __('Videos'); ?></span></a></li>
                        <li class="pull-right" >
                            <button type="button" class="btn btn-default pull-right"   data-toggle="tooltip" title="<?php echo __('New'); ?>"  
                                    onclick="createNewProgram();" >
                                <i class="fas fa-plus"></i>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <?php
                        $pl = PlayList::getAllFromUser(User::getId(), false);
                        $count = 0;
                        foreach ($pl as $value) {
                            $count++;
                            //var_dump($value);

                            $rowsSubPlaylists = PlayList::getAllSubPlayLists($value["id"]);
                            $totalSubPlaylists = count($rowsSubPlaylists);


                            $rowsNOTSubPlaylists = PlayList::getAllNOTSubPlayLists($value["id"]);
                            $totalNOTSubPlaylists = count($rowsNOTSubPlaylists);

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
                            <div class="col-sm-6 col-md-4 col-lg-3 pl pl<?php echo $value["id"]; ?> <?php echo implode(' ', $classes) ?>" >
                                <div class="panel panel-<?php echo $totalSubPlaylists ? 'primary' : 'default'; ?>">
                                    <div class="panel-heading">
                                        <?php
                                        if (!empty($totalSubPlaylists)) {
                                            echo '<i class="fas fa-layer-group"></i> ';
                                        } else {
                                            echo '<i class="fas fa-film"></i> ';
                                        }
                                        echo $value['name_translated'];
                                        ?>

                                        <div class="btn-group pull-right" playlists_id="<?php echo $value["id"]; ?>">
                                            <button type="button" class="btn btn-default btn-xs pull-right"  data-toggle="tooltip" 
                                                    title="<?php echo __('Delete'); ?>" 
                                                    onclick="deleteProgram(<?php echo $value["id"]; ?>);" >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button type="button" class="btn btn-default btn-xs pull-right"   data-toggle="tooltip" 
                                                    title="<?php echo __('Play'); ?>"  onclick="avideoModalIframe('<?php echo PlayLists::getLink($value["id"], true); ?>');" >
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button type="button" class="btn btn-default btn-xs editBtn " onclick="editPlayList(<?php echo $value["id"]; ?>);" data-toggle="tooltip" 
                                                    title="<?php echo __('Edit'); ?>" >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>

                                    </div>
                                    <div class="panel-body playLists">

                                        <ul class="nav nav-tabs">
                                            <?php
                                            $active = 'active';
                                            if (!empty($totalSubPlaylists)) {
                                                ?>
                                                <li class="<?php echo $active; ?>">
                                                    <a data-toggle="tab" href="#seasons<?php echo $value["id"]; ?>"><i class="fas fa-list"></i> <?php echo __('Seasons'); ?> 
                                                        <span class="badge" id="badge_playlists_id_<?php echo $value["id"]; ?>">
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
                                                        <span class="badge" id="badge_playlists_id_<?php echo $value["id"]; ?>">
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
                                                            foreach ($rowsSubPlaylists as $row) {
                                                                ?>
                                                                <li class="list-group-item" id="videos_id_<?php echo $row["id"]; ?>_playlists_id_<?php echo $value["id"]; ?>">
                                                                    <div class="ellipsis videoTitle">
                                                                        <?php
                                                                        echo $row['title'];
                                                                        ?>
                                                                    </div>
                                                                    <button type="button" class="btn btn-default btn-xs pull-right"  data-toggle="tooltip" title="<?php echo __('Remove Serie'); ?>" onclick="removeFromSerie(<?php echo $value["id"]; ?>, <?php echo $row["id"]; ?>);" >
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-default btn-xs pull-right"   data-toggle="tooltip" title="<?php echo __('Play Video'); ?>"  onclick="avideoModalIframe('<?php echo Video::getPermaLink($row["id"], true); ?>');" >
                                                                        <i class="fas fa-play"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-default btn-xs pull-right"   data-toggle="tooltip" title="<?php echo __('Edit Video'); ?>"  onclick="avideoModalIframe(webSiteRootURL + 'mvideos?video_id=<?php echo $row["id"]; ?>');" >
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
                                                        foreach ($rowsNOTSubPlaylists as $row) {
                                                            if ($totalNOTSubPlaylists > 0) {
                                                                ?>
                                                                <li class="list-group-item" id="videos_id_<?php echo $row["id"]; ?>_playlists_id_<?php echo $value["id"]; ?>">
                                                                    <div class="ellipsis videoTitle">
                                                                        <?php
                                                                        echo $row['title'];
                                                                        ?>
                                                                    </div>
                                                                    <button type="button" class="btn btn-default btn-xs pull-right"   data-toggle="tooltip" title="<?php echo __('Remove Video'); ?>"  onclick="removeFromSerie(<?php echo $value["id"]; ?>, <?php echo $row["id"]; ?>);" >
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-default btn-xs pull-right"   data-toggle="tooltip" title="<?php echo __('Play Video'); ?>"  onclick="avideoModalIframe('<?php echo Video::getPermaLink($row["id"], true); ?>');" >
                                                                        <i class="fas fa-play"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-default btn-xs pull-right"   data-toggle="tooltip" title="<?php echo __('Edit Video'); ?>"  onclick="avideoModalIframe(webSiteRootURL + 'mvideos?video_id=<?php echo $row["id"]; ?>');" >
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
                                            echo '<div class="pull-left" style="margin-right:5px;">'.Video::getChangeVideoStatusButton($isASerie['id']).'</div> ';
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
                                    </div>
                                </div>   
                            </div> 
                            <?php
                        }
                        if (empty($count)) {
                            ?>
                            <div class="col-sm-12">
                                <div class="alert alert-info">
                                    <?php echo __('Sorry you do not have any playlist yet'); ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {


            });

            function pl_filter(filter, t) {
                $('.pl_filter').removeClass('active');
                t.addClass('active');
                if (filter === 'all') {
                    $('.pl').show();
                } else {
                    var selector = '.pl_' + filter;
                    $(selector).show();
                    $('.pl').not(selector).hide();
                }
            }

            function editPlayList(playlists_id) {
                avideoModalIframe(webSiteRootURL + 'viewProgram/' + playlists_id);
            }

            function removeFromSerie(playlists_id, videos_id) {
                swal({
                    title: "<?php echo __('Are you sure?'); ?>",
                    text: "<?php echo __('You will not be able to recover this action!'); ?>",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                        .then(function (willDelete) {
                            if (willDelete) {
                                addVideoToPlayList(videos_id, false, playlists_id);
                                $('#videos_id_' + videos_id + '_playlists_id_' + playlists_id).fadeOut();
                                $('#badge_playlists_id_' + playlists_id).text(parseInt($('#badge_playlists_id_' + playlists_id).text()) - 1);
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
                        .then(function (willDelete) {
                            if (willDelete) {
                                modal.showPleaseWait();
                                $.ajax({
                                    url: webSiteRootURL + 'objects/playlistRemove.php',
                                    data: {
                                        "playlist_id": playlists_id
                                    },
                                    type: 'post',
                                    success: function (response) {
                                        $('.pl' + playlists_id).fadeOut();
                                        modal.hidePleaseWait();
                                        avideoToastSuccess('<?php echo __('Deleted'); ?>');
                                    }
                                });
                            } else {

                            }
                        });
            }


            function createNewProgram() {
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
                    showCancelButton: true,
                    closeOnConfirm: true,
                    inputPlaceholder: "<?php echo __('Program title'); ?>"
                }).then((inputValue) => {
                    if (inputValue === false)
                        return false;

                    if (inputValue === "") {
                        swal.showInputError("<?php echo __('Please provide a title'); ?>");
                        return false
                    }

                    $.ajax({
                        url: webSiteRootURL + 'objects/playlistAddNew.json.php',
                        method: 'POST',
                        data: {
                            'status': "public",
                            'name': inputValue
                        },
                        success: function (response) {
                            if (response.status > 0) {
                                location.reload();
                            } else {
                                modal.hidePleaseWait();
                            }
                        }
                    });
                });
            }
        </script>
    </body>
</html>
