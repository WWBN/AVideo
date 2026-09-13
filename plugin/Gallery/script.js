function channelToGallery(users_id, add) {
    $.ajax({
        url: webSiteRootURL + 'plugin/Gallery/channelToGallery.json.php',
        method: 'POST',
        data: {'users_id': users_id, 'add': add, 'globalToken': galleryChannelToken},
        success: function (response) {
            avideoResponse(response);
            if(!response.error){
                if(response.add){
                    $('.ChannelToGallery'+response.users_id).addClass('isChannelToGallery');
                }else{
                    $('.ChannelToGallery'+response.users_id).removeClass('isChannelToGallery');
                }
            }
        }
    });
}

$(function () {
    var featuredCarousel = $('#bigVideoCarousel.gallery-featured');
    if (!featuredCarousel.hasClass('carousel')) {
        return;
    }
    function updateFeaturedIndicators() {
        featuredCarousel.find('.carousel-indicators button').each(function () {
            $(this).attr('aria-current', $(this).parent().hasClass('active') ? 'true' : 'false');
        });
        lazyImage();
    }
    updateFeaturedIndicators();
    featuredCarousel.on('slid.bs.carousel', updateFeaturedIndicators);
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        featuredCarousel.carousel({interval: false});
    }
    featuredCarousel.on('slide.bs.carousel', function (event) {
        // Do not move a slide away while its menu or keyboard controls are in use.
        if ($(this).find('.item.active .open').length ||
            $(document.activeElement).closest('#bigVideoCarousel .item.active').length) {
            event.preventDefault();
        }
    });
});
