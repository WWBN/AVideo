<?php
//set the $url and the $title before include this
$facebookURL = "https://www.facebook.com/sharer.php?u={$url}&title={$title}";
$twitterURL = "http://twitter.com/intent/tweet?text={$title}+{$url}";
$tumblr = "http://www.tumblr.com/share?v=3&u=$url&quote=$title&s=";
$pinterest = "http://pinterest.com/pin/create/button/?url=$url&description=";
$reddit = "http://www.reddit.com/submit?url=$url&title=$title";
$linkedin = "http://www.linkedin.com/shareArticle?mini=true&url=$url&title=$title&summary=&source=$url";
$wordpress = "http://wordpress.com/press-this.php?u=$url&quote=$title&s=";
$pinboard = "https://pinboard.in/popup_login/?url=$url&title=$title&description=";
?>                                           
<ul class="social-network social-circle">
    <li><a href="<?php echo $facebookURL; ?>" target="_blank" class="icoFacebook" title="Facebook"><i class="fab fa-facebook-square"></i></a></li>
    <li><a href="<?php echo $twitterURL; ?>" target="_blank"  class="icoTwitter" title="Twitter"><i class="fab fa-twitter"></i></a></li>
    <li><a href="<?php echo $tumblr; ?>" target="_blank"  class="icoTumblr" title="Tumblr"><i class="fab fa-tumblr"></i></a></li>
    <li><a href="<?php echo $pinterest; ?>" target="_blank"  class="icoPinterest" title="Pinterest"><i class="fab fa-pinterest-p"></i></a></li>
    <li><a href="<?php echo $reddit; ?>" target="_blank"  class="icoReddit" title="Reddit"><i class="fab fa-reddit-alien"></i></a></li>
    <li><a href="<?php echo $linkedin; ?>" target="_blank"  class="icoLinkedin" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
    <li><a href="<?php echo $wordpress; ?>" target="_blank"  class="icoWordpress" title="Wordpress"><i class="fab fa-wordpress-simple"></i></a></li>
    <li><a href="<?php echo $pinboard; ?>" target="_blank"  class="icoPinboard" title="Pinboard"><i class="fas fa-thumbtack"></i></a></li>
  
    
</ul>
