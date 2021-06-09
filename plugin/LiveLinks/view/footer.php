<script>
    var liveLinkAppsCalled = {};
    function liveLinkApps($liveLi, className, live_starts) {
        if(new Date(live_starts).getTime()<new Date().getTime()){
            return false;
        }
        if(typeof liveLinkAppsCalled[className] !== 'undefined'){ // do not call it twice
            //return false;
        }
        //console.log('liveLinkApps', $liveLi, className, live_starts, $liveLi.find('.liveNow'));
        liveLinkAppsCalled[className] = live_starts;
        $liveLi.find('.liveNow').html("<?php echo __('Starts in'); ?> <span class='Timer_"+className+"'>"+live_starts+"<span>");
        $liveLi.find('.liveNow').attr("class", 'label label-primary liveFuture');
        //console.log('liveLinkApps', '.'+className+' '+live_starts);
        startTimerToDate(live_starts, '.Timer_'+className, false);
        return $liveLi;
    }
</script>