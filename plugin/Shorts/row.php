<?php
$totalFlickityCells = array('0' => 5, '992' => 4, '767' => 2);

$Gobj = AVideoPlugin::getDataObjectIfEnabled('Gallery');
if(!empty($Gobj->ShortsCustomTitle)){
    $Gtitle = __($Gobj->ShortsCustomTitle);
}else{
    $Gtitle = __('Shorts');
}
?>
<link href="<?php echo getURL('node_modules/flickity/dist/flickity.min.css'); ?>" rel="stylesheet" type="text/css" />
<style>
    #Shorts.carousel {
        background: #99999922;
        margin-bottom: 30px;
        display: none;
    }
    #Shorts.carousel.flickity-enabled {
        display: block;
    }
    
    #Shorts.carousel .flickity-page-dots {
        bottom: -35px;
    }

    #Shorts .carousel-cell {
        margin-right: 10px;
        border-radius: 5px;
        counter-increment: carousel-cell;
        border-radius: 10px;
    }

    #Shorts .carousel-cell-content {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-size: cover;
        background-position: center;
        cursor: pointer;
        border-radius: 10px;
    }

    /* cell number */
    #Shorts .carousel-cell:before {
        display: block;
        text-align: center;
        content: counter(carousel-cell);
        line-height: 200px;
        font-size: 80px;
        color: transparent;
        padding-top: 56.25%;
    }

    #Shorts .carousel-cell .carousel-cell-content strong {
        position: absolute;
        bottom: 0;
        padding: 5px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        width: 100%;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    <?php
    foreach ($totalFlickityCells as $key => $value) {
        if (!empty($key)) {
            echo "@media (max-width: {$key}px) {";
        }
        $width = floor(100 / $value) - 2;
        echo "#Shorts .carousel-cell {width: {$width}%;}";
        if (!empty($key)) {
            echo '}';
        }
    }
    ?>
</style>
<script src="<?php echo getURL('node_modules/flickity/dist/flickity.pkgd.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('node_modules/flickity-bg-lazyload/bg-lazyload.js'); ?>" type="text/javascript"></script>
<h3 class="galleryTitle"><i class="fas fa-layer-group"></i> <?php echo $Gtitle; ?></h3>
<div class="carousel" id="Shorts"></div>
<script>
    var isLoadingShorts = false;
    var hasMoreShorts = true;
    var shortPage = 1;
    var shortVideos = [];
    var isDraggingShorts = false;

    function loadShorts() {
        if (isLoadingShorts || !hasMoreShorts) {
            return;
        }

        isLoadingShorts = true;
        var current = shortPage++;
        $.ajax({
            url: webSiteRootURL + 'plugin/Shorts/row.json.php?current=' + current,
            success: function (response) {
                if (response.data.length === 0) {
                    hasMoreShorts = false;
                    return;
                }
                //$('#carouselContent').show();
                var $carousel = $('#Shorts');
                for (var key in response.data) {
                    var video = response.data[key];
                    if (typeof video != 'object') {
                        continue;
                    }
                    shortVideos.push(video);
                    populateCarouselPlayer(video);
                    imageUrl = video.images.poster;
                    var newCarouselCell = $('<div>').addClass('carousel-cell');
                    var newCarouselCellContent = $('<div>')
                            .addClass('carousel-cell-content')
                            .attr('data-flickity-bg-lazyload', imageUrl);
                    newCarouselCellContent.append($('<strong>').text(video.title));
                    newCarouselCell.append(newCarouselCellContent);
                    // append the new cell to the carousel
                    $carousel.flickity('append', newCarouselCell);

                }

                isLoadingShorts = false;
            }
        });
    }

    function createShortsFlickity() {
        var $carousel = $('#Shorts');

        $carousel.flickity({
            groupCells: true,
            bgLazyLoad: true
        });

        $carousel.on('scroll.flickity', function (event, progress) {
            if (progress > 0.7) {
                loadShorts();
            }
        });
        $carousel.on('click', '.carousel-cell-content', function () {
            console.log('carousel click');
            if (!isDraggingShorts) {
                var index = $(this).parents('.carousel-cell').index();
                shortsOpen(index);
            } else {
                console.log('carousel is dragging');
            }
            ;
        });
        $carousel.on('dragStart.flickity', function (event, pointer) {
            isDraggingShorts = true;
            console.log('carousel dragStart');
        });
        $carousel.on('dragEnd.flickity', function (event, pointer) {
            setTimeout(function () {
                isDraggingShorts = false;
                console.log('carousel dragEnd');
            }, 500);
        });
    }

    $(document).ready(function () {
        createShortsFlickity();
        loadShorts();
    });
</script>
<?php
include $global['systemRootPath'] . 'plugin/Shorts/player.php';
?>