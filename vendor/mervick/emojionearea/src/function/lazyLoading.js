define([
    'jquery'
],
function($) {
    return function() {
        var self = this;
        if (!self.sprite && self.lasyEmoji[0] && self.lasyEmoji.eq(0).is(".lazy-emoji")) {
            var pickerTop = self.picker.offset().top,
                pickerBottom = pickerTop + self.picker.height() + 20;

            self.lasyEmoji.each(function() {
                var e = $(this), top = e.offset().top;

                if (top > pickerTop && top < pickerBottom) {
                    e.attr("src", e.data("src")).removeClass("lazy-emoji");
                }

                if (top > pickerBottom) {
                    return false;
                }
            });
            self.lasyEmoji = self.lasyEmoji.filter(".lazy-emoji");
        }
    };
});
