<?php
global $socialAdded;
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
$removeChars = array('|');
$titleSocial = str_replace($removeChars, '-', $titleSocial);
//$originalURL = $urlSocial;
$urlSocial = urlencode($url);
//set the $urlSocial and the $titleSocial before include this
$facebookURL = "https://www.facebook.com/sharer.php?u={$urlSocial}&title={$titleSocial}";
$twitterURL = "http://twitter.com/intent/tweet?text={$titleSocial}+{$urlSocial}";
$tumblr = "http://www.tumblr.com/share?v=3&u=$urlSocial&quote=$titleSocial&s=";
$pinterest = "http://pinterest.com/pin/create/button/?url=$urlSocial&description=";
$reddit = "http://www.reddit.com/submit?url=$urlSocial&title=$titleSocial";
$linkedin = "http://www.linkedin.com/shareArticle?mini=true&url=$urlSocial&title=$titleSocial&summary=&source=$urlSocial";
$wordpress = "http://wordpress.com/press-this.php?u=$urlSocial&quote=$titleSocial&s=";
$pinboard = "https://pinboard.in/popup_login/?url=$urlSocial&title=$titleSocial&description=";
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
        <a href="#" class="icoCopy" title="<?php echo __('Copy to Clipboard'); ?>" data-toggle="tooltip" onclick="copyToClipboard('<?php echo urldecode($urlSocial); ?>');$(this).closest('.modal').modal('hide');" >
            <i class="far fa-copy"></i>
        </a>
    </li>
</ul>
