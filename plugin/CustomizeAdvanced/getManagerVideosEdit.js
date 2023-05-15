$("#doNotShowAdsOnThisVideo").prop("checked", false);
$("#doNotShowAdsOnThisChannel").prop("checked", false);
$("#redirectVideoCode").val(0);
$("#redirectVideoURL").val('');
$("#inputMetaDescription").val('');
$("#inputShortSummary").val('');
if (typeof row.externalOptions !== 'undefined' && row.externalOptions) {
    
    var json = typeof row.externalOptions == 'string' ? JSON.parse(row.externalOptions):row.externalOptions;
    
    if(json.doNotShowAdsOnThisVideo){
        $("#doNotShowAdsOnThisVideo").prop("checked", true);
    }
    if(!empty(json.redirectVideo)){
        $("#redirectVideoCode").val(json.redirectVideo.code);
        $("#redirectVideoURL").val(json.redirectVideo.url);
    }else{
        $("#redirectVideoCode").val(0);
        $("#redirectVideoURL").val('');
    }
    if(!empty(json.SEO)){
        $("#inputMetaDescription").val(json.SEO.MetaDescription);
        $("#inputShortSummary").val(json.SEO.ShortSummary);
    }else{
        $("#inputMetaDescription").val('');
        $("#inputShortSummary").val('');
    }
    $("#inputMetaDescription, #inputShortSummary").trigger('keyup');
}

if (typeof row.userExternalOptions !== 'undefined' && row.userExternalOptions) {
    
    try {
        var json = json_decode(row.userExternalOptions);

        if(json.doNotShowAdsOnThisChannel){
            $("#doNotShowAdsOnThisChannel").prop("checked", true);
        }
    } catch (e) {
        
    }

}