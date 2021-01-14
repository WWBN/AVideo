var socketMyResourceId = 0;
function socketConnect() {
    console.log('Trying to reconnect on ' + webSocketURL);
    conn = new WebSocket(webSocketURL);
    conn.onopen = function (e) {
        console.log("Socket onopen", e);
        socketMyResourceId = 0;
        sendSocketMessage("webSocketToken", "");
        return false;
    };
    conn.onmessage = function (e) {
        console.log("Socket onmessage", e);
        if (!socketMyResourceId) {
            socketMyResourceId = parseInt(e.data);
            var msg = "Socket socketMyResourceId " + socketMyResourceId;
            sendSocketMessage(msg, "");
            console.log(msg);
        } else {
            var json = JSON.parse(e.data);
            var myfunc;
            if (json.callback) {
                var code = "if(typeof " + json.callback + " == 'function'){myfunc = " + json.callback + ";}else{myfunc = defaultCallback;}";
                console.log(code);
                eval(code);
            } else {
                myfunc = defaultCallback;
            }
            myfunc(json);
        }

    };
    conn.onclose = function (e) {
        console.log('Socket is closed. Reconnect will be attempted in 1 second.', e.reason);
        setTimeout(function () {
            socketConnect();
        }, 1000);
    };

    conn.onerror = function (err) {
        console.error('Socket encountered error: ', err.message, 'Closing socket');
        conn.close();
    };
}

function sendSocketMessage(msg, callback) {
    if (conn.readyState === 1) {
        conn.send(JSON.stringify({msg: msg, webSocketToken: webSocketToken, callback: callback}));
    } else {
        console.log('Socket not ready send message in 1 second');
        setTimeout(function () {
            sendSocketMessage(msg, callback);
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
    console.log('defaultCallback', json);
}

$(function () {
    socketConnect();
    sendSocketMessage("test", "");
});