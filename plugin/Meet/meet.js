// this is the script that will be executed in the iframe on Jitsi side
var jitsiIsLive = false;
function setLivestreamURL() {
    var selector = "input[name='streamId']";
    if (typeof $(selector) !== 'undefined' && $(selector).length && getRTMPLink && $(selector).val() !== getRTMPLink) {
        $(selector).closest('form').hide();
        $(selector).val('');
        var input = document.querySelector(selector);
        var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        nativeInputValueSetter.call(input, getRTMPLink);
        var ev2 = new Event('input', {bubbles: true});
        input.dispatchEvent(ev2);
    }
}
function isJitsiLive() {
    jitsiIsLive = $(".circular-label.stream").is(":visible");
    window.parent.postMessage({"isLive": jitsiIsLive}, "*");
    if (jitsiIsLive) {
        $(".showOnLive").show();
        $(".hideOnLive").hide();
    } else {
        $(".showOnLive").hide();
        $(".hideOnLive").show();
    }
}

function isConferenceReady() {
    conferenceIsReady = $("#videoconference_page").is(":visible");
    if (conferenceIsReady) {
        window.parent.postMessage({"conferenceIsReady": true}, "*");
    } else {
        setTimeout(function () {
            isConferenceReady();
        }, 1000);
    }
}

function startYPTScripts() {
    if (window.jQuery) {
        console.log("startYPTScripts started");
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
            } else if (typeof e.data.append !== 'undefined') {
                $(e.data.append.parentSelector).append(e.data.append.html);
            } else if (typeof e.data.prepend !== 'undefined') {
                $(e.data.prepend.parentSelector).prepend(e.data.prepend.html);
            }
        });

        window.parent.postMessage({"YPTisReady": true}, "*");
        isConferenceReady();
        
        fixHREF();
        
    } else {
        setTimeout(function () {
            startYPTScripts();
        }, 500);
    }
}

function aVideoMeetStartRecording(RTMPLink, dropURL) {
    window.parent.postMessage({"aVideoMeetStartRecording": {RTMPLink:RTMPLink, dropURL:dropURL}}, "*");
}

function aVideoMeetStopRecording(dropURL) {
    window.parent.postMessage({"aVideoMeetStopRecording": {dropURL:dropURL}}, "*");
}

function fixHREF(){
    if($('a[href="https://jitsi.org"]').length){
        $('a[href="https://jitsi.org"]').attr("href", webSiteRootURL);
    }else{
        setTimeout(function () {
            fixHREF();
        }, 500);
    }
}

startYPTScripts();