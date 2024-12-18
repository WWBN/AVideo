var yptPopupOpened = false;
eventer(messageEvent, function (e) {
    console.log('EventListener ypt', e.data);
    saveYPT(e.data.provider, e.data.name, e.data.parameters);
}, false);

var yptWin;

function openYPT(provider) {
    yptPopupOpened = 1;
    modal.showPleaseWait();
    var url = yptURL + 'confirm/'+provider;
    var name = "theYPTPopUp";
    var params = {
        title: $('#title').val(),
        description: $('#description').val()
    };
    var strWindowFeatures = "directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,resizable=no,height=600,width=800";
    yptWin = openWindowWithPost(url, name, params, strWindowFeatures);
    var pollTimer = window.setInterval(function () {
        if (yptWin.closed !== false) { // !== is required for compatibility with Opera
            window.clearInterval(pollTimer);
            modal.hidePleaseWait();
            yptPopupOpened = 0;
            //avideoToast('closed');
        }
    }, 200);
}

function saveYPT(provider, name, parameters) {
    console.log('saveYPT', provider, name, parameters);
    yptPopupOpened = 0;
    $.ajax({
        url: webSiteRootURL + 'plugin/SocialMediaPublisher/View/Publisher_user_preferences/add.json.php',
        type: "POST",
        data: {
            provider: provider,
            name: name,
            json: parameters,
        },
        success: function (response) {
            modal.hidePleaseWait();
            reloadSocialAccountsTables();
        }
    });
    yptWin.close();
}

function checkIfIsConnected(id) {

    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'plugin/SocialMediaPublisher/isConnected.json.php',
        type: "POST",
        data: {
            id: id,
        },
        success: function (response) {
            modal.hidePleaseWait();
            avideoResponse(response);
            reloadSocialAccountsTables();
        }
    });
}

function uploadToSocial(id, videos_id, title, description, visibility) {

    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'plugin/SocialMediaPublisher/uploadVideo.json.php',
        type: "POST",
        data: {
            id: id,
            videos_id: videos_id,
            title: title,
            description: description,
            visibility: visibility,
        },
        success: function (response) {
            modal.hidePleaseWait();
            avideoResponse(response);
            reloadSocialAccountsTables();
        }
    });
}

function reloadSocialAccountsTables() {
    if (typeof Publisher_user_preferencestableVar !== 'undefined') {
        Publisher_user_preferencestableVar.ajax.reload();
    }
    if (typeof Publisher_social_mediastableVar !== 'undefined') {
        Publisher_social_mediastableVar.ajax.reload();
    }
    if (typeof Publisher_video_publisher_logstableVarVID !== 'undefined') {
        Publisher_video_publisher_logstableVarVID.ajax.reload();
    }

}
    
function checkInstagram(accessToken, containerId, instagramAccountId) {
    var url = webSiteRootURL + 'plugin/SocialMediaPublisher/publishInstagram.json.php';
    url = addQueryStringParameter(url, 'accessToken', accessToken);
    url = addQueryStringParameter(url, 'containerId', containerId);
    url = addQueryStringParameter(url, 'instagramAccountId', instagramAccountId);
    modal.showPleaseWait();
    $.ajax({
        url: url,
        complete: function(response) {
            modal.hidePleaseWait();
            if (response.error) {
                avideoAlertError(response.msg);
            } else {                
                reloadSocialAccountsTables();
            }
        }
    });
}