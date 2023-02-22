$("#releaseDate").val('datetime');
$("#releaseDate").trigger('change');
$("#releaseDateTime").val('');
if (typeof row.externalOptions !== 'undefined' && row.externalOptions) {
    
    var json = JSON.parse(row.externalOptions);
    
    if(!empty(json.releaseDateTime)){
        $("#releaseDateTime").val(convertDateFromTimezoneToLocal(json.releaseDateTime, json.releaseDateTimeZone));
    }
}