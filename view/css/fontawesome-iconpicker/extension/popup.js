/*!
 * Font Awesome Icon Picker
 * https://itsjavi.com/fontawesome-iconpicker/
 *
 * Originally written by (c) 2016 Javi Aguilar
 * Licensed under the MIT License
 * https://github.com/itsjavi/fontawesome-iconpicker/blob/master/LICENSE
 *
 */
var FA_CSS_RULES = null;
function _selectText(element) {
    var doc = window.document, range = null;

    if (doc.body.createTextRange) { // ms
        range = doc.body.createTextRange();
        range.moveToElementText(element);
        range.select();
    } else if (window.getSelection) { // moz, opera, webkit
        var selection = window.getSelection();
        range = doc.createRange();
        range.selectNodeContents(element);
        selection.removeAllRanges();
        selection.addRange(range);
    }
}
function _getStyleRuleValue(style, selector) {
    for (var j = 0, k = FA_CSS_RULES.length; j < k; j++) {
        var rule = FA_CSS_RULES[j];
        if (rule.selectorText) {
            var ruleSplit = rule.selectorText.replace(/ /g, '').split(',');
            if (ruleSplit.length > 1) {
                //console.log(ruleSplit);
            }
        } else {
            ruleSplit = [];
        }
        if (rule.selectorText && ruleSplit.indexOf(selector) !== -1) {
            return rule.style[style];
        }
    }
    return null;
}
function getRemoteCssRules(stylesheet_uri) {
    $.ajax(stylesheet_uri).done(function (data) {
        var style_tag = document.createElement('style');
        style_tag.id = 'temp_fa_sheet';
        style_tag.appendChild(document.createTextNode(data));
        document.head.appendChild(style_tag);
        FA_CSS_RULES = document.styleSheets[4].cssRules;
        $('#temp_fa_sheet').remove();
    });
}

$(function () {
    $('.iconpicker').iconpicker({
        showFooter: true,
        templates: {
            buttons: '<div></div>',
            search: '<input type="search" class="form-control iconpicker-search" placeholder="Type to filter" />',
            footer: '<div class="popover-footer"><p class="icn"><i class="fa fa-3x fa-fw"></i></p><p class="txt"></p></div>'
        }
    }).on('iconpickerSelected iconpickerUpdated', function (e) {
        if (!e.iconpickerValue) {
            return;
        }
        var $footer = e.iconpickerInstance.popover.find('.popover-footer').show();
        console.log(e.iconpickerValue);
        var _icnChar = _getStyleRuleValue('content', '.' + e.iconpickerValue + '::before');
        console.log(_icnChar);
        $footer.find('.icn .fa').html(_icnChar.replace(/"/g, ''));
        var _txt = $footer.find('.txt')
            .html(e.iconpickerValue +
                '<br>' + '<small>&lt;i class="fa ' + e.iconpickerValue + '"&gt;&lt;/i&gt;</small>');
        _selectText(_txt.find('small').get(0));
    }).data('iconpicker');
    getRemoteCssRules('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
});