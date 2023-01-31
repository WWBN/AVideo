try {
    var _serverTime;
    var _serverDBTime;
    var _serverTimeString;
    var _serverDBTimeString;
    var _serverTimezone;
    var _serverDBTimezone;
    var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
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
    var playerCurrentTime;
    var mediaId;
    var isDebuging = false;
    var avideoIsOnline = false;
    var userLang = navigator.language || navigator.userLanguage;
    var iframeAllowAttributes = 'allow="fullscreen;autoplay;camera *;microphone *;" allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" webkitallowfullscreen="webkitallowfullscreen"';

    // Create browser compatible event handler.
    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
    // Listen for a message from the iframe.
    eventer(messageEvent, function (e) {
        ////console.log('EventListener', e.data);
        if (e.data.getHeight) {
            var height = $('body > div.container-fluid').height();
            if (!height) {
                height = $('body > div.container').height();
            }
            if (!height) {
                height = $('body').height();
            }
            parent.postMessage({height: height}, '*');
        }else if (e.data.play) {
            var currentTime = e.data.play.currentTime;
            var muted = !empty(e.data.play.muted);
            if(!muted){
                playerPlay(currentTime);
            }else{
                tryToPlayMuted(currentTime);
            }
        }
    }, false);

    eventer("online", function (e) {
        avideoToastSuccess("Connected");
        setBodyOnline();
    }, false);

    eventer("offline", function (e) {
        avideoToastError("Disconnected");
        setBodyOnline();
    }, false);

    setBodyOnline();
} catch (e) {
    //console.log('Variable declaration ERROR', e);
}

var queryString = window.location.search;
var urlParams = new URLSearchParams(queryString);

if (urlParams.has('debug')) {
    isDebuging = false;
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
}
if (typeof String.prototype.replaceAll === "undefined") {
    console.log('replaceAll is undefined');
    String.prototype.replaceAll = function (match, _replace) {
        return this.replace(new RegExp(escapeRegExp(match), 'g'), _replace);
    }
}

async function setBodyOnline() {
    if (isOnline()) {
        $('body').removeClass('isOffline');
        $('body').addClass('isOnline');
    } else {
        $('body').removeClass('isOnline');
        $('body').addClass('isOffline');
    }
}

function consolelog() {
    if (isDebuging) {
        for (var item in arguments) {
            console.log(arguments[item]);
        }
    }
}

function consoleLog() {
    return consolelog();
}

$(document).mousemove(function (e) {
    mouseX = e.pageX;
    mouseY = e.pageY;
});
String.prototype.stripAccents = function () {
    var returnvar = this.replace(/[áàâãªäą]/g, 'a');
    returnvar = returnvar.replace(/[ÁÀÂÃÄĄ]/g, 'A');
    returnvar = returnvar.replace(/[ÍÌÎÏ]/g, 'I');
    returnvar = returnvar.replace(/[íìîï]/g, 'i');
    returnvar = returnvar.replace(/[éèêëę]/g, 'e');
    returnvar = returnvar.replace(/[ÉÈÊËĘ]/g, 'E');
    returnvar = returnvar.replace(/[óòôõºö]/g, 'o');
    returnvar = returnvar.replace(/[ÓÒÔÕÖ]/g, 'O');
    returnvar = returnvar.replace(/[úùûü]/g, 'u');
    returnvar = returnvar.replace(/[ÚÙÛÜ]/g, 'U');
    returnvar = returnvar.replace(/[çć]/g, 'c');
    returnvar = returnvar.replace(/[ÇĆ]/g, 'C');
    returnvar = returnvar.replace(/[ñń]/g, 'n');
    returnvar = returnvar.replace(/[ÑŃ]/g, 'N');
    returnvar = returnvar.replace(/–/g, '-');
    returnvar = returnvar.replace(/[’‘‹›‚]/g, ' ');
    returnvar = returnvar.replace(/[“”«»„]/g, ' ');
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
    returnvar = returnvar.replace(/[ЗŻŹ]/g, 'Z');
    returnvar = returnvar.replace(/И/g, 'I');
    returnvar = returnvar.replace(/Й/g, 'J');
    returnvar = returnvar.replace(/К/g, 'K');
    returnvar = returnvar.replace(/[ЛŁ]/g, 'L');
    returnvar = returnvar.replace(/М/g, 'M');
    returnvar = returnvar.replace(/Н/g, 'N');
    returnvar = returnvar.replace(/О/g, 'O');
    returnvar = returnvar.replace(/П/g, 'P');
    returnvar = returnvar.replace(/Р/g, 'R');
    returnvar = returnvar.replace(/[СŚ]/g, 'S');
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
    returnvar = returnvar.replace(/[зżź]/g, 'z');
    returnvar = returnvar.replace(/и/g, 'i');
    returnvar = returnvar.replace(/й/g, 'j');
    returnvar = returnvar.replace(/к/g, 'k');
    returnvar = returnvar.replace(/[лł]/g, 'l');
    returnvar = returnvar.replace(/м/g, 'm');
    returnvar = returnvar.replace(/н/g, 'n');
    returnvar = returnvar.replace(/о/g, 'o');
    returnvar = returnvar.replace(/п/g, 'p');
    returnvar = returnvar.replace(/р/g, 'r');
    returnvar = returnvar.replace(/[сś]/g, 's');
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

var processing_lazyImage = false;
async function lazyImage() {
    if (processing_lazyImage) {
        return false;
    }
    processing_lazyImage = true;
    try {
        if ($(".thumbsJPG").length) {
            $('.thumbsJPG').lazy({
                effect: 'fadeIn',
                visibleOnly: true,
                // called after an element was successfully handled
                afterLoad: function (element) {

                    element.addClass('gifNotLoaded');
                    element.removeClass('blur');
                    element.mouseover(function () {

                        if ($(this).hasClass('gifNotLoaded')) {
                            var element = $(this);
                            element.removeClass('gifNotLoaded');
                            var gif = element.parent().find('.thumbsGIF');
                            gif.lazy({
                                effect: 'fadeIn'
                            });
                            /*
                             gif.addClass('animate__animated');
                             gif.addClass('animate__bounceIn');
                             gif.css('-webkit-animation-delay', step+"s");
                             gif.css('animation-delay', "1s");
                             */

                            gif.height(element.height());
                            gif.width(element.width());
                            ////console.log('lazyImage', gif);
                        }

                        $("#log").append("<div>Handler for .mouseover() called.</div>");
                    });
                }
            });
            mouseEffect();
        }
    } catch (e) {
    }
    processing_lazyImage = false;
}

var pauseIfIsPlayinAdsInterval;
var seconds_watching_video = 0;
var _startCountPlayingTime;
async function setPlayerListners() {
    if (typeof player !== 'undefined') {
        player.on('pause', function () {
            clearTimeout(promisePlayTimeout);
            //console.log("setPlayerListners: pause");
            //userIsControling = true;
            clearInterval(pauseIfIsPlayinAdsInterval);
            clearInterval(_startCountPlayingTime);
        });
        player.on('play', function () {
            isTryingToPlay = false;
            clearTimeout(promisePlayTimeout);
            if (startCurrentTime) {
                setTimeout(function () {
                    setCurrentTime(startCurrentTime);
                    startCurrentTime = 0;
                }, 100);
            }
            //console.log("setPlayerListners: play");
            //userIsControling = true;
            pauseIfIsPlayinAdsInterval = setInterval(function () {
                pauseIfIsPlayinAds();
            }, 500);
            clearInterval(_startCountPlayingTime);
            _startCountPlayingTime = setInterval(function () {
                seconds_watching_video++;
            }, 1000);
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
    var autoLoad = true;
    for (i = 0; i < source.length; i++) {
        if (source[i].type) {
            if (source[i].type === "application/x-mpegURL") {
                // it is HLS cancel it
                //return false;
                autoLoad = false;
            }
            srcs.push(source[i]);
        } else if (source[i].srclang) {
            player.addRemoteTextTrack(source[i]);
        }
    }
    //console.log('changeVideoSrc srcs', srcs);
    vid_obj.src(srcs);
    setTimeout(function () {
        if (autoLoad) {
            changeVideoSrcLoad();
        } else {
            player.play();
        }
    }, 1000);
    return true;
}

function changeVideoSrcLoad() {
    //console.log("changeVideoSrcLoad: Try to load player");
    player.load();
    player.ready(function () {
        //console.log("changeVideoSrcLoad: Player ready");
        var err = this.error();
        if (err && err.code) {
            //console.log("changeVideoSrcLoad: Load player Error");
            setTimeout(function () {
                changeVideoSrcLoad();
            }, 1000);
        } else {
            //console.log("changeVideoSrcLoad: Load player Success, Play");
            setTimeout(function () {
                player.load();
                //console.log("changeVideoSrcLoad: Trying to play");
                player.play();
            }, 1000);
        }
    });
}
var _reloadAdsTimeout;
var isReloadingAds = false;
function reloadAds() {
    if (isReloadingAds) {
        return false;
    }
    isReloadingAds = true;
    setTimeout(function () {
        isReloadingAds = false;
    }, 500);
    clearTimeout(_reloadAdsTimeout);
    //console.log('reloadAds ');
    if (playerIsReady() && player.ima) {
        try {
            //console.log('reloadAds player.ima.getAdsManager()', player.ima.getAdsManager());
            if (player.ima.getAdsManager()) {
                player.ima.requestAds();
            }
            player.ima.changeAdTag(null);
            player.ima.setContentWithAdTag(null, _adTagUrl, false);
            player.ima.changeAdTag(_adTagUrl);
            setTimeout(function () {
                player.ima.requestAds();
                //console.log('reloadAds done');
            }, 2000);
            player.ima.requestAds();
        } catch (e) {
            //console.log('reloadAds ERROR', e.message);
        }
    } else {
        _reloadAdsTimeout = setTimeout(function () {
            reloadAds();
        }, 200);
    }
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function isEmailValid(email) {
    return validateEmail(email);
}

function subscribe(email, user_id) {
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'objects/subscribe.json.php',
        method: 'POST',
        data: {
            'email': email,
            'user_id': user_id
        },
        success: function (response) {
            var totalElement = $('.notificationButton' + user_id + ' .badge');
            if (response.subscribe == "i") {
                $('.notificationButton' + user_id).removeClass("subscribed");
                totalElement.text(parseInt(totalElement.first().text()) - 1);
            } else {
                $('.notificationButton' + user_id).addClass("subscribed");
                totalElement.text(parseInt(totalElement.first().text()) + 1);
            }
            if (!response.notify) {
                $('.notificationButton' + user_id).removeClass("notify");
            } else {
                $('.notificationButton' + user_id).addClass("notify");
            }
            $('#popover-content #subscribeEmail').val(email);
            $('.subscribeButton' + user_id).popover('hide');
            modal.hidePleaseWait();
        }
    });
}

function toogleNotify(user_id) {
    email = $('#subscribeEmail' + user_id).val();
    subscribeNotify(email, user_id);
}
function subscribeNotify(email, user_id) {
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'objects/subscribeNotify.json.php',
        method: 'POST',
        data: {
            'email': email,
            'user_id': user_id
        },
        success: function (response) {
            if (response.notify) {
                $('.notificationButton' + user_id).addClass("notify");
            } else {
                $('.notificationButton' + user_id).removeClass("notify");
            }
            modal.hidePleaseWait();
        }
    });
}
async function mouseEffect() {
    $(".thumbsImage").on("mouseenter", function () {
        var gif = $(this).find(".thumbsGIF");
        var jpg = $(this).find(".thumbsJPG");
        try {
            gif.lazy({effect: 'fadeIn'});
            setTimeout(function () {
                gif.height(jpg.height());
                gif.width(jpg.width());
            }, 100);
        } catch (e) {
        }
        gif.height(jpg.height());
        gif.width(jpg.width());
        gif.stop(true, true).fadeIn();
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
var videoViewAdded = false;
var addViewBeaconTimeout;
var _addViewCheck = false;
function addView(videos_id, currentTime) {
    addViewSetCookie(PHPSESSID, videos_id, currentTime, seconds_watching_video);
    if (_addViewCheck) {
        return false;
    }
    if (last_videos_id == videos_id && last_currentTime == currentTime) {
        return false;
    }
    if (currentTime > 5 && currentTime % 30 !== 0) { // only update each 30 seconds
        return false;
    }
    _addViewCheck = true;
    last_videos_id = videos_id;
    last_currentTime = currentTime;
    _addView(videos_id, currentTime, seconds_watching_video);
    setTimeout(function () {
        _addViewCheck = false
    }, 1000);
    return true;
}

function _addView(videos_id, currentTime, seconds_watching_video) {
    if (typeof PHPSESSID == 'undefined') {
        PHPSESSID = '';
    }
    var url = webSiteRootURL + 'objects/videoAddViewCount.json.php';
    if (empty(PHPSESSID)) {
        return false;
    }
    url = addGetParam(url, 'PHPSESSID', PHPSESSID);
    //console.log('_addView', videos_id, currentTime);
    $.ajax({
        url: url,
        method: 'POST',
        data: {
            id: videos_id,
            currentTime: currentTime,
            seconds_watching_video: seconds_watching_video
        },
        success: function (response) {
            $('.view-count' + videos_id).text(response.countHTML);
        }
    });
}

var _addViewAsyncSent = false;
function _addViewAsync() {
    if (_addViewAsyncSent || typeof webSiteRootURL == 'undefined' || typeof player == 'undefined') {
        return false;
    }
    if (typeof PHPSESSID == 'undefined') {
        PHPSESSID = '';
    }
    //console.log('_addViewAsync', mediaId, playerCurrentTime);
    var url = webSiteRootURL + 'objects/videoAddViewCount.json.php';
    url = addGetParam(url, 'PHPSESSID', PHPSESSID);
    _addViewAsyncSent = true;
    _addView(mediaId, playerCurrentTime, seconds_watching_video);
    setTimeout(function () {
        _addViewAsyncSent = false;
    }, 2000);
}

var _addViewFromCookie_addingtime = false;
async function addViewFromCookie() {
    if (typeof webSiteRootURL == 'undefined') {
        return false;
    }
    if (_addViewFromCookie_addingtime) {
        return false;
    }
    _addViewFromCookie_addingtime = true;
    var addView_PHPSESSID = Cookies.get('addView_PHPSESSID');
    var addView_videos_id = Cookies.get('addView_videos_id');
    var addView_playerCurrentTime = Cookies.get('addView_playerCurrentTime');
    var addView_seconds_watching_video = Cookies.get('addView_seconds_watching_video');
    if (!addView_PHPSESSID || addView_PHPSESSID === 'false' ||
            !addView_videos_id || addView_videos_id === 'false' ||
            !addView_playerCurrentTime || addView_playerCurrentTime === 'false' ||
            !addView_seconds_watching_video || addView_seconds_watching_video === 'false') {
        return false;
    }
    //console.log('addViewFromCookie', addView_videos_id, addView_playerCurrentTime, addView_seconds_watching_video);
    var url = webSiteRootURL + 'objects/videoAddViewCount.json.php';
    url = addGetParam(url, 'PHPSESSID', addView_PHPSESSID);
    if (mediaId == addView_videos_id) {
        // it is the same video, play at the last moment
        forceCurrentTime = addView_playerCurrentTime;
    }

    _addView(addView_videos_id, addView_playerCurrentTime, addView_seconds_watching_video)
    setTimeout(function () {
        _addViewFromCookie_addingtime = false;
    }, 2000);
    addViewSetCookie(false, false, false, false);

}

async function addViewSetCookie(PHPSESSID, videos_id, playerCurrentTime, seconds_watching_video) {
    ////console.log('addViewSetCookie', videos_id, playerCurrentTime, seconds_watching_video, new Error().stack);
    Cookies.set('addView_PHPSESSID', PHPSESSID, {
        path: '/',
        expires: 1
    });
    Cookies.set('addView_videos_id', videos_id, {
        path: '/',
        expires: 1
    });
    Cookies.set('addView_playerCurrentTime', playerCurrentTime, {
        path: '/',
        expires: 1
    });
    Cookies.set('addView_seconds_watching_video', seconds_watching_video, {
        path: '/',
        expires: 1
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

async function copyToClipboard(text) {
    $('body').append('<textarea id="elementToCopyAvideo" style="filter: alpha(opacity=0);-moz-opacity: 0;-khtml-opacity: 0; opacity: 0;position: absolute;z-index: -9999;top: 0;left: 0;pointer-events: none;"></textarea>');
    $('#elementToCopyAvideo').css({'top': mouseY, 'left': 0}).fadeIn('slow');
    $('#elementToCopyAvideo').val(text);
    $('#elementToCopyAvideo').focus();
    $('#elementToCopyAvideo').select();
    document.execCommand('copy');
    $('#elementToCopyAvideo').remove();
    $.toast("Copied to Clipboard");
}

function nl2br(str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function inMainIframe() {
    var response = false;
    if (window.self !== window.top) {
        try {
            var mainIframe = $('iframe', window.parent.document).attr('id');
            response = mainIframe === 'mainIframe';
        } catch (e) {

        }

    }
    return response;
}

function inIframe() {
    if (inMainIframe()) {
        return false;
    }
    var url = new URL(location.href);
    var avideoIframe = url.searchParams.get("avideoIframe");
    if (avideoIframe && avideoIframe !== '0' && avideoIframe !== 0) {
        return true;
    }
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

function playerIsReady() {
    return (typeof player !== 'undefined' && player.isReady_);
}

var promisePlaytry = 20;
var promisePlaytryNetworkFail = 0;
var promisePlayTimeoutTime = 500;
var promisePlayTimeout;
var promisePlay;
var browserPreventShowed = false;
var playerPlayTimeout;
var isTryingToPlay = false;
var promisePlaytryNetworkFailTimeout;
function playerPlay(currentTime) {
    isTryingToPlay = true;
    clearTimeout(playerPlayTimeout);
    if (playerIsPlayingAds()) {
        return false;
    }
    if (currentTime) {
        //console.log("playerPlay time:", currentTime);
    }
    if (!playerIsReady()) {
        playerPlayTimeout = setTimeout(function () {
            playerPlay(currentTime);
        }, 200);
        return false;
    }
    if (userIsControling) { // stops here if the user already clicked on play or pause
        //console.log("playerPlay: userIsControling");
        return true;
    }
    if (promisePlaytry <= 0) {
        //console.log("playerPlay: promisePlaytry <= 0");
        if (!browserPreventShowed) {
            browserPreventShowed = true;
            $.toast("Your browser prevent autoplay");
        }
        return false;
    }
    promisePlaytry--;
    if (typeof player !== 'undefined') {
        if (currentTime) {
            setCurrentTime(currentTime);
        }
        try {
            //console.log("playerPlay: Trying to play", player);
            promisePlay = player.play();
            if (promisePlay !== undefined) {
                tryToPlay(currentTime);
                //console.log("playerPlay: promise found", currentTime);
                setPlayerListners();
                promisePlay.then(function () {
                    //console.log("playerPlay: Autoplay started", currentTime);
                    userIsControling = true;
                    if (player.paused()) {
                        //console.log("The video still paused, trying to mute and play");
                        if (promisePlaytry <= 10) {
                            //console.log("playerPlay: (" + promisePlaytry + ") The video still paused, trying to mute and play");
                            tryToPlayMuted(currentTime);
                        } else {
                            //console.log("playerPlay: (" + promisePlaytry + ") The video still paused, trying to play again");
                            tryToPlay(currentTime);
                        }
                    } else {
                        //player.muted(false);
                        if (player.muted() && !inIframe()) {
                            showUnmutePopup();
                        }
                    }
                }).catch(function (error) {
                    if (player.networkState() === 3 && promisePlaytryNetworkFail < 5) {
                        promisePlaytry = 20;
                        promisePlaytryNetworkFail++;
                        //console.log("playerPlay: Network error detected, trying again", promisePlaytryNetworkFail);
                        clearTimeout(promisePlaytryNetworkFailTimeout);
                        promisePlaytryNetworkFailTimeout = setTimeout(function () {
                            player.src(player.currentSources());
                            userIsControling = false;
                            tryToPlay(currentTime);
                        }, promisePlaytryNetworkFail * 1000);
                    } else {
                        if (promisePlaytryNetworkFail >= 5) {
                            userIsControling = true;
                            //console.log("playerPlay: (promisePlaytryNetworkFail) Autoplay was prevented");
                            player.pause();
                        } else if (promisePlaytry <= 10) {
                            //console.log("playerPlay: (" + promisePlaytry + ") Autoplay was prevented, trying to mute and play ***");
                            tryToPlayMuted(currentTime);
                        } else {
                            //console.log("playerPlay: (" + promisePlaytry + ") Autoplay was prevented, trying to play again");
                            tryToPlay(currentTime);
                        }
                    }
                });
            } else {
                tryToPlay(currentTime);
            }
        } catch (e) {
            //console.log("playerPlay: We could not autoplay, trying again in 1 second");
            tryToPlay(currentTime);
        }
    } else {
        //console.log("playerPlay: Player is Undefined");
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
            playerPlayTimeout = setTimeout(function () {
                playerPlay(currentTime);
            }, 200);
        }
    }, promisePlayTimeoutTime);
}

function tryToPlayMuted(currentTime) {
    muteInCookieAllow();
    return tryToPlay(currentTime);
}

function muteIfNotAudio() {
    if (!player.isAudio()) {
        //console.log("muteIfNotAudio: We will mute this video");
        player.muted(true);
        return true;
    }
    //console.log("muteIfNotAudio: We will not mute an audio");
    return false;
}

function muteInCookieAllow() {
    var mute = Cookies.get('muted');
    if (isALiveContent() || typeof mute === 'undefined' || (mute && mute !== "false")) {
        //console.log("muteInCookieAllow: said yes");
        return muteIfNotAudio();
    }
    //console.log("muteInCookieAllow: said no");
    return false;
}

function playMuted(currentTime) {
    muteInCookieAllow();
    playerPlayTimeout = setTimeout(function () {
        playerPlay(currentTime);
    }, 200);
}

async function showMuteTooltip() {
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
        $('#mainVideo .vjs-volume-panel[data-toggle="tooltip"]').tooltip({container: '.vjs-control-bar', html: true});
        $('#mainVideo .vjs-volume-panel[data-toggle="tooltip"]').tooltip('show');
        $("#mainVideo .vjs-volume-panel").click(function () {
            //console.log("remove unmute tooltip");
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
    if (isWebRTC()) {
        return false;
    }
    if (forceCurrentTime !== null) {
        currentTime = forceCurrentTime;
        forceCurrentTime = null;
        //console.log("playerPlayIfAutoPlay: forceCurrentTime:", currentTime);
    }

    if (currentTime) {
        setCurrentTime(currentTime);
    }
    if (isAutoplayEnabled()) {
        playerPlayTimeout = setTimeout(function () {
            //console.log('playerPlayIfAutoPlay true', currentTime);
            playerPlay(currentTime);
        }, 200);
        return true;
    }
    //console.log('playerPlayIfAutoPlay false', currentTime);
    //$.toast("Autoplay disabled");
    return false;
}

function playerPlayMutedIfAutoPlay(currentTime) {
    if (isWebRTC()) {
        return false;
    }
    if (forceCurrentTime !== null) {
        currentTime = forceCurrentTime;
        forceCurrentTime = null;
        //console.log("playerPlayIfAutoPlay: forceCurrentTime:", currentTime);
    }

    if (currentTime) {
        setCurrentTime(currentTime);
    }
    if (isAutoplayEnabled()) {
        playerPlayTimeout = setTimeout(function () {
            //console.log('playerPlayIfAutoPlay true', currentTime);
            tryToPlayMuted(currentTime);
        }, 200);
        return true;
    }
    //console.log('playerPlayIfAutoPlay false', currentTime);
    //$.toast("Autoplay disabled");
    return false;
}

function playNext(url) {
    if (!player.paused()) {
        return false;
    }
    if (playerIsPlayingAds()) {
        setTimeout(function () {
            playNext(url);
        }, 1000);
    } else if (isPlayNextEnabled()) {
        modal.showPleaseWait();
        if (typeof autoPlayAjax == 'undefined' || !autoPlayAjax) {
            //console.log("playNext changing location " + url);
            document.location = url;
        } else {
            forceCurrentTime = 0;
            setCurrentTime(0);
            //console.log("playNext ajax");
            $.ajax({
                url: webSiteRootURL + 'view/infoFromURL.php?url=' + encodeURI(url),
                success: function (response) {
                    //console.log(response);
                    if (!response || response.error) {
                        //console.log("playNext ajax fail");
                        if (response.url) {
                            document.location = response.url;
                        }
                    } else {
                        //console.log("playNext ajax success");
                        $('topInfo').hide();
                        playNextURL = (typeof isEmbed !== 'undefined' && isEmbed) ? response.nextURLEmbed : response.nextURL;
                        //console.log("New playNextURL", playNextURL);
                        var cSource = false;
                        try {
                            cSource = changeVideoSrc(player, response.sources);
                        } catch (e) {
                            //console.log('changeVideoSrc', e.message);
                        }
                        if (!cSource) {
                            document.location = url;
                            return false;
                        }
                        mediaId = response.videos_id;
                        webSocketVideos_id = mediaId;
                        $('video, #mainVideo').attr('poster', response.poster);
                        player.poster(response.poster);
                        history.pushState(null, null, url);
                        $('.topInfoTitle, title').text(response.title);
                        $('#topInfo img').attr('src', response.userPhoto);
                        $('#topInfo a').attr('href', response.url);
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
        avideoToast("Looping video");
        userIsControling = false;
        playerPlayTimeout = setTimeout(function () {
            playerPlay(currentTime);
        }, 200);
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

async function tooglePlayerLoop() {
    setPlayerLoop(!isPlayerLoop());
}

var setPlayerLoopSetTimeout;
async function setPlayerLoop(loop) {
    clearTimeout(setPlayerLoopSetTimeout);
    if (typeof player === 'undefined' && $('#mainVideo').length) {
        setPlayerLoopSetTimeout = setTimeout(function () {
            setPlayerLoop(loop)
        }, 1000);
        return false;
    }
    if (loop) {
        //console.log("Loop ON");
        //$.toast("Loop ON");
        player.loop(1);
        $(".loop-button").removeClass('loop-disabled-button');
        $(".loop-button, .loopButton").addClass('fa-spin');
    } else {
        $(".loop-button").addClass('loop-disabled-button');
        $(".loop-button, .loopButton").removeClass('fa-spin');
        //console.log("Loop OFF");
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

async function setImageLoop() {
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
    if (typeof player === 'undefined' && $('#mainVideo').length) {
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

async function reloadVideoJS() {
    if (typeof player.currentSources === 'function') {
        var src = player.currentSources();
        player.src(src);
        if (typeof replaceVideoSourcesPerOfflineVersion === 'function') {
            replaceVideoSourcesPerOfflineVersion();
        }
    }
}

var initdone = false;
var startCurrentTime = 0;
var forceCurrentTime = null;
function setCurrentTime(currentTime) {
    //console.log("setCurrentTime:", currentTime, forceCurrentTime);
    if (forceCurrentTime !== null) {
        startCurrentTime = forceCurrentTime;
        currentTime = forceCurrentTime;
        forceCurrentTime = null;
        //console.log("forceCurrentTime:", currentTime);
    } else if (startCurrentTime != currentTime) {
        startCurrentTime = currentTime;
        //console.log("setCurrentTime changed:", currentTime);
    }
    //console.log('setCurrentTime', currentTime);
    if (typeof player !== 'undefined') {
        if (isTryingToPlay) {
            if (currentTime <= player.currentTime()) {
                //console.log('setCurrentTime is trying to play', currentTime);
                return false; // if is trying to play, only update if the time is greater
            }
        }
        player.currentTime(currentTime);
        initdone = false;
        // wait for video metadata to load, then set time 
        player.on("loadedmetadata", function () {
            //console.log('setCurrentTime loadedmetadata', currentTime);
            //player.currentTime(currentTime);
        });
        // iPhone/iPad need to play first, then set the time
        // events: https://www.w3.org/TR/html5/embedded-content-0.html#mediaevents
        player.on("canplaythrough", function () {
            if (!initdone) {
                console.log('setCurrentTime canplaythrough', currentTime);
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

function isALiveContent() {
    if (typeof isLive !== 'undefined' && isLive && (typeof isOnlineLabel === 'undefined' || isOnlineLabel === true || $('.liveOnlineLabel').hasClass('label-success'))) {
        return true;
    }
    return false;
}

function isWebRTC() {
    if (typeof _isWebRTC !== 'undefined') {
        return _isWebRTC;
    }
    return false;
}

function isAutoplayEnabled() {
    //consoleLog("Cookies.get('autoplay')", Cookies.get('autoplay'));
    if (typeof forceNotautoplay !== 'undefined' && forceNotautoplay) {
        return false;
    } else if (typeof forceautoplay !== 'undefined' && forceautoplay) {
        return true;
    } else if (isWebRTC()) {
        consoleLog("isAutoplayEnabled said No because is WebRTC ");
        return false;
    } else if (isALiveContent()) {
        consoleLog("isAutoplayEnabled always autoplay live contents");
        return true;
    } else
    if ($("#autoplay").length) {
        autoplay = $("#autoplay").is(":checked");
        consoleLog("isAutoplayEnabled #autoplay said " + ((autoplay) ? "Yes" : "No"));
        setAutoplay(autoplay);
        return autoplay;
    } else if (
            typeof Cookies !== 'undefined' &&
            typeof Cookies.get('autoplay') !== 'undefined'
            ) {
        if (Cookies.get('autoplay') === 'true' || Cookies.get('autoplay') == true) {
            consoleLog("isAutoplayEnabled Cookie said Yes ");
            setAutoplay(true);
            return true;
        } else {
            consoleLog("isAutoplayEnabled Cookie said No ");
            setAutoplay(false);
            return false;
        }
    } else {
        if (typeof autoplay !== 'undefined') {
            consoleLog("isAutoplayEnabled autoplay said " + ((autoplay) ? "Yes" : "No"));
            setAutoplay(autoplay);
            return autoplay;
        }
    }
    setAutoplay(false);
    consoleLog("isAutoplayEnabled Default is No ");
    return false;
}

function setAutoplay(value) {
    Cookies.set('autoplay', value, {
        path: '/',
        expires: 365
    });
}

async function showAutoPlayVideoDiv() {
    var auto = $("#autoplay").prop('checked');
    if (!auto) {
        $('#autoPlayVideoDiv').slideUp();
    } else {
        $('#autoPlayVideoDiv').slideDown();
    }
}

function enableAutoPlay() {
    setAutoplay(true);
    checkAutoPlay();
}

function disableAutoPlay() {
    setAutoplay(false);
    checkAutoPlay();
}

async function checkAutoPlay() {
    if (isAutoplayEnabled()) {
        $("#autoplay").prop('checked', true);
        $('.autoplay-button').addClass('checked');
        avideoTooltip(".autoplay-button", "Autoplay is ON");
    } else {
        $("#autoplay").prop('checked', false);
        $('.autoplay-button').removeClass('checked');
        avideoTooltip(".autoplay-button", "Autoplay is OFF");
    }
    showAutoPlayVideoDiv();
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
    if (typeof msg !== 'string') {
        return false;
    }
    avideoAlertHTMLText(title, msg, type);
}

function avideoAlertOnce(title, msg, type, uid) {
    var cookieName = 'avideoAlertOnce' + uid;
    if (!Cookies.get(cookieName)) {
        var span = document.createElement("span");
        span.innerHTML = msg;
        swal({
            title: title,
            content: span,
            icon: type,
            closeOnClickOutside: false,
            closeModal: true
        }).then(okay => {
            if (okay) {
                Cookies.set(cookieName, 1, {
                    path: '/',
                    expires: 365
                });
            }
        });
    }
}

async function avideoConfirm(msg) {
    var span = document.createElement("span");
    span.innerHTML = msg;
    var response = await swal({
        title: 'Confrim',
        content: span,
        icon: 'warning',
        closeOnClickOutside: false,
        closeModal: true,
        buttons: {
            cancel: "Cancel",
            confirm: {
                text: "Confirm",
                value: "confirm",
                className: "btn-danger",
            },
        }
    }).then(function (value) {
        return value == 'confirm';
    });
    return response;
}

function avideoAlertOnceForceConfirm(title, msg, type) {
    var span = document.createElement("span");
    span.innerHTML = msg;
    swal({
        title: title,
        content: span,
        icon: type,
        closeOnClickOutside: false,
        closeModal: true
    });
}

function _avideoToast(msg, icon) {
    var options = {text: msg, hideAfter: 7000};
    if (icon) {
        options.icon = icon;
    }
    $.toast(options);
}
function avideoToast(msg) {
    _avideoToast(msg, null);
}
function avideoToastInfo(msg) {
    _avideoToast(msg, 'info');
}
function avideoToastError(msg) {
    _avideoToast(msg, 'error');
}
function avideoToastSuccess(msg) {
    _avideoToast(msg, 'success');
}
function avideoToastWarning(msg) {
    _avideoToast(msg, 'warning');
}

function avideoAlertAJAXHTML(url) {
    modal.showPleaseWait();
    $.ajax({
        url: url,
        success: function (response) {
            avideoAlertText(response);
            modal.hidePleaseWait();
        }
    });
}

function avideoAlertAJAX(url) {
    modal.showPleaseWait();
    $.ajax({
        url: url,
        success: function (response) {
            avideoResponse(response);
            modal.hidePleaseWait();
        }
    });
}

function avideoAlertHTMLText(title, msg, type) {
    var isErrorOrWarning = (type == 'error' || type == 'warning');
    var span = document.createElement("span");
    span.innerHTML = msg;
    swal({
        title: title,
        content: span,
        icon: type,
        closeModal: true,
        closeOnClickOutside: !isErrorOrWarning,
        buttons: isErrorOrWarning ? null : (empty(type) ? false : true),
    });
}

function avideoModalIframeClose() {
    //console.log('avideoModalIframeClose');
    try {
        swal.close();
    } catch (e) {

    }
    try {
        if (inIframe()) {
            window.parent.swal.close();
        }
    } catch (e) {

    }
}

function avideoModalIframeCloseToastSuccess(msg) {
    avideoModalIframeClose();
    avideoToastSuccess(msg);
    window.parent.avideoToastSuccess(msg);
}

function avideoDialog(url, maximize) {
    if (typeof parent.openWindow === 'function') {
        url = addGetParam(url, 'avideoIframe', 1);
        parent.openWindow(url, iframeAllowAttributes, '', maximize);
    } else {
        avideoModalIframeFullScreen(url);
    }
}

function avideoDialogWithPost(url, params) {
    if (typeof parent.openWindowWithPost === 'function') {
        parent.openWindowWithPost(url, iframeAllowAttributes, params);
    } else {
        openWindowWithPost(url, 'avideoDialogWithPost', params, '');
    }
}

function avideoModalIframe(url) {
    avideoModalIframeWithClassName(url, 'swal-modal-iframe', false);
}

function avideoModalIframeXSmall(url) {
    avideoModalIframeWithClassName(url, 'swal-modal-iframe-xsmall', false);
}

function avideoModalIframeSmall(url) {
    avideoModalIframeWithClassName(url, 'swal-modal-iframe-small', false);
}

function avideoModalIframeLarge(url) {
    avideoModalIframeWithClassName(url, 'swal-modal-iframe-large', false);
}

function avideoModalIframeFullScreen(url) {
    avideoModalIframeWithClassName(url, 'swal-modal-iframe-full', true);
}

function avideoModalIframeFullWithMinimize(url) {
    if (false && typeof parent.openWindow === 'function') {
        parent.openWindow(url, iframeAllowAttributes, '', true);
    } else {
        avideoModalIframeWithClassName(url, 'swal-modal-iframe-full-with-minimize', true);
    }
}

function avideoModalIframeFullTransparent(url) {
    avideoModalIframeWithClassName(url, 'swal-modal-iframe-full-transparent', false);
}

function avideoModalIframeFullScreenMinimize() {
    $('.swal-modal-iframe-full-with-minimize').closest('.swal-overlay').addClass('swal-offline-video-compress');
}

function avideoModalIframeFullScreenMaximize() {
    $('.swal-modal-iframe-full-with-minimize').closest('.swal-overlay').removeClass('swal-offline-video-compress');
}

function avideoModalIframeFullScreenClose() {
    if (typeof swal === 'function') {
        $('.swal-overlay iframe').attr('src', 'about:blank');
        try {
            /*
             $('.swal-overlay').slideUp();
             setTimeout(function(){
             swal.close();
             },500);
             */
            swal.close();
        } catch (e) {

        }
    }
}
// this is to make sure when the use click on the back page button it will close the iframe
window.onload = function () {
    if (typeof history.pushState === "function") {
        ////console.log('history.pushState loaded');
        window.onpopstate = function (e) {
            ////console.log('onpopstate', e.state, history.state);
            avideoModalIframeFullScreenClose();
        };
    }
}

function avideoModalIframeFull(url) {
    avideoModalIframeFullScreen(url);
}

function avideoAddIframeIntoElement(element, url, insideSelector) {
    url = addGetParam(url, 'avideoIframe', 1);
    //console.log('avideoAddIframeIntoElement', url, element);
    var html = '';
    html += '<iframe frameBorder="0" class="avideoIframeIntoElement" src="' + url + '"  ' + iframeAllowAttributes + ' ></iframe>';

    var insideElement = $(element);

    if (!empty(insideSelector)) {
        insideElement = $(element).find(insideSelector);
    }

    insideElement.append(html);
}

function avideoWindowIframe(url) {
    url = addGetParam(url, 'avideoIframe', 1);
    //console.log('avideoModalIframeWithClassName', url);
    var html = '';
    html += '<div class="panel panel-default" id="draggable" style="width: 400px; height: 200px; float: left; z-index: 9999;">';
    html += '<div class="panel-heading" style="cursor: move;">head</div>';
    html += '<div class="panel-body" style="padding: 0;">';
    html += '<iframe id="avideoWindowIframe" frameBorder="0" class="animate__animated animate__bounceInDown" src="' + url + '"  ' + iframeAllowAttributes + '></iframe>';
    html += '</div>';
    html += '</div>';
    $('body').append(html);
    $("#draggable").draggable({handle: ".panel-heading", containment: "parent"});
    //$( "div, p" ).disableSelection();
    $("#draggable").resizable();
}

var avideoModalIframeFullScreenOriginalURL = false;
var avideoModalIframeWithClassNameTimeout;
var avideoModalIframeFullScreenMinimize;
function avideoModalIframeWithClassName(url, className, updateURL) {
    var closeModal = true;
    showURL = document.location.href;
    if (updateURL) {
        if (!avideoModalIframeFullScreenOriginalURL) {
            avideoModalIframeFullScreenOriginalURL = document.location.href;
        }
        showURL = url;
    }
    url = addGetParam(url, 'avideoIframe', 1);
    //console.log('avideoModalIframeWithClassName', url, className, updateURL);
    var html = '';
    html += '<div id="avideoModalIframeDiv" class="clearfix popover-title">';

    if (typeof avideoModalIframeFullScreenCloseButton === 'undefined') {
        avideoModalIframeFullScreenCloseButtonSmall = '<button class="btn btn-default pull-left" onclick="avideoModalIframeFullScreenClose();">';
        avideoModalIframeFullScreenCloseButtonSmall += '<i class="fas fa-chevron-left"></i>';
        avideoModalIframeFullScreenCloseButtonSmall += '</button>';

        avideoModalIframeFullScreenCloseButton = avideoModalIframeFullScreenCloseButtonSmall;
    }
    avideoModalIframeFullScreenMaximize();
    if (className === 'swal-modal-iframe-full-with-minimize') {
        html += '<button class="btn btn-default pull-right swal-modal-iframe-full-with-minimize-btn" onclick="avideoModalIframeFullScreenMinimize();">';
        html += '<i class="fas fa-compress-arrows-alt"></i>';
        html += '</button>';
        html += '<button class="btn btn-default pull-right swal-modal-iframe-full-with-maximize-btn" onclick="avideoModalIframeFullScreenMaximize();">';
        html += '<i class="fas fa-expand-arrows-alt"></i>';
        html += '</button>';
        showURL = document.location.href;
        closeModal = false;
    }

    if (inIframe()) {
        html += avideoModalIframeFullScreenCloseButtonSmall;
    } else {
        html += avideoModalIframeFullScreenCloseButton;
        html += '<img src="' + webSiteRootURL + 'videos/userPhoto/logo.png" class="img img-responsive swal-modal-logo" style="max-height:34px;">';
    }

    html += '</div>';
    html += '<iframe id="avideoModalIframe" frameBorder="0" class="animate__animated animate__bounceInDown" src="' + url + '"  ' + iframeAllowAttributes + ' ></iframe>';

    try {
        console.log('avideoModalIframeWithClassName window.history.pushState showURL', showURL);
        avideoPushState(showURL);
    } catch (e) {

    }

    var span = document.createElement("span");
    span.innerHTML = html;
    $('.swal-overlay').show();
    swal({
        content: span,
        closeModal: closeModal,
        buttons: false,
        className: className,
        onClose: avideoModalIframeRemove
    }).then(() => {
        if (avideoModalIframeFullScreenOriginalURL) {
            //console.log('avideoModalIframeWithClassName window.history.pushState avideoModalIframeFullScreenOriginalURL', avideoModalIframeFullScreenOriginalURL);
            avideoPushState(avideoModalIframeFullScreenOriginalURL);
            avideoModalIframeFullScreenOriginalURL = false;
        }
    });
    setTimeout(function () {
        if (!isSameDomain(url)) {
            //console.log('avideoModalIframeWithClassName different domain');
            avideoModalIframeRemove();
        } else {
            var contentLoaded = false;
            try {
                $('#avideoModalIframe').load(function () {
                    contentLoaded = true;
                    //console.log('avideoModalIframeWithClassName content loaded 1');
                    clearTimeout(avideoModalIframeWithClassNameTimeout);
                    avideoModalIframeRemove();
                });
            } catch (e) {
            }

            if ($('#avideoModalIframe').contents().find("body").length) {
                //console.log('avideoModalIframeWithClassName content loaded 2');
                contentLoaded = true;
            }

            if (contentLoaded) {
                //console.log('avideoModalIframeWithClassName content loaded 3');
                clearTimeout(avideoModalIframeWithClassNameTimeout);
                avideoModalIframeRemove();
            } else {
                //console.log('avideoModalIframeWithClassName content loaded 4');
                clearTimeout(avideoModalIframeWithClassNameTimeout);
                avideoModalIframeWithClassNameTimeout = setTimeout(function () {
                    if (!$('#avideoModalIframe').contents().find("body").length) {
                        console.log('avideoModalIframeWithClassName content NOT loaded');
                        // is not loaded
                        url = addGetParam(url, 'avideoIframe', 0);
                        if (isSameDomain(url)) {
                            document.location = url;
                        }
                    }
                }, 5000);
            }
        }
    }, 1000);
}

function avideoPushState(url) {
    window.history.pushState("", "", url);
    if (typeof parent.updatePageSRC == 'funciton') {
        console.log('avideoPushState', url);
        parent.updatePageSRC(url);
    }
}

function checkIframeLoaded(id) {
    // Get a handle to the iframe element
    var iframe = document.getElementById(id);
    var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    // Check if loading is complete
    if (iframeDoc.readyState == 'complete') {
        //iframe.contentWindow.alert("Hello");
        iframe.contentWindow.onload = function () {
            alert("I am loaded");
        };
        // The loading is complete, call the function we want executed once the iframe is loaded
        afterLoading();
        return;
    }

    // If we are here, it is not loaded. Set things up so we check   the status again in 100 milliseconds
    window.setTimeout(checkIframeLoaded, 100);
}

function avideoModalIframeIsVisible() {
    var modal = '';
    if ($('.swal-modal-iframe-xsmall').length) {
        modal = $('.swal-modal-iframe-xsmall');
    } else if ($('.swal-modal-iframe-small').length) {
        modal = $('.swal-modal-iframe-small');
    } else if ($('.swal-modal-iframe-large').length) {
        modal = $('.swal-modal-iframe-large');
    } else if ($('.swal-modal-iframe-full').length) {
        modal = $('.swal-modal-iframe-full');
    } else if ($('.swal-modal-iframe-full-transparent').length) {
        modal = $('.swal-modal-iframe-full-transparent');
    } else if ($('.swal-modal-iframe-full-with-minimize').length) {
        modal = $('.swal-modal-iframe-full-with-minimize');
    } else {
        modal = $('.swal-modal-iframe');
    }

    if (modal.parent().hasClass('swal-overlay--show-modal')) {
        return true;
    } else {
        return false;
    }
}

function avideoModalIframeRemove() {
    if (avideoModalIframeIsVisible()) {
        setTimeout(function () {
            avideoModalIframeRemove();
        }, 1000);
    } else {
        //console.log('avideoModalIframeRemove');
        $('.swal-content').html('');
    }
}

function avideoResponse(response) {
    //console.log('avideoResponse', response);
    if (typeof response === 'string') {
        response = JSON.parse(response);
    }
    //console.log('avideoResponse', response);
    if (response.error) {
        if (!response.msg) {
            if (typeof response.error === 'string') {
                response.msg = response.error;
            } else {
                response.msg = 'Error';
            }
        }
        avideoAlertError(response.msg);
    } else {
        if (!response.msg) {
            response.msg = 'Success';
        }
        if (response.warning) {
            avideoToastWarning(response.msg);
        } else if (response.info) {
            avideoToastInfo(response.msg);
        } else {
            avideoToastSuccess(response.msg);
        }
    }
}

function avideoAlertText(msg) {
    avideoAlert("", msg, '');
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
    $(selector).tooltip({html: true});
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

function playerIsPlayingAds() {
    return ($("#mainVideo_ima-ad-container").length && $("#mainVideo_ima-ad-container").is(':visible')) && player.ima.getAdsManager().getRemainingTime() > 0;
}

function playerHasAds() {
    return ($("#mainVideo_ima-ad-container").length > 0);
}

function pauseIfIsPlayinAds() { // look like the mobile does not know if is playing ads
    if (!isMobile() && !player.paused() && playerHasAds() && playerIsPlayingAds()) {
        //player.pause();
    }
}

function countToOrRevesrse(selector, total) {
    var text = $(selector).text();
    if (isNaN(text)) {
        current = 0;
    } else {
        current = parseInt(text);
    }
    total = parseInt(total);

    if (current <= total) {
        countTo(selector, total);
    } else {
        countToReverse(selector, total);
    }
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

function countToReverse(selector, total) {
    var text = $(selector).text();
    if (isNaN(text)) {
        return false;
    } else {
        current = parseInt(text);
    }
    total = parseInt(total);
    if (!total || current <= total) {
        $(selector).removeClass('loading');
        return;
    }
    var rest = (current - total);
    var step = parseInt(rest / 100);
    if (step < 1) {
        step = 1;
    }
    current -= step;
    $(selector).text(current);
    var timeout = (500 / rest);
    setTimeout(function () {
        countToReverse(selector, total);
    }, timeout);
}

if (typeof showPleaseWaitTimeOut == 'undefined') {
    var showPleaseWaitTimeOut = 0;
}

var tabsCategoryDocumentHeight = 0;
function tabsCategoryDocumentHeightChanged() {
    var newHeight = $(document).height();
    if (tabsCategoryDocumentHeight !== newHeight) {
        tabsCategoryDocumentHeight = newHeight;
        return true;
    }
    return false;
}

async function checkDescriptionArea() {
    $(".descriptionArea").each(function (index) {
        if ($(this).height() < $(this).find('.descriptionAreaContent').height()) {
            $(this).find('.descriptionAreaShowMoreBtn').show();
        }
    });
}
function clearCache(showPleaseWait, FirstPage, sessionOnly) {
    if (showPleaseWait) {
        modal.showPleaseWait();
    }
    $.ajax({
        url: webSiteRootURL + 'objects/configurationClearCache.json.php?FirstPage=' + FirstPage + '&sessionOnly=' + sessionOnly,
        success: function (response) {
            if (showPleaseWait) {
                avideoResponse(response);
                modal.hidePleaseWait();
            }
        }
    });
}

function validURL(str) {
    var pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+:]*)*' + // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
            '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
    return !!pattern.test(str);
}

function isURL(url) {
    return validURL(url);
}
var startTimerInterval = [];
async function startTimer(duration, selector, prepend) {
    ////console.log('startTimer 1', duration);
    clearInterval(startTimerInterval[selector]);
    var timer = duration;
    startTimerInterval[selector] = setInterval(function () {

        // Time calculations for days, hours, minutes and seconds
        var years = Math.floor(duration / (60 * 60 * 24 * 365));
        var days = Math.floor((duration % (60 * 60 * 24 * 365)) / (60 * 60 * 24));
        var hours = Math.floor((duration % (60 * 60 * 24)) / (60 * 60));
        var minutes = Math.floor((duration % (60 * 60)) / (60));
        var seconds = Math.floor((duration % (60)));
        // Display the result in the element with id="demo"
        var text = '';
        if (years) {
            text += years + 'y ';
        }
        if (days || text) {
            text += days + 'd ';
        }
        if (hours || text) {
            text += hours + 'h ';
        }
        if (minutes || text) {
            text += minutes + 'm ';
        }
        if (seconds || text) {
            text += seconds + 's ';
        }
        // If the count down is finished, write some text
        if (duration < 0) {
            clearInterval(startTimerInterval[selector]);
            //$(selector).text("EXPIRED");
            startTimerTo(duration * -1, selector);
        } else {
            $(selector).html(prepend + text);
            duration--;
        }

    }, 1000);
}

var startTimerToInterval = [];
function startTimerTo(durationTo, selector) {
    clearInterval(startTimerToInterval[selector]);
    startTimerToInterval[selector] = setInterval(function () {

        // Time calculations for days, hours, minutes and seconds
        var years = Math.floor(durationTo / (60 * 60 * 24 * 365));
        var days = Math.floor((durationTo % (60 * 60 * 24 * 365)) / (60 * 60 * 24));
        var hours = Math.floor((durationTo % (60 * 60 * 24)) / (60 * 60));
        var minutes = Math.floor((durationTo % (60 * 60)) / (60));
        var seconds = Math.floor((durationTo % (60)));
        // Display the result in the element with id="demo"
        var text = '';
        if (years) {
            text += years + 'y ';
        }
        if (days || text) {
            text += days + 'd ';
        }
        if (hours || text) {
            text += hours + 'h ';
        }
        if (minutes || text) {
            text += minutes + 'm ';
        }
        if (seconds || text) {
            text += seconds + 's ';
        }
        $(selector).text(text);
        durationTo++;
    }, 1000);
}

var startTimerToDateTimeOut = [];
function startTimerToDate(toDate, selector, useDBDate) {
    clearTimeout(startTimerToDateTimeOut[selector]);
    if (typeof _serverTime === 'undefined') {
        ////console.log('startTimerToDate _serverTime is undefined');
        getServerTime();
        startTimerToDateTimeOut[selector] = setTimeout(function () {
            startTimerToDate(toDate, selector, useDBDate)
        }, 1000);
        return false;
    }
    if (typeof toDate === 'string') {
        ////console.log('startTimerToDate 1 '+toDate);
        toDate = new Date(toDate.replace(/-/g, "/"));
    }
    if (useDBDate) {
        if (typeof _serverDBTimeString !== 'undefined') {
            date2 = new Date(_serverDBTimeString.replace(/-/g, "/"));
            ////console.log('startTimerToDate 2 '+date2);
        }
    } else {
        if (typeof _serverTimeString !== 'undefined') {
            date2 = new Date(_serverTimeString.replace(/-/g, "/"));
            ////console.log('startTimerToDate 3 '+date2);
        }
    }
    if (typeof date2 === 'undefined') {
        date2 = new Date();
        ////console.log('startTimerToDate 4 '+date2);
    }

    var seconds = (toDate.getTime() - date2.getTime()) / 1000;
    ////console.log('startTimerToDate toDate', toDate);
    ////console.log('startTimerToDate selector', selector);
    ////console.log('startTimerToDate seconds', seconds);
    return startTimer(seconds, selector, toDate.toLocaleString() + '<br>');
}

var _timerIndex = 0;
function createTimer(selector) {
    var toDate = $(selector).text();
    var id = $(selector).attr('id');
    if (!id) {
        _timerIndex++;
        id = 'timer_' + _timerIndex;
        $(selector).attr('id', id);
    }

    startTimerToDate(toDate, '#' + id, true);
}

var getServerTimeActive = 0;
async function getServerTime() {
    if (getServerTimeActive || _serverTime) {
        return false;
    }
    if (typeof webSiteRootURL == 'undefined') {
        setTimeout(function () {
            getServerTime();
        }, 1000);
        return false;
    }
    getServerTimeActive = 1;
    var d = new Date();
    $.ajax({
        url: webSiteRootURL + 'objects/getTimes.json.php',
        success: function (response) {
            //console.log('getServerTime', response);
            _serverTime = response._serverTime;
            _serverDBTime = response._serverDBTime;
            _serverTimeString = response._serverTimeString;
            _serverDBTimeString = response._serverDBTimeString;
            _serverTimezone = response._serverTimezone;
            _serverDBTimezone = response._serverDBTimezone;
            _serverSystemTimezone = response._serverSystemTimezone;
            //console.log('getServerTime _serverDBTimezone', _serverDBTimezone, response._serverDBTimezone);
            setInterval(function () {
                _serverTime++;
                _serverDBTime++;
                _serverTimeString = new Date(_serverTime * 1000).toISOString().slice(0, 19).replace('T', ' ');
                _serverDBTimeString = new Date(_serverDBTime * 1000).toISOString().slice(0, 19).replace('T', ' ');
            }, 1000);
        }
    });
}

function clearServerTime() {
    //console.log('clearServerTime');
    _serverTime = null;
    _serverDBTime = null;
    _serverTimeString = null;
    _serverDBTimeString = null;
}

function convertDBDateToLocal(dbDateString) {
    if (!/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/.test(dbDateString)) {
        //console.log('convertDBDateToLocal format does not match', dbDateString);
        return dbDateString;
    }
    checkMoment();
    dbDateString = $.trim(dbDateString.replace(/[^ 0-9:-]/g, ''));
    var m;
    if (!_serverDBTimezone) {
        getServerTime();
        //console.log('convertDBDateToLocal _serverDBTimezone is empty', dbDateString);
        m = moment.tz(dbDateString);
    } else {
        _serverDBTimezone = $.trim(_serverDBTimezone);
        //m = moment(dbDateString).tz(_serverDBTimezone);
        //m = moment.tz(dbDateString, _serverDBTimezone);
        m = moment.tz(dbDateString, _serverDBTimezone).local();
    }
    var fromNow = m.fromNow();
    consolelog('convertDBDateToLocal', dbDateString, _serverDBTimezone, fromNow);
    return fromNow;
}

function convertDateFromTimezoneToLocal(dbDateString, timezone) {
    if (!/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/.test(dbDateString)) {
        //console.log('convertDBDateToLocal format does not match', dbDateString);
        return dbDateString;
    }
    checkMoment();
    dbDateString = $.trim(dbDateString.replace(/[^ 0-9:-]/g, ''));
    timezone = $.trim(timezone);
    var m = moment.tz(dbDateString, timezone).local();
    return m.format("YYYY-MM-DD HH:mm:ss");
}

function checkMoment() {
    /*
     while(typeof moment === 'undefined' || moment.tz !== 'function'){
     console.log('checkMoment Waiting moment.tz to load');
     delay(1);
     }
     */
}

function addGetParam(_url, _key, _value) {
    if (typeof _url !== 'string') {
        return false;
    }
    if (typeof _value == 'undefined' || _value == 'undefined' || _value == '') {
        return _url;
    }
    var param = _key + '=' + escape(_value);
    var sep = '&';
    if (_url.indexOf('?') < 0) {
        sep = '?';
    } else {
        var lastChar = _url.slice(-1);
        if (lastChar == '&')
            sep = '';
        if (lastChar == '?')
            sep = '';
    }
    _url += sep + param;
    _url = removeDuplicatedGetParam(_url);
    return _url;
}

function addQueryStringParameter(_url, _key, _value) {
    return addGetParam(_url, _key, _value);
}

function removeDuplicatedGetParam(_url) {
    var queryParam = _url.replace(/^[^?]+\?/, '');
    if (queryParam == '') {
        return _url;
    }
    var params = queryParam.split('&'),
            results = {};
    for (var i = 0; i < params.length; i++) {
        var temp = params[i].split('='),
                key = temp[0],
                val = temp[1];
        results[key] = val;
    }

    var newQueryParam = [];
    for (var key in results) {
        newQueryParam.push(key + '=' + results[key]);
    }
    var newQueryParamString = newQueryParam.join('&');
    return _url.replace(queryParam, newQueryParamString);
}

function removeGetParam(_url, parameter) {
    var queryParam = _url.replace(/^[^?]+\?/, '');
    if (queryParam == '') {
        return _url;
    }
    var params = queryParam.split('&'),
            results = {};
    for (var i = 0; i < params.length; i++) {
        var temp = params[i].split('='),
                key = temp[0],
                val = temp[1];
        if (key !== parameter) {
            results[key] = val;
        }
    }

    var newQueryParam = [];
    for (var key in results) {
        newQueryParam.push(key + '=' + results[key]);
    }

    var newQueryParamString = newQueryParam.join('&');
    queryParam = '?' + queryParam;
    if (!empty(newQueryParamString)) {
        newQueryParamString = '?' + newQueryParamString;
    }
    return _url.replace(queryParam, newQueryParamString);
}

function readFileCroppie(input, crop) {
    if ($(input)[0].files && $(input)[0].files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            crop.croppie('bind', {
                url: e.target.result
            }).then(function () {
                //console.log('jQuery bind complete');
            });
        }

        reader.readAsDataURL($(input)[0].files[0]);
    } else {
        avideoAlert("Sorry - you're browser doesn't support the FileReader API");
    }
}

function getCroppie(uploadCropObject, callback, width, height) {
    //console.log('getCroppie 1', uploadCropObject);
    var ret = uploadCropObject.croppie('result', {type: 'base64', size: {width: width, height: height}, format: 'png'}).then(function (resp) {
        ////console.log('getCroppie 2 ' + callback, resp);
        eval(callback + "(resp);");
    }).catch(function (err) {
        //console.log('cropieError getCroppie => ' + callback, err);
        eval(callback + "(null);");
    });
    //console.log('getCroppie 3', ret);
}

async function setToolTips() {
    var selector = '[data-toggle="tooltip"]';
    if (!$(selector).not('.alreadyTooltip').length) {
        return false;
    }
    try {
        $(selector).not('.alreadyTooltip').tooltip({container: 'body', html: true});
        $(selector).not('.alreadyTooltip').on('click', function () {
            var t = this;
            setTimeout(function () {
                try {
                    $(t).tooltip('hide');
                } catch (e) {

                }
            }, 2000);
        });
        $(selector).addClass('alreadyTooltip');
    } catch (e) {
        console.log('setToolTips', e);
        setTimeout(function () {
            setToolTips();
        }, 1000);
    }

}

function avideoSocketIsActive() {
    if (typeof isSocketActive == 'function') {
        return isSocketActive();
    } else {
        return false;
    }
}

function isMediaSiteURL(url) {
    if (validURL(url)) {
        if (url.match(/youtube/i) ||
                url.match(/youtu\.be/i) ||
                url.match(/vimeo/i) ||
                url.match(/dailymotion/i) ||
                url.match(/metacafe/i) ||
                url.match(/vid\.me/i) ||
                url.match(/rutube\.ru/i) ||
                url.match(/ok\.ru/i) ||
                url.match(/streamable/i) ||
                url.match(/twitch/i) ||
                url.match(/evideoEmbed/i) ||
                url.match(/videoEmbeded/i)) {
            return true;
        }
    }
    return false;
}

function avideoSocket() {
    if (typeof parseSocketResponse === 'function') {
        parseSocketResponse();
    }
}

function changeVideoStatus(videos_id, status) {
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'objects/videoStatus.json.php',
        data: {"id": [videos_id], "status": status},
        type: 'post',
        success: function (response) {
            modal.hidePleaseWait();
            if (response.error) {
                avideoToast("Sorry!", response.msg, "error");
            } else {

                for (var item in response.status) {
                    var videos_id = response.status[item].videos_id
                    $(".getChangeVideoStatusButton_" + videos_id).removeClass('status_a');
                    $(".getChangeVideoStatusButton_" + videos_id).removeClass('status_u');
                    $(".getChangeVideoStatusButton_" + videos_id).removeClass('status_i');
                    $(".getChangeVideoStatusButton_" + videos_id).removeClass('status_s');
                    $(".getChangeVideoStatusButton_" + videos_id).addClass('status_' + response.status[item].status);
                }


            }
        }
    });
}

function avideoAjax(url, data) {
    avideoAjax2(url, data, true);
}

function avideoAjax2(url, data, pleaseWait) {
    if(pleaseWait){
        modal.showPleaseWait();
    }
    $.ajax({
        url: url,
        data: data,
        type: 'post',
        success: function (response) {
            if(pleaseWait){
                modal.hidePleaseWait();
            }
            if (response.error) {
                avideoAlertError(response.msg);
            } else {
                avideoToastSuccess(response.msg);
                if (typeof response.eval !== 'undefined') {
                    eval(response.eval);
                }
            }
        }
    });
}

function isPlayerUserActive() {
    return $('#mainVideo').hasClass("vjs-user-active");
}

eventer('beforeunload', function (e) {
    ////console.log('window.addEventListener(beforeunload');
    _addViewAsync();
}, false);
eventer('visibilitychange', function () {
    if (document.visibilityState === 'hidden') {
        _addViewAsync();
    }
});
function socketClearSessionCache(json) {
    //console.log('socketClearSessionCache', json);
    clearCache(false, 0, 1);
}

async function animateChilds(selector, type, delay) {
    var step = delay;
    $(selector).children().each(function () {
        var $currentElement = $(this);
        $currentElement.addClass('animate__animated');
        $currentElement.addClass(type);
        $currentElement.css('-webkit-animation-delay', step + "s");
        $currentElement.css('animation-delay', step + "s");
        step += delay;
    });
}

function goToURLOrAlertError(jsonURL, data) {
    modal.showPleaseWait();
    $.ajax({
        url: jsonURL,
        method: 'POST',
        data: data,
        success: function (response) {
            if (response.error) {
                avideoAlertError(response.msg);
                modal.hidePleaseWait();
            } else if (response.url) {
                if (response.msg) {
                    avideoAlertInfo(response.msg);
                }
                document.location = response.url;
                setTimeout(function () {
                    modal.hidePleaseWait();
                }, 3000)
            } else {
                avideoResponse(response);
                modal.hidePleaseWait();
            }
        }
    });
}

function downloadURL(url, filename) {
    filename = clean_name(filename) + '.' + clean_name(url.split(/[#?]/)[0].split('.').pop().trim());
    console.log('downloadURL start ', url, filename);
    var loaded = 0;
    var contentLength = 0;
    fetch(url)
            .then(response => {
                avideoToastSuccess('Download Start');
                const contentEncoding = response.headers.get('content-encoding');
                const contentLength = response.headers.get(contentEncoding ? 'x-file-size' : 'content-length');
                if (contentLength === null) {
                    throw Error('Response size header unavailable');
                }

                const total = parseInt(contentLength, 10);
                let loaded = 0;
                return new Response(
                        new ReadableStream({
                            start(controller) {
                                const reader = response.body.getReader();
                                read();
                                function read() {
                                    reader.read().then(({ done, value }) => {
                                        if (done) {
                                            controller.close();
                                            return;
                                        }
                                        loaded += value.byteLength;
                                        var percentageLoaded = Math.round(loaded / total * 100);
                                        ////console.log(percentageLoaded);
                                        modal.setProgress(percentageLoaded);
                                        modal.setText('Downloading ... ' + percentageLoaded + '%');
                                        controller.enqueue(value);
                                        read();
                                    }).catch(error => {
                                        console.error(error);
                                        controller.error(error)
                                    })
                                }
                            }
                        })
                        );
            })
            .then(response => response.blob())
            .then(blob => {
                const urlFromBlob = window.URL.createObjectURL(blob);
                console.log('downloadURL', url, filename, blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = urlFromBlob;
                // the filename you want
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                modal.hidePleaseWait();
                avideoToastSuccess('Download complete ' + filename);
            })
            .catch(function (err) {
                //avideoAlertError('Error on download ');
                console.log(err);
                addQueryStringParameter(url, 'download', 1);
                addQueryStringParameter(url, 'title', filename);
                document.location = url;
            });
}

var downloadURLOrAlertErrorInterval;
function downloadURLOrAlertError(jsonURL, data, filename, FFMpegProgress) {
    if (empty(jsonURL)) {
        console.log('downloadURLOrAlertError error empty jsonURL', jsonURL, data, filename, FFMpegProgress);
        return false;
    }
    modal.showPleaseWait();
    avideoToastInfo('Converting');
    console.log('downloadURLOrAlertError 1', jsonURL, FFMpegProgress);
    checkFFMPEGProgress(FFMpegProgress);
    $.ajax({
        url: jsonURL,
        method: 'POST',
        data: data,
        success: function (response) {
            clearInterval(downloadURLOrAlertErrorInterval);
            if (response.error) {
                avideoAlertError(response.msg);
                modal.hidePleaseWait();
            } else if (response.url) {
                if (response.msg) {
                    avideoAlertInfo(response.msg);
                }
                if (
                        isMobile()
                        //|| /cdn.ypt.me/.test(response.url)
                        ) {
                    window.open(response.url, '_blank');
                    avideoToastInfo('Opening file');
                    //document.location = response.url
                } else {
                    downloadURL(response.url, filename);
                }
            } else {
                avideoResponse(response);
                modal.hidePleaseWait();
            }
        }
    });
}

function checkFFMPEGProgress(FFMpegProgress) {
    if (empty(FFMpegProgress)) {
        return false;
    }
    $.ajax({
        url: FFMpegProgress,
        success: function (response) {
            //console.log(response);
            if (typeof response.progress.progress !== 'undefined') {
                var text = 'Converting ...';
                if (typeof response.progress.progress !== 'undefined') {
                    text += response.progress.progress + '% ';
                    modal.setProgress(response.progress.progress);
                }
                modal.setText(text);
                if (response.progress.progress !== 100) {
                    setTimeout(function () {
                        checkFFMPEGProgress(FFMpegProgress);
                    }, 1000);
                }
            }
        }
    });
}

function startGoogleAd(selector) {
    if (isVisibleAndInViewport(selector)) {
        //console.log('startGoogleAd', selector);
        try {
            (adsbygoogle = window.adsbygoogle || []).push({});
        } catch (e) {
            //console.log('startGoogleAd ERROR', selector, $(selector), e);
        }

    } else {
        setTimeout(function () {
            startGoogleAd(selector);
        }, 1000);
    }
}

function isVisibleAndInViewport(selector) {
    if ($(selector).is(":visible")) {
        var elementTop = $(selector).offset().top;
        var elementBottom = elementTop + $(selector).outerHeight();
        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop + $(window).height();
        return elementBottom > viewportTop && elementTop < viewportBottom;
    } else {
        return false;
    }
}

var playAudioTimeout = [];
var showEnableAudioMessage = true;
var audioList = [];
function playAudio(mp3) {
    clearTimeout(playAudioTimeout[mp3]);
    playAudioTimeout[mp3] = setTimeout(function () {
        var audio = new Audio();
        audio.autoplay = true;
        audio.src = "data:audio/mpeg;base64,SUQzBAAAAAABEVRYWFgAAAAtAAADY29tbWVudABCaWdTb3VuZEJhbmsuY29tIC8gTGFTb25vdGhlcXVlLm9yZwBURU5DAAAAHQAAA1N3aXRjaCBQbHVzIMKpIE5DSCBTb2Z0d2FyZQBUSVQyAAAABgAAAzIyMzUAVFNTRQAAAA8AAANMYXZmNTcuODMuMTAwAAAAAAAAAAAAAAD/80DEAAAAA0gAAAAATEFNRTMuMTAwVVVVVVVVVVVVVUxBTUUzLjEwMFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/zQsRbAAADSAAAAABVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/zQMSkAAADSAAAAABVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV";
        audio.src = mp3;
        audioList[mp3] = audio;
        //console.log('playAudio', audioList);
        ////console.log('pling setTimeout', audio);
        const promise = audio.play();
        if (promise !== undefined) {
            ////console.log('pling promise', promise);
            promise.then((response) => {
                ////console.log('pling audio played', response);
                plingEnabled = false;
                setTimeout(function () {
                    plingEnabled = true;
                }, 3000);
            }).catch(error => {
                ////console.log('pling audio disabled', error);
                if (showEnableAudioMessage) {
                    showEnableAudioMessage = false;
                    avideoAlertInfo('Click here to enable audio');
                }
            });
        }
    }, 500);
    return playAudioTimeout[mp3];
}

function stopAllAudio() {
    var audios = document.getElementsByTagName('audio');
    for (var i = 0, len = audios.length; i < len; i++) {
        if (audios[i] != e.target) {
            audios[i].pause();
        }
    }
    for (var i in audioList) {
        if (typeof audioList[i] === 'object') {
            audioList[i].pause();
        }
    }
}

function isSameDomain(url) {
    var hrefURL, pageURL;
    hrefURL = new URL(url);
    pageURL = new URL(window.location);
    if (url.startsWith("/") || hrefURL.host === pageURL.host) {
        return true;
    }
    return false;
}

function empty(data) {
    var type = typeof (data);
    if (type == 'undefined' || data === null) {
        return true;
    } else if (type === 'function') {
        return false;
    } else if (type === 'number') {
        return data == 0;
    } else if (type === 'boolean') {
        return !data;
    } else if (type === 'string') {
        return /^[\s]*$/.test(data);
    } else if (type !== 'undefined') {
        return Object.keys(data).length == 0;
    }
    for (var i in data) {
        if (data.hasOwnProperty(i)) {
            return false;
        }
    }
    return true;
}

function in_array(needle, haystack) {
    var length = haystack.length;
    for (var i = 0; i < length; i++) {
        if (haystack[i] == needle)
            return true;
    }
    return false;
}

function replaceLast(find, replace, string) {
    var lastIndex = string.lastIndexOf(find);

    if (lastIndex === -1) {
        return string;
    }

    var beginString = string.substring(0, lastIndex);
    var endString = string.substring(lastIndex + find.length);

    return beginString + replace + endString;
}


function getCursorPos(input) {
    if ("selectionStart" in input && document.activeElement == input) {
        return {
            start: input.selectionStart,
            end: input.selectionEnd
        };
    } else if (input.createTextRange) {
        var sel = document.selection.createRange();
        if (sel.parentElement() === input) {
            var rng = input.createTextRange();
            rng.moveToBookmark(sel.getBookmark());
            for (var len = 0; rng.compareEndPoints("EndToStart", rng) > 0; rng.moveEnd("character", -1)) {
                len++;
            }
            rng.setEndPoint("StartToStart", input.createTextRange());
            for (var pos = {start: 0, end: len}; rng.compareEndPoints("EndToStart", rng) > 0; rng.moveEnd("character", -1)) {
                pos.start++;
                pos.end++;
            }
            return pos;
        }
    } else if (document.getSelection) {    // all browsers, except IE before version 9
        var sel = document.getSelection();
        return {
            start: sel.anchorOffset,
            end: sel.focusOffset
        };
    }
    return -1;
}

function isUserOnline(users_id) {
    users_id = parseInt(users_id);
    if (typeof users_id_online === 'undefined' || empty(users_id_online)) {
        return false;
    }
    if (typeof users_id_online[users_id] === 'undefined' || empty(users_id_online[users_id])) {
        return false;
    }
    if (empty(users_id_online[users_id].resourceId)) {
        return false;
    }
    return users_id_online[users_id];
}

function isReadyToCheckIfIsOnline() {
    return !empty(users_id_online);
}

var addAtMentionActive = false;
function addAtMention(selector) {
    var emojioneArea = false;
    if (typeof $(selector).data("emojioneArea") !== 'undefined') {
        emojioneArea = selector;
        selector = '.emojionearea-editor';
    }
    //console.log('addAtMention(selector)', selector, emojioneArea);
    var SpaceKeyCode = ' '.charCodeAt(0);
    var AtMatcher = /^@.+/i;
    $(selector).on("keydown", function (event) {
        if (!$(this).autocomplete("instance").menu.active) {
            if (
                    event.keyCode === SpaceKeyCode ||
                    event.keyCode === $.ui.keyCode.TAB ||
                    event.keyCode === $.ui.keyCode.ENTER ||
                    event.keyCode === $.ui.keyCode.ESCAPE) {
                $(this).autocomplete("close");
            }
        } else {
            if ((event.keyCode === $.ui.keyCode.TAB)) {
                event.preventDefault();
            }
        }
    })
            .autocomplete({
                minLength: 2,
                source: function (request, response) {

                    var pos = getCursorPos($(selector)[0]);
                    stringStart = request.term.substring(0, pos.end);

                    var term = stringStart.split(/\s+/).pop();
                    //console.log('autocomplete', request.term, term, AtMatcher.test(term));
                    if (AtMatcher.test(term)) {
                        $.ajax({
                            url: webSiteRootURL + "objects/mention.json.php",
                            data: {
                                term: term
                            },
                            success: function (data) {
                                response(data);
                            }
                        });
                    } else {
                        return false;
                    }
                },
                focus: function () {
                    // prevent value inserted on focus
                    return false;
                },
                select: function (event, ui) {
                    addAtMentionActive = true;
                    setTimeout(function () {
                        addAtMentionActive = false;
                    }, 200);
                    if (emojioneArea) {
                        this.value = $(emojioneArea).data("emojioneArea").getText();
                    }
                    //console.log('addAtMention', this, this.value);
                    var pos = getCursorPos($(selector)[0]);
                    stringStart = this.value.substring(0, pos.end);
                    stringEnd = this.value.substring(pos.end);

                    var terms = stringStart.split(/\s+/);
                    // remove the current input
                    var word = terms.pop();
                    // add the selected item
                    //terms.push('@' + ui.item.value);
                    // add placeholder to get the comma-and-space at the end
                    //terms.push("");
                    replace = '@' + ui.item.value;

                    this.value = replaceLast(word, '@' + ui.item.value, stringStart) + stringEnd;
                    if (emojioneArea) {
                        $(emojioneArea).data("emojioneArea").setText(this.value);
                        setTimeout(function () {
                            contentEditableElement = document.getElementsByClassName("emojionearea-editor")[0];
                            range = document.createRange();//Create a range (a range is a like the selection but invisible)
                            range.selectNodeContents(contentEditableElement);//Select the entire contents of the element with the range
                            range.collapse(false);//collapse the range to the end point. false means collapse to end rather than the start
                            selection = window.getSelection();//get the selection object (allows you to change selection)
                            selection.removeAllRanges();//remove any selections already made
                            selection.addRange(range);//make the range you have just created the visible selection
                        }, 50);
                    }
                    return false;
                },
                create: function () {
                    $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                        return $('<li>' + item.label + '</li>').appendTo(ul); // customize your HTML
                    };
                },
                position: {collision: "flip"}
            });
}
/*
 async function selectAElements() {
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
 }*/

var hidePleaseWaitTimeout = {};
var pleaseWaitIsINUse = {};
var pleaseNextIndex = 0;
function getPleaseWait() {
    return (function () {
        var index = pleaseNextIndex;
        pleaseNextIndex++;
        var selector = "#pleaseWaitDialog_" + index;
        var pleaseWaitDiv = $(selector);
        if (pleaseWaitDiv.length === 0) {
            //console.log('getPleaseWait', index);
            if (typeof avideoLoader == 'undefined') {
                avideoLoader = '';
            }
            pleaseWaitDiv = $('<div id="pleaseWaitDialog_' + index + '" class="pleaseWaitDialog modal fade"  data-backdrop="static" data-keyboard="false">' + avideoLoader + '<h2 style="display:none;">Processing...</h2><div class="progress" style="display:none;"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div></div></div>').appendTo('body');
        }

        return {
            showPleaseWait: function () {
                if (!empty(pleaseWaitIsINUse[index])) {
                    console.log('showPleaseWait is in use', index, new Error().stack);
                    return false;
                }
                pleaseWaitIsINUse[index] = true;
                $(selector).removeClass('loaded');
                $(selector).find('.progress').hide();
                this.setText('Processing...');
                $(selector).find('h2').hide();
                this.setProgress(0);
                $(selector).find('.progress').hide();
                pleaseWaitDiv.modal();
            },
            hidePleaseWait: function () {
                clearTimeout(hidePleaseWaitTimeout[index]);
                hidePleaseWaitTimeout[index] = setTimeout(function () {
                    setTimeout(function () {
                        $(selector).addClass('loaded');
                    }, showPleaseWaitTimeOut / 2);
                    setTimeout(function () {
                        pleaseWaitDiv.modal('hide');
                    }, showPleaseWaitTimeOut); // wait for loader animation
                    setTimeout(function () {
                        pleaseWaitIsINUse[index] = false;
                    }, showPleaseWaitTimeOut + 1000);
                }, 500);
            },
            setProgress: function (valeur) {
                var progressSelector = selector + ' .progress';
                //console.log('showPleaseWait setProgress', progressSelector);
                $(progressSelector).slideDown();
                $(selector).find('.progress-bar').css('width', valeur + '%').attr('aria-valuenow', valeur);
            },
            setText: function (text) {
                var textSelector = selector + ' h2';
                //console.log('showPleaseWait setText', textSelector);
                $(textSelector).slideDown();
                $(textSelector).html(text);
            },
            getProgressSelector: function () {
                var progressSelector = selector + ' .progress';
                return progressSelector;
            },
        };
    })();
}

$(document).ready(function () {
    getServerTime();
    addViewFromCookie();
    checkDescriptionArea();
    setInterval(function () {// check for the carousel
        checkDescriptionArea();
    }, 3000);
    Cookies.set('timezone', timezone, {
        path: '/',
        expires: 365
    });
    tabsCategoryDocumentHeight = $(document).height();
    if (typeof $('.nav-tabs-horizontal').scrollingTabs == 'function') {
        $('.nav-tabs-horizontal').scrollingTabs();
        //$('.nav-tabs-horizontal').fadeIn();
    }
    setInterval(function () {
        if (tabsCategoryDocumentHeightChanged()) {
            if (typeof $('.nav-tabs-horizontal').scrollingTabs == 'function') {
                $('.nav-tabs-horizontal').scrollingTabs('refresh');
            }
        }
    }, 2000);
    modal = getPleaseWait();
    try {
        $('[data-toggle="popover"]').popover();
    } catch (e) {

    }

    setInterval(function () {
        setToolTips();
    }, 5000);
    lazyImage();
    //aHrefToAjax();
    //selectAElements();
    $('#clearCache, .clearCacheButton').on('click', function (ev) {
        ev.preventDefault();
        clearCache(true, 0, 0);
    });
    $('.clearCacheFirstPageButton').on('click', function (ev) {
        ev.preventDefault();
        clearCache(true, 1, 0);
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
    checkSocketStatus();
    checkSavedCookies();
    $("input.saveCookie").change(function () {
        var auto = $(this).prop('checked');
        Cookies.set($(this).attr("name"), auto, {
            path: '/',
            expires: 365
        });
    });
    if (isAutoplayEnabled()) {
        $("#autoplay").prop('checked', true);
    }
    $("#autoplay").change(function () {
        checkAutoPlay();
    });
    checkAutoPlay();
    // Code to handle install prompt on desktop
    //aHrefToAjax();

    _alertFromGet('error');
    _alertFromGet('msg');
    _alertFromGet('success');
    _alertFromGet('toast');

});

/*!
 * Sanitize an HTML string
 * (c) 2021 Chris Ferdinandi, MIT License, https://gomakethings.com
 * @param  {String}          str   The HTML string to sanitize
 * @param  {Boolean}         nodes If true, returns HTML nodes instead of a string
 * @return {String|NodeList}       The sanitized string or nodes
 */
function cleanHTML (str, nodes) {

	/**
	 * Convert the string to an HTML document
	 * @return {Node} An HTML document
	 */
	function stringToHTML () {
		let parser = new DOMParser();
		let doc = parser.parseFromString(str, 'text/html');
		return doc.body || document.createElement('body');
	}

	/**
	 * Remove <script> elements
	 * @param  {Node} html The HTML
	 */
	function removeScripts (html) {
		let scripts = html.querySelectorAll('script');
		for (let script of scripts) {
			script.remove();
		}
	}

	/**
	 * Check if the attribute is potentially dangerous
	 * @param  {String}  name  The attribute name
	 * @param  {String}  value The attribute value
	 * @return {Boolean}       If true, the attribute is potentially dangerous
	 */
	function isPossiblyDangerous (name, value) {
		let val = value.replace(/\s+/g, '').toLowerCase();
		if (['src', 'href', 'xlink:href'].includes(name)) {
			if (val.includes('javascript:') || val.includes('data:text/html')) return true;
		}
		if (name.startsWith('on')) return true;
	}

	/**
	 * Remove potentially dangerous attributes from an element
	 * @param  {Node} elem The element
	 */
	function removeAttributes (elem) {

		// Loop through each attribute
		// If it's dangerous, remove it
		let atts = elem.attributes;
		for (let {name, value} of atts) {
			if (!isPossiblyDangerous(name, value)) continue;
			elem.removeAttribute(name);
		}

	}

	/**
	 * Remove dangerous stuff from the HTML document's nodes
	 * @param  {Node} html The HTML document
	 */
	function clean (html) {
		let nodes = html.children;
		for (let node of nodes) {
			removeAttributes(node);
			clean(node);
		}
	}

	// Convert the string to HTML
	let html = stringToHTML();

	// Sanitize it
	removeScripts(html);
	clean(html);

	// If the user wants HTML nodes back, return them
	// Otherwise, pass a sanitized string back
	return nodes ? html.childNodes : html.innerHTML;

}

async function _alertFromGet(type) {
    if (urlParams.has(type)) {
        var msg = urlParams.get(type);
        var div = document.createElement("div");
        div.innerHTML = cleanHTML(msg, false);
        var text = div.textContent || div.innerText || "";
        if (!empty(text)) {
            switch (type) {
                case 'error':
                    avideoAlertError(text);
                    break;
                case 'msg':
                    avideoAlertInfo(text);
                    break;
                case 'success':
                    avideoAlertSuccess(text);
                    break;
                case 'toast':
                    avideoToast(text);
                    break;
            }
            var url = removeGetParam(window.location.href, type);
            window.history.pushState({}, document.title, url);
        }
    }
}


async function checkSocketStatus() {
    if (typeof conn != 'undefined') {
        if (avideoSocketIsActive()) {
            $(".socketStatus").removeClass('disconnected');
        } else {
            $(".socketStatus").addClass('disconnected');
        }
    }
    setTimeout(function () {
        checkSocketStatus();
    }, 1000);
}

async function checkSavedCookies() {
    $("input.saveCookie").each(function () {
        var mycookie = Cookies.get($(this).attr('name'));
        if (mycookie && mycookie == "true") {
            $(this).prop('checked', mycookie);
        }
    });
}

function openWindow(url) {
    var windowObject = window.open(url, '_blank').focus();
    return windowObject;
}

function openWindowWithPost(url, name, params, strWindowFeatures) {
    if (empty(strWindowFeatures)) {
        strWindowFeatures = "directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,resizable=no,height=600,width=800";
    }
    var windowObject = window.open("about:blank", name, strWindowFeatures);
    postFormToTarget(url, name, params);
    return windowObject;
}

function postFormToTarget(url, name, params) {
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", url);
    form.setAttribute("target", name);
    for (var i in params) {
        if (params.hasOwnProperty(i)) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = i;
            input.value = params[i];
            form.appendChild(input);
        }
    }
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
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

/**
 * recreate the sources from the video source tags
 * @type type
 */
var videoJSRecreateSourcesTimeout;
async function videoJSRecreateSources(defaultSource) {
    clearTimeout(videoJSRecreateSourcesTimeout);
    if (empty(player) || empty(player.options_)) {
        videoJSRecreateSourcesTimeout = setTimeout(function () {
            videoJSRecreateSources(defaultSource);
        }, 1000);
        console.log('videoJSRecreateSources player is empty');
        return false;
    }

    var newSources = [];

    $("#mainVideo source").each(function (index) {
        var res = $(this).attr("res");
        if (empty(res)) {
            res = 'auto';
        }
        var source = {
            res: $(this).attr("res"),
            label: $(this).attr("label"),
            type: $(this).attr("type"),
            src: $(this).attr("src"),
        };
        ////console.log('videoJSRecreateSources', $(this), source);
        newSources.push(source);
    });
    if (empty(newSources)) {
        console.log('videoJSRecreateSources: source are empty');
        return false;
    }

    player.options_.sources = newSources;
    if (!empty(player.updateSrc)) {
        player.updateSrc(player.options_.sources);
    }
    if (!empty(player.currentResolution) && !empty(defaultSource)) {
        player.currentResolution(defaultSource.label, null);
    }
    if (!empty(fixResolutionMenu)) {
        fixResolutionMenu();
    }
}

/**
 * 
 * MEDIA_ERR_ABORTED (numeric value 1)
 MEDIA_ERR_NETWORK (numeric value 2)
 MEDIA_ERR_DECODE (numeric value 3)
 MEDIA_ERR_SRC_NOT_SUPPORTED (numeric value 4)
 MEDIA_ERR_ENCRYPTED (numeric value 5)
 */
var AvideoJSErrorReloadedTimes = 0;
function AvideoJSError(code) {
    switch (code) {
        case 1:
        case 2:
        case 3:
        case 4:
            if (empty(AvideoJSErrorReloadedTimes)) {
                AvideoJSErrorReloadedTimes++;
                console.log('AvideoJSError reloadVideoJS in 2 sec');
                setTimeout(function () {
                    //reloadVideoJS();
                }, 2000);
            } else if (AvideoJSErrorReloadedTimes === 1) {
                console.log('AvideoJSError reloadDefaultHTML5Player');
                AvideoJSErrorReloadedTimes++;
                //var sources = player.currentSources();
                //reloadDefaultHTML5Player();
            }
            break;
    }
}

function reloadDefaultHTML5Player() {
    var videoElement;
    if ($('#mainVideo video').length) {
        videoElement = $('#mainVideo video').clone();
    } else if ($('#mainVideo').length) {
        videoElement = $('#mainVideo').clone();
    } else {
        return false;
    }
    videoElement.attr('id', 'mainVideo');
    videoElement.attr('controls', 'controls');
    videoElement.removeClass('vjs-tech');
    player.dispose();

    $("#main-video").empty();
    $("#main-video").append(videoElement);

    player = document.getElementById("mainVideo");
}

function isPromise(p) {
    if (typeof p === 'object' && typeof p.then === 'function') {
        return true;
    }

    return false;
}

function replaceAll(str, find, replace) {
    return str.replace(new RegExp(find, 'g'), replace);
}

function getExtension(url) {
    if (empty(url)) {
        return false;
    }
    let domain = (new URL(url));
    var extension = domain.pathname.split('.').pop().toLowerCase();
    return extension;
}

function getMimeType(url) {
    if (empty(url)) {
        return false;
    }
    var extension = getExtension(url);
    var type = 'text/plain';
    if (extension === 'js') {
        type = 'application/javascript';
    } else if (extension === 'css') {
        type = 'text/css';
    } else if (extension === 'ico') {
        type = 'image/x-icon';
    } else if (extension === 'jpg' || extension === 'jpeg') {
        type = 'image/jpeg';
    } else if (extension === 'gif') {
        type = 'image/gif';
    } else if (extension === 'webp') {
        type = 'image/webp';
    } else if (extension === 'woff') {
        type = 'font/woff';
    } else if (extension === 'woff2') {
        type = 'font/woff2';
    } else if (extension === 'pdf') {
        type = 'application/pdf';
    } else if (extension === 'zip') {
        type = 'application/zip';
    }
    return type;
}

function isValidURL(value) {
    if (empty(value)) {
        return false;
    }
    if (/^(ws|wss):\/\//i.test(value)) {
        return true;
    }
    if (/^(https?|ftp):\/\//i.test(value)) {
        return true;
    }
    return /^(?:(?:(?:https?|ftp|ws|wss):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i.test(value);
}

function blobToURL(blob, type) {
    blob = blob.slice(0, blob.size, type);
    var src;
    if (window.webkitURL != null) {
        src = window.webkitURL.createObjectURL(blob);
    } else {
        src = window.URL.createObjectURL(blob);
    }
    return src;
}

function isOnline() {
    //console.log('window.navigator.onLine', window.navigator.onLine);
    return window.navigator.onLine;
}
var notifyInputIfIsOutOfBounds_removeClassTImeout;
var notifyInputIfIsOutOfBounds_animateClassTImeout;
function notifyInputIfIsOutOfBounds(selector, min_length, max_length) {
    clearTimeout(notifyInputIfIsOutOfBounds_removeClassTImeout);
    clearTimeout(notifyInputIfIsOutOfBounds_animateClassTImeout);
    var text = $(selector).val();
    var parent = $(selector).parent();
    var animationInfo = 'animate__headShake';
    var animationError = 'animate__shakeX';
    var animationWarning = 'animate__headShake';
    parent.removeClass('has-error');
    parent.removeClass('has-warning');
    parent.removeClass('has-info');
    parent.removeClass('has-success');
    parent.removeClass('has-feedback');
    $(selector).removeClass(animationInfo);
    $(selector).removeClass(animationError);
    $(selector).removeClass(animationWarning);
    $(selector).addClass('animate__animated');
    parent.find('.help-block').remove();
    parent.find('.form-control-feedback').remove();
    var isRequired = min_length == 0 || !empty($(selector).attr('required'));
    var icon = '';
    var feedback = '';
    var force_length = parseInt($(selector).attr('maxlength'));

    if (text.length == 0 && !isRequired) {

    } else if (isTextOutOfBounds(text, min_length, max_length, isRequired)) {
        var feedbackIcon = 'fas fa-exclamation';
        parent.addClass('has-feedback');
        if (!empty(force_length) && text.length >= force_length) {
            text = text.substr(0, force_length);
            $(selector).val(text);
            icon = '<i class="fas fa-exclamation-triangle"></i>';
            parent.addClass('has-info');
            notifyInputIfIsOutOfBounds_animateClassTImeout = setTimeout(function () {
                $(selector).addClass(animationInfo);
            }, 500);
        } else if (text.length < min_length || !isRequired) {
            icon = '<i class="fas fa-exclamation-circle"></i>';
            parent.addClass('has-warning');
            notifyInputIfIsOutOfBounds_animateClassTImeout = setTimeout(function () {
                $(selector).addClass(animationWarning);
            }, 500);
        } else {
            icon = '<i class="fas fa-exclamation-circle"></i>';
            parent.addClass('has-error');
            feedbackIcon = 'fas fa-times';
            notifyInputIfIsOutOfBounds_animateClassTImeout = setTimeout(function () {
                $(selector).addClass(animationError);
            }, 500);
        }
        feedback = '<i class="' + feedbackIcon + ' form-control-feedback" style="right:15px;"></i>';
    } else {
        //console.log('notifyInputIfIsOutOfBounds', text.length, force_length);
        if (!empty(force_length) && text.length == force_length) {
            notifyInputIfIsOutOfBounds_animateClassTImeout = setTimeout(function () {
                $(selector).addClass(animationInfo);
            }, 500);
        }
        icon = '<i class="fas fa-check-circle"></i>';
        parent.addClass('has-success');
    }
    notifyInputIfIsOutOfBounds_removeClassTImeout = setTimeout(function () {
        $(selector).removeClass(animationInfo);
        $(selector).removeClass(animationError);
        $(selector).removeClass(animationWarning);
    }, 1000);
    parent.append(feedback + '<small class="help-block">' + icon + ' ' + text.length + ' characters of ' + min_length + '-' + max_length + ' recommended</small>');
}

function passStrengthCheck(selector) {
    var minLen = 6;
    var pass = $(selector).val();

    var strength = 0;
    var strengthMsg = [];
    if (pass.length > minLen) {
        strength++;
    } else {
        strengthMsg.push('Min length ' + minLen);
    }
    if (/[a-z]+/.test(pass)) {
        strength++;
    } else {
        strengthMsg.push('Lower case letters');
    }
    if (/[A-Z]+/.test(pass)) {
        strength++;
    } else {
        strengthMsg.push('Upper case letters');
    }
    if (/[0-9]+/.test(pass)) {
        strength++;
    } else {
        strengthMsg.push('Numbers');
    }
    if (/[^a-z0-9]+/i.test(pass)) {
        strength++;
    } else {
        strengthMsg.push('Special chars');
    }
    return {strength: strength, strengthMsg: strengthMsg};
}

function passStrengthCheckInput(selector) {
    var strengthCheck = passStrengthCheck(selector);
    var msg = strengthCheck.strengthMsg;
    var parent = $(selector).parent();
    parent.removeClass('has-error');
    parent.removeClass('has-warning');
    parent.removeClass('has-success');
    avideoTooltip(selector, '');
    var pass = $(selector).val();
    if (empty(pass)) {
        return false;
    }
    switch (strengthCheck.strength) {
        case 0:
        case 1:
        case 2:
            parent.addClass('has-error');
            break;
        case 3:
        case 4:
            parent.addClass('has-warning');
            break;
        case 5:
            parent.addClass('has-success');
            break;
    }
    if (!empty(msg)) {
        var text = msg.join(', ');
        avideoTooltip(selector, 'Strength: ' + text);
    }
    return true;
}

function passStrengthCheckInputKeyUp(selector) {
    $(selector).keyup(function () {
        passStrengthCheckInput('#' + $(this).attr('id'));
    });
}

function setupFormElement(selector, min_length, max_length, force_length, isRequired) {
    $(selector).attr('min_length', min_length);
    $(selector).attr('max_length', max_length);
    if (!isRequired) {
        $(selector).removeAttr('required');
    } else {
        $(selector).attr('required', 'required');
    }
    if (force_length) {
        $(selector).attr('maxlength', max_length);
        $(selector).attr('minlength', min_length);
    }
    $(selector).keyup(function () {
        notifyInputIfIsOutOfBounds('#' + $(this).attr('id'), $(this).attr('min_length'), $(this).attr('max_length'));
    });
}

var notifyInputIfIsWrongFormat_removeClassTImeout;
var notifyInputIfIsWrongFormat_animateClassTImeout;
function notifyInputIfIsWrongFormat(_this, isValid) {
    clearTimeout(notifyInputIfIsWrongFormat_removeClassTImeout);
    clearTimeout(notifyInputIfIsWrongFormat_animateClassTImeout);
    var text = $(_this).val();
    var parent = $(_this).parent();
    var animationError = 'animate__shakeX';
    var feedback = '';
    parent.removeClass('has-error');
    parent.removeClass('has-success');
    $(_this).removeClass(animationError);
    $(_this).addClass('animate__animated');
    parent.find('.help-block').remove();
    parent.find('.form-control-feedback').remove();
    if (!isValid) {
        feedbackIcon = 'fas fa-times';
        parent.addClass('has-error');
        notifyInputIfIsWrongFormat_animateClassTImeout = setTimeout(function () {
            $(_this).addClass(animationError);
        }, 1000);
    } else {
        feedbackIcon = 'fas fa-check';
        parent.addClass('has-success');
    }
    feedback = '<i class="' + feedbackIcon + ' form-control-feedback" style="top: 25px;right:15px;"></i>';
    notifyInputIfIsWrongFormat_removeClassTImeout = setTimeout(function () {
        $(_this).removeClass(animationError);
    }, 1000);
    parent.append(feedback);
    $(_this).val(text);
}

function setupMySQLInput(selector) {
    if (typeof $(selector).inputmask !== 'function') {
        addScript(webSiteRootURL + 'node_modules/inputmask/dist/jquery.inputmask.min.js');
        setTimeout(function () {
            setupMySQLInput(selector);
        }, 1000);
        return false;
    }
    $(selector).inputmask({
        mask: "9999-99-99 99:99:99",
        onincomplete: function (buffer, opts) {
            notifyInputIfIsWrongFormat($(this), false);
        },
        oncomplete: function (buffer, opts) {
            notifyInputIfIsWrongFormat($(this), true);
        }
    });
}

function isTextOutOfBounds(text, min_length, max_length, isRequired) {
    //console.log('isTextOutOfBounds', text, min_length, max_length, allow_null);
    if (empty(text)) {
        if (!empty(min_length) && isRequired) {
            //console.log('isTextOutOfBounds 1');
            return true;
        } else {
            //console.log('isTextOutOfBounds 2');
            return false;
        }
    }
    if (text.length < min_length) {
        //console.log('isTextOutOfBounds 3');
        return true;
    }
    if (text.length > max_length) {
        //console.log('isTextOutOfBounds 4');
        return true;
    }
    //console.log('isTextOutOfBounds 5');
    return false;
}

/**
 * Usage: setVideoSuggested(videos_id, isSuggested).then((data) => {...}).catch((error) => {console.log(error)});
 * @param {type} videos_id
 * @param {type} isSuggested
 * @returns {Promise}
 */
async function setVideoSuggested(videos_id, isSuggested) {
    modal.showPleaseWait();
    return new Promise((resolve, reject) => {
        $.ajax({
            url: webSiteRootURL + 'objects/videoSuggest.php',
            data: {"id": videos_id, "isSuggested": isSuggested},
            type: 'post',
            success: function (data) {
                modal.hidePleaseWait();
                avideoResponse(data);
                resolve(data)
            },
            error: function (error) {
                modal.hidePleaseWait();
                reject(error)
            },
        })
    })
}

function toogleVideoSuggested(btn) {
    var videos_id = $(btn).attr('videos_id');
    var isSuggested = $(btn).hasClass('isSuggested');
    setVideoSuggested(videos_id, !isSuggested).then((data) => {
        if (!isSuggested) {
            $(btn).removeClass('isNotSuggested btn-default');
            $(btn).addClass('isSuggested btn-warning');
        } else {
            $(btn).addClass('isNotSuggested btn-default');
            $(btn).removeClass('isSuggested btn-warning');
        }
    }).catch((error) => {
        console.log(error)
    });
}


// Cookie functions stolen from w3schools
function setCookie(cname, cvalue, exdays) {
    Cookies.set(cname, cvalue, {
        path: '/',
        expires: exdays
    });
}

function getCookie(cname) {
    return Cookies.get(cname);
}

function delay(time) {
    return new Promise(resolve => setTimeout(resolve, time));
}

function arrayToTemplate(itemsArray, template) {
    if (typeof itemsArray == 'function') {
        return '';
    }
    if (typeof template !== 'string') {
        console.error('arrayToTemplate', typeof template, template);
        return '';
    }
    for (var search in itemsArray) {
        var replace = itemsArray[search];
        if (typeof replace == 'function') {
            continue;
        }
        template = template.replace(new RegExp('{' + search + '}', 'g'), replace);
    }
    template = template.replace(new RegExp('{[^\}]}', 'g'), '');
    return template;
}
/*
 function avideoLoadPage(url) {
 console.log('avideoLoadPage', url);
 avideoPushState(url);
 if (inMainIframe()) {
 parent.avideoLoadPage(url);
 } else {
 document.location = url;
 }
 }
 
 function avideoLoadPage3(url) {
 console.log('avideoLoadPage3', url);
 avideoPushState(url);
 if (inMainIframe()) {
 parent.modal.showPleaseWait();
 } else {
 modal.showPleaseWait();
 }
 $.ajax({
 url: url,
 success: function (data) {
 var parser = new DOMParser();
 var htmlDoc = parser.parseFromString(data, "text/html");
 $('body').fadeOut('fast', function () {
 var head = $(htmlDoc).find('head');
 $('head').html(head.html());
 var selector = 'body > .container-fluid, body > .container';
 var container = $(htmlDoc).find(selector).html();
 $(selector).html(container);
 var scriptsToAdd = $(htmlDoc).find('body script');
 addScripts(scriptsToAdd);
 var footerCode = $(htmlDoc).find('#pluginFooterCode').html();
 $('#pluginFooterCode').html(footerCode);
 $('body').fadeIn('fast', function () {
 if (inMainIframe()) {
 parent.modal.hidePleaseWait();
 parent.updatePageFromIframe();
 } else {
 modal.hidePleaseWait();
 }
 //aHrefToAjax();
 });
 });
 }
 });
 }
 
 function avideoLoadPage2(url) {
 console.log('avideoLoadPage', url);
 avideoPushState(url);
 modal.showPleaseWait();
 $.ajax({
 url: url,
 success: function (data) {
 var parser = new DOMParser();
 var htmlDoc = parser.parseFromString(data, "text/html");
 
 $('body').fadeOut('fast', function () {
 var bodyElement = $(htmlDoc).find('body');
 var head = $(htmlDoc).find('head').html();
 var body = bodyElement.html();
 var _class = bodyElement.attr('class');
 var id = bodyElement.attr('id');
 var style = bodyElement.attr('style');
 $('head').html(head);
 $('body').attr('class', _class);
 $('body').attr('id', id);
 $('body').attr('style', style);
 $('body').html(body);
 $('#pluginFooterCode').fadeIn('slow', function () {
 modal.hidePleaseWait();
 });
 });
 }
 });
 }
 
 
 async function aHrefToAjax() {
 if(typeof useIframe === 'undefined' || !useIframe){
 return false;
 }
 $('a.aHrefToAjax').off('click');
 $('a').click(function (evt) {
 var target = $(this).attr('target');
 $(this).addClass('aHrefToAjax');
 if (empty(target)) {
 var url = $(this).attr('href');
 if (isValidURL(url)) {
 evt.preventDefault();
 avideoLoadPage(url);
 return false;
 }
 }
 });
 }
 
 function addScripts(scriptsToAdd) {
 var localScripts = $("script");
 for (index in scriptsToAdd) {
 var script = scriptsToAdd[index];
 if (typeof script === 'object') {
 var src = $(script).attr('src');
 console.log(typeof script, typeof $(script));
 if (empty(src)) {
 try {
 $('body').append(script);
 } catch (e) {
 
 }
 } else {
 var scriptFound = false;
 localScripts.each(function () {
 var _src = $(this).attr('src');
 
 if (src === _src) {
 scriptFound = true;
 return false;
 }
 });
 if (!scriptFound) {
 $('<script src="' + src + '" type="text/javascript"></script>').appendTo(document.body);
 }
 }
 }
 }
 }
 * */

function addScript(src) {
    if (!empty(src)) {
        var localScripts = $("script");
        var scriptFound = false;
        localScripts.each(function () {
            var _src = $(this).attr('src');

            if (src === _src) {
                scriptFound = true;
                return false;
            }
        });
        if (!scriptFound) {
            console.log('addScript', src);
            $('<script src="' + src + '" type="text/javascript"></script>').appendTo(document.body);
        } else {
            console.log('addScript already added ', src);
        }
    }
}

function avideoLogoff(redirect) {
    sendAVideoMobileLiveStreamerMessage('logoff', '');
    if (redirect) {
        document.location = webSiteRootURL + 'logoff';
    }
}

async function sendAVideoMobileLiveStreamerMessage(type, value){
    if (typeof window.flutter_inappwebview !== 'undefined') {
        if (typeof window.flutter_inappwebview.callHandler == 'function') {
            for (i = 0; i < 10; i++) {
                response = await window.flutter_inappwebview.callHandler('AVideoMobileLiveStreamer' + i, {type: type, value: value, instanceIndex: i});
                if(response!==null){
                    console.log('sendAVideoMobileLiveStreamerMessage executed', i, response, type, value);
                    break;
                }else{
                    console.log('sendAVideoMobileLiveStreamerMessage not found', i, type, value);
                }
            }
        } else {
            console.log('sendAVideoMobileLiveStreamerMessage will try again', type, value);
            setTimeout(function () {
                sendAVideoMobileLiveStreamerMessage(type, value);
            }, 1000);
        }
    } else {
        //window.parent.postMessage({type: type, value: value}, '*');
        window.top.postMessage({type: type, value: value}, '*');
    }
}
window.addEventListener("flutterInAppWebViewPlatformReady", function (event) {
    sendAVideoMobileLiveStreamerMessage('APPIsReady', 1);
});

function getUser() {
    var url = webSiteRootURL + 'plugin/API/get.json.php?APIName=user';
    return $.ajax({
        url: url,
        async: false
    }).responseText;
}
