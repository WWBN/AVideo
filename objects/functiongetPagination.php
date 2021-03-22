<div class="scroller-status$uid">
    <div class="infinite-scroll-request loader-ellips text-center">
        <i class="fas fa-spinner fa-pulse text-muted"></i>
    </div>
</div>
<script src="$webSiteRootURLview/js/infinite-scroll.pkgd.min.js" type="text/javascript"></script>
<script>
    var $container$uid;
    $(function () {
        loadInfiniteScrool$uid(false);
    });
    
    function loadInfiniteScrool$uid(retrieve){
        if(typeof $('$infinityScrollAppendIntoSelector').infiniteScroll !== 'funciton'){
            $container$uid = $('$infinityScrollAppendIntoSelector').infiniteScroll({
                path: '.pagination__next$uid',
                append: '$infinityScrollGetFromSelector',
                status: '.scroller-status$uid',
                hideNav: '.infiniteScrollPagination$uid',
                prefill: false,
                history: false
            });
            $container$uid.on('append.infiniteScroll', function (event, response, path, items) {
                lazyImage();
                avideoSocket();
            });
            if(retrieve){
                $container$uid.infinitescroll('retrieve');
            }
        }
    }
    
</script>
<center>
    <button class="btn btn-xs btn-default" onclick="loadInfiniteScrool$uid(true);"> ... </button>
</center>