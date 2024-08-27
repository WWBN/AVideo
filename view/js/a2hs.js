var deferredPrompt;
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
    eventer('beforeinstallprompt', (e) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault();
        $('.A2HSInstall').show();
        // Stash the event so it can be triggered later.
        deferredPrompt = e;
        var beforeinstallprompt = Cookies.get('beforeinstallprompt');
        if (!empty(beforeinstallprompt)) {
            return false;
        }
        var msg = "<a href='#' onclick='A2HSInstall();'><img src='" + $('[rel="apple-touch-icon"]').attr('href') + "' class='img img-responsive pull-left' style='max-width: 20px; margin-right:5px;'> " + __('Add To Home Screen') + "</a>";
        var options = { text: msg, hideAfter: 20000 };
        $.toast(options);
        Cookies.set('beforeinstallprompt', 1, {
            path: '/',
            expires: 365
        });
    });
});