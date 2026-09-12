var socketConnectRequested = 0;
var totalDevicesOnline = 0;
var yptSocketResponse;
var tokenRefreshTimeout;

var socketResourceId;
var socketConnectTimeout;
var users_id_online = undefined;

var socketConnectRetryTimeout = 2000;

var connWS;
var socket;
var socketReady = false;
var socketReadyTimeout;
var socketTokenFetching = false;
var socketTokenCallbacks = [];
var socketSendQueue = [];
var socketSendTimer;
var socketSendQueueLimit = 1000;
var socketSendLifetime = 30000;

// Debug flag for socket logging - set to true to enable verbose logging
var AVIDEO_SOCKET_DEBUG = false;

function socketLog() {
    if (AVIDEO_SOCKET_DEBUG && console && console.log) {
        var args = Array.prototype.slice.call(arguments);
        args.unshift('[YPTSocket]');
        console.log.apply(console, args);
    }
}

// client.browser/os come from get_browser_name()/getOS() (PHP), whose fallback embeds the raw User-Agent header, so they must be escaped before reaching innerHTML
function socketEscapeHtml(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function socketWarn() {
    if (console && console.log) {
        var args = Array.prototype.slice.call(arguments);
        args.unshift('[YPTSocket]');
        console.log.apply(console, args);
    }
}

function socketError() {
    if (console && console.error) {
        var args = Array.prototype.slice.call(arguments);
        args.unshift('[YPTSocket]');
        console.error.apply(console, args);
    }
}

// Allowlist of socket callback names a peer is permitted to trigger. A denylist of
// native functions is not enough - any function AVideo itself defines on window (e.g.
// one that touches innerHTML/eval) would still be a usable gadget. Plugins that need to
// react to a socket callback must opt in here instead of relying on window[name] lookup.
var socketCallbackAllowlist = {};

function registerSocketCallback(name, fn, sanitizeDetails) {
    if (typeof name === 'string' && typeof fn === 'function') {
        socketCallbackAllowlist[name] = {
            callback: fn,
            sanitizeDetails: typeof sanitizeDetails === 'function' ? sanitizeDetails : null
        };
    }
}

// Shared by plugin/WebRTC/call/caller.js and plugin/YPTSocket/caller.js, which only differ
// in the link-opening callback (openWebRTCLink vs openMeetLink) - avoids repeating the rest.
function registerCallSocketCallbacks(linkCallbackName, linkCallbackFn) {
    registerSocketCallback('incomeCall', incomeCall, sanitizeSocketCallDetails);
    registerSocketCallback('hangUpCall', hangUpCall, sanitizeSocketCallDetails);
    registerSocketCallback('hideCall', hideCall, sanitizeSocketCallDetails);
    registerSocketCallback('callAccepted', callAccepted, sanitizeSocketCallDetails);
    registerSocketCallback(linkCallbackName, linkCallbackFn, sanitizeSocketCallbackURL);
    registerSocketCallback('callModalIFrameClosed', callModalIFrameClosed);
    registerSocketCallback('hideCallPleaseWait', hideCallPleaseWait);
}

function cloneSocketCallbackDetails(value, depth) {
    depth = depth || 0;
    if (depth > 10) {
        return null;
    }
    if (value === null || typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
        return value;
    }
    if (Array.isArray(value)) {
        return value.map(function (item) {
            return cloneSocketCallbackDetails(item, depth + 1);
        });
    }
    if (typeof value !== 'object') {
        return null;
    }

    var clone = {};
    Object.keys(value).forEach(function (key) {
        var normalizedKey = key.toLowerCase();
        if (
            normalizedKey === '__proto__' ||
            normalizedKey === 'prototype' ||
            normalizedKey === 'constructor' ||
            normalizedKey === 'eval' ||
            normalizedKey === 'actions' ||
            normalizedKey === 'autoevalcodeonhtml' ||
            normalizedKey === 'callback'
        ) {
            return;
        }
        clone[key] = cloneSocketCallbackDetails(value[key], depth + 1);
    });
    return clone;
}

function escapeSocketCallbackHTML(value) {
    var element = document.createElement('div');
    element.textContent = value === null || typeof value === 'undefined' ? '' : String(value);
    return element.innerHTML;
}

function sanitizeSocketCallbackObject(details) {
    return cloneSocketCallbackDetails(details);
}

function sanitizeSocketCallbackText(details) {
    return escapeSocketCallbackHTML(details);
}

function sanitizeSocketCallbackStrings(details) {
    var sanitized = cloneSocketCallbackDetails(details);
    if (!sanitized || typeof sanitized !== 'object') {
        return {};
    }
    Object.keys(sanitized).forEach(function (key) {
        if (typeof sanitized[key] === 'string') {
            sanitized[key] = escapeSocketCallbackHTML(sanitized[key]);
        }
    });
    return sanitized;
}

function sanitizeSocketUserNotification(details) {
    var sanitized = sanitizeSocketCallbackStrings(details);
    // These fields are inserted directly into the notification template as markup/attributes.
    sanitized.onclick = '';
    sanitized.html = '';
    sanitized.id = parseInt(sanitized.id) || 0;
    sanitized.priority = parseInt(sanitized.priority) || 0;
    sanitized.toast = Boolean(sanitized.toast);
    return sanitized;
}

function sanitizeSocketCallDetails(details) {
    var sanitized = cloneSocketCallbackDetails(details);
    if (!sanitized || typeof sanitized !== 'object') {
        return {};
    }
    ['from_users_id', 'to_users_id', 'users_id'].forEach(function (key) {
        if (Object.prototype.hasOwnProperty.call(sanitized, key)) {
            sanitized[key] = parseInt(sanitized[key]) || 0;
        }
    });
    sanitized.playCallBusySound = sanitized.playCallBusySound ? 1 : 0;
    return sanitized;
}

function sanitizeSocketCallbackURL(details) {
    try {
        var url = new URL(String(details || ''), window.location.href);
        if (url.protocol === 'http:' || url.protocol === 'https:') {
            return url.href;
        }
    } catch (error) {
        // Return an empty URL below.
    }
    return '';
}

function sanitizeSocketRedirect(details) {
    var sanitized = cloneSocketCallbackDetails(details);
    if (!sanitized || typeof sanitized !== 'object' || !sanitized.redirectLive) {
        return {};
    }

    var redirect = sanitized.redirectLive;
    try {
        var url = new URL(String(redirect.viewerUrl || ''), window.location.href);
        if (url.protocol !== 'http:' && url.protocol !== 'https:') {
            redirect.viewerUrl = '';
        } else {
            redirect.viewerUrl = url.href;
        }
    } catch (error) {
        redirect.viewerUrl = '';
    }
    redirect.users_id = parseInt(redirect.users_id) || 0;
    // Socket peers are not authoritative redirect sources. Require an explicit click instead
    // of allowing the callback to start the automatic redirect countdown.
    redirect.requireConfirmation = true;
    return sanitized;
}

function safeSocketAvideoResponse(response) {
    if (!response || typeof response !== 'object') {
        return;
    }
    if (response.responseJSON && typeof response.responseJSON === 'object') {
        response = response.responseJSON;
    }
    var msg = response.msg || (typeof response.error === 'string' ? response.error : '');
    if (response.error) {
        if (typeof avideoAlertError === 'function') {
            avideoAlertError(String(msg || __('Error')));
        }
        return;
    }

    var safeMsg = escapeSocketCallbackHTML(msg || __('Success'));
    if (response.warning && typeof avideoToastWarning === 'function') {
        avideoToastWarning(safeMsg);
    } else if (response.info && typeof avideoToastInfo === 'function') {
        avideoToastInfo(safeMsg);
    } else if (typeof avideoToastSuccess === 'function') {
        avideoToastSuccess(safeMsg);
    }
}

function safeSocketRedirect(details) {
    if (
        !details ||
        !details.redirectLive ||
        typeof isLive !== 'function' ||
        typeof window.redirectLive !== 'function'
    ) {
        return;
    }
    var currentLive = isLive();
    if (!currentLive || parseInt(currentLive.users_id) !== details.redirectLive.users_id) {
        return;
    }
    window.redirectLive(details);
}

// Resolve functions when a message arrives because some plugin scripts load before YPTSocket
// and others load after it. The callback name itself is still fixed by this allowlist.
function registerWindowSocketCallback(name, sanitizeDetails) {
    registerSocketCallback(name, function (details) {
        var callback = window[name];
        if (typeof callback === 'function') {
            callback(details);
        }
    }, sanitizeDetails);
}

function registerSocketEvent(name, sanitizeDetails) {
    registerSocketCallback(name, defaultCallback, sanitizeDetails);
}

registerSocketCallback('avideoResponse', safeSocketAvideoResponse, sanitizeSocketCallbackObject);
registerSocketCallback('avideoToastSuccess', function (details) {
    if (typeof avideoToastSuccess === 'function') {
        avideoToastSuccess(details);
    }
}, sanitizeSocketCallbackText);
registerWindowSocketCallback('socketClearSessionCache', sanitizeSocketCallbackObject);
registerWindowSocketCallback('aiSocketMessage', sanitizeSocketCallbackStrings);
registerWindowSocketCallback('socketWalletAddBalance', sanitizeSocketCallbackObject);
registerWindowSocketCallback('socketCDNStorageMoved', sanitizeSocketCallbackText);
registerSocketEvent('BTCPayments', sanitizeSocketCallbackStrings);
registerWindowSocketCallback('socketUserNotificationCallback', sanitizeSocketUserNotification);
registerWindowSocketCallback('socketLiveONCallback', sanitizeSocketCallbackObject);
registerWindowSocketCallback('socketLiveOFFCallback', sanitizeSocketCallbackObject);
registerSocketCallback('redirectLive', safeSocketRedirect, sanitizeSocketRedirect);
registerSocketEvent('socketRemoveLiveLinks', sanitizeSocketCallbackObject);
registerSocketEvent('loadCallerPanel', sanitizeSocketCallbackObject);

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

        if (json.callback && Object.prototype.hasOwnProperty.call(socketCallbackAllowlist, json.callback)) {
            // Allowlist-only dispatch: a peer can only trigger a handler a plugin explicitly
            // registered via registerSocketCallback(), never an arbitrary window function.
            socketLog('Executing callback:', json.callback);
            var callbackRegistration = socketCallbackAllowlist[json.callback];
            if (callbackRegistration.sanitizeDetails) {
                _details = callbackRegistration.sanitizeDetails(_details);
            }
            myfunc = callbackRegistration.callback;

            // Trigger the event with the same name as json.callback and pass the JSON object
            const event = new CustomEvent(json.callback, { detail: _details });
            document.dispatchEvent(event);
        } else {
            socketLog('No valid callback defined, using default');
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

function socketDisposeTransport() {
    clearTimeout(socketReadyTimeout);
    socketReady = false;
    const previousSocket = typeof socket !== 'undefined' ? socket : null;
    const previousWS = connWS;
    socket = null;
    connWS = null;
    if (previousSocket) previousSocket.disconnect();
    if (previousWS && previousWS.readyState < 2) previousWS.close();
}

function socketScheduleReconnect() {
    clearTimeout(socketConnectTimeout);
    socketConnectRequested = false;
    socketDisposeTransport();
    onSocketClose();
    const delay = socketConnectRetryTimeout;
    socketConnectRetryTimeout = Math.min(delay * 2, 60000);
    socketConnectTimeout = setTimeout(function () {
        socketConnectTimeout = null;
        startSocket();
    }, delay);
}

function socketConnectOld() {
    if (socketConnectRequested || isSocketActive()) return false;
    clearTimeout(socketConnectTimeout);
    if (!isOnline()) {
        socketScheduleReconnect();
        return false;
    }
    socketConnectRequested = true;
    setSocketIconStatus('loading');
    const url = addGetParam(webSocketURL, 'page_title', document.title);
    let connection;
    try {
        if (!isValidURL(url)) throw new Error('Invalid WebSocket URL');
        connection = new WebSocket(url);
        connWS = connection;
    } catch (error) {
        socketError('WebSocket creation failed:', error.message);
        socketScheduleReconnect();
        return false;
    }
    socketReadyTimeout = setTimeout(function () {
        if (connWS === connection && !socketReady) socketScheduleReconnect();
    }, 35000);
    connection.onopen = function () {
        // Wait for the authenticated PHP server's first message before enabling sends.
        if (connWS !== connection) return;
        setSocketIconStatus('loading');
        // Request an existing, non-mutating echo even when presence broadcasts are disabled.
        try {
            connection.send(JSON.stringify({ type: webSocketTypes.TESTING, msg: webSocketTypes.TESTING, webSocketToken: webSocketToken }));
        } catch (error) {
            socketScheduleReconnect();
        }
    };
    connection.onmessage = function (event) {
        if (connWS !== connection) return;
        let json;
        try {
            json = JSON.parse(event.data);
        } catch (error) {
            socketError('Invalid WebSocket response');
            return;
        }
        if (!json || typeof json !== 'object') return;
        if (json.resourceId) socketResourceId = json.resourceId;
        yptSocketResponse = json;
        parseSocketResponse();
        onSocketOpen();
        const messages = json.type === webSocketTypes.MSG_TO_ALL && Array.isArray(json.msg) ? json.msg : [json];
        messages.forEach(socketProcessMessage);
    };
    connection.onclose = function () {
        if (connWS === connection) socketScheduleReconnect();
    };
    connection.onerror = function () {
        if (connWS === connection) socketScheduleReconnect();
    };
}

function socketProcessMessage(message) {
    if (!message || typeof message !== 'object') return;
    try {
        processSocketJson(message);
        if (message.users_id) setUserOnlineStatus(message.users_id);
    } catch (error) {
        // One plugin callback must not prevent delivery of the rest of a batch.
        socketError('Socket message callback failed:', error.message);
    }
}

function socketConnectIO() {
    if (socketConnectRequested || isSocketActive()) return false;
    clearTimeout(socketConnectTimeout);
    if (!isOnline()) {
        socketScheduleReconnect();
        return false;
    }
    socketConnectRequested = true;
    setSocketIconStatus('loading');
    const url = addGetParam(webSocketURL, 'page_title', encodeURIComponent(document.title));
    let connection;
    try {
        if (!isValidURL(url)) throw new Error('Invalid Socket.IO URL');
        connection = io(url, { transports: ['websocket'], timeout: 10000, reconnection: false });
        socket = connection;
    } catch (error) {
        socketError('Socket.IO initialization failed:', error.message);
        socketScheduleReconnect();
        return false;
    }
    socketReadyTimeout = setTimeout(function () {
        if (socket === connection && !socketReady) socketScheduleReconnect();
    }, 35000);
    connection.on('connect', function () {
        if (socket !== connection) return;
        socketResourceId = connection.id;
        // Socket.IO transport connected; PHP authentication is still pending.
        setSocketIconStatus('loading');
    });
    connection.on('yptReady', function () {
        if (socket === connection) onSocketOpen();
    });
    connection.on('message', function (data) {
        if (socket !== connection || !data || typeof data !== 'object') return;
        // Older Node servers confirm authentication by their first delivered message.
        onSocketOpen();
        if (data.type === webSocketTypes.MSG_BATCH) {
            yptSocketResponse = data;
            parseSocketResponse();
            if (Array.isArray(data.messages)) data.messages.forEach(socketProcessMessage);
        } else {
            if (data.users_id_online !== undefined) socketApplyOnlineUsers(data.users_id_online);
            socketProcessMessage(data);
        }
    });
    connection.on('broadcast', function (data) {
        if (socket !== connection) return;
        onSocketOpen();
        socketProcessMessage(data);
    });
    connection.on('disconnect', function (reason) {
        if (socket !== connection) return;
        socketError('Disconnected:', reason);
        if (reason === 'io client disconnect') {
            socketConnectRequested = false;
            onSocketClose();
        } else {
            socketScheduleReconnect();
        }
    });
    connection.on('connect_error', function (error) {
        if (socket !== connection) return;
        socketError('Connection error:', error.message);
        socketScheduleReconnect();
    });
    connection.on('error', function () {
        if (socket === connection) socketError('Socket server reported a message or validation error');
    });
}

/** Share one bounded token request between reconnect and proactive refresh. */
function fetchWebSocketToken(onSuccess, onFail) {
    socketTokenCallbacks.push({ onSuccess: onSuccess, onFail: onFail });
    if (socketTokenFetching) return;
    socketTokenFetching = true;
    let url = webSiteRootURL + 'plugin/YPTSocket/getWebSocket.json.php';
    url = addGetParam(url, 'webSocketSelfURI', webSocketSelfURI);
    url = addGetParam(url, 'webSocketVideos_id', webSocketVideos_id);
    url = addGetParam(url, 'webSocketLiveKey', webSocketLiveKey);
    function finish(response, error) {
        socketTokenFetching = false;
        const callbacks = socketTokenCallbacks.splice(0);
        callbacks.forEach(function (pending) {
            const callback = error ? pending.onFail : pending.onSuccess;
            if (typeof callback === 'function') callback(error || response);
        });
    }
    $.ajax({
        url: url,
        dataType: 'json',
        timeout: 10000,
        success: function (response) {
            if (!response || response.error || typeof response.webSocketToken !== 'string' || !response.webSocketToken || typeof response.webSocketURL !== 'string' || !response.webSocketURL) {
                finish(null, 'Socket configuration unavailable');
                return;
            }
            webSocketToken = response.webSocketToken;
            webSocketURL = response.webSocketURL;
            finish(response);
        },
        error: function () { finish(null, 'Socket configuration request failed'); }
    });
}

function attemptTokenRefresh() {
    fetchWebSocketToken(
        function () { if (socketReady) scheduleTokenRefresh(); },
        function () {
            if (socketReady) tokenRefreshTimeout = setTimeout(attemptTokenRefresh, 30 * 60 * 1000);
        }
    );
}

function scheduleTokenRefresh() {
    clearTimeout(tokenRefreshTimeout);
    tokenRefreshTimeout = setTimeout(attemptTokenRefresh, 11 * 60 * 60 * 1000);
}

function onSocketOpen() {
    if (socketReady) return;
    socketReady = true;
    socketConnectRequested = false;
    socketConnectRetryTimeout = 2000;
    clearTimeout(socketConnectTimeout);
    clearTimeout(socketReadyTimeout);
    setSocketIconStatus('connected');
    scheduleTokenRefresh();
    socketFlushSendQueue();
    document.dispatchEvent(new CustomEvent('YPTSocketReady'));
}

function onSocketClose() {
    socketReady = false;
    socketResourceId = undefined;
    clearTimeout(socketReadyTimeout);
    clearTimeout(tokenRefreshTimeout);
    setSocketIconStatus('disconnected');
    socketApplyOnlineUsers([]);
}


function setSocketIconStatus(status) {
    if (typeof socketInfoSetStatus === 'function') socketInfoSetStatus(status);
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
    return sendSocketMessageToUser(msg, callback, "");
}

function sendSocketMessageToNone(msg, callback) {
    return sendSocketMessageToUser(msg, callback, -1);
}

function sendSocketMessage(payload) {
    if (!payload || typeof payload !== 'object') return false;
    socketPruneSendQueue();
    if (socketSendQueue.length >= socketSendQueueLimit) {
        socketReportSendFailure('queue_full', 1);
        return false;
    }
    let snapshot;
    try {
        // Snapshot now, as the old immediate send did. Callers may reuse/mutate msg.
        snapshot = JSON.parse(JSON.stringify({ ...payload, webSocketToken: undefined }));
    } catch (error) {
        socketReportSendFailure('invalid_payload', 1);
        return false;
    }
    // The token is filled when sending, after any refresh/reconnection.
    socketSendQueue.push({ payload: snapshot, expires: Date.now() + socketSendLifetime });
    if (!socketSendTimer) socketSendTimer = setTimeout(socketFlushSendQueue, 0);
    return true;
}

function socketReportSendFailure(reason, count) {
    socketError('Outgoing socket messages not sent:', reason, count);
    document.dispatchEvent(new CustomEvent('YPTSocketSendError', { detail: { reason: reason, count: count } }));
}

function socketPruneSendQueue() {
    let expired = 0;
    while (socketSendQueue.length && socketSendQueue[0].expires <= Date.now()) {
        socketSendQueue.shift();
        expired++;
    }
    if (expired) socketReportSendFailure('expired', expired);
}

function socketFlushSendQueue() {
    clearTimeout(socketSendTimer);
    socketSendTimer = null;
    socketPruneSendQueue();
    let sent = 0;
    while (socketSendQueue.length && isSocketActive() && sent < 32) {
        const item = socketSendQueue.shift();
        const payload = { ...item.payload, webSocketToken: webSocketToken };
        try {
            if (useSocketIO) socket.emit('message', payload);
            else connWS.send(JSON.stringify(payload));
        } catch (error) {
            // A send may already have reached the peer: never replay it automatically.
            socketReportSendFailure('send_failed', 1);
            socketScheduleReconnect();
            break;
        }
        sent++;
    }
    if (socketSendQueue.length) {
        const delay = isSocketActive() ? 25 : Math.max(1, socketSendQueue[0].expires - Date.now());
        socketSendTimer = setTimeout(socketFlushSendQueue, delay);
    }
}

function sendSocketMessageToUser(msg, callback, to_users_id) {
    return sendSocketMessage({ msg, webSocketToken, callback, to_users_id });
}

function sendSocketMessageToResourceId(msg, callback, resourceId) {
    return sendSocketMessage({ msg, webSocketToken, callback, resourceId });
}

function isSocketActive() {
    return isOnline() && socketReady && (useSocketIO ? !!(socket && socket.connected) : !!(connWS && connWS.readyState === 1));
}

function defaultCallback(json) {
    ////console.log('defaultCallback', json);
}

var socketAutoUpdateOnHTMLTimout;
var globalAutoUpdateOnHTML = [];
function socketAutoUpdateOnHTML(autoUpdateOnHTML) {
    if (typeof socketInfoRecordUpdate === 'function') socketInfoRecordUpdate(autoUpdateOnHTML);
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
        socketApplyOnlineUsers(json.users_id_online);
    }

    if (typeof json.autoUpdateOnHTML !== 'undefined') {
        socketAutoUpdateOnHTML(json.autoUpdateOnHTML);
    }

    // SECURITY REVIEW (2026-08-21): live eval() sink. Only reachable here via the top-level
    // (non-batch) message wrapper's .msg field, which server-side PHP implementations
    // (Message.php/MessageSQLite*.php) strip via removeAutoEvalCodeOnHTMLRecursive() before
    // relay; the shipped node socket server does not contain that filter. Not confirmed
    // exploitable through the node server in this pass (batch-wrapped messages read this from
    // the array wrapper, not a per-item message), but this is a defense-in-depth gap - the
    // client should not eval server/peer-supplied strings at all. Left for maintainer judgement,
    // do not remove this comment without re-verifying reachability through the node server.
    if (json.msg?.autoEvalCodeOnHTML !== undefined) {
        eval(json.msg.autoEvalCodeOnHTML);
    }

    const ignoreURI = ['latestOrLive.php', 'plugin/Chat2'];
    const validAnchorHrefs = new Set();

    if (json && (json.users_uri || json.users_id_online) && $('#socket_info_container').length) {
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
                        updateSocketUserCard(userData, socketResourceId, validAnchorHrefs, 'a1');
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

                updateSocketUserCard(element, socketResourceId, validAnchorHrefs, 'a2');
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
        tooltip = `(${socketEscapeHtml(client.browser)} - ${socketEscapeHtml(client.os)}) ${socketEscapeHtml(userData.ip)}`;
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
    if (typeof users_id_online === 'undefined') return false;
    socketOnlineUserIds(users_id_online).forEach(setUserOnlineStatus);
    return true;
}

function socketOnlineUserIds(users) {
    if (!users || typeof users !== 'object') return [];
    const ids = Array.isArray(users) ? users.map(user => user && typeof user === 'object' ? user.users_id : user) : Object.keys(users);
    return [...new Set(ids.map(Number).filter(id => Number.isSafeInteger(id) && id >= 0))];
}

function socketApplyOnlineUsers(users) {
    if (!users || typeof users !== 'object') return;
    const previous = socketOnlineUserIds(users_id_online);
    users_id_online = users;
    new Set([...previous, ...socketOnlineUserIds(users)]).forEach(setUserOnlineStatus);
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
async function startSocket() {
    if(typeof webSocketURL === 'undefined') {
        console.debug('startSocket: webSocketURL is empty or undefined');
        return false;
    }
    if (socketConnectRequested || isSocketActive()) return false;
    clearTimeout(socketConnectTimeout);
    if (!isOnline() || typeof webSiteRootURL == 'undefined') {
        //console.log('startSocket: Not Online');
        socketScheduleReconnect();
        return false;
    }
    socketConnectRequested = true;
    setSocketIconStatus('loading');
    ////console.log('Getting webSocketToken ...');
    fetchWebSocketToken(
        function () {
            socketConnectRequested = false;
            socketConnect();
        },
        function (reason) {
            socketError(reason);
            socketScheduleReconnect();
        }
    );
    if (inIframe()) {
        $('#socket_info_container').hide();
    }
    setInitialOnlineStatus();
}
