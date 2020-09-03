var jitsiIsLive = false;
function setLivestreamURL() {
    var selector = "input[name='streamId']";
    if (typeof $(selector) !== 'undefined' && $(selector).length && getRTMPLink && $(selector).val() !== getRTMPLink) {
        $(selector).closest('form').hide();
        $(selector).val('');
        var input = document.querySelector(selector);
        var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        nativeInputValueSetter.call(input, '$getRTMPLink');
        var ev2 = new Event('input', {bubbles: true});
        input.dispatchEvent(ev2);
    }
}
function isJitsiLive() {
    jitsiIsLive = $(".circular-label.stream").is(":visible");
    window.parent.postMessage({"isLive": jitsiIsLive}, "*");
}

function startYPTScripts() {
    if (window.jQuery) {
        isJitsiLive();
        setInterval(function () {
            isJitsiLive();
        }, 1000);

        setLivestreamURL();
        setInterval(function () {
            setLivestreamURL();
        }, 500);
        var eventMethod = window.addEventListener
                ? "addEventListener"
                : "attachEvent";
        var eventer = window[eventMethod];
        var messageEvent = eventMethod === "attachEvent"
                ? "onmessage"
                : "message";
        eventer(messageEvent, function (e) {
            if (typeof e.data.hideElement !== 'undefined') {
                $(e.data.hideElement).hide();
            }
        });
    } else {
        setTimeout(function () {
            startYPTScripts();
        }, 500);
    }
}
