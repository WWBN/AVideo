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
var mouseX;
var mouseY;
var videoContainerDragged = false;
var youTubeMenuIsOpened = false;
var userIsControling = false;

$(document).mousemove(function (e) {
    mouseX = e.pageX;
    mouseY = e.pageY;
});
String.prototype.stripAccents = function () {
    var returnvar = this.replace(/[áàâãªä]/g, 'a');
    returnvar = returnvar.replace(/[ÁÀÂÃÄ]/g, 'A');
    returnvar = returnvar.replace(/[ÍÌÎÏ]/g, 'I');
    returnvar = returnvar.replace(/[íìîï]/g, 'i');
    returnvar = returnvar.replace(/[éèêë]/g, 'e');
    returnvar = returnvar.replace(/[ÉÈÊË]/g, 'E');
    returnvar = returnvar.replace(/[óòôõºö]/g, 'o');
    returnvar = returnvar.replace(/[ÓÒÔÕÖ]/g, 'O');
    returnvar = returnvar.replace(/[úùûü]/g, 'u');
    returnvar = returnvar.replace(/[ÚÙÛÜ]/g, 'U');
    returnvar = returnvar.replace(/ç/g, 'c');
    returnvar = returnvar.replace(/Ç/g, 'C');
    returnvar = returnvar.replace(/ñ/g, 'n');
    returnvar = returnvar.replace(/Ñ/g, 'N');
    returnvar = returnvar.replace(/–/g, '-');
    returnvar = returnvar.replace(/[’‘‹›‚]/g, ' ');
    returnvar = returnvar.replace(/[“”«»„]/g, ' ');
    returnvar = returnvar.replace(/ /g, ' ');
    returnvar = returnvar.replace(/Є/g, 'YE');
    returnvar = returnvar.replace(/І/g, 'I');
    returnvar = returnvar.replace(/Ѓ/g, 'G');
    returnvar = returnvar.replace(/і/g, 'i');
    returnvar = returnvar.replace(/№/g, '#');
    returnvar = returnvar.replace(/є/g, 'ye');
    returnvar = returnvar.replace(/ѓ/g, 'g');
    returnvar = returnvar.replace(/А/g, 'A');
    returnvar = returnvar.replace(/Б/g, 'B');
    returnvar = returnvar.replace(/В/g, 'V');
    returnvar = returnvar.replace(/Г/g, 'G');
    returnvar = returnvar.replace(/Д/g, 'D');
    returnvar = returnvar.replace(/Е/g, 'E');
    returnvar = returnvar.replace(/Ё/g, 'YO');
    returnvar = returnvar.replace(/Ж/g, 'ZH');
    returnvar = returnvar.replace(/З/g, 'Z');
    returnvar = returnvar.replace(/И/g, 'I');
    returnvar = returnvar.replace(/Й/g, 'J');
    returnvar = returnvar.replace(/К/g, 'K');
    returnvar = returnvar.replace(/Л/g, 'L');
    returnvar = returnvar.replace(/М/g, 'M');
    returnvar = returnvar.replace(/Н/g, 'N');
    returnvar = returnvar.replace(/О/g, 'O');
    returnvar = returnvar.replace(/П/g, 'P');
    returnvar = returnvar.replace(/Р/g, 'R');
    returnvar = returnvar.replace(/С/g, 'S');
    returnvar = returnvar.replace(/Т/g, 'T');
    returnvar = returnvar.replace(/У/g, 'U');
    returnvar = returnvar.replace(/Ф/g, 'F');
    returnvar = returnvar.replace(/Х/g, 'H');
    returnvar = returnvar.replace(/Ц/g, 'C');
    returnvar = returnvar.replace(/Ч/g, 'CH');
    returnvar = returnvar.replace(/Ш/g, 'SH');
    returnvar = returnvar.replace(/Щ/g, 'SHH');
    returnvar = returnvar.replace(/Ъ/g, '');
    returnvar = returnvar.replace(/Ы/g, 'Y');
    returnvar = returnvar.replace(/Ь/g, '');
    returnvar = returnvar.replace(/Э/g, 'E');
    returnvar = returnvar.replace(/Ю/g, 'YU');
    returnvar = returnvar.replace(/Я/g, 'YA');
    returnvar = returnvar.replace(/а/g, 'a');
    returnvar = returnvar.replace(/б/g, 'b');
    returnvar = returnvar.replace(/в/g, 'v');
    returnvar = returnvar.replace(/г/g, 'g');
    returnvar = returnvar.replace(/д/g, 'd');
    returnvar = returnvar.replace(/е/g, 'e');
    returnvar = returnvar.replace(/ё/g, 'yo');
    returnvar = returnvar.replace(/ж/g, 'zh');
    returnvar = returnvar.replace(/з/g, 'z');
    returnvar = returnvar.replace(/и/g, 'i');
    returnvar = returnvar.replace(/й/g, 'j');
    returnvar = returnvar.replace(/к/g, 'k');
    returnvar = returnvar.replace(/л/g, 'l');
    returnvar = returnvar.replace(/м/g, 'm');
    returnvar = returnvar.replace(/н/g, 'n');
    returnvar = returnvar.replace(/о/g, 'o');
    returnvar = returnvar.replace(/п/g, 'p');
    returnvar = returnvar.replace(/р/g, 'r');
    returnvar = returnvar.replace(/с/g, 's');
    returnvar = returnvar.replace(/т/g, 't');
    returnvar = returnvar.replace(/у/g, 'u');
    returnvar = returnvar.replace(/ф/g, 'f');
    returnvar = returnvar.replace(/х/g, 'h');
    returnvar = returnvar.replace(/ц/g, 'c');
    returnvar = returnvar.replace(/ч/g, 'ch');
    returnvar = returnvar.replace(/ш/g, 'sh');
    returnvar = returnvar.replace(/щ/g, 'shh');
    returnvar = returnvar.replace(/ъ/g, '');
    returnvar = returnvar.replace(/ы/g, 'y');
    returnvar = returnvar.replace(/ь/g, '');
    returnvar = returnvar.replace(/э/g, 'e');
    returnvar = returnvar.replace(/ю/g, 'yu');
    returnvar = returnvar.replace(/я/g, 'ya');
    returnvar = returnvar.replace(/—/g, '-');
    returnvar = returnvar.replace(/«/g, '');
    returnvar = returnvar.replace(/»/g, '');
    returnvar = returnvar.replace(/…/g, '');
    return returnvar;
};
function clean_name(str) {

    str = str.stripAccents().toLowerCase();
    return str.replace(/[!#$&'()*+,/:;=?@[\] ]+/g, "-");
}

function lazyImage() {
    try {
        if ($(".thumbsJPG").length) {
            $('.thumbsJPG').lazy({
                effect: 'fadeIn',
                visibleOnly: true,
                // called after an element was successfully handled
                afterLoad: function (element) {
                    element.removeClass('blur');
                    element.parent().find('.thumbsGIF').lazy({
                        effect: 'fadeIn'
                    });
                }
            });
            mouseEffect();
        }
    } catch (e) {
    }
}

lazyImage();

var pleaseWaitIsINUse = false;

function setPlayerListners() {
    if (typeof player !== 'undefined') {
        player.on('pause', function () {
            clearTimeout(promisePlayTimeout);
            console.log("setPlayerListners: pause");
            //userIsControling = true;
        });

        player.on('play', function () {
            clearTimeout(promisePlayTimeout);
            console.log("setPlayerListners: play");
            //userIsControling = true;
        });

        $("#mainVideo .vjs-mute-control").click(function () {
            Cookies.set('muted', player.muted(), {
                path: '/',
                expires: 365
            });
        });
    } else {
        setTimeout(function () {
            setPlayerListners();
        }, 2000);
    }
}

function removeTracks() {
    var oldTracks = player.remoteTextTracks();
    var i = oldTracks.length;
    while (i--) {
        player.removeRemoteTextTrack(oldTracks[i]);
    }
}

function changeVideoSrc(vid_obj, source) {
    var srcs = [];
    removeTracks();
    for (i = 0; i < source.length; i++) {
        if (source[i].type) {
            console.log(source[i].type);
            if (source[i].type === "application/x-mpegURL") {
                // it is HLS cancel it
                return false;
            }
            srcs.push(source[i]);
        } else if (source[i].srclang) {
            player.addRemoteTextTrack(source[i]);
        }
    }
    vid_obj.src(srcs);
    setTimeout(function () {
        changeVideoSrcLoad();
    }, 1000);
    return true;
}

function changeVideoSrcLoad() {
    console.log("changeVideoSrcLoad: Try to load player");
    player.load();
    player.ready(function () {
        console.log("changeVideoSrcLoad: Player ready");
        var err = this.error();
        if (err && err.code) {
            console.log("changeVideoSrcLoad: Load player Error");
            setTimeout(function () {
                changeVideoSrcLoad();
            }, 1000);
        } else {
            console.log("changeVideoSrcLoad: Load player Success, Play");
            setTimeout(function () {
                player.load();
                console.log("changeVideoSrcLoad: Trying to play");
                player.play();
            }, 1000);
        }
    });
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
        url: webSiteRootURL + 'objects/subscribe.json.php',
        method: 'POST',
        data: {
            'email': email,
            'user_id': user_id
        },
        success: function (response) {
            if (response.subscribe == "i") {
                $('.subs' + user_id).removeClass("subscribed");
                $('.subs' + user_id + ' b.text').text("Subscribe");
                $('b.textTotal' + user_id).text(parseInt($('b.textTotal' + user_id).first().text()) - 1);
            } else {
                $('.subs' + user_id).addClass("subscribed");
                $('.subs' + user_id + ' b.text').text("Subscribed");
                $('b.textTotal' + user_id).text(parseInt($('b.textTotal' + user_id).first().text()) + 1);
            }
            $('#popover-content #subscribeEmail').val(email);
            $('.subscribeButton' + user_id).popover('hide');
        }
    });
}

function subscribeNotify(email, user_id) {
    $.ajax({
        url: webSiteRootURL + 'objects/subscribeNotify.json.php',
        method: 'POST',
        data: {
            'email': email,
            'user_id': user_id
        },
        success: function (response) {
            if (response.notify) {
                $('.notNotify' + user_id).addClass("hidden");
                $('.notify' + user_id).removeClass("hidden");
            } else {
                $('.notNotify' + user_id).removeClass("hidden");
                $('.notify' + user_id).addClass("hidden");
            }
        }
    });
}
function mouseEffect() {

    $(".thumbsImage").on("mouseenter", function () {
        try {
            $(this).find(".thumbsGIF").lazy({effect: 'fadeIn'});
        } catch (e) {
        }
        $(this).find(".thumbsGIF").height($(this).find(".thumbsJPG").height());
        $(this).find(".thumbsGIF").width($(this).find(".thumbsJPG").width());
        $(this).find(".thumbsGIF").stop(true, true).fadeIn();
    });
    $(".thumbsImage").on("mouseleave", function () {
        $(this).find(".thumbsGIF").stop(true, true).fadeOut();
    });
}

function isMobile() {
    var check = false;
    (function (a) {
        if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4)))
            check = true
    })(navigator.userAgent || navigator.vendor || window.opera);
    return check;
}

var last_videos_id = 0;
var last_currentTime = -1;
function addView(videos_id, currentTime) {
    if (last_videos_id == videos_id && last_currentTime == currentTime) {
        return false;
    }
    last_videos_id = videos_id;
    last_currentTime = currentTime;
    $.ajax({
        url: webSiteRootURL + 'objects/videoAddViewCount.json.php',
        method: 'POST',
        data: {
            'id': videos_id,
            'currentTime': currentTime
        },
        success: function (response) {
            $('.view-count' + videos_id).text(response.count);
        }
    });
}

function getPlayerButtonIndex(name) {
    var children = player.getChild('controlBar').children();
    for (i = 0; i < children.length; i++) {
        if (children[i].name_ === name) {
            return i;
        }
    }
    return children.length;
}

function copyToClipboard(text) {

    $('#elementToCopy').css({'top': mouseY, 'left': 0}).fadeIn('slow');
    $('#elementToCopy').val(text);
    $('#elementToCopy').focus();
    $('#elementToCopy').select();
    document.execCommand('copy');
    $('#elementToCopy').hide();
    $.toast("Copied to Clipboard");
}

function nl2br(str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
function inIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}
var promisePlaytry = 20;
var promisePlayTimeoutTime = 500;
var promisePlayTimeout;
var promisePlay;
var browserPreventShowed = false;
function playerPlay(currentTime) {
    if (typeof player === 'undefined' || !player.isReady_) {
        setTimeout(function () {
            playerPlay(currentTime);
        }, 200);
        return false;
    }
    if (userIsControling) { // stops here if the user already clicked on play or pause
        console.log("playerPlay: userIsControling");
        return true;
    }
    if (promisePlaytry <= 0) {
        console.log("playerPlay: promisePlaytry <= 0");
        if (!browserPreventShowed) {
            browserPreventShowed = true;
            $.toast("Your browser prevent autoplay");
        }
        return false;
    }
    promisePlaytry--;
    if (typeof player !== 'undefined') {
        if (currentTime) {
            player.currentTime(currentTime);
        }
        try {
            console.log("playerPlay: Trying to play", player);
            promisePlay = player.play();
            if (promisePlay !== undefined) {
                tryToPlay(currentTime);
                console.log("playerPlay: promise found");
                promisePlay.then(function () {
                    console.log("playerPlay: Autoplay started");
                    userIsControling = true;
                    if (player.paused()) {
                        console.log("The video still paused, trying to mute and play");
                        if (promisePlaytry <= 10) {
                            console.log("playerPlay: (" + promisePlaytry + ") The video still paused, trying to mute and play");
                            tryToPlayMuted(currentTime);
                        } else {
                            console.log("playerPlay: (" + promisePlaytry + ") The video still paused, trying to play again");
                            tryToPlay(currentTime);
                        }
                    } else {
                        //player.muted(false);
                        if (player.muted() && !inIframe()) {
                            showUnmutePopup();
                        }
                    }
                }).catch(function (error) {
                    if (promisePlaytry <= 10) {
                        console.log("playerPlay: (" + promisePlaytry + ") Autoplay was prevented, trying to mute and play ***");
                        tryToPlayMuted(currentTime);
                    } else {
                        console.log("playerPlay: (" + promisePlaytry + ") Autoplay was prevented, trying to play again");
                        tryToPlay(currentTime);
                    }
                });
            } else {
                tryToPlay(currentTime);
            }
        } catch (e) {
            console.log("playerPlay: We could not autoplay, trying again in 1 second");
            tryToPlay(currentTime);
        }
    } else {
        console.log("playerPlay: Player is Undefined");
    }
}

function showUnmutePopup() {

    var donotShowUnmuteAgain = Cookies.get('donotShowUnmuteAgain');
    if (!donotShowUnmuteAgain) {
        var span = document.createElement("span");
        span.innerHTML = "<b>Would</b> you like to unmute it?<div id='allowAutoplay' style='max-height: 100px; overflow-y: scroll;'></div>";
        swal({
            title: "Your Media is Muted",
            icon: "warning",
            content: span,
            dangerMode: true,
            buttons: {
                cancel: "Cancel",
                unmute: true,
                donotShowUnmuteAgain: {
                    text: "Don't show again",
                    value: "donotShowUnmuteAgain",
                    className: "btn-danger",
                },
            }
        }).then(function (value) {
            switch (value) {
                case "unmute":
                    player.muted(false);
                    break;
                case "donotShowUnmuteAgain":
                    Cookies.set('donotShowUnmuteAgain', true, {
                        path: '/',
                        expires: 365
                    });
                    break;
            }
        });
    }
    showMuteTooltip();
    setTimeout(function () {
        $("#allowAutoplay").load(webSiteRootURL + "plugin/PlayerSkins/allowAutoplay/");
        player.userActive(true);
    }, 500);
}

function tryToPlay(currentTime) {
    clearTimeout(promisePlayTimeout);
    promisePlayTimeout = setTimeout(function () {
        if (player.paused()) {
            playerPlay(currentTime);
        }
    }, promisePlayTimeoutTime);
}

function tryToPlayMuted(currentTime) {
    muteInCookieAllow();
    return tryToPlay(currentTime);
}

function muteIfNotAudio() {
    if (!player.isAudio()) {
        console.log("muteIfNotAudio: We will mute this video");
        player.muted(true);
        return true;
    }
    console.log("muteIfNotAudio: We will not mute an audio");
    return false;
}

function muteInCookieAllow() {
    var mute = Cookies.get('muted');
    if (isALiveContent() || typeof mute === 'undefined' || (mute && mute !== "false")) {
        console.log("muteInCookieAllow: said yes");
        return muteIfNotAudio();
    }
    console.log("muteInCookieAllow: said no");
    return false;
}

function playMuted(currentTime) {
    muteInCookieAllow();
    return playerPlay(currentTime);
}

function showMuteTooltip() {
    if ($("#mainVideo .vjs-volume-panel").length) {
        if (!$("#mainVideo .vjs-volume-panel").is(":visible")) {
            setTimeout(function () {
                showMuteTooltip();
            }, 500);
            return false;
        }
        $("#mainVideo .vjs-volume-panel").attr("data-toggle", "tooltip");
        $("#mainVideo .vjs-volume-panel").attr("data-placement", "top");
        $("#mainVideo .vjs-volume-panel").attr("title", "Click to activate the sound");
        $('#mainVideo .vjs-volume-panel[data-toggle="tooltip"]').tooltip({container: '.vjs-control-bar'});
        $('#mainVideo .vjs-volume-panel[data-toggle="tooltip"]').tooltip('show');
        $("#mainVideo .vjs-volume-panel").click(function () {
            console.log("remove unmute tooltip");
            $('#mainVideo .vjs-volume-panel[data-toggle="tooltip"]').tooltip('hide');
            $("#mainVideo .vjs-volume-panel").removeAttr("data-toggle");
            $("#mainVideo .vjs-volume-panel").removeAttr("data-placement");
            $("#mainVideo .vjs-volume-panel").removeAttr("title");
            $("#mainVideo .vjs-volume-panel").removeData('tooltip').unbind().next('div.tooltip').remove();
        });
    }
    player.userActive(true);
    setTimeout(function () {
        player.userActive(true);
    }, 1000);
    setTimeout(function () {
        player.userActive(true);
    }, 1500);
    setTimeout(function () {
        $('#mainVideo .vjs-volume-panel[data-toggle="tooltip"]').tooltip('hide');
    }, 5000);
}

function playerPlayIfAutoPlay(currentTime) {
    if (isAutoplayEnabled()) {
        playerPlay(currentTime);
        return true;
    }
    setCurrentTime(currentTime);
    //$.toast("Autoplay disabled");
    return false;
}

function playNext(url) {
    if (isPlayingAds()) {
        setTimeout(function () {
            playNext(url);
        }, 1000);
    } else if (isPlayNextEnabled()) {
        modal.showPleaseWait();
        if (typeof autoPlayAjax == 'undefined' || !autoPlayAjax) {
            console.log("playNext changing location " + url);
            document.location = url;
        } else {
            console.log("playNext ajax");
            $.ajax({
                url: webSiteRootURL + 'view/infoFromURL.php?url=' + encodeURI(url),
                success: function (response) {
                    console.log(response);
                    if (!response || response.error) {
                        console.log("playNext ajax fail");
                        //document.location = url;
                    } else {
                        console.log("playNext ajax success");
                        $('topInfo').hide();
                        playNextURL = isEmbed ? response.nextURLEmbed : response.nextURL;
                        console.log("New playNextURL", playNextURL);
                        if (!changeVideoSrc(player, response.sources)) {
                            document.location = url;
                            return false;
                        }
                        $('video, #mainVideo').attr('poster', response.poster);
                        history.pushState(null, null, url);
                        $('.vjs-thumbnail-holder, .vjs-thumbnail-holder img').attr('src', response.sprits);
                        modal.hidePleaseWait();
                        if ($('#modeYoutubeBottom').length) {
                            $.ajax({
                                url: url,
                                success: function (response) {
                                    modeYoutubeBottom = $(response).find('#modeYoutubeBottom').html();
                                    $('#modeYoutubeBottom').html(modeYoutubeBottom);
                                }
                            });
                        }
                    }
                }
            });
        }
    } else if (isPlayerLoop()) {
        $.toast("Looping video");
        userIsControling = false;
        playerPlay(0);
    }
}

function formatBytes(bytes, decimals) {
    if (bytes == 0)
        return '0 Bytes';
    var k = 1024,
            dm = decimals <= 0 ? 0 : decimals || 2,
            sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
            i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function tooglePlayerLoop() {
    setPlayerLoop(!isPlayerLoop());
}

var setPlayerLoopSetTimeout;
function setPlayerLoop(loop) {
    clearTimeout(setPlayerLoopSetTimeout);
    if (typeof player === 'undefined') {
        setPlayerLoopSetTimeout = setTimeout(function () {
            setPlayerLoop(loop)
        }, 1000);
        return false;
    }
    if (loop) {
        console.log("Loop ON");
        //$.toast("Loop ON");
        player.loop(1);
        $(".loop-button").removeClass('loop-disabled-button');
        $(".loop-button, .loopButton").addClass('fa-spin');
    } else {
        $(".loop-button").addClass('loop-disabled-button');
        $(".loop-button, .loopButton").removeClass('fa-spin');
        console.log("Loop OFF");
        //$.toast("Loop OFF");
        player.loop(0);
    }
    Cookies.set('playerLoop', loop, {
        path: '/',
        expires: 365
    });
    if (typeof setImageLoop === 'function') {
        setImageLoop();
    }
}

function setImageLoop() {
    if (isPlayerLoop()) {
        $('.loopButton').removeClass('opacityBtn');
        $('.loopButton').addClass('fa-spin');
    } else {
        $('.loopButton').addClass('opacityBtn');
        $('.loopButton').removeClass('fa-spin');
    }
}

function toogleImageLoop(t) {
    tooglePlayerLoop();
    if (typeof setImageLoop === 'function') {
        setImageLoop();
    }
}

function isPlayerLoop() {
    if (typeof player === 'undefined') {
        return false;
    }
    var loop = Cookies.get('playerLoop');
    if (!loop || loop === "false") {
        return player.loop();
    } else {
        return true;
    }
}

function isArray(what) {
    return Object.prototype.toString.call(what) === '[object Array]';
}

function reloadVideoJS() {
    if (typeof player.currentSources === 'function') {
        var src = player.currentSources();
        player.src(src);
    }
}

var initdone = false;
function setCurrentTime(currentTime) {
    if (typeof player !== 'undefined') {
        player.currentTime(currentTime);
        initdone = false;
        // wait for video metadata to load, then set time 
        player.on("loadedmetadata", function () {
            player.currentTime(currentTime);
        });
        // iPhone/iPad need to play first, then set the time
        // events: https://www.w3.org/TR/html5/embedded-content-0.html#mediaevents
        player.on("canplaythrough", function () {
            if (!initdone) {
                player.currentTime(currentTime);
                initdone = true;
            }
        });
    } else {
        setTimeout(function () {
            setCurrentTime(currentTime);
        }, 1000);
    }
}

function isALiveContent(){
    if(typeof isLive !== 'undefined' && isLive){
        return true;
    }
    return false;
}

function isAutoplayEnabled() {
    console.log("Cookies.get('autoplay')", Cookies.get('autoplay'));
    if(isALiveContent()){
        console.log("isAutoplayEnabled always autoplay live contents");
        return true;
    }else
    if ($("#autoplay").length && $("#autoplay").is(':visible')) {
        autoplay = $("#autoplay").is(":checked");
        console.log("isAutoplayEnabled #autoplay said " + ((autoplay) ? "Yes" : "No"));
        setAutoplay(autoplay);
        return autoplay;
    } else if (
            typeof Cookies !== 'undefined' &&
            typeof Cookies.get('autoplay') !== 'undefined'
            ) {
        if (Cookies.get('autoplay') === 'true' || Cookies.get('autoplay') == true) {
            console.log("isAutoplayEnabled Cookie said Yes ");
            setAutoplay(true);
            return true;
        } else {
            console.log("isAutoplayEnabled Cookie said No ");
            setAutoplay(false);
            return false;
        }
    } else {
        if (typeof autoplay !== 'undefined') {
            console.log("isAutoplayEnabled autoplay said " + ((autoplay) ? "Yes" : "No"));
            setAutoplay(autoplay);
            return autoplay;
        }
    }
    setAutoplay(false);
    console.log("isAutoplayEnabled Default is No ");
    return false;
}

function setAutoplay(value) {
    Cookies.set('autoplay', value, {
        path: '/',
        expires: 365
    });
}

function isPlayNextEnabled() {
    if (isPlayerLoop()) {
        return false;
    } else if (isAutoplayEnabled()) {
        return true;
    }
    return false;
}

function avideoAlert(title, msg, type) {
    if (msg !== msg.replace(/<\/?[^>]+(>|$)/g, "")) {//it has HTML
        avideoAlertHTMLText(title, msg, type);
    } else {
        swal(title, msg, type);
    }
}

function avideoAlertHTMLText(title, msg, type) {
    var span = document.createElement("span");
    span.innerHTML = msg;
    swal({
        title: title,
        content: span,
        type: type,
        icon: type,
        html: true,
        closeModal: true
    });
}

function avideoAlertInfo(msg) {
    avideoAlert("Info", msg, 'info');
}
function avideoAlertError(msg) {
    avideoAlert("Error", msg, 'error');
}
function avideoAlertSuccess(msg) {
    avideoAlert("Success", msg, 'success');
}

function avideoTooltip(selector, text) {
    $(selector).attr('title', text);
    $(selector).attr('data-toggle', 'tooltip');
    $(selector).attr('data-original-title', text);
    $(selector).tooltip();
}

function fixAdSize() {
    ad_container = $('#mainVideo_ima-ad-container');
    if (ad_container.length) {
        height = ad_container.css('height');
        width = ad_container.css('width');
        $($('#mainVideo_ima-ad-container div:first-child')[0]).css({'height': height});
        $($('#mainVideo_ima-ad-container div:first-child')[0]).css({'width': width});
    }
}

function isPlayingAds() {
    return ($("#mainVideo_ima-ad-container").length && $("#mainVideo_ima-ad-container").is(':visible'));
}

function playerHasAds() {
    return ($("#mainVideo_ima-ad-container").length > 0);
}

function countTo(selector, total) {
    var text = $(selector).text();
    if (isNaN(text)) {
        current = 0;
    } else {
        current = parseInt(text);
    }
    total = parseInt(total);
    if (!total || current >= total) {
        $(selector).removeClass('loading');
        return;
    }
    var rest = (total - current);
    var step = parseInt(rest / 100);
    if (step < 1) {
        step = 1;
    }
    current += step;
    $(selector).text(current);
    var timeout = (500 / rest);
    setTimeout(function () {
        countTo(selector, total);
    }, timeout);
}



$(document).ready(function () {
    modal = modal || (function () {
        var pleaseWaitDiv = $("#pleaseWaitDialog");
        if (pleaseWaitDiv.length === 0) {
            pleaseWaitDiv = $('<div id="pleaseWaitDialog" class="modal fade"  data-backdrop="static" data-keyboard="false"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"><h2>Processing...</h2><div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div></div></div></div></div>').appendTo('body');
        }

        return {
            showPleaseWait: function () {
                if (pleaseWaitIsINUse) {
                    return false;
                }
                pleaseWaitIsINUse = true;
                pleaseWaitDiv.modal();
            },
            hidePleaseWait: function () {
                pleaseWaitDiv.modal('hide');
                pleaseWaitIsINUse = false;
            },
            setProgress: function (valeur) {
                pleaseWaitDiv.find('.progress-bar').css('width', valeur + '%').attr('aria-valuenow', valeur);
            },
            setText: function (text) {
                pleaseWaitDiv.find('h2').html(text);
            },
        };
    })();
    try {
        $('[data-toggle="popover"]').popover();
        $('[data-toggle="tooltip"]').tooltip({container: 'body'});
        $('[data-toggle="tooltip"]').on('click', function () {
            var t = this;
            setTimeout(function () {
                $(t).tooltip('hide');
            }, 2000);
        });
    } catch (e) {

    }

    $(".thumbsImage").on("mouseenter", function () {
        gifId = $(this).find(".thumbsGIF").attr('id');
        $(".thumbsGIF").fadeOut();
        if (gifId != undefined) {
            id = gifId.replace('thumbsGIF', '');
            $(this).find(".thumbsGIF").height($(this).find(".thumbsJPG").height());
            $(this).find(".thumbsGIF").width($(this).find(".thumbsJPG").width());
            try {
                $(this).find(".thumbsGIF").lazy({effect: 'fadeIn'});
            } catch (e) {
            }
            $(this).find(".thumbsGIF").stop(true, true).fadeIn();
        }
    });
    $(".thumbsImage").on("mouseleave", function () {
        $(this).find(".thumbsGIF").stop(true, true).fadeOut();
    });

    lazyImage();

    $("a").each(function () {
        var location = window.location.toString()
        var res = location.split("?");
        pathWitoutGet = res[0];
        if ($(this).attr("href") == window.location.pathname
                || $(this).attr("href") == window.location
                || $(this).attr("href") == pathWitoutGet) {
            $(this).addClass("selected");
        }
    });
    $('#clearCache, .clearCacheButton').on('click', function (ev) {
        ev.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'objects/configurationClearCache.json.php',
            success: function (response) {
                if (!response.error) {
                    avideoAlert("Congratulations!", "Your cache has been cleared!", "success");
                } else {
                    avideoAlert("Sorry!", "Your cache has NOT been cleared!", "error");
                }
                modal.hidePleaseWait();
            }
        });
    });
    $('.clearCacheFirstPageButton').on('click', function (ev) {
        ev.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'objects/configurationClearCache.json.php?FirstPage=1',
            success: function (response) {
                if (!response.error) {
                    avideoAlert("Congratulations!", "Your First Page cache has been cleared!", "success");
                } else {
                    avideoAlert("Sorry!", "Your First Page cache has NOT been cleared!", "error");
                }
                modal.hidePleaseWait();
            }
        });
    });
    $('#generateSiteMap, .generateSiteMapButton').on('click', function (ev) {
        ev.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'objects/configurationGenerateSiteMap.json.php',
            success: function (response) {
                if (!response.error) {
                    avideoAlert("Congratulations!", "File created!", "success");
                } else {
                    if (response.msg) {
                        avideoAlert("Sorry!", response.msg, "error");
                    } else {
                        avideoAlert("Sorry!", "File NOT created!", "error");
                    }
                }
                modal.hidePleaseWait();
            }
        });
    });
    setPlayerListners();

    $('.duration:contains("00:00:00"), .duration:contains("EE:EE:EE")').hide();

});

function validURL(str) {
    var pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+:]*)*' + // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
            '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
    return !!pattern.test(str);
}

function startTimer(duration, selector) {
    var timer = duration;
    var startTimerInterval = setInterval(function () {

        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(duration / (60 * 60 * 24));
        var hours = Math.floor((duration % (60 * 60 * 24)) / (60 * 60));
        var minutes = Math.floor((duration % (60 * 60)) / (60));
        var seconds = Math.floor((duration % (60)));

        // Display the result in the element with id="demo"
        var text = days + "d " + hours + "h "
                + minutes + "m " + seconds + "s ";
        $(selector).text(text);
        duration--;
        // If the count down is finished, write some text
        if (duration < 0) {
            clearInterval(startTimerInterval);
            $(selector).text("EXPIRED");
        }

    }, 1000);
}