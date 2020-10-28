<?php
echo "<!-- OpenGraph for the Site -->";
if ($users_id = isChannel()) {
    $imgw = 200;
    $imgh = 200;
    $img = User::getOGImage($users_id);
    $title = User::getNameIdentificationById($users_id);
    $url = User::getChannelLink($users_id);
    ?>
    <meta property="og:type" content="profile" />
    <meta property="profile:username" content="<?php echo $title; ?>" />
    <?php
} else if (!isVideo()) {
    $imgw = 200;
    $imgh = 200;
    $img = Configuration::getOGImage();
    $title = html2plainText($config->getWebSiteTitle());
    $url = $global['webSiteRootURL'];
    ?>
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="<?php echo $title; ?>">
    <?php
} else {
    return false;
}
?>
<link rel="image_src" href="<?php echo $img; ?>" />
<meta property="og:image" content="<?php echo $img; ?>" />
<meta property="og:image:url" content="<?php echo $img; ?>" />
<meta property="og:image:secure_url" content="<?php echo $img; ?>" />
<meta property="og:image:type" content="image/jpeg" />
<meta property="og:image:width"        content="<?php echo $imgw; ?>" />
<meta property="og:image:height"       content="<?php echo $imgh; ?>" />

<meta property="fb:app_id"             content="774958212660408" />
<meta property="og:title"              content="<?php echo $title; ?>" />
<meta property="og:description"        content="<?php echo $title; ?>" />
<meta property="og:url"                content="<?php echo $url; ?>" />

<?php
if (!empty($advancedCustom->twitter_summary_large_image)) {
    ?>
    <meta name="twitter:card" content="summary_large_image" />   
    <?php
} else {
    ?>
    <meta name="twitter:card" content="summary" />   
    <?php
}
?>
<meta name="twitter:url" content="<?php echo $global['webSiteRootURL']; ?>"/>
<meta name="twitter:title" content="<?php echo $title; ?>"/>
<meta name="twitter:description" content="<?php echo $title; ?>"/>
<meta name="twitter:image" content="<?php echo $img; ?>"/>
