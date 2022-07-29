$("#doNotShowAdsOnThisVideo").prop("checked", false);
$("#redirectVideoCode").val(0);
$("#redirectVideoURL").val('');
if (typeof row.externalOptions !== 'undefined' && row.externalOptions) {
    
    var json = JSON.parse(row.externalOptions);
    
    if(json.doNotShowAdsOnThisVideo){
        $("#doNotShowAdsOnThisVideo").prop("checked", true);
    }
    if(!empty(json.redirectVideo)){
        $("#redirectVideoCode").val(json.redirectVideo.code);
        $("#redirectVideoURL").val(json.redirectVideo.url);
    }
}