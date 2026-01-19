define([
    'var/slice',
    'function/trigger',
    'prototype/var/EmojioneArea'
],
function(slice, trigger, EmojioneArea) {
    EmojioneArea.prototype.trigger = function() {
        var args = slice.call(arguments),
            call_args = [this].concat(args.slice(0,1));
        call_args.push(args.slice(1));
        return trigger.apply(this, call_args);
    };
});