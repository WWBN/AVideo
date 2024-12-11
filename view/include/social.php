<?php
global $socialAdded, $global;
$titleSocial = @$title;
$urlShort = @$url;

if (empty($video['id']) && !empty(getVideos_id())) {
    $video['id'] = getVideos_id();
}
if (!empty($video['id'])) {
    $url = Video::getLinkToVideo($video['id']);
    $urlShort = Video::getLinkToVideo($video['id'], '', false, Video::$urlTypeShort, [], true);
    if (!empty($video['title'])) {
        $titleSocial = $video['title'];
    } else {
        $video = new Video("", "", $video['id']);
        $titleSocial = $video->getTitle();
    }
}

if(empty($urlShort)){
    $live = isLive();
    if(!empty($live)){
        $livet = LiveTransmition::getFromRequest();
        $titleSocial = $livet['title'];
        $urlShort = Live::getLinkToLiveFromUsers_idAndLiveServer($livet['users_id'], $livet['live_servers_id'], $livet['live_index'], $live['live_schedule'] );
    }
    //var_dump($livet['users_id'], $livet['live_servers_id'], $livet['live_index'], $live['live_schedule'] , $urlShort, $titleSocial,  $live, $livet, debug_backtrace());exit;
}
if(empty($urlShort)){
    echo '<!-- could not create social URL -->';
    return;
}
if (empty($advancedCustom)) {
    $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
}

$titleSocial = getSEOTitle($titleSocial);
$titleSocial = str_replace('#', '', $titleSocial);
//$originalURL = $urlSocial;
$urlSocial = urlencode($url);
//set the $urlSocial and the $titleSocial before include this


if(!isset($global)){
    $global = [];
}
$global['social_medias_Whatsapp'] = "https://api.whatsapp.com/send?text={$titleSocial}%20{$urlSocial}";
$global['social_medias_Telegram'] = "https://t.me/share/url?url={$urlSocial}&text={$titleSocial}";
$global['social_medias_Facebook'] = "https://www.facebook.com/sharer.php?u={$urlSocial}&title={$titleSocial}";
$global['social_medias_Twitter'] = "http://twitter.com/intent/tweet?text={$titleSocial}+{$urlSocial}";
$global['social_medias_Tumblr'] = "http://www.tumblr.com/share?v=3&u=$urlSocial&quote=$titleSocial&s=";
$global['social_medias_Pinterest'] = "http://pinterest.com/pin/create/button/?url=$urlSocial&description=";
$global['social_medias_Reddit'] = "http://www.reddit.com/submit?url=$urlSocial&title=$titleSocial";
$global['social_medias_LinkedIn'] = "http://www.linkedin.com/shareArticle?mini=true&url=$urlSocial&title=$titleSocial&summary=&source=$urlSocial";
$global['social_medias_Wordpress'] = "http://wordpress.com/press-this.php?u=$urlSocial&quote=$titleSocial&s=";
$global['social_medias_Pinboard'] = "https://pinboard.in/popup_login/?url=$urlSocial&title=$titleSocial&description=";
$global['social_medias_Gab'] = "https://gab.com/compose?url={$urlSocial}&text={$titleSocial}";
$global['social_medias_CloutHub'] = "https://app.clouthub.com/share?url={$urlSocial}&text={$titleSocial}";

$socialAdded = 1;

$social_medias = [
    new SocialMedias(@$href, @$class, @$title, @$iclass, @$img, @$onclick),
];
?>
<ul class="social-network social-circle social-bgColor">
    <?php
    $loaderSequenceName = uniqid();
    foreach ($global['social_medias'] as $key => $value) {
        eval("\$show = \$advancedCustom->showShareButton_{$key};");
        if (empty($show)) {
            continue;
        }
        $url = $global['social_medias_' . $key];
        if (empty($value->img)) {
            echo '<li class=""><a href="' . $url . '" target="_blank" class="ico' . $key . ' ' . getCSSAnimationClassAndStyle('animate__bounceIn', $loaderSequenceName) . '" title="' . $key . '" data-toggle="tooltip" ><i class="' . $value->iclass . '"></i></a></li>';
        } else {
            echo '<li class=""><a href="' . $url . '" target="_blank" class="ico' . $key . ' ' . getCSSAnimationClassAndStyle('animate__bounceIn', $loaderSequenceName) . '" title="' . $key . '" data-toggle="tooltip" ><i class="fas"><img src="' . $value->img . '" title="' . $key . '" style="height: 30px;"/></i></a></li>';
        }
    }
    ?>
    <li>
        <a href="#" class="icoCopy <?php echo getCSSAnimationClassAndStyle('animate__bounceIn', $loaderSequenceName); ?>" title="<?php echo __('Copy to Clipboard'); ?>" data-toggle="tooltip" onclick="copyToClipboard('<?php echo $urlShort; ?>');$(this).closest('.modal').modal('hide');return false;" >
            <i class="far fa-copy"></i>
        </a>
    </li>
</ul>
<div style="margin-top: 10px;">
    <?php
    getInputCopyToClipboard(uniqid(), $urlShort, 'class="form-control" readonly="readonly" style="background-color: #EEE; color: #000;"');
    ?>
</div>