<?php
global $socialAdded;
if (!empty($video['id'])) {
    $urlSocial = Video::getLinkToVideo($video['id']);
    if (!empty($video['title'])) {
        $title = $video['title'];
    } else {
        $video = new Video("", "", $video['id']);
        $title = $video->getTitle();
    }
}

//$originalURL = $urlSocial;
$urlSocial = urlencode($url);
//set the $urlSocial and the $title before include this
$facebookURL = "https://www.facebook.com/sharer.php?u={$urlSocial}&title={$title}";
$twitterURL = "http://twitter.com/intent/tweet?text={$title}+{$urlSocial}";
$tumblr = "http://www.tumblr.com/share?v=3&u=$urlSocial&quote=$title&s=";
$pinterest = "http://pinterest.com/pin/create/button/?url=$urlSocial&description=";
$reddit = "http://www.reddit.com/submit?url=$urlSocial&title=$title";
$linkedin = "http://www.linkedin.com/shareArticle?mini=true&url=$urlSocial&title=$title&summary=&source=$urlSocial";
$wordpress = "http://wordpress.com/press-this.php?u=$urlSocial&quote=$title&s=";
$pinboard = "https://pinboard.in/popup_login/?url=$urlSocial&title=$title&description=";
if (empty($socialAdded)) { // do not add the CSS more then once
    ?>     
    <link href="<?php echo getCDN(); ?>view/css/social.css" rel="stylesheet" type="text/css"/>
    <?php
}
$socialAdded = 1;
?>
<ul class="social-network social-circle">
    <li><a href="<?php echo $facebookURL; ?>" target="_blank" class="icoFacebook" title="Facebook" data-toggle="tooltip" ><i class="fab fa-facebook-square"></i></a></li>
    <li><a href="<?php echo $twitterURL; ?>" target="_blank"  class="icoTwitter" title="Twitter" data-toggle="tooltip" ><i class="fab fa-twitter"></i></a></li>
    <li><a href="<?php echo $tumblr; ?>" target="_blank"  class="icoTumblr" title="Tumblr" data-toggle="tooltip" ><i class="fab fa-tumblr"></i></a></li>
    <li><a href="<?php echo $pinterest; ?>" target="_blank"  class="icoPinterest" title="Pinterest" data-toggle="tooltip" ><i class="fab fa-pinterest-p"></i></a></li>
    <li><a href="<?php echo $reddit; ?>" target="_blank"  class="icoReddit" title="Reddit" data-toggle="tooltip" ><i class="fab fa-reddit-alien"></i></a></li>
    <li><a href="<?php echo $linkedin; ?>" target="_blank"  class="icoLinkedin" title="LinkedIn" data-toggle="tooltip" ><i class="fab fa-linkedin-in"></i></a></li>
    <li><a href="<?php echo $wordpress; ?>" target="_blank"  class="icoWordpress" title="Wordpress" data-toggle="tooltip" ><i class="fab fa-wordpress-simple"></i></a></li>
    <li><a href="<?php echo $pinboard; ?>" target="_blank"  class="icoPinboard" title="Pinboard" data-toggle="tooltip" ><i class="fas fa-thumbtack"></i></a></li>
    <li>
        <a href="#" class="icoCopy" title="<?php echo __('Copy to Clipboard'); ?>" data-toggle="tooltip" onclick="copyToClipboard('<?php echo ($urlSocial); ?>');$(this).closest('.modal').modal('hide');" >
            <i class="far fa-copy"></i>
        </a>
    </li>
</ul>
