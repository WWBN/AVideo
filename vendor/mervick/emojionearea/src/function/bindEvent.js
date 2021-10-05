define([
    'jquery',
    'var/slice',
    'var/possibleEvents',
    'function/trigger',
],
function($, slice, possibleEvents, trigger) {
    return function(self, event) {
        event = event.replace(/^@/, '');
        var id = self.id;
        if (possibleEvents[id][event]) {
            $.each(possibleEvents[id][event], function(i, ev) {
                // ev[0] = element
                // ev[1] = event
                // ev[2] = target
                $.each($.isArray(ev[0]) ? ev[0] : [ev[0]], function(i, el) {
                    $(el).on(ev[1], function() {
                        var args = slice.call(arguments),
                            target = $.isFunction(ev[2]) ? ev[2].apply(self, [event].concat(args)) : ev[2];
                        if (target) {
                            trigger(self, event, [target].concat(args));
                        }
                    });
                });
            });
            possibleEvents[id][event] = null;
        }
    }
});