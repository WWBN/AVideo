
// Register service worker to control making site work offline
function serviceWorkerRegister() {
    if (typeof webSiteRootURL == 'undefined') {
        setTimeout(function () {
            serviceWorkerRegister();
        }, 1000);
        return false;
    }
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker
                .register(webSiteRootURL + 'sw.js?' + Math.random())
                .then(() => {
                    console.log('Service Worker Registered');
                });
    }
}

function A2HSInstall() {
    // Show the prompt
    deferredPrompt.prompt();
    // Wait for the user to respond to the prompt
    deferredPrompt.userChoice.then((choiceResult) => {
        if (choiceResult.outcome === 'accepted') {
            console.log('User accepted the A2HS prompt');
        } else {
            console.log('User dismissed the A2HS prompt');
        }
        deferredPrompt = null;
    });
}

$(document).ready(function () {
    serviceWorkerRegister();
});