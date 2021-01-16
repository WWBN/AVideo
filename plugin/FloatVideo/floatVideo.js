
function closeFloatVideo() {
    $('#videoContainer').fadeOut('fast', function () {
// this is to remove the dragable and resize
        floatLeft = $("#videoContainer").css("left");
        floatTop = $("#videoContainer").css("top");
        floatWidth = $("#videoContainer").css("width");
        floatHeight = $("#videoContainer").css("height");
        $("#videoContainer").css({"top": ""});
        $("#videoContainer").css({"left": ""});
        $("#videoContainer").css({"height": ""});
        $("#videoContainer").css({"width": ""});
        $('#videoContainer').parent().css('height', '');
        $('#videoContainer').removeClass('floatVideo');
        $("#videoContainer").resizable('destroy');
        $("#videoContainer").draggable('destroy');
        $('#floatButtons').hide();
        changingVideoFloat = 0;
    });
    $('#videoContainer').fadeIn();
}

function setFloatVideo() {
    if (!videoContainerDragged) {
        if (!floatLeft || parseInt(floatLeft) < 10 || parseInt(floatLeft) === 310) {
            floatLeft = "10px";
        }
        if (parseInt(floatLeft) === 10 && youTubeMenuIsOpened) {
            floatLeft = "310px";
        }
        $("#videoContainer").css({"left": floatLeft});
    }
    if (!$('#videoContainer').hasClass("floatVideo") && !floatClosed) {
        $('#videoContainer').hide();
        $('#videoContainer').addClass('floatVideo');
        $('#videoContainer').parent().css('height', mainVideoHeight);
        if (parseInt(floatTop) < 70) {
            floatTop = "70px";
        }
        $("#videoContainer").css({"top": floatTop});
        $("#videoContainer").css({"height": floatHeight});
        $("#videoContainer").css({"width": floatWidth});
        $("#videoContainer").resizable({
            aspectRatio: 16 / 9,
            minHeight: 150,
            minWidth: 266
        });
        $("#videoContainer").draggable({
            handle: ".move",
            containment: ".principalContainer",
            drag: function () {
                videoContainerDragged = true;
            }
        });
        changingVideoFloat = 0;
        $('#videoContainer').fadeIn();
        $('#floatButtons').fadeIn();
    } else {
        changingVideoFloat = 0;
    }
}

var setFloatVideoYouTubeMenuIsOpened;
$(document).ready(function () {
    mainVideoHeight = $('#videoContainer').innerHeight();
    $(window).resize(function () {
        mainVideoHeight = $('#videoContainer').innerHeight();
    });
    $(window).scroll(function () {
        if (changingVideoFloat) {
            return false;
        }
        changingVideoFloat = 1;
        var s = $(window).scrollTop();
        //console.log("$(window).scrollTop()= " + s);
        //console.log("mainVideoHeight = $('#videoContainer').innerHeight()= " + mainVideoHeight);
        if (s > mainVideoHeight) {
            setFloatVideo();
        } else {
            floatClosed = 0;
            if ($('#videoContainer').hasClass("floatVideo")) {
                closeFloatVideo();
            } else {
                changingVideoFloat = 0;
            }
        }
    });

    setInterval(function () {
        if (setFloatVideoYouTubeMenuIsOpened === youTubeMenuIsOpened || !$('#videoContainer').hasClass("floatVideo")) {
            return false;
        }
        setFloatVideoYouTubeMenuIsOpened = youTubeMenuIsOpened;
        setFloatVideo();
    }, 1000);
});