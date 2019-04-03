<?php
//set the $url and the $title before include this
$facebookURL = "https://www.facebook.com/sharer.php?u={$url}&title={$title}";
$twitterURL = "http://twitter.com/home?status={$title}+{$url}";
?>                                           
<ul class="social-network social-circle">
    <li><a href="<?php echo $facebookURL; ?>" target="_blank" class="icoFacebook" title="Facebook"><i class="fab fa-facebook-square"></i></a></li>
    <li><a href="<?php echo $twitterURL; ?>" target="_blank"  class="icoTwitter" title="Twitter"><i class="fab fa-twitter"></i></a></li>
</ul>