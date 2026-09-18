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
        // Fullscreen is driven by the stylesheet (inset:0); the floating
        // left/top/width/height would override it, so drop them while expanded
        // and restore the remembered spot when the viewer compresses again.
        if (expanded) clearLayout();
        else floatPanel();
    }
    function setOpen(open) {
        panel.hidden = !open;
        syncExpanded();
        // Measured while visible: a hidden panel has no rect to start from.
        if (open) floatPanel();
        launcher.setAttribute('aria-expanded', String(open));
        // The same button opens and closes the chat: show which one it will do
        // (the stylesheet swaps the icon/photo for a chevron while open).
        widget.classList.toggle('is-open', open);
        if (open) hideTeaser();
        var label = open ? launcher.dataset.labelClose : launcher.dataset.labelOpen;
        if (label) {
            launcher.setAttribute('aria-label', label);
            launcher.setAttribute('title', label);
        }
        // Load only once. Hiding preserves the draft, messages and any stream.
        if (open && !frame.getAttribute('src')) frame.src = frame.dataset.src;
        if (open) close.focus();
        else launcher.focus();
    }
    launcher.addEventListener('click', function () { setOpen(panel.hidden); });
    close.addEventListener('click', function () { setOpen(false); });

    // --- Launcher branding: the agent's photo and a short invitation ---
    // Fetched from Companion's public widget config (same key as the iframe)
    // so it always reflects the Site's current settings. Any failure simply
    // leaves the generic chat icon; the chat itself never depends on this.
    var teaser = widget.querySelector('.companion-widget-teaser');
    var teaserText = teaser.querySelector('.companion-widget-teaser-text');
    var teaserDismiss = teaser.querySelector('.companion-widget-teaser-dismiss');
    var photo = launcher.querySelector('.companion-widget-launcher-photo');
    var statusDot = launcher.querySelector('.companion-widget-launcher-status');
    var titleText = widget.querySelector('.companion-widget-title-text');
    var TEASER_DELAY_MS = 2500;
    var teaserTimer = null;
    // Hide only for this page visit. Opening another video or reloading must
    // show the current invitation, even if the viewer opened chat earlier.
    var teaserDismissed = false;
    function hideTeaser() {
        if (teaserTimer) { clearTimeout(teaserTimer); teaserTimer = null; }
        teaser.hidden = true;
        teaserDismissed = true;
    }
    function scheduleTeaser(message) {
        if (!message || teaserDismissed || !panel.hidden) return;
        teaserText.textContent = message;
        teaserTimer = setTimeout(function () {
            teaserTimer = null;
            if (panel.hidden && !teaserDismissed) teaser.hidden = false;
        }, TEASER_DELAY_MS);
    }
    teaserText.addEventListener('click', function () { setOpen(true); });
    teaserDismiss.addEventListener('click', function (event) {
        event.stopPropagation();
        hideTeaser();
        launcher.focus();
    });
    function applyBranding(config) {
        var name = config && typeof config.agent_name === 'string' ? config.agent_name.trim() : '';
        var avatar = config && typeof config.agent_avatar_url === 'string' ? config.agent_avatar_url.trim() : '';
        var message = config && typeof config.launcher_message === 'string' ? config.launcher_message.trim() : '';
        if (config && (config.theme === 'dark' || config.theme === 'light')) {
            widget.dataset.theme = config.theme;
        }
        if (name) {
            titleText.textContent = name;
            if (panel.hidden) launcher.setAttribute('title', name);
        }
        if (avatar && /^https?:\/\//i.test(avatar)) {
            photo.addEventListener('load', function () {
                photo.hidden = false;
                statusDot.hidden = false;
                widget.classList.add('has-photo');
            });
            photo.src = avatar;
        }
        scheduleTeaser(message || teaser.dataset.defaultMessage || '');
    }
    var configUrl = widget.dataset.configUrl;
    if (configUrl && window.fetch) {
        fetch(configUrl, { credentials: 'omit', mode: 'cors', cache: 'no-store' })
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(applyBranding)
            .catch(function () { applyBranding(null); });
    } else {
        applyBranding(null);
    }
    expand.addEventListener('click', function () {
        setExpanded(!panel.classList.contains('is-expanded'));
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !panel.hidden) setOpen(false);
    });

    // --- Floating layout: drag, resize and remember ---
    // jQuery UI is loaded globally by view/include/footer.php before plugin
    // footer code, and is already used the same way by avideoWindowIframe()
    // in view/js/script.js. On phones (the stylesheet's 600px breakpoint) the
    // panel keeps its fixed inset layout and none of this applies.
    var LAYOUT_KEY = 'companionWidgetLayout';
    var LAYOUT_MIN = { width: 280, height: 240 };
    var mobileQuery = window.matchMedia ? window.matchMedia('(max-width: 600px)') : null;
    var $panel = window.jQuery ? window.jQuery(panel) : null;
    var canFloat = !!($panel && $panel.draggable && $panel.resizable);
    function isMobile() { return !!(mobileQuery && mobileQuery.matches); }
    function readLayout() {
        try {
            var saved = JSON.parse(window.localStorage.getItem(LAYOUT_KEY));
            if (saved && isFinite(saved.left) && isFinite(saved.top) && isFinite(saved.width) && isFinite(saved.height)) return saved;
        } catch (e) {
            // Storage blocked or a malformed value: start from the default spot.
        }
        return null;
    }
    function saveLayout(layout) {
        try {
            window.localStorage.setItem(LAYOUT_KEY, JSON.stringify(layout));
        } catch (e) {
            // Storage blocked (private mode/quota): the layout lasts for this page only.
        }
    }
    // The whole panel stays inside the viewport and never grows past it. A
    // remembered layout from a bigger screen is shrunk/moved to fit this one.
    function fitLayout(layout) {
        var winW = window.innerWidth, winH = window.innerHeight;
        var out = {};
        out.width = Math.max(Math.min(LAYOUT_MIN.width, winW), Math.min(layout.width, winW));
        out.height = Math.max(Math.min(LAYOUT_MIN.height, winH), Math.min(layout.height, winH));
        out.left = Math.min(Math.max(layout.left, 0), winW - out.width);
        out.top = Math.min(Math.max(layout.top, 0), winH - out.height);
        return out;
    }
    function currentLayout() {
        var rect = panel.getBoundingClientRect();
        return { left: rect.left, top: rect.top, width: rect.width, height: rect.height };
    }
    function applyLayout(layout) {
        layout = fitLayout(layout);
        panel.classList.add('is-floating');
        panel.style.left = Math.round(layout.left) + 'px';
        panel.style.top = Math.round(layout.top) + 'px';
        panel.style.width = Math.round(layout.width) + 'px';
        panel.style.height = Math.round(layout.height) + 'px';
        return layout;
    }
    function clearLayout() {
        panel.classList.remove('is-floating');
        panel.style.left = panel.style.top = panel.style.width = panel.style.height = '';
    }
    // Pin the open panel to the remembered spot, or to wherever the
    // stylesheet placed it when nothing was remembered yet.
    function floatPanel() {
        if (!canFloat || panel.hidden || panel.classList.contains('is-expanded')) return;
        if (isMobile()) { clearLayout(); return; }
        applyLayout(readLayout() || currentLayout());
    }
    function rememberLayout() {
        panel.classList.remove('is-dragging');
        saveLayout(applyLayout(currentLayout()));
    }
    function beginDragOrResize() {
        if (!panel.classList.contains('is-floating')) return false;
        // The iframe would swallow the pointer while it passes over it.
        panel.classList.add('is-dragging');
        return true;
    }
    // Edge handles (n/w) move the panel while resizing it, so a plain
    // maxWidth/maxHeight is not enough: clamp position and size together.
    // jQuery UI applies ui.position/ui.size again after this callback.
    function clampResize(event, ui) {
        var winW = window.innerWidth, winH = window.innerHeight;
        if (ui.position.left < 0) { ui.size.width += ui.position.left; ui.position.left = 0; }
        if (ui.position.top < 0) { ui.size.height += ui.position.top; ui.position.top = 0; }
        ui.size.width = Math.min(ui.size.width, winW - ui.position.left);
        ui.size.height = Math.min(ui.size.height, winH - ui.position.top);
    }
    if (canFloat) {
        $panel.draggable({
            handle: '.companion-widget-bar',
            cancel: '.companion-widget-actions',
            containment: 'window',
            start: beginDragOrResize,
            stop: rememberLayout
        });
        $panel.resizable({
            handles: 'all',
            minWidth: LAYOUT_MIN.width,
            minHeight: LAYOUT_MIN.height,
            start: beginDragOrResize,
            resize: clampResize,
            stop: rememberLayout
        });
        // Re-fit when the window shrinks, and go back to the remembered size
        // when it grows again (fitLayout only shrinks what does not fit).
        window.addEventListener('resize', floatPanel);
    }

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
