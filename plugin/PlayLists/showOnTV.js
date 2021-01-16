$(document).ready(function () {
    $(document).keyup(function (e) {
        if (typeof window.parent.closeLiveVideo === "function") {
            parent.focus();
            window.parent.focus();
        }
        if (e.key === "Escape") {
            if (typeof window.parent.closeLiveVideo === "function") {
                e.preventDefault();
                parent.focus();
                window.parent.focus();
                window.parent.closeLiveVideo();
            }
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            var volume = player.volume();
            volume += 0.1;
            if (volume > 1) {
                volume = 1;
            }
            console.log(volume);
            player.muted(false);
            player.volume(volume);
        } else if (e.key === "ArrowDown") {
            e.preventDefault();
            var volume = player.volume();
            volume -= 0.1;
            if (volume < 0) {
                volume = 0;
            }
            console.log(volume);
            player.volume(volume);
        }
    });
});