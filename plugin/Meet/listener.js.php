<script>
    var jitsiIsLive = false;

    var eventMethod = window.addEventListener
            ? "addEventListener"
            : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod === "attachEvent"
            ? "onmessage"
            : "message";
    eventer(messageEvent, function (e) {
        if(typeof e.data.isLive !== 'undefined'){
            jitsiIsLive = e.data.isLive;
        }else if(typeof e.data.YPTisReady !== 'undefined'){
            document.querySelector("iframe").contentWindow.postMessage({hideElement: ".watermark, .toolbox-button-wth-dialog"},"*");
        }
    });
</script>