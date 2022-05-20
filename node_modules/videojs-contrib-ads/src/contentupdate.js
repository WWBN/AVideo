/*
This feature sends a `contentupdate` event when the player source changes.
*/

// Start sending contentupdate events
export default function initializeContentupdate(player) {

  // Keep track of the current content source
  // If you want to change the src of the video without triggering
  // the ad workflow to restart, you can update this variable before
  // modifying the player's source
  player.ads.contentSrc = player.currentSrc();

  player.ads._seenInitialLoadstart = false;

  // Check if a new src has been set, if so, trigger contentupdate
  const checkSrc = function() {
    if (!player.ads.inAdBreak()) {
      const src = player.currentSrc();

      if (src !== player.ads.contentSrc) {

        if (player.ads._seenInitialLoadstart) {
          player.trigger({
            type: 'contentchanged'
          });
        }

        player.trigger({
          type: 'contentupdate',
          oldValue: player.ads.contentSrc,
          newValue: src
        });
        player.ads.contentSrc = src;
      }

      player.ads._seenInitialLoadstart = true;
    }
  };

  // loadstart reliably indicates a new src has been set
  player.on('loadstart', checkSrc);
}
