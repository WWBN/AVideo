var sourcesForAdsInterval = setInterval(function(){
    setSourcesForAds();
},200);

function setSourcesForAds(){
    if(typeof player ==='undefined'){
        return false;
    }
    if(typeof player.currentSources !== 'function'){
        if(typeof player.currentSources === 'object'){
            console.log('currentSources changed to function');
            var sourcesForAds = player.currentSources;
            player.currentSources = function(){return sourcesForAds;};
            console.log('currentSources', player.currentSources);
        }
    }else{
        clearTimeout(sourcesForAdsInterval);
        setTimeout(function(){
            setSourcesForAds();
        },1000);
    }
}