define([
    'jquery',
    'function/trigger',
    'prototype/var/EmojioneArea'
],
function($, trigger, EmojioneArea) {
    EmojioneArea.prototype.enable = function () {
        var self = this;
        var next = function () {
            self.disabled = false;
            self.editor.prop('contenteditable', true);
            self.button.show();
            var editor = self[(self.standalone) ? "button" : "editor"];
            editor.parent().removeClass('emojionearea-disable');
            trigger(self, 'enabled', [editor]);
        };
        self.isReady ? next() : self.on("ready", next);
        return self;
    }
});