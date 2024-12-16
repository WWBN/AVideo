<?php

function _addPageNumber($url, $page)
{   
    if (preg_match("/_pageNum_/", $url)) {
        $url = str_replace("_pageNum_", $page, $url);
    }
    
    if (preg_match("/page\/([0-9]+)\/?/", $url, $match)) {
        $url = str_replace("/page/{$match[1]}/", "/page/{$page}/", $url);
        $url = removeQueryStringParameter($url, 'page');
    }else{
        $url = addQueryStringParameter($url, 'page', $page);
    }

    $url = addQueryStringParameter($url, 'current', $page);

    // remove duplicated
    $pattern = '/\/page\/(\d+)\/\d+\//';
    $replacement = "/page/{$page}/";
    $url = preg_replace($pattern, $replacement, $url, 1);
    return $url;
}

function _getPageItem($url, $page, $uid, $isNext = false, $icon = '')
{
    $class = '';
    if ($isNext) {
        $class .= 'pagination__next pagination__next' . $uid;
    }
    $currentPage = getCurrentPage(true);
    if ($page == $currentPage) {
        return '<li class="page-item active"><span class="page-link"> ' . $page . ' <span class="sr-only">(current)</span></span></li>';
    }
    $link = _addPageNumber($url, $page);
    $li =  "<li class=\"page-item\" ><a class=\"page-link {$class}\" href=\"{$link}\" tabindex=\"-1\" pageNum=\"{$page}\" currentPage=\"{$currentPage}\" onclick=\"modal.showPleaseWait();\">";
    if (!empty($icon)) {
        $li .=  "<i class=\"{$icon}\"></i>";
    } else {
        $li .= $page;
    }
    $li .=  "</a></li>";
    return $li;
}

function getPagination($total, $link = "", $maxVisible = 10, $infinityScrollGetFromSelector = "", $infinityScrollAppendIntoSelector = "", $loadOnScroll = false, $showOnly = '')
{
    global $global, $advancedCustom;
    if ($total < 2) {
        return '<!-- getPagination total < 2 (' . json_encode(array('total'=>$total, 'maxVisible'=>$maxVisible, 'total'=>$total, )) . ') -->';
    }

    $page = getCurrentPage();
    if ($total < $page) {
        $page = $total;
    }
    
    //var_dump($page, $total, getCurrentPage());exit;

    $isInfiniteScroll = !empty($infinityScrollGetFromSelector) && !empty($infinityScrollAppendIntoSelector);

    $uid = uniqid();

    if ($total < $maxVisible) {
        $maxVisible = $total;
    }
    if (empty($link)) {
        $link = getSelfURI();
        if (preg_match("/(current=[0-9]+)/i", $link, $match)) {
            $link = str_replace($match[1], "current=_pageNum_", $link);
        } else {
            $link .= (parse_url($link, PHP_URL_QUERY) ? '&' : '?') . 'current=_pageNum_';
        }
    }
    if ($isInfiniteScroll) {
        $link = addQueryStringParameter($link, 'isInfiniteScroll', getCurrentPage());
    }
    if (!empty($showOnly)) {
        $link = addQueryStringParameter($link, 'showOnly', $showOnly);
    }
    $class = '';
    if (!empty($infinityScrollGetFromSelector) && !empty($infinityScrollAppendIntoSelector)) {
        $class = "infiniteScrollPagination{$uid} hidden";
    }

    if ($isInfiniteScroll && $page > 1) {
        return "<nav class=\"{$class}\">"
            . "<ul class=\"pagination\">"
            . _getPageItem($link, $page, $uid, true)
            . "</ul></nav>";
    }
    $pag = '<nav aria-label="Page navigation" class="text-center ' . $class . '"><ul class="pagination"><!-- ' . json_encode(array('total'=>$total, 'maxVisible'=>$maxVisible, 'page'=>$page, 'link'=>$link, )) . ' -->';
    $start = 1;
    $end = $maxVisible;

    if ($page > $maxVisible - 2) {
        $start = $page - ($maxVisible - 2);
        $end = $page + 2;
        if ($end > $total) {
            $rest = $end - $total;
            $start -= $rest;
            $end -= $rest;
        }
    }
    if ($start <= 0) {
        $start = 1;
    }
    if (!$isInfiniteScroll) {
        if ($page > 1) {
            $pageLinkNum = 1;
            $pageBackLinkNum = $page - 1;
            if ($start > ($page - 1)) {
                $pag .= _getPageItem($link, $pageLinkNum, $uid, false, 'fas fa-angle-double-left');
            }
            $pag .= _getPageItem($link, $pageBackLinkNum, $uid, false, 'fas fa-angle-left');
        }
        for ($i = $start; $i <= $end; $i++) {
            $pag .= _getPageItem($link, $i, $uid);
        }
    }
    if ($page < $total) {
        $pageLinkNum = $total;
        $pageForwardLinkNum = $page + 1;
        $pageLink = _addPageNumber($link, $pageLinkNum);

        $pag .= _getPageItem($link, $pageForwardLinkNum, $uid, true, 'fas fa-angle-right');
        if ($total > ($end + 1)) {
            $pag .= _getPageItem($link, $pageLinkNum, $uid, false, 'fas fa-angle-double-right');
        }
    }
    //var_dump($page, $link, $pageForwardLink, $pag);exit;
    $pag .= PHP_EOL . '</ul></nav> ';

    if ($isInfiniteScroll) {
        $content = file_get_contents($global['systemRootPath'] . 'objects/functiongetPagination.php');
        $pag .= str_replace(
            ['$uid', '$webSiteRootURL', '$infinityScrollGetFromSelector', '$infinityScrollAppendIntoSelector', '$loadMore', '$loadOnScroll'],
            [$uid, $global['webSiteRootURL'], $infinityScrollGetFromSelector, $infinityScrollAppendIntoSelector,  __('Load More'), (!empty($loadOnScroll) ? 'true' : 'false')],
            $content
        );
    }

    return $pag;
}
