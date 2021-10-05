define([
    'jquery',
    'var/emojione',
    'var/emojioneSupportMode',
    'function/getTemplate'
],
function($, emojione, emojioneSupportMode, getTemplate) {
    return function(str, template, clear) {
        return str.replace(/:?\+?[\w_\-]+:?/g, function(shortname) {
            shortname = ":" + shortname.replace(/:$/,'').replace(/^:/,'') + ":";
            var unicode = emojione.emojioneList[shortname];
            if (unicode) {
                if (emojioneSupportMode > 4) {
                    return getTemplate(template, unicode, shortname);
                } else {
                    if (emojioneSupportMode > 3) unicode = unicode.unicode;
                    return getTemplate(template, unicode[unicode.length-1], shortname);
                }
            }
            return clear ? '' : shortname;
        });
    };
});
