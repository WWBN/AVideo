define([
    'var/emojione',
    'var/emojioneSupportMode'
],
function(emojione, emojioneSupportMode) {
    return function(template, unicode, shortname) {
        var imageType = emojione.imageType, imagePath;
        if (imageType=='svg'){
            imagePath = emojione.imagePathSVG;
        } else {
            imagePath = emojione.imagePathPNG;
        }
        var friendlyName = '';
        if (shortname) {
            friendlyName = shortname.substr(1, shortname.length - 2).replace(/_/g, ' ').replace(/\w\S*/g, function(txt) { return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
        }
        var fname = '';
        if (unicode.uc_base && emojioneSupportMode > 4) {
            fname = unicode.uc_base;
            unicode = unicode.uc_output.toUpperCase();
        } else {
            fname = unicode;
        }
        template = template.replace('{name}', shortname || '')
            .replace('{friendlyName}', friendlyName)
            .replace('{img}', imagePath + (emojioneSupportMode < 2 ? fname.toUpperCase() : fname) + '.' + imageType)
            .replace('{uni}', unicode);

        if(shortname) {
            template = template.replace('{alt}', emojione.shortnameToUnicode(shortname));
        } else {
            template = template.replace('{alt}', emojione.convert(unicode));
        }

        return template;
    };
});
