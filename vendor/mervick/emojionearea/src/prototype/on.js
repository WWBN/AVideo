define([
    'jquery',
    'var/eventStorage',
    'function/bindEvent',
    'prototype/var/EmojioneArea'
],
function($, eventStorage, bindEvent, EmojioneArea) {
    EmojioneArea.prototype.on = function(events, handler) {
        if (events && $.isFunction(handler)) {
            var self = this;
            $.each(events.toLowerCase().split(' '), function(i, event) {
                bindEvent(self, event);
                (eventStorage[self.id][event] || (eventStorage[self.id][event] = [])).push(handler);
            });
        }
        return this;
    };
});