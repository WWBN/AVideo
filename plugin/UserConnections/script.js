function disconnectMe(users_id) {
    avideoConfirm('Are you sure you want to remove this friend connection? This action will cancel any pending friend requests and remove this user from your friends list.').then(response => {
        if (response) {
            _disconnectMe(users_id);
            return true;
        } else {
            return false;
        }
    });
}

function connectMe(users_id) {
    modal.showPleaseWait();
    var url = webSiteRootURL + 'plugin/UserConnections/connectMe.json.php';
    url = addQueryStringParameter(url, 'users_id', users_id);
    $.ajax({
        url: url,
        success: function (response) {
            modal.hidePleaseWait();
            if (response.error) {
                avideoAlertError(response.msg);
                //modal.hidePleaseWait();
            } else {
                setUserConnectButtonsStatus(response.users_id, response.status.mine+response.status.friend);
                avideoToast(response.msg);
            }
        }
    });
}

function setUserConnectButtonsStatus(users_id, status){
    var selector = '.userConnectButtons'+users_id;
    console.log('setUserConnectButtonsStatus', users_id, status, selector);
    $(selector).removeClass('connectionStatus_ii');
    $(selector).removeClass('connectionStatus_an');
    $(selector).removeClass('connectionStatus_na');
    $(selector).removeClass('connectionStatus_aa');

    $(selector).addClass('connectionStatus_'+status);
}

function _disconnectMe(users_id) {
    modal.showPleaseWait();
    var url = webSiteRootURL + 'plugin/UserConnections/disconnectMe.json.php';
    url = addQueryStringParameter(url, 'users_id', users_id);
    $.ajax({
        url: url,
        success: function (response) {
            modal.hidePleaseWait();
            if (response.error) {
                avideoAlertError(response.msg);
                //modal.hidePleaseWait();
            } else {
                setUserConnectButtonsStatus(response.users_id, response.status.mine+response.status.friend);
                avideoToast(response.msg);
            }
        }
    });
}
