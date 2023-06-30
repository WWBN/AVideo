/*
* This feature allows metadata text tracks to be manipulated once available
* @see processMetadataTracks.
* It also allows ad implementations to leverage ad cues coming through
* text tracks, @see processAdTrack
**/

import videojs from 'video.js';

const cueTextTracks = {};

/*
* This feature allows metadata text tracks to be manipulated once they are available,
* usually after the 'loadstart' event is observed on the player
* @param player A reference to a player
* @param processMetadataTrack A callback that performs some operations on a
* metadata text track
**/
cueTextTracks.processMetadataTracks = function(player, processMetadataTrack) {
  const tracks = player.textTracks();
  const setModeAndProcess = function(track) {
    if (track.kind === 'metadata') {
      player.ads.cueTextTracks.setMetadataTrackMode(track);
      processMetadataTrack(player, track);
    }
  };

  // Text tracks are available
  for (let i = 0; i < tracks.length; i++) {
    setModeAndProcess(tracks[i]);
  }

  // Wait until text tracks are added
  tracks.addEventListener('addtrack', (event) => {
    setModeAndProcess(event.track);
  });
};

/*
* Sets the track mode to one of 'disabled', 'hidden' or 'showing'
* @see https://github.com/videojs/video.js/blob/master/docs/guides/text-tracks.md
* Default behavior is to do nothing, @override if this is not desired
* @param track The text track to set the mode on
*/
cueTextTracks.setMetadataTrackMode = function(track) {
  return;
};

/*
* Determines whether cue is an ad cue and returns the cue data.
* @param player A reference to the player
* @param cue The full cue object
* Returns the given cue by default @override if futher processing is required
* @return {Object} a useable ad cue or null if not supported
**/
cueTextTracks.getSupportedAdCue = function(player, cue) {
  return cue;
};

/*
* Defines whether a cue is supported or not, potentially
* based on the player settings
* @param player A reference to the player
* @param cue The cue to be checked
* Default behavior is to return true, @override if this is not desired
* @return {Boolean}
*/
cueTextTracks.isSupportedAdCue = function(player, cue) {
  return true;
};

/*
* Gets the id associated with a cue.
* @param cue The cue to extract an ID from
* @returns The first occurance of 'id' in the object,
* @override if this is not the desired cue id
**/
cueTextTracks.getCueId = function(player, cue) {
  return cue.id;
};

/*
* Checks whether a cue has already been used
* @param cueId The Id associated with a cue
**/
const cueAlreadySeen = function(player, cueId) {
  return (cueId !== undefined) && player.ads.includedCues[cueId];
};

/*
* Indicates that a cue has been used
* @param cueId The Id associated with a cue
**/
const setCueAlreadySeen = function(player, cueId) {
  if (cueId !== undefined && cueId !== '') {
    player.ads.includedCues[cueId] = true;
  }
};

/*
* This feature allows ad metadata tracks to be manipulated in ad implementations
* @param player A reference to the player
* @param cues The set of cues to work with
* @param processCue A method that uses a cue to make some
* ad request in the ad implementation
* @param [cancelAdsHandler] A method that dynamically cancels ads in the ad implementation
**/
cueTextTracks.processAdTrack = function(player, cues, processCue, cancelAdsHandler) {
  player.ads.includedCues = {};

  // loop over set of cues
  for (let i = 0; i < cues.length; i++) {
    const cue = cues[i];
    const cueData = this.getSupportedAdCue(player, cue);

    // Exit if this is not a supported cue
    if (!this.isSupportedAdCue(player, cue)) {
      videojs.log.warn('Skipping as this is not a supported ad cue.', cue);
      return;
    }

    // Continue processing supported cue
    const cueId = this.getCueId(player, cue);
    const startTime = cue.startTime;

    // Skip ad if cue was already used
    if (cueAlreadySeen(player, cueId)) {
      videojs.log('Skipping ad already seen with ID ' + cueId);
      return;
    }

    // Optional dynamic ad cancellation
    if (cancelAdsHandler) {
      cancelAdsHandler(player, cueData, cueId, startTime);
    }

    // Process cue as an ad cue
    processCue(player, cueData, cueId, startTime);

    // Indicate that this cue has been used
    setCueAlreadySeen(player, cueId);

  }
};

export default cueTextTracks;
