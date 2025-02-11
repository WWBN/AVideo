<?php
global $advancedCustom;
$removeAnimation = false;

$class = "animate__animated animate__bounceInLeft";
$shortsOpen = "$('#ShortsPlayerContent').removeClass('animate__bounceOutLeft').addClass('animate__bounceInLeft');";
$shortsClose = "$('#ShortsPlayerContent').removeClass('animate__bounceInLeft').addClass('animate__bounceOutLeft');";

if ($removeAnimation || !empty($advancedCustom->disableAnimations)) {
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
        width: calc(100% - 60px);
        height: calc(50% - 60px);
        cursor: move;
    }


    body.playingShorts {
        overflow: hidden;
    }

    body.playingShorts .hideOnPlayShorts {
        display: none;
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

    .circleCarouselBtn {
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
        margin-bottom: 15px;
    }

    .circleCarouselBtn:hover {
        background: rgba(255, 255, 255, 0.9);
        /* white with 70% transparency */
    }

    .circleCarouselBtn i {
        font-size: 1.7em;
        /* adjust size as needed */
        color: #333
    }

    .circleCarouselBtn.active {
        background: rgba(255, 255, 255, 0.85);
        box-shadow: 0 0 0 5px #19f;
    }

    .circleCarouselBtn.active i {
        color: #111
    }

    #buttonsCarousel {
        position: fixed;
        z-index: 9999;
        right: 10px;
        bottom: 70px;
    }

    #closeCarousel {
        position: fixed;
        z-index: 9999;
        right: 10px;
        top: 10px;
    }

    #buttonsCarousel .circleCarouselBtn {
        position: relative;
    }

    .circleCarouselBtn .votes {
        position: absolute;
        top: -5px;
        left: -5px;
    }

    @media (max-height: 600px) {
        #buttonsCarousel {
            bottom: 50px;
        }

        .circleCarouselBtn {
            width: 35px;
            height: 35px;
            margin-bottom: 5px;
        }
    }
</style>
<div style="display: none;" class="<?php echo $class; ?>" id="ShortsPlayerContent">
    <div class="carousel" id="ShortsPlayer"></div>
    <button id="closeCarousel" class="circleCarouselBtn">
        <i class="fas fa-times"></i>
    </button>
    <div id="buttonsCarousel">
        <?php
        if (empty($advancedCustom->removeThumbsUpAndDown)) {
        ?>
            <button id="likeCarousel" class="circleCarouselBtn" onclick="carouselPlayerLike()">
                <i class="fas fa-thumbs-up"></i>
                <br>
                <span class="votes badge"></span>
            </button>
            <button id="dislikeCarousel" class="circleCarouselBtn" onclick="carouselPlayerDislike()">
                <i class="fas fa-thumbs-down"></i>
                <br>
                <span class="votes badge"></span>
            </button>
        <?php
        }
        if (isShareEnabled()) {
        ?>
            <button id="shareCarousel" class="circleCarouselBtn" onclick="shareCarouselShorts();">
                <i class="fas fa-share"></i>
            </button>
            <script>
                function shareCarouselShorts() {
                    $('.ShortsPlayerOverlay').hide();
                    iframe[0].contentWindow.postMessage('togglePlayerSocial', '*');
                }
            </script>
        <?php
        }
        if (User::canComment() && false) {
        ?>
            <button id="commentCarousel" class="circleCarouselBtn">
                <i class="fas fa-comment"></i>
            </button>
        <?php
        }
        ?>
    </div>
</div>
<script>
    var currentCell;
    var shortIsOpen = false;
    var currentCarouselPlayerVideo;

    function carouselPlayerGetLikes() {
        if (shortIsOpen) {
            var videos_id = currentCarouselPlayerVideo.id;
            var url = webSiteRootURL + 'plugin/API/get.json.php?APIName=likes&videos_id=' + videos_id;
            console.log('carouselPlayerGetLikes', url);
            carouselPlayerResetLikesResponse();
            $.ajax({
                url: url,
                success: function(response) {
                    carouselPlayerProcessLikesResponse(response);
                }
            });
        } else {
            console.log('carouselPlayerGetLikes else', shortIsOpen);
        }
    }

    function carouselPlayerProcessLikesResponse(response) {
        if (response.error) {
            avideoAlertError(response.msg);
        } else {
            avideoToastSuccess(response.msg);
            if (typeof response.response !== 'undefined') {
                try {
                    $('#likeCarousel .votes').text(response.response.likes);
                    $('#dislikeCarousel .votes').text(response.response.dislikes);
                    $('#likeCarousel, #dislikeCarousel').removeClass('active');
                    if (response.response.myVote == 1) {
                        $('#likeCarousel').addClass('active');
                    } else if (response.response.myVote == -1) {
                        $('#dislikeCarousel').addClass('active');
                    }
                    console.log('carouselPlayerProcessLikesResponse response.response', response.response);
                } catch (e) {
                    console.log('carouselPlayerProcessLikesResponse ERROR response.response', response.response);
                }


            }
        }
    }

    function carouselPlayerResetLikesResponse() {
        $('#likeCarousel .votes').text(0);
        $('#dislikeCarousel .votes').text(0);
        $('#likeCarousel, #dislikeCarousel').removeClass('active');
    }

    function carouselPlayerLike() {
        carouselPlayerLikeDislike('like');
    }

    function carouselPlayerDislike() {
        carouselPlayerLikeDislike('dislike');
    }

    function carouselPlayerLikeDislike(APIName) {
        if (shortIsOpen) {
            modal.showPleaseWait();
            console.log('currentCarouselPlayerVideo', currentCarouselPlayerVideo);
            var videos_id = currentCarouselPlayerVideo.id;
            var url = webSiteRootURL + 'plugin/API/set.json.php?APIName=' + APIName + '&videos_id=' + videos_id;

            $.ajax({
                url: url,
                success: function(response) {
                    carouselPlayerProcessLikesResponse(response)
                },
                complete: function(response) {
                    modal.hidePleaseWait();
                }
            });
        }
    }

    function shortsOpen(index) {
        shortIsOpen = true;
        $('body').addClass('playingShorts');
        <?php echo $shortsOpen; ?>
        console.log('shortsPlay', index);
        $('#ShortsPlayerContent').show();
        $('#ShortsPlayer').flickity('destroy');
        createShortsPlayerFlickity(index);
        $('#ShortsPlayer').flickity('select', index);
        currentShortsPlayerIndex = -1;
    }

    function shortsClose() {
        shortIsOpen = false;
        currentShortsPlayerIndex = -1;
        if (typeof currentCell != 'undefined') {
            currentCell.html('');
        }
        console.log('shortsClose 1');
        setTimeout(function() {
            console.log('shortsClose 2');
            $('body').removeClass('playingShorts');
            <?php echo $shortsClose; ?>
            $('#ShortsPlayerContent').hide();
            $('#ShortsPlayerContent').removeClass('animate__bounceOutLeft');
        }, 100);
    }

    function populateCarouselPlayer(video) {
        var $carouselPlayer = $('#ShortsPlayer');
        var newCarouselCell = $('<div>').addClass('carousel-cell');
        var newCarouselCellContent = $('<div>')
            .addClass('carousel-cell-content')
            .attr('data-flickity-bg-lazyload', video.images.poster);
        newCarouselCellContent.append($('<strong>').text(video.title));
        newCarouselCell.append(newCarouselCellContent);
        $carouselPlayer.flickity('append', newCarouselCell);
    }

    function resetPlayerFlickity() {
        isSettling = false;
        timeoutId = null;
    }

    var isSettling = false;
    var timeoutId = null;
    var iframe;
    var currentShortsPlayerIndex = -1;

    function playNextShorts() {
        $('#ShortsPlayer').flickity('next');
    }

    function createShortsPlayerFlickity(initialIndex) {
        var $carouselPlayer = $('#ShortsPlayer');

        $carouselPlayer.flickity({
            fullscreen: true,
            contain: true,
            pageDots: false,
            initialIndex: initialIndex,
            bgLazyLoad: true,
            adaptiveHeight: true,
            cellSelector: '.carousel-cell',
        });
        $carouselPlayer.on('scroll.flickity', function(event, progress) {
            if (typeof currentCell != 'undefined') {
                currentCell.html('');
            }
            if (progress > 0.7) {
                loadShorts();
            }
        });
        $carouselPlayer.on('settle.flickity', function(event, index) {
            if (isSettling) {
                return;
            }
            isSettling = true;

            if (timeoutId !== null) {
                clearTimeout(timeoutId);
            }

            if (typeof currentCell != 'undefined') {
                currentCell.html('');
            }
            timeoutId = setTimeout(function() {
                var index2 = $('#ShortsPlayer .carousel-cell.is-selected').index();
                if (currentShortsPlayerIndex !== index2) {
                    carouselPlayerGetLikes();
                }
                currentShortsPlayerIndex = index2;
                currentCarouselPlayerVideo = shortVideos[index2];
                index = index2;
                var src = 'about:blank';
                if (shortIsOpen) {
                    src = addQueryStringParameter(shortVideos[index2].embedlink, 'autoplay', 1);
                    src = addQueryStringParameter(src, 'showBigButton', 1);
                }
                console.log('Flickity settled at ', index2, src);
                iframe = $('<iframe/>', {
                    // The attributes for the iframe
                    width: '100vw',
                    height: '100vh',
                    frameborder: 0,
                    src: src
                });
                var overlay = $('<div/>', {
                    // The attributes for the overlay
                    class: 'ShortsPlayerOverlay',
                    /*
                    click: function () {
                        $(this).hide();
                    }
                     */
                });
                currentCell = $('#ShortsPlayer .carousel-cell.is-selected .carousel-cell-content');
                currentCell.html(iframe);
                currentCell.append(overlay); // Add the overlay to the cell

            }, 300);
            resetPlayerFlickity(); // reset the timeoutId
        });

        $carouselPlayer.on('change.flickity', function(event, index) {
            console.log('Slide changed to ' + index)
        });

        $carouselPlayer.on('select.flickity', function(event, index) {
            if (shortIsOpen) {
                var browserWidth = $(window).width();
                <?php
                foreach ($totalFlickityCells as $key => $value) {
                    if (empty($key)) {
                        echo "var newIndex = Math.floor(index/{$value});";
                    } else {
                        echo "if(browserWidth<{$key}){newIndex = Math.floor(index/{$value});}";
                    }
                }
                ?>
                $('#Shorts').flickity('select', newIndex);
                console.log('Flickity select ' + index)
            }
        });

    }


    $(document).ready(function() {
        createShortsPlayerFlickity();
        $('#closeCarousel').on('click', function() {
            shortsClose();
        });
    });
    // Event listener to receive messages from the iframe
    window.addEventListener('message', function(event) {
        if (event.data === 'playNextShorts') {
            // Call the function
            playNextShorts();
        } else {
            // Execute another code if the function doesn't exist
            console.log("playNextShorts() does not exist in the parent page");
        }
    });
</script>
