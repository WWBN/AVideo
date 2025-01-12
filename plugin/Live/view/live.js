function isInLive(json) {
    selector1 = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
    selector2 = '.liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
    selector3 = '#liveViewStatusID_' + json.cleanKey + '_' + json.live_servers_id;
    selector4 = '.liveViewStatusClass_' + json.cleanKey + '_' + json.live_servers_id;
    //console.log('isInLive 1', json);
    //console.log('isInLive 2', selector1, selector2, selector3, selector4);
    var _isInLive = $(selector1).length || $(selector2).length || $(selector3).length || $(selector4).length;
    //console.log('isInLive 3', $(selector1).length, $(selector2).length, $(selector3).length, $(selector4).length, _isInLive);
    return _isInLive;
}

var prerollPosterAlreadyPlayed = false;
async function showImage(type, key) {
    if (typeof player === 'undefined') {
        return false;
    }
    if (typeof closeLiveImageRoll == 'function') {
        closeLiveImageRoll();
    }
    $('.' + type).remove();
    var img = false;
    console.log('showImage', type, key, player.paused());
    eval('prerollPoster = prerollPoster_' + key);
    eval('postrollPoster = postrollPoster_' + key);
    eval('liveImgCloseTimeInSecondsPreroll = liveImgCloseTimeInSecondsPreroll_' + key);
    eval('liveImgTimeInSecondsPreroll = liveImgTimeInSecondsPreroll_' + key);
    eval('liveImgCloseTimeInSecondsPostroll = liveImgCloseTimeInSecondsPostroll_' + key);
    eval('liveImgTimeInSecondsPostroll = liveImgTimeInSecondsPostroll_' + key);
    var liveImgTimeInSeconds = 30;
    var liveImgCloseTimeInSeconds = 30;
    if (type == 'prerollPoster' && prerollPoster) {
        if (prerollPosterAlreadyPlayed) {
            console.log('showImage prerollPosterAlreadyPlayed');
            return false;
        }
        prerollPosterAlreadyPlayed = true;
        if (player.paused()) {
            setTimeout(function () {
                showImage(type, key);
            }, 1000);
            return false;
        }
        liveImgTimeInSeconds = liveImgTimeInSecondsPreroll;
        liveImgCloseTimeInSeconds = liveImgCloseTimeInSecondsPreroll;
        img = prerollPoster;
    } else if (type == 'postrollPoster' && postrollPoster) {
        liveImgTimeInSeconds = liveImgTimeInSecondsPostroll;
        liveImgCloseTimeInSeconds = liveImgCloseTimeInSecondsPostroll;
        img = postrollPoster;
    }
    console.log('showImage Poster', type, img, key);
    if (img) {

        var _liveImageBGTemplate = liveImageBGTemplate.replace('{liveImgCloseTimeInSeconds}', liveImgCloseTimeInSeconds);
        var _liveImageBGTemplate = _liveImageBGTemplate.replace('{liveImgTimeInSeconds}', liveImgTimeInSeconds);
        var _liveImageBGTemplate = _liveImageBGTemplate.replace('{src}', img);
        _liveImageBGTemplate = _liveImageBGTemplate.replace(/\{class\}/g, type);

        $(_liveImageBGTemplate).appendTo("#mainVideo");
    }

    //console.log('prerollPoster', prerollPoster);
    //console.log('postrollPoster', postrollPoster);
    //console.log('liveImgTimeInSeconds', liveImgTimeInSeconds);
    //console.log('liveImgCloseTimeInSeconds', liveImgCloseTimeInSeconds);
}
