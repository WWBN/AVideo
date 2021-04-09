<div class="scroller-status$uid">
    <div class="infinite-scroll-request loader-ellips text-center">
        <i class="fas fa-spinner fa-pulse text-muted"></i>
    </div>
</div>
<center>
    <button class="btn btn-xs btn-default" style="border: none; background: transparent;" id="loadInfiniteScrollButton$uid"> More </button>
</center>
<script src="$webSiteRootURLview/js/infinite-scroll.pkgd.min.js" type="text/javascript"></script>
<script>
    var $container$uid;
    $(function () {
        loadInfiniteScrool$uid();
    });
    /*
     * 
     
    document.addEventListener('scroll', function (e) {
        var scrollPosition = window.pageYOffset;
        var windowSize = window.innerHeight;
        var bodyHeight = document.body.offsetHeight;
        var distance = bodyHeight - (scrollPosition + windowSize);
        //console.log('infiniteScroll ', distance, windowSize, distance < windowSize);
        if (distance < windowSize) {
            $container$uid.infiniteScroll('loadNextPage');
        }
    });*/
    var append_infiniteScroll_timout;
    function loadInfiniteScrool$uid() {
        if (typeof $('$infinityScrollAppendIntoSelector').infiniteScroll !== 'funciton') {
            $container$uid = $('$infinityScrollAppendIntoSelector').infiniteScroll({
                path: '.pagination__next$uid',
                append: '$infinityScrollGetFromSelector',
                status: '.scroller-status$uid',
                hideNav: '.infiniteScrollPagination$uid',
                debug: true,
                prefill: true,
                history: false,
                checkLastPage: false,
                button: '#loadInfiniteScrollButton$uid'
            });
            $container$uid.on('scrollThreshold.infiniteScroll', function (event) {
                console.log('infiniteScroll Scroll at bottom');
            });
            $container$uid.on('request.infiniteScroll', function (event, path, fetchPromise) {
                console.log(`infiniteScroll Loading page: ${path}`);
            });
            let infScroll = $container$uid.data('infiniteScroll');
            $container$uid.on('load.infiniteScroll', function (event, body, path, response) {
                console.log(`infiniteScroll Loaded: ${path}`, `Status: ${response.status}`, `Current page: ${infScroll.pageIndex}`, `${infScroll.loadCount} pages loaded`);
            });
            $container$uid.on('append.infiniteScroll', function (event, body, path, items, response) {
                console.log(`infiniteScroll Appended ${items.length} items on ${path}`, body);
                clearTimeout(append_infiniteScroll_timout);
                append_infiniteScroll_timout = setTimeout(function () {
                    lazyImage();
                    avideoSocket();
                }, 1000);
            });
            $container$uid.on('error.infiniteScroll', function (event, error, path, response) {
                console.error(`infiniteScroll Could not load: ${path}. ${error}`);
            });
            $container$uid.on('last.infiniteScroll', function (event, body, path) {
                console.log(`infiniteScroll Last page hit on ${path}`, body, event);
            });
            $container$uid.on('history.infiniteScroll', function (event, title, path) {
                console.log(`infiniteScroll History changed to: ${path}`);
            });            
        }
    }

</script>