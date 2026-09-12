/* Presentation only: no polling, connection changes or server requests on open. */
var socketInfoState = { status: 'loading', connectedAt: 0, everConnected: false, reconnects: 0, updatedAt: 0, onlineCount: null };
var socketInfoPosition = null;
var socket_info_container_draging = false;

function socketInfoMinimize(restoreFocus) {
    const container = document.getElementById('socket_info_container');
    if (!container) return;
    container.classList.add('socketMinimized');
    document.getElementById('socketInfoPanel').hidden = true;
    document.getElementById('socketInfoToggle').setAttribute('aria-expanded', 'false');
    checkSocketInfoPosition();
    if (restoreFocus) document.getElementById('socketInfoToggle').focus();
}

function socketInfoMaximize() {
    const container = document.getElementById('socket_info_container');
    if (!container) return;
    container.classList.remove('socketMinimized');
    document.getElementById('socketInfoPanel').hidden = false;
    document.getElementById('socketInfoToggle').setAttribute('aria-expanded', 'true');
    checkSocketInfoPosition();
    socketInfoRefreshTimes();
}

// Keep the existing spelling for integrations that call this function.
function socketInfoToogle() {
    if (socket_info_container_draging) return;
    const container = document.getElementById('socket_info_container');
    if (!container) return;
    if (container.classList.contains('socketMinimized')) socketInfoMaximize();
    else socketInfoMinimize();
}

function checkSocketInfoPosition() {
    const container = document.getElementById('socket_info_container');
    if (!container || socket_info_container_draging || !container.getClientRects().length) return;
    if (!socketInfoPosition) {
        container.classList.toggle('socket-inspector-right', Cookies.get('socketInfoCorner') === 'right');
        return;
    }
    const rect = container.getBoundingClientRect();
    const viewport = window.visualViewport;
    const leftEdge = viewport ? viewport.offsetLeft : 0;
    const topEdge = viewport ? viewport.offsetTop : 0;
    const width = viewport ? viewport.width : document.documentElement.clientWidth;
    const height = viewport ? viewport.height : document.documentElement.clientHeight;
    const left = Math.min(Math.max(leftEdge + 12, socketInfoPosition.left), Math.max(leftEdge + 12, leftEdge + width - rect.width - 12));
    const top = Math.min(Math.max(topEdge + 12, socketInfoPosition.top), Math.max(topEdge + 12, topEdge + height - rect.height - 12));
    $(container).css({ left: left, top: top, right: 'auto', bottom: 'auto' });
}

function socketInfoInitDrag() {
    const container = $('#socket_info_container');
    const savedLeft = Cookies.get('socketInfoPositionLeft');
    const savedTop = Cookies.get('socketInfoPositionTop');
    if (savedLeft !== undefined && savedTop !== undefined && savedLeft.trim() !== '' && savedTop.trim() !== '' && Number.isFinite(Number(savedLeft)) && Number.isFinite(Number(savedTop))) {
        socketInfoPosition = { left: Number(savedLeft), top: Number(savedTop) };
    }
    checkSocketInfoPosition();
    if (typeof container.draggable !== 'function') return;
    container.draggable({
        handle: '.socket-inspector-heading, #socketInfoToggle',
        cancel: '#socketInfoClose, a, input, textarea, select, option, summary',
        containment: 'window',
        scroll: false,
        distance: 6,
        start: function () {
            const rect = this.getBoundingClientRect();
            socket_info_container_draging = true;
            $(this).css({ left: rect.left, top: rect.top, right: 'auto', bottom: 'auto' });
        },
        stop: function () {
            const rect = this.getBoundingClientRect();
            socketInfoPosition = { left: rect.left, top: rect.top };
            // Ignore the mouseup/click from dragging the compact toggle.
            setTimeout(() => {
                socket_info_container_draging = false;
                checkSocketInfoPosition();
                const position = this.getBoundingClientRect();
                socketInfoPosition = { left: position.left, top: position.top };
                Cookies.set('socketInfoPositionLeft', position.left, avideoCookieOptions(365));
                Cookies.set('socketInfoPositionTop', position.top, avideoCookieOptions(365));
            }, 0);
        }
    });
}

function socketUserNameToggle(socketUserDivID) {
    const user = $(socketUserDivID);
    user.toggleClass('visible');
    Cookies.set(socketUserDivID, user.hasClass('visible'), avideoCookieOptions(365));
}

function socketInfoSetStatus(status) {
    const label = document.querySelector('.socket-inspector-status');
    if (!label) return;
    status = status === 'connected' || status === 'disconnected' ? status : 'loading';
    if (status === 'connected' && socketInfoState.status !== 'connected') {
        if (socketInfoState.everConnected) socketInfoState.reconnects++;
        socketInfoState.everConnected = true;
        socketInfoState.connectedAt = Date.now();
    }
    if (status !== 'connected') {
        socketInfoState.connectedAt = 0;
        socketInfoState.onlineCount = null;
    }
    socketInfoState.status = status;
    const text = label.getAttribute('data-' + status);
    label.textContent = text;
    document.querySelector('.socket-inspector-label').textContent = text;
    socketInfoRefreshSummary();
    document.getElementById('socketInfoReconnects').textContent = socketInfoState.reconnects;
    document.getElementById('socketInfoTransport').textContent = typeof useSocketIO !== 'undefined' && useSocketIO ? 'Socket.IO / WebSocket' : 'WebSocket';
    socketInfoRefreshTimes();
}

function socketInfoRecordUpdate(values) {
    if (!values || !document.getElementById('socket_info_container')) return;
    // This total groups tabs by user/device; total_users_online counts connections.
    const online = values.total_devices_online;
    if ((typeof online === 'number' || (typeof online === 'string' && online.trim() !== '')) && Number.isSafeInteger(Number(online)) && Number(online) >= 0) {
        socketInfoState.onlineCount = Number(online);
        socketInfoRefreshSummary();
    }
    const keys = ['total_devices_online', 'total_users_online', 'webSocketServerVersion', 'socket_mem'];
    let changed = false;
    keys.forEach(key => {
        if (typeof values[key] === 'string' || typeof values[key] === 'number') {
            $('#socket_info_container .' + key).text(values[key]);
            changed = true;
        }
    });
    if (changed) {
        socketInfoState.updatedAt = Date.now();
        socketInfoRefreshTimes();
    }
}

function socketInfoRefreshSummary() {
    const online = document.getElementById('socketInfoOnline');
    online.hidden = socketInfoState.status !== 'connected' || socketInfoState.onlineCount === null;
    document.getElementById('socketInfoOnlineCount').textContent = socketInfoState.onlineCount === null ? '—' : socketInfoState.onlineCount.toLocaleString();
    const status = document.querySelector('.socket-inspector-label').textContent;
    const count = online.hidden ? '' : ', ' + online.textContent.replace(/\s+/g, ' ').trim();
    document.getElementById('socketInfoToggle').setAttribute('aria-label', document.getElementById('socketInfoTitle').textContent + ': ' + status + count);
}

function socketInfoRefreshTimes() {
    const panel = document.getElementById('socketInfoPanel');
    if (!panel || panel.hidden || document.hidden) return;
    const seconds = socketInfoState.connectedAt ? Math.max(0, Math.floor((Date.now() - socketInfoState.connectedAt) / 1000)) : null;
    const elapsed = seconds === null ? '—' : [Math.floor(seconds / 3600), Math.floor(seconds / 60) % 60, seconds % 60].map(value => String(value).padStart(2, '0')).join(':');
    document.getElementById('socketInfoDuration').textContent = elapsed;
    document.getElementById('socketInfoLastUpdate').textContent = socketInfoState.updatedAt ? new Date(socketInfoState.updatedAt).toLocaleTimeString() : '—';
}

$(function () {
    const container = document.getElementById('socket_info_container');
    if (!container) return;
    socketInfoInitDrag();
    $(window).on('resize.socketInfo orientationchange.socketInfo', checkSocketInfoPosition);
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', checkSocketInfoPosition);
        window.visualViewport.addEventListener('scroll', checkSocketInfoPosition);
    }
    // Details, translations and status labels can change the panel's dimensions.
    if (typeof ResizeObserver === 'function') {
        new ResizeObserver(checkSocketInfoPosition).observe(container);
    }
    $('#socketInfoToggle').on('click', socketInfoToogle);
    $('#socketInfoClose').on('click', () => socketInfoMinimize(true));
    $(document).on('click.socketInfo', event => {
        if (!container.contains(event.target)) socketInfoMinimize();
    }).on('keydown.socketInfo', event => {
        if (event.key === 'Escape' && !container.classList.contains('socketMinimized')) {
            socketInfoMinimize(container.contains(document.activeElement));
        }
    }).on('focusin.socketInfo focusout.socketInfo fullscreenchange.socketInfo', () => {
        setTimeout(() => {
            const editing = window.matchMedia('(max-width: 767px)').matches && document.activeElement && document.activeElement.matches('input, textarea, [contenteditable="true"]');
            container.classList.toggle('socket-inspector-suspended', !!document.fullscreenElement || !!editing);
        }, 0);
    });
    $('#socketInfoRetry').on('click', () => startSocket());
    $('#socketInfoCopyCommand').on('click', function () { copyToClipboard(this.getAttribute('data-command')); });
    $('#socketInfoRestart').on('click', async function () {
        if (await avideoConfirm(this.getAttribute('data-confirm'))) {
            avideoAjax(webSiteRootURL + 'plugin/YPTSocket/restart.json.php', {});
        }
    });
    setInterval(socketInfoRefreshTimes, 1000);
});
