function toogleTagSubscribe(encryptedIdAndUser, notify) {
    var data = { encryptedIdAndUser: encryptedIdAndUser, notify: notify };
    var url = webSiteRootURL + 'plugin/VideoTags/subscribe.json.php';
    modal.showPleaseWait();
    $.ajax({
        url: url,
        data: data,
        type: 'post',
        success: function (response) {
            modal.hidePleaseWait();
            if (response.error) {
                avideoAlertError(response.msg);
            } else {
                avideoToastSuccess(response.msg);
                var className = '.subscribedTags' + response.tags_id;
                if (response.add) {
                    $(className).addClass('subscribed');
                } else {
                    $(className).removeClass('subscribed');
                }
                if (response.notify) {
                    $(className).addClass('notify');
                } else {
                    $(className).removeClass('notify');
                }
            }
        }
    });
}
function loadVideoTagsLabels() {
    if (typeof videoTagsLabels !== 'undefined') {
        var videoTagsLabelsElement = $('<div>' + videoTagsLabels + '</div>');
        videoTagsLabelsElement.addClass('hideOnPlayerUserInactive');
        videoTagsLabelsElement.addClass('pull-right');
        videoTagsLabelsElement.addClass('videoTagsLabelsElement');
        appendOnPlayer(videoTagsLabelsElement);
    } else {
        setTimeout(function () { loadVideoTagsLabels(); }, 1000);
    }
}
$(function () {
    loadVideoTagsLabels();
})