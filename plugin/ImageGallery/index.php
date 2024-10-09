<?php

require_once '../../videos/configuration.php';


if (!AVideoPlugin::isEnabledByName('ImageGallery')) {
    forbiddenPage('ImageGallery plugin is disabled');
}

$videos_id = getVideos_id();
ImageGallery::dieIfIsInvalid($videos_id);

//global $isEmbed;
//$isEmbed = 1;
//$global['bypassSameDomainCheck'] = 1;
User::loginFromRequestIfNotLogged();

$video = Video::getVideo($videos_id, Video::SORT_TYPE_VIEWABLE, false, false, false, true);
Video::unsetAddView($video['id']);

AVideoPlugin::getEmbed($video['id']);

if (empty($video)) {
    $msg = __('Video not found');
    if (User::isAdmin()) {
        $msg = "{$msg} " . json_encode($_GET);
    }
    forbiddenPage($msg);
}
if ($video['status'] == 'i') {
    forbiddenPage("Video inactive");
}
if (empty($video['users_id'])) {
    $video['users_id'] = User::getId();
}
if (empty($customizedAdvanced)) {
    $customizedAdvanced = AVideoPlugin::getObjectDataIfEnabled('CustomizeAdvanced');
}

forbiddenPageIfCannotEmbed($video['id']);

require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

$obj = new Video("", "", $video['id']);
$resp = $obj->addView();


$list = ImageGallery::listFiles($videos_id);

if (User::hasBlockedUser($video['users_id'])) {
    $video['type'] = "blockedUser";
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">

<head>
    <meta name="robots" content="noindex">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?php echo $config->getFavicon(); ?>">
    <title><?php echo $video['title'] . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
    <link href="<?php echo getURL('view/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css" />

    <link href="<?php echo getURL('node_modules/@fortawesome/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css" />

    <link href="<?php echo getURL('node_modules/jquery-toast-plugin/dist/jquery.toast.min.css'); ?>" rel="stylesheet" type="text/css" />

    <script src="<?php echo getURL('node_modules/jquery/dist/jquery.min.js'); ?>" type="text/javascript"></script>

    <link href="<?php echo getURL('node_modules/glightbox/dist/css/glightbox.min.css'); ?>" rel="stylesheet" type="text/css" />

    <script src="<?php echo getURL('node_modules/glightbox/dist/js/glightbox.min.js'); ?>" type="text/javascript"></script>

    <link href="<?php echo getURL('node_modules/flickity/dist/flickity.min.css'); ?>" rel="stylesheet" type="text/css" />
    <script src="<?php echo getURL('node_modules/flickity/dist/flickity.pkgd.min.js'); ?>" type="text/javascript"></script>
    <style>
        body {
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden;
            background-color: #000;
        }

        #blockUserTop {
            position: absolute;
            right: 25px;
            top: 25px;
        }

        .carousel-cell {
            width: 66%;
            height: 100vh;
            margin-right: 10px;
            background: #CCCCCC11;
            border-radius: 5px;
            counter-increment: carousel-cell;
        }

        .gallery-cell {
            display: flex;
            /* Use flexbox to center contents */
            justify-content: center;
            /* Center horizontally */
            align-items: center;
            /* Center vertically, if desired */
        }

        .gallery-cell img {
            /* If you need additional styling for the image, add it here */
            max-width: 100%;
            /* Ensure image is responsive */
            height: auto;
            /* Maintain aspect ratio */
        }
    </style>
    <?php
    include $global['systemRootPath'] . 'view/include/head.php';
    getOpenGraph($video['id']);
    getLdJson($video['id']);
    ?>
    <script src="<?php echo getURL('node_modules/js-cookie/dist/js.cookie.js'); ?>" type="text/javascript"></script>

</head>

<body>
    <div id="imageGallery" class="gallery js-flickity" data-flickity-options='{ "wrapAround": true }'>
        <!-- Dynamic items will be injected here -->
    </div>

    <script>
        var dragEndFlickityTimeout;
        var flkty;
        $(document).ready(function() {
            var galleryList = <?php echo json_encode($list); ?>;
            var isDragging = false;

            var lightboxItems = [];
            $.each(galleryList, function(index, item) {
                var cell = $('<div class="carousel-cell gallery-cell"></div>');
                var contentType = item.type.split('/')[0];
                var content;

                if (contentType === 'image') {
                    content = $('<img src="' + item.url + '" class="img-responsive" alt="Gallery Image">');
                } else if (contentType === 'video') {
                    content = $('<video class="img-responsive" controls><source src="' + item.url + '" type="' + item.type + '"></video>');
                }

                cell.append(content);
                $('#imageGallery').append(cell);

                // Prepare GLightbox items
                lightboxItems.push({
                    href: item.url,
                    type: contentType,
                    source: item.url
                });
            });

            var $carousel = $('#imageGallery').flickity({
                contain: true,
                wrapAround: true,
                pageDots: false,
                prevNextButtons: true,
                autoPlay: false,
                lazyLoad: 1
            });

            flkty = $carousel.data('flickity');

            // Monitor drag start
            $carousel.on('dragStart.flickity', function() {
                console.log('dragStart.flickity');
                isDragging = true;
            });

            // Initialize GLightbox
            var lightbox = GLightbox({
                elements: lightboxItems,
                onOpen: function() {
                    // Attempt to enter fullscreen when the lightbox opens
                    var lightboxContent = document.querySelector('.glightbox-container');
                    if (lightboxContent.requestFullscreen) {
                        lightboxContent.requestFullscreen();
                    } else if (lightboxContent.mozRequestFullScreen) {
                        /* Firefox */
                        lightboxContent.mozRequestFullScreen();
                    } else if (lightboxContent.webkitRequestFullscreen) {
                        /* Chrome, Safari & Opera */
                        lightboxContent.webkitRequestFullscreen();
                    } else if (lightboxContent.msRequestFullscreen) {
                        /* IE/Edge */
                        lightboxContent.msRequestFullscreen();
                    }
                }
            });


            // Click event to open GLightbox
            $('#imageGallery').on('click', '.gallery-cell', function(e) {
                if (!isDragging) {
                    // Find the index of the clicked item in the lightboxItems array
                    var href = $(this).find('img, video').attr('src'); // Assuming the src attribute for images and videos
                    var lightboxIndex = lightboxItems.findIndex(item => item.href === href);
                    if (lightboxIndex !== -1) {
                        lightbox.openAt(lightboxIndex);
                    }
                }
                // Use a timeout to reset isDragging to ensure it's not reset before the dragEnd event can set it
                clearTimeout(dragEndFlickityTimeout);
                dragEndFlickityTimeout = setTimeout(() => {
                    isDragging = false;
                }, 100);
            });


            // Reset isDragging on drag end
            $carousel.on('dragEnd.flickity', function() {
                clearTimeout(dragEndFlickityTimeout);
                dragEndFlickityTimeout = setTimeout(() => {
                    console.log('dragEnd.flickity');
                    isDragging = false;
                }, 1000);
            });
        });
    </script>


    <?php
    showCloseButton();
    ?>
</body>

</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>