// Use blockAdBlock directly instead of fetching Google IMA SDK.
// Firefox's Enhanced Tracking Protection (ETP) blocks imasdk.googleapis.com by default,
// causing false positives even when no ad blocker extension is installed.
function checkScriptBlocking() {
    if (typeof blockAdBlock !== 'undefined') {
        blockAdBlock.onDetected(adBlockDetected);
    }
}
document.addEventListener('DOMContentLoaded', checkScriptBlocking);
