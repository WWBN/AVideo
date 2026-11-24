/**
 * "View on site" from the ADs editor: scrolls to the position chosen there.
 * Loaded only for admins with ?AdsDebug=1&AdsHighlight=<type> (ADs::getAdsHighlightFooterCode()).
 * options.targets: the type first, then the versions that may be on the page instead
 * (split parent, desktop/mobile counterpart).
 */
function ADsHighlightPosition(options, attempt) {
    attempt = attempt || 0;
    var $target = $();
    var index = 0;
    for (; index < options.targets.length; index++) {
        $target = $('.AdsDebug[data-ads-type="' + options.targets[index] + '"]').first();
        if ($target.length) {
            break;
        }
    }
    if (!$target.length) {
        // sections loaded after the page (e.g. Flix category rows)
        if (attempt < 3) {
            setTimeout(function () {
                ADsHighlightPosition(options, attempt + 1);
            }, 1500);
        } else {
            avideoToastWarning(options.name + ': ' + options.notFound);
        }
        return;
    }
    $('.AdsDebugHighlight').removeClass('AdsDebugHighlight');
    $target.addClass('AdsDebugHighlight');
    $target.attr('tabindex', '-1');
    $target[0].scrollIntoView({block: 'center'});
    $target[0].focus({preventScroll: true});
    if (index > 0) {
        avideoToastInfo(options.name + ': ' + options.otherVersion);
    }
}
