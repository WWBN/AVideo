$("#doNotShowAdsOnThisVideo").prop("checked", false);
if (typeof row.externalOptions !== 'undefined' && row.externalOptions) {
    
    var json = JSON.parse(row.externalOptions);
    
    if(json.doNotShowAdsOnThisVideo){
        $("#doNotShowAdsOnThisVideo").prop("checked", true);
    }
}