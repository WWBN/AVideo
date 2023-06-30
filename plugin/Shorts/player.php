<?php
$removeAnimation = false;

$class = "animate__animated animate__bounceInLeft";
$shortsOpen = "$('#ShortsPlayerContent').removeClass('animate__bounceOutLeft');$('#ShortsPlayerContent').addClass('animate__bounceInLeft');";
$shortsClose = "$('#ShortsPlayerContent').addClass('animate__bounceOutLeft').one('animationend', function() { $(this).hide();});";

if ($removeAnimation) {
    $class = "";
    $shortsOpen = "";
    $shortsClose = "$('#ShortsPlayerContent').hide();";
}
?>
<style>
    .ShortsPlayerOverlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: calc(50% - 60px);
        cursor: move;
    }

    .playingShorts .scrtabs-tab-container {
        display: none;
    }

    body.playingShorts {
        overflow: hidden;
    }

    #ShortsPlayerContent {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 9999;
        background: #000;
        overflow: hidden;
        width: 100%;
        height: 100%;
    }

    #ShortsPlayer.carousel {
        position: fixed;
        top: 0;
        left: 0;
        background: #000;
        overflow: hidden;
        width: 100vw;
        height: 100vh;
    }

    #ShortsPlayer .carousel-cell {
        width: 100%;
        margin-right: 10px;
        background: #8C8;
        border-radius: 5px;
        counter-increment: carousel-cell;
        height: 100vh !important;
    }

    #ShortsPlayer .carousel-cell-content {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-size: cover;
        background-position: center;
        cursor: pointer;
        height: 100vh !important;
    }

    #ShortsPlayer .carousel-cell.is-selected {
        background: #000;
    }

    /* cell number */
    #ShortsPlayer .carousel-cell:before {
        display: block;
        text-align: center;
        content: counter(carousel-cell);
        line-height: 200px;
        font-size: 80px;
        color: white;
        padding-top: 56.25%;
    }

    #ShortsPlayer .carousel-cell .carousel-cell-content strong {
        position: absolute;
        bottom: 0;
        padding: 5px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        width: 100%;
    }

    #closeCarousel {
        width: 40px;
        /* adjust size as needed */
        height: 40px;
        /* adjust size as needed */
        border-radius: 50%;
        /* make it round */
        background: rgba(255, 255, 255, 0.7);
        /* white with 50% transparency */
        display: flex;
        /* center the icon */
        justify-content: center;
        align-items: center;
        transition: background 0.3s ease;
        border: none;
    }

    #closeCarousel:hover {
        background: rgba(255, 255, 255, 0.9);
        /* white with 70% transparency */
    }

    #closeCarousel i {
        font-size: 2em;
        /* adjust size as needed */
        color: #333
    }
</style>
<div style="display: none;" class="<?php echo $class; ?>" id="ShortsPlayerContent">
    <div class="carousel" id="ShortsPlayer"></div>
    <button id="closeCarousel" style="position: absolute; z-index: 9999; right: 10px; top: 10px;">
        <i class="fas fa-times"></i>
    </button>
</div>
<script>
    var currentCell;
    var shortIsOpen = false;

    function shortsOpen(index) {
        shortIsOpen = true;
        $('body').addClass('playingShorts');
        <?php echo $shortsOpen; ?>
        console.log('shortsPlay', index);
        $('#ShortsPlayerContent').show();
        $('#ShortsPlayer').flickity('destroy');
        createShortsPlayerFlickity(index);
        $('#ShortsPlayer').flickity('select', index);
    }

    function shortsClose() {
        shortIsOpen = false;
        $('body').removeClass('playingShorts');
        <?php echo $shortsClose; ?>
        if (typeof currentCell != 'undefined') {
            currentCell.html('');
        }
    }

    function populateCarouselPlayer(video) {
        var $carouselPlayer = $('#ShortsPlayer');
        var newCarouselCell = $('<div>').addClass('carousel-cell');
        var newCarouselCellContent = $('<div>').addClass('carousel-cell-content').attr('data-video-url', video.videoLink).css('background-image', 'url(' + video.images.posterLandscapeThumbs + ')');
        newCarouselCellContent.append($('<strong>').text(video.title));
        newCarouselCell.append(newCarouselCellContent);
        $carouselPlayer.flickity('append', newCarouselCell);
    }
    var isSettling = false;
    var timeoutId = null;

    function createShortsPlayerFlickity(initialIndex) {
        var $carouselPlayer = $('#ShortsPlayer');

        $carouselPlayer.flickity({
            fullscreen: true,
            contain: true,
            pageDots: false,
            initialIndex: initialIndex,
        });

        $carouselPlayer.on('settle.flickity', function(event, index) {
            if (isSettling) {
                return;
            }
            isSettling = true;

            if (timeoutId !== null) {
                clearTimeout(timeoutId);
            }

            timeoutId = setTimeout(function() {
                if (typeof currentCell != 'undefined') {
                    currentCell.html('');
                }
                var index2 = $('#ShortsPlayer .carousel-cell.is-selected').index();
                index = index2;
                console.log('Flickity settled at ', index2, shortVideos[index2]);
                var src = 'about:blank';
                if (shortIsOpen) {
                    src = addQueryStringParameter(shortVideos[index2].embedlink, 'autoplay', 1);
                }
                var iframe = $('<iframe/>', {
                    // The attributes for the iframe
                    width: '100vw',
                    height: '100vh',
                    frameborder: 0,
                    src: src
                });
                var overlay = $('<div/>', {
                    // The attributes for the overlay
                    class: 'ShortsPlayerOverlay',
                    click: function() {
                        $(this).hide();
                    }
                });
                currentCell = $('#ShortsPlayer .carousel-cell.is-selected .carousel-cell-content');
                currentCell.html(iframe);
                currentCell.append(overlay); // Add the overlay to the cell

                // After 300 milliseconds, allow the event to trigger again
                isSettling = false;
                timeoutId = null; // reset the timeoutId
            }, 300);
        });

        $carouselPlayer.on('change.flickity', function(event, index) {
            console.log('Slide changed to ' + index)
        });

        $carouselPlayer.on('select.flickity', function(event, index) {
            console.log('Flickity select ' + index)
        });

    }


    $(document).ready(function() {
        createShortsPlayerFlickity();
        $('#closeCarousel').on('click', function() {
            shortsClose();
        });
    });
</script>