define([
    'function/isObject'
],
function(isObject) {
    return function(emojione) {
        var version;
        if (emojione.cacheBustParam) {
            version = emojione.cacheBustParam;
            if (!isObject(emojione['jsEscapeMap'])) return '1.5.2';
            if (version === "?v=1.2.4") return '2.0.0';
            if (version === "?v=2.0.1") return '2.1.0'; // v2.0.1 || v2.1.0
            if (version === "?v=2.1.1") return '2.1.1';
            if (version === "?v=2.1.2") return '2.1.2';
            if (version === "?v=2.1.3") return '2.1.3';
            if (version === "?v=2.1.4") return '2.1.4';
            if (version === "?v=2.2.7") return '2.2.7';
            return '2.2.7';
        } else {
            return emojione.emojiVersion;
        }
    };
});
