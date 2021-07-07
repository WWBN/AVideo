define([
    'var/emojione',
    'var/uniRegexp',
    'var/emojioneSupportMode',
    'function/getTemplate'
],
function(emojione, uniRegexp, emojioneSupportMode, getTemplate) {
    return function(str, template) {
        return str.replace(uniRegexp, function(unicodeChar) {
            var map = emojione[(emojioneSupportMode === 0 ? 'jsecapeMap' : 'jsEscapeMap')];
            if (typeof unicodeChar !== 'undefined' && unicodeChar in map) {
                return getTemplate(template, map[unicodeChar], emojione.toShort(unicodeChar));
            }
            return unicodeChar;
        });
    }
});
