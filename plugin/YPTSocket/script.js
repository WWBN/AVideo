var socketConnectRequested = 0;
var totalDevicesOnline = 0;
var yptSocketResponse;

var socketResourceId;
var socketConnectTimeout;
var users_id_online = undefined;

var socketConnectRetryTimeout = 15000;

function processSocketJson(json) {
    if (json && typeof json.autoUpdateOnHTML !== 'undefined') {
        socketAutoUpdateOnHTML(json.autoUpdateOnHTML);
    }
    if (json.type == webSocketTypes.UNDEFINED) {
        console.log("processSocketJson UNDEFINED", json);
        if (typeof json.msg === 'object' && typeof json.msg.callback === 'string') {
            console.log("Socket onmessage UNDEFINED process subobject", json.msg);
            return processSocketJson(json.msg)
        }
    }
    if (json.type == webSocketTypes.ON_VIDEO_MSG) {
        console.log("processSocketJson ON_VIDEO_MSG", json);
        $('.videoUsersOnline, .videoUsersOnline_' + json.videos_id).text(json.total);
    }
    if (json.type == webSocketTypes.ON_LIVE_MSG && typeof json.is_live !== 'undefined') {
        console.log("processSocketJson ON_LIVE_MSG", json);
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
                // If parsing succeeds, use the parsed object
                _details = parsed;
                console.log('processSocketJson: Parsed JSON:', _details);
            } catch (e) {
                // If parsing fails, keep the original string
                console.log('processSocketJson: Not a JSON string, keeping as is:', _details);
            }
        }

        if (json.callback) {
            // Check if a function exists with the name in json.callback
            var code = "if (typeof " + json.callback + " == 'function') { myfunc = " + json.callback + "; } else { myfunc = defaultCallback; }";
            console.log('processSocketJson: code=' + code, _details);
            eval(code);

            // Trigger the event with the same name as json.callback and pass the JSON object
            const event = new CustomEvent(json.callback, { detail: _details }); // Pass the JSON as `detail`
            document.dispatchEvent(event);
        } else {
            console.log("processSocketJson: callback not found", json);
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
        console.log('socketConnect: already requested');
        return false;
    }
    clearTimeout(socketConnectTimeout);

    if (!isOnline()) {
        console.log('socketConnect: Not Online');
        socketConnectRequested = 0;
        socketConnectTimeout = setTimeout(function () {
            socketConnect();
        }, 1000);
        return false;
    }

    socketConnectRequested = 1;
    var url = addGetParam(webSocketURL, 'page_title', $('<textarea />').html($(document).find("title").text()).text());
    console.log('socketConnect: Trying to reconnect with URL:', url);

    if (!isValidURL(url)) {
        socketConnectRequested = 0;
        console.log("socketConnect: Invalid URL:", url);
        socketConnectTimeout = setTimeout(function () {
            socketConnect();
        }, 30000);
        return false;
    }
    try {
        conn = new WebSocket(url);
    } catch (error) {
        console.error('socketConnect', error);
    }
    setSocketIconStatus('loading');

    console.trace();
    conn.onopen = function (e) {
        socketConnectRequested = 0;
        socketConnectRetryTimeout = 2000; // Reset retry timer
        clearTimeout(socketConnectTimeout);
        console.warn("socketConnect: Socket connection established. onopen event triggered.");
        onSocketOpen();
        return false;
    };

    conn.onmessage = function (e) {
        try {
            var json = JSON.parse(e.data);
            console.log("Socket onmessage received:", json);
            socketResourceId = json.resourceId;
            yptSocketResponse = json;
            parseSocketResponse();

            if (json.type == webSocketTypes.MSG_TO_ALL && Array.isArray(json.msg)) {
                console.log("Socket onmessage contains", json.msg.length, "messages. lastMessageToAllDuration=" + (json.lastMessageToAllDuration).toFixed(4) + " average time=" + (json.lastMessageToAllDuration / json.users_id_online.length).toFixed(4) + " seconds");

                if (Array.isArray(json.lastMessageToAllDurationMessages) && json.lastMessageToAllDurationMessages.length > 0) {
                    console.log("Socket force to disconnect ", json.lastMessageToAllDurationMessages.length, "users.");
                }

                json.msg.forEach(function (element) {
                    processSocketJson(element);
                });
            } else {
                processSocketJson(json);
            }
        } catch (parseError) {
            console.error("Error parsing socket message:", e.data, parseError);
        }
    };

    conn.onclose = function (e) {
        socketConnectRequested = 0;

        if (e.code === 1006) {
            console.error('WebSocket closed unexpectedly with code 1006. Investigating possible causes...');

            // Check the WebSocket readyState to understand the closure phase
            switch (conn.readyState) {
                case WebSocket.CONNECTING:
                    console.error('WebSocket was in CONNECTING state. The connection attempt failed.');
                    break;
                case WebSocket.OPEN:
                    console.error('WebSocket was in OPEN state. Connection was unexpectedly closed.');
                    break;
                case WebSocket.CLOSING:
                    console.error('WebSocket was in CLOSING state. It might have been closed due to an error.');
                    break;
                case WebSocket.CLOSED:
                    console.error('WebSocket was already in CLOSED state.');
                    break;
            }

            console.error('Retrying connection in ' + socketConnectRetryTimeout / 1000 + ' seconds.');

            // Retry connection with exponential backoff
            socketConnectTimeout = setTimeout(function () {
                socketConnectRetryTimeout = Math.min(socketConnectRetryTimeout * 2, 60000); // Increase timeout up to 1 minute
                socketConnect();
            }, socketConnectRetryTimeout);

            // Optionally, add checks for connection timeouts, SSL issues, or network connectivity
            checkNetworkConnection();
            checkSSLIssues(webSocketURL);
        } else {
            console.log('Socket closed normally with code: ' + e.code + '. Reason: ' + (e.reason || 'No reason provided.'));
            socketConnectTimeout = setTimeout(function () {
                socketConnect();
            }, socketConnectRetryTimeout);
        }

        onSocketClose();
    };

    function checkNetworkConnection() {
        if (!navigator.onLine) {
            console.error('It seems you are offline. Check your internet connection.');
        } else {
            console.log('Network appears to be online.');
        }
    }

    function checkSSLIssues(url) {
        try {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url.replace('wss://', 'https://'), true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log('SSL seems to be working fine for URL:', url);
                    // No SSL issue, recommend clearing cookies if problem persists
                    console.log('If you continue to experience issues, try clearing your browser cookies.');
                } else {
                    console.error('SSL issue detected: HTTP status', xhr.status, 'for URL:', url);
                    console.log('The problem might be related to SSL configuration. Please verify your SSL certificates.');
                }
            };
            xhr.onerror = function () {
                console.error('Failed to check SSL. There might be a problem with the certificate.');
                console.log('The issue seems to be related to SSL. Please verify your SSL configuration.');
            };
            xhr.send();
        } catch (e) {
            console.error('Error while checking SSL issues:', e.message);
            console.log('It could be an SSL-related issue. Please verify the SSL configuration.');
        }
    }


    conn.onerror = function (err) {
        socketConnectRequested = 0;
        console.error('Socket encountered error: ', err, 'URL:', url);
        if (err.target.readyState === WebSocket.CLOSED) {
            console.error('WebSocket is in CLOSED state. Likely a network or server issue.');
        } else if (err.target.readyState === WebSocket.CLOSING) {
            console.error('WebSocket is in CLOSING state. Could be a graceful shutdown or an error in closure.');
        } else if (err.target.readyState === WebSocket.CONNECTING) {
            console.error('WebSocket is in CONNECTING state. Check server status or network issues.');
        }
        conn.close();
    };
}

function socketConnectIO() {
    if (socketConnectRequested) {
        console.log("socketConnectIO: already requested");
        return false;
    }
    clearTimeout(socketConnectTimeout);

    if (!isOnline()) {
        console.log("socketConnectIO: Not Online");
        socketConnectRequested = false;
        socketConnectTimeout = setTimeout(() => {
            socketConnectIO();
        }, 1000);
        return false;
    }

    socketConnectRequested = true;

    const url = addGetParam(webSocketURL, "page_title", encodeURIComponent(document.title));
    console.log("socketConnectIO: Trying to connect to URL:", url);

    if (!isValidURL(url)) {
        socketConnectRequested = false;
        console.error("socketConnectIO: Invalid URL:", url);
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
        console.error("socketConnectIO Error:", error);
    }

    setSocketIconStatus("loading");

    socket.on("connect", () => {
        socketConnectRequested = false;
        socketConnectRetryTimeout = 2000; // Reset retry timer
        clearTimeout(socketConnectTimeout);
        console.warn("socketConnectIO: Socket.IO connection established.");
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
            console.log("📩 Socket.IO message received MSG_BATCH:", data);
            data.messages.forEach(function (message, index) {
                processSocketJson(message);
                if(message.users_id){
                    setUserOnlineStatus(message.users_id);
                }
            });
        } else {
            console.log("📩 Socket.IO message received:", data);
            processSocketJson(data);
        }
    });

    socket.on("broadcast", (data) => {
        console.log("📩 Received broadcast:", data);
        processSocketJson(data);
    });

    socket.on("disconnect", (reason) => {
        console.error("❌ Socket.IO disconnected. Reason:", reason);

        if (reason === "io server disconnect") {
            console.warn("Server disconnected the socket, attempting to reconnect...");
            socket.connect();
        } else if (reason === "transport close") {
            console.error("Transport closed. Retrying...");
        } else {
            console.error("Unknown disconnection reason. Retrying...");
        }

        onSocketClose();
    });

    socket.on("connect_error", (err) => {
        console.error("⚠️ Socket.IO connection error:", err);
    });

    socket.on("connect_timeout", () => {
        console.error("⏳ Connection timeout. Retrying...");
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

function sendSocketMessageToUser(msg, callback, to_users_id) {
    if (conn.readyState === 1) {
        conn.send(JSON.stringify({ msg: msg, webSocketToken: webSocketToken, callback: callback, to_users_id: to_users_id }));
    } else {
        //console.log('Socket not ready send message in 1 second');
        setTimeout(function () {
            sendSocketMessageToUser(msg, to_users_id, callback);
        }, 1000);
    }
}
function sendSocketMessageToResourceId(msg, callback, resourceId) {
    if (conn.readyState === 1) {
        conn.send(JSON.stringify({ msg: msg, webSocketToken: webSocketToken, callback: callback, resourceId: resourceId }));
    } else {
        //console.log('Socket not ready send message in 1 second');
        setTimeout(function () {
            sendSocketMessageToUser(msg, to_users_id, callback);
        }, 1000);
    }
}

function isSocketActive() {
    return isOnline() && ((typeof conn != 'undefined' && conn.readyState === 1) || (typeof socket != 'undefined' && socket.connected));
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
    setUserOnlineStatus(json.msg.users_id);
}

function socketDisconnection(json) {
    setUserOnlineStatus(json.msg.users_id);
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
