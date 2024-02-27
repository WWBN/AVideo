player.playlist(embed_playerPlaylist);
player.playlist.autoadvance(0);
player.on('play', function () {
    addViewOnCurrentPlaylitItem(0);
});
player.on('ended', function () {
    setCurrentPlaylitItemVideoStartSeconds(0);
});
player.on('timeupdate', function () {
    var time = Math.round(player.currentTime());
    if (time >= 5) {
        setCurrentPlaylitItemVideoStartSeconds(time);
        if (time % 5 === 0) {
            addViewOnCurrentPlaylitItem(time);
        }
    }

});
player.on('playlistchange', function () {
    console.log('event playlistchange');
});
player.on('duringplaylistchange', function () {
    console.log('event duringplaylistchange');
});
player.on('playlistitem', function () {
    var index = player.playlist.currentIndex();
    updatePLSources(index);
    mediaId = getCurrentPlaylitItemVideosId();
    console.log('event playlistitem ', index, mediaId);
});
player.playlistUi();
if (!empty({$pl_index})) {
    player.playlist.currentItem({$pl_index});
}
if (typeof embed_playerPlaylist[0] !== 'undefined') {
    updatePLSources({$pl_index});
}
$('.vjs-playlist-item ').click(function () {
    var index = player.playlist.currentIndex();
    updatePLSources(index);
    console.log('$(.vjs-playlist-item).click ', index);
});