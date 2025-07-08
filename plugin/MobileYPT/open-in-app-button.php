<?php
global $global;

if (!isMobile()) {
    return; // only run on mobile devices
}

$videos_id = getVideos_id();

if (empty($videos_id)) {
    if (!isLive()) {
        return; // no video or live stream, stop script
    }
    $isLive = isLive();
    $path = "live?key={$isLive['key']}";
} else {
    $path = "video?videos_id={$videos_id}";
}

$host = parse_url($global['webSiteRootURL'], PHP_URL_HOST);
$deepLink = "ypt://{$host}/{$path}";
?>

<script>
    $(document).ready(function() {
        if (isMobile()) {
            const deepLink = "<?php echo $deepLink; ?>";
            const timeout = 1200;
            const clickedAt = +new Date();

            console.log("Trying to open deep link:", deepLink);
            window.location = deepLink;

            setTimeout(function() {
                const delta = +new Date() - clickedAt;
                if (delta < timeout + 100) {
                    console.log("deepLink: App not opened (probably not installed). Staying on the page.");
                } else {
                    console.log("deepLink: App likely opened successfully.");
                }
            }, timeout);
        }

    });
</script>
