<script>
    var eventMethod = window.addEventListener
            ? "addEventListener"
            : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod === "attachEvent"
            ? "onmessage"
            : "message";
    eventer(messageEvent, function (e) {
        if(typeof e.data.isLive !== 'undefined'){
            if(e.data.isLive){
                event_on_live();
            }else{
                event_on_liveStop();
            }            
        }else if(typeof e.data.YPTisReady !== 'undefined'){
            console.log("YPTMeetScript is loaded");
        }else if(typeof e.data.conferenceIsReady !== 'undefined'){   
            event_on_meetReady();
        }
    });
</script>