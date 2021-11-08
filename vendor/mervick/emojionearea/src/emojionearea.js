define([
    'jquery',
    'prototype/var/EmojioneArea',
    'function/getDefaultOptions',
    'function/htmlFromText',
    'var/blankImg',
    'var/emojioneSupportMode',
    'function/loadEmojione',
    'function/getOptions',

    'prototype/on',
    'prototype/off',
    'prototype/trigger',
    'prototype/setFocus',
    'prototype/setText',
    'prototype/getText',
    'prototype/showPicker',
    'prototype/hidePicker',
    'prototype/enable',
    'prototype/disable'
],
function($, EmojioneArea, getDefaultOptions, htmlFromText, blankImg, emojioneSupportMode, loadEmojione, getOptions) {
    $.fn.emojioneArea = function(options) {
        return this.each(function() {
            if (!!this.emojioneArea) return this.emojioneArea;
            $.data(this, 'emojioneArea', this.emojioneArea = new EmojioneArea($(this), options));
            return this.emojioneArea;
        });
    };

    $.fn.emojioneArea.defaults = getDefaultOptions();

    $.fn.emojioneAreaText = function(options) {
        options = getOptions(options);

        var self = this, pseudoSelf = {
            shortnames: (options && typeof options.shortnames !== 'undefined' ? options.shortnames : true),
            emojiTemplate: '<img alt="{alt}" class="emojione' + (options && options.sprite && emojioneSupportMode < 3 ? '-{uni}" src="' + blankImg : 'emoji" src="{img}') + '" crossorigin />'
        };

        loadEmojione(options);
        emojioneReady(function() {
            self.each(function() {
                var $this = $(this);
                if (!$this.hasClass('emojionearea-text')) {
                    $this.addClass('emojionearea-text').html(htmlFromText(($this.is('TEXTAREA') || $this.is('INPUT') ? $this.val() : $this.text()), pseudoSelf));
                }
                return $this;
            });
        });

        return this;
    };
});
