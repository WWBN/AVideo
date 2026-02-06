var socketConnectRequested = 0;
var totalDevicesOnline = 0;
var yptSocketResponse;

var socketResourceId;
var socketConnectTimeout;
var users_id_online = undefined;

var socketConnectRetryTimeout = 15000;

var connWS;

// Debug flag for socket logging - set to true to enable verbose logging
var AVIDEO_SOCKET_DEBUG = false;

function socketLog() {
    if (AVIDEO_SOCKET_DEBUG && console && console.log) {
        var args = Array.prototype.slice.call(arguments);
        args.unshift('[YPTSocket]');
        console.log.apply(console, args);
    }
}

function socketWarn() {
    if (console && console.warn) {
        var args = Array.prototype.slice.call(arguments);
        args.unshift('[YPTSocket]');
        console.warn.apply(console, args);
    }
}

function socketError() {
    if (console && console.error) {
        var args = Array.prototype.slice.call(arguments);
        args.unshift('[YPTSocket]');
        console.error.apply(console, args);
    }
}

function processSocketJson(json) {
    if (json && typeof json.autoUpdateOnHTML !== 'undefined') {
        socketAutoUpdateOnHTML(json.autoUpdateOnHTML);
    }
    if (json.type == webSocketTypes.UNDEFINED) {
        socketLog('UNDEFINED message received', json);
        if (typeof json.msg === 'object' && typeof json.msg.callback === 'string') {
            socketLog('Processing subobject from UNDEFINED message', json.msg);
            return processSocketJson(json.msg)
        }
    }
    if (json.type == webSocketTypes.ON_VIDEO_MSG) {
        socketLog('ON_VIDEO_MSG', json.videos_id, 'total:', json.total);
        $('.videoUsersOnline, .videoUsersOnline_' + json.videos_id).text(json.total);
    }
    if (json.type == webSocketTypes.ON_LIVE_MSG && typeof json.is_live !== 'undefined') {
        socketLog('ON_LIVE_MSG', json.live_key ? json.live_key.key : 'unknown', 'is_live:', json.is_live);
        var selector = '#liveViewStatusID_' + json.live_key.key + '_' + json.live_key.live_servers_id;
        if (json.is_live) {
            onlineLabelOnline(selector);
        } else {
            onlineLabelOffline(selector);
        }
    }
    if (json.type == webSocketTypes.NEW_CONNECTION) {
        //console.log("processSocketJson NEW_CONNECTION", json);
        if (typeof onUserSocketConnect === 'function') {
            onUserSocketConnect(json);
        }
    } else if (json.type == webSocketTypes.NEW_DISCONNECTION) {
        //console.log("processSocketJson NEW_DISCONNECTION", json);
        if (typeof onUserSocketDisconnect === 'function') {
            onUserSocketDisconnect(json);
        }
    } else {
        var myfunc;
        var _details = json;
        if (typeof json.msg != 'undefined') {
            _details = json.msg;
        }
        if (typeof _details === 'string') {
            try {
                const parsed = JSON.parse(_details);
                _details = parsed;
                socketLog('Parsed JSON details');
            } catch (e) {
                // If parsing fails, keep the original string
            }
        }

        if (json.callback) {
            // Check if a function exists with the name in json.callback
            var code = "if (typeof " + json.callback + " == 'function') { myfunc = " + json.callback + "; } else { myfunc = defaultCallback; }";
            socketLog('Executing callback:', json.callback);
            eval(code);

            // Trigger the event with the same name as json.callback and pass the JSON object
            const event = new CustomEvent(json.callback, { detail: _details });
            document.dispatchEvent(event);
        } else {
            socketLog('No callback defined, using default');
            myfunc = defaultCallback;
        }

        // Call the function and pass the JSON object
        myfunc(_details);

    }
}

function socketConnect() {
    if (useSocketIO) {
        return socketConnectIO();
    } else {
        return socketConnectOld();
    }
}

function socketConnectOld() {
    if (socketConnectRequested) {
        socketLog('Connection already requested, skipping');
        return false;
    }
    clearTimeout(socketConnectTimeout);

    if (!isOnline()) {
        socketLog('Browser offline, retrying in 1s');
        socketConnectRequested = 0;
        socketConnectTimeout = setTimeout(function () {
            socketConnect();
        }, 1000);
        return false;
    }

    socketConnectRequested = 1;
    var url = addGetParam(webSocketURL, 'page_title', $('<textarea />').html($(document).find("title").text()).text());
    socketLog('Connecting to WebSocket');

    if (!isValidURL(url)) {
        socketConnectRequested = 0;
        socketError('Invalid WebSocket URL');
        socketConnectTimeout = setTimeout(function () {
            socketConnect();
        }, 30000);
        return false;
    }
    try {
        conn = new WebSocket(url);
    } catch (error) {
        socketError('WebSocket creation failed:', error.message);
    }
    setSocketIconStatus('loading');

    connWS.onopen = function (e) {
        socketConnectRequested = 0;
        socketConnectRetryTimeout = 2000; // Reset retry timer
        clearTimeout(socketConnectTimeout);
        socketWarn('WebSocket connection established');
        onSocketOpen();
        return false;
    };

    connWS.onmessage = function (e) {
        try {
            var json = JSON.parse(e.data);
            socketLog('Message received');
            socketResourceId = json.resourceId;
            yptSocketResponse = json;
            parseSocketResponse();

            if (json.type == webSocketTypes.MSG_TO_ALL && Array.isArray(json.msg)) {
                socketLog('Batch message received:', json.msg.length, 'messages');

                if (Array.isArray(json.lastMessageToAllDurationMessages) && json.lastMessageToAllDurationMessages.length > 0) {
                    socketLog('Force disconnect:', json.lastMessageToAllDurationMessages.length, 'users');
                }

                json.msg.forEach(function (element) {
                    processSocketJson(element);
                });
            } else {
                processSocketJson(json);
            }
        } catch (parseError) {
            socketError('Error parsing socket message:', parseError.message);
        }
    };

    connWS.onclose = function (e) {
        socketConnectRequested = 0;

        if (e.code === 1006) {
            socketError('WebSocket closed unexpectedly (code 1006)');

            // Check the WebSocket readyState to understand the closure phase
            switch (connWS.readyState) {
                case WebSocket.CONNECTING:
                    socketError('Connection attempt failed (CONNECTING state)');
                    break;
                case WebSocket.OPEN:
                    socketError('Unexpected close (OPEN state)');
                    break;
                case WebSocket.CLOSING:
                    socketError('Error during close (CLOSING state)');
                    break;
                case WebSocket.CLOSED:
                    socketError('Already closed (CLOSED state)');
                    break;
            }

            socketLog('Retrying in', socketConnectRetryTimeout / 1000, 'seconds');

            // Retry connection with exponential backoff
            socketConnectTimeout = setTimeout(function () {
                socketConnectRetryTimeout = Math.min(socketConnectRetryTimeout * 2, 60000); // Increase timeout up to 1 minute
                socketConnect();
            }, socketConnectRetryTimeout);

            // Optionally, add checks for connection timeouts, SSL issues, or network connectivity
            checkNetworkConnection();
            checkSSLIssues(webSocketURL);
        } else {
            socketLog('Socket closed normally, code:', e.code);
            socketConnectTimeout = setTimeout(function () {
                socketConnect();
            }, socketConnectRetryTimeout);
        }

        onSocketClose();
    };

    function checkNetworkConnection() {
        if (!navigator.onLine) {
            socketError('Browser appears to be offline');
        }
    }

    function checkSSLIssues(url) {
        try {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url.replace('wss://', 'https://'), true);
            xhr.onload = function () {
                if (xhr.status !== 200) {
                    socketError('SSL issue detected, status:', xhr.status);
                }
            };
            xhr.onerror = function () {
                socketError('SSL check failed - possible certificate issue');
            };
            xhr.send();
        } catch (e) {
            socketError('SSL check error:', e.message);
        }
    }


    connWS.onerror = function (err) {
        socketConnectRequested = 0;
        socketError('WebSocket error, readyState:', err.target.readyState);
        connWS.close();
    };
}

function socketConnectIO() {
    if (socketConnectRequested) {
        socketLog('Socket.IO connection already requested');
        return false;
    }
    clearTimeout(socketConnectTimeout);

    if (!isOnline()) {
        socketLog('Browser offline, retrying in 1s');
        socketConnectRequested = false;
        socketConnectTimeout = setTimeout(() => {
            socketConnectIO();
        }, 1000);
        return false;
    }

    socketConnectRequested = true;

    const url = addGetParam(webSocketURL, "page_title", encodeURIComponent(document.title));
    socketLog('Connecting to Socket.IO');

    if (!isValidURL(url)) {
        socketConnectRequested = false;
        socketError('Invalid Socket.IO URL');
        socketConnectTimeout = setTimeout(() => {
            socketConnectIO();
        }, 30000);
        return false;
    }

    try {
        socket = io(url, {
            transports: ["websocket"],
            timeout: 10000, // 5 seconds timeout
            pingTimeout: 60000,
            pingInterval: 25000
        });
    } catch (error) {
        socketError('Socket.IO initialization failed:', error.message);
    }

    setSocketIconStatus("loading");

    socket.on("connect", () => {
        socketConnectRequested = false;
        socketConnectRetryTimeout = 2000; // Reset retry timer
        clearTimeout(socketConnectTimeout);
        socketWarn('Socket.IO connection established');
        onSocketOpen();
    });

    socket.on("message", (data) => {
        if (data.type == webSocketTypes.MSG_BATCH && data.messages.length > 0) {
            socketResourceId = data.resourceId;
            yptSocketResponse = data;
            parseSocketResponse();

            if (data.autoUpdateOnHTML) {
                socketAutoUpdateOnHTML(data.autoUpdateOnHTML);
            }
            // console.log("📩 Socket.IO message received MSG_BATCH:", data);
            data.messages.forEach(function (message, index) {
                processSocketJson(message);
                if(message.users_id){
                    setUserOnlineStatus(message.users_id);
                }
            });
        } else {
            socketLog('Message received');
            processSocketJson(data);
        }
    });

    socket.on("broadcast", (data) => {
        socketLog('Broadcast received');
        processSocketJson(data);
    });

    socket.on("disconnect", (reason) => {
        socketError('Disconnected:', reason);

        if (reason === "io server disconnect") {
            socketWarn('Server initiated disconnect, reconnecting...');
            socket.connect();
        } else if (reason === "transport close") {
            socketError('Transport closed, retrying...');
        }

        onSocketClose();
    });

    socket.on("connect_error", (err) => {
        socketError('Connection error:', err.message || err);
    });

    socket.on("connect_timeout", () => {
        socketError('Connection timeout, retrying...');
    });

}

function onSocketOpen() {
    setSocketIconStatus('connected');
}

function onSocketClose() {
    setSocketIconStatus('disconnected');
}

function setSocketIconStatus(status) {
    var selector = '.socket_info';
    if (status == 'connected') {
        $(selector).removeClass('socket_loading');
        $(selector).removeClass('socket_disconnected');
        $(selector).addClass('socket_connected');
    } else if (status == 'disconnected') {
        $(selector).removeClass('socket_loading');
        $(selector).addClass('socket_disconnected');
        $(selector).removeClass('socket_connected');
    } else {
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

function sendSocketMessage(payload) {
    if (useSocketIO) {
        if (typeof socket !== 'undefined' && socket.connected) {
            socket.emit('message', payload);
        } else {
            setTimeout(() => sendSocketMessage(payload), 1000);
        }
    } else {
        if (connWS && connWS.readyState === 1) {
            connWS.send(JSON.stringify(payload));
        } else {
            setTimeout(() => sendSocketMessage(payload), 1000);
        }
    }
}

function sendSocketMessageToUser(msg, callback, to_users_id) {
    sendSocketMessage({ msg, webSocketToken, callback, to_users_id });
}

function sendSocketMessageToResourceId(msg, callback, resourceId) {
    sendSocketMessage({ msg, webSocketToken, callback, resourceId });
}

function isSocketActive() {
    return isOnline() && ((typeof conn != 'undefined' && connWS.readyState === 1) || (typeof socket != 'undefined' && socket.connected));
}

function defaultCallback(json) {
    ////console.log('defaultCallback', json);
}

var socketAutoUpdateOnHTMLTimout;
var globalAutoUpdateOnHTML = [];
function socketAutoUpdateOnHTML(autoUpdateOnHTML) {
    for (var prop in autoUpdateOnHTML) {
        if (autoUpdateOnHTML[prop] === false) {
            continue;
        }
        if (typeof autoUpdateOnHTML[prop] !== 'string' && typeof autoUpdateOnHTML[prop] !== 'number') {
            continue;
        }
        //console.log('socketAutoUpdateOnHTML 1', prop, globalAutoUpdateOnHTML[prop], autoUpdateOnHTML[prop]);
        globalAutoUpdateOnHTML[prop] = autoUpdateOnHTML[prop];
    }

    //console.log('socketAutoUpdateOnHTML 2', autoUpdateOnHTML);
    //console.log('socketAutoUpdateOnHTML 3', globalAutoUpdateOnHTML);
}


async function AutoUpdateOnHTMLTimer() {
    var localAutoUpdateOnHTML = [];
    clearTimeout(socketAutoUpdateOnHTMLTimout);
    //console.log('socket AutoUpdateOnHTMLTimer 1', empty(globalAutoUpdateOnHTML), globalAutoUpdateOnHTML);
    if (!empty(globalAutoUpdateOnHTML)) {
        $('.total_on').text(0);
        $('.total_on').parent().removeClass('text-success');
        //console.log("socket AutoUpdateOnHTMLTimer 2", $('.total_on'), globalAutoUpdateOnHTML);

        localAutoUpdateOnHTML = globalAutoUpdateOnHTML;
        globalAutoUpdateOnHTML = [];
        //console.log('socket AutoUpdateOnHTMLTimer localAutoUpdateOnHTML 1', globalAutoUpdateOnHTML, localAutoUpdateOnHTML);
        for (var prop in localAutoUpdateOnHTML) {
            if (localAutoUpdateOnHTML[prop] === false) {
                continue;
            }
            var val = localAutoUpdateOnHTML[prop];
            if (typeof val == 'string' || typeof val == 'number') {
                //console.log('socket AutoUpdateOnHTMLTimer 3', prop, val, $('.' + prop).text());
                $('.' + prop).text(val);
                //console.log('socket AutoUpdateOnHTMLTimer 4', prop, val, $('.' + prop).text());
                if (parseInt(val) > 0) {
                    //$('.' + prop).parent().addClass('text-success');
                }
            }
        }
    } else {
        globalAutoUpdateOnHTML = [];
    }
    localAutoUpdateOnHTML = [];

    socketAutoUpdateOnHTMLTimout = setTimeout(function () {
        AutoUpdateOnHTMLTimer();
    }, 2000);
}

var canShowSocketToast = true;
async function parseSocketResponse() {
    const json = yptSocketResponse;
    yptSocketResponse = false;

    if (!json) return false;

    if (json.isAdmin && webSocketServerVersion > json.webSocketServerVersion) {
        if (canShowSocketToast && typeof avideoToastWarning === 'function') {
            avideoToastWarning(`Please restart your socket server. You are running (v${json.webSocketServerVersion}) and your client is expecting (v${webSocketServerVersion})`);
            canShowSocketToast = false;
            setTimeout(() => { canShowSocketToast = true; }, 300000);
        }
    }

    if (typeof json.users_id_online !== 'undefined') {
        users_id_online = json.users_id_online;
    }

    if (typeof json.autoUpdateOnHTML !== 'undefined') {
        socketAutoUpdateOnHTML(json.autoUpdateOnHTML);
    }

    if (json.msg?.autoEvalCodeOnHTML !== undefined) {
        eval(json.msg.autoEvalCodeOnHTML);
    }

    const ignoreURI = ['latestOrLive.php', 'plugin/Chat2'];
    const validAnchorHrefs = new Set();

    if (json && $('#socket_info_container').length) {
        if (typeof json.users_uri !== 'undefined') {
            for (const group in json.users_uri) {
                const groupData = json.users_uri[group];
                if (!groupData) continue;

                for (const subGroup in groupData) {
                    const subGroupData = groupData[subGroup];
                    if (!subGroupData || typeof subGroupData !== 'object') continue;

                    for (const index in subGroupData) {
                        const userData = subGroupData[index];
                        if (!userData || typeof userData !== 'object') continue;

                        const selfURI = userData.selfURI;
                        const resourceId = userData.resourceId;
                        if (!selfURI || !resourceId || ignoreURI.some(uri => selfURI.includes(uri))) continue;
                        //console.log('updateSocketUserCard', userData, json);
                        updateSocketUserCard(userData, json.ResourceID, validAnchorHrefs, 'a1');
                    }
                }
            }
        }

        if (typeof json.users_id_online !== 'undefined') {
            for (const key in json.users_id_online) {
                if (!Object.hasOwnProperty.call(json.users_id_online, key)) continue;

                const element = json.users_id_online[key];
                const selfURI = element.selfURI;
                const resourceId = element.resourceId;
                if (!selfURI || !resourceId || ignoreURI.some(uri => selfURI.includes(uri))) continue;

                updateSocketUserCard(element, json.ResourceID, validAnchorHrefs, 'a2');
            }
        }

        // 🔴 Remover <a> que não estão mais na resposta do socket
        $('.socketUserPages a').each(function () {
            const resourceId = $(this).data('resource-id');
            const selfURI = $(this).attr('href');
            if (!validAnchorHrefs.has(`${resourceId}-${selfURI}`)) {
                $(this).remove();
            }
        });

        $('#socketUsersURI .socketUserDiv').each(function () {
            // Check if .socketUserPages is empty, remove if true
            if ($(this).find(`.socketUserPages`).text().trim() === '') {
                $(this).remove();
            }
        });


        $('#socketUsersURI').tooltip({ html: true });
    }
}

async function updateSocketUserCard(userData, currentResourceID, validAnchorHrefs, className) {
    const selfURI = userData.selfURI;
    const resourceId = userData.resourceId;
    const socketUserDivID = 'socketUser' + userData.users_id;

    if (!$(`#${socketUserDivID}`).length) {
        const userName = userData.user_name || userData.identification || 'Unknown';
        const html = `
            <div class="socketUserDiv" id="${socketUserDivID}">
                <div class="socketUserName" onclick="socketUserNameToggle('#${socketUserDivID}');">
                    <i class="fas fa-caret-down"></i><i class="fas fa-caret-up"></i>
                    ${userName}
                </div>
                <div class="socketUserPages"></div>
            </div>`;
        $('#socketUsersURI').append(html);
    }

    let textParts = [];

    if (currentResourceID == userData.resourceId) {
        textParts.push('<strong>(YOU)</strong>');
    }

    if (userData.page_title) {
        textParts.push(userData.page_title);
    }

    const client = userData.client;
    var tooltip = '';
    if (client?.browser && client?.os && userData.ip) {
        tooltip = `(${client.browser} - ${client.os}) ${userData.ip}`;
    }

    const location = userData.location;
    if (location?.country_code && location.country_code !== '-' && location.country_name) {
        textParts.push(`<br><i class="flagstrap-icon flagstrap-${location.country_code}" style="margin-right: 10px;"></i> ${location.country_name}`);
    }

    const finalText = textParts.join(' ');
    const linkSelector = `.socketUserPages a[data-resource-id="${resourceId}"][href="${selfURI}"]`;

    // Atualiza ou adiciona o botão
    if (!$(linkSelector).length) {
        const html = `
            <a href="${selfURI}" target="_blank"
            class="${className} btn btn-primary btn-sm btn-block mb-1"
            data-resource-id="${resourceId}"
            data-toggle="tooltip"
            title="${tooltip}"
            >
                <i class="far fa-compass"></i> ${finalText}
            </a>`;
        $(`#${socketUserDivID} .socketUserPages`).append(html);
    }

    validAnchorHrefs.add(`${resourceId}-${selfURI}`);

    // Gerencia visibilidade
    const isVisible = Cookies.get(`#${socketUserDivID}`);
    if (isVisible && isVisible !== 'false') {
        $(`#${socketUserDivID}`).addClass('visible');
    }

}

function socketNewConnection(json) {
    if (json?.msg?.users_id) {
        setUserOnlineStatus(json.msg.users_id);
    }
}

function socketDisconnection(json) {
    if (json?.msg?.users_id) {
        setUserOnlineStatus(json.msg.users_id);
    }
}

function setInitialOnlineStatus() {
    if (!isReadyToCheckIfIsOnline()) {
        setTimeout(function () {
            setInitialOnlineStatus();
        }, 1000);
        return false;
    }

    for (var users_id in users_id_online) {
        setUserOnlineStatus(users_id);
    }
    return true;
}

function setUserOnlineStatus(users_id) {
    if (isUserOnline(users_id)) {
        $('.users_id_' + users_id).removeClass('offline');
        $('.users_id_' + users_id).addClass('online');
    } else {
        $('.users_id_' + users_id).removeClass('online');
        $('.users_id_' + users_id).addClass('offline');
    }
}
var getWebSocket;
$(async function () {
    await startSocket();
    AutoUpdateOnHTMLTimer();
});
var _startSocketTimeout;
async function startSocket() {
    if(typeof webSocketURL === 'undefined') {
        console.warn('startSocket: webSocketURL is empty or undefined');
        return false;
    }
    console.log('startSocket');
    clearTimeout(_startSocketTimeout);
    if (!isOnline() || typeof webSiteRootURL == 'undefined') {
        //console.log('startSocket: Not Online');
        _startSocketTimeout = setTimeout(async function () {
            await startSocket();
        }, 10000);
        return false;
    }
    ////console.log('Getting webSocketToken ...');
    getWebSocket = webSiteRootURL + 'plugin/YPTSocket/getWebSocket.json.php';
    getWebSocket = addGetParam(getWebSocket, 'webSocketSelfURI', webSocketSelfURI);
    getWebSocket = addGetParam(getWebSocket, 'webSocketVideos_id', webSocketVideos_id);
    getWebSocket = addGetParam(getWebSocket, 'webSocketLiveKey', webSocketLiveKey);
    $.ajax({
        url: getWebSocket,
        success: function (response) {
            if (response.error) {
                //console.log('Getting webSocketToken ERROR ' + response.msg);
                if (typeof avideoToastError == 'function') {
                    avideoToastError(response.msg);
                }
            } else {
                ////console.log('Getting webSocketToken SUCCESS ', response);
                webSocketToken = response.webSocketToken;
                webSocketURL = response.webSocketURL;
                socketConnect();
            }
        }
    });
    if (inIframe()) {
        $('#socket_info_container').hide();
    }
    setInitialOnlineStatus();
}
