<div class="infiniteScrollControls" id="infiniteScrollControls$uid">
    <button type="button" class="btn btn-default btn-block loadInfiniteScrollButton" id="loadInfiniteScrollButton$uid" aria-describedby="infiniteScrollStatus$uid">
        <i class="fas fa-arrow-down infiniteScrollIdleIcon" aria-hidden="true"></i>
        <i class="fas fa-spinner fa-spin infiniteScrollBusyIcon" aria-hidden="true"></i>
        <span class="infiniteScrollButtonLabel">$loadMore</span>
    </button>
    <div class="infiniteScrollStatus text-muted" id="infiniteScrollStatus$uid" role="status" aria-live="polite" aria-atomic="true"></div>
    <span class="hidden infiniteScrollLoadingLabel">$loadingLabel</span>
    <span class="hidden infiniteScrollRetryLabel">$retryLabel</span>
    <span class="hidden infiniteScrollErrorLabel">$errorLabel</span>
    <span class="hidden infiniteScrollEndLabel">$endLabel</span>
</div>
<script src="$webSiteRootURLnode_modules/infinite-scroll/dist/infinite-scroll.pkgd.min.js" type="text/javascript"></script>
<script>
    $(function() {
        var $container = $('$infinityScrollAppendIntoSelector').first();
        var $controls = $('#infiniteScrollControls$uid');
        var $button = $('#loadInfiniteScrollButton$uid');
        var $label = $button.find('.infiniteScrollButtonLabel');
        var $status = $('#infiniteScrollStatus$uid');
        var idleLabel = $label.text();
        var nextLink = document.querySelector('.pagination__next$uid');
        if (!nextLink || typeof $container.infiniteScroll !== 'function') {
            $controls.hide();
            return;
        }
        var nextPage = Number(nextLink.getAttribute('pageNum'));
        var nextURL = new URL(nextLink.href, location.href);
        var ended = false;
        var infScroll;
        function setLoading(loading) {
            $button.prop('disabled', loading).attr('aria-busy', String(loading));
            $controls.toggleClass('is-loading', loading);
            $container.attr('aria-busy', String(loading));
            $label.text(loading ? $controls.find('.infiniteScrollLoadingLabel').text() : idleLabel);
        }
        // Bind before initialization so prefill requests also update the local status.
        $container.on('request.infiniteScroll', function() {
            $status.text('');
            setLoading(true);
        }).on('load.infiniteScroll', function() {
            nextPage++;
            if (nextPage > $totalPages) {
                infScroll.lastPageReached();
            }
        }).on('append.infiniteScroll', function() {
            if (!infScroll.isLoading) setLoading(false);
            lazyImage();
            avideoSocket();
        }).on('error.infiniteScroll', function() {
            setLoading(false);
            $status.text($controls.find('.infiniteScrollErrorLabel').text());
            $label.text($controls.find('.infiniteScrollRetryLabel').text());
            $button.show();
        }).on('last.infiniteScroll', function() {
            ended = true;
            setLoading(false);
            $button.hide();
            $status.text($controls.find('.infiniteScrollEndLabel').text());
        });
        $container.infiniteScroll({
            path: function() {
                if (ended) return;
                // Keep friendly paths and query pagination in sync on every request.
                var url = new URL(nextURL.href);
                url.pathname = url.pathname.replace(/\/page\/\d+(?=\/|$)/, '/page/' + nextPage);
                if (url.searchParams.has('page')) url.searchParams.set('page', nextPage);
                url.searchParams.set('current', nextPage);
                return url.href;
            },
            append: '$infinityScrollGetFromSelector',
            hideNav: '.infiniteScrollPagination$uid',
            history: false,
            checkLastPage: false,
            prefill: $loadOnScroll,
            loadOnScroll: $loadOnScroll
        });
        infScroll = $container.data('infiniteScroll');
        $button.on('click', function() {
            // Infinite Scroll stops after an error; an explicit retry resumes the same page.
            if (!ended && !infScroll.isLoading) {
                infScroll.canLoad = true;
                infScroll.loadNextPage();
            }
        });
    });
</script>
