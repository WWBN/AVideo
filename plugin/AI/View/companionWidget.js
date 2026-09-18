(function () {
    'use strict';
    var widget = document.getElementById('companionChatWidget');
    if (!widget || widget.dataset.initialized) return;
    widget.dataset.initialized = 'true';
    var panel = widget.querySelector('.companion-widget-panel');
    var launcher = widget.querySelector('.companion-widget-launcher');
    var close = widget.querySelector('.companion-widget-close');
    var expand = widget.querySelector('.companion-widget-expand');
    var frame = widget.querySelector('iframe');
    var frameOrigin = '';
    try {
        frameOrigin = new URL(frame.dataset.src, window.location.href).origin;
    } catch (e) {
        // Keep the chat itself usable; only the parent<->frame protocol below
        // needs an origin to validate against.
        frameOrigin = '';
    }
    function syncExpanded() {
        var expanded = panel.classList.contains('is-expanded') && !panel.hidden;
        widget.classList.toggle('is-expanded', expanded);
        document.body.classList.toggle('companion-widget-fullscreen', expanded);
    }
    function setExpanded(expanded) {
        panel.classList.toggle('is-expanded', expanded);
        expand.setAttribute('aria-pressed', String(expanded));
        expand.querySelector('i').className = expanded ? 'fas fa-compress' : 'fas fa-expand';
        syncExpanded();
    }
    function setOpen(open) {
        panel.hidden = !open;
        syncExpanded();
        launcher.setAttribute('aria-expanded', String(open));
        // Load only once. Hiding preserves the draft, messages and any stream.
        if (open && !frame.getAttribute('src')) frame.src = frame.dataset.src;
        if (open) close.focus();
        else launcher.focus();
    }
    launcher.addEventListener('click', function () { setOpen(panel.hidden); });
    close.addEventListener('click', function () { setOpen(false); });
    expand.addEventListener('click', function () {
        setExpanded(!panel.classList.contains('is-expanded'));
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !panel.hidden) setOpen(false);
    });

    // --- Host player control (see frontend/src/components/EmbedPlayerProvider.tsx) ---
    // The chat is framed over the watch page of the very video it answers
    // about, so a citation seeks THIS player instead of opening a second copy
    // of the same video in another tab.
    function getPlayer() {
        // AVideo's own global, set by the watch page's video.js bootstrap.
        if (typeof player !== 'undefined' && player && typeof player.currentTime === 'function') return player;
        if (window.videojs && typeof window.videojs.getPlayers === 'function') {
            var players = window.videojs.getPlayers();
            for (var id in players) {
                if (players[id] && typeof players[id].currentTime === 'function') return players[id];
            }
        }
        return null;
    }
    function tell(type) {
        if (frameOrigin && frame.contentWindow) frame.contentWindow.postMessage({ type: type }, frameOrigin);
    }
    // video.js may still be booting when the chat asks. Answer as soon as the
    // player shows up rather than declaring it unavailable for the session.
    function announcePlayer(attempt) {
        if (getPlayer()) { tell('companion:player-available'); return; }
        if ((attempt || 0) >= 20) { tell('companion:player-unavailable'); return; }
        setTimeout(function () { announcePlayer((attempt || 0) + 1); }, 500);
    }
    function seekTo(seconds) {
        var target = getPlayer();
        if (!target || typeof seconds !== 'number' || !isFinite(seconds) || seconds < 0) return;
        // AVideo's own chapter jump also keeps ?t= in the address bar in sync,
        // so a reload or a shared URL lands on the same moment. It only acts on
        // the `player` global, so fall back whenever that is not the player we
        // actually found.
        if (typeof playChapter === 'function' && typeof player !== 'undefined' && target === player) {
            playChapter(seconds);
        } else {
            target.currentTime(seconds);
        }
        // A fullscreen chat panel would cover the moment we just jumped to.
        if (panel.classList.contains('is-expanded')) setExpanded(false);
        var started = target.play();
        // Autoplay policies are per-window, and the click happened inside the
        // cross-origin chat frame, so this can be refused on a page the viewer
        // has not interacted with yet. The seek already landed; leaving the
        // player paused on the right frame is the correct fallback.
        if (started && typeof started.catch === 'function') started.catch(function () {});
        if (typeof target.el === 'function' && target.el() && target.el().scrollIntoView) {
            target.el().scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // --- Playback context for a question (see frontend EmbedPlayerProvider.requestPlayerState) ---
    // A viewer asking "what are the options?" means the question on screen
    // RIGHT NOW. Right before sending, the chat asks where the player is and
    // for a capture of it; Companion shows that capture to the model for that
    // one answer and never stores it.
    var CAPTURE_MAX_WIDTH = 768;
    function captureFrame(target) {
        try {
            var el = typeof target.el === 'function' ? target.el() : null;
            var video = el ? el.querySelector('video') : null;
            if (!video || !video.videoWidth || !video.videoHeight || video.readyState < 2) return null;
            var scale = Math.min(1, CAPTURE_MAX_WIDTH / video.videoWidth);
            var canvas = document.createElement('canvas');
            canvas.width = Math.round(video.videoWidth * scale);
            canvas.height = Math.round(video.videoHeight * scale);
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            // Throws SecurityError when the media is cross-origin without CORS
            // (canvas tainted) - then only the position is reported.
            return canvas.toDataURL('image/jpeg', 0.72);
        } catch (e) {
            return null;
        }
    }
    function reportPlayerState(nonce) {
        var target = getPlayer();
        var message = { type: 'companion:player-state', nonce: nonce, currentTime: null, screenshot: null };
        if (target) {
            var time = Number(target.currentTime());
            if (isFinite(time) && time >= 0) {
                message.currentTime = time;
                message.screenshot = captureFrame(target);
            }
        }
        if (frameOrigin && frame.contentWindow) frame.contentWindow.postMessage(message, frameOrigin);
    }

    if (!frameOrigin) return;
    frame.addEventListener('load', function () { announcePlayer(0); });
    window.addEventListener('message', function (event) {
        if (event.source !== frame.contentWindow || event.origin !== frameOrigin) return;
        var data = event.data;
        if (!data || typeof data !== 'object') return;
        if (data.type === 'companion:close') setOpen(false);
        else if (data.type === 'companion:player-query') announcePlayer(0);
        else if (data.type === 'companion:player-state-query') reportPlayerState(data.nonce);
        else if (data.type === 'companion:seek') seekTo(Number(data.seconds));
    });
}());
