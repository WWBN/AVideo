<?php
/**
 * Channel "About" text is written by any channel owner, so it is purified before it is shown.
 * Iframes are kept only for the video players TinyMCE's media button inserts (YouTube, Vimeo,
 * Dailymotion) and for this site's own embed pages. Other pages of this site are never allowed:
 * a same-origin iframe loads with the viewer's session and passes the same-origin CSRF check.
 */
function getChannelAboutSafeIframeRegexp($webSiteRootURL)
{
    return '~' . getChannelAboutSafeIframePattern($webSiteRootURL) . '~i';
}

/** Pattern without delimiters, also valid as a JavaScript RegExp (TinyMCE media dialog) */
function getChannelAboutSafeIframePattern($webSiteRootURL)
{
    $providers = '(?:www\.)?youtube(?:-nocookie)?\.com/embed/'
        . '|player\.vimeo\.com/video/'
        . '|(?:www\.)?dailymotion\.com/embed/video/';
    $regexp = '(?:https?:)?//(?:' . $providers . ')';

    $host = preg_replace('#^https?://#i', '', rtrim((string) $webSiteRootURL, '/'));
    if (!empty($host)) {
        // A path segment made only of dots (".." or "%2e%2e"), a backslash, an encoded slash or a
        // tab/newline (browsers drop them) could climb out of the embed path. Dots inside a
        // segment are fine: video titles such as "wait-for-it..." are part of the embed URLs.
        $regexp .= '|(?:https?:)?//' . preg_quote($host, '~')
            . '(?![^?#]*(?:\\\\|%5c|%2f|[\t\n\r]|/(?:\.|%2e)+(?:[/?#]|$)))/'
            . '(?:(?:videoEmbed|vEmbed|videoEmbeded|evideoEmbed|articleEmbed|audioEmbed)/'
            . '|channel/[^/?#]+/(?:videoEmbed|articleEmbed|audioEmbed)/)';
    }
    return '^(?:' . $regexp . ')';
}

function purifyChannelAboutHTML($about, $webSiteRootURL, $cacheDir = null)
{
    if (empty($about)) {
        return '';
    }
    $configPuri = HTMLPurifier_Config::createDefault();
    if (!empty($cacheDir)) {
        $configPuri->set('Cache.SerializerPath', $cacheDir);
    }
    $configPuri->set('HTML.Allowed', 'a[href|target|title|class],abbr[title],b,blockquote[class],br,code,div[class|style],em,h1[class],h2[class],h3[class],h4[class],h5[class],h6[class],hr[class],i[class],iframe[src|width|height|frameborder|title],img[class|src|alt|style|width|height],li[class],ol[class],p[class|style],pre[class],s,span[class|style],strong,u,ul[class]');
    $configPuri->set('Attr.AllowedFrameTargets', ['_blank']);
    $configPuri->set('AutoFormat.RemoveEmpty', true);
    $configPuri->set('HTML.SafeIframe', true);
    $configPuri->set('URI.SafeIframeRegexp', getChannelAboutSafeIframeRegexp($webSiteRootURL));
    $purifier = new HTMLPurifier($configPuri);
    $about = $purifier->purify($about);
    // HTMLPurifier only keeps allowfullscreen in trusted mode; every iframe left here passed the allowlist
    return str_replace('<iframe ', '<iframe allowfullscreen="allowfullscreen" ', $about);
}
