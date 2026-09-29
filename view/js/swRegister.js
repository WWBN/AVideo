// Register service worker to control making site work offline

function swapOriginsFromDomains(url1, url2) {
    let domain1 = (new URL(url1));
    let domain2 = (new URL(url2));
    return url1.replace(domain1.origin, domain2.origin);
}

function getQueryParamFromScriptTag(param) {
    const script = document.currentScript;
    if (!script) {
        return null;
    }

    const src = script.getAttribute('src');
    if (!src) {
        return null;
    }

    const queryStringIndex = src.indexOf('?');
    if (queryStringIndex === -1) {
        return null;
    }

    const queryString = src.substring(queryStringIndex);
    const urlParams = new URLSearchParams(queryString);

    return urlParams.get(param);
}

function serviceWorkerRegister() {
    //console.log('Service Worker called');
    if (typeof webSiteRootURL == 'undefined') {
        webSiteRootURL = getQueryParamFromScriptTag('webSiteRootURL');

        const urlRegex = /^(https?:\/\/)?(localhost\b|[\w\-]+(\.[\w\-]+)+)[:/#?]?.*$/;

        if (typeof webSiteRootURL == 'undefined' || !urlRegex.test(webSiteRootURL)) {
            console.log('Service Worker NOT Registered', webSiteRootURL);
            setTimeout(function () {
                serviceWorkerRegister();
            }, 100);
            return false;
        }
    }
    if ('serviceWorker' in navigator) {
        try {
            var newURL = swapOriginsFromDomains(webSiteRootURL, window.location.href);
            // WebView worker navigations can lose the app User-Agent. Remove only
            // this site's registration, preserving other applications on the origin.
            if (/AVideoMobileApp/.test(navigator.userAgent)) {
                if (typeof navigator.serviceWorker.getRegistrations === 'function') {
                    navigator.serviceWorker.getRegistrations().then(function (registrations) {
                        return Promise.all(registrations.map(function (registration) {
                            if (registration.scope === newURL) {
                                return registration.unregister();
                            }
                        }));
                    }).catch(function (e) {
                        console.log('serviceWorkerRegister cleanup ERROR', e);
                    });
                }
                return false;
            }
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
serviceWorkerRegister();
