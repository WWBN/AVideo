let deferredPrompt;

// Register service worker to control making site work offline
function serviceWorkerRegister() {
    //console.log('Service Worker called');
    if (typeof webSiteRootURL == 'undefined') {
        setTimeout(function () {
            //console.log('Service Worker NOT Registered');
            serviceWorkerRegister();
        }, 1000);
        return false;
    }
    if ('serviceWorker' in navigator) {
        var newURL = swapOriginsFromDomains(webSiteRootURL, window.location.href);
        //console.log('Service Worker trying to Register', newURL, window.location.href, webSiteRootURL);
        try {
            navigator.serviceWorker
                    .register(newURL + 'sw.js?' + Math.random())
                    .then(() => {
                        console.log('Service Worker Registered');
                    });
        } catch (e) {
            console.log('serviceWorkerRegister ERROR', e, window.location.href, webSiteRootURL);
        }
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

function swapOriginsFromDomains(url1, url2) {
    let domain1 = (new URL(url1));
    let domain2 = (new URL(url2));
    return url1.replace(domain1.origin, domain2.origin);
}
$(document).ready(function () {
    eventer('beforeinstallprompt', (e) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault();
        $('.A2HSInstall').show();
        // Stash the event so it can be triggered later.
        deferredPrompt = e;
        var beforeinstallprompt = Cookies.get('beforeinstallprompt');
        if (beforeinstallprompt) {
            return false;
        }
        var msg = "<a href='#' onclick='A2HSInstall();'><img src='" + $('[rel="apple-touch-icon"]').attr('href') + "' class='img img-responsive pull-left' style='max-width: 20px; margin-right:5px;'> Add To Home Screen </a>";
        var options = {text: msg, hideAfter: 20000};
        $.toast(options);
        Cookies.set('beforeinstallprompt', 1, {
            path: '/',
            expires: 365
        });
    });
    serviceWorkerRegister();
});