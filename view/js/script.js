var modal;
var player;

var floatLeft = "";
var floatTop = "";
var floatWidth = "";
var floatHeight = "";

var changingVideoFloat = 0;
var floatClosed = 0;
var fullDuration = 0;
var isPlayingAd = false;

var mainVideoHeight = 0;

String.prototype.stripAccents = function () {
    var translate_re = /[àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ]/g;
    var translate = 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY';
    return (this.replace(translate_re, function (match) {
        return translate.substr(translate_re.source.indexOf(match) - 1, 1);
    })
            );
};
function clean_name(str) {

    str = str.stripAccents().toLowerCase();
    return str.replace(/\W+/g, "-");
}

$(document).ready(function () {
    modal = modal || (function () {
        var pleaseWaitDiv = $("#pleaseWaitDialog");
        if (pleaseWaitDiv.length === 0) {
            pleaseWaitDiv = $('<div id="pleaseWaitDialog" class="modal fade"  data-backdrop="static" data-keyboard="false"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"><h2>Processing...</h2><div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div></div></div></div></div>').appendTo('body');
        }

        return {
            showPleaseWait: function () {
                pleaseWaitDiv.modal();
            },
            hidePleaseWait: function () {
                pleaseWaitDiv.modal('hide');
            },
            setProgress: function (valeur) {
                pleaseWaitDiv.find('.progress-bar').css('width', valeur + '%').attr('aria-valuenow', valeur);
            },
            setText: function (text) {
                pleaseWaitDiv.find('h2').html(text);
            },
        };
    })();

    $('[data-toggle="popover"]').popover();
    $('[data-toggle="tooltip"]').tooltip();

    $(".thumbsImage").on("mouseenter", function () {
        $(this).find(".thumbsGIF").height($(this).find(".thumbsJPG").height());
        $(this).find(".thumbsGIF").width($(this).find(".thumbsJPG").width());
        $(this).find(".thumbsGIF").stop(true, true).fadeIn();
    });

    $(".thumbsImage").on("mouseleave", function () {
        $(this).find(".thumbsGIF").stop(true, true).fadeOut();
    });

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
        if (s > mainVideoHeight) {
            if (!$('#videoContainer').hasClass("floatVideo") && !floatClosed) {
                $('#videoContainer').hide();
                $('#videoContainer').addClass('floatVideo');
                $('#videoContainer').parent().css('height', mainVideoHeight);
                if (parseInt(floatTop) < 70) {
                    floatTop = "70px";
                }
                if (parseInt(floatLeft) < 10) {
                    floatLeft = "10px";
                }
                $("#videoContainer").css({"top": floatTop});
                $("#videoContainer").css({"left": floatLeft});
                $("#videoContainer").css({"height": floatHeight});
                $("#videoContainer").css({"width": floatWidth});

                $("#videoContainer").resizable({
                    aspectRatio: 16 / 9,
                    minHeight: 150,
                    minWidth: 266
                });
                $("#videoContainer").draggable({
                    handle: ".move",
                    containment: ".principalContainer"
                });
                changingVideoFloat = 0;
                $('#videoContainer').fadeIn();
                $('#floatButtons').fadeIn();
            } else {
                changingVideoFloat = 0;
            }
        } else {
            floatClosed = 0;
            if ($('#videoContainer').hasClass("floatVideo")) {
                closeFloatVideo();
            } else {
                changingVideoFloat = 0;
            }
        }
    });
});
function changeVideoSrc(vid_obj, fileName) {
    vid_obj.src([
        {type: "video/mp4", src: fileName + ".mp4"},
        {type: "video/webm", src: fileName + ".webm"}
    ]);
    vid_obj.load();
    vid_obj.play();
}

/**
 * 
 * @param {String} str 00:00:00
 * @returns {int} int of seconds
 */
function strToSeconds(str) {
    var partsOfStr = str.split(':');
    var seconds = parseInt(partsOfStr[2]);
    seconds += parseInt(partsOfStr[1]) * 60;
    seconds += parseInt(partsOfStr[0]) * 60 * 60;
    return seconds;
}

/**
 * 
 * @param {int} seconds
 * @param {int} level 3 = 00:00:00 2 = 00:00 1 = 00
 * @returns {String} 00:00:00
 */
function secondsToStr(seconds, level) {
    var hours = parseInt(seconds / (60 * 60));
    var minutes = parseInt(seconds / (60));
    seconds = parseInt(seconds % (60));
    hours = hours > 9 ? hours : "0" + hours;
    minutes = minutes > 9 ? minutes : "0" + minutes;
    seconds = seconds > 9 ? seconds : "0" + seconds;
    switch (level) {
        case 3:
            return hours + ":" + minutes + ":" + seconds;
            break;
        case 2:
            return minutes + ":" + seconds;
            break;
        case 1:
            return seconds;
            break;
        default:
            return hours + ":" + minutes + ":" + seconds;
    }
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function subscribe(email, user_id) {
    $.ajax({
        url: webSiteRootURL + 'subscribe.json',
        method: 'POST',
        data: {
            'email': email,
            'user_id': user_id
        },
        success: function (response) {
            console.log(response);
            if (response.subscribe == "i") {
                $('.subscribeButton').removeClass("subscribed");
                $('.subscribeButton b').text("Subscribe");
            } else {
                $('.subscribeButton').addClass("subscribed");
                $('.subscribeButton b').text("Subscribed");
            }
            $('#popover-content #subscribeEmail').val(email);
            $('.subscribeButton').popover('hide');
        }
    });
}

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


function mouseEffect(){

    $(".thumbsImage").on("mouseenter", function () {
        $(this).find(".thumbsGIF").height($(this).find(".thumbsJPG").height());
        $(this).find(".thumbsGIF").width($(this).find(".thumbsJPG").width());
        $(this).find(".thumbsGIF").stop(true, true).fadeIn();
    });

    $(".thumbsImage").on("mouseleave", function () {
        $(this).find(".thumbsGIF").stop(true, true).fadeOut();
    });
}