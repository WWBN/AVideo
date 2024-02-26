
<style>
    .PLLoadMore{
        display: none;
    }
    .hasMore .PLLoadMore{
        display: block;
    }
</style>
<div class="modal fade" id="videoSearchModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">

                <div class="panel panle-default">
                    <div class="panel-heading">
                        <ul class="nav nav-tabs">
                            <li class="active"><a data-toggle="tab" href="#addSeries"><i class="fas fa-list"></i> <?php echo __('Series'); ?></a></li>
                            <li><a data-toggle="tab" href="#addVideos"><i class="fas fa-video"></i> <?php echo __('Videos'); ?></a></li>
                        </ul>
                    </div>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div id="addSeries" class="tab-pane fade in active">
                                <form id="serieSearch-form" name="search-form" action="<?php echo $global['webSiteRootURL'] . ''; ?>" method="get">
                                    <div id="custom-search-input">
                                        <div class="input-group col-md-12">
                                            <input type="search" name="searchPhrase" id="serieSearch-input" class="form-control input-lg" placeholder="<?php echo __('Search Serie'); ?>" value="">
                                            <span class="input-group-btn">
                                                <button class="btn btn-info btn-lg" type="submit">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </form>
                                <hr>
                                <div id="searchSerieResult" class="">

                                </div>
                                <button class="btn btn-xs btn-primary btn-block PLLoadMore" onclick="videoSearchLoadMore();"><?php echo __('Load More'); ?>...</button>
                            </div>
                            <div id="addVideos" class="tab-pane fade">
                                <form id="videoSearch-form" name="search-form" action="<?php echo $global['webSiteRootURL'] . ''; ?>" method="get">
                                    <div id="custom-search-input">
                                        <div class="input-group col-md-12">
                                            <input type="search" name="searchPhrase" id="videoSearch-input" class="form-control input-lg" placeholder="<?php echo __('Search Videos'); ?>" value="">
                                            <span class="input-group-btn">
                                                <button class="btn btn-info btn-lg" type="submit">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </form>
                                <hr>
                                <div id="searchVideoResult">

                                </div>
                                <button class="btn btn-xs btn-primary btn-block PLLoadMore" onclick="videoSearchLoadMore();"><?php echo __('Load More'); ?>...</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var currentSerieVideos_id = 0;
    var videoWasAdded = false;
    var current_is_serie;
    var current_current;

    function openVideoSearch(videos_id) {
        currentSerieVideos_id = videos_id;
        $('#videoSearchModal').modal();
    }

    function videoSearchLoadMore() {
        videoSearch(current_is_serie, current_current + 1);
    }

    function videoSearch(is_serie, current) {
        modal.showPleaseWait();
        current_is_serie = is_serie;
        current_current = current;
        var searchPhrase = $('#videoSearch-input').val();
        if (is_serie) {
            searchPhrase = $('#serieSearch-input').val();
        }
        var url = webSiteRootURL + 'plugin/API/get.json.php';
        url = addQueryStringParameter(url, 'APIName', 'video');
        url = addQueryStringParameter(url, 'rowCount', 10);
        url = addQueryStringParameter(url, 'current', current);
        url = addQueryStringParameter(url, 'is_serie', is_serie);
        url = addQueryStringParameter(url, 'searchPhrase', searchPhrase);
        $.ajax({
            url: url,
            success: function (response) {
                console.log(response);
                var resultId = '#searchVideoResult';
                if (is_serie) {
                    resultId = '#searchSerieResult';
                }
                if (current <= 1) {
                    $(resultId).empty();
                }
                var rows = response.response.rows;
                if(response.response.hasMore){
                    $(resultId).parent().addClass('hasMore');
                }else{
                    $(resultId).parent().removeClass('hasMore');
                }
                for (var i in rows) {
                    if (typeof rows[i] !== 'object') {
                        continue;
                    }
                    if (rows[i].id == currentSerieVideos_id) {
                        continue;
                    }
                    var html = '<button type="button" class="btn btn-default btn-block"  data-toggle="tooltip" title="<?php echo __('Add To Serie'); ?>" onclick="addToSerie(<?php echo $program['id']; ?>, ' + rows[i].id + ');" id="videos_id_' + rows[i].id + '_playlists_id_<?php echo $program['id']; ?>" ><i class="fas fa-plus"></i> ' + rows[i].title + '</button>';
                    $(resultId).append(html);
                }
                modal.hidePleaseWait();
            }
        });
    }

    function addToSerie(playlists_id, videos_id) {
        addVideoToPlayList(videos_id, true, playlists_id);
        $('#videos_id_' + videos_id + '_playlists_id_' + playlists_id).fadeOut();
        videoWasAdded = true;
    }
    $(document).ready(function () {

        $('#videoSearch-form').submit(function (event) {
            event.preventDefault();
            videoSearch(0, 1);
        });

        $('#serieSearch-form').submit(function (event) {
            event.preventDefault();
            videoSearch(1, 1);
        });

        $('#videoSearchModal').on('hidden.bs.modal', function () {
            if (videoWasAdded) {
                modal.showPleaseWait();
                location.reload();
            }
        });

    });
</script>