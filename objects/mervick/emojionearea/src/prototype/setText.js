define([
    'jquery',
    'function/emojioneReady',
    'function/htmlFromText',
    'function/textFromHtml',
    'function/trigger',
    'function/calcButtonPosition',
    'prototype/var/EmojioneArea'
],
function($, emojioneReady, htmlFromText, trigger, calcButtonPosition, EmojioneArea) {
    EmojioneArea.prototype.setText = function (str) {
        var self = this;
        emojioneReady(function () {
            self.editor.html(htmlFromText(str, self));
            self.content = self.editor.html();
            trigger(self, 'change', [self.editor]);
            calcButtonPosition.apply(self);
        });
        return self;
    }
});