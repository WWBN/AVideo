player.ready(function () {
    var currentSrc = player.currentSrc() || '';
    var currentSources = getPlayerCurrentSources();

    if (currentSrc || hasHlsSource(currentSources) || hasInlineHlsSource() || !currentSources.length) {
        return;
    }

    player.src(currentSources);
});
