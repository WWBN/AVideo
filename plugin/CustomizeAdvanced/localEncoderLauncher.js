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
        var pollTimer = null;
        var generation = 0;
        var readyBase = '';
        var nonce = '';
        open.href = nativeLink;
        open.removeAttribute('aria-disabled');
        open.classList.remove('disabled');
        function clearFrames() {
            frames.forEach(function (item) { item.frame.remove(); });
            frames = [];
        }
        function stop() {
            win.clearTimeout(timer);
            win.clearTimeout(pollTimer);
            clearFrames();
            generation++;
        }
        function controlURL(base) { return base + '/#avideo=' + encodeURIComponent(nativeLink); }
        function receive(event) {
            var data = event.data;
            if (!data || data.type !== 'avideo-local-encoder:ready' || data.protocol !== 1 || data.nonce !== nonce) return;
            var sender = frames.find(function (item) { return event.origin === item.base && event.source === item.frame.contentWindow; });
            if (!sender) return;
            readyBase = sender.base;
            stop();
            status.textContent = config.text.ready;
            control.href = controlURL(readyBase);
            control.classList.remove('hidden');
            open.textContent = config.text.open;
            if (autoOpen) { autoOpen = false; win.location.replace(control.href); }
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
            autoOpen = false;
            status.textContent = config.text.opening;
            detect(45000, true);
            // Keep the native href and the user's click: browsers may require confirmation.
        }
        function checkClick() { status.textContent = config.text.checking; detect(5000, false); }
        function changed() {
            var params = new URLSearchParams(win.location.hash.slice(1));
            if (params.has('open') || params.has('url')) init(config, win, doc);
        }
        function revived(event) { if (event.persisted) init(config, win, doc); }
        function cleanup() {
            stop();
            win.removeEventListener('message', receive);
            win.removeEventListener('hashchange', changed);
            win.removeEventListener('pagehide', stop);
            win.removeEventListener('pageshow', revived);
            open.removeEventListener('click', openClick);
            check.removeEventListener('click', checkClick);
        }
        panel.avideoLauncherCleanup = cleanup;
        open.addEventListener('click', openClick);
        check.addEventListener('click', checkClick);
        win.addEventListener('message', receive);
        win.addEventListener('hashchange', changed);
        win.addEventListener('pagehide', stop);
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
