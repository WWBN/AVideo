<?php
/**
 * Rich-text <description>/<itunes:summary> of feed/rss.php (channel About text and video descriptions).
 * Some of that text is stored HTML-entity-encoded (User::setAbout() runs xss_esc(), and HTMLPurifier
 * keeps typed "<tags>" as "&lt;tags&gt;"), so it is decoded until stable to avoid double-encoding.
 * Decoding turns that text back into markup, and feed/rss-style.xsl prints it with
 * disable-output-escaping on the site origin, so the decoded HTML is purified AFTER decoding
 * (GHSA-2qxw-wm6h-5jr7). Formatting is kept; scripts, event handlers, inline CSS, iframes and
 * non-http(s)/mailto links are removed.
 */
function feedDescriptionHTML($text, $cacheDir = null)
{
    if ($text === null || $text === '') {
        return '';
    }
    $decoded = (string) $text;
    for ($i = 0; $i < 3; $i++) {
        $next = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($next === $decoded) {
            break;
        }
        $decoded = $next;
    }

    $configPuri = HTMLPurifier_Config::createDefault();
    if (!empty($cacheDir)) {
        $configPuri->set('Cache.SerializerPath', $cacheDir);
    }
    $configPuri->set('HTML.Allowed', 'a[href|title|target],abbr[title],b,blockquote,br,code,div[class],em,h1,h2,h3,h4,h5,h6,hr,i,img[src|alt|width|height],li,ol,p[class],pre,s,span[class],strike,strong,sub,sup,table,tbody,thead,tfoot,tr,td[colspan|rowspan],th[colspan|rowspan],u,ul');
    $configPuri->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
    $configPuri->set('Attr.AllowedFrameTargets', ['_blank']);
    $purifier = new HTMLPurifier($configPuri);
    $html = $purifier->purify($decoded);

    // guard against a literal "]]>" ever closing the CDATA section early
    return trim(str_replace(']]>', ']]&gt;', $html));
}
