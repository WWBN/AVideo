<?php
/**
 * Unlock page shown instead of a bare 403 when a mandatory playback group rule refuses the title.
 * Included by PlaybackAccess::showUnlockPage(), which sets $video (videos row), $videos_id and $offers.
 * It shows only metadata (artwork, title, description) and links to the existing checkouts; it never
 * prints a playable source.
 */
global $global;
$global['doNotLoadPlayer'] = 1;

AVideoPlugin::loadPluginIfEnabled('YPTWallet');
$unlockFormatPrice = function ($value) {
    if (empty($value)) {
        return __('Free');
    }
    return class_exists('YPTWallet') ? YPTWallet::formatCurrency($value) : number_format($value, 2);
};
$unlockPeriod = function ($offer) {
    if ($offer['type'] === 'ppv' || $offer['type'] === 'rental') {
        return sprintf(__('Valid for %d hours'), $offer['hours']);
    }
    switch ($offer['days']) {
        case 0:
            return '';
        case 1:
            return __('per day');
        case 7:
            return __('per week');
        case 30:
        case 31:
            return __('per month');
        case 365:
        case 366:
            return __('per year');
    }
    return sprintf(__('every %d days'), $offer['days']);
};

$unlockImages = Video::getImageFromFilename($video['filename'], $video['type']);
$unlockBackdrop = !empty($unlockImages->poster) ? $unlockImages->poster : '';
$unlockPoster = '';
$unlockPosterIsPortrait = false;
if (!empty($unlockImages->posterPortrait) && !ImagesPlaceHolders::isDefaultImage($unlockImages->posterPortrait)) {
    $unlockPoster = !empty($unlockImages->posterPortraitThumbs) ? $unlockImages->posterPortraitThumbs : $unlockImages->posterPortrait;
    $unlockPosterIsPortrait = true;
} elseif (!empty($unlockBackdrop) && !ImagesPlaceHolders::isDefaultImage($unlockBackdrop)) {
    $unlockPoster = $unlockBackdrop;
}

$unlockMeta = [];
if (!empty($video['rrating'])) {
    $unlockMeta[] = '<span class="avideoUnlock-rating">' . htmlspecialchars(strtoupper($video['rrating']), ENT_QUOTES, 'UTF-8') . '</span>';
}
$unlockDuration = parseDurationToSeconds($video['duration'] ?? '');
if ($unlockDuration >= 3600) {
    $unlockMeta[] = sprintf('%dh %02dmin', floor($unlockDuration / 3600), floor(($unlockDuration % 3600) / 60));
} elseif ($unlockDuration >= 60) {
    $unlockMeta[] = sprintf('%d min', floor($unlockDuration / 60));
}
if (!empty($video['categories_id'])) {
    $unlockCategory = new Category($video['categories_id']);
    if ($unlockCategory->getName()) {
        $unlockMeta[] = htmlspecialchars($unlockCategory->getName(), ENT_QUOTES, 'UTF-8');
    }
}
$unlockDescription = trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", (string) $video['description'])), ENT_QUOTES, 'UTF-8'));

$unlockTrailer = '';
// Same check as the other trailer buttons: avideoModalIframe() puts the address inside src="...".
if (isValidURL($video['trailer1'])) {
    $unlockTrailer = parseVideos($video['trailer1'], 1, 0, 0, 0, 1);
}

$unlockVideoURL = Video::getLink($videos_id, $video['clean_title']);
$unlockSignInURL = addQueryStringParameter($global['webSiteRootURL'] . 'user', 'redirectUri', $unlockVideoURL);
$unlockIsLogged = User::isLogged();

$_page = new Page(['Unlock this content']);
$_page->setExtraStyles(['view/css/playbackAccessUnlock.css']);
?>
<section class="avideoUnlock" style="--avideo-unlock-image: url(<?php echo htmlspecialchars(json_encode((string) $unlockBackdrop, JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); ?>);">
    <div class="avideoUnlock-backdrop" aria-hidden="true"></div>
    <div class="avideoUnlock-shade" aria-hidden="true"></div>
    <div class="container avideoUnlock-content">
        <div class="avideoUnlock-hero">
            <?php if (!empty($unlockPoster)) { ?>
                <div class="avideoUnlock-poster hidden-xs <?php echo $unlockPosterIsPortrait ? 'avideoUnlock-poster-portrait' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($unlockPoster, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>" />
                    <span class="avideoUnlock-poster-lock" aria-hidden="true"><i class="fas fa-lock"></i></span>
                </div>
            <?php } ?>
            <div class="avideoUnlock-info">
                <div class="avideoUnlock-eyebrow">
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    <span><?php echo __('Unlock this content'); ?></span>
                </div>
                <h1 class="avideoUnlock-title"><?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <?php if (!empty($unlockMeta)) { ?>
                    <ul class="avideoUnlock-meta">
                        <?php foreach ($unlockMeta as $unlockMetaItem) { ?>
                            <li><?php echo $unlockMetaItem; ?></li>
                        <?php } ?>
                    </ul>
                <?php } ?>
                <?php if ($unlockDescription !== '') { ?>
                    <p class="avideoUnlock-description"><?php echo nl2br(htmlspecialchars($unlockDescription, ENT_QUOTES, 'UTF-8')); ?></p>
                <?php } ?>
                <div class="avideoUnlock-actions">
                    <?php if (!$unlockIsLogged) { ?>
                        <a href="<?php echo htmlspecialchars($unlockSignInURL, ENT_QUOTES, 'UTF-8'); ?>" class="avideoUnlock-btn avideoUnlock-btn-primary">
                            <i class="fas fa-sign-in-alt" aria-hidden="true"></i> <?php echo __('Sign In'); ?>
                        </a>
                    <?php } ?>
                    <?php if (!empty($unlockTrailer)) { ?>
                        <button type="button" class="avideoUnlock-btn avideoUnlock-btn-ghost" id="avideoUnlockTrailer" data-trailer="<?php echo htmlspecialchars($unlockTrailer, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-film" aria-hidden="true"></i> <?php echo __('Trailer'); ?>
                        </button>
                    <?php } ?>
                </div>
                <?php if (!$unlockIsLogged) { ?>
                    <p class="avideoUnlock-hint"><?php echo __('Already a member? Sign in to watch.'); ?></p>
                <?php } ?>
            </div>
        </div>

        <?php if (!empty($offers)) { ?>
            <h2 class="avideoUnlock-heading"><?php echo __('Choose how you want to watch'); ?></h2>
            <div class="avideoUnlock-offers">
                <?php foreach ($offers as $offer) { ?>
                    <div class="avideoUnlock-offer <?php echo $offer['featured'] ? 'avideoUnlock-offer-featured' : ''; ?>">
                        <?php if ($offer['featured']) { ?>
                            <span class="avideoUnlock-offer-badge"><?php echo __('Most Popular'); ?></span>
                        <?php } ?>
                        <div class="avideoUnlock-offer-type">
                            <?php if ($offer['type'] === 'rental') { ?>
                                <i class="fas fa-film" aria-hidden="true"></i> <?php echo __('Rent this title'); ?>
                            <?php } elseif ($offer['type'] === 'ppv') { ?>
                                <i class="fas fa-ticket-alt" aria-hidden="true"></i> <?php echo __('Pay-Per-View'); ?>
                            <?php } else { ?>
                                <i class="fas fa-crown" aria-hidden="true"></i> <?php echo __('Subscription'); ?>
                            <?php } ?>
                        </div>
                        <h3 class="avideoUnlock-offer-name"><?php echo htmlspecialchars($offer['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="avideoUnlock-offer-price">
                            <span class="avideoUnlock-offer-amount"><?php echo $unlockFormatPrice($offer['price']); ?></span>
                            <span class="avideoUnlock-offer-period"><?php echo $unlockPeriod($offer); ?></span>
                        </div>
                        <?php
                        // Plan descriptions can be written by users (PPV), so they are shown as text.
                        $unlockOfferDescription = trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", (string) $offer['description'])), ENT_QUOTES, 'UTF-8'));
                        if ($unlockOfferDescription !== '') {
                        ?>
                            <p class="avideoUnlock-offer-description"><?php echo nl2br(htmlspecialchars($unlockOfferDescription, ENT_QUOTES, 'UTF-8')); ?></p>
                        <?php } ?>
                        <a href="<?php echo htmlspecialchars($offer['url'], ENT_QUOTES, 'UTF-8'); ?>" class="avideoUnlock-btn avideoUnlock-btn-primary avideoUnlock-offer-cta">
                            <?php echo $offer['type'] === 'rental' ? __('Rent Now') : ($offer['type'] === 'ppv' ? __('Get Access') : __('Subscribe')); ?>
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="avideoUnlock-empty">
                <i class="fas fa-user-lock" aria-hidden="true"></i>
                <p><?php echo __('This title is available only to members of an authorized group.'); ?></p>
            </div>
        <?php } ?>

        <div class="avideoUnlock-footer">
            <a href="<?php echo htmlspecialchars(getHomePageURL(), ENT_QUOTES, 'UTF-8'); ?>" class="avideoUnlock-link">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> <?php echo __('Back to browse'); ?>
            </a>
        </div>
    </div>
</section>
<?php if (!empty($unlockTrailer)) { ?>
    <script>
        $(function () {
            $('#avideoUnlockTrailer').on('click', function () {
                avideoModalIframe($(this).data('trailer'));
            });
        });
    </script>
<?php } ?>
<?php
$_page->print();
?>
