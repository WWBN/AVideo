define([
    'function/updateRecent',
    'function/getRecent'
],
function(updateRecent, getRecent) {
    return function(self, emoji) {
        var recent = getRecent();
        var emojis = recent.split("|");

        var index = emojis.indexOf(emoji);
        if (index !== -1) {
            emojis.splice(index, 1);
        }
        emojis.unshift(emoji);

        if (emojis.length > 9) {
            emojis.pop();
        }

        localStorage.setItem("recent_emojis", emojis.join("|"));

        updateRecent(self);
    };
});