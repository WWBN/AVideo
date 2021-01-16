<div id="programsContainer"></div>
<p class="pagination infiniteScrollPagination">
    <a class="pagination__next" href="<?php echo $global['webSiteRootURL']; ?>view/channelPlaylistItems.php?channelName=<?php echo $_GET['channelName']; ?>&current=1"></a>
</p>
<div class="scroller-status">
    <div class="infinite-scroll-request loader-ellips text-center">
        <i class="fas fa-spinner fa-pulse text-muted"></i>
    </div>
</div>
<script>

    var timoutembed;
    function setTextEmbedCopied() {
        clearTimeout(timoutembed);
        $("#btnEmbedText").html("<?php echo __("Copied!"); ?>");
        timoutembed = setTimeout(function () {
            $("#btnEmbedText").html("<?php echo __("Copy embed code"); ?>");
        }, 3000);
    }

    function saveSortable($sortableObject, playlist_id) {
        var list = $($sortableObject).sortable("toArray");
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php',
            data: {
                "list": list,
                "playlist_id": playlist_id
            },
            type: 'post',
            success: function (response) {
                $("#channelPlaylists").load(webSiteRootURL + "view/channelPlaylist.php?channelName=" + channelName);
                modal.hidePleaseWait();
            }
        });
    }

    function sortNow($t, position) {
        var $this = $($t).closest('.galleryVideo');
        var $uiDiv = $($t).closest('.ui-sortable');
        var $playListId = $($t).closest('.panel').attr('playListId');
        var $list = $($t).closest('.ui-sortable').find('li');
        if (position < 0) {
            return false;
        }
        if (position === 0) {
            $this.slideUp(500, function () {
                $this.insertBefore($this.siblings(':eq(0)'));
                saveSortable($uiDiv, $playListId);
            }).slideDown(500);
        } else if ($list.length - 1 > position) {
            $this.slideUp(500, function () {
                $this.insertBefore($this.siblings(':eq(' + position + ')'));
                saveSortable($uiDiv, $playListId);
            }).slideDown(500);
        } else {
            $this.slideUp(500, function () {
                $this.insertAfter($this.siblings(':eq(' + ($list.length - 2) + ')'));
                saveSortable($uiDiv, $playListId);
            }).slideDown(500);
        }
    }

    var currentObject;
    $(function () {

        $container = $('#programsContainer').infiniteScroll({
            path: '.pagination__next',
            append: '.programsContainerItem',
            status: '.scroller-status',
            hideNav: '.infiniteScrollPagination',
            prefill: false,
            history: false,
            loadOnScroll: false
        });
        $container.on('append.infiniteScroll', function (event, response, path, items) {
            lazyImage();
        });

        // trigger the infinity scroll when click on the tab
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            if ((target == '#channelPlayLists')) {
                $container.infiniteScroll('loadNextPage');
                $container.infiniteScroll('option', {
                    loadOnScroll: true,
                });
            }
        });

    });
</script>