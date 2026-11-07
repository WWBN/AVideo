<?php
/**
 * "Notify Subscribers" messages are written in TinyMCE by any user who can upload and are sent
 * inside the site-branded e-mail template. The formatting is kept (text styles, colors, alignment,
 * links, images, lists, tables); scripts, forms, iframes, positioning CSS and any other markup are
 * removed. E-mail clients do not play video, so a video/audio/iframe from the media button becomes
 * a link to its address.
 */
function purifyEmailMessageHTML($message, $cacheDir = null)
{
    $message = trim((string) $message);
    if ($message === '') {
        return '';
    }
    if ($message === strip_tags($message)) {
        // plain text: keep the line breaks
        return nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    }

    $message = preg_replace_callback('~<(video|audio|iframe)\b[^>]*>.*?</\1\s*>~is', 'emailMessageMediaToLink', $message);

    $configPuri = HTMLPurifier_Config::createDefault();
    if (!empty($cacheDir)) {
        $configPuri->set('Cache.SerializerPath', $cacheDir);
    }
    $configPuri->set('HTML.Allowed', 'a[href|title|target],b,blockquote,br,code,div[style],em,h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],hr,i,img[src|alt|width|height|style],li[style],ol,p[style],pre,s,span[style],strike,strong,sub,sup,table[border|cellpadding|cellspacing|width|style],tbody,thead,tfoot,tr,td[colspan|rowspan|width|style],th[colspan|rowspan|width|style],u,ul');
    $configPuri->set('CSS.AllowedProperties', ['color', 'background-color', 'text-align', 'text-decoration', 'font-weight', 'font-style', 'font-size', 'width', 'height', 'padding-left', 'margin-left', 'border', 'border-collapse']);
    $configPuri->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
    $configPuri->set('Attr.AllowedFrameTargets', ['_blank']);
    $configPuri->set('AutoFormat.RemoveEmpty', true);
    $purifier = new HTMLPurifier($configPuri);
    return $purifier->purify($message);
}

function emailMessageMediaToLink($matches)
{
    if (!preg_match('~\bsrc\s*=\s*(["\'])(.*?)\1~is', $matches[0], $src)) {
        return '';
    }
    $url = html_entity_decode($src[2], ENT_QUOTES, 'UTF-8');
    if (!preg_match('~^https?://~i', $url)) {
        return '';
    }
    $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    return "<a href=\"{$url}\" target=\"_blank\">{$url}</a>";
}
