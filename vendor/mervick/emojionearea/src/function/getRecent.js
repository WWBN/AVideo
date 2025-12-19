define([], function() {
    return function () {
        return localStorage.getItem("recent_emojis") || "";
    }
});