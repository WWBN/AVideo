$(document).ready(function () {
    setInterval(function () {
        if (typeof isOnlineLabel !== 'undefined' && (isOnlineLabel || $('.liveOnlineLabel.label-success').length)) {
            $('body').addClass('isLiveOnline');
            $('#liveControls').slideDown();
        } else {
            $('body').removeClass('isLiveOnline');
            $('#liveControls').slideUp();
        }
    }, 1000);

});