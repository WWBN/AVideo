
var FloatVideoTimeout;
var floatClosed = 0;
function closeFloatVideo() {
    if(!$('body').hasClass('floatVideo')){
        return false;
    }
    clearTimeout(FloatVideoTimeout);
    setTimeout(function () {
        $('#videoCol').css('height', '');
        $('#videoContainer').removeClass('animate__animated');
        $('#videoContainer').removeClass('animate__bounceInDown');
        $('body').removeClass('floatVideo');
    }, 100);
}

function setFloatVideo() {
    if(floatClosed){
        return false;
    }
    if($('body').hasClass('floatVideo')){
        return false;
    }
    clearTimeout(FloatVideoTimeout);
    setTimeout(function () {
        var videoContainerHeight = $('#videoContainer').height();
        $('#videoCol').height(videoContainerHeight);
        $('#videoContainer').addClass('animate__animated');
        $('#videoContainer').addClass('animate__bounceInDown');
        $('body').addClass('floatVideo');
    }, 100);
}

$(function () {
    // Function to handle scroll event
    function handleScroll() {
        var element = $('#videoCol'); // Replace 'your-element-id' with the ID of your target element
        if (element.isVisible()) {
            closeFloatVideo();
        } else {
            setFloatVideo();
        }
    }

    // Bind the handleScroll function to the scroll event
    $(window).scroll(handleScroll);
});