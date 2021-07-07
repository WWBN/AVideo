define([
    'var/emojione',
    'var/readyCallbacks'
],
function(emojione, readyCallbacks) {
    return function (fn) {
        if (emojione) {
            fn();
        } else {
            readyCallbacks.push(fn);
        }
    };
});