define([], function() {
    var saveSelection, restoreSelection;
    if (window.getSelection && document.createRange) {
        saveSelection = function(el) {
            var sel = window.getSelection && window.getSelection();
            if (sel && sel.rangeCount > 0) {
                return sel.getRangeAt(0);
            }
            return null;
        };

        restoreSelection = function(el, sel) {
            var range = document.createRange();
            range.setStart(sel.startContainer, sel.startOffset);
            range.setEnd(sel.endContainer, sel.endOffset)

            sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    } else if (document.selection && document.body.createTextRange) {
        saveSelection = function(el) {
            return document.selection.createRange();
        };

        restoreSelection = function(el, sel) {
            var textRange = document.body.createTextRange();
            textRange.moveToElementText(el);
            textRange.setStart(sel.startContainer, sel.startOffset);
            textRange.setEnd(sel.endContainer, sel.endOffset);
            textRange.select();
        };
    }
});
