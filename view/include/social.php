<?php
global $socialAdded, $global;
$titleSocial = @$title;
if (!empty($video['id'])) {
    $url = Video::getLinkToVideo($video['id']);
    if (!empty($video['title'])) {
        $titleSocial = $video['title'];
    } else {
        $video = new Video("", "", $video['id']);
        $titleSocial = $video->getTitle();
    }
}

if (empty($advancedCustom)) {
    $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
}

$removeChars = array('|');
$titleSocial = str_replace($removeChars, '-', $titleSocial);
//$originalURL = $urlSocial;
$urlSocial = urlencode($url);
//set the $urlSocial and the $titleSocial before include this

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

if (empty($socialAdded)) { // do not add the CSS more then once
    ?>     
    <link href="<?php echo getURL('view/css/social.css'); ?>" rel="stylesheet" type="text/css"/>
    <?php
}
$socialAdded = 1;

$social_medias = array(
    new SocialMedias($href, $class, $title, $iclass, $img, $onclick)
);


?>
<ul class="social-network social-circle">
    <?php
    $loaderSequenceName = uniqid();
    foreach ($global['social_medias'] as $key => $value) {
        eval("\$show = \$advancedCustom->showShareButton_{$key};");
        if(empty($show)){
            continue;
        }
        $url = $global['social_medias_'.$key];
        if(empty($value->img)){
            echo '<li class=""><a href="'.$url.'" target="_blank" class="ico'.$key.' '.getCSSAnimationClassAndStyle('animate__bounceIn', $loaderSequenceName).'" title="'.$key.'" data-toggle="tooltip" ><i class="'.$value->iclass.'"></i></a></li>';
        }else{
            echo '<li class=""><a href="'.$url.'" target="_blank" class="ico'.$key.' '.getCSSAnimationClassAndStyle('animate__bounceIn', $loaderSequenceName).'" title="'.$key.'" data-toggle="tooltip" ><i class="fas"><img src="'.$value->img.'" title="'.$key.'" style="height: 30px;"/></i></a></li>';
        }
    }
    ?>
    <li>
        <a href="#" class="icoCopy <?php echo getCSSAnimationClassAndStyle('animate__bounceIn', $loaderSequenceName); ?>" title="<?php echo __('Copy to Clipboard'); ?>" data-toggle="tooltip" onclick="copyToClipboard('<?php echo urldecode($urlSocial); ?>');$(this).closest('.modal').modal('hide');return false;" >
            <i class="far fa-copy"></i>
        </a>
    </li>
</ul>
