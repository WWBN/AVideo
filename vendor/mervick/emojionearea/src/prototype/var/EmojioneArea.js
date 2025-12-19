define([
    'var/unique',
    'var/eventStorage',
    'var/possibleEvents',
    'function/emojioneReady',
    'function/init',
    'function/loadEmojione'
],
function(unique, eventStorage, possibleEvents, emojioneReady, init, loadEmojione) {
    return function(element, options) {
        var self = this;
        loadEmojione(options);
        eventStorage[self.id = ++unique] = {};
        possibleEvents[self.id] = {};
        emojioneReady(function() {
            init(self, element, options);
        });
    };
});