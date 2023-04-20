<div class="scroller-status$uid">
    <div class="infinite-scroll-request loader-ellips text-center">
        <i class="fas fa-spinner fa-pulse text-muted"></i>
    </div>
</div>
<center>
    <button class="btn btn-xs btn-default" style="border: none; background: transparent;" id="loadInfiniteScrollButton$uid"> More </button>
</center>
<script src="$webSiteRootURLnode_modules/infinite-scroll/dist/infinite-scroll.pkgd.min.js" type="text/javascript"></script>
<script>
    var $container$uid;
    $(function() {
        loadInfiniteScrool$uid();
    });
    var append_infiniteScroll_timout;

    function loadInfiniteScrool$uid() {
        if (typeof $('$infinityScrollAppendIntoSelector').infiniteScroll == 'function') {
            $container$uid = $('$infinityScrollAppendIntoSelector').infiniteScroll({
                path: '.pagination__next$uid',
                append: '$infinityScrollGetFromSelector',
                status: '.scroller-status$uid',
                hideNav: '.infiniteScrollPagination$uid',
                debug: false,
                prefill: true,
                history: false,
                checkLastPage: false,
                button: '#loadInfiniteScrollButton$uid',
                scrollThreshold: false,
                loadOnScroll: false,
                responseType: 'document',
            });
            $container$uid.on('request.infiniteScroll', function(event, path, fetchPromise) {
                //console.log(`infiniteScroll Loading page: ${path}`);
            });
            let infScroll = $container$uid.data('infiniteScroll');
            $container$uid.on('load.infiniteScroll', function(event, body, path, response) {
                //console.log(`infiniteScroll Loaded: ${path}`, `Status: ${response.status}`, `Current page: ${infScroll.pageIndex}`, `${infScroll.loadCount} pages loaded`);
                var $response = $(response);
                var items = $response.find('$infinityScrollGetFromSelector').get();
                $container$uid.infiniteScroll('appendItems', items, $response);
                lazyImage();
                avideoSocket();
            });
            $container$uid.on('error.infiniteScroll', function(event, error, path, response) {
                //console.error(`infiniteScroll Could not load: ${path}. ${error}`);
            });
            $container$uid.on('last.infiniteScroll', function(event, body, path) {
                //console.log(`infiniteScroll Last page hit on ${path}`, body, event);
            });
            $container$uid.on('history.infiniteScroll', function(event, title, path) {
                //console.log(`infiniteScroll History changed to: ${path}`);
            });
        }
    }
</script>