var modal;
var player;
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

String.prototype.stripAccents = function() {
    var translate_re = /[àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ]/g;
    var translate = 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY';
    return (this.replace(translate_re, function(match){
        return translate.substr(translate_re.source.indexOf(match)-1, 1); })
    );
};

function clean_name(str) {
    
    str = str.stripAccents().toLowerCase();
    return str.replace(/\W+/g, "-");
}

$(document).ready(function () {
    $('[data-toggle="popover"]').popover();
    $('[data-toggle="tooltip"]').tooltip();
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
function strToSeconds(str){
    var partsOfStr = str.split(':');
    var seconds = parseInt(partsOfStr[2]);
    seconds += parseInt(partsOfStr[1])*60;
    seconds += parseInt(partsOfStr[0])*60*60;
    return seconds;
}

/**
 * 
 * @param {int} seconds
 * @param {int} level 3 = 00:00:00 2 = 00:00 1 = 00
 * @returns {String} 00:00:00
 */
function secondsToStr(seconds, level){
    var hours = parseInt(seconds/(60*60));
    var minutes = parseInt(seconds/(60));
    seconds = parseInt(seconds%(60));
    
    hours = hours>9?hours:"0"+hours;
    minutes = minutes>9?minutes:"0"+minutes;
    seconds = seconds>9?seconds:"0"+seconds;
    switch (level){
        case 3:
            return hours+":"+minutes+":"+seconds;
            break;
        case 2:
            return minutes+":"+seconds;
            break;
        case 1:
            return seconds;
            break;
        default:
            return hours+":"+minutes+":"+seconds;
            
    }
}
