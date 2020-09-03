<script>
    var lastLiveStatus;
    var eventMethod = window.addEventListener
            ? "addEventListener"
            : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod === "attachEvent"
            ? "onmessage"
            : "message";
    eventer(messageEvent, function (e) {
        if(typeof e.data.isLive !== 'undefined'){
            if(lastLiveStatus !== e.data.isLive){
                console.log("YPTMeetScript live status changed");
                if(lastLiveStatus){
                    event_on_live();
                }else{
                    event_on_liveStop();
                } 
                event_on_liveStatusChange();
            }
            lastLiveStatus = e.data.isLive;           
        }else if(typeof e.data.YPTisReady !== 'undefined'){
            console.log("YPTMeetScript is loaded");
        }else if(typeof e.data.conferenceIsReady !== 'undefined'){   
            console.log("YPTMeetScript conference is ready");
            event_on_meetReady();
        }
    });
</script>