var socketMyResourceId = 0;
var socketConnectRequested = 0;
function socketConnect() {
    if(socketConnectRequested){
        return false;
    }
    socketConnectRequested = 1;
    console.log('Trying to reconnect on ' + webSocketURL);
    conn = new WebSocket(webSocketURL);
    conn.onopen = function (e) {
        console.log("Socket onopen", e);
        socketMyResourceId = 0;
        sendSocketMessageToNone("webSocketToken", "");
        return false;
    };
    conn.onmessage = function (e) {
        //console.log("Socket onmessage", e);
        var json = JSON.parse(e.data);
        console.log("Socket onmessage", json);
        if (!socketMyResourceId) {
            socketMyResourceId = parseInt(json.ResourceId);
            var msg = "Socket socketMyResourceId " + socketMyResourceId;
            //sendSocketMessage(msg, "", "");
            console.log(msg);
        } else {
            var myfunc;
            if (json.callback) {
                var code = "if(typeof " + json.callback + " == 'function'){myfunc = " + json.callback + ";}else{myfunc = defaultCallback;}";
                //console.log(code);
                eval(code);
            } else {
                console.log("onmessage: callback not found");
                myfunc = defaultCallback;
            }
            myfunc(json);
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

function sendSocketMessageToUser(msg, callback, users_id) {
    if (conn.readyState === 1) {
        conn.send(JSON.stringify({msg: msg, webSocketToken: webSocketToken, callback: callback, users_id, users_id}));
    } else {
        console.log('Socket not ready send message in 1 second');
        setTimeout(function () {
            sendSocketMessageToUser(msg, callback, to_users_id);
        }, 1000);
    }
}

function sendSocketMessageToUser(msg, users_id, callback) {
    if (conn.readyState === 1) {
        conn.send(JSON.stringify({msg: msg, webSocketToken: webSocketToken, users_id: users_id, callback: callback}));
    } else {
        console.log('Socket not ready send message in 1 second');
        setTimeout(function () {
            sendSocketMessageToUser(msg, users_id, callback);
        }, 1000);
    }
}

function isSocketActive(){
    return typeof conn != 'undefined' && conn.readyState === 1;
}

function defaultCallback(json) {
    //console.log('defaultCallback', json);
}

$(function () {
    socketConnect();
});