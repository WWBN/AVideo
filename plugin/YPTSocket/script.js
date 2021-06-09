var socketConnectRequested = 0;
var totalDevicesOnline = 0;
var yptSocketResponse;

var socketConnectTimeout;
function socketConnect() {
    if (socketConnectRequested) {
        return false;
    }
    clearTimeout(socketConnectTimeout);
    socketConnectRequested = 1;
    var url = addGetParam(webSocketURL, 'page_title', $('<textarea />').html($(document).find("title").text()).text());
    //console.log('Trying to reconnect on socket... ');
    conn = new WebSocket(url);    
    setSocketIconStatus('loading');
    conn.onopen = function (e) {
        clearTimeout(socketConnectTimeout);
        console.log("Socket onopen");
        onSocketOpen();
        return false;
    };
    conn.onmessage = function (e) {
        var json = JSON.parse(e.data);
        yptSocketResponse = json;
        parseSocketResponse();
        if (json.type == webSocketTypes.ON_VIDEO_MSG) {
            console.log("Socket onmessage ON_VIDEO_MSG", json);
            $('.videoUsersOnline, .videoUsersOnline_' + json.videos_id).text(json.total);
        }
        if (json.type == webSocketTypes.ON_LIVE_MSG) {
            console.log("Socket onmessage ON_LIVE_MSG", json);
            var selector = '#liveViewStatusID_' + json.live_key.key + '_' + json.live_key.live_servers_id;
            if (json.is_live) {
                onlineLabelOnline(selector);
            } else {
                onlineLabelOffline(selector);
            }
        }
        if (json.type == webSocketTypes.NEW_CONNECTION) {
            //console.log("Socket onmessage NEW_CONNECTION", json);
            if (typeof onUserSocketConnect === 'function') {
                onUserSocketConnect(json);
            }
        } else if (json.type == webSocketTypes.NEW_DISCONNECTION) {
            //console.log("Socket onmessage NEW_DISCONNECTION", json);
            if (typeof onUserSocketDisconnect === 'function') {
                onUserSocketDisconnect(json);
            }
        } else {
            var myfunc;
            if (json.callback) {
                console.log("Socket onmessage json.callback", json.callback);
                var code = "if(typeof " + json.callback + " == 'function'){myfunc = " + json.callback + ";}else{myfunc = defaultCallback;}";
                //console.log(code);
                eval(code);
            } else {
                console.log("onmessage: callback not found", json);
                myfunc = defaultCallback;
            }
            myfunc(json.msg);
        }
    };
    conn.onclose = function (e) {
        socketConnectRequested = 0;
        console.log('Socket is closed. Reconnect will be attempted in 15 seconds.', e.reason);
        socketConnectTimeout = setTimeout(function () {
            socketConnect();
        }, 15000);
        onSocketClose();
    };

    conn.onerror = function (err) {
        socketConnectRequested = 0;
        console.error('Socket encountered error: ', err, 'Closing socket');
        conn.close();
    };
}

function onSocketOpen() {
    setSocketIconStatus('connected');
}

function onSocketClose() {
    setSocketIconStatus('disconnected');
}

function setSocketIconStatus(status){
    var selector = '.socket_info';
    if(status=='connected'){
        $(selector).removeClass('socket_loading');
        $(selector).removeClass('socket_disconnected');
        $(selector).addClass('socket_connected');
    }else if(status=='disconnected'){
        $(selector).removeClass('socket_loading');
        $(selector).addClass('socket_disconnected');
        $(selector).removeClass('socket_connected');
    }else{
        $(selector).addClass('socket_loading');
        $(selector).removeClass('socket_disconnected');
        $(selector).removeClass('socket_connected');
    }
}

function sendSocketMessageToAll(msg, callback) {
    sendSocketMessageToUser(msg, callback, "");
}

function sendSocketMessageToNone(msg, callback) {
    sendSocketMessageToUser(msg, callback, -1);
}

function sendSocketMessageToUser(msg, callback, to_users_id) {
    if (conn.readyState === 1) {
        conn.send(JSON.stringify({msg: msg, webSocketToken: webSocketToken, callback: callback, to_users_id: to_users_id}));
    } else {
        console.log('Socket not ready send message in 1 second');
        setTimeout(function () {
            sendSocketMessageToUser(msg, to_users_id, callback);
        }, 1000);
    }
}

function isSocketActive() {
    return typeof conn != 'undefined' && conn.readyState === 1;
}

function defaultCallback(json) {
    //console.log('defaultCallback', json);
}

var socketAutoUpdateOnHTMLTimout;
function socketAutoUpdateOnHTML(autoUpdateOnHTML) {
    clearTimeout(socketAutoUpdateOnHTMLTimout);
    socketAutoUpdateOnHTMLTimout = setTimeout(function () {
        $('.total_on').text(0);
        $('.total_on').parent().removeClass('text-success');
        //console.log("parseSocketResponse", json.autoUpdateOnHTML);
        for (var prop in autoUpdateOnHTML) {
            if (autoUpdateOnHTML[prop] === false) {
                continue;
            }
            var val = autoUpdateOnHTML[prop];
            $('.' + prop).text(val);
            if (parseInt(val) > 0) {
                $('.' + prop).parent().addClass('text-success');
            }
        }
    }, 500);
}

function parseSocketResponse() {
    json = yptSocketResponse;
    if (typeof json === 'undefined') {
        return false;
    }
    //console.log("parseSocketResponse", json);
    if (json.isAdmin && webSocketServerVersion > json.webSocketServerVersion) {
        if (typeof avideoToastWarning == 'funciton') {
            avideoToastWarning("Please restart your socket server. You are running (v" + json.webSocketServerVersion + ") and your client is expecting (v" + webSocketServerVersion + ")");
        }
    }
    if (json && typeof json.autoUpdateOnHTML !== 'undefined') {
        socketAutoUpdateOnHTML(json.autoUpdateOnHTML);
    }

    if (json && typeof json.msg.autoEvalCodeOnHTML !== 'undefined') {
        //console.log("autoEvalCodeOnHTML", json.msg.autoEvalCodeOnHTML);
        eval(json.msg.autoEvalCodeOnHTML);
    }

    $('#socketUsersURI').empty();
    if (json && typeof json.users_uri !== 'undefined' && $('#socket_info_container').length) {
        for (var prop in json.users_uri) {
            if (json.users_uri[prop] === false) {
                continue;
            }

            for (var prop2 in json.users_uri[prop]) {
                if (json.users_uri[prop][prop2] === false || typeof json.users_uri[prop][prop2] !== 'object') {
                    continue;
                }
                for (var prop3 in json.users_uri[prop][prop2]) {
                    if (json.users_uri[prop][prop2][prop3] === false || typeof json.users_uri[prop][prop2][prop3] !== 'object') {
                        continue;
                    }

                    var socketUserDivID = 'socketUser' + json.users_uri[prop][prop2][prop3].users_id;

                    if (!$('#' + socketUserDivID).length) {
                        var html = '<div class="socketUserDiv" id="' + socketUserDivID + '" >';
                        html += '<div class="socketUserName" onclick="socketUserNameToggle(\'#' + socketUserDivID + '\');">';
                        html += '<i class="fas fa-caret-down"></i><i class="fas fa-caret-up"></i>';
                        if (json.users_uri[prop][prop2].length < 50) {
                            html += '<img src="' + webSiteRootURL + 'user/' + json.users_uri[prop][prop2][prop3].users_id + '/foto.png" class="img img-circle img-responsive">';
                        }
                        html += json.users_uri[prop][prop2][prop3].user_name + '</div>';
                        html += '<div class="socketUserPages"></div></div>';
                        $('#socketUsersURI').append(html);
                    }

                    var text = '';
                    if (json.ResourceID == json.users_uri[prop][prop2][prop3].resourceId) {
                        text += '<stcong>(YOU)</strong>';
                    }
                    text = ' ' + json.users_uri[prop][prop2][prop3].page_title;
                    text += '<br><small>(' + json.users_uri[prop][prop2][prop3].client.browser + ' - ' + json.users_uri[prop][prop2][prop3].client.os + ') ' + json.users_uri[prop][prop2][prop3].ip + '</small>';
                    if (json.users_uri[prop][prop2][prop3].location) {
                        text += '<br><i class="flagstrap-icon flagstrap-' + json.users_uri[prop][prop2][prop3].location.country_code + '" style="margin-right: 10px;"></i>';
                        text += ' ' + json.users_uri[prop][prop2][prop3].location.country_name;
                    }
                    html = '<a href="' + json.users_uri[prop][prop2][prop3].selfURI + '" target="_blank" class="btn btn-xs btn-default btn-block"><i class="far fa-compass"></i> ' + text + '</a>';
                    $('#' + socketUserDivID + ' .socketUserPages').append(html);
                    var isVisible = Cookies.get('#' + socketUserDivID);
                    if (isVisible && isVisible !== 'false') {
                        $('#' + socketUserDivID).addClass('visible')
                    }
                }
            }

        }
    }
}

$(function () {
    //console.log('Getting webSocketToken ...');
    var getWebSocket = webSiteRootURL + 'plugin/YPTSocket/getWebSocket.json.php';
    getWebSocket = addGetParam(getWebSocket, 'webSocketSelfURI', webSocketSelfURI);
    getWebSocket = addGetParam(getWebSocket, 'webSocketVideos_id', webSocketVideos_id);
    getWebSocket = addGetParam(getWebSocket, 'webSocketLiveKey', webSocketLiveKey);
    $.ajax({
        url: getWebSocket,
        success: function (response) {
            if (response.error) {
                console.log('Getting webSocketToken ERROR ' + response.msg);
                if (typeof avideoToastError == 'funciton') {
                    avideoToastError(response.msg);
                }
            } else {
                //console.log('Getting webSocketToken SUCCESS ', response);
                webSocketToken = response.webSocketToken;
                webSocketURL = response.webSocketURL;
                socketConnect();
            }
        }
    });
    if (inIframe()) {
        $('#socket_info_container').hide();
    }
});