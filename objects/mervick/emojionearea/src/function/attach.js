define([
    'jquery',
    'var/slice',
    'var/possibleEvents'
],
function($, slice, possibleEvents) {
    return function(self, element, events, target) {
        target = target || function (event, callerEvent) { return $(callerEvent.currentTarget) };
        $.each(events, function(event, link) {
            event = $.isArray(events) ? link : event;
            (possibleEvents[self.id][link] || (possibleEvents[self.id][link] = []))
                .push([element, event, target]);
        });
    }
});