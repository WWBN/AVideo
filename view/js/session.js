/**
 * AVideoSession – single source of truth for the PHP session ID in JS.
 *
 * Why PHPSESSID is exposed to JS:
 *   Cross-domain <iframe> pages cannot rely on the PHPSESSID cookie being
 *   forwarded by the browser, so the session ID is passed as a GET parameter
 *   (?PHPSESSID=...) for endpoints that explicitly support it (view counting,
 *   CAPTCHA). The browser's same-origin policy blocks cross-origin reads of
 *   phpsessionid.json.php, so only same-origin JS can fetch it.
 *
 * Self-correction after session regeneration (e.g. after login):
 *   videoAddViewCount.json.php always echoes the current server-side
 *   session_id() in its JSON response. addView.js calls AVideoSession.set()
 *   on each successful response, so the value stays in sync automatically
 *   without any extra round-trip.
 *
 * API:
 *   AVideoSession.get()     – returns current session ID (same as PHPSESSID)
 *   AVideoSession.set(id)   – update from any server response (updates PHPSESSID too)
 *   AVideoSession.load()    – re-fetch from server (called once on DOMContentLoaded)
 *
 * Legacy global `PHPSESSID` is kept for backward compatibility with all
 * existing callers (addView.js, main.js, etc.) that read it directly.
 */
var PHPSESSID = null; // legacy global – always kept in sync by AVideoSession

var AVideoSession = (function () {
    function get() {
        return PHPSESSID;
    }

    function set(id) {
        if (id && id !== PHPSESSID) {
            PHPSESSID = id; // sync legacy global
        }
    }

    function load() {
        fetch(webSiteRootURL + 'objects/phpsessionid.json.php', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) { set(data.phpsessid); })
            .catch(function (error) { console.error('AVideoSession.load error:', error); });
    }

    // Load as fast as possible; subsequent updates come from server responses.
    window.addEventListener('DOMContentLoaded', load);

    return { get: get, set: set, load: load };
})();
