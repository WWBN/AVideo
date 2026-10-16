/* Read-only discovery. A timeout is inconclusive, never proof that the app is absent. */
(function (root) {
    'use strict';
    function address(value, site) {
        if (typeof value !== 'string' || !value || value.length > 8192 || /[\s\\]/.test(value)) throw new Error('address');
        var url = new URL(value);
        if (!/^https?:$/.test(url.protocol) || !url.hostname || url.username || url.password) throw new Error('address');
        if (site && (url.search || url.hash)) throw new Error('site');
        if (site) url.pathname = url.pathname.replace(/\/+$/, '') + '/';
        return url.href;
    }
    function context(site, fragment) {
        if (fragment.length > 24000) throw new Error('link');
        var result = { site: address(site, true) };
        var params = new URLSearchParams(fragment.replace(/^#/, ''));
        var media = params.get('url');
        if (media) result.url = address(media, false);
        return result;
    }
    function link(request) {
        var params = new URLSearchParams();
        params.set('site', address(request.site, true));
        if (request.url) params.set('url', address(request.url, false));
        return 'avideo-local-encoder://open?' + params.toString();
    }
    function init(config, win, doc) {
        win = win || window;
        doc = doc || document;
        var panel = doc.getElementById('local-encoder-launcher');
        if (!panel) return;
        if (panel.avideoLauncherCleanup) panel.avideoLauncherCleanup();
        var open = panel.querySelector('[data-open]');
        var control = panel.querySelector('[data-control]');
        control.classList.add('hidden');
        var status = panel.querySelector('[data-status]');
        var check = panel.querySelector('[data-check]');
        var request;
        var invalidRequest = false;
        try { request = context(config.site, win.location.hash); }
        catch (_) {
            status.textContent = config.text.invalid;
            request = { site: address(config.site, true) }; // retain manual opening and download
            invalidRequest = true;
        }
        var nativeLink = link(request);
        var autoOpen = !invalidRequest && new URLSearchParams(win.location.hash.slice(1)).get('open') === '1';
        var frames = [];
        var timer = null;
        var legacyTimer = null;
        var pollTimer = null;
        var generation = 0;
        var readyBase = '';
        var readyChallenge = '';
        var readyState = '';
        var readyFrame = null;
        var pendingLogin = null;
        var acknowledge = null;
        var nonce = '';
        open.href = nativeLink;
        open.removeAttribute('aria-disabled');
        open.classList.remove('disabled');
        function clearFrames(keep) {
            frames.forEach(function (item) { if (item !== keep) item.frame.remove(); });
            frames = keep ? [keep] : [];
        }
        function stop(keep) {
            if (pendingLogin) { pendingLogin.abort(); pendingLogin = null; }
            if (acknowledge) { acknowledge(false); acknowledge = null; }
            win.clearTimeout(legacyTimer);
            legacyTimer = null;
            win.clearTimeout(timer);
            win.clearTimeout(pollTimer);
            clearFrames(keep);
            generation++;
        }
        function controlURL(base) { return base + '/#avideo=' + encodeURIComponent(nativeLink); }
        async function openControl(base, challenge, state) {
            var destination = controlURL(base);
            var current = generation;
            if (config.login && /^[A-Za-z0-9_-]{43}$/.test(challenge) && readyFrame && !pendingLogin) {
                var abort = new win.AbortController();
                pendingLogin = abort;
                var timeout = win.setTimeout(function () {
                    abort.abort();
                    if (acknowledge) { acknowledge(false); acknowledge = null; }
                }, 25000);
                status.textContent = config.text.connecting;
                if (win.modal) win.modal.showPleaseWait();
                try {
                    var endpoint = new URL(config.login.url);
                    if (endpoint.origin !== new URL(win.location.href).origin) throw new Error('origin');
                    var fields = new URLSearchParams({ action: 'issue', challenge: challenge });
                    fields.set(config.login.field === 'csrfToken' ? 'csrfToken' : 'globalToken', config.login.token);
                    var response = await win.fetch(endpoint.href, { method: 'POST', credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                        body: fields.toString(), signal: abort.signal });
                    if (!response.ok) throw new Error('authorization');
                    var grant = await response.json();
                    if (current !== generation || abort.signal.aborted) return;
                    if (grant.error !== false || typeof grant.code !== 'string' || !/^[a-f0-9]{64}$/.test(grant.code)) throw new Error('authorization');
                    var accepted = new Promise(function (resolve) { acknowledge = resolve; });
                    // The one-use code stays out of native links, navigation, history and referrers.
                    readyFrame.frame.contentWindow.postMessage({ type: 'avideo-local-encoder:authorize', nonce: state,
                        site: request.site, code: grant.code }, base);
                    if (!await accepted) throw new Error('authorization');
                    destination += '&state=' + state; // public discovery correlation only; API CORS stays closed
                } catch (_) {
                    if (current === generation) status.textContent = config.text.loginFailed;
                } finally {
                    win.clearTimeout(timeout);
                    if (pendingLogin === abort) pendingLogin = null;
                    acknowledge = null;
                    if (win.modal) win.modal.hidePleaseWait();
                }
            }
            if (current === generation) win.location.replace(destination);
        }
        function receive(event) {
            var data = event.data;
            if (data && data.type === 'avideo-local-encoder:authorized') {
                if (acknowledge && readyFrame && event.origin === readyBase && event.source === readyFrame.frame.contentWindow
                    && data.nonce === readyState && data.protocol === 1) {
                    var resolve = acknowledge; acknowledge = null; resolve(data.ok === true);
                }
                return;
            }
            if (!data || data.type !== 'avideo-local-encoder:ready' || data.protocol !== 1 || data.nonce !== nonce) return;
            var sender = frames.find(function (item) { return event.origin === item.base && event.source === item.frame.contentWindow; });
            if (!sender) return;
            var challenge = typeof data.login_challenge === 'string' ? data.login_challenge : '';
            if (config.login && !/^[A-Za-z0-9_-]{43}$/.test(challenge)) {
                if (!legacyTimer) {
                    var current = generation;
                    legacyTimer = win.setTimeout(function () {
                        if (current === generation) confirmed(sender, data, '');
                    }, 400);
                }
                return;
            }
            confirmed(sender, data, challenge);
        }
        function confirmed(sender, data, challenge) {
            stop(config.login && /^[A-Za-z0-9_-]{43}$/.test(challenge) ? sender : null);
            readyBase = sender.base;
            readyChallenge = challenge;
            readyState = data.nonce;
            readyFrame = sender;
            status.textContent = config.text.ready;
            control.href = controlURL(readyBase);
            control.classList.remove('hidden');
            open.textContent = config.text.open;
            if (autoOpen) { autoOpen = false; openControl(readyBase, readyChallenge, readyState); }
        }
        function probe() {
            clearFrames();
            var current = generation;
            for (var port = 47800; port < 47810; port++) {
                var base = 'http://127.0.0.1:' + port;
                var frame = doc.createElement('iframe');
                frame.hidden = true;
                frame.title = 'Local Encoder discovery';
                frame.referrerPolicy = 'no-referrer';
                frame.setAttribute('sandbox', 'allow-scripts allow-same-origin');
                frame.setAttribute('allow', 'local-network-access');
                frames.push({ base: base, frame: frame });
                frame.onload = (function (target, origin) {
                    return function () {
                        if (current === generation && target.contentWindow && frames.some(function (item) { return item.frame === target; })) {
                            target.contentWindow.postMessage({ type: 'avideo-local-encoder:discover', nonce: nonce }, origin);
                        }
                    };
                })(frame, base);
                frame.src = base + '/bridge';
                doc.body.appendChild(frame);
            }
        }
        function detect(duration, repeat) {
            stop();
            readyBase = '';
            control.classList.add('hidden');
            open.href = nativeLink;
            if (!win.crypto || !win.crypto.getRandomValues) { status.textContent = config.text.unknown; return; }
            var random = new Uint8Array(16);
            win.crypto.getRandomValues(random);
            nonce = Array.from(random, function (n) { return n.toString(16).padStart(2, '0'); }).join('');
            probe();
            var current = generation;
            function again() {
                if (current !== generation) return;
                probe();
                pollTimer = win.setTimeout(again, 2500);
            }
            if (repeat) pollTimer = win.setTimeout(again, 2500);
            timer = win.setTimeout(function () {
                if (current !== generation) return;
                stop();
                status.textContent = config.text.unknown;
            }, duration);
        }
        function openClick() {
            autoOpen = true;
            status.textContent = config.text.opening;
            detect(45000, true);
            // Keep the native href and the user's click: browsers may require confirmation.
        }
        function controlClick(event) {
            if (!readyBase) return;
            event.preventDefault();
            if (pendingLogin) return;
            openControl(readyBase, readyChallenge, readyState);
        }
        function checkClick() { status.textContent = config.text.checking; detect(5000, false); }
        function changed() {
            var params = new URLSearchParams(win.location.hash.slice(1));
            if (params.has('open') || params.has('url')) init(config, win, doc);
        }
        function revived(event) { if (event.persisted) init(config, win, doc); }
        function pageHidden() { stop(); }
        function cleanup() {
            stop();
            win.removeEventListener('message', receive);
            win.removeEventListener('hashchange', changed);
            win.removeEventListener('pagehide', pageHidden);
            win.removeEventListener('pageshow', revived);
            open.removeEventListener('click', openClick);
            control.removeEventListener('click', controlClick);
            check.removeEventListener('click', checkClick);
        }
        panel.avideoLauncherCleanup = cleanup;
        open.addEventListener('click', openClick);
        control.addEventListener('click', controlClick);
        check.addEventListener('click', checkClick);
        win.addEventListener('message', receive);
        win.addEventListener('hashchange', changed);
        win.addEventListener('pagehide', pageHidden);
        win.addEventListener('pageshow', revived);
        if (/Android|iPhone|iPad|iPod/i.test(win.navigator.userAgent) || (win.navigator.platform === 'MacIntel' && win.navigator.maxTouchPoints > 1)) {
            open.hidden = true;
            check.hidden = true;
            open.classList.add('hidden');
            check.classList.add('hidden');
            status.textContent = config.text.computer;
            return;
        }
        if (invalidRequest) return;
        status.textContent = config.text.checking;
        detect(5000, false);
    }
    var api = { address: address, context: context, link: link, init: init };
    if (typeof module !== 'undefined') module.exports = api;
    root.AVideoLocalEncoderLauncher = api;
    if (root.document) root.document.addEventListener('DOMContentLoaded', function () {
        var config = root.document.getElementById('local-encoder-config');
        if (config) init(JSON.parse(config.textContent));
    });
})(typeof window !== 'undefined' ? window : globalThis);
