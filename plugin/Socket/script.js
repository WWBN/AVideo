var socketConnectRequested = 0;
var totalDevicesOnline = 0;
function socketConnect() {
    if (socketConnectRequested) {
        return false;
    }
    socketConnectRequested = 1;
    console.log('Trying to reconnect on socket...');
    conn = new WebSocket(webSocketURL);
    conn.onopen = function (e) {
        console.log("Socket onopen");
        return false;
    };
    conn.onmessage = function (e) {
        var json = JSON.parse(e.data);
        parseSocketResponse(json);
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
        } else if (json.type == webSocketTypes.NEW_DISCONNECTION) {
            //console.log("Socket onmessage NEW_DISCONNECTION", json);
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
        console.log('Socket is closed. Reconnect will be attempted in 1 second.', e.reason);
        setTimeout(function () {
            socketConnect();
        }, 1000);
    };

    conn.onerror = function (err) {
        socketConnectRequested = 0;
        console.error('Socket encountered error: ', err.message, 'Closing socket');
        conn.close();
    };
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

function parseSocketResponse(json) {
    console.log("parseSocketResponse", json);
    if (json && typeof json.autoUpdateOnHTML !== 'undefined') {
        //console.log("parseSocketResponse", json.autoUpdateOnHTML);
        for (var prop in json.autoUpdateOnHTML) {
            if(json.autoUpdateOnHTML[prop]===false){
                continue;
            }
            $('.'+prop).text(json.autoUpdateOnHTML[prop]);
        }
    }
    
    if (json && typeof json.autoEvalCode !== 'undefined') {
        //console.log("parseSocketResponse", json.autoUpdateOnHTML);
        for (var prop in json.autoUpdateOnHTML) {
            if(json.autoEvalCode[prop]===false){
                continue;
            }
            console.log("autoEvalCode", json.autoEvalCode[prop]);
            eval(json.autoEvalCode[prop]);
        }
    }
}

$(function () {
    socketConnect();
});