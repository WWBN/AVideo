define([
    'jquery',
    'var/eventStorage',
],
function($, eventStorage) {
    return function(self, event, args) {
        var result = true, j = 1;
        if (event) {
            event = event.toLowerCase();
            do {
                var _event = j==1 ? '@' + event : event;
                if (eventStorage[self.id][_event] && eventStorage[self.id][_event].length) {
                    $.each(eventStorage[self.id][_event], function (i, fn) {
                        return result = fn.apply(self, args|| []) !== false;
                    });
                }
            } while (result && !!j--);
        }
        return result;
    }
});