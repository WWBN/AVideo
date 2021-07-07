// see https://github.com/Modernizr/Modernizr/blob/master/feature-detects/storage/localstorage.js
define([], function() {
    return function () {
        var test = 'test';
        try {
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            return true;
        } catch(e) {
            return false;
        }
    }
});