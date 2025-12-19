define([
    'jquery',
    'var/slice',
    'function/selector',
    'function/isObject'
],
function($, slice, selector, isObject) {
    return function(prefix) {
        var parent = $('<div/>', isObject(prefix) ? prefix : {"class" : selector(prefix, true)});
        $.each(slice.call(arguments).slice(1), function(i, child) {
            if ($.isFunction(child)) {
                child = child.call(parent);
            }
            if (child) {
                $(child).appendTo(parent);
            }
        });
        return parent;
    }
});