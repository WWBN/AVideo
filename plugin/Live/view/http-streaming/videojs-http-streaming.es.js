/**
 * @videojs/http-streaming
 * @version 1.0.1
 * @copyright 2018 Brightcove, Inc
 * @license Apache-2.0
 */
import URLToolkit from 'url-toolkit';
import window$1 from 'global/window';
import videojs from 'video.js';
import { Parser } from 'm3u8-parser';
import document from 'global/document';
import { parse, parseUTCTiming } from 'mpd-parser';
import mp4probe from 'mux.js/lib/mp4/probe';
import tsInspector from 'mux.js/lib/tools/ts-inspector.js';
import { Decrypter, AsyncStream, decrypt } from 'aes-decrypter';

/**
 * @file resolve-url.js
 */

const resolveUrl = function(baseURL, relativeURL) {
  // return early if we don't need to resolve
  if ((/^[a-z]+:/i).test(relativeURL)) {
    return relativeURL;
  }

  // if the base URL is relative then combine with the current location
  if (!(/\/\//i).test(baseURL)) {
    baseURL = URLToolkit.buildAbsoluteURL(window$1.location.href, baseURL);
  }

  return URLToolkit.buildAbsoluteURL(baseURL, relativeURL);
};

/**
 * @file playlist-loader.js
 *
 * A state machine that manages the loading, caching, and updating of
 * M3U8 playlists.
 *
 */

const { mergeOptions, EventTarget, log } = videojs;

/**
 * Loops through all supported media groups in master and calls the provided
 * callback for each group
 *
 * @param {Object} master
 *        The parsed master manifest object
 * @param {Function} callback
 *        Callback to call for each media group
 */
const forEachMediaGroup = (master, callback) => {
  ['AUDIO', 'SUBTITLES'].forEach((mediaType) => {
    for (let groupKey in master.mediaGroups[mediaType]) {
      for (let labelKey in master.mediaGroups[mediaType][groupKey]) {
        const mediaProperties = master.mediaGroups[mediaType][groupKey][labelKey];

        callback(mediaProperties, mediaType, groupKey, labelKey);
      }
    }
  });
};

/**
  * Returns a new array of segments that is the result of merging
  * properties from an older list of segments onto an updated
  * list. No properties on the updated playlist will be overridden.
  *
  * @param {Array} original the outdated list of segments
  * @param {Array} update the updated list of segments
  * @param {Number=} offset the index of the first update
  * segment in the original segment list. For non-live playlists,
  * this should always be zero and does not need to be
  * specified. For live playlists, it should be the difference
  * between the media sequence numbers in the original and updated
  * playlists.
  * @return a list of merged segment objects
  */
const updateSegments = (original, update, offset) => {
  const result = update.slice();

  offset = offset || 0;
  const length = Math.min(original.length, update.length + offset);

  for (let i = offset; i < length; i++) {
    result[i - offset] = mergeOptions(original[i], result[i - offset]);
  }
  return result;
};

const resolveSegmentUris = (segment, baseUri) => {
  if (!segment.resolvedUri) {
    segment.resolvedUri = resolveUrl(baseUri, segment.uri);
  }
  if (segment.key && !segment.key.resolvedUri) {
    segment.key.resolvedUri = resolveUrl(baseUri, segment.key.uri);
  }
  if (segment.map && !segment.map.resolvedUri) {
    segment.map.resolvedUri = resolveUrl(baseUri, segment.map.uri);
  }
};

/**
  * Returns a new master playlist that is the result of merging an
  * updated media playlist into the original version. If the
  * updated media playlist does not match any of the playlist
  * entries in the original master playlist, null is returned.
  *
  * @param {Object} master a parsed master M3U8 object
  * @param {Object} media a parsed media M3U8 object
  * @return {Object} a new object that represents the original
  * master playlist with the updated media playlist merged in, or
  * null if the merge produced no change.
  */
const updateMaster = (master, media) => {
  const result = mergeOptions(master, {});
  const playlist = result.playlists[media.uri];

  if (!playlist) {
    return null;
  }

  // consider the playlist unchanged if the number of segments is equal and the media
  // sequence number is unchanged
  if (playlist.segments &&
      media.segments &&
      playlist.segments.length === media.segments.length &&
      playlist.mediaSequence === media.mediaSequence) {
    return null;
  }

  const mergedPlaylist = mergeOptions(playlist, media);

  // if the update could overlap existing segment information, merge the two segment lists
  if (playlist.segments) {
    mergedPlaylist.segments = updateSegments(
      playlist.segments,
      media.segments,
      media.mediaSequence - playlist.mediaSequence
    );
  }

  // resolve any segment URIs to prevent us from having to do it later
  mergedPlaylist.segments.forEach((segment) => {
    resolveSegmentUris(segment, mergedPlaylist.resolvedUri);
  });

  // TODO Right now in the playlists array there are two references to each playlist, one
  // that is referenced by index, and one by URI. The index reference may no longer be
  // necessary.
  for (let i = 0; i < result.playlists.length; i++) {
    if (result.playlists[i].uri === media.uri) {
      result.playlists[i] = mergedPlaylist;
    }
  }
  result.playlists[media.uri] = mergedPlaylist;

  return result;
};

const setupMediaPlaylists = (master) => {
  // setup by-URI lookups and resolve media playlist URIs
  let i = master.playlists.length;

  while (i--) {
    let playlist = master.playlists[i];

    master.playlists[playlist.uri] = playlist;
    playlist.resolvedUri = resolveUrl(master.uri, playlist.uri);
    playlist.id = i;

    if (!playlist.attributes) {
      // Although the spec states an #EXT-X-STREAM-INF tag MUST have a
      // BANDWIDTH attribute, we can play the stream without it. This means a poorly
      // formatted master playlist may not have an attribute list. An attributes
      // property is added here to prevent undefined references when we encounter
      // this scenario.
      playlist.attributes = {};

      log.warn('Invalid playlist STREAM-INF detected. Missing BANDWIDTH attribute.');
    }
  }
};

const resolveMediaGroupUris = (master) => {
  forEachMediaGroup(master, (properties) => {
    if (properties.uri) {
      properties.resolvedUri = resolveUrl(master.uri, properties.uri);
    }
  });
};

/**
 * Calculates the time to wait before refreshing a live playlist
 *
 * @param {Object} media
 *        The current media
 * @param {Boolean} update
 *        True if there were any updates from the last refresh, false otherwise
 * @return {Number}
 *         The time in ms to wait before refreshing the live playlist
 */
const refreshDelay = (media, update) => {
  const lastSegment = media.segments[media.segments.length - 1];
  let delay;

  if (update && lastSegment && lastSegment.duration) {
    delay = lastSegment.duration * 1000;
  } else {
    // if the playlist is unchanged since the last reload or last segment duration
    // cannot be determined, try again after half the target duration
    delay = (media.targetDuration || 10) * 500;
  }
  return delay;
};

/**
 * Load a playlist from a remote location
 *
 * @class PlaylistLoader
 * @extends Stream
 * @param {String} srcUrl the url to start with
 * @param {Boolean} withCredentials the withCredentials xhr option
 * @constructor
 */
class PlaylistLoader extends EventTarget {
  constructor(srcUrl, hls, withCredentials) {
    super();

    this.srcUrl = srcUrl;
    this.hls_ = hls;
    this.withCredentials = withCredentials;

    if (!this.srcUrl) {
      throw new Error('A non-empty playlist URL is required');
    }

    // initialize the loader state
    this.state = 'HAVE_NOTHING';

    // live playlist staleness timeout
    this.on('mediaupdatetimeout', () => {
      if (this.state !== 'HAVE_METADATA') {
        // only refresh the media playlist if no other activity is going on
        return;
      }

      this.state = 'HAVE_CURRENT_METADATA';

      this.request = this.hls_.xhr({
        uri: resolveUrl(this.master.uri, this.media().uri),
        withCredentials: this.withCredentials
      }, (error, req) => {
        // disposed
        if (!this.request) {
          return;
        }

        if (error) {
          return this.playlistRequestError(
            this.request, this.media().uri, 'HAVE_METADATA');
        }

        this.haveMetadata(this.request, this.media().uri);
      });
    });
  }

  playlistRequestError(xhr, url, startingState) {
    // any in-flight request is now finished
    this.request = null;

    if (startingState) {
      this.state = startingState;
    }

    this.error = {
      playlist: this.master.playlists[url],
      status: xhr.status,
      message: 'HLS playlist request error at URL: ' + url,
      responseText: xhr.responseText,
      code: (xhr.status >= 500) ? 4 : 2
    };

    this.trigger('error');
  }

  // update the playlist loader's state in response to a new or
  // updated playlist.
  haveMetadata(xhr, url) {
    // any in-flight request is now finished
    this.request = null;
    this.state = 'HAVE_METADATA';

    const parser = new Parser();

    parser.push(xhr.responseText);
    parser.end();
    parser.manifest.uri = url;
    // m3u8-parser does not attach an attributes property to media playlists so make
    // sure that the property is attached to avoid undefined reference errors
    parser.manifest.attributes = parser.manifest.attributes || {};

    // merge this playlist into the master
    const update = updateMaster(this.master, parser.manifest);

    this.targetDuration = parser.manifest.targetDuration;

    if (update) {
      this.master = update;
      this.media_ = this.master.playlists[parser.manifest.uri];
    } else {
      this.trigger('playlistunchanged');
    }

    // refresh live playlists after a target duration passes
    if (!this.media().endList) {
      window$1.clearTimeout(this.mediaUpdateTimeout);
      this.mediaUpdateTimeout = window$1.setTimeout(() => {
        this.trigger('mediaupdatetimeout');
      }, refreshDelay(this.media(), !!update));
    }

    this.trigger('loadedplaylist');
  }

   /**
    * Abort any outstanding work and clean up.
    */
  dispose() {
    this.stopRequest();
    window$1.clearTimeout(this.mediaUpdateTimeout);
  }

  stopRequest() {
    if (this.request) {
      const oldRequest = this.request;

      this.request = null;
      oldRequest.onreadystatechange = null;
      oldRequest.abort();
    }
  }

   /**
    * When called without any arguments, returns the currently
    * active media playlist. When called with a single argument,
    * triggers the playlist loader to asynchronously switch to the
    * specified media playlist. Calling this method while the
    * loader is in the HAVE_NOTHING causes an error to be emitted
    * but otherwise has no effect.
    *
    * @param {Object=} playlist the parsed media playlist
    * object to switch to
    * @return {Playlist} the current loaded media
    */
  media(playlist) {
    // getter
    if (!playlist) {
      return this.media_;
    }

    // setter
    if (this.state === 'HAVE_NOTHING') {
      throw new Error('Cannot switch media playlist from ' + this.state);
    }

    const startingState = this.state;

    // find the playlist object if the target playlist has been
    // specified by URI
    if (typeof playlist === 'string') {
      if (!this.master.playlists[playlist]) {
        throw new Error('Unknown playlist URI: ' + playlist);
      }
      playlist = this.master.playlists[playlist];
    }

    const mediaChange = !this.media_ || playlist.uri !== this.media_.uri;

    // switch to fully loaded playlists immediately
    if (this.master.playlists[playlist.uri].endList) {
      // abort outstanding playlist requests
      if (this.request) {
        this.request.onreadystatechange = null;
        this.request.abort();
        this.request = null;
      }
      this.state = 'HAVE_METADATA';
      this.media_ = playlist;

      // trigger media change if the active media has been updated
      if (mediaChange) {
        this.trigger('mediachanging');
        this.trigger('mediachange');
      }
      return;
    }

    // switching to the active playlist is a no-op
    if (!mediaChange) {
      return;
    }

    this.state = 'SWITCHING_MEDIA';

    // there is already an outstanding playlist request
    if (this.request) {
      if (resolveUrl(this.master.uri, playlist.uri) === this.request.url) {
        // requesting to switch to the same playlist multiple times
        // has no effect after the first
        return;
      }
      this.request.onreadystatechange = null;
      this.request.abort();
      this.request = null;
    }

    // request the new playlist
    if (this.media_) {
      this.trigger('mediachanging');
    }

    this.request = this.hls_.xhr({
      uri: resolveUrl(this.master.uri, playlist.uri),
      withCredentials: this.withCredentials
    }, (error, req) => {
      // disposed
      if (!this.request) {
        return;
      }

      if (error) {
        return this.playlistRequestError(this.request, playlist.uri, startingState);
      }

      this.haveMetadata(req, playlist.uri);

      // fire loadedmetadata the first time a media playlist is loaded
      if (startingState === 'HAVE_MASTER') {
        this.trigger('loadedmetadata');
      } else {
        this.trigger('mediachange');
      }
    });
  }

  /**
   * pause loading of the playlist
   */
  pause() {
    this.stopRequest();
    window$1.clearTimeout(this.mediaUpdateTimeout);
    if (this.state === 'HAVE_NOTHING') {
      // If we pause the loader before any data has been retrieved, its as if we never
      // started, so reset to an unstarted state.
      this.started = false;
    }
    // Need to restore state now that no activity is happening
    if (this.state === 'SWITCHING_MEDIA') {
      // if the loader was in the process of switching media, it should either return to
      // HAVE_MASTER or HAVE_METADATA depending on if the loader has loaded a media
      // playlist yet. This is determined by the existence of loader.media_
      if (this.media_) {
        this.state = 'HAVE_METADATA';
      } else {
        this.state = 'HAVE_MASTER';
      }
    } else if (this.state === 'HAVE_CURRENT_METADATA') {
      this.state = 'HAVE_METADATA';
    }
  }

  /**
   * start loading of the playlist
   */
  load(isFinalRendition) {
    window$1.clearTimeout(this.mediaUpdateTimeout);

    const media = this.media();

    if (isFinalRendition) {
      const delay = media ? (media.targetDuration / 2) * 1000 : 5 * 1000;

      this.mediaUpdateTimeout = window$1.setTimeout(() => this.load(), delay);
      return;
    }

    if (!this.started) {
      this.start();
      return;
    }

    if (media && !media.endList) {
      this.trigger('mediaupdatetimeout');
    } else {
      this.trigger('loadedplaylist');
    }
  }

  /**
   * start loading of the playlist
   */
  start() {
    this.started = true;

    // request the specified URL
    this.request = this.hls_.xhr({
      uri: this.srcUrl,
      withCredentials: this.withCredentials
    }, (error, req) => {
      // disposed
      if (!this.request) {
        return;
      }

      // clear the loader's request reference
      this.request = null;

      if (error) {
        this.error = {
          status: req.status,
          message: 'HLS playlist request error at URL: ' + this.srcUrl,
          responseText: req.responseText,
          // MEDIA_ERR_NETWORK
          code: 2
        };
        if (this.state === 'HAVE_NOTHING') {
          this.started = false;
        }
        return this.trigger('error');
      }

      const parser = new Parser();

      parser.push(req.responseText);
      parser.end();

      this.state = 'HAVE_MASTER';

      parser.manifest.uri = this.srcUrl;

      // loaded a master playlist
      if (parser.manifest.playlists) {
        this.master = parser.manifest;

        setupMediaPlaylists(this.master);
        resolveMediaGroupUris(this.master);

        this.trigger('loadedplaylist');
        if (!this.request) {
          // no media playlist was specifically selected so start
          // from the first listed one
          this.media(parser.manifest.playlists[0]);
        }
        return;
      }

      // loaded a media playlist
      // infer a master playlist if none was previously requested
      this.master = {
        mediaGroups: {
          'AUDIO': {},
          'VIDEO': {},
          'CLOSED-CAPTIONS': {},
          'SUBTITLES': {}
        },
        uri: window$1.location.href,
        playlists: [{
          uri: this.srcUrl,
          id: 0
        }]
      };
      this.master.playlists[this.srcUrl] = this.master.playlists[0];
      this.master.playlists[0].resolvedUri = this.srcUrl;
      // m3u8-parser does not attach an attributes property to media playlists so make
      // sure that the property is attached to avoid undefined reference errors
      this.master.playlists[0].attributes = this.master.playlists[0].attributes || {};
      this.haveMetadata(req, this.srcUrl);
      return this.trigger('loadedmetadata');
    });
  }
}

/**
 * @file playlist.js
 *
 * Playlist related utilities.
 */

const {createTimeRange} = videojs;

/**
 * walk backward until we find a duration we can use
 * or return a failure
 *
 * @param {Playlist} playlist the playlist to walk through
 * @param {Number} endSequence the mediaSequence to stop walking on
 */

const backwardDuration = function(playlist, endSequence) {
  let result = 0;
  let i = endSequence - playlist.mediaSequence;
  // if a start time is available for segment immediately following
  // the interval, use it
  let segment = playlist.segments[i];

  // Walk backward until we find the latest segment with timeline
  // information that is earlier than endSequence
  if (segment) {
    if (typeof segment.start !== 'undefined') {
      return { result: segment.start, precise: true };
    }
    if (typeof segment.end !== 'undefined') {
      return {
        result: segment.end - segment.duration,
        precise: true
      };
    }
  }
  while (i--) {
    segment = playlist.segments[i];
    if (typeof segment.end !== 'undefined') {
      return { result: result + segment.end, precise: true };
    }

    result += segment.duration;

    if (typeof segment.start !== 'undefined') {
      return { result: result + segment.start, precise: true };
    }
  }
  return { result, precise: false };
};

/**
 * walk forward until we find a duration we can use
 * or return a failure
 *
 * @param {Playlist} playlist the playlist to walk through
 * @param {Number} endSequence the mediaSequence to stop walking on
 */
const forwardDuration = function(playlist, endSequence) {
  let result = 0;
  let segment;
  let i = endSequence - playlist.mediaSequence;
  // Walk forward until we find the earliest segment with timeline
  // information

  for (; i < playlist.segments.length; i++) {
    segment = playlist.segments[i];
    if (typeof segment.start !== 'undefined') {
      return {
        result: segment.start - result,
        precise: true
      };
    }

    result += segment.duration;

    if (typeof segment.end !== 'undefined') {
      return {
        result: segment.end - result,
        precise: true
      };
    }

  }
  // indicate we didn't find a useful duration estimate
  return { result: -1, precise: false };
};

/**
  * Calculate the media duration from the segments associated with a
  * playlist. The duration of a subinterval of the available segments
  * may be calculated by specifying an end index.
  *
  * @param {Object} playlist a media playlist object
  * @param {Number=} endSequence an exclusive upper boundary
  * for the playlist.  Defaults to playlist length.
  * @param {Number} expired the amount of time that has dropped
  * off the front of the playlist in a live scenario
  * @return {Number} the duration between the first available segment
  * and end index.
  */
const intervalDuration = function(playlist, endSequence, expired) {
  let backward;
  let forward;

  if (typeof endSequence === 'undefined') {
    endSequence = playlist.mediaSequence + playlist.segments.length;
  }

  if (endSequence < playlist.mediaSequence) {
    return 0;
  }

  // do a backward walk to estimate the duration
  backward = backwardDuration(playlist, endSequence);
  if (backward.precise) {
    // if we were able to base our duration estimate on timing
    // information provided directly from the Media Source, return
    // it
    return backward.result;
  }

  // walk forward to see if a precise duration estimate can be made
  // that way
  forward = forwardDuration(playlist, endSequence);
  if (forward.precise) {
    // we found a segment that has been buffered and so it's
    // position is known precisely
    return forward.result;
  }

  // return the less-precise, playlist-based duration estimate
  return backward.result + expired;
};

/**
  * Calculates the duration of a playlist. If a start and end index
  * are specified, the duration will be for the subset of the media
  * timeline between those two indices. The total duration for live
  * playlists is always Infinity.
  *
  * @param {Object} playlist a media playlist object
  * @param {Number=} endSequence an exclusive upper
  * boundary for the playlist. Defaults to the playlist media
  * sequence number plus its length.
  * @param {Number=} expired the amount of time that has
  * dropped off the front of the playlist in a live scenario
  * @return {Number} the duration between the start index and end
  * index.
  */
const duration = function(playlist, endSequence, expired) {
  if (!playlist) {
    return 0;
  }

  if (typeof expired !== 'number') {
    expired = 0;
  }

  // if a slice of the total duration is not requested, use
  // playlist-level duration indicators when they're present
  if (typeof endSequence === 'undefined') {
    // if present, use the duration specified in the playlist
    if (playlist.totalDuration) {
      return playlist.totalDuration;
    }

    // duration should be Infinity for live playlists
    if (!playlist.endList) {
      return window$1.Infinity;
    }
  }

  // calculate the total duration based on the segment durations
  return intervalDuration(playlist,
                          endSequence,
                          expired);
};

/**
  * Calculate the time between two indexes in the current playlist
  * neight the start- nor the end-index need to be within the current
  * playlist in which case, the targetDuration of the playlist is used
  * to approximate the durations of the segments
  *
  * @param {Object} playlist a media playlist object
  * @param {Number} startIndex
  * @param {Number} endIndex
  * @return {Number} the number of seconds between startIndex and endIndex
  */
const sumDurations = function(playlist, startIndex, endIndex) {
  let durations = 0;

  if (startIndex > endIndex) {
    [startIndex, endIndex] = [endIndex, startIndex];
  }

  if (startIndex < 0) {
    for (let i = startIndex; i < Math.min(0, endIndex); i++) {
      durations += playlist.targetDuration;
    }
    startIndex = 0;
  }

  for (let i = startIndex; i < endIndex; i++) {
    durations += playlist.segments[i].duration;
  }

  return durations;
};

/**
 * Determines the media index of the segment corresponding to the safe edge of the live
 * window which is the duration of the last segment plus 2 target durations from the end
 * of the playlist.
 *
 * @param {Object} playlist
 *        a media playlist object
 * @return {Number}
 *         The media index of the segment at the safe live point. 0 if there is no "safe"
 *         point.
 * @function safeLiveIndex
 */
const safeLiveIndex = function(playlist) {
  if (!playlist.segments.length) {
    return 0;
  }

  let i = playlist.segments.length - 1;
  let distanceFromEnd = playlist.segments[i].duration || playlist.targetDuration;
  const safeDistance = distanceFromEnd + playlist.targetDuration * 2;

  while (i--) {
    distanceFromEnd += playlist.segments[i].duration;

    if (distanceFromEnd >= safeDistance) {
      break;
    }
  }

  return Math.max(0, i);
};

/**
 * Calculates the playlist end time
 *
 * @param {Object} playlist a media playlist object
 * @param {Number=} expired the amount of time that has
 *                  dropped off the front of the playlist in a live scenario
 * @param {Boolean|false} useSafeLiveEnd a boolean value indicating whether or not the
 *                        playlist end calculation should consider the safe live end
 *                        (truncate the playlist end by three segments). This is normally
 *                        used for calculating the end of the playlist's seekable range.
 * @returns {Number} the end time of playlist
 * @function playlistEnd
 */
const playlistEnd = function(playlist, expired, useSafeLiveEnd) {
  if (!playlist || !playlist.segments) {
    return null;
  }
  if (playlist.endList) {
    return duration(playlist);
  }

  if (expired === null) {
    return null;
  }

  expired = expired || 0;

  const endSequence = useSafeLiveEnd ? safeLiveIndex(playlist) : playlist.segments.length;

  return intervalDuration(playlist,
                          playlist.mediaSequence + endSequence,
                          expired);
};

/**
  * Calculates the interval of time that is currently seekable in a
  * playlist. The returned time ranges are relative to the earliest
  * moment in the specified playlist that is still available. A full
  * seekable implementation for live streams would need to offset
  * these values by the duration of content that has expired from the
  * stream.
  *
  * @param {Object} playlist a media playlist object
  * dropped off the front of the playlist in a live scenario
  * @param {Number=} expired the amount of time that has
  * dropped off the front of the playlist in a live scenario
  * @return {TimeRanges} the periods of time that are valid targets
  * for seeking
  */
const seekable = function(playlist, expired) {
  let useSafeLiveEnd = true;
  let seekableStart = expired || 0;
  let seekableEnd = playlistEnd(playlist, expired, useSafeLiveEnd);

  if (seekableEnd === null) {
    return createTimeRange();
  }
  return createTimeRange(seekableStart, seekableEnd);
};

const isWholeNumber = function(num) {
  return (num - Math.floor(num)) === 0;
};

const roundSignificantDigit = function(increment, num) {
  // If we have a whole number, just add 1 to it
  if (isWholeNumber(num)) {
    return num + (increment * 0.1);
  }

  let numDecimalDigits = num.toString().split('.')[1].length;

  for (let i = 1; i <= numDecimalDigits; i++) {
    let scale = Math.pow(10, i);
    let temp = num * scale;

    if (isWholeNumber(temp) ||
        i === numDecimalDigits) {
      return (temp + increment) / scale;
    }
  }
};

const ceilLeastSignificantDigit = roundSignificantDigit.bind(null, 1);
const floorLeastSignificantDigit = roundSignificantDigit.bind(null, -1);

/**
 * Determine the index and estimated starting time of the segment that
 * contains a specified playback position in a media playlist.
 *
 * @param {Object} playlist the media playlist to query
 * @param {Number} currentTime The number of seconds since the earliest
 * possible position to determine the containing segment for
 * @param {Number} startIndex
 * @param {Number} startTime
 * @return {Object}
 */
const getMediaInfoForTime = function(playlist,
                                            currentTime,
                                            startIndex,
                                            startTime) {
  let i;
  let segment;
  let numSegments = playlist.segments.length;

  let time = currentTime - startTime;

  if (time < 0) {
    // Walk backward from startIndex in the playlist, adding durations
    // until we find a segment that contains `time` and return it
    if (startIndex > 0) {
      for (i = startIndex - 1; i >= 0; i--) {
        segment = playlist.segments[i];
        time += floorLeastSignificantDigit(segment.duration);
        if (time > 0) {
          return {
            mediaIndex: i,
            startTime: startTime - sumDurations(playlist, startIndex, i)
          };
        }
      }
    }
    // We were unable to find a good segment within the playlist
    // so select the first segment
    return {
      mediaIndex: 0,
      startTime: currentTime
    };
  }

  // When startIndex is negative, we first walk forward to first segment
  // adding target durations. If we "run out of time" before getting to
  // the first segment, return the first segment
  if (startIndex < 0) {
    for (i = startIndex; i < 0; i++) {
      time -= playlist.targetDuration;
      if (time < 0) {
        return {
          mediaIndex: 0,
          startTime: currentTime
        };
      }
    }
    startIndex = 0;
  }

  // Walk forward from startIndex in the playlist, subtracting durations
  // until we find a segment that contains `time` and return it
  for (i = startIndex; i < numSegments; i++) {
    segment = playlist.segments[i];
    time -= ceilLeastSignificantDigit(segment.duration);
    if (time < 0) {
      return {
        mediaIndex: i,
        startTime: startTime + sumDurations(playlist, startIndex, i)
      };
    }
  }

  // We are out of possible candidates so load the last one...
  return {
    mediaIndex: numSegments - 1,
    startTime: currentTime
  };
};

/**
 * Check whether the playlist is blacklisted or not.
 *
 * @param {Object} playlist the media playlist object
 * @return {boolean} whether the playlist is blacklisted or not
 * @function isBlacklisted
 */
const isBlacklisted = function(playlist) {
  return playlist.excludeUntil && playlist.excludeUntil > Date.now();
};

/**
 * Check whether the playlist is compatible with current playback configuration or has
 * been blacklisted permanently for being incompatible.
 *
 * @param {Object} playlist the media playlist object
 * @return {boolean} whether the playlist is incompatible or not
 * @function isIncompatible
 */
const isIncompatible = function(playlist) {
  return playlist.excludeUntil && playlist.excludeUntil === Infinity;
};

/**
 * Check whether the playlist is enabled or not.
 *
 * @param {Object} playlist the media playlist object
 * @return {boolean} whether the playlist is enabled or not
 * @function isEnabled
 */
const isEnabled = function(playlist) {
  const blacklisted = isBlacklisted(playlist);

  return (!playlist.disabled && !blacklisted);
};

/**
 * Check whether the playlist has been manually disabled through the representations api.
 *
 * @param {Object} playlist the media playlist object
 * @return {boolean} whether the playlist is disabled manually or not
 * @function isDisabled
 */
const isDisabled = function(playlist) {
  return playlist.disabled;
};

/**
 * Returns whether the current playlist is an AES encrypted HLS stream
 *
 * @return {Boolean} true if it's an AES encrypted HLS stream
 */
const isAes = function(media) {
  for (let i = 0; i < media.segments.length; i++) {
    if (media.segments[i].key) {
      return true;
    }
  }
  return false;
};

/**
 * Returns whether the current playlist contains fMP4
 *
 * @return {Boolean} true if the playlist contains fMP4
 */
const isFmp4 = function(media) {
  for (let i = 0; i < media.segments.length; i++) {
    if (media.segments[i].map) {
      return true;
    }
  }
  return false;
};

/**
 * Checks if the playlist has a value for the specified attribute
 *
 * @param {String} attr
 *        Attribute to check for
 * @param {Object} playlist
 *        The media playlist object
 * @return {Boolean}
 *         Whether the playlist contains a value for the attribute or not
 * @function hasAttribute
 */
const hasAttribute = function(attr, playlist) {
  return playlist.attributes && playlist.attributes[attr];
};

/**
 * Estimates the time required to complete a segment download from the specified playlist
 *
 * @param {Number} segmentDuration
 *        Duration of requested segment
 * @param {Number} bandwidth
 *        Current measured bandwidth of the player
 * @param {Object} playlist
 *        The media playlist object
 * @param {Number=} bytesReceived
 *        Number of bytes already received for the request. Defaults to 0
 * @return {Number|NaN}
 *         The estimated time to request the segment. NaN if bandwidth information for
 *         the given playlist is unavailable
 * @function estimateSegmentRequestTime
 */
const estimateSegmentRequestTime = function(segmentDuration,
                                                   bandwidth,
                                                   playlist,
                                                   bytesReceived = 0) {
  if (!hasAttribute('BANDWIDTH', playlist)) {
    return NaN;
  }

  const size = segmentDuration * playlist.attributes.BANDWIDTH;

  return (size - (bytesReceived * 8)) / bandwidth;
};

/*
 * Returns whether the current playlist is the lowest rendition
 *
 * @return {Boolean} true if on lowest rendition
 */
const isLowestEnabledRendition = (master, media) => {
  if (master.playlists.length === 1) {
    return true;
  }

  const currentBandwidth = media.attributes.BANDWIDTH || Number.MAX_VALUE;

  return (master.playlists.filter((playlist) => {
    if (!isEnabled(playlist)) {
      return false;
    }

    return (playlist.attributes.BANDWIDTH || 0) < currentBandwidth;

  }).length === 0);
};

// exports
var Playlist = {
  duration,
  seekable,
  safeLiveIndex,
  getMediaInfoForTime,
  isEnabled,
  isDisabled,
  isBlacklisted,
  isIncompatible,
  playlistEnd,
  isAes,
  isFmp4,
  hasAttribute,
  estimateSegmentRequestTime,
  isLowestEnabledRendition
};

/**
 * @file xhr.js
 */

const {
  xhr: videojsXHR,
  mergeOptions: mergeOptions$1
} = videojs;

const xhrFactory = function() {
  const xhr = function XhrFunction(options, callback) {
    // Add a default timeout for all hls requests
    options = mergeOptions$1({
      timeout: 45e3
    }, options);

    // Allow an optional user-specified function to modify the option
    // object before we construct the xhr request
    let beforeRequest = XhrFunction.beforeRequest || videojs.Hls.xhr.beforeRequest;

    if (beforeRequest && typeof beforeRequest === 'function') {
      let newOptions = beforeRequest(options);

      if (newOptions) {
        options = newOptions;
      }
    }

    let request = videojsXHR(options, function(error, response) {
      let reqResponse = request.response;

      if (!error && reqResponse) {
        request.responseTime = Date.now();
        request.roundTripTime = request.responseTime - request.requestTime;
        request.bytesReceived = reqResponse.byteLength || reqResponse.length;
        if (!request.bandwidth) {
          request.bandwidth =
            Math.floor((request.bytesReceived / request.roundTripTime) * 8 * 1000);
        }
      }

      if (response.headers) {
        request.responseHeaders = response.headers;
      }

      // videojs.xhr now uses a specific code on the error
      // object to signal that a request has timed out instead
      // of setting a boolean on the request object
      if (error && error.code === 'ETIMEDOUT') {
        request.timedout = true;
      }

      // videojs.xhr no longer considers status codes outside of 200 and 0
      // (for file uris) to be errors, but the old XHR did, so emulate that
      // behavior. Status 206 may be used in response to byterange requests.
      if (!error &&
          !request.aborted &&
          response.statusCode !== 200 &&
          response.statusCode !== 206 &&
          response.statusCode !== 0) {
        error = new Error('XHR Failed with a response of: ' +
                          (request && (reqResponse || request.responseText)));
      }

      callback(error, request);
    });
    const originalAbort = request.abort;

    request.abort = function() {
      request.aborted = true;
      return originalAbort.apply(request, arguments);
    };
    request.uri = options.uri;
    request.requestTime = Date.now();
    return request;
  };

  return xhr;
};

/**
 * @file bin-utils.js
 */

/**
 * convert a TimeRange to text
 *
 * @param {TimeRange} range the timerange to use for conversion
 * @param {Number} i the iterator on the range to convert
 */
const textRange = function(range, i) {
  return range.start(i) + '-' + range.end(i);
};

/**
 * format a number as hex string
 *
 * @param {Number} e The number
 * @param {Number} i the iterator
 */
const formatHexString = function(e, i) {
  let value = e.toString(16);

  return '00'.substring(0, 2 - value.length) + value + (i % 2 ? ' ' : '');
};
const formatAsciiString = function(e) {
  if (e >= 0x20 && e < 0x7e) {
    return String.fromCharCode(e);
  }
  return '.';
};

/**
 * Creates an object for sending to a web worker modifying properties that are TypedArrays
 * into a new object with seperated properties for the buffer, byteOffset, and byteLength.
 *
 * @param {Object} message
 *        Object of properties and values to send to the web worker
 * @return {Object}
 *         Modified message with TypedArray values expanded
 * @function createTransferableMessage
 */
const createTransferableMessage = function(message) {
  const transferable = {};

  Object.keys(message).forEach((key) => {
    const value = message[key];

    if (ArrayBuffer.isView(value)) {
      transferable[key] = {
        bytes: value.buffer,
        byteOffset: value.byteOffset,
        byteLength: value.byteLength
      };
    } else {
      transferable[key] = value;
    }
  });

  return transferable;
};

/**
 * Returns a unique string identifier for a media initialization
 * segment.
 */
const initSegmentId = function(initSegment) {
  let byterange = initSegment.byterange || {
    length: Infinity,
    offset: 0
  };

  return [
    byterange.length, byterange.offset, initSegment.resolvedUri
  ].join(',');
};

/**
 * utils to help dump binary data to the console
 */
const hexDump = (data) => {
  let bytes = Array.prototype.slice.call(data);
  let step = 16;
  let result = '';
  let hex;
  let ascii;

  for (let j = 0; j < bytes.length / step; j++) {
    hex = bytes.slice(j * step, j * step + step).map(formatHexString).join('');
    ascii = bytes.slice(j * step, j * step + step).map(formatAsciiString).join('');
    result += hex + ' ' + ascii + '\n';
  }

  return result;
};

const tagDump = ({ bytes }) => hexDump(bytes);

const textRanges = (ranges) => {
  let result = '';
  let i;

  for (i = 0; i < ranges.length; i++) {
    result += textRange(ranges, i) + ' ';
  }
  return result;
};

var utils = /*#__PURE__*/Object.freeze({
  createTransferableMessage: createTransferableMessage,
  initSegmentId: initSegmentId,
  hexDump: hexDump,
  tagDump: tagDump,
  textRanges: textRanges
});

/**
 * ranges
 *
 * Utilities for working with TimeRanges.
 *
 */

// Fudge factor to account for TimeRanges rounding
const TIME_FUDGE_FACTOR = 1 / 30;
// Comparisons between time values such as current time and the end of the buffered range
// can be misleading because of precision differences or when the current media has poorly
// aligned audio and video, which can cause values to be slightly off from what you would
// expect. This value is what we consider to be safe to use in such comparisons to account
// for these scenarios.
const SAFE_TIME_DELTA = TIME_FUDGE_FACTOR * 3;
const filterRanges = function(timeRanges, predicate) {
  let results = [];
  let i;

  if (timeRanges && timeRanges.length) {
    // Search for ranges that match the predicate
    for (i = 0; i < timeRanges.length; i++) {
      if (predicate(timeRanges.start(i), timeRanges.end(i))) {
        results.push([timeRanges.start(i), timeRanges.end(i)]);
      }
    }
  }

  return videojs.createTimeRanges(results);
};

/**
 * Attempts to find the buffered TimeRange that contains the specified
 * time.
 * @param {TimeRanges} buffered - the TimeRanges object to query
 * @param {number} time  - the time to filter on.
 * @returns {TimeRanges} a new TimeRanges object
 */
const findRange = function(buffered, time) {
  return filterRanges(buffered, function(start, end) {
    return start - TIME_FUDGE_FACTOR <= time &&
      end + TIME_FUDGE_FACTOR >= time;
  });
};

/**
 * Returns the TimeRanges that begin later than the specified time.
 * @param {TimeRanges} timeRanges - the TimeRanges object to query
 * @param {number} time - the time to filter on.
 * @returns {TimeRanges} a new TimeRanges object.
 */
const findNextRange = function(timeRanges, time) {
  return filterRanges(timeRanges, function(start) {
    return start - TIME_FUDGE_FACTOR >= time;
  });
};

/**
 * Returns gaps within a list of TimeRanges
 * @param {TimeRanges} buffered - the TimeRanges object
 * @return {TimeRanges} a TimeRanges object of gaps
 */
const findGaps = function(buffered) {
  if (buffered.length < 2) {
    return videojs.createTimeRanges();
  }

  let ranges = [];

  for (let i = 1; i < buffered.length; i++) {
    let start = buffered.end(i - 1);
    let end = buffered.start(i);

    ranges.push([start, end]);
  }

  return videojs.createTimeRanges(ranges);
};

/**
 * Gets a human readable string for a TimeRange
 *
 * @param {TimeRange} range
 * @returns {String} a human readable string
 */
const printableRange = (range) => {
  let strArr = [];

  if (!range || !range.length) {
    return '';
  }

  for (let i = 0; i < range.length; i++) {
    strArr.push(range.start(i) + ' => ' + range.end(i));
  }

  return strArr.join(', ');
};

/**
 * Calculates the amount of time left in seconds until the player hits the end of the
 * buffer and causes a rebuffer
 *
 * @param {TimeRange} buffered
 *        The state of the buffer
 * @param {Numnber} currentTime
 *        The current time of the player
 * @param {Number} playbackRate
 *        The current playback rate of the player. Defaults to 1.
 * @return {Number}
 *         Time until the player has to start rebuffering in seconds.
 * @function timeUntilRebuffer
 */
const timeUntilRebuffer = function(buffered, currentTime, playbackRate = 1) {
  const bufferedEnd = buffered.length ? buffered.end(buffered.length - 1) : 0;

  return (bufferedEnd - currentTime) / playbackRate;
};

/**
 * Converts a TimeRanges object into an array representation
 * @param {TimeRanges} timeRanges
 * @returns {Array}
 */
const timeRangesToArray = (timeRanges) => {
  const timeRangesList = [];

  for (let i = 0; i < timeRanges.length; i++) {
    timeRangesList.push({
      start: timeRanges.start(i),
      end: timeRanges.end(i)
    });
  }

  return timeRangesList;
};

/**
 * @file create-text-tracks-if-necessary.js
 */

/**
 * Create text tracks on video.js if they exist on a segment.
 *
 * @param {Object} sourceBuffer the VSB or FSB
 * @param {Object} mediaSource the HTML media source
 * @param {Object} segment the segment that may contain the text track
 * @private
 */
const createTextTracksIfNecessary = function(sourceBuffer, mediaSource, segment) {
  const player = mediaSource.player_;

  // create an in-band caption track if one is present in the segment
  if (segment.captions &&
      segment.captions.length) {
    if (!sourceBuffer.inbandTextTracks_) {
      sourceBuffer.inbandTextTracks_ = {};
    }

    for (let trackId in segment.captionStreams) {
      if (!sourceBuffer.inbandTextTracks_[trackId]) {
        player.tech_.trigger({type: 'usage', name: 'hls-608'});
        let track = player.textTracks().getTrackById(trackId);

        if (track) {
          // Resuse an existing track with a CC# id because this was
          // very likely created by videojs-contrib-hls from information
          // in the m3u8 for us to use
          sourceBuffer.inbandTextTracks_[trackId] = track;
        } else {
          // Otherwise, create a track with the default `CC#` label and
          // without a language
          sourceBuffer.inbandTextTracks_[trackId] = player.addRemoteTextTrack({
            kind: 'captions',
            id: trackId,
            label: trackId
          }, false).track;
        }
      }
    }
  }

  if (segment.metadata &&
      segment.metadata.length &&
      !sourceBuffer.metadataTrack_) {
    sourceBuffer.metadataTrack_ = player.addRemoteTextTrack({
      kind: 'metadata',
      label: 'Timed Metadata'
    }, false).track;
    sourceBuffer.metadataTrack_.inBandMetadataTrackDispatchType =
      segment.metadata.dispatchType;
  }
};

/**
 * @file remove-cues-from-track.js
 */

/**
 * Remove cues from a track on video.js.
 *
 * @param {Double} start start of where we should remove the cue
 * @param {Double} end end of where the we should remove the cue
 * @param {Object} track the text track to remove the cues from
 * @private
 */
const removeCuesFromTrack = function(start, end, track) {
  let i;
  let cue;

  if (!track) {
    return;
  }

  if (!track.cues) {
    return;
  }

  i = track.cues.length;

  while (i--) {
    cue = track.cues[i];

    // Remove any overlapping cue
    if (cue.startTime <= end && cue.endTime >= start) {
      track.removeCue(cue);
    }
  }
};

/**
 * @file add-text-track-data.js
 */
/**
 * Define properties on a cue for backwards compatability,
 * but warn the user that the way that they are using it
 * is depricated and will be removed at a later date.
 *
 * @param {Cue} cue the cue to add the properties on
 * @private
 */
const deprecateOldCue = function(cue) {
  Object.defineProperties(cue.frame, {
    id: {
      get() {
        videojs.log.warn(
          'cue.frame.id is deprecated. Use cue.value.key instead.'
        );
        return cue.value.key;
      }
    },
    value: {
      get() {
        videojs.log.warn(
          'cue.frame.value is deprecated. Use cue.value.data instead.'
        );
        return cue.value.data;
      }
    },
    privateData: {
      get() {
        videojs.log.warn(
          'cue.frame.privateData is deprecated. Use cue.value.data instead.'
        );
        return cue.value.data;
      }
    }
  });
};

const durationOfVideo = function(duration) {
  let dur;

  if (isNaN(duration) || Math.abs(duration) === Infinity) {
    dur = Number.MAX_VALUE;
  } else {
    dur = duration;
  }
  return dur;
};
/**
 * Add text track data to a source handler given the captions and
 * metadata from the buffer.
 *
 * @param {Object} sourceHandler the virtual source buffer
 * @param {Array} captionArray an array of caption data
 * @param {Array} metadataArray an array of meta data
 * @private
 */
const addTextTrackData = function(sourceHandler, captionArray, metadataArray) {
  let Cue = window$1.WebKitDataCue || window$1.VTTCue;

  if (captionArray) {
    captionArray.forEach(function(caption) {
      let track = caption.stream;

      this.inbandTextTracks_[track].addCue(
        new Cue(
          caption.startTime + this.timestampOffset,
          caption.endTime + this.timestampOffset,
          caption.text
        ));
    }, sourceHandler);
  }

  if (metadataArray) {
    let videoDuration = durationOfVideo(sourceHandler.mediaSource_.duration);

    metadataArray.forEach(function(metadata) {
      let time = metadata.cueTime + this.timestampOffset;

      metadata.frames.forEach(function(frame) {
        let cue = new Cue(
          time,
          time,
          frame.value || frame.url || frame.data || '');

        cue.frame = frame;
        cue.value = frame;
        deprecateOldCue(cue);

        this.metadataTrack_.addCue(cue);
      }, this);
    }, sourceHandler);

    // Updating the metadeta cues so that
    // the endTime of each cue is the startTime of the next cue
    // the endTime of last cue is the duration of the video
    if (sourceHandler.metadataTrack_ &&
        sourceHandler.metadataTrack_.cues &&
        sourceHandler.metadataTrack_.cues.length) {
      let cues = sourceHandler.metadataTrack_.cues;
      let cuesArray = [];

      // Create a copy of the TextTrackCueList...
      // ...disregarding cues with a falsey value
      for (let i = 0; i < cues.length; i++) {
        if (cues[i]) {
          cuesArray.push(cues[i]);
        }
      }

      // Group cues by their startTime value
      let cuesGroupedByStartTime = cuesArray.reduce((obj, cue) => {
        let timeSlot = obj[cue.startTime] || [];

        timeSlot.push(cue);
        obj[cue.startTime] = timeSlot;

        return obj;
      }, {});

      // Sort startTimes by ascending order
      let sortedStartTimes = Object.keys(cuesGroupedByStartTime)
                                   .sort((a, b) => Number(a) - Number(b));

      // Map each cue group's endTime to the next group's startTime
      sortedStartTimes.forEach((startTime, idx) => {
        let cueGroup = cuesGroupedByStartTime[startTime];
        let nextTime = Number(sortedStartTimes[idx + 1]) || videoDuration;

        // Map each cue's endTime the next group's startTime
        cueGroup.forEach((cue) => {
          cue.endTime = nextTime;
        });
      });
    }
  }
};

var win = typeof window !== 'undefined' ? window : {},
    TARGET = typeof Symbol === 'undefined' ? '__target' : Symbol(),
    SCRIPT_TYPE = 'application/javascript',
    BlobBuilder = win.BlobBuilder || win.WebKitBlobBuilder || win.MozBlobBuilder || win.MSBlobBuilder,
    URL = win.URL || win.webkitURL || (URL && URL.msURL),
    Worker = win.Worker;

/**
 * Returns a wrapper around Web Worker code that is constructible.
 *
 * @function shimWorker
 *
 * @param { String }    filename    The name of the file
 * @param { Function }  fn          Function wrapping the code of the worker
 */
function shimWorker (filename, fn) {
    return function ShimWorker (forceFallback) {
        var o = this;

        if (!fn) {
            return new Worker(filename);
        }
        else if (Worker && !forceFallback) {
            // Convert the function's inner code to a string to construct the worker
            var source = fn.toString().replace(/^function.+?{/, '').slice(0, -1),
                objURL = createSourceObject(source);

            this[TARGET] = new Worker(objURL);
            wrapTerminate(this[TARGET], objURL);
            return this[TARGET];
        }
        else {
            var selfShim = {
                    postMessage: function(m) {
                        if (o.onmessage) {
                            setTimeout(function(){ o.onmessage({ data: m, target: selfShim }); });
                        }
                    }
                };

            fn.call(selfShim);
            this.postMessage = function(m) {
                setTimeout(function(){ selfShim.onmessage({ data: m, target: o }); });
            };
            this.isThisThread = true;
        }
    };
}
// Test Worker capabilities
if (Worker) {
    var testWorker,
        objURL = createSourceObject('self.onmessage = function () {}'),
        testArray = new Uint8Array(1);

    try {
        testWorker = new Worker(objURL);

        // Native browser on some Samsung devices throws for transferables, let's detect it
        testWorker.postMessage(testArray, [testArray.buffer]);
    }
    catch (e) {
        Worker = null;
    }
    finally {
        URL.revokeObjectURL(objURL);
        if (testWorker) {
            testWorker.terminate();
        }
    }
}

function createSourceObject(str) {
    try {
        return URL.createObjectURL(new Blob([str], { type: SCRIPT_TYPE }));
    }
    catch (e) {
        var blob = new BlobBuilder();
        blob.append(str);
        return URL.createObjectURL(blob.getBlob(type));
    }
}

function wrapTerminate(worker, objURL){
    if(!worker || !objURL) return;
    var term = worker.terminate;
    worker.objURL = objURL;
    worker.terminate = function(){
        if(worker.objURL)
            URL.revokeObjectURL(worker.objURL);
        term.call(worker);
    };
}

var TransmuxWorker = new shimWorker("./transmuxer-worker.worker.js", function (window, document$$1) {
var self = this;
var transmuxerWorker = (function () {

	var commonjsGlobal = typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};

	var win;

	if (typeof window !== "undefined") {
	    win = window;
	} else if (typeof commonjsGlobal !== "undefined") {
	    win = commonjsGlobal;
	} else if (typeof self !== "undefined") {
	    win = self;
	} else {
	    win = {};
	}

	var window_1 = win;

	/**
	 * mux.js
	 *
	 * Copyright (c) 2015 Brightcove
	 * All rights reserved.
	 *
	 * Functions that generate fragmented MP4s suitable for use with Media
	 * Source Extensions.
	 */

	var UINT32_MAX = Math.pow(2, 32) - 1;

	var box, dinf, esds, ftyp, mdat, mfhd, minf, moof, moov, mvex, mvhd, trak, tkhd, mdia, mdhd, hdlr, sdtp, stbl, stsd, traf, trex, trun, types, MAJOR_BRAND, MINOR_VERSION, AVC1_BRAND, VIDEO_HDLR, AUDIO_HDLR, HDLR_TYPES, VMHD, SMHD, DREF, STCO, STSC, STSZ, STTS;

	// pre-calculate constants
	(function () {
	  var i;
	  types = {
	    avc1: [], // codingname
	    avcC: [],
	    btrt: [],
	    dinf: [],
	    dref: [],
	    esds: [],
	    ftyp: [],
	    hdlr: [],
	    mdat: [],
	    mdhd: [],
	    mdia: [],
	    mfhd: [],
	    minf: [],
	    moof: [],
	    moov: [],
	    mp4a: [], // codingname
	    mvex: [],
	    mvhd: [],
	    sdtp: [],
	    smhd: [],
	    stbl: [],
	    stco: [],
	    stsc: [],
	    stsd: [],
	    stsz: [],
	    stts: [],
	    styp: [],
	    tfdt: [],
	    tfhd: [],
	    traf: [],
	    trak: [],
	    trun: [],
	    trex: [],
	    tkhd: [],
	    vmhd: []
	  };

	  // In environments where Uint8Array is undefined (e.g., IE8), skip set up so that we
	  // don't throw an error
	  if (typeof Uint8Array === 'undefined') {
	    return;
	  }

	  for (i in types) {
	    if (types.hasOwnProperty(i)) {
	      types[i] = [i.charCodeAt(0), i.charCodeAt(1), i.charCodeAt(2), i.charCodeAt(3)];
	    }
	  }

	  MAJOR_BRAND = new Uint8Array(['i'.charCodeAt(0), 's'.charCodeAt(0), 'o'.charCodeAt(0), 'm'.charCodeAt(0)]);
	  AVC1_BRAND = new Uint8Array(['a'.charCodeAt(0), 'v'.charCodeAt(0), 'c'.charCodeAt(0), '1'.charCodeAt(0)]);
	  MINOR_VERSION = new Uint8Array([0, 0, 0, 1]);
	  VIDEO_HDLR = new Uint8Array([0x00, // version 0
	  0x00, 0x00, 0x00, // flags
	  0x00, 0x00, 0x00, 0x00, // pre_defined
	  0x76, 0x69, 0x64, 0x65, // handler_type: 'vide'
	  0x00, 0x00, 0x00, 0x00, // reserved
	  0x00, 0x00, 0x00, 0x00, // reserved
	  0x00, 0x00, 0x00, 0x00, // reserved
	  0x56, 0x69, 0x64, 0x65, 0x6f, 0x48, 0x61, 0x6e, 0x64, 0x6c, 0x65, 0x72, 0x00 // name: 'VideoHandler'
	  ]);
	  AUDIO_HDLR = new Uint8Array([0x00, // version 0
	  0x00, 0x00, 0x00, // flags
	  0x00, 0x00, 0x00, 0x00, // pre_defined
	  0x73, 0x6f, 0x75, 0x6e, // handler_type: 'soun'
	  0x00, 0x00, 0x00, 0x00, // reserved
	  0x00, 0x00, 0x00, 0x00, // reserved
	  0x00, 0x00, 0x00, 0x00, // reserved
	  0x53, 0x6f, 0x75, 0x6e, 0x64, 0x48, 0x61, 0x6e, 0x64, 0x6c, 0x65, 0x72, 0x00 // name: 'SoundHandler'
	  ]);
	  HDLR_TYPES = {
	    video: VIDEO_HDLR,
	    audio: AUDIO_HDLR
	  };
	  DREF = new Uint8Array([0x00, // version 0
	  0x00, 0x00, 0x00, // flags
	  0x00, 0x00, 0x00, 0x01, // entry_count
	  0x00, 0x00, 0x00, 0x0c, // entry_size
	  0x75, 0x72, 0x6c, 0x20, // 'url' type
	  0x00, // version 0
	  0x00, 0x00, 0x01 // entry_flags
	  ]);
	  SMHD = new Uint8Array([0x00, // version
	  0x00, 0x00, 0x00, // flags
	  0x00, 0x00, // balance, 0 means centered
	  0x00, 0x00 // reserved
	  ]);
	  STCO = new Uint8Array([0x00, // version
	  0x00, 0x00, 0x00, // flags
	  0x00, 0x00, 0x00, 0x00 // entry_count
	  ]);
	  STSC = STCO;
	  STSZ = new Uint8Array([0x00, // version
	  0x00, 0x00, 0x00, // flags
	  0x00, 0x00, 0x00, 0x00, // sample_size
	  0x00, 0x00, 0x00, 0x00 // sample_count
	  ]);
	  STTS = STCO;
	  VMHD = new Uint8Array([0x00, // version
	  0x00, 0x00, 0x01, // flags
	  0x00, 0x00, // graphicsmode
	  0x00, 0x00, 0x00, 0x00, 0x00, 0x00 // opcolor
	  ]);
	})();

	box = function box(type) {
	  var payload = [],
	      size = 0,
	      i,
	      result,
	      view;

	  for (i = 1; i < arguments.length; i++) {
	    payload.push(arguments[i]);
	  }

	  i = payload.length;

	  // calculate the total size we need to allocate
	  while (i--) {
	    size += payload[i].byteLength;
	  }
	  result = new Uint8Array(size + 8);
	  view = new DataView(result.buffer, result.byteOffset, result.byteLength);
	  view.setUint32(0, result.byteLength);
	  result.set(type, 4);

	  // copy the payload into the result
	  for (i = 0, size = 8; i < payload.length; i++) {
	    result.set(payload[i], size);
	    size += payload[i].byteLength;
	  }
	  return result;
	};

	dinf = function dinf() {
	  return box(types.dinf, box(types.dref, DREF));
	};

	esds = function esds(track) {
	  return box(types.esds, new Uint8Array([0x00, // version
	  0x00, 0x00, 0x00, // flags

	  // ES_Descriptor
	  0x03, // tag, ES_DescrTag
	  0x19, // length
	  0x00, 0x00, // ES_ID
	  0x00, // streamDependenceFlag, URL_flag, reserved, streamPriority

	  // DecoderConfigDescriptor
	  0x04, // tag, DecoderConfigDescrTag
	  0x11, // length
	  0x40, // object type
	  0x15, // streamType
	  0x00, 0x06, 0x00, // bufferSizeDB
	  0x00, 0x00, 0xda, 0xc0, // maxBitrate
	  0x00, 0x00, 0xda, 0xc0, // avgBitrate

	  // DecoderSpecificInfo
	  0x05, // tag, DecoderSpecificInfoTag
	  0x02, // length
	  // ISO/IEC 14496-3, AudioSpecificConfig
	  // for samplingFrequencyIndex see ISO/IEC 13818-7:2006, 8.1.3.2.2, Table 35
	  track.audioobjecttype << 3 | track.samplingfrequencyindex >>> 1, track.samplingfrequencyindex << 7 | track.channelcount << 3, 0x06, 0x01, 0x02 // GASpecificConfig
	  ]));
	};

	ftyp = function ftyp() {
	  return box(types.ftyp, MAJOR_BRAND, MINOR_VERSION, MAJOR_BRAND, AVC1_BRAND);
	};

	hdlr = function hdlr(type) {
	  return box(types.hdlr, HDLR_TYPES[type]);
	};
	mdat = function mdat(data) {
	  return box(types.mdat, data);
	};
	mdhd = function mdhd(track) {
	  var result = new Uint8Array([0x00, // version 0
	  0x00, 0x00, 0x00, // flags
	  0x00, 0x00, 0x00, 0x02, // creation_time
	  0x00, 0x00, 0x00, 0x03, // modification_time
	  0x00, 0x01, 0x5f, 0x90, // timescale, 90,000 "ticks" per second

	  track.duration >>> 24 & 0xFF, track.duration >>> 16 & 0xFF, track.duration >>> 8 & 0xFF, track.duration & 0xFF, // duration
	  0x55, 0xc4, // 'und' language (undetermined)
	  0x00, 0x00]);

	  // Use the sample rate from the track metadata, when it is
	  // defined. The sample rate can be parsed out of an ADTS header, for
	  // instance.
	  if (track.samplerate) {
	    result[12] = track.samplerate >>> 24 & 0xFF;
	    result[13] = track.samplerate >>> 16 & 0xFF;
	    result[14] = track.samplerate >>> 8 & 0xFF;
	    result[15] = track.samplerate & 0xFF;
	  }

	  return box(types.mdhd, result);
	};
	mdia = function mdia(track) {
	  return box(types.mdia, mdhd(track), hdlr(track.type), minf(track));
	};
	mfhd = function mfhd(sequenceNumber) {
	  return box(types.mfhd, new Uint8Array([0x00, 0x00, 0x00, 0x00, // flags
	  (sequenceNumber & 0xFF000000) >> 24, (sequenceNumber & 0xFF0000) >> 16, (sequenceNumber & 0xFF00) >> 8, sequenceNumber & 0xFF // sequence_number
	  ]));
	};
	minf = function minf(track) {
	  return box(types.minf, track.type === 'video' ? box(types.vmhd, VMHD) : box(types.smhd, SMHD), dinf(), stbl(track));
	};
	moof = function moof(sequenceNumber, tracks) {
	  var trackFragments = [],
	      i = tracks.length;
	  // build traf boxes for each track fragment
	  while (i--) {
	    trackFragments[i] = traf(tracks[i]);
	  }
	  return box.apply(null, [types.moof, mfhd(sequenceNumber)].concat(trackFragments));
	};
	/**
	 * Returns a movie box.
	 * @param tracks {array} the tracks associated with this movie
	 * @see ISO/IEC 14496-12:2012(E), section 8.2.1
	 */
	moov = function moov(tracks) {
	  var i = tracks.length,
	      boxes = [];

	  while (i--) {
	    boxes[i] = trak(tracks[i]);
	  }

	  return box.apply(null, [types.moov, mvhd(0xffffffff)].concat(boxes).concat(mvex(tracks)));
	};
	mvex = function mvex(tracks) {
	  var i = tracks.length,
	      boxes = [];

	  while (i--) {
	    boxes[i] = trex(tracks[i]);
	  }
	  return box.apply(null, [types.mvex].concat(boxes));
	};
	mvhd = function mvhd(duration) {
	  var bytes = new Uint8Array([0x00, // version 0
	  0x00, 0x00, 0x00, // flags
	  0x00, 0x00, 0x00, 0x01, // creation_time
	  0x00, 0x00, 0x00, 0x02, // modification_time
	  0x00, 0x01, 0x5f, 0x90, // timescale, 90,000 "ticks" per second
	  (duration & 0xFF000000) >> 24, (duration & 0xFF0000) >> 16, (duration & 0xFF00) >> 8, duration & 0xFF, // duration
	  0x00, 0x01, 0x00, 0x00, // 1.0 rate
	  0x01, 0x00, // 1.0 volume
	  0x00, 0x00, // reserved
	  0x00, 0x00, 0x00, 0x00, // reserved
	  0x00, 0x00, 0x00, 0x00, // reserved
	  0x00, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x40, 0x00, 0x00, 0x00, // transformation: unity matrix
	  0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, // pre_defined
	  0xff, 0xff, 0xff, 0xff // next_track_ID
	  ]);
	  return box(types.mvhd, bytes);
	};

	sdtp = function sdtp(track) {
	  var samples = track.samples || [],
	      bytes = new Uint8Array(4 + samples.length),
	      flags,
	      i;

	  // leave the full box header (4 bytes) all zero

	  // write the sample table
	  for (i = 0; i < samples.length; i++) {
	    flags = samples[i].flags;

	    bytes[i + 4] = flags.dependsOn << 4 | flags.isDependedOn << 2 | flags.hasRedundancy;
	  }

	  return box(types.sdtp, bytes);
	};

	stbl = function stbl(track) {
	  return box(types.stbl, stsd(track), box(types.stts, STTS), box(types.stsc, STSC), box(types.stsz, STSZ), box(types.stco, STCO));
	};

	(function () {
	  var videoSample, audioSample;

	  stsd = function stsd(track) {

	    return box(types.stsd, new Uint8Array([0x00, // version 0
	    0x00, 0x00, 0x00, // flags
	    0x00, 0x00, 0x00, 0x01]), track.type === 'video' ? videoSample(track) : audioSample(track));
	  };

	  videoSample = function videoSample(track) {
	    var sps = track.sps || [],
	        pps = track.pps || [],
	        sequenceParameterSets = [],
	        pictureParameterSets = [],
	        i;

	    // assemble the SPSs
	    for (i = 0; i < sps.length; i++) {
	      sequenceParameterSets.push((sps[i].byteLength & 0xFF00) >>> 8);
	      sequenceParameterSets.push(sps[i].byteLength & 0xFF); // sequenceParameterSetLength
	      sequenceParameterSets = sequenceParameterSets.concat(Array.prototype.slice.call(sps[i])); // SPS
	    }

	    // assemble the PPSs
	    for (i = 0; i < pps.length; i++) {
	      pictureParameterSets.push((pps[i].byteLength & 0xFF00) >>> 8);
	      pictureParameterSets.push(pps[i].byteLength & 0xFF);
	      pictureParameterSets = pictureParameterSets.concat(Array.prototype.slice.call(pps[i]));
	    }

	    return box(types.avc1, new Uint8Array([0x00, 0x00, 0x00, 0x00, 0x00, 0x00, // reserved
	    0x00, 0x01, // data_reference_index
	    0x00, 0x00, // pre_defined
	    0x00, 0x00, // reserved
	    0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, // pre_defined
	    (track.width & 0xff00) >> 8, track.width & 0xff, // width
	    (track.height & 0xff00) >> 8, track.height & 0xff, // height
	    0x00, 0x48, 0x00, 0x00, // horizresolution
	    0x00, 0x48, 0x00, 0x00, // vertresolution
	    0x00, 0x00, 0x00, 0x00, // reserved
	    0x00, 0x01, // frame_count
	    0x13, 0x76, 0x69, 0x64, 0x65, 0x6f, 0x6a, 0x73, 0x2d, 0x63, 0x6f, 0x6e, 0x74, 0x72, 0x69, 0x62, 0x2d, 0x68, 0x6c, 0x73, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, // compressorname
	    0x00, 0x18, // depth = 24
	    0x11, 0x11 // pre_defined = -1
	    ]), box(types.avcC, new Uint8Array([0x01, // configurationVersion
	    track.profileIdc, // AVCProfileIndication
	    track.profileCompatibility, // profile_compatibility
	    track.levelIdc, // AVCLevelIndication
	    0xff // lengthSizeMinusOne, hard-coded to 4 bytes
	    ].concat([sps.length // numOfSequenceParameterSets
	    ]).concat(sequenceParameterSets).concat([pps.length // numOfPictureParameterSets
	    ]).concat(pictureParameterSets))), // "PPS"
	    box(types.btrt, new Uint8Array([0x00, 0x1c, 0x9c, 0x80, // bufferSizeDB
	    0x00, 0x2d, 0xc6, 0xc0, // maxBitrate
	    0x00, 0x2d, 0xc6, 0xc0])) // avgBitrate
	    );
	  };

	  audioSample = function audioSample(track) {
	    return box(types.mp4a, new Uint8Array([

	    // SampleEntry, ISO/IEC 14496-12
	    0x00, 0x00, 0x00, 0x00, 0x00, 0x00, // reserved
	    0x00, 0x01, // data_reference_index

	    // AudioSampleEntry, ISO/IEC 14496-12
	    0x00, 0x00, 0x00, 0x00, // reserved
	    0x00, 0x00, 0x00, 0x00, // reserved
	    (track.channelcount & 0xff00) >> 8, track.channelcount & 0xff, // channelcount

	    (track.samplesize & 0xff00) >> 8, track.samplesize & 0xff, // samplesize
	    0x00, 0x00, // pre_defined
	    0x00, 0x00, // reserved

	    (track.samplerate & 0xff00) >> 8, track.samplerate & 0xff, 0x00, 0x00 // samplerate, 16.16

	    // MP4AudioSampleEntry, ISO/IEC 14496-14
	    ]), esds(track));
	  };
	})();

	tkhd = function tkhd(track) {
	  var result = new Uint8Array([0x00, // version 0
	  0x00, 0x00, 0x07, // flags
	  0x00, 0x00, 0x00, 0x00, // creation_time
	  0x00, 0x00, 0x00, 0x00, // modification_time
	  (track.id & 0xFF000000) >> 24, (track.id & 0xFF0000) >> 16, (track.id & 0xFF00) >> 8, track.id & 0xFF, // track_ID
	  0x00, 0x00, 0x00, 0x00, // reserved
	  (track.duration & 0xFF000000) >> 24, (track.duration & 0xFF0000) >> 16, (track.duration & 0xFF00) >> 8, track.duration & 0xFF, // duration
	  0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, // reserved
	  0x00, 0x00, // layer
	  0x00, 0x00, // alternate_group
	  0x01, 0x00, // non-audio track volume
	  0x00, 0x00, // reserved
	  0x00, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x40, 0x00, 0x00, 0x00, // transformation: unity matrix
	  (track.width & 0xFF00) >> 8, track.width & 0xFF, 0x00, 0x00, // width
	  (track.height & 0xFF00) >> 8, track.height & 0xFF, 0x00, 0x00 // height
	  ]);

	  return box(types.tkhd, result);
	};

	/**
	 * Generate a track fragment (traf) box. A traf box collects metadata
	 * about tracks in a movie fragment (moof) box.
	 */
	traf = function traf(track) {
	  var trackFragmentHeader, trackFragmentDecodeTime, trackFragmentRun, sampleDependencyTable, dataOffset, upperWordBaseMediaDecodeTime, lowerWordBaseMediaDecodeTime;

	  trackFragmentHeader = box(types.tfhd, new Uint8Array([0x00, // version 0
	  0x00, 0x00, 0x3a, // flags
	  (track.id & 0xFF000000) >> 24, (track.id & 0xFF0000) >> 16, (track.id & 0xFF00) >> 8, track.id & 0xFF, // track_ID
	  0x00, 0x00, 0x00, 0x01, // sample_description_index
	  0x00, 0x00, 0x00, 0x00, // default_sample_duration
	  0x00, 0x00, 0x00, 0x00, // default_sample_size
	  0x00, 0x00, 0x00, 0x00 // default_sample_flags
	  ]));

	  upperWordBaseMediaDecodeTime = Math.floor(track.baseMediaDecodeTime / (UINT32_MAX + 1));
	  lowerWordBaseMediaDecodeTime = Math.floor(track.baseMediaDecodeTime % (UINT32_MAX + 1));

	  trackFragmentDecodeTime = box(types.tfdt, new Uint8Array([0x01, // version 1
	  0x00, 0x00, 0x00, // flags
	  // baseMediaDecodeTime
	  upperWordBaseMediaDecodeTime >>> 24 & 0xFF, upperWordBaseMediaDecodeTime >>> 16 & 0xFF, upperWordBaseMediaDecodeTime >>> 8 & 0xFF, upperWordBaseMediaDecodeTime & 0xFF, lowerWordBaseMediaDecodeTime >>> 24 & 0xFF, lowerWordBaseMediaDecodeTime >>> 16 & 0xFF, lowerWordBaseMediaDecodeTime >>> 8 & 0xFF, lowerWordBaseMediaDecodeTime & 0xFF]));

	  // the data offset specifies the number of bytes from the start of
	  // the containing moof to the first payload byte of the associated
	  // mdat
	  dataOffset = 32 + // tfhd
	  20 + // tfdt
	  8 + // traf header
	  16 + // mfhd
	  8 + // moof header
	  8; // mdat header

	  // audio tracks require less metadata
	  if (track.type === 'audio') {
	    trackFragmentRun = trun(track, dataOffset);
	    return box(types.traf, trackFragmentHeader, trackFragmentDecodeTime, trackFragmentRun);
	  }

	  // video tracks should contain an independent and disposable samples
	  // box (sdtp)
	  // generate one and adjust offsets to match
	  sampleDependencyTable = sdtp(track);
	  trackFragmentRun = trun(track, sampleDependencyTable.length + dataOffset);
	  return box(types.traf, trackFragmentHeader, trackFragmentDecodeTime, trackFragmentRun, sampleDependencyTable);
	};

	/**
	 * Generate a track box.
	 * @param track {object} a track definition
	 * @return {Uint8Array} the track box
	 */
	trak = function trak(track) {
	  track.duration = track.duration || 0xffffffff;
	  return box(types.trak, tkhd(track), mdia(track));
	};

	trex = function trex(track) {
	  var result = new Uint8Array([0x00, // version 0
	  0x00, 0x00, 0x00, // flags
	  (track.id & 0xFF000000) >> 24, (track.id & 0xFF0000) >> 16, (track.id & 0xFF00) >> 8, track.id & 0xFF, // track_ID
	  0x00, 0x00, 0x00, 0x01, // default_sample_description_index
	  0x00, 0x00, 0x00, 0x00, // default_sample_duration
	  0x00, 0x00, 0x00, 0x00, // default_sample_size
	  0x00, 0x01, 0x00, 0x01 // default_sample_flags
	  ]);
	  // the last two bytes of default_sample_flags is the sample
	  // degradation priority, a hint about the importance of this sample
	  // relative to others. Lower the degradation priority for all sample
	  // types other than video.
	  if (track.type !== 'video') {
	    result[result.length - 1] = 0x00;
	  }

	  return box(types.trex, result);
	};

	(function () {
	  var audioTrun, videoTrun, trunHeader;

	  // This method assumes all samples are uniform. That is, if a
	  // duration is present for the first sample, it will be present for
	  // all subsequent samples.
	  // see ISO/IEC 14496-12:2012, Section 8.8.8.1
	  trunHeader = function trunHeader(samples, offset) {
	    var durationPresent = 0,
	        sizePresent = 0,
	        flagsPresent = 0,
	        compositionTimeOffset = 0;

	    // trun flag constants
	    if (samples.length) {
	      if (samples[0].duration !== undefined) {
	        durationPresent = 0x1;
	      }
	      if (samples[0].size !== undefined) {
	        sizePresent = 0x2;
	      }
	      if (samples[0].flags !== undefined) {
	        flagsPresent = 0x4;
	      }
	      if (samples[0].compositionTimeOffset !== undefined) {
	        compositionTimeOffset = 0x8;
	      }
	    }

	    return [0x00, // version 0
	    0x00, durationPresent | sizePresent | flagsPresent | compositionTimeOffset, 0x01, // flags
	    (samples.length & 0xFF000000) >>> 24, (samples.length & 0xFF0000) >>> 16, (samples.length & 0xFF00) >>> 8, samples.length & 0xFF, // sample_count
	    (offset & 0xFF000000) >>> 24, (offset & 0xFF0000) >>> 16, (offset & 0xFF00) >>> 8, offset & 0xFF // data_offset
	    ];
	  };

	  videoTrun = function videoTrun(track, offset) {
	    var bytes, samples, sample, i;

	    samples = track.samples || [];
	    offset += 8 + 12 + 16 * samples.length;

	    bytes = trunHeader(samples, offset);

	    for (i = 0; i < samples.length; i++) {
	      sample = samples[i];
	      bytes = bytes.concat([(sample.duration & 0xFF000000) >>> 24, (sample.duration & 0xFF0000) >>> 16, (sample.duration & 0xFF00) >>> 8, sample.duration & 0xFF, // sample_duration
	      (sample.size & 0xFF000000) >>> 24, (sample.size & 0xFF0000) >>> 16, (sample.size & 0xFF00) >>> 8, sample.size & 0xFF, // sample_size
	      sample.flags.isLeading << 2 | sample.flags.dependsOn, sample.flags.isDependedOn << 6 | sample.flags.hasRedundancy << 4 | sample.flags.paddingValue << 1 | sample.flags.isNonSyncSample, sample.flags.degradationPriority & 0xF0 << 8, sample.flags.degradationPriority & 0x0F, // sample_flags
	      (sample.compositionTimeOffset & 0xFF000000) >>> 24, (sample.compositionTimeOffset & 0xFF0000) >>> 16, (sample.compositionTimeOffset & 0xFF00) >>> 8, sample.compositionTimeOffset & 0xFF // sample_composition_time_offset
	      ]);
	    }
	    return box(types.trun, new Uint8Array(bytes));
	  };

	  audioTrun = function audioTrun(track, offset) {
	    var bytes, samples, sample, i;

	    samples = track.samples || [];
	    offset += 8 + 12 + 8 * samples.length;

	    bytes = trunHeader(samples, offset);

	    for (i = 0; i < samples.length; i++) {
	      sample = samples[i];
	      bytes = bytes.concat([(sample.duration & 0xFF000000) >>> 24, (sample.duration & 0xFF0000) >>> 16, (sample.duration & 0xFF00) >>> 8, sample.duration & 0xFF, // sample_duration
	      (sample.size & 0xFF000000) >>> 24, (sample.size & 0xFF0000) >>> 16, (sample.size & 0xFF00) >>> 8, sample.size & 0xFF]); // sample_size
	    }

	    return box(types.trun, new Uint8Array(bytes));
	  };

	  trun = function trun(track, offset) {
	    if (track.type === 'audio') {
	      return audioTrun(track, offset);
	    }

	    return videoTrun(track, offset);
	  };
	})();

	var mp4Generator = {
	  ftyp: ftyp,
	  mdat: mdat,
	  moof: moof,
	  moov: moov,
	  initSegment: function initSegment(tracks) {
	    var fileType = ftyp(),
	        movie = moov(tracks),
	        result;

	    result = new Uint8Array(fileType.byteLength + movie.byteLength);
	    result.set(fileType);
	    result.set(movie, fileType.byteLength);
	    return result;
	  }
	};

	/**
	 * mux.js
	 *
	 * Copyright (c) 2014 Brightcove
	 * All rights reserved.
	 *
	 * A lightweight readable stream implemention that handles event dispatching.
	 * Objects that inherit from streams should call init in their constructors.
	 */

	var Stream = function Stream() {
	  this.init = function () {
	    var listeners = {};
	    /**
	     * Add a listener for a specified event type.
	     * @param type {string} the event name
	     * @param listener {function} the callback to be invoked when an event of
	     * the specified type occurs
	     */
	    this.on = function (type, listener) {
	      if (!listeners[type]) {
	        listeners[type] = [];
	      }
	      listeners[type] = listeners[type].concat(listener);
	    };
	    /**
	     * Remove a listener for a specified event type.
	     * @param type {string} the event name
	     * @param listener {function} a function previously registered for this
	     * type of event through `on`
	     */
	    this.off = function (type, listener) {
	      var index;
	      if (!listeners[type]) {
	        return false;
	      }
	      index = listeners[type].indexOf(listener);
	      listeners[type] = listeners[type].slice();
	      listeners[type].splice(index, 1);
	      return index > -1;
	    };
	    /**
	     * Trigger an event of the specified type on this stream. Any additional
	     * arguments to this function are passed as parameters to event listeners.
	     * @param type {string} the event name
	     */
	    this.trigger = function (type) {
	      var callbacks, i, length, args;
	      callbacks = listeners[type];
	      if (!callbacks) {
	        return;
	      }
	      // Slicing the arguments on every invocation of this method
	      // can add a significant amount of overhead. Avoid the
	      // intermediate object creation for the common case of a
	      // single callback argument
	      if (arguments.length === 2) {
	        length = callbacks.length;
	        for (i = 0; i < length; ++i) {
	          callbacks[i].call(this, arguments[1]);
	        }
	      } else {
	        args = [];
	        i = arguments.length;
	        for (i = 1; i < arguments.length; ++i) {
	          args.push(arguments[i]);
	        }
	        length = callbacks.length;
	        for (i = 0; i < length; ++i) {
	          callbacks[i].apply(this, args);
	        }
	      }
	    };
	    /**
	     * Destroys the stream and cleans up.
	     */
	    this.dispose = function () {
	      listeners = {};
	    };
	  };
	};

	/**
	 * Forwards all `data` events on this stream to the destination stream. The
	 * destination stream should provide a method `push` to receive the data
	 * events as they arrive.
	 * @param destination {stream} the stream that will receive all `data` events
	 * @param autoFlush {boolean} if false, we will not call `flush` on the destination
	 *                            when the current stream emits a 'done' event
	 * @see http://nodejs.org/api/stream.html#stream_readable_pipe_destination_options
	 */
	Stream.prototype.pipe = function (destination) {
	  this.on('data', function (data) {
	    destination.push(data);
	  });

	  this.on('done', function (flushSource) {
	    destination.flush(flushSource);
	  });

	  return destination;
	};

	// Default stream functions that are expected to be overridden to perform
	// actual work. These are provided by the prototype as a sort of no-op
	// implementation so that we don't have to check for their existence in the
	// `pipe` function above.
	Stream.prototype.push = function (data) {
	  this.trigger('data', data);
	};

	Stream.prototype.flush = function (flushSource) {
	  this.trigger('done', flushSource);
	};

	var stream = Stream;

	// -----------------
	// Link To Transport
	// -----------------

	// Supplemental enhancement information (SEI) NAL units have a
	// payload type field to indicate how they are to be
	// interpreted. CEAS-708 caption content is always transmitted with
	// payload type 0x04.
	var USER_DATA_REGISTERED_ITU_T_T35 = 4,
	    RBSP_TRAILING_BITS = 128;

	/**
	  * Parse a supplemental enhancement information (SEI) NAL unit.
	  * Stops parsing once a message of type ITU T T35 has been found.
	  *
	  * @param bytes {Uint8Array} the bytes of a SEI NAL unit
	  * @return {object} the parsed SEI payload
	  * @see Rec. ITU-T H.264, 7.3.2.3.1
	  */
	var parseSei = function parseSei(bytes) {
	  var i = 0,
	      result = {
	    payloadType: -1,
	    payloadSize: 0
	  },
	      payloadType = 0,
	      payloadSize = 0;

	  // go through the sei_rbsp parsing each each individual sei_message
	  while (i < bytes.byteLength) {
	    // stop once we have hit the end of the sei_rbsp
	    if (bytes[i] === RBSP_TRAILING_BITS) {
	      break;
	    }

	    // Parse payload type
	    while (bytes[i] === 0xFF) {
	      payloadType += 255;
	      i++;
	    }
	    payloadType += bytes[i++];

	    // Parse payload size
	    while (bytes[i] === 0xFF) {
	      payloadSize += 255;
	      i++;
	    }
	    payloadSize += bytes[i++];

	    // this sei_message is a 608/708 caption so save it and break
	    // there can only ever be one caption message in a frame's sei
	    if (!result.payload && payloadType === USER_DATA_REGISTERED_ITU_T_T35) {
	      result.payloadType = payloadType;
	      result.payloadSize = payloadSize;
	      result.payload = bytes.subarray(i, i + payloadSize);
	      break;
	    }

	    // skip the payload and parse the next message
	    i += payloadSize;
	    payloadType = 0;
	    payloadSize = 0;
	  }

	  return result;
	};

	// see ANSI/SCTE 128-1 (2013), section 8.1
	var parseUserData = function parseUserData(sei) {
	  // itu_t_t35_contry_code must be 181 (United States) for
	  // captions
	  if (sei.payload[0] !== 181) {
	    return null;
	  }

	  // itu_t_t35_provider_code should be 49 (ATSC) for captions
	  if ((sei.payload[1] << 8 | sei.payload[2]) !== 49) {
	    return null;
	  }

	  // the user_identifier should be "GA94" to indicate ATSC1 data
	  if (String.fromCharCode(sei.payload[3], sei.payload[4], sei.payload[5], sei.payload[6]) !== 'GA94') {
	    return null;
	  }

	  // finally, user_data_type_code should be 0x03 for caption data
	  if (sei.payload[7] !== 0x03) {
	    return null;
	  }

	  // return the user_data_type_structure and strip the trailing
	  // marker bits
	  return sei.payload.subarray(8, sei.payload.length - 1);
	};

	// see CEA-708-D, section 4.4
	var parseCaptionPackets = function parseCaptionPackets(pts, userData) {
	  var results = [],
	      i,
	      count,
	      offset,
	      data;

	  // if this is just filler, return immediately
	  if (!(userData[0] & 0x40)) {
	    return results;
	  }

	  // parse out the cc_data_1 and cc_data_2 fields
	  count = userData[0] & 0x1f;
	  for (i = 0; i < count; i++) {
	    offset = i * 3;
	    data = {
	      type: userData[offset + 2] & 0x03,
	      pts: pts
	    };

	    // capture cc data when cc_valid is 1
	    if (userData[offset + 2] & 0x04) {
	      data.ccData = userData[offset + 3] << 8 | userData[offset + 4];
	      results.push(data);
	    }
	  }
	  return results;
	};

	var CaptionStream = function CaptionStream() {

	  CaptionStream.prototype.init.call(this);

	  this.captionPackets_ = [];

	  this.ccStreams_ = [new Cea608Stream(0, 0), // eslint-disable-line no-use-before-define
	  new Cea608Stream(0, 1), // eslint-disable-line no-use-before-define
	  new Cea608Stream(1, 0), // eslint-disable-line no-use-before-define
	  new Cea608Stream(1, 1) // eslint-disable-line no-use-before-define
	  ];

	  this.reset();

	  // forward data and done events from CCs to this CaptionStream
	  this.ccStreams_.forEach(function (cc) {
	    cc.on('data', this.trigger.bind(this, 'data'));
	    cc.on('done', this.trigger.bind(this, 'done'));
	  }, this);
	};

	CaptionStream.prototype = new stream();
	CaptionStream.prototype.push = function (event) {
	  var sei, userData;

	  // only examine SEI NALs
	  if (event.nalUnitType !== 'sei_rbsp') {
	    return;
	  }

	  // parse the sei
	  sei = parseSei(event.escapedRBSP);

	  // ignore everything but user_data_registered_itu_t_t35
	  if (sei.payloadType !== USER_DATA_REGISTERED_ITU_T_T35) {
	    return;
	  }

	  // parse out the user data payload
	  userData = parseUserData(sei);

	  // ignore unrecognized userData
	  if (!userData) {
	    return;
	  }

	  // Sometimes, the same segment # will be downloaded twice. To stop the
	  // caption data from being processed twice, we track the latest dts we've
	  // received and ignore everything with a dts before that. However, since
	  // data for a specific dts can be split across packets on either side of
	  // a segment boundary, we need to make sure we *don't* ignore the packets
	  // from the *next* segment that have dts === this.latestDts_. By constantly
	  // tracking the number of packets received with dts === this.latestDts_, we
	  // know how many should be ignored once we start receiving duplicates.
	  if (event.dts < this.latestDts_) {
	    // We've started getting older data, so set the flag.
	    this.ignoreNextEqualDts_ = true;
	    return;
	  } else if (event.dts === this.latestDts_ && this.ignoreNextEqualDts_) {
	    this.numSameDts_--;
	    if (!this.numSameDts_) {
	      // We've received the last duplicate packet, time to start processing again
	      this.ignoreNextEqualDts_ = false;
	    }
	    return;
	  }

	  // parse out CC data packets and save them for later
	  this.captionPackets_ = this.captionPackets_.concat(parseCaptionPackets(event.pts, userData));
	  if (this.latestDts_ !== event.dts) {
	    this.numSameDts_ = 0;
	  }
	  this.numSameDts_++;
	  this.latestDts_ = event.dts;
	};

	CaptionStream.prototype.flush = function () {
	  // make sure we actually parsed captions before proceeding
	  if (!this.captionPackets_.length) {
	    this.ccStreams_.forEach(function (cc) {
	      cc.flush();
	    }, this);
	    return;
	  }

	  // In Chrome, the Array#sort function is not stable so add a
	  // presortIndex that we can use to ensure we get a stable-sort
	  this.captionPackets_.forEach(function (elem, idx) {
	    elem.presortIndex = idx;
	  });

	  // sort caption byte-pairs based on their PTS values
	  this.captionPackets_.sort(function (a, b) {
	    if (a.pts === b.pts) {
	      return a.presortIndex - b.presortIndex;
	    }
	    return a.pts - b.pts;
	  });

	  this.captionPackets_.forEach(function (packet) {
	    if (packet.type < 2) {
	      // Dispatch packet to the right Cea608Stream
	      this.dispatchCea608Packet(packet);
	    }
	    // this is where an 'else' would go for a dispatching packets
	    // to a theoretical Cea708Stream that handles SERVICEn data
	  }, this);

	  this.captionPackets_.length = 0;
	  this.ccStreams_.forEach(function (cc) {
	    cc.flush();
	  }, this);
	  return;
	};

	CaptionStream.prototype.reset = function () {
	  this.latestDts_ = null;
	  this.ignoreNextEqualDts_ = false;
	  this.numSameDts_ = 0;
	  this.activeCea608Channel_ = [null, null];
	  this.ccStreams_.forEach(function (ccStream) {
	    ccStream.reset();
	  });
	};

	CaptionStream.prototype.dispatchCea608Packet = function (packet) {
	  // NOTE: packet.type is the CEA608 field
	  if (this.setsChannel1Active(packet)) {
	    this.activeCea608Channel_[packet.type] = 0;
	  } else if (this.setsChannel2Active(packet)) {
	    this.activeCea608Channel_[packet.type] = 1;
	  }
	  if (this.activeCea608Channel_[packet.type] === null) {
	    // If we haven't received anything to set the active channel, discard the
	    // data; we don't want jumbled captions
	    return;
	  }
	  this.ccStreams_[(packet.type << 1) + this.activeCea608Channel_[packet.type]].push(packet);
	};

	CaptionStream.prototype.setsChannel1Active = function (packet) {
	  return (packet.ccData & 0x7800) === 0x1000;
	};
	CaptionStream.prototype.setsChannel2Active = function (packet) {
	  return (packet.ccData & 0x7800) === 0x1800;
	};

	// ----------------------
	// Session to Application
	// ----------------------

	var CHARACTER_TRANSLATION = {
	  0x2a: 0xe1, // á
	  0x5c: 0xe9, // é
	  0x5e: 0xed, // í
	  0x5f: 0xf3, // ó
	  0x60: 0xfa, // ú
	  0x7b: 0xe7, // ç
	  0x7c: 0xf7, // ÷
	  0x7d: 0xd1, // Ñ
	  0x7e: 0xf1, // ñ
	  0x7f: 0x2588, // █
	  0x0130: 0xae, // ®
	  0x0131: 0xb0, // °
	  0x0132: 0xbd, // ½
	  0x0133: 0xbf, // ¿
	  0x0134: 0x2122, // ™
	  0x0135: 0xa2, // ¢
	  0x0136: 0xa3, // £
	  0x0137: 0x266a, // ♪
	  0x0138: 0xe0, // à
	  0x0139: 0xa0, //
	  0x013a: 0xe8, // è
	  0x013b: 0xe2, // â
	  0x013c: 0xea, // ê
	  0x013d: 0xee, // î
	  0x013e: 0xf4, // ô
	  0x013f: 0xfb, // û
	  0x0220: 0xc1, // Á
	  0x0221: 0xc9, // É
	  0x0222: 0xd3, // Ó
	  0x0223: 0xda, // Ú
	  0x0224: 0xdc, // Ü
	  0x0225: 0xfc, // ü
	  0x0226: 0x2018, // ‘
	  0x0227: 0xa1, // ¡
	  0x0228: 0x2a, // *
	  0x0229: 0x27, // '
	  0x022a: 0x2014, // —
	  0x022b: 0xa9, // ©
	  0x022c: 0x2120, // ℠
	  0x022d: 0x2022, // •
	  0x022e: 0x201c, // “
	  0x022f: 0x201d, // ”
	  0x0230: 0xc0, // À
	  0x0231: 0xc2, // Â
	  0x0232: 0xc7, // Ç
	  0x0233: 0xc8, // È
	  0x0234: 0xca, // Ê
	  0x0235: 0xcb, // Ë
	  0x0236: 0xeb, // ë
	  0x0237: 0xce, // Î
	  0x0238: 0xcf, // Ï
	  0x0239: 0xef, // ï
	  0x023a: 0xd4, // Ô
	  0x023b: 0xd9, // Ù
	  0x023c: 0xf9, // ù
	  0x023d: 0xdb, // Û
	  0x023e: 0xab, // «
	  0x023f: 0xbb, // »
	  0x0320: 0xc3, // Ã
	  0x0321: 0xe3, // ã
	  0x0322: 0xcd, // Í
	  0x0323: 0xcc, // Ì
	  0x0324: 0xec, // ì
	  0x0325: 0xd2, // Ò
	  0x0326: 0xf2, // ò
	  0x0327: 0xd5, // Õ
	  0x0328: 0xf5, // õ
	  0x0329: 0x7b, // {
	  0x032a: 0x7d, // }
	  0x032b: 0x5c, // \
	  0x032c: 0x5e, // ^
	  0x032d: 0x5f, // _
	  0x032e: 0x7c, // |
	  0x032f: 0x7e, // ~
	  0x0330: 0xc4, // Ä
	  0x0331: 0xe4, // ä
	  0x0332: 0xd6, // Ö
	  0x0333: 0xf6, // ö
	  0x0334: 0xdf, // ß
	  0x0335: 0xa5, // ¥
	  0x0336: 0xa4, // ¤
	  0x0337: 0x2502, // │
	  0x0338: 0xc5, // Å
	  0x0339: 0xe5, // å
	  0x033a: 0xd8, // Ø
	  0x033b: 0xf8, // ø
	  0x033c: 0x250c, // ┌
	  0x033d: 0x2510, // ┐
	  0x033e: 0x2514, // └
	  0x033f: 0x2518 // ┘
	};

	var getCharFromCode = function getCharFromCode(code) {
	  if (code === null) {
	    return '';
	  }
	  code = CHARACTER_TRANSLATION[code] || code;
	  return String.fromCharCode(code);
	};

	// the index of the last row in a CEA-608 display buffer
	var BOTTOM_ROW = 14;

	// This array is used for mapping PACs -> row #, since there's no way of
	// getting it through bit logic.
	var ROWS = [0x1100, 0x1120, 0x1200, 0x1220, 0x1500, 0x1520, 0x1600, 0x1620, 0x1700, 0x1720, 0x1000, 0x1300, 0x1320, 0x1400, 0x1420];

	// CEA-608 captions are rendered onto a 34x15 matrix of character
	// cells. The "bottom" row is the last element in the outer array.
	var createDisplayBuffer = function createDisplayBuffer() {
	  var result = [],
	      i = BOTTOM_ROW + 1;
	  while (i--) {
	    result.push('');
	  }
	  return result;
	};

	var Cea608Stream = function Cea608Stream(field, dataChannel) {
	  Cea608Stream.prototype.init.call(this);

	  this.field_ = field || 0;
	  this.dataChannel_ = dataChannel || 0;

	  this.name_ = 'CC' + ((this.field_ << 1 | this.dataChannel_) + 1);

	  this.setConstants();
	  this.reset();

	  this.push = function (packet) {
	    var data, swap, char0, char1, text;
	    // remove the parity bits
	    data = packet.ccData & 0x7f7f;

	    // ignore duplicate control codes; the spec demands they're sent twice
	    if (data === this.lastControlCode_) {
	      this.lastControlCode_ = null;
	      return;
	    }

	    // Store control codes
	    if ((data & 0xf000) === 0x1000) {
	      this.lastControlCode_ = data;
	    } else if (data !== this.PADDING_) {
	      this.lastControlCode_ = null;
	    }

	    char0 = data >>> 8;
	    char1 = data & 0xff;

	    if (data === this.PADDING_) {
	      return;
	    } else if (data === this.RESUME_CAPTION_LOADING_) {
	      this.mode_ = 'popOn';
	    } else if (data === this.END_OF_CAPTION_) {
	      this.clearFormatting(packet.pts);
	      // if a caption was being displayed, it's gone now
	      this.flushDisplayed(packet.pts);

	      // flip memory
	      swap = this.displayed_;
	      this.displayed_ = this.nonDisplayed_;
	      this.nonDisplayed_ = swap;

	      // start measuring the time to display the caption
	      this.startPts_ = packet.pts;
	    } else if (data === this.ROLL_UP_2_ROWS_) {
	      this.topRow_ = BOTTOM_ROW - 1;
	      this.mode_ = 'rollUp';
	    } else if (data === this.ROLL_UP_3_ROWS_) {
	      this.topRow_ = BOTTOM_ROW - 2;
	      this.mode_ = 'rollUp';
	    } else if (data === this.ROLL_UP_4_ROWS_) {
	      this.topRow_ = BOTTOM_ROW - 3;
	      this.mode_ = 'rollUp';
	    } else if (data === this.CARRIAGE_RETURN_) {
	      this.clearFormatting(packet.pts);
	      this.flushDisplayed(packet.pts);
	      this.shiftRowsUp_();
	      this.startPts_ = packet.pts;
	    } else if (data === this.BACKSPACE_) {
	      if (this.mode_ === 'popOn') {
	        this.nonDisplayed_[BOTTOM_ROW] = this.nonDisplayed_[BOTTOM_ROW].slice(0, -1);
	      } else {
	        this.displayed_[BOTTOM_ROW] = this.displayed_[BOTTOM_ROW].slice(0, -1);
	      }
	    } else if (data === this.ERASE_DISPLAYED_MEMORY_) {
	      this.flushDisplayed(packet.pts);
	      this.displayed_ = createDisplayBuffer();
	    } else if (data === this.ERASE_NON_DISPLAYED_MEMORY_) {
	      this.nonDisplayed_ = createDisplayBuffer();
	    } else if (data === this.RESUME_DIRECT_CAPTIONING_) {
	      this.mode_ = 'paintOn';

	      // Append special characters to caption text
	    } else if (this.isSpecialCharacter(char0, char1)) {
	      // Bitmask char0 so that we can apply character transformations
	      // regardless of field and data channel.
	      // Then byte-shift to the left and OR with char1 so we can pass the
	      // entire character code to `getCharFromCode`.
	      char0 = (char0 & 0x03) << 8;
	      text = getCharFromCode(char0 | char1);
	      this[this.mode_](packet.pts, text);
	      this.column_++;

	      // Append extended characters to caption text
	    } else if (this.isExtCharacter(char0, char1)) {
	      // Extended characters always follow their "non-extended" equivalents.
	      // IE if a "è" is desired, you'll always receive "eè"; non-compliant
	      // decoders are supposed to drop the "è", while compliant decoders
	      // backspace the "e" and insert "è".

	      // Delete the previous character
	      if (this.mode_ === 'popOn') {
	        this.nonDisplayed_[this.row_] = this.nonDisplayed_[this.row_].slice(0, -1);
	      } else {
	        this.displayed_[BOTTOM_ROW] = this.displayed_[BOTTOM_ROW].slice(0, -1);
	      }

	      // Bitmask char0 so that we can apply character transformations
	      // regardless of field and data channel.
	      // Then byte-shift to the left and OR with char1 so we can pass the
	      // entire character code to `getCharFromCode`.
	      char0 = (char0 & 0x03) << 8;
	      text = getCharFromCode(char0 | char1);
	      this[this.mode_](packet.pts, text);
	      this.column_++;

	      // Process mid-row codes
	    } else if (this.isMidRowCode(char0, char1)) {
	      // Attributes are not additive, so clear all formatting
	      this.clearFormatting(packet.pts);

	      // According to the standard, mid-row codes
	      // should be replaced with spaces, so add one now
	      this[this.mode_](packet.pts, ' ');
	      this.column_++;

	      if ((char1 & 0xe) === 0xe) {
	        this.addFormatting(packet.pts, ['i']);
	      }

	      if ((char1 & 0x1) === 0x1) {
	        this.addFormatting(packet.pts, ['u']);
	      }

	      // Detect offset control codes and adjust cursor
	    } else if (this.isOffsetControlCode(char0, char1)) {
	      // Cursor position is set by indent PAC (see below) in 4-column
	      // increments, with an additional offset code of 1-3 to reach any
	      // of the 32 columns specified by CEA-608. So all we need to do
	      // here is increment the column cursor by the given offset.
	      this.column_ += char1 & 0x03;

	      // Detect PACs (Preamble Address Codes)
	    } else if (this.isPAC(char0, char1)) {

	      // There's no logic for PAC -> row mapping, so we have to just
	      // find the row code in an array and use its index :(
	      var row = ROWS.indexOf(data & 0x1f20);

	      if (row !== this.row_) {
	        // formatting is only persistent for current row
	        this.clearFormatting(packet.pts);
	        this.row_ = row;
	      }
	      // All PACs can apply underline, so detect and apply
	      // (All odd-numbered second bytes set underline)
	      if (char1 & 0x1 && this.formatting_.indexOf('u') === -1) {
	        this.addFormatting(packet.pts, ['u']);
	      }

	      if ((data & 0x10) === 0x10) {
	        // We've got an indent level code. Each successive even number
	        // increments the column cursor by 4, so we can get the desired
	        // column position by bit-shifting to the right (to get n/2)
	        // and multiplying by 4.
	        this.column_ = ((data & 0xe) >> 1) * 4;
	      }

	      if (this.isColorPAC(char1)) {
	        // it's a color code, though we only support white, which
	        // can be either normal or italicized. white italics can be
	        // either 0x4e or 0x6e depending on the row, so we just
	        // bitwise-and with 0xe to see if italics should be turned on
	        if ((char1 & 0xe) === 0xe) {
	          this.addFormatting(packet.pts, ['i']);
	        }
	      }

	      // We have a normal character in char0, and possibly one in char1
	    } else if (this.isNormalChar(char0)) {
	      if (char1 === 0x00) {
	        char1 = null;
	      }
	      text = getCharFromCode(char0);
	      text += getCharFromCode(char1);
	      this[this.mode_](packet.pts, text);
	      this.column_ += text.length;
	    } // finish data processing
	  };
	};
	Cea608Stream.prototype = new stream();
	// Trigger a cue point that captures the current state of the
	// display buffer
	Cea608Stream.prototype.flushDisplayed = function (pts) {
	  var content = this.displayed_
	  // remove spaces from the start and end of the string
	  .map(function (row) {
	    return row.trim();
	  })
	  // combine all text rows to display in one cue
	  .join('\n')
	  // and remove blank rows from the start and end, but not the middle
	  .replace(/^\n+|\n+$/g, '');

	  if (content.length) {
	    this.trigger('data', {
	      startPts: this.startPts_,
	      endPts: pts,
	      text: content,
	      stream: this.name_
	    });
	  }
	};

	/**
	 * Zero out the data, used for startup and on seek
	 */
	Cea608Stream.prototype.reset = function () {
	  this.mode_ = 'popOn';
	  // When in roll-up mode, the index of the last row that will
	  // actually display captions. If a caption is shifted to a row
	  // with a lower index than this, it is cleared from the display
	  // buffer
	  this.topRow_ = 0;
	  this.startPts_ = 0;
	  this.displayed_ = createDisplayBuffer();
	  this.nonDisplayed_ = createDisplayBuffer();
	  this.lastControlCode_ = null;

	  // Track row and column for proper line-breaking and spacing
	  this.column_ = 0;
	  this.row_ = BOTTOM_ROW;

	  // This variable holds currently-applied formatting
	  this.formatting_ = [];
	};

	/**
	 * Sets up control code and related constants for this instance
	 */
	Cea608Stream.prototype.setConstants = function () {
	  // The following attributes have these uses:
	  // ext_ :    char0 for mid-row codes, and the base for extended
	  //           chars (ext_+0, ext_+1, and ext_+2 are char0s for
	  //           extended codes)
	  // control_: char0 for control codes, except byte-shifted to the
	  //           left so that we can do this.control_ | CONTROL_CODE
	  // offset_:  char0 for tab offset codes
	  //
	  // It's also worth noting that control codes, and _only_ control codes,
	  // differ between field 1 and field2. Field 2 control codes are always
	  // their field 1 value plus 1. That's why there's the "| field" on the
	  // control value.
	  if (this.dataChannel_ === 0) {
	    this.BASE_ = 0x10;
	    this.EXT_ = 0x11;
	    this.CONTROL_ = (0x14 | this.field_) << 8;
	    this.OFFSET_ = 0x17;
	  } else if (this.dataChannel_ === 1) {
	    this.BASE_ = 0x18;
	    this.EXT_ = 0x19;
	    this.CONTROL_ = (0x1c | this.field_) << 8;
	    this.OFFSET_ = 0x1f;
	  }

	  // Constants for the LSByte command codes recognized by Cea608Stream. This
	  // list is not exhaustive. For a more comprehensive listing and semantics see
	  // http://www.gpo.gov/fdsys/pkg/CFR-2010-title47-vol1/pdf/CFR-2010-title47-vol1-sec15-119.pdf
	  // Padding
	  this.PADDING_ = 0x0000;
	  // Pop-on Mode
	  this.RESUME_CAPTION_LOADING_ = this.CONTROL_ | 0x20;
	  this.END_OF_CAPTION_ = this.CONTROL_ | 0x2f;
	  // Roll-up Mode
	  this.ROLL_UP_2_ROWS_ = this.CONTROL_ | 0x25;
	  this.ROLL_UP_3_ROWS_ = this.CONTROL_ | 0x26;
	  this.ROLL_UP_4_ROWS_ = this.CONTROL_ | 0x27;
	  this.CARRIAGE_RETURN_ = this.CONTROL_ | 0x2d;
	  // paint-on mode (not supported)
	  this.RESUME_DIRECT_CAPTIONING_ = this.CONTROL_ | 0x29;
	  // Erasure
	  this.BACKSPACE_ = this.CONTROL_ | 0x21;
	  this.ERASE_DISPLAYED_MEMORY_ = this.CONTROL_ | 0x2c;
	  this.ERASE_NON_DISPLAYED_MEMORY_ = this.CONTROL_ | 0x2e;
	};

	/**
	 * Detects if the 2-byte packet data is a special character
	 *
	 * Special characters have a second byte in the range 0x30 to 0x3f,
	 * with the first byte being 0x11 (for data channel 1) or 0x19 (for
	 * data channel 2).
	 *
	 * @param  {Integer} char0 The first byte
	 * @param  {Integer} char1 The second byte
	 * @return {Boolean}       Whether the 2 bytes are an special character
	 */
	Cea608Stream.prototype.isSpecialCharacter = function (char0, char1) {
	  return char0 === this.EXT_ && char1 >= 0x30 && char1 <= 0x3f;
	};

	/**
	 * Detects if the 2-byte packet data is an extended character
	 *
	 * Extended characters have a second byte in the range 0x20 to 0x3f,
	 * with the first byte being 0x12 or 0x13 (for data channel 1) or
	 * 0x1a or 0x1b (for data channel 2).
	 *
	 * @param  {Integer} char0 The first byte
	 * @param  {Integer} char1 The second byte
	 * @return {Boolean}       Whether the 2 bytes are an extended character
	 */
	Cea608Stream.prototype.isExtCharacter = function (char0, char1) {
	  return (char0 === this.EXT_ + 1 || char0 === this.EXT_ + 2) && char1 >= 0x20 && char1 <= 0x3f;
	};

	/**
	 * Detects if the 2-byte packet is a mid-row code
	 *
	 * Mid-row codes have a second byte in the range 0x20 to 0x2f, with
	 * the first byte being 0x11 (for data channel 1) or 0x19 (for data
	 * channel 2).
	 *
	 * @param  {Integer} char0 The first byte
	 * @param  {Integer} char1 The second byte
	 * @return {Boolean}       Whether the 2 bytes are a mid-row code
	 */
	Cea608Stream.prototype.isMidRowCode = function (char0, char1) {
	  return char0 === this.EXT_ && char1 >= 0x20 && char1 <= 0x2f;
	};

	/**
	 * Detects if the 2-byte packet is an offset control code
	 *
	 * Offset control codes have a second byte in the range 0x21 to 0x23,
	 * with the first byte being 0x17 (for data channel 1) or 0x1f (for
	 * data channel 2).
	 *
	 * @param  {Integer} char0 The first byte
	 * @param  {Integer} char1 The second byte
	 * @return {Boolean}       Whether the 2 bytes are an offset control code
	 */
	Cea608Stream.prototype.isOffsetControlCode = function (char0, char1) {
	  return char0 === this.OFFSET_ && char1 >= 0x21 && char1 <= 0x23;
	};

	/**
	 * Detects if the 2-byte packet is a Preamble Address Code
	 *
	 * PACs have a first byte in the range 0x10 to 0x17 (for data channel 1)
	 * or 0x18 to 0x1f (for data channel 2), with the second byte in the
	 * range 0x40 to 0x7f.
	 *
	 * @param  {Integer} char0 The first byte
	 * @param  {Integer} char1 The second byte
	 * @return {Boolean}       Whether the 2 bytes are a PAC
	 */
	Cea608Stream.prototype.isPAC = function (char0, char1) {
	  return char0 >= this.BASE_ && char0 < this.BASE_ + 8 && char1 >= 0x40 && char1 <= 0x7f;
	};

	/**
	 * Detects if a packet's second byte is in the range of a PAC color code
	 *
	 * PAC color codes have the second byte be in the range 0x40 to 0x4f, or
	 * 0x60 to 0x6f.
	 *
	 * @param  {Integer} char1 The second byte
	 * @return {Boolean}       Whether the byte is a color PAC
	 */
	Cea608Stream.prototype.isColorPAC = function (char1) {
	  return char1 >= 0x40 && char1 <= 0x4f || char1 >= 0x60 && char1 <= 0x7f;
	};

	/**
	 * Detects if a single byte is in the range of a normal character
	 *
	 * Normal text bytes are in the range 0x20 to 0x7f.
	 *
	 * @param  {Integer} char  The byte
	 * @return {Boolean}       Whether the byte is a normal character
	 */
	Cea608Stream.prototype.isNormalChar = function (char) {
	  return char >= 0x20 && char <= 0x7f;
	};

	// Adds the opening HTML tag for the passed character to the caption text,
	// and keeps track of it for later closing
	Cea608Stream.prototype.addFormatting = function (pts, format) {
	  this.formatting_ = this.formatting_.concat(format);
	  var text = format.reduce(function (text, format) {
	    return text + '<' + format + '>';
	  }, '');
	  this[this.mode_](pts, text);
	};

	// Adds HTML closing tags for current formatting to caption text and
	// clears remembered formatting
	Cea608Stream.prototype.clearFormatting = function (pts) {
	  if (!this.formatting_.length) {
	    return;
	  }
	  var text = this.formatting_.reverse().reduce(function (text, format) {
	    return text + '</' + format + '>';
	  }, '');
	  this.formatting_ = [];
	  this[this.mode_](pts, text);
	};

	// Mode Implementations
	Cea608Stream.prototype.popOn = function (pts, text) {
	  var baseRow = this.nonDisplayed_[this.row_];

	  // buffer characters
	  baseRow += text;
	  this.nonDisplayed_[this.row_] = baseRow;
	};

	Cea608Stream.prototype.rollUp = function (pts, text) {
	  var baseRow = this.displayed_[BOTTOM_ROW];

	  baseRow += text;
	  this.displayed_[BOTTOM_ROW] = baseRow;
	};

	Cea608Stream.prototype.shiftRowsUp_ = function () {
	  var i;
	  // clear out inactive rows
	  for (i = 0; i < this.topRow_; i++) {
	    this.displayed_[i] = '';
	  }
	  // shift displayed rows up
	  for (i = this.topRow_; i < BOTTOM_ROW; i++) {
	    this.displayed_[i] = this.displayed_[i + 1];
	  }
	  // clear out the bottom row
	  this.displayed_[BOTTOM_ROW] = '';
	};

	// paintOn mode is not implemented
	Cea608Stream.prototype.paintOn = function () {};

	// exports
	var captionStream = {
	  CaptionStream: CaptionStream,
	  Cea608Stream: Cea608Stream
	};

	var streamTypes = {
	  H264_STREAM_TYPE: 0x1B,
	  ADTS_STREAM_TYPE: 0x0F,
	  METADATA_STREAM_TYPE: 0x15
	};

	var MAX_TS = 8589934592;

	var RO_THRESH = 4294967296;

	var handleRollover = function handleRollover(value, reference) {
	  var direction = 1;

	  if (value > reference) {
	    // If the current timestamp value is greater than our reference timestamp and we detect a
	    // timestamp rollover, this means the roll over is happening in the opposite direction.
	    // Example scenario: Enter a long stream/video just after a rollover occurred. The reference
	    // point will be set to a small number, e.g. 1. The user then seeks backwards over the
	    // rollover point. In loading this segment, the timestamp values will be very large,
	    // e.g. 2^33 - 1. Since this comes before the data we loaded previously, we want to adjust
	    // the time stamp to be `value - 2^33`.
	    direction = -1;
	  }

	  // Note: A seek forwards or back that is greater than the RO_THRESH (2^32, ~13 hours) will
	  // cause an incorrect adjustment.
	  while (Math.abs(reference - value) > RO_THRESH) {
	    value += direction * MAX_TS;
	  }

	  return value;
	};

	var TimestampRolloverStream = function TimestampRolloverStream(type) {
	  var lastDTS, referenceDTS;

	  TimestampRolloverStream.prototype.init.call(this);

	  this.type_ = type;

	  this.push = function (data) {
	    if (data.type !== this.type_) {
	      return;
	    }

	    if (referenceDTS === undefined) {
	      referenceDTS = data.dts;
	    }

	    data.dts = handleRollover(data.dts, referenceDTS);
	    data.pts = handleRollover(data.pts, referenceDTS);

	    lastDTS = data.dts;

	    this.trigger('data', data);
	  };

	  this.flush = function () {
	    referenceDTS = lastDTS;
	    this.trigger('done');
	  };

	  this.discontinuity = function () {
	    referenceDTS = void 0;
	    lastDTS = void 0;
	  };
	};

	TimestampRolloverStream.prototype = new stream();

	var timestampRolloverStream = {
	  TimestampRolloverStream: TimestampRolloverStream,
	  handleRollover: handleRollover
	};

	var percentEncode = function percentEncode(bytes, start, end) {
	  var i,
	      result = '';
	  for (i = start; i < end; i++) {
	    result += '%' + ('00' + bytes[i].toString(16)).slice(-2);
	  }
	  return result;
	},

	// return the string representation of the specified byte range,
	// interpreted as UTf-8.
	parseUtf8 = function parseUtf8(bytes, start, end) {
	  return decodeURIComponent(percentEncode(bytes, start, end));
	},

	// return the string representation of the specified byte range,
	// interpreted as ISO-8859-1.
	parseIso88591 = function parseIso88591(bytes, start, end) {
	  return unescape(percentEncode(bytes, start, end)); // jshint ignore:line
	},
	    parseSyncSafeInteger = function parseSyncSafeInteger(data) {
	  return data[0] << 21 | data[1] << 14 | data[2] << 7 | data[3];
	},
	    tagParsers = {
	  TXXX: function TXXX(tag) {
	    var i;
	    if (tag.data[0] !== 3) {
	      // ignore frames with unrecognized character encodings
	      return;
	    }

	    for (i = 1; i < tag.data.length; i++) {
	      if (tag.data[i] === 0) {
	        // parse the text fields
	        tag.description = parseUtf8(tag.data, 1, i);
	        // do not include the null terminator in the tag value
	        tag.value = parseUtf8(tag.data, i + 1, tag.data.length).replace(/\0*$/, '');
	        break;
	      }
	    }
	    tag.data = tag.value;
	  },
	  WXXX: function WXXX(tag) {
	    var i;
	    if (tag.data[0] !== 3) {
	      // ignore frames with unrecognized character encodings
	      return;
	    }

	    for (i = 1; i < tag.data.length; i++) {
	      if (tag.data[i] === 0) {
	        // parse the description and URL fields
	        tag.description = parseUtf8(tag.data, 1, i);
	        tag.url = parseUtf8(tag.data, i + 1, tag.data.length);
	        break;
	      }
	    }
	  },
	  PRIV: function PRIV(tag) {
	    var i;

	    for (i = 0; i < tag.data.length; i++) {
	      if (tag.data[i] === 0) {
	        // parse the description and URL fields
	        tag.owner = parseIso88591(tag.data, 0, i);
	        break;
	      }
	    }
	    tag.privateData = tag.data.subarray(i + 1);
	    tag.data = tag.privateData;
	  }
	},
	    _MetadataStream;

	_MetadataStream = function MetadataStream(options) {
	  var settings = {
	    debug: !!(options && options.debug),

	    // the bytes of the program-level descriptor field in MP2T
	    // see ISO/IEC 13818-1:2013 (E), section 2.6 "Program and
	    // program element descriptors"
	    descriptor: options && options.descriptor
	  },

	  // the total size in bytes of the ID3 tag being parsed
	  tagSize = 0,

	  // tag data that is not complete enough to be parsed
	  buffer = [],

	  // the total number of bytes currently in the buffer
	  bufferSize = 0,
	      i;

	  _MetadataStream.prototype.init.call(this);

	  // calculate the text track in-band metadata track dispatch type
	  // https://html.spec.whatwg.org/multipage/embedded-content.html#steps-to-expose-a-media-resource-specific-text-track
	  this.dispatchType = streamTypes.METADATA_STREAM_TYPE.toString(16);
	  if (settings.descriptor) {
	    for (i = 0; i < settings.descriptor.length; i++) {
	      this.dispatchType += ('00' + settings.descriptor[i].toString(16)).slice(-2);
	    }
	  }

	  this.push = function (chunk) {
	    var tag, frameStart, frameSize, frame, i, frameHeader;
	    if (chunk.type !== 'timed-metadata') {
	      return;
	    }

	    // if data_alignment_indicator is set in the PES header,
	    // we must have the start of a new ID3 tag. Assume anything
	    // remaining in the buffer was malformed and throw it out
	    if (chunk.dataAlignmentIndicator) {
	      bufferSize = 0;
	      buffer.length = 0;
	    }

	    // ignore events that don't look like ID3 data
	    if (buffer.length === 0 && (chunk.data.length < 10 || chunk.data[0] !== 'I'.charCodeAt(0) || chunk.data[1] !== 'D'.charCodeAt(0) || chunk.data[2] !== '3'.charCodeAt(0))) {
	      if (settings.debug) {
	        // eslint-disable-next-line no-console
	        console.log('Skipping unrecognized metadata packet');
	      }
	      return;
	    }

	    // add this chunk to the data we've collected so far

	    buffer.push(chunk);
	    bufferSize += chunk.data.byteLength;

	    // grab the size of the entire frame from the ID3 header
	    if (buffer.length === 1) {
	      // the frame size is transmitted as a 28-bit integer in the
	      // last four bytes of the ID3 header.
	      // The most significant bit of each byte is dropped and the
	      // results concatenated to recover the actual value.
	      tagSize = parseSyncSafeInteger(chunk.data.subarray(6, 10));

	      // ID3 reports the tag size excluding the header but it's more
	      // convenient for our comparisons to include it
	      tagSize += 10;
	    }

	    // if the entire frame has not arrived, wait for more data
	    if (bufferSize < tagSize) {
	      return;
	    }

	    // collect the entire frame so it can be parsed
	    tag = {
	      data: new Uint8Array(tagSize),
	      frames: [],
	      pts: buffer[0].pts,
	      dts: buffer[0].dts
	    };
	    for (i = 0; i < tagSize;) {
	      tag.data.set(buffer[0].data.subarray(0, tagSize - i), i);
	      i += buffer[0].data.byteLength;
	      bufferSize -= buffer[0].data.byteLength;
	      buffer.shift();
	    }

	    // find the start of the first frame and the end of the tag
	    frameStart = 10;
	    if (tag.data[5] & 0x40) {
	      // advance the frame start past the extended header
	      frameStart += 4; // header size field
	      frameStart += parseSyncSafeInteger(tag.data.subarray(10, 14));

	      // clip any padding off the end
	      tagSize -= parseSyncSafeInteger(tag.data.subarray(16, 20));
	    }

	    // parse one or more ID3 frames
	    // http://id3.org/id3v2.3.0#ID3v2_frame_overview
	    do {
	      // determine the number of bytes in this frame
	      frameSize = parseSyncSafeInteger(tag.data.subarray(frameStart + 4, frameStart + 8));
	      if (frameSize < 1) {
	        // eslint-disable-next-line no-console
	        return console.log('Malformed ID3 frame encountered. Skipping metadata parsing.');
	      }
	      frameHeader = String.fromCharCode(tag.data[frameStart], tag.data[frameStart + 1], tag.data[frameStart + 2], tag.data[frameStart + 3]);

	      frame = {
	        id: frameHeader,
	        data: tag.data.subarray(frameStart + 10, frameStart + frameSize + 10)
	      };
	      frame.key = frame.id;
	      if (tagParsers[frame.id]) {
	        tagParsers[frame.id](frame);

	        // handle the special PRIV frame used to indicate the start
	        // time for raw AAC data
	        if (frame.owner === 'com.apple.streaming.transportStreamTimestamp') {
	          var d = frame.data,
	              size = (d[3] & 0x01) << 30 | d[4] << 22 | d[5] << 14 | d[6] << 6 | d[7] >>> 2;

	          size *= 4;
	          size += d[7] & 0x03;
	          frame.timeStamp = size;
	          // in raw AAC, all subsequent data will be timestamped based
	          // on the value of this frame
	          // we couldn't have known the appropriate pts and dts before
	          // parsing this ID3 tag so set those values now
	          if (tag.pts === undefined && tag.dts === undefined) {
	            tag.pts = frame.timeStamp;
	            tag.dts = frame.timeStamp;
	          }
	          this.trigger('timestamp', frame);
	        }
	      }
	      tag.frames.push(frame);

	      frameStart += 10; // advance past the frame header
	      frameStart += frameSize; // advance past the frame body
	    } while (frameStart < tagSize);
	    this.trigger('data', tag);
	  };
	};
	_MetadataStream.prototype = new stream();

	var metadataStream = _MetadataStream;

	var TimestampRolloverStream$1 = timestampRolloverStream.TimestampRolloverStream;

	// object types
	var _TransportPacketStream, _TransportParseStream, _ElementaryStream;

	// constants
	var MP2T_PACKET_LENGTH = 188,
	    // bytes
	SYNC_BYTE = 0x47;

	/**
	 * Splits an incoming stream of binary data into MPEG-2 Transport
	 * Stream packets.
	 */
	_TransportPacketStream = function TransportPacketStream() {
	  var buffer = new Uint8Array(MP2T_PACKET_LENGTH),
	      bytesInBuffer = 0;

	  _TransportPacketStream.prototype.init.call(this);

	  // Deliver new bytes to the stream.

	  this.push = function (bytes) {
	    var startIndex = 0,
	        endIndex = MP2T_PACKET_LENGTH,
	        everything;

	    // If there are bytes remaining from the last segment, prepend them to the
	    // bytes that were pushed in
	    if (bytesInBuffer) {
	      everything = new Uint8Array(bytes.byteLength + bytesInBuffer);
	      everything.set(buffer.subarray(0, bytesInBuffer));
	      everything.set(bytes, bytesInBuffer);
	      bytesInBuffer = 0;
	    } else {
	      everything = bytes;
	    }

	    // While we have enough data for a packet
	    while (endIndex < everything.byteLength) {
	      // Look for a pair of start and end sync bytes in the data..
	      if (everything[startIndex] === SYNC_BYTE && everything[endIndex] === SYNC_BYTE) {
	        // We found a packet so emit it and jump one whole packet forward in
	        // the stream
	        this.trigger('data', everything.subarray(startIndex, endIndex));
	        startIndex += MP2T_PACKET_LENGTH;
	        endIndex += MP2T_PACKET_LENGTH;
	        continue;
	      }
	      // If we get here, we have somehow become de-synchronized and we need to step
	      // forward one byte at a time until we find a pair of sync bytes that denote
	      // a packet
	      startIndex++;
	      endIndex++;
	    }

	    // If there was some data left over at the end of the segment that couldn't
	    // possibly be a whole packet, keep it because it might be the start of a packet
	    // that continues in the next segment
	    if (startIndex < everything.byteLength) {
	      buffer.set(everything.subarray(startIndex), 0);
	      bytesInBuffer = everything.byteLength - startIndex;
	    }
	  };

	  this.flush = function () {
	    // If the buffer contains a whole packet when we are being flushed, emit it
	    // and empty the buffer. Otherwise hold onto the data because it may be
	    // important for decoding the next segment
	    if (bytesInBuffer === MP2T_PACKET_LENGTH && buffer[0] === SYNC_BYTE) {
	      this.trigger('data', buffer);
	      bytesInBuffer = 0;
	    }
	    this.trigger('done');
	  };
	};
	_TransportPacketStream.prototype = new stream();

	/**
	 * Accepts an MP2T TransportPacketStream and emits data events with parsed
	 * forms of the individual transport stream packets.
	 */
	_TransportParseStream = function TransportParseStream() {
	  var parsePsi, parsePat, parsePmt, self;
	  _TransportParseStream.prototype.init.call(this);
	  self = this;

	  this.packetsWaitingForPmt = [];
	  this.programMapTable = undefined;

	  parsePsi = function parsePsi(payload, psi) {
	    var offset = 0;

	    // PSI packets may be split into multiple sections and those
	    // sections may be split into multiple packets. If a PSI
	    // section starts in this packet, the payload_unit_start_indicator
	    // will be true and the first byte of the payload will indicate
	    // the offset from the current position to the start of the
	    // section.
	    if (psi.payloadUnitStartIndicator) {
	      offset += payload[offset] + 1;
	    }

	    if (psi.type === 'pat') {
	      parsePat(payload.subarray(offset), psi);
	    } else {
	      parsePmt(payload.subarray(offset), psi);
	    }
	  };

	  parsePat = function parsePat(payload, pat) {
	    pat.section_number = payload[7]; // eslint-disable-line camelcase
	    pat.last_section_number = payload[8]; // eslint-disable-line camelcase

	    // skip the PSI header and parse the first PMT entry
	    self.pmtPid = (payload[10] & 0x1F) << 8 | payload[11];
	    pat.pmtPid = self.pmtPid;
	  };

	  /**
	   * Parse out the relevant fields of a Program Map Table (PMT).
	   * @param payload {Uint8Array} the PMT-specific portion of an MP2T
	   * packet. The first byte in this array should be the table_id
	   * field.
	   * @param pmt {object} the object that should be decorated with
	   * fields parsed from the PMT.
	   */
	  parsePmt = function parsePmt(payload, pmt) {
	    var sectionLength, tableEnd, programInfoLength, offset;

	    // PMTs can be sent ahead of the time when they should actually
	    // take effect. We don't believe this should ever be the case
	    // for HLS but we'll ignore "forward" PMT declarations if we see
	    // them. Future PMT declarations have the current_next_indicator
	    // set to zero.
	    if (!(payload[5] & 0x01)) {
	      return;
	    }

	    // overwrite any existing program map table
	    self.programMapTable = {
	      video: null,
	      audio: null,
	      'timed-metadata': {}
	    };

	    // the mapping table ends at the end of the current section
	    sectionLength = (payload[1] & 0x0f) << 8 | payload[2];
	    tableEnd = 3 + sectionLength - 4;

	    // to determine where the table is, we have to figure out how
	    // long the program info descriptors are
	    programInfoLength = (payload[10] & 0x0f) << 8 | payload[11];

	    // advance the offset to the first entry in the mapping table
	    offset = 12 + programInfoLength;
	    while (offset < tableEnd) {
	      var streamType = payload[offset];
	      var pid = (payload[offset + 1] & 0x1F) << 8 | payload[offset + 2];

	      // only map a single elementary_pid for audio and video stream types
	      // TODO: should this be done for metadata too? for now maintain behavior of
	      //       multiple metadata streams
	      if (streamType === streamTypes.H264_STREAM_TYPE && self.programMapTable.video === null) {
	        self.programMapTable.video = pid;
	      } else if (streamType === streamTypes.ADTS_STREAM_TYPE && self.programMapTable.audio === null) {
	        self.programMapTable.audio = pid;
	      } else if (streamType === streamTypes.METADATA_STREAM_TYPE) {
	        // map pid to stream type for metadata streams
	        self.programMapTable['timed-metadata'][pid] = streamType;
	      }

	      // move to the next table entry
	      // skip past the elementary stream descriptors, if present
	      offset += ((payload[offset + 3] & 0x0F) << 8 | payload[offset + 4]) + 5;
	    }

	    // record the map on the packet as well
	    pmt.programMapTable = self.programMapTable;
	  };

	  /**
	   * Deliver a new MP2T packet to the stream.
	   */
	  this.push = function (packet) {
	    var result = {},
	        offset = 4;

	    result.payloadUnitStartIndicator = !!(packet[1] & 0x40);

	    // pid is a 13-bit field starting at the last bit of packet[1]
	    result.pid = packet[1] & 0x1f;
	    result.pid <<= 8;
	    result.pid |= packet[2];

	    // if an adaption field is present, its length is specified by the
	    // fifth byte of the TS packet header. The adaptation field is
	    // used to add stuffing to PES packets that don't fill a complete
	    // TS packet, and to specify some forms of timing and control data
	    // that we do not currently use.
	    if ((packet[3] & 0x30) >>> 4 > 0x01) {
	      offset += packet[offset] + 1;
	    }

	    // parse the rest of the packet based on the type
	    if (result.pid === 0) {
	      result.type = 'pat';
	      parsePsi(packet.subarray(offset), result);
	      this.trigger('data', result);
	    } else if (result.pid === this.pmtPid) {
	      result.type = 'pmt';
	      parsePsi(packet.subarray(offset), result);
	      this.trigger('data', result);

	      // if there are any packets waiting for a PMT to be found, process them now
	      while (this.packetsWaitingForPmt.length) {
	        this.processPes_.apply(this, this.packetsWaitingForPmt.shift());
	      }
	    } else if (this.programMapTable === undefined) {
	      // When we have not seen a PMT yet, defer further processing of
	      // PES packets until one has been parsed
	      this.packetsWaitingForPmt.push([packet, offset, result]);
	    } else {
	      this.processPes_(packet, offset, result);
	    }
	  };

	  this.processPes_ = function (packet, offset, result) {
	    // set the appropriate stream type
	    if (result.pid === this.programMapTable.video) {
	      result.streamType = streamTypes.H264_STREAM_TYPE;
	    } else if (result.pid === this.programMapTable.audio) {
	      result.streamType = streamTypes.ADTS_STREAM_TYPE;
	    } else {
	      // if not video or audio, it is timed-metadata or unknown
	      // if unknown, streamType will be undefined
	      result.streamType = this.programMapTable['timed-metadata'][result.pid];
	    }

	    result.type = 'pes';
	    result.data = packet.subarray(offset);

	    this.trigger('data', result);
	  };
	};
	_TransportParseStream.prototype = new stream();
	_TransportParseStream.STREAM_TYPES = {
	  h264: 0x1b,
	  adts: 0x0f
	};

	/**
	 * Reconsistutes program elementary stream (PES) packets from parsed
	 * transport stream packets. That is, if you pipe an
	 * mp2t.TransportParseStream into a mp2t.ElementaryStream, the output
	 * events will be events which capture the bytes for individual PES
	 * packets plus relevant metadata that has been extracted from the
	 * container.
	 */
	_ElementaryStream = function ElementaryStream() {
	  var self = this,

	  // PES packet fragments
	  video = {
	    data: [],
	    size: 0
	  },
	      audio = {
	    data: [],
	    size: 0
	  },
	      timedMetadata = {
	    data: [],
	    size: 0
	  },
	      parsePes = function parsePes(payload, pes) {
	    var ptsDtsFlags;

	    // get the packet length, this will be 0 for video
	    pes.packetLength = 6 + (payload[4] << 8 | payload[5]);

	    // find out if this packets starts a new keyframe
	    pes.dataAlignmentIndicator = (payload[6] & 0x04) !== 0;
	    // PES packets may be annotated with a PTS value, or a PTS value
	    // and a DTS value. Determine what combination of values is
	    // available to work with.
	    ptsDtsFlags = payload[7];

	    // PTS and DTS are normally stored as a 33-bit number.  Javascript
	    // performs all bitwise operations on 32-bit integers but javascript
	    // supports a much greater range (52-bits) of integer using standard
	    // mathematical operations.
	    // We construct a 31-bit value using bitwise operators over the 31
	    // most significant bits and then multiply by 4 (equal to a left-shift
	    // of 2) before we add the final 2 least significant bits of the
	    // timestamp (equal to an OR.)
	    if (ptsDtsFlags & 0xC0) {
	      // the PTS and DTS are not written out directly. For information
	      // on how they are encoded, see
	      // http://dvd.sourceforge.net/dvdinfo/pes-hdr.html
	      pes.pts = (payload[9] & 0x0E) << 27 | (payload[10] & 0xFF) << 20 | (payload[11] & 0xFE) << 12 | (payload[12] & 0xFF) << 5 | (payload[13] & 0xFE) >>> 3;
	      pes.pts *= 4; // Left shift by 2
	      pes.pts += (payload[13] & 0x06) >>> 1; // OR by the two LSBs
	      pes.dts = pes.pts;
	      if (ptsDtsFlags & 0x40) {
	        pes.dts = (payload[14] & 0x0E) << 27 | (payload[15] & 0xFF) << 20 | (payload[16] & 0xFE) << 12 | (payload[17] & 0xFF) << 5 | (payload[18] & 0xFE) >>> 3;
	        pes.dts *= 4; // Left shift by 2
	        pes.dts += (payload[18] & 0x06) >>> 1; // OR by the two LSBs
	      }
	    }
	    // the data section starts immediately after the PES header.
	    // pes_header_data_length specifies the number of header bytes
	    // that follow the last byte of the field.
	    pes.data = payload.subarray(9 + payload[8]);
	  },
	      flushStream = function flushStream(stream$$1, type, forceFlush) {
	    var packetData = new Uint8Array(stream$$1.size),
	        event = {
	      type: type
	    },
	        i = 0,
	        offset = 0,
	        packetFlushable = false,
	        fragment;

	    // do nothing if there is not enough buffered data for a complete
	    // PES header
	    if (!stream$$1.data.length || stream$$1.size < 9) {
	      return;
	    }
	    event.trackId = stream$$1.data[0].pid;

	    // reassemble the packet
	    for (i = 0; i < stream$$1.data.length; i++) {
	      fragment = stream$$1.data[i];

	      packetData.set(fragment.data, offset);
	      offset += fragment.data.byteLength;
	    }

	    // parse assembled packet's PES header
	    parsePes(packetData, event);

	    // non-video PES packets MUST have a non-zero PES_packet_length
	    // check that there is enough stream data to fill the packet
	    packetFlushable = type === 'video' || event.packetLength <= stream$$1.size;

	    // flush pending packets if the conditions are right
	    if (forceFlush || packetFlushable) {
	      stream$$1.size = 0;
	      stream$$1.data.length = 0;
	    }

	    // only emit packets that are complete. this is to avoid assembling
	    // incomplete PES packets due to poor segmentation
	    if (packetFlushable) {
	      self.trigger('data', event);
	    }
	  };

	  _ElementaryStream.prototype.init.call(this);

	  this.push = function (data) {
	    ({
	      pat: function pat() {
	        // we have to wait for the PMT to arrive as well before we
	        // have any meaningful metadata
	      },
	      pes: function pes() {
	        var stream$$1, streamType;

	        switch (data.streamType) {
	          case streamTypes.H264_STREAM_TYPE:
	          case streamTypes.H264_STREAM_TYPE:
	            stream$$1 = video;
	            streamType = 'video';
	            break;
	          case streamTypes.ADTS_STREAM_TYPE:
	            stream$$1 = audio;
	            streamType = 'audio';
	            break;
	          case streamTypes.METADATA_STREAM_TYPE:
	            stream$$1 = timedMetadata;
	            streamType = 'timed-metadata';
	            break;
	          default:
	            // ignore unknown stream types
	            return;
	        }

	        // if a new packet is starting, we can flush the completed
	        // packet
	        if (data.payloadUnitStartIndicator) {
	          flushStream(stream$$1, streamType, true);
	        }

	        // buffer this fragment until we are sure we've received the
	        // complete payload
	        stream$$1.data.push(data);
	        stream$$1.size += data.data.byteLength;
	      },
	      pmt: function pmt() {
	        var event = {
	          type: 'metadata',
	          tracks: []
	        },
	            programMapTable = data.programMapTable;

	        // translate audio and video streams to tracks
	        if (programMapTable.video !== null) {
	          event.tracks.push({
	            timelineStartInfo: {
	              baseMediaDecodeTime: 0
	            },
	            id: +programMapTable.video,
	            codec: 'avc',
	            type: 'video'
	          });
	        }
	        if (programMapTable.audio !== null) {
	          event.tracks.push({
	            timelineStartInfo: {
	              baseMediaDecodeTime: 0
	            },
	            id: +programMapTable.audio,
	            codec: 'adts',
	            type: 'audio'
	          });
	        }

	        self.trigger('data', event);
	      }
	    })[data.type]();
	  };

	  /**
	   * Flush any remaining input. Video PES packets may be of variable
	   * length. Normally, the start of a new video packet can trigger the
	   * finalization of the previous packet. That is not possible if no
	   * more video is forthcoming, however. In that case, some other
	   * mechanism (like the end of the file) has to be employed. When it is
	   * clear that no additional data is forthcoming, calling this method
	   * will flush the buffered packets.
	   */
	  this.flush = function () {
	    // !!THIS ORDER IS IMPORTANT!!
	    // video first then audio
	    flushStream(video, 'video');
	    flushStream(audio, 'audio');
	    flushStream(timedMetadata, 'timed-metadata');
	    this.trigger('done');
	  };
	};
	_ElementaryStream.prototype = new stream();

	var m2ts = {
	  PAT_PID: 0x0000,
	  MP2T_PACKET_LENGTH: MP2T_PACKET_LENGTH,
	  TransportPacketStream: _TransportPacketStream,
	  TransportParseStream: _TransportParseStream,
	  ElementaryStream: _ElementaryStream,
	  TimestampRolloverStream: TimestampRolloverStream$1,
	  CaptionStream: captionStream.CaptionStream,
	  Cea608Stream: captionStream.Cea608Stream,
	  MetadataStream: metadataStream
	};

	for (var type in streamTypes) {
	  if (streamTypes.hasOwnProperty(type)) {
	    m2ts[type] = streamTypes[type];
	  }
	}

	var m2ts_1 = m2ts;

	var _AdtsStream;

	var ADTS_SAMPLING_FREQUENCIES = [96000, 88200, 64000, 48000, 44100, 32000, 24000, 22050, 16000, 12000, 11025, 8000, 7350];

	/*
	 * Accepts a ElementaryStream and emits data events with parsed
	 * AAC Audio Frames of the individual packets. Input audio in ADTS
	 * format is unpacked and re-emitted as AAC frames.
	 *
	 * @see http://wiki.multimedia.cx/index.php?title=ADTS
	 * @see http://wiki.multimedia.cx/?title=Understanding_AAC
	 */
	_AdtsStream = function AdtsStream() {
	  var buffer;

	  _AdtsStream.prototype.init.call(this);

	  this.push = function (packet) {
	    var i = 0,
	        frameNum = 0,
	        frameLength,
	        protectionSkipBytes,
	        frameEnd,
	        oldBuffer,
	        sampleCount,
	        adtsFrameDuration;

	    if (packet.type !== 'audio') {
	      // ignore non-audio data
	      return;
	    }

	    // Prepend any data in the buffer to the input data so that we can parse
	    // aac frames the cross a PES packet boundary
	    if (buffer) {
	      oldBuffer = buffer;
	      buffer = new Uint8Array(oldBuffer.byteLength + packet.data.byteLength);
	      buffer.set(oldBuffer);
	      buffer.set(packet.data, oldBuffer.byteLength);
	    } else {
	      buffer = packet.data;
	    }

	    // unpack any ADTS frames which have been fully received
	    // for details on the ADTS header, see http://wiki.multimedia.cx/index.php?title=ADTS
	    while (i + 5 < buffer.length) {

	      // Loook for the start of an ADTS header..
	      if (buffer[i] !== 0xFF || (buffer[i + 1] & 0xF6) !== 0xF0) {
	        // If a valid header was not found,  jump one forward and attempt to
	        // find a valid ADTS header starting at the next byte
	        i++;
	        continue;
	      }

	      // The protection skip bit tells us if we have 2 bytes of CRC data at the
	      // end of the ADTS header
	      protectionSkipBytes = (~buffer[i + 1] & 0x01) * 2;

	      // Frame length is a 13 bit integer starting 16 bits from the
	      // end of the sync sequence
	      frameLength = (buffer[i + 3] & 0x03) << 11 | buffer[i + 4] << 3 | (buffer[i + 5] & 0xe0) >> 5;

	      sampleCount = ((buffer[i + 6] & 0x03) + 1) * 1024;
	      adtsFrameDuration = sampleCount * 90000 / ADTS_SAMPLING_FREQUENCIES[(buffer[i + 2] & 0x3c) >>> 2];

	      frameEnd = i + frameLength;

	      // If we don't have enough data to actually finish this ADTS frame, return
	      // and wait for more data
	      if (buffer.byteLength < frameEnd) {
	        return;
	      }

	      // Otherwise, deliver the complete AAC frame
	      this.trigger('data', {
	        pts: packet.pts + frameNum * adtsFrameDuration,
	        dts: packet.dts + frameNum * adtsFrameDuration,
	        sampleCount: sampleCount,
	        audioobjecttype: (buffer[i + 2] >>> 6 & 0x03) + 1,
	        channelcount: (buffer[i + 2] & 1) << 2 | (buffer[i + 3] & 0xc0) >>> 6,
	        samplerate: ADTS_SAMPLING_FREQUENCIES[(buffer[i + 2] & 0x3c) >>> 2],
	        samplingfrequencyindex: (buffer[i + 2] & 0x3c) >>> 2,
	        // assume ISO/IEC 14496-12 AudioSampleEntry default of 16
	        samplesize: 16,
	        data: buffer.subarray(i + 7 + protectionSkipBytes, frameEnd)
	      });

	      // If the buffer is empty, clear it and return
	      if (buffer.byteLength === frameEnd) {
	        buffer = undefined;
	        return;
	      }

	      frameNum++;

	      // Remove the finished frame from the buffer and start the process again
	      buffer = buffer.subarray(frameEnd);
	    }
	  };
	  this.flush = function () {
	    this.trigger('done');
	  };
	};

	_AdtsStream.prototype = new stream();

	var adts = _AdtsStream;

	var ExpGolomb;

	/**
	 * Parser for exponential Golomb codes, a variable-bitwidth number encoding
	 * scheme used by h264.
	 */
	ExpGolomb = function ExpGolomb(workingData) {
	  var
	  // the number of bytes left to examine in workingData
	  workingBytesAvailable = workingData.byteLength,


	  // the current word being examined
	  workingWord = 0,
	      // :uint

	  // the number of bits left to examine in the current word
	  workingBitsAvailable = 0; // :uint;

	  // ():uint
	  this.length = function () {
	    return 8 * workingBytesAvailable;
	  };

	  // ():uint
	  this.bitsAvailable = function () {
	    return 8 * workingBytesAvailable + workingBitsAvailable;
	  };

	  // ():void
	  this.loadWord = function () {
	    var position = workingData.byteLength - workingBytesAvailable,
	        workingBytes = new Uint8Array(4),
	        availableBytes = Math.min(4, workingBytesAvailable);

	    if (availableBytes === 0) {
	      throw new Error('no bytes available');
	    }

	    workingBytes.set(workingData.subarray(position, position + availableBytes));
	    workingWord = new DataView(workingBytes.buffer).getUint32(0);

	    // track the amount of workingData that has been processed
	    workingBitsAvailable = availableBytes * 8;
	    workingBytesAvailable -= availableBytes;
	  };

	  // (count:int):void
	  this.skipBits = function (count) {
	    var skipBytes; // :int
	    if (workingBitsAvailable > count) {
	      workingWord <<= count;
	      workingBitsAvailable -= count;
	    } else {
	      count -= workingBitsAvailable;
	      skipBytes = Math.floor(count / 8);

	      count -= skipBytes * 8;
	      workingBytesAvailable -= skipBytes;

	      this.loadWord();

	      workingWord <<= count;
	      workingBitsAvailable -= count;
	    }
	  };

	  // (size:int):uint
	  this.readBits = function (size) {
	    var bits = Math.min(workingBitsAvailable, size),
	        // :uint
	    valu = workingWord >>> 32 - bits; // :uint
	    // if size > 31, handle error
	    workingBitsAvailable -= bits;
	    if (workingBitsAvailable > 0) {
	      workingWord <<= bits;
	    } else if (workingBytesAvailable > 0) {
	      this.loadWord();
	    }

	    bits = size - bits;
	    if (bits > 0) {
	      return valu << bits | this.readBits(bits);
	    }
	    return valu;
	  };

	  // ():uint
	  this.skipLeadingZeros = function () {
	    var leadingZeroCount; // :uint
	    for (leadingZeroCount = 0; leadingZeroCount < workingBitsAvailable; ++leadingZeroCount) {
	      if ((workingWord & 0x80000000 >>> leadingZeroCount) !== 0) {
	        // the first bit of working word is 1
	        workingWord <<= leadingZeroCount;
	        workingBitsAvailable -= leadingZeroCount;
	        return leadingZeroCount;
	      }
	    }

	    // we exhausted workingWord and still have not found a 1
	    this.loadWord();
	    return leadingZeroCount + this.skipLeadingZeros();
	  };

	  // ():void
	  this.skipUnsignedExpGolomb = function () {
	    this.skipBits(1 + this.skipLeadingZeros());
	  };

	  // ():void
	  this.skipExpGolomb = function () {
	    this.skipBits(1 + this.skipLeadingZeros());
	  };

	  // ():uint
	  this.readUnsignedExpGolomb = function () {
	    var clz = this.skipLeadingZeros(); // :uint
	    return this.readBits(clz + 1) - 1;
	  };

	  // ():int
	  this.readExpGolomb = function () {
	    var valu = this.readUnsignedExpGolomb(); // :int
	    if (0x01 & valu) {
	      // the number is odd if the low order bit is set
	      return 1 + valu >>> 1; // add 1 to make it even, and divide by 2
	    }
	    return -1 * (valu >>> 1); // divide by two then make it negative
	  };

	  // Some convenience functions
	  // :Boolean
	  this.readBoolean = function () {
	    return this.readBits(1) === 1;
	  };

	  // ():int
	  this.readUnsignedByte = function () {
	    return this.readBits(8);
	  };

	  this.loadWord();
	};

	var expGolomb = ExpGolomb;

	var _H264Stream, _NalByteStream;
	var PROFILES_WITH_OPTIONAL_SPS_DATA;

	/**
	 * Accepts a NAL unit byte stream and unpacks the embedded NAL units.
	 */
	_NalByteStream = function NalByteStream() {
	  var syncPoint = 0,
	      i,
	      buffer;
	  _NalByteStream.prototype.init.call(this);

	  this.push = function (data) {
	    var swapBuffer;

	    if (!buffer) {
	      buffer = data.data;
	    } else {
	      swapBuffer = new Uint8Array(buffer.byteLength + data.data.byteLength);
	      swapBuffer.set(buffer);
	      swapBuffer.set(data.data, buffer.byteLength);
	      buffer = swapBuffer;
	    }

	    // Rec. ITU-T H.264, Annex B
	    // scan for NAL unit boundaries

	    // a match looks like this:
	    // 0 0 1 .. NAL .. 0 0 1
	    // ^ sync point        ^ i
	    // or this:
	    // 0 0 1 .. NAL .. 0 0 0
	    // ^ sync point        ^ i

	    // advance the sync point to a NAL start, if necessary
	    for (; syncPoint < buffer.byteLength - 3; syncPoint++) {
	      if (buffer[syncPoint + 2] === 1) {
	        // the sync point is properly aligned
	        i = syncPoint + 5;
	        break;
	      }
	    }

	    while (i < buffer.byteLength) {
	      // look at the current byte to determine if we've hit the end of
	      // a NAL unit boundary
	      switch (buffer[i]) {
	        case 0:
	          // skip past non-sync sequences
	          if (buffer[i - 1] !== 0) {
	            i += 2;
	            break;
	          } else if (buffer[i - 2] !== 0) {
	            i++;
	            break;
	          }

	          // deliver the NAL unit if it isn't empty
	          if (syncPoint + 3 !== i - 2) {
	            this.trigger('data', buffer.subarray(syncPoint + 3, i - 2));
	          }

	          // drop trailing zeroes
	          do {
	            i++;
	          } while (buffer[i] !== 1 && i < buffer.length);
	          syncPoint = i - 2;
	          i += 3;
	          break;
	        case 1:
	          // skip past non-sync sequences
	          if (buffer[i - 1] !== 0 || buffer[i - 2] !== 0) {
	            i += 3;
	            break;
	          }

	          // deliver the NAL unit
	          this.trigger('data', buffer.subarray(syncPoint + 3, i - 2));
	          syncPoint = i - 2;
	          i += 3;
	          break;
	        default:
	          // the current byte isn't a one or zero, so it cannot be part
	          // of a sync sequence
	          i += 3;
	          break;
	      }
	    }
	    // filter out the NAL units that were delivered
	    buffer = buffer.subarray(syncPoint);
	    i -= syncPoint;
	    syncPoint = 0;
	  };

	  this.flush = function () {
	    // deliver the last buffered NAL unit
	    if (buffer && buffer.byteLength > 3) {
	      this.trigger('data', buffer.subarray(syncPoint + 3));
	    }
	    // reset the stream state
	    buffer = null;
	    syncPoint = 0;
	    this.trigger('done');
	  };
	};
	_NalByteStream.prototype = new stream();

	// values of profile_idc that indicate additional fields are included in the SPS
	// see Recommendation ITU-T H.264 (4/2013),
	// 7.3.2.1.1 Sequence parameter set data syntax
	PROFILES_WITH_OPTIONAL_SPS_DATA = {
	  100: true,
	  110: true,
	  122: true,
	  244: true,
	  44: true,
	  83: true,
	  86: true,
	  118: true,
	  128: true,
	  138: true,
	  139: true,
	  134: true
	};

	/**
	 * Accepts input from a ElementaryStream and produces H.264 NAL unit data
	 * events.
	 */
	_H264Stream = function H264Stream() {
	  var nalByteStream = new _NalByteStream(),
	      self,
	      trackId,
	      currentPts,
	      currentDts,
	      discardEmulationPreventionBytes,
	      readSequenceParameterSet,
	      skipScalingList;

	  _H264Stream.prototype.init.call(this);
	  self = this;

	  this.push = function (packet) {
	    if (packet.type !== 'video') {
	      return;
	    }
	    trackId = packet.trackId;
	    currentPts = packet.pts;
	    currentDts = packet.dts;

	    nalByteStream.push(packet);
	  };

	  nalByteStream.on('data', function (data) {
	    var event = {
	      trackId: trackId,
	      pts: currentPts,
	      dts: currentDts,
	      data: data
	    };

	    switch (data[0] & 0x1f) {
	      case 0x05:
	        event.nalUnitType = 'slice_layer_without_partitioning_rbsp_idr';
	        break;
	      case 0x06:
	        event.nalUnitType = 'sei_rbsp';
	        event.escapedRBSP = discardEmulationPreventionBytes(data.subarray(1));
	        break;
	      case 0x07:
	        event.nalUnitType = 'seq_parameter_set_rbsp';
	        event.escapedRBSP = discardEmulationPreventionBytes(data.subarray(1));
	        event.config = readSequenceParameterSet(event.escapedRBSP);
	        break;
	      case 0x08:
	        event.nalUnitType = 'pic_parameter_set_rbsp';
	        break;
	      case 0x09:
	        event.nalUnitType = 'access_unit_delimiter_rbsp';
	        break;

	      default:
	        break;
	    }
	    self.trigger('data', event);
	  });
	  nalByteStream.on('done', function () {
	    self.trigger('done');
	  });

	  this.flush = function () {
	    nalByteStream.flush();
	  };

	  /**
	   * Advance the ExpGolomb decoder past a scaling list. The scaling
	   * list is optionally transmitted as part of a sequence parameter
	   * set and is not relevant to transmuxing.
	   * @param count {number} the number of entries in this scaling list
	   * @param expGolombDecoder {object} an ExpGolomb pointed to the
	   * start of a scaling list
	   * @see Recommendation ITU-T H.264, Section 7.3.2.1.1.1
	   */
	  skipScalingList = function skipScalingList(count, expGolombDecoder) {
	    var lastScale = 8,
	        nextScale = 8,
	        j,
	        deltaScale;

	    for (j = 0; j < count; j++) {
	      if (nextScale !== 0) {
	        deltaScale = expGolombDecoder.readExpGolomb();
	        nextScale = (lastScale + deltaScale + 256) % 256;
	      }

	      lastScale = nextScale === 0 ? lastScale : nextScale;
	    }
	  };

	  /**
	   * Expunge any "Emulation Prevention" bytes from a "Raw Byte
	   * Sequence Payload"
	   * @param data {Uint8Array} the bytes of a RBSP from a NAL
	   * unit
	   * @return {Uint8Array} the RBSP without any Emulation
	   * Prevention Bytes
	   */
	  discardEmulationPreventionBytes = function discardEmulationPreventionBytes(data) {
	    var length = data.byteLength,
	        emulationPreventionBytesPositions = [],
	        i = 1,
	        newLength,
	        newData;

	    // Find all `Emulation Prevention Bytes`
	    while (i < length - 2) {
	      if (data[i] === 0 && data[i + 1] === 0 && data[i + 2] === 0x03) {
	        emulationPreventionBytesPositions.push(i + 2);
	        i += 2;
	      } else {
	        i++;
	      }
	    }

	    // If no Emulation Prevention Bytes were found just return the original
	    // array
	    if (emulationPreventionBytesPositions.length === 0) {
	      return data;
	    }

	    // Create a new array to hold the NAL unit data
	    newLength = length - emulationPreventionBytesPositions.length;
	    newData = new Uint8Array(newLength);
	    var sourceIndex = 0;

	    for (i = 0; i < newLength; sourceIndex++, i++) {
	      if (sourceIndex === emulationPreventionBytesPositions[0]) {
	        // Skip this byte
	        sourceIndex++;
	        // Remove this position index
	        emulationPreventionBytesPositions.shift();
	      }
	      newData[i] = data[sourceIndex];
	    }

	    return newData;
	  };

	  /**
	   * Read a sequence parameter set and return some interesting video
	   * properties. A sequence parameter set is the H264 metadata that
	   * describes the properties of upcoming video frames.
	   * @param data {Uint8Array} the bytes of a sequence parameter set
	   * @return {object} an object with configuration parsed from the
	   * sequence parameter set, including the dimensions of the
	   * associated video frames.
	   */
	  readSequenceParameterSet = function readSequenceParameterSet(data) {
	    var frameCropLeftOffset = 0,
	        frameCropRightOffset = 0,
	        frameCropTopOffset = 0,
	        frameCropBottomOffset = 0,
	        sarScale = 1,
	        expGolombDecoder,
	        profileIdc,
	        levelIdc,
	        profileCompatibility,
	        chromaFormatIdc,
	        picOrderCntType,
	        numRefFramesInPicOrderCntCycle,
	        picWidthInMbsMinus1,
	        picHeightInMapUnitsMinus1,
	        frameMbsOnlyFlag,
	        scalingListCount,
	        sarRatio,
	        aspectRatioIdc,
	        i;

	    expGolombDecoder = new expGolomb(data);
	    profileIdc = expGolombDecoder.readUnsignedByte(); // profile_idc
	    profileCompatibility = expGolombDecoder.readUnsignedByte(); // constraint_set[0-5]_flag
	    levelIdc = expGolombDecoder.readUnsignedByte(); // level_idc u(8)
	    expGolombDecoder.skipUnsignedExpGolomb(); // seq_parameter_set_id

	    // some profiles have more optional data we don't need
	    if (PROFILES_WITH_OPTIONAL_SPS_DATA[profileIdc]) {
	      chromaFormatIdc = expGolombDecoder.readUnsignedExpGolomb();
	      if (chromaFormatIdc === 3) {
	        expGolombDecoder.skipBits(1); // separate_colour_plane_flag
	      }
	      expGolombDecoder.skipUnsignedExpGolomb(); // bit_depth_luma_minus8
	      expGolombDecoder.skipUnsignedExpGolomb(); // bit_depth_chroma_minus8
	      expGolombDecoder.skipBits(1); // qpprime_y_zero_transform_bypass_flag
	      if (expGolombDecoder.readBoolean()) {
	        // seq_scaling_matrix_present_flag
	        scalingListCount = chromaFormatIdc !== 3 ? 8 : 12;
	        for (i = 0; i < scalingListCount; i++) {
	          if (expGolombDecoder.readBoolean()) {
	            // seq_scaling_list_present_flag[ i ]
	            if (i < 6) {
	              skipScalingList(16, expGolombDecoder);
	            } else {
	              skipScalingList(64, expGolombDecoder);
	            }
	          }
	        }
	      }
	    }

	    expGolombDecoder.skipUnsignedExpGolomb(); // log2_max_frame_num_minus4
	    picOrderCntType = expGolombDecoder.readUnsignedExpGolomb();

	    if (picOrderCntType === 0) {
	      expGolombDecoder.readUnsignedExpGolomb(); // log2_max_pic_order_cnt_lsb_minus4
	    } else if (picOrderCntType === 1) {
	      expGolombDecoder.skipBits(1); // delta_pic_order_always_zero_flag
	      expGolombDecoder.skipExpGolomb(); // offset_for_non_ref_pic
	      expGolombDecoder.skipExpGolomb(); // offset_for_top_to_bottom_field
	      numRefFramesInPicOrderCntCycle = expGolombDecoder.readUnsignedExpGolomb();
	      for (i = 0; i < numRefFramesInPicOrderCntCycle; i++) {
	        expGolombDecoder.skipExpGolomb(); // offset_for_ref_frame[ i ]
	      }
	    }

	    expGolombDecoder.skipUnsignedExpGolomb(); // max_num_ref_frames
	    expGolombDecoder.skipBits(1); // gaps_in_frame_num_value_allowed_flag

	    picWidthInMbsMinus1 = expGolombDecoder.readUnsignedExpGolomb();
	    picHeightInMapUnitsMinus1 = expGolombDecoder.readUnsignedExpGolomb();

	    frameMbsOnlyFlag = expGolombDecoder.readBits(1);
	    if (frameMbsOnlyFlag === 0) {
	      expGolombDecoder.skipBits(1); // mb_adaptive_frame_field_flag
	    }

	    expGolombDecoder.skipBits(1); // direct_8x8_inference_flag
	    if (expGolombDecoder.readBoolean()) {
	      // frame_cropping_flag
	      frameCropLeftOffset = expGolombDecoder.readUnsignedExpGolomb();
	      frameCropRightOffset = expGolombDecoder.readUnsignedExpGolomb();
	      frameCropTopOffset = expGolombDecoder.readUnsignedExpGolomb();
	      frameCropBottomOffset = expGolombDecoder.readUnsignedExpGolomb();
	    }
	    if (expGolombDecoder.readBoolean()) {
	      // vui_parameters_present_flag
	      if (expGolombDecoder.readBoolean()) {
	        // aspect_ratio_info_present_flag
	        aspectRatioIdc = expGolombDecoder.readUnsignedByte();
	        switch (aspectRatioIdc) {
	          case 1:
	            sarRatio = [1, 1];break;
	          case 2:
	            sarRatio = [12, 11];break;
	          case 3:
	            sarRatio = [10, 11];break;
	          case 4:
	            sarRatio = [16, 11];break;
	          case 5:
	            sarRatio = [40, 33];break;
	          case 6:
	            sarRatio = [24, 11];break;
	          case 7:
	            sarRatio = [20, 11];break;
	          case 8:
	            sarRatio = [32, 11];break;
	          case 9:
	            sarRatio = [80, 33];break;
	          case 10:
	            sarRatio = [18, 11];break;
	          case 11:
	            sarRatio = [15, 11];break;
	          case 12:
	            sarRatio = [64, 33];break;
	          case 13:
	            sarRatio = [160, 99];break;
	          case 14:
	            sarRatio = [4, 3];break;
	          case 15:
	            sarRatio = [3, 2];break;
	          case 16:
	            sarRatio = [2, 1];break;
	          case 255:
	            {
	              sarRatio = [expGolombDecoder.readUnsignedByte() << 8 | expGolombDecoder.readUnsignedByte(), expGolombDecoder.readUnsignedByte() << 8 | expGolombDecoder.readUnsignedByte()];
	              break;
	            }
	        }
	        if (sarRatio) {
	          sarScale = sarRatio[0] / sarRatio[1];
	        }
	      }
	    }
	    return {
	      profileIdc: profileIdc,
	      levelIdc: levelIdc,
	      profileCompatibility: profileCompatibility,
	      width: Math.ceil(((picWidthInMbsMinus1 + 1) * 16 - frameCropLeftOffset * 2 - frameCropRightOffset * 2) * sarScale),
	      height: (2 - frameMbsOnlyFlag) * (picHeightInMapUnitsMinus1 + 1) * 16 - frameCropTopOffset * 2 - frameCropBottomOffset * 2
	    };
	  };
	};
	_H264Stream.prototype = new stream();

	var h264 = {
	  H264Stream: _H264Stream,
	  NalByteStream: _NalByteStream
	};

	// Constants
	var _AacStream;

	/**
	 * Splits an incoming stream of binary data into ADTS and ID3 Frames.
	 */

	_AacStream = function AacStream() {
	  var everything = new Uint8Array(),
	      timeStamp = 0;

	  _AacStream.prototype.init.call(this);

	  this.setTimestamp = function (timestamp) {
	    timeStamp = timestamp;
	  };

	  this.parseId3TagSize = function (header, byteIndex) {
	    var returnSize = header[byteIndex + 6] << 21 | header[byteIndex + 7] << 14 | header[byteIndex + 8] << 7 | header[byteIndex + 9],
	        flags = header[byteIndex + 5],
	        footerPresent = (flags & 16) >> 4;

	    if (footerPresent) {
	      return returnSize + 20;
	    }
	    return returnSize + 10;
	  };

	  this.parseAdtsSize = function (header, byteIndex) {
	    var lowThree = (header[byteIndex + 5] & 0xE0) >> 5,
	        middle = header[byteIndex + 4] << 3,
	        highTwo = header[byteIndex + 3] & 0x3 << 11;

	    return highTwo | middle | lowThree;
	  };

	  this.push = function (bytes) {
	    var frameSize = 0,
	        byteIndex = 0,
	        bytesLeft,
	        chunk,
	        packet,
	        tempLength;

	    // If there are bytes remaining from the last segment, prepend them to the
	    // bytes that were pushed in
	    if (everything.length) {
	      tempLength = everything.length;
	      everything = new Uint8Array(bytes.byteLength + tempLength);
	      everything.set(everything.subarray(0, tempLength));
	      everything.set(bytes, tempLength);
	    } else {
	      everything = bytes;
	    }

	    while (everything.length - byteIndex >= 3) {
	      if (everything[byteIndex] === 'I'.charCodeAt(0) && everything[byteIndex + 1] === 'D'.charCodeAt(0) && everything[byteIndex + 2] === '3'.charCodeAt(0)) {

	        // Exit early because we don't have enough to parse
	        // the ID3 tag header
	        if (everything.length - byteIndex < 10) {
	          break;
	        }

	        // check framesize
	        frameSize = this.parseId3TagSize(everything, byteIndex);

	        // Exit early if we don't have enough in the buffer
	        // to emit a full packet
	        if (frameSize > everything.length) {
	          break;
	        }
	        chunk = {
	          type: 'timed-metadata',
	          data: everything.subarray(byteIndex, byteIndex + frameSize)
	        };
	        this.trigger('data', chunk);
	        byteIndex += frameSize;
	        continue;
	      } else if (everything[byteIndex] & 0xff === 0xff && (everything[byteIndex + 1] & 0xf0) === 0xf0) {

	        // Exit early because we don't have enough to parse
	        // the ADTS frame header
	        if (everything.length - byteIndex < 7) {
	          break;
	        }

	        frameSize = this.parseAdtsSize(everything, byteIndex);

	        // Exit early if we don't have enough in the buffer
	        // to emit a full packet
	        if (frameSize > everything.length) {
	          break;
	        }

	        packet = {
	          type: 'audio',
	          data: everything.subarray(byteIndex, byteIndex + frameSize),
	          pts: timeStamp,
	          dts: timeStamp
	        };
	        this.trigger('data', packet);
	        byteIndex += frameSize;
	        continue;
	      }
	      byteIndex++;
	    }
	    bytesLeft = everything.length - byteIndex;

	    if (bytesLeft > 0) {
	      everything = everything.subarray(byteIndex);
	    } else {
	      everything = new Uint8Array();
	    }
	  };
	};

	_AacStream.prototype = new stream();

	var aac = _AacStream;

	var highPrefix = [33, 16, 5, 32, 164, 27];
	var lowPrefix = [33, 65, 108, 84, 1, 2, 4, 8, 168, 2, 4, 8, 17, 191, 252];
	var zeroFill = function zeroFill(count) {
	  var a = [];
	  while (count--) {
	    a.push(0);
	  }
	  return a;
	};

	var makeTable = function makeTable(metaTable) {
	  return Object.keys(metaTable).reduce(function (obj, key) {
	    obj[key] = new Uint8Array(metaTable[key].reduce(function (arr, part) {
	      return arr.concat(part);
	    }, []));
	    return obj;
	  }, {});
	};

	// Frames-of-silence to use for filling in missing AAC frames
	var coneOfSilence = {
	  96000: [highPrefix, [227, 64], zeroFill(154), [56]],
	  88200: [highPrefix, [231], zeroFill(170), [56]],
	  64000: [highPrefix, [248, 192], zeroFill(240), [56]],
	  48000: [highPrefix, [255, 192], zeroFill(268), [55, 148, 128], zeroFill(54), [112]],
	  44100: [highPrefix, [255, 192], zeroFill(268), [55, 163, 128], zeroFill(84), [112]],
	  32000: [highPrefix, [255, 192], zeroFill(268), [55, 234], zeroFill(226), [112]],
	  24000: [highPrefix, [255, 192], zeroFill(268), [55, 255, 128], zeroFill(268), [111, 112], zeroFill(126), [224]],
	  16000: [highPrefix, [255, 192], zeroFill(268), [55, 255, 128], zeroFill(268), [111, 255], zeroFill(269), [223, 108], zeroFill(195), [1, 192]],
	  12000: [lowPrefix, zeroFill(268), [3, 127, 248], zeroFill(268), [6, 255, 240], zeroFill(268), [13, 255, 224], zeroFill(268), [27, 253, 128], zeroFill(259), [56]],
	  11025: [lowPrefix, zeroFill(268), [3, 127, 248], zeroFill(268), [6, 255, 240], zeroFill(268), [13, 255, 224], zeroFill(268), [27, 255, 192], zeroFill(268), [55, 175, 128], zeroFill(108), [112]],
	  8000: [lowPrefix, zeroFill(268), [3, 121, 16], zeroFill(47), [7]]
	};

	var silence = makeTable(coneOfSilence);

	var ONE_SECOND_IN_TS = 90000,
	    // 90kHz clock
	secondsToVideoTs,
	    secondsToAudioTs,
	    videoTsToSeconds,
	    audioTsToSeconds,
	    audioTsToVideoTs,
	    videoTsToAudioTs;

	secondsToVideoTs = function secondsToVideoTs(seconds) {
	  return seconds * ONE_SECOND_IN_TS;
	};

	secondsToAudioTs = function secondsToAudioTs(seconds, sampleRate) {
	  return seconds * sampleRate;
	};

	videoTsToSeconds = function videoTsToSeconds(timestamp) {
	  return timestamp / ONE_SECOND_IN_TS;
	};

	audioTsToSeconds = function audioTsToSeconds(timestamp, sampleRate) {
	  return timestamp / sampleRate;
	};

	audioTsToVideoTs = function audioTsToVideoTs(timestamp, sampleRate) {
	  return secondsToVideoTs(audioTsToSeconds(timestamp, sampleRate));
	};

	videoTsToAudioTs = function videoTsToAudioTs(timestamp, sampleRate) {
	  return secondsToAudioTs(videoTsToSeconds(timestamp), sampleRate);
	};

	var clock = {
	  secondsToVideoTs: secondsToVideoTs,
	  secondsToAudioTs: secondsToAudioTs,
	  videoTsToSeconds: videoTsToSeconds,
	  audioTsToSeconds: audioTsToSeconds,
	  audioTsToVideoTs: audioTsToVideoTs,
	  videoTsToAudioTs: videoTsToAudioTs
	};

	var H264Stream = h264.H264Stream;

	// constants
	var AUDIO_PROPERTIES = ['audioobjecttype', 'channelcount', 'samplerate', 'samplingfrequencyindex', 'samplesize'];

	var VIDEO_PROPERTIES = ['width', 'height', 'profileIdc', 'levelIdc', 'profileCompatibility'];

	var ONE_SECOND_IN_TS$1 = 90000; // 90kHz clock

	// object types
	var _VideoSegmentStream, _AudioSegmentStream, _Transmuxer, _CoalesceStream;

	// Helper functions
	var createDefaultSample, isLikelyAacData, collectDtsInfo, clearDtsInfo, calculateTrackBaseMediaDecodeTime, arrayEquals, sumFrameByteLengths;

	/**
	 * Default sample object
	 * see ISO/IEC 14496-12:2012, section 8.6.4.3
	 */
	createDefaultSample = function createDefaultSample() {
	  return {
	    size: 0,
	    flags: {
	      isLeading: 0,
	      dependsOn: 1,
	      isDependedOn: 0,
	      hasRedundancy: 0,
	      degradationPriority: 0
	    }
	  };
	};

	isLikelyAacData = function isLikelyAacData(data) {
	  if (data[0] === 'I'.charCodeAt(0) && data[1] === 'D'.charCodeAt(0) && data[2] === '3'.charCodeAt(0)) {
	    return true;
	  }
	  return false;
	};

	/**
	 * Compare two arrays (even typed) for same-ness
	 */
	arrayEquals = function arrayEquals(a, b) {
	  var i;

	  if (a.length !== b.length) {
	    return false;
	  }

	  // compare the value of each element in the array
	  for (i = 0; i < a.length; i++) {
	    if (a[i] !== b[i]) {
	      return false;
	    }
	  }

	  return true;
	};

	/**
	 * Sum the `byteLength` properties of the data in each AAC frame
	 */
	sumFrameByteLengths = function sumFrameByteLengths(array) {
	  var i,
	      currentObj,
	      sum = 0;

	  // sum the byteLength's all each nal unit in the frame
	  for (i = 0; i < array.length; i++) {
	    currentObj = array[i];
	    sum += currentObj.data.byteLength;
	  }

	  return sum;
	};

	/**
	 * Constructs a single-track, ISO BMFF media segment from AAC data
	 * events. The output of this stream can be fed to a SourceBuffer
	 * configured with a suitable initialization segment.
	 * @param track {object} track metadata configuration
	 * @param options {object} transmuxer options object
	 * @param options.keepOriginalTimestamps {boolean} If true, keep the timestamps
	 *        in the source; false to adjust the first segment to start at 0.
	 */
	_AudioSegmentStream = function AudioSegmentStream(track, options) {
	  var adtsFrames = [],
	      sequenceNumber = 0,
	      earliestAllowedDts = 0,
	      audioAppendStartTs = 0,
	      videoBaseMediaDecodeTime = Infinity;

	  options = options || {};

	  _AudioSegmentStream.prototype.init.call(this);

	  this.push = function (data) {
	    collectDtsInfo(track, data);

	    if (track) {
	      AUDIO_PROPERTIES.forEach(function (prop) {
	        track[prop] = data[prop];
	      });
	    }

	    // buffer audio data until end() is called
	    adtsFrames.push(data);
	  };

	  this.setEarliestDts = function (earliestDts) {
	    earliestAllowedDts = earliestDts - track.timelineStartInfo.baseMediaDecodeTime;
	  };

	  this.setVideoBaseMediaDecodeTime = function (baseMediaDecodeTime) {
	    videoBaseMediaDecodeTime = baseMediaDecodeTime;
	  };

	  this.setAudioAppendStart = function (timestamp) {
	    audioAppendStartTs = timestamp;
	  };

	  this.flush = function () {
	    var frames, moof, mdat, boxes;

	    // return early if no audio data has been observed
	    if (adtsFrames.length === 0) {
	      this.trigger('done', 'AudioSegmentStream');
	      return;
	    }

	    frames = this.trimAdtsFramesByEarliestDts_(adtsFrames);
	    track.baseMediaDecodeTime = calculateTrackBaseMediaDecodeTime(track, options.keepOriginalTimestamps);

	    this.prefixWithSilence_(track, frames);

	    // we have to build the index from byte locations to
	    // samples (that is, adts frames) in the audio data
	    track.samples = this.generateSampleTable_(frames);

	    // concatenate the audio data to constuct the mdat
	    mdat = mp4Generator.mdat(this.concatenateFrameData_(frames));

	    adtsFrames = [];

	    moof = mp4Generator.moof(sequenceNumber, [track]);
	    boxes = new Uint8Array(moof.byteLength + mdat.byteLength);

	    // bump the sequence number for next time
	    sequenceNumber++;

	    boxes.set(moof);
	    boxes.set(mdat, moof.byteLength);

	    clearDtsInfo(track);

	    this.trigger('data', { track: track, boxes: boxes });
	    this.trigger('done', 'AudioSegmentStream');
	  };

	  // Possibly pad (prefix) the audio track with silence if appending this track
	  // would lead to the introduction of a gap in the audio buffer
	  this.prefixWithSilence_ = function (track, frames) {
	    var baseMediaDecodeTimeTs,
	        frameDuration = 0,
	        audioGapDuration = 0,
	        audioFillFrameCount = 0,
	        audioFillDuration = 0,
	        silentFrame,
	        i;

	    if (!frames.length) {
	      return;
	    }

	    baseMediaDecodeTimeTs = clock.audioTsToVideoTs(track.baseMediaDecodeTime, track.samplerate);
	    // determine frame clock duration based on sample rate, round up to avoid overfills
	    frameDuration = Math.ceil(ONE_SECOND_IN_TS$1 / (track.samplerate / 1024));

	    if (audioAppendStartTs && videoBaseMediaDecodeTime) {
	      // insert the shortest possible amount (audio gap or audio to video gap)
	      audioGapDuration = baseMediaDecodeTimeTs - Math.max(audioAppendStartTs, videoBaseMediaDecodeTime);
	      // number of full frames in the audio gap
	      audioFillFrameCount = Math.floor(audioGapDuration / frameDuration);
	      audioFillDuration = audioFillFrameCount * frameDuration;
	    }

	    // don't attempt to fill gaps smaller than a single frame or larger
	    // than a half second
	    if (audioFillFrameCount < 1 || audioFillDuration > ONE_SECOND_IN_TS$1 / 2) {
	      return;
	    }

	    silentFrame = silence[track.samplerate];

	    if (!silentFrame) {
	      // we don't have a silent frame pregenerated for the sample rate, so use a frame
	      // from the content instead
	      silentFrame = frames[0].data;
	    }

	    for (i = 0; i < audioFillFrameCount; i++) {
	      frames.splice(i, 0, {
	        data: silentFrame
	      });
	    }

	    track.baseMediaDecodeTime -= Math.floor(clock.videoTsToAudioTs(audioFillDuration, track.samplerate));
	  };

	  // If the audio segment extends before the earliest allowed dts
	  // value, remove AAC frames until starts at or after the earliest
	  // allowed DTS so that we don't end up with a negative baseMedia-
	  // DecodeTime for the audio track
	  this.trimAdtsFramesByEarliestDts_ = function (adtsFrames) {
	    if (track.minSegmentDts >= earliestAllowedDts) {
	      return adtsFrames;
	    }

	    // We will need to recalculate the earliest segment Dts
	    track.minSegmentDts = Infinity;

	    return adtsFrames.filter(function (currentFrame) {
	      // If this is an allowed frame, keep it and record it's Dts
	      if (currentFrame.dts >= earliestAllowedDts) {
	        track.minSegmentDts = Math.min(track.minSegmentDts, currentFrame.dts);
	        track.minSegmentPts = track.minSegmentDts;
	        return true;
	      }
	      // Otherwise, discard it
	      return false;
	    });
	  };

	  // generate the track's raw mdat data from an array of frames
	  this.generateSampleTable_ = function (frames) {
	    var i,
	        currentFrame,
	        samples = [];

	    for (i = 0; i < frames.length; i++) {
	      currentFrame = frames[i];
	      samples.push({
	        size: currentFrame.data.byteLength,
	        duration: 1024 // For AAC audio, all samples contain 1024 samples
	      });
	    }
	    return samples;
	  };

	  // generate the track's sample table from an array of frames
	  this.concatenateFrameData_ = function (frames) {
	    var i,
	        currentFrame,
	        dataOffset = 0,
	        data = new Uint8Array(sumFrameByteLengths(frames));

	    for (i = 0; i < frames.length; i++) {
	      currentFrame = frames[i];

	      data.set(currentFrame.data, dataOffset);
	      dataOffset += currentFrame.data.byteLength;
	    }
	    return data;
	  };
	};

	_AudioSegmentStream.prototype = new stream();

	/**
	 * Constructs a single-track, ISO BMFF media segment from H264 data
	 * events. The output of this stream can be fed to a SourceBuffer
	 * configured with a suitable initialization segment.
	 * @param track {object} track metadata configuration
	 * @param options {object} transmuxer options object
	 * @param options.alignGopsAtEnd {boolean} If true, start from the end of the
	 *        gopsToAlignWith list when attempting to align gop pts
	 * @param options.keepOriginalTimestamps {boolean} If true, keep the timestamps
	 *        in the source; false to adjust the first segment to start at 0.
	 */
	_VideoSegmentStream = function VideoSegmentStream(track, options) {
	  var sequenceNumber = 0,
	      nalUnits = [],
	      gopsToAlignWith = [],
	      config,
	      pps;

	  options = options || {};

	  _VideoSegmentStream.prototype.init.call(this);

	  delete track.minPTS;

	  this.gopCache_ = [];

	  this.push = function (nalUnit) {
	    collectDtsInfo(track, nalUnit);

	    // record the track config
	    if (nalUnit.nalUnitType === 'seq_parameter_set_rbsp' && !config) {
	      config = nalUnit.config;
	      track.sps = [nalUnit.data];

	      VIDEO_PROPERTIES.forEach(function (prop) {
	        track[prop] = config[prop];
	      }, this);
	    }

	    if (nalUnit.nalUnitType === 'pic_parameter_set_rbsp' && !pps) {
	      pps = nalUnit.data;
	      track.pps = [nalUnit.data];
	    }

	    // buffer video until flush() is called
	    nalUnits.push(nalUnit);
	  };

	  this.flush = function () {
	    var frames, gopForFusion, gops, moof, mdat, boxes;

	    // Throw away nalUnits at the start of the byte stream until
	    // we find the first AUD
	    while (nalUnits.length) {
	      if (nalUnits[0].nalUnitType === 'access_unit_delimiter_rbsp') {
	        break;
	      }
	      nalUnits.shift();
	    }

	    // Return early if no video data has been observed
	    if (nalUnits.length === 0) {
	      this.resetStream_();
	      this.trigger('done', 'VideoSegmentStream');
	      return;
	    }

	    // Organize the raw nal-units into arrays that represent
	    // higher-level constructs such as frames and gops
	    // (group-of-pictures)
	    frames = this.groupNalsIntoFrames_(nalUnits);
	    gops = this.groupFramesIntoGops_(frames);

	    // If the first frame of this fragment is not a keyframe we have
	    // a problem since MSE (on Chrome) requires a leading keyframe.
	    //
	    // We have two approaches to repairing this situation:
	    // 1) GOP-FUSION:
	    //    This is where we keep track of the GOPS (group-of-pictures)
	    //    from previous fragments and attempt to find one that we can
	    //    prepend to the current fragment in order to create a valid
	    //    fragment.
	    // 2) KEYFRAME-PULLING:
	    //    Here we search for the first keyframe in the fragment and
	    //    throw away all the frames between the start of the fragment
	    //    and that keyframe. We then extend the duration and pull the
	    //    PTS of the keyframe forward so that it covers the time range
	    //    of the frames that were disposed of.
	    //
	    // #1 is far prefereable over #2 which can cause "stuttering" but
	    // requires more things to be just right.
	    if (!gops[0][0].keyFrame) {
	      // Search for a gop for fusion from our gopCache
	      gopForFusion = this.getGopForFusion_(nalUnits[0], track);

	      if (gopForFusion) {
	        gops.unshift(gopForFusion);
	        // Adjust Gops' metadata to account for the inclusion of the
	        // new gop at the beginning
	        gops.byteLength += gopForFusion.byteLength;
	        gops.nalCount += gopForFusion.nalCount;
	        gops.pts = gopForFusion.pts;
	        gops.dts = gopForFusion.dts;
	        gops.duration += gopForFusion.duration;
	      } else {
	        // If we didn't find a candidate gop fall back to keyframe-pulling
	        gops = this.extendFirstKeyFrame_(gops);
	      }
	    }

	    // Trim gops to align with gopsToAlignWith
	    if (gopsToAlignWith.length) {
	      var alignedGops;

	      if (options.alignGopsAtEnd) {
	        alignedGops = this.alignGopsAtEnd_(gops);
	      } else {
	        alignedGops = this.alignGopsAtStart_(gops);
	      }

	      if (!alignedGops) {
	        // save all the nals in the last GOP into the gop cache
	        this.gopCache_.unshift({
	          gop: gops.pop(),
	          pps: track.pps,
	          sps: track.sps
	        });

	        // Keep a maximum of 6 GOPs in the cache
	        this.gopCache_.length = Math.min(6, this.gopCache_.length);

	        // Clear nalUnits
	        nalUnits = [];

	        // return early no gops can be aligned with desired gopsToAlignWith
	        this.resetStream_();
	        this.trigger('done', 'VideoSegmentStream');
	        return;
	      }

	      // Some gops were trimmed. clear dts info so minSegmentDts and pts are correct
	      // when recalculated before sending off to CoalesceStream
	      clearDtsInfo(track);

	      gops = alignedGops;
	    }

	    collectDtsInfo(track, gops);

	    // First, we have to build the index from byte locations to
	    // samples (that is, frames) in the video data
	    track.samples = this.generateSampleTable_(gops);

	    // Concatenate the video data and construct the mdat
	    mdat = mp4Generator.mdat(this.concatenateNalData_(gops));

	    track.baseMediaDecodeTime = calculateTrackBaseMediaDecodeTime(track, options.keepOriginalTimestamps);

	    this.trigger('processedGopsInfo', gops.map(function (gop) {
	      return {
	        pts: gop.pts,
	        dts: gop.dts,
	        byteLength: gop.byteLength
	      };
	    }));

	    // save all the nals in the last GOP into the gop cache
	    this.gopCache_.unshift({
	      gop: gops.pop(),
	      pps: track.pps,
	      sps: track.sps
	    });

	    // Keep a maximum of 6 GOPs in the cache
	    this.gopCache_.length = Math.min(6, this.gopCache_.length);

	    // Clear nalUnits
	    nalUnits = [];

	    this.trigger('baseMediaDecodeTime', track.baseMediaDecodeTime);
	    this.trigger('timelineStartInfo', track.timelineStartInfo);

	    moof = mp4Generator.moof(sequenceNumber, [track]);

	    // it would be great to allocate this array up front instead of
	    // throwing away hundreds of media segment fragments
	    boxes = new Uint8Array(moof.byteLength + mdat.byteLength);

	    // Bump the sequence number for next time
	    sequenceNumber++;

	    boxes.set(moof);
	    boxes.set(mdat, moof.byteLength);

	    this.trigger('data', { track: track, boxes: boxes });

	    this.resetStream_();

	    // Continue with the flush process now
	    this.trigger('done', 'VideoSegmentStream');
	  };

	  this.resetStream_ = function () {
	    clearDtsInfo(track);

	    // reset config and pps because they may differ across segments
	    // for instance, when we are rendition switching
	    config = undefined;
	    pps = undefined;
	  };

	  // Search for a candidate Gop for gop-fusion from the gop cache and
	  // return it or return null if no good candidate was found
	  this.getGopForFusion_ = function (nalUnit) {
	    var halfSecond = 45000,
	        // Half-a-second in a 90khz clock
	    allowableOverlap = 10000,
	        // About 3 frames @ 30fps
	    nearestDistance = Infinity,
	        dtsDistance,
	        nearestGopObj,
	        currentGop,
	        currentGopObj,
	        i;

	    // Search for the GOP nearest to the beginning of this nal unit
	    for (i = 0; i < this.gopCache_.length; i++) {
	      currentGopObj = this.gopCache_[i];
	      currentGop = currentGopObj.gop;

	      // Reject Gops with different SPS or PPS
	      if (!(track.pps && arrayEquals(track.pps[0], currentGopObj.pps[0])) || !(track.sps && arrayEquals(track.sps[0], currentGopObj.sps[0]))) {
	        continue;
	      }

	      // Reject Gops that would require a negative baseMediaDecodeTime
	      if (currentGop.dts < track.timelineStartInfo.dts) {
	        continue;
	      }

	      // The distance between the end of the gop and the start of the nalUnit
	      dtsDistance = nalUnit.dts - currentGop.dts - currentGop.duration;

	      // Only consider GOPS that start before the nal unit and end within
	      // a half-second of the nal unit
	      if (dtsDistance >= -allowableOverlap && dtsDistance <= halfSecond) {

	        // Always use the closest GOP we found if there is more than
	        // one candidate
	        if (!nearestGopObj || nearestDistance > dtsDistance) {
	          nearestGopObj = currentGopObj;
	          nearestDistance = dtsDistance;
	        }
	      }
	    }

	    if (nearestGopObj) {
	      return nearestGopObj.gop;
	    }
	    return null;
	  };

	  this.extendFirstKeyFrame_ = function (gops) {
	    var currentGop;

	    if (!gops[0][0].keyFrame && gops.length > 1) {
	      // Remove the first GOP
	      currentGop = gops.shift();

	      gops.byteLength -= currentGop.byteLength;
	      gops.nalCount -= currentGop.nalCount;

	      // Extend the first frame of what is now the
	      // first gop to cover the time period of the
	      // frames we just removed
	      gops[0][0].dts = currentGop.dts;
	      gops[0][0].pts = currentGop.pts;
	      gops[0][0].duration += currentGop.duration;
	    }

	    return gops;
	  };

	  // Convert an array of nal units into an array of frames with each frame being
	  // composed of the nal units that make up that frame
	  // Also keep track of cummulative data about the frame from the nal units such
	  // as the frame duration, starting pts, etc.
	  this.groupNalsIntoFrames_ = function (nalUnits) {
	    var i,
	        currentNal,
	        currentFrame = [],
	        frames = [];

	    currentFrame.byteLength = 0;

	    for (i = 0; i < nalUnits.length; i++) {
	      currentNal = nalUnits[i];

	      // Split on 'aud'-type nal units
	      if (currentNal.nalUnitType === 'access_unit_delimiter_rbsp') {
	        // Since the very first nal unit is expected to be an AUD
	        // only push to the frames array when currentFrame is not empty
	        if (currentFrame.length) {
	          currentFrame.duration = currentNal.dts - currentFrame.dts;
	          frames.push(currentFrame);
	        }
	        currentFrame = [currentNal];
	        currentFrame.byteLength = currentNal.data.byteLength;
	        currentFrame.pts = currentNal.pts;
	        currentFrame.dts = currentNal.dts;
	      } else {
	        // Specifically flag key frames for ease of use later
	        if (currentNal.nalUnitType === 'slice_layer_without_partitioning_rbsp_idr') {
	          currentFrame.keyFrame = true;
	        }
	        currentFrame.duration = currentNal.dts - currentFrame.dts;
	        currentFrame.byteLength += currentNal.data.byteLength;
	        currentFrame.push(currentNal);
	      }
	    }

	    // For the last frame, use the duration of the previous frame if we
	    // have nothing better to go on
	    if (frames.length && (!currentFrame.duration || currentFrame.duration <= 0)) {
	      currentFrame.duration = frames[frames.length - 1].duration;
	    }

	    // Push the final frame
	    frames.push(currentFrame);
	    return frames;
	  };

	  // Convert an array of frames into an array of Gop with each Gop being composed
	  // of the frames that make up that Gop
	  // Also keep track of cummulative data about the Gop from the frames such as the
	  // Gop duration, starting pts, etc.
	  this.groupFramesIntoGops_ = function (frames) {
	    var i,
	        currentFrame,
	        currentGop = [],
	        gops = [];

	    // We must pre-set some of the values on the Gop since we
	    // keep running totals of these values
	    currentGop.byteLength = 0;
	    currentGop.nalCount = 0;
	    currentGop.duration = 0;
	    currentGop.pts = frames[0].pts;
	    currentGop.dts = frames[0].dts;

	    // store some metadata about all the Gops
	    gops.byteLength = 0;
	    gops.nalCount = 0;
	    gops.duration = 0;
	    gops.pts = frames[0].pts;
	    gops.dts = frames[0].dts;

	    for (i = 0; i < frames.length; i++) {
	      currentFrame = frames[i];

	      if (currentFrame.keyFrame) {
	        // Since the very first frame is expected to be an keyframe
	        // only push to the gops array when currentGop is not empty
	        if (currentGop.length) {
	          gops.push(currentGop);
	          gops.byteLength += currentGop.byteLength;
	          gops.nalCount += currentGop.nalCount;
	          gops.duration += currentGop.duration;
	        }

	        currentGop = [currentFrame];
	        currentGop.nalCount = currentFrame.length;
	        currentGop.byteLength = currentFrame.byteLength;
	        currentGop.pts = currentFrame.pts;
	        currentGop.dts = currentFrame.dts;
	        currentGop.duration = currentFrame.duration;
	      } else {
	        currentGop.duration += currentFrame.duration;
	        currentGop.nalCount += currentFrame.length;
	        currentGop.byteLength += currentFrame.byteLength;
	        currentGop.push(currentFrame);
	      }
	    }

	    if (gops.length && currentGop.duration <= 0) {
	      currentGop.duration = gops[gops.length - 1].duration;
	    }
	    gops.byteLength += currentGop.byteLength;
	    gops.nalCount += currentGop.nalCount;
	    gops.duration += currentGop.duration;

	    // push the final Gop
	    gops.push(currentGop);
	    return gops;
	  };

	  // generate the track's sample table from an array of gops
	  this.generateSampleTable_ = function (gops, baseDataOffset) {
	    var h,
	        i,
	        sample,
	        currentGop,
	        currentFrame,
	        dataOffset = baseDataOffset || 0,
	        samples = [];

	    for (h = 0; h < gops.length; h++) {
	      currentGop = gops[h];

	      for (i = 0; i < currentGop.length; i++) {
	        currentFrame = currentGop[i];

	        sample = createDefaultSample();

	        sample.dataOffset = dataOffset;
	        sample.compositionTimeOffset = currentFrame.pts - currentFrame.dts;
	        sample.duration = currentFrame.duration;
	        sample.size = 4 * currentFrame.length; // Space for nal unit size
	        sample.size += currentFrame.byteLength;

	        if (currentFrame.keyFrame) {
	          sample.flags.dependsOn = 2;
	        }

	        dataOffset += sample.size;

	        samples.push(sample);
	      }
	    }
	    return samples;
	  };

	  // generate the track's raw mdat data from an array of gops
	  this.concatenateNalData_ = function (gops) {
	    var h,
	        i,
	        j,
	        currentGop,
	        currentFrame,
	        currentNal,
	        dataOffset = 0,
	        nalsByteLength = gops.byteLength,
	        numberOfNals = gops.nalCount,
	        totalByteLength = nalsByteLength + 4 * numberOfNals,
	        data = new Uint8Array(totalByteLength),
	        view = new DataView(data.buffer);

	    // For each Gop..
	    for (h = 0; h < gops.length; h++) {
	      currentGop = gops[h];

	      // For each Frame..
	      for (i = 0; i < currentGop.length; i++) {
	        currentFrame = currentGop[i];

	        // For each NAL..
	        for (j = 0; j < currentFrame.length; j++) {
	          currentNal = currentFrame[j];

	          view.setUint32(dataOffset, currentNal.data.byteLength);
	          dataOffset += 4;
	          data.set(currentNal.data, dataOffset);
	          dataOffset += currentNal.data.byteLength;
	        }
	      }
	    }
	    return data;
	  };

	  // trim gop list to the first gop found that has a matching pts with a gop in the list
	  // of gopsToAlignWith starting from the START of the list
	  this.alignGopsAtStart_ = function (gops) {
	    var alignIndex, gopIndex, align, gop, byteLength, nalCount, duration, alignedGops;

	    byteLength = gops.byteLength;
	    nalCount = gops.nalCount;
	    duration = gops.duration;
	    alignIndex = gopIndex = 0;

	    while (alignIndex < gopsToAlignWith.length && gopIndex < gops.length) {
	      align = gopsToAlignWith[alignIndex];
	      gop = gops[gopIndex];

	      if (align.pts === gop.pts) {
	        break;
	      }

	      if (gop.pts > align.pts) {
	        // this current gop starts after the current gop we want to align on, so increment
	        // align index
	        alignIndex++;
	        continue;
	      }

	      // current gop starts before the current gop we want to align on. so increment gop
	      // index
	      gopIndex++;
	      byteLength -= gop.byteLength;
	      nalCount -= gop.nalCount;
	      duration -= gop.duration;
	    }

	    if (gopIndex === 0) {
	      // no gops to trim
	      return gops;
	    }

	    if (gopIndex === gops.length) {
	      // all gops trimmed, skip appending all gops
	      return null;
	    }

	    alignedGops = gops.slice(gopIndex);
	    alignedGops.byteLength = byteLength;
	    alignedGops.duration = duration;
	    alignedGops.nalCount = nalCount;
	    alignedGops.pts = alignedGops[0].pts;
	    alignedGops.dts = alignedGops[0].dts;

	    return alignedGops;
	  };

	  // trim gop list to the first gop found that has a matching pts with a gop in the list
	  // of gopsToAlignWith starting from the END of the list
	  this.alignGopsAtEnd_ = function (gops) {
	    var alignIndex, gopIndex, align, gop, alignEndIndex, matchFound;

	    alignIndex = gopsToAlignWith.length - 1;
	    gopIndex = gops.length - 1;
	    alignEndIndex = null;
	    matchFound = false;

	    while (alignIndex >= 0 && gopIndex >= 0) {
	      align = gopsToAlignWith[alignIndex];
	      gop = gops[gopIndex];

	      if (align.pts === gop.pts) {
	        matchFound = true;
	        break;
	      }

	      if (align.pts > gop.pts) {
	        alignIndex--;
	        continue;
	      }

	      if (alignIndex === gopsToAlignWith.length - 1) {
	        // gop.pts is greater than the last alignment candidate. If no match is found
	        // by the end of this loop, we still want to append gops that come after this
	        // point
	        alignEndIndex = gopIndex;
	      }

	      gopIndex--;
	    }

	    if (!matchFound && alignEndIndex === null) {
	      return null;
	    }

	    var trimIndex;

	    if (matchFound) {
	      trimIndex = gopIndex;
	    } else {
	      trimIndex = alignEndIndex;
	    }

	    if (trimIndex === 0) {
	      return gops;
	    }

	    var alignedGops = gops.slice(trimIndex);
	    var metadata = alignedGops.reduce(function (total, gop) {
	      total.byteLength += gop.byteLength;
	      total.duration += gop.duration;
	      total.nalCount += gop.nalCount;
	      return total;
	    }, { byteLength: 0, duration: 0, nalCount: 0 });

	    alignedGops.byteLength = metadata.byteLength;
	    alignedGops.duration = metadata.duration;
	    alignedGops.nalCount = metadata.nalCount;
	    alignedGops.pts = alignedGops[0].pts;
	    alignedGops.dts = alignedGops[0].dts;

	    return alignedGops;
	  };

	  this.alignGopsWith = function (newGopsToAlignWith) {
	    gopsToAlignWith = newGopsToAlignWith;
	  };
	};

	_VideoSegmentStream.prototype = new stream();

	/**
	 * Store information about the start and end of the track and the
	 * duration for each frame/sample we process in order to calculate
	 * the baseMediaDecodeTime
	 */
	collectDtsInfo = function collectDtsInfo(track, data) {
	  if (typeof data.pts === 'number') {
	    if (track.timelineStartInfo.pts === undefined) {
	      track.timelineStartInfo.pts = data.pts;
	    }

	    if (track.minSegmentPts === undefined) {
	      track.minSegmentPts = data.pts;
	    } else {
	      track.minSegmentPts = Math.min(track.minSegmentPts, data.pts);
	    }

	    if (track.maxSegmentPts === undefined) {
	      track.maxSegmentPts = data.pts;
	    } else {
	      track.maxSegmentPts = Math.max(track.maxSegmentPts, data.pts);
	    }
	  }

	  if (typeof data.dts === 'number') {
	    if (track.timelineStartInfo.dts === undefined) {
	      track.timelineStartInfo.dts = data.dts;
	    }

	    if (track.minSegmentDts === undefined) {
	      track.minSegmentDts = data.dts;
	    } else {
	      track.minSegmentDts = Math.min(track.minSegmentDts, data.dts);
	    }

	    if (track.maxSegmentDts === undefined) {
	      track.maxSegmentDts = data.dts;
	    } else {
	      track.maxSegmentDts = Math.max(track.maxSegmentDts, data.dts);
	    }
	  }
	};

	/**
	 * Clear values used to calculate the baseMediaDecodeTime between
	 * tracks
	 */
	clearDtsInfo = function clearDtsInfo(track) {
	  delete track.minSegmentDts;
	  delete track.maxSegmentDts;
	  delete track.minSegmentPts;
	  delete track.maxSegmentPts;
	};

	/**
	 * Calculate the track's baseMediaDecodeTime based on the earliest
	 * DTS the transmuxer has ever seen and the minimum DTS for the
	 * current track
	 * @param track {object} track metadata configuration
	 * @param keepOriginalTimestamps {boolean} If true, keep the timestamps
	 *        in the source; false to adjust the first segment to start at 0.
	 */
	calculateTrackBaseMediaDecodeTime = function calculateTrackBaseMediaDecodeTime(track, keepOriginalTimestamps) {
	  var baseMediaDecodeTime,
	      scale,
	      minSegmentDts = track.minSegmentDts;

	  // Optionally adjust the time so the first segment starts at zero.
	  if (!keepOriginalTimestamps) {
	    minSegmentDts -= track.timelineStartInfo.dts;
	  }

	  // track.timelineStartInfo.baseMediaDecodeTime is the location, in time, where
	  // we want the start of the first segment to be placed
	  baseMediaDecodeTime = track.timelineStartInfo.baseMediaDecodeTime;

	  // Add to that the distance this segment is from the very first
	  baseMediaDecodeTime += minSegmentDts;

	  // baseMediaDecodeTime must not become negative
	  baseMediaDecodeTime = Math.max(0, baseMediaDecodeTime);

	  if (track.type === 'audio') {
	    // Audio has a different clock equal to the sampling_rate so we need to
	    // scale the PTS values into the clock rate of the track
	    scale = track.samplerate / ONE_SECOND_IN_TS$1;
	    baseMediaDecodeTime *= scale;
	    baseMediaDecodeTime = Math.floor(baseMediaDecodeTime);
	  }

	  return baseMediaDecodeTime;
	};

	/**
	 * A Stream that can combine multiple streams (ie. audio & video)
	 * into a single output segment for MSE. Also supports audio-only
	 * and video-only streams.
	 */
	_CoalesceStream = function CoalesceStream(options, metadataStream) {
	  // Number of Tracks per output segment
	  // If greater than 1, we combine multiple
	  // tracks into a single segment
	  this.numberOfTracks = 0;
	  this.metadataStream = metadataStream;

	  if (typeof options.remux !== 'undefined') {
	    this.remuxTracks = !!options.remux;
	  } else {
	    this.remuxTracks = true;
	  }

	  this.pendingTracks = [];
	  this.videoTrack = null;
	  this.pendingBoxes = [];
	  this.pendingCaptions = [];
	  this.pendingMetadata = [];
	  this.pendingBytes = 0;
	  this.emittedTracks = 0;

	  _CoalesceStream.prototype.init.call(this);

	  // Take output from multiple
	  this.push = function (output) {
	    // buffer incoming captions until the associated video segment
	    // finishes
	    if (output.text) {
	      return this.pendingCaptions.push(output);
	    }
	    // buffer incoming id3 tags until the final flush
	    if (output.frames) {
	      return this.pendingMetadata.push(output);
	    }

	    // Add this track to the list of pending tracks and store
	    // important information required for the construction of
	    // the final segment
	    this.pendingTracks.push(output.track);
	    this.pendingBoxes.push(output.boxes);
	    this.pendingBytes += output.boxes.byteLength;

	    if (output.track.type === 'video') {
	      this.videoTrack = output.track;
	    }
	    if (output.track.type === 'audio') {
	      this.audioTrack = output.track;
	    }
	  };
	};

	_CoalesceStream.prototype = new stream();
	_CoalesceStream.prototype.flush = function (flushSource) {
	  var offset = 0,
	      event = {
	    captions: [],
	    captionStreams: {},
	    metadata: [],
	    info: {}
	  },
	      caption,
	      id3,
	      initSegment,
	      timelineStartPts = 0,
	      i;

	  if (this.pendingTracks.length < this.numberOfTracks) {
	    if (flushSource !== 'VideoSegmentStream' && flushSource !== 'AudioSegmentStream') {
	      // Return because we haven't received a flush from a data-generating
	      // portion of the segment (meaning that we have only recieved meta-data
	      // or captions.)
	      return;
	    } else if (this.remuxTracks) {
	      // Return until we have enough tracks from the pipeline to remux (if we
	      // are remuxing audio and video into a single MP4)
	      return;
	    } else if (this.pendingTracks.length === 0) {
	      // In the case where we receive a flush without any data having been
	      // received we consider it an emitted track for the purposes of coalescing
	      // `done` events.
	      // We do this for the case where there is an audio and video track in the
	      // segment but no audio data. (seen in several playlists with alternate
	      // audio tracks and no audio present in the main TS segments.)
	      this.emittedTracks++;

	      if (this.emittedTracks >= this.numberOfTracks) {
	        this.trigger('done');
	        this.emittedTracks = 0;
	      }
	      return;
	    }
	  }

	  if (this.videoTrack) {
	    timelineStartPts = this.videoTrack.timelineStartInfo.pts;
	    VIDEO_PROPERTIES.forEach(function (prop) {
	      event.info[prop] = this.videoTrack[prop];
	    }, this);
	  } else if (this.audioTrack) {
	    timelineStartPts = this.audioTrack.timelineStartInfo.pts;
	    AUDIO_PROPERTIES.forEach(function (prop) {
	      event.info[prop] = this.audioTrack[prop];
	    }, this);
	  }

	  if (this.pendingTracks.length === 1) {
	    event.type = this.pendingTracks[0].type;
	  } else {
	    event.type = 'combined';
	  }

	  this.emittedTracks += this.pendingTracks.length;

	  initSegment = mp4Generator.initSegment(this.pendingTracks);

	  // Create a new typed array to hold the init segment
	  event.initSegment = new Uint8Array(initSegment.byteLength);

	  // Create an init segment containing a moov
	  // and track definitions
	  event.initSegment.set(initSegment);

	  // Create a new typed array to hold the moof+mdats
	  event.data = new Uint8Array(this.pendingBytes);

	  // Append each moof+mdat (one per track) together
	  for (i = 0; i < this.pendingBoxes.length; i++) {
	    event.data.set(this.pendingBoxes[i], offset);
	    offset += this.pendingBoxes[i].byteLength;
	  }

	  // Translate caption PTS times into second offsets into the
	  // video timeline for the segment, and add track info
	  for (i = 0; i < this.pendingCaptions.length; i++) {
	    caption = this.pendingCaptions[i];
	    caption.startTime = caption.startPts - timelineStartPts;
	    caption.startTime /= 90e3;
	    caption.endTime = caption.endPts - timelineStartPts;
	    caption.endTime /= 90e3;
	    event.captionStreams[caption.stream] = true;
	    event.captions.push(caption);
	  }

	  // Translate ID3 frame PTS times into second offsets into the
	  // video timeline for the segment
	  for (i = 0; i < this.pendingMetadata.length; i++) {
	    id3 = this.pendingMetadata[i];
	    id3.cueTime = id3.pts - timelineStartPts;
	    id3.cueTime /= 90e3;
	    event.metadata.push(id3);
	  }
	  // We add this to every single emitted segment even though we only need
	  // it for the first
	  event.metadata.dispatchType = this.metadataStream.dispatchType;

	  // Reset stream state
	  this.pendingTracks.length = 0;
	  this.videoTrack = null;
	  this.pendingBoxes.length = 0;
	  this.pendingCaptions.length = 0;
	  this.pendingBytes = 0;
	  this.pendingMetadata.length = 0;

	  // Emit the built segment
	  this.trigger('data', event);

	  // Only emit `done` if all tracks have been flushed and emitted
	  if (this.emittedTracks >= this.numberOfTracks) {
	    this.trigger('done');
	    this.emittedTracks = 0;
	  }
	};
	/**
	 * A Stream that expects MP2T binary data as input and produces
	 * corresponding media segments, suitable for use with Media Source
	 * Extension (MSE) implementations that support the ISO BMFF byte
	 * stream format, like Chrome.
	 */
	_Transmuxer = function Transmuxer(options) {
	  var self = this,
	      hasFlushed = true,
	      videoTrack,
	      audioTrack;

	  _Transmuxer.prototype.init.call(this);

	  options = options || {};
	  this.baseMediaDecodeTime = options.baseMediaDecodeTime || 0;
	  this.transmuxPipeline_ = {};

	  this.setupAacPipeline = function () {
	    var pipeline = {};
	    this.transmuxPipeline_ = pipeline;

	    pipeline.type = 'aac';
	    pipeline.metadataStream = new m2ts_1.MetadataStream();

	    // set up the parsing pipeline
	    pipeline.aacStream = new aac();
	    pipeline.audioTimestampRolloverStream = new m2ts_1.TimestampRolloverStream('audio');
	    pipeline.timedMetadataTimestampRolloverStream = new m2ts_1.TimestampRolloverStream('timed-metadata');
	    pipeline.adtsStream = new adts();
	    pipeline.coalesceStream = new _CoalesceStream(options, pipeline.metadataStream);
	    pipeline.headOfPipeline = pipeline.aacStream;

	    pipeline.aacStream.pipe(pipeline.audioTimestampRolloverStream).pipe(pipeline.adtsStream);
	    pipeline.aacStream.pipe(pipeline.timedMetadataTimestampRolloverStream).pipe(pipeline.metadataStream).pipe(pipeline.coalesceStream);

	    pipeline.metadataStream.on('timestamp', function (frame) {
	      pipeline.aacStream.setTimestamp(frame.timeStamp);
	    });

	    pipeline.aacStream.on('data', function (data) {
	      if (data.type === 'timed-metadata' && !pipeline.audioSegmentStream) {
	        audioTrack = audioTrack || {
	          timelineStartInfo: {
	            baseMediaDecodeTime: self.baseMediaDecodeTime
	          },
	          codec: 'adts',
	          type: 'audio'
	        };
	        // hook up the audio segment stream to the first track with aac data
	        pipeline.coalesceStream.numberOfTracks++;
	        pipeline.audioSegmentStream = new _AudioSegmentStream(audioTrack, options);
	        // Set up the final part of the audio pipeline
	        pipeline.adtsStream.pipe(pipeline.audioSegmentStream).pipe(pipeline.coalesceStream);
	      }
	    });

	    // Re-emit any data coming from the coalesce stream to the outside world
	    pipeline.coalesceStream.on('data', this.trigger.bind(this, 'data'));
	    // Let the consumer know we have finished flushing the entire pipeline
	    pipeline.coalesceStream.on('done', this.trigger.bind(this, 'done'));
	  };

	  this.setupTsPipeline = function () {
	    var pipeline = {};
	    this.transmuxPipeline_ = pipeline;

	    pipeline.type = 'ts';
	    pipeline.metadataStream = new m2ts_1.MetadataStream();

	    // set up the parsing pipeline
	    pipeline.packetStream = new m2ts_1.TransportPacketStream();
	    pipeline.parseStream = new m2ts_1.TransportParseStream();
	    pipeline.elementaryStream = new m2ts_1.ElementaryStream();
	    pipeline.videoTimestampRolloverStream = new m2ts_1.TimestampRolloverStream('video');
	    pipeline.audioTimestampRolloverStream = new m2ts_1.TimestampRolloverStream('audio');
	    pipeline.timedMetadataTimestampRolloverStream = new m2ts_1.TimestampRolloverStream('timed-metadata');
	    pipeline.adtsStream = new adts();
	    pipeline.h264Stream = new H264Stream();
	    pipeline.captionStream = new m2ts_1.CaptionStream();
	    pipeline.coalesceStream = new _CoalesceStream(options, pipeline.metadataStream);
	    pipeline.headOfPipeline = pipeline.packetStream;

	    // disassemble MPEG2-TS packets into elementary streams
	    pipeline.packetStream.pipe(pipeline.parseStream).pipe(pipeline.elementaryStream);

	    // !!THIS ORDER IS IMPORTANT!!
	    // demux the streams
	    pipeline.elementaryStream.pipe(pipeline.videoTimestampRolloverStream).pipe(pipeline.h264Stream);
	    pipeline.elementaryStream.pipe(pipeline.audioTimestampRolloverStream).pipe(pipeline.adtsStream);

	    pipeline.elementaryStream.pipe(pipeline.timedMetadataTimestampRolloverStream).pipe(pipeline.metadataStream).pipe(pipeline.coalesceStream);

	    // Hook up CEA-608/708 caption stream
	    pipeline.h264Stream.pipe(pipeline.captionStream).pipe(pipeline.coalesceStream);

	    pipeline.elementaryStream.on('data', function (data) {
	      var i;

	      if (data.type === 'metadata') {
	        i = data.tracks.length;

	        // scan the tracks listed in the metadata
	        while (i--) {
	          if (!videoTrack && data.tracks[i].type === 'video') {
	            videoTrack = data.tracks[i];
	            videoTrack.timelineStartInfo.baseMediaDecodeTime = self.baseMediaDecodeTime;
	          } else if (!audioTrack && data.tracks[i].type === 'audio') {
	            audioTrack = data.tracks[i];
	            audioTrack.timelineStartInfo.baseMediaDecodeTime = self.baseMediaDecodeTime;
	          }
	        }

	        // hook up the video segment stream to the first track with h264 data
	        if (videoTrack && !pipeline.videoSegmentStream) {
	          pipeline.coalesceStream.numberOfTracks++;
	          pipeline.videoSegmentStream = new _VideoSegmentStream(videoTrack, options);

	          pipeline.videoSegmentStream.on('timelineStartInfo', function (timelineStartInfo) {
	            // When video emits timelineStartInfo data after a flush, we forward that
	            // info to the AudioSegmentStream, if it exists, because video timeline
	            // data takes precedence.
	            if (audioTrack) {
	              audioTrack.timelineStartInfo = timelineStartInfo;
	              // On the first segment we trim AAC frames that exist before the
	              // very earliest DTS we have seen in video because Chrome will
	              // interpret any video track with a baseMediaDecodeTime that is
	              // non-zero as a gap.
	              pipeline.audioSegmentStream.setEarliestDts(timelineStartInfo.dts);
	            }
	          });

	          pipeline.videoSegmentStream.on('processedGopsInfo', self.trigger.bind(self, 'gopInfo'));

	          pipeline.videoSegmentStream.on('baseMediaDecodeTime', function (baseMediaDecodeTime) {
	            if (audioTrack) {
	              pipeline.audioSegmentStream.setVideoBaseMediaDecodeTime(baseMediaDecodeTime);
	            }
	          });

	          // Set up the final part of the video pipeline
	          pipeline.h264Stream.pipe(pipeline.videoSegmentStream).pipe(pipeline.coalesceStream);
	        }

	        if (audioTrack && !pipeline.audioSegmentStream) {
	          // hook up the audio segment stream to the first track with aac data
	          pipeline.coalesceStream.numberOfTracks++;
	          pipeline.audioSegmentStream = new _AudioSegmentStream(audioTrack, options);

	          // Set up the final part of the audio pipeline
	          pipeline.adtsStream.pipe(pipeline.audioSegmentStream).pipe(pipeline.coalesceStream);
	        }
	      }
	    });

	    // Re-emit any data coming from the coalesce stream to the outside world
	    pipeline.coalesceStream.on('data', this.trigger.bind(this, 'data'));
	    // Let the consumer know we have finished flushing the entire pipeline
	    pipeline.coalesceStream.on('done', this.trigger.bind(this, 'done'));
	  };

	  // hook up the segment streams once track metadata is delivered
	  this.setBaseMediaDecodeTime = function (baseMediaDecodeTime) {
	    var pipeline = this.transmuxPipeline_;

	    this.baseMediaDecodeTime = baseMediaDecodeTime;
	    if (audioTrack) {
	      audioTrack.timelineStartInfo.dts = undefined;
	      audioTrack.timelineStartInfo.pts = undefined;
	      clearDtsInfo(audioTrack);
	      audioTrack.timelineStartInfo.baseMediaDecodeTime = baseMediaDecodeTime;
	      if (pipeline.audioTimestampRolloverStream) {
	        pipeline.audioTimestampRolloverStream.discontinuity();
	      }
	    }
	    if (videoTrack) {
	      if (pipeline.videoSegmentStream) {
	        pipeline.videoSegmentStream.gopCache_ = [];
	        pipeline.videoTimestampRolloverStream.discontinuity();
	      }
	      videoTrack.timelineStartInfo.dts = undefined;
	      videoTrack.timelineStartInfo.pts = undefined;
	      clearDtsInfo(videoTrack);
	      pipeline.captionStream.reset();
	      videoTrack.timelineStartInfo.baseMediaDecodeTime = baseMediaDecodeTime;
	    }

	    if (pipeline.timedMetadataTimestampRolloverStream) {
	      pipeline.timedMetadataTimestampRolloverStream.discontinuity();
	    }
	  };

	  this.setAudioAppendStart = function (timestamp) {
	    if (audioTrack) {
	      this.transmuxPipeline_.audioSegmentStream.setAudioAppendStart(timestamp);
	    }
	  };

	  this.alignGopsWith = function (gopsToAlignWith) {
	    if (videoTrack && this.transmuxPipeline_.videoSegmentStream) {
	      this.transmuxPipeline_.videoSegmentStream.alignGopsWith(gopsToAlignWith);
	    }
	  };

	  // feed incoming data to the front of the parsing pipeline
	  this.push = function (data) {
	    if (hasFlushed) {
	      var isAac = isLikelyAacData(data);

	      if (isAac && this.transmuxPipeline_.type !== 'aac') {
	        this.setupAacPipeline();
	      } else if (!isAac && this.transmuxPipeline_.type !== 'ts') {
	        this.setupTsPipeline();
	      }
	      hasFlushed = false;
	    }
	    this.transmuxPipeline_.headOfPipeline.push(data);
	  };

	  // flush any buffered data
	  this.flush = function () {
	    hasFlushed = true;
	    // Start at the top of the pipeline and flush all pending work
	    this.transmuxPipeline_.headOfPipeline.flush();
	  };

	  // Caption data has to be reset when seeking outside buffered range
	  this.resetCaptions = function () {
	    if (this.transmuxPipeline_.captionStream) {
	      this.transmuxPipeline_.captionStream.reset();
	    }
	  };
	};
	_Transmuxer.prototype = new stream();

	var transmuxer = {
	  Transmuxer: _Transmuxer,
	  VideoSegmentStream: _VideoSegmentStream,
	  AudioSegmentStream: _AudioSegmentStream,
	  AUDIO_PROPERTIES: AUDIO_PROPERTIES,
	  VIDEO_PROPERTIES: VIDEO_PROPERTIES
	};

	var mp4 = {
	  generator: mp4Generator,
	  Transmuxer: transmuxer.Transmuxer,
	  AudioSegmentStream: transmuxer.AudioSegmentStream,
	  VideoSegmentStream: transmuxer.VideoSegmentStream
	};

	var classCallCheck = function (instance, Constructor) {
	  if (!(instance instanceof Constructor)) {
	    throw new TypeError("Cannot call a class as a function");
	  }
	};

	var createClass = function () {
	  function defineProperties(target, props) {
	    for (var i = 0; i < props.length; i++) {
	      var descriptor = props[i];
	      descriptor.enumerable = descriptor.enumerable || false;
	      descriptor.configurable = true;
	      if ("value" in descriptor) descriptor.writable = true;
	      Object.defineProperty(target, descriptor.key, descriptor);
	    }
	  }

	  return function (Constructor, protoProps, staticProps) {
	    if (protoProps) defineProperties(Constructor.prototype, protoProps);
	    if (staticProps) defineProperties(Constructor, staticProps);
	    return Constructor;
	  };
	}();

	/**
	 * @file transmuxer-worker.js
	 */

	/**
	 * Re-emits transmuxer events by converting them into messages to the
	 * world outside the worker.
	 *
	 * @param {Object} transmuxer the transmuxer to wire events on
	 * @private
	 */
	var wireTransmuxerEvents = function wireTransmuxerEvents(transmuxer) {
	  transmuxer.on('data', function (segment) {
	    // transfer ownership of the underlying ArrayBuffer
	    // instead of doing a copy to save memory
	    // ArrayBuffers are transferable but generic TypedArrays are not
	    // @link https://developer.mozilla.org/en-US/docs/Web/API/Web_Workers_API/Using_web_workers#Passing_data_by_transferring_ownership_(transferable_objects)
	    var initArray = segment.initSegment;

	    segment.initSegment = {
	      data: initArray.buffer,
	      byteOffset: initArray.byteOffset,
	      byteLength: initArray.byteLength
	    };

	    var typedArray = segment.data;

	    segment.data = typedArray.buffer;
	    window_1.postMessage({
	      action: 'data',
	      segment: segment,
	      byteOffset: typedArray.byteOffset,
	      byteLength: typedArray.byteLength
	    }, [segment.data]);
	  });

	  if (transmuxer.captionStream) {
	    transmuxer.captionStream.on('data', function (caption) {
	      window_1.postMessage({
	        action: 'caption',
	        data: caption
	      });
	    });
	  }

	  transmuxer.on('done', function (data) {
	    window_1.postMessage({ action: 'done' });
	  });

	  transmuxer.on('gopInfo', function (gopInfo) {
	    window_1.postMessage({
	      action: 'gopInfo',
	      gopInfo: gopInfo
	    });
	  });
	};

	/**
	 * All incoming messages route through this hash. If no function exists
	 * to handle an incoming message, then we ignore the message.
	 *
	 * @class MessageHandlers
	 * @param {Object} options the options to initialize with
	 */

	var MessageHandlers = function () {
	  function MessageHandlers(options) {
	    classCallCheck(this, MessageHandlers);

	    this.options = options || {};
	    this.init();
	  }

	  /**
	   * initialize our web worker and wire all the events.
	   */


	  createClass(MessageHandlers, [{
	    key: 'init',
	    value: function init() {
	      if (this.transmuxer) {
	        this.transmuxer.dispose();
	      }
	      this.transmuxer = new mp4.Transmuxer(this.options);
	      wireTransmuxerEvents(this.transmuxer);
	    }

	    /**
	     * Adds data (a ts segment) to the start of the transmuxer pipeline for
	     * processing.
	     *
	     * @param {ArrayBuffer} data data to push into the muxer
	     */

	  }, {
	    key: 'push',
	    value: function push(data) {
	      // Cast array buffer to correct type for transmuxer
	      var segment = new Uint8Array(data.data, data.byteOffset, data.byteLength);

	      this.transmuxer.push(segment);
	    }

	    /**
	     * Recreate the transmuxer so that the next segment added via `push`
	     * start with a fresh transmuxer.
	     */

	  }, {
	    key: 'reset',
	    value: function reset() {
	      this.init();
	    }

	    /**
	     * Set the value that will be used as the `baseMediaDecodeTime` time for the
	     * next segment pushed in. Subsequent segments will have their `baseMediaDecodeTime`
	     * set relative to the first based on the PTS values.
	     *
	     * @param {Object} data used to set the timestamp offset in the muxer
	     */

	  }, {
	    key: 'setTimestampOffset',
	    value: function setTimestampOffset(data) {
	      var timestampOffset = data.timestampOffset || 0;

	      this.transmuxer.setBaseMediaDecodeTime(Math.round(timestampOffset * 90000));
	    }
	  }, {
	    key: 'setAudioAppendStart',
	    value: function setAudioAppendStart(data) {
	      this.transmuxer.setAudioAppendStart(Math.ceil(data.appendStart * 90000));
	    }

	    /**
	     * Forces the pipeline to finish processing the last segment and emit it's
	     * results.
	     *
	     * @param {Object} data event data, not really used
	     */

	  }, {
	    key: 'flush',
	    value: function flush(data) {
	      this.transmuxer.flush();
	    }
	  }, {
	    key: 'resetCaptions',
	    value: function resetCaptions() {
	      this.transmuxer.resetCaptions();
	    }
	  }, {
	    key: 'alignGopsWith',
	    value: function alignGopsWith(data) {
	      this.transmuxer.alignGopsWith(data.gopsToAlignWith.slice());
	    }
	  }]);
	  return MessageHandlers;
	}();

	/**
	 * Our web wroker interface so that things can talk to mux.js
	 * that will be running in a web worker. the scope is passed to this by
	 * webworkify.
	 *
	 * @param {Object} self the scope for the web worker
	 */


	var TransmuxerWorker = function TransmuxerWorker(self) {
	  self.onmessage = function (event) {
	    if (event.data.action === 'init' && event.data.options) {
	      this.messageHandlers = new MessageHandlers(event.data.options);
	      return;
	    }

	    if (!this.messageHandlers) {
	      this.messageHandlers = new MessageHandlers();
	    }

	    if (event.data && event.data.action && event.data.action !== 'init') {
	      if (this.messageHandlers[event.data.action]) {
	        this.messageHandlers[event.data.action](event.data);
	      }
	    }
	  };
	};

	var transmuxerWorker = new TransmuxerWorker(self);

	return transmuxerWorker;

}());


});

/**
 * @file codec-utils.js
 */

/**
 * Check if a codec string refers to an audio codec.
 *
 * @param {String} codec codec string to check
 * @return {Boolean} if this is an audio codec
 * @private
 */
const isAudioCodec = function(codec) {
  return (/mp4a\.\d+.\d+/i).test(codec);
};

/**
 * Check if a codec string refers to a video codec.
 *
 * @param {String} codec codec string to check
 * @return {Boolean} if this is a video codec
 * @private
 */
const isVideoCodec = function(codec) {
  return (/avc1\.[\da-f]+/i).test(codec);
};

/**
 * Parse a content type header into a type and parameters
 * object
 *
 * @param {String} type the content type header
 * @return {Object} the parsed content-type
 * @private
 */
const parseContentType = function(type) {
  let object = {type: '', parameters: {}};
  let parameters = type.trim().split(';');

  // first parameter should always be content-type
  object.type = parameters.shift().trim();
  parameters.forEach((parameter) => {
    let pair = parameter.trim().split('=');

    if (pair.length > 1) {
      let name = pair[0].replace(/"/g, '').trim();
      let value = pair[1].replace(/"/g, '').trim();

      object.parameters[name] = value;
    }
  });

  return object;
};

/**
 * Replace the old apple-style `avc1.<dd>.<dd>` codec string with the standard
 * `avc1.<hhhhhh>`
 *
 * @param {Array} codecs an array of codec strings to fix
 * @return {Array} the translated codec array
 * @private
 */
const translateLegacyCodecs = function(codecs) {
  return codecs.map((codec) => {
    return codec.replace(/avc1\.(\d+)\.(\d+)/i, function(orig, profile, avcLevel) {
      let profileHex = ('00' + Number(profile).toString(16)).slice(-2);
      let avcLevelHex = ('00' + Number(avcLevel).toString(16)).slice(-2);

      return 'avc1.' + profileHex + '00' + avcLevelHex;
    });
  });
};

/**
 * @file virtual-source-buffer.js
 */

// We create a wrapper around the SourceBuffer so that we can manage the
// state of the `updating` property manually. We have to do this because
// Firefox changes `updating` to false long before triggering `updateend`
// events and that was causing strange problems in videojs-contrib-hls
const makeWrappedSourceBuffer = function(mediaSource, mimeType) {
  const sourceBuffer = mediaSource.addSourceBuffer(mimeType);
  const wrapper = Object.create(null);

  wrapper.updating = false;
  wrapper.realBuffer_ = sourceBuffer;

  for (let key in sourceBuffer) {
    if (typeof sourceBuffer[key] === 'function') {
      wrapper[key] = (...params) => sourceBuffer[key](...params);
    } else if (typeof wrapper[key] === 'undefined') {
      Object.defineProperty(wrapper, key, {
        get: () => sourceBuffer[key],
        set: (v) => sourceBuffer[key] = v
      });
    }
  }

  return wrapper;
};

/**
 * Returns a list of gops in the buffer that have a pts value of 3 seconds or more in
 * front of current time.
 *
 * @param {Array} buffer
 *        The current buffer of gop information
 * @param {Player} player
 *        The player instance
 * @param {Double} mapping
 *        Offset to map display time to stream presentation time
 * @return {Array}
 *         List of gops considered safe to append over
 */
const gopsSafeToAlignWith = (buffer, player, mapping) => {
  if (!player || !buffer.length) {
    return [];
  }

  // pts value for current time + 3 seconds to give a bit more wiggle room
  const currentTimePts = Math.ceil((player.currentTime() - mapping + 3) * 90000);

  let i;

  for (i = 0; i < buffer.length; i++) {
    if (buffer[i].pts > currentTimePts) {
      break;
    }
  }

  return buffer.slice(i);
};

/**
 * Appends gop information (timing and byteLength) received by the transmuxer for the
 * gops appended in the last call to appendBuffer
 *
 * @param {Array} buffer
 *        The current buffer of gop information
 * @param {Array} gops
 *        List of new gop information
 * @param {boolean} replace
 *        If true, replace the buffer with the new gop information. If false, append the
 *        new gop information to the buffer in the right location of time.
 * @return {Array}
 *         Updated list of gop information
 */
const updateGopBuffer = (buffer, gops, replace) => {
  if (!gops.length) {
    return buffer;
  }

  if (replace) {
    // If we are in safe append mode, then completely overwrite the gop buffer
    // with the most recent appeneded data. This will make sure that when appending
    // future segments, we only try to align with gops that are both ahead of current
    // time and in the last segment appended.
    return gops.slice();
  }

  const start = gops[0].pts;

  let i = 0;

  for (i; i < buffer.length; i++) {
    if (buffer[i].pts >= start) {
      break;
    }
  }

  return buffer.slice(0, i).concat(gops);
};

/**
 * Removes gop information in buffer that overlaps with provided start and end
 *
 * @param {Array} buffer
 *        The current buffer of gop information
 * @param {Double} start
 *        position to start the remove at
 * @param {Double} end
 *        position to end the remove at
 * @param {Double} mapping
 *        Offset to map display time to stream presentation time
 */
const removeGopBuffer = (buffer, start, end, mapping) => {
  const startPts = Math.ceil((start - mapping) * 90000);
  const endPts = Math.ceil((end - mapping) * 90000);
  const updatedBuffer = buffer.slice();

  let i = buffer.length;

  while (i--) {
    if (buffer[i].pts <= endPts) {
      break;
    }
  }

  if (i === -1) {
    // no removal because end of remove range is before start of buffer
    return updatedBuffer;
  }

  let j = i + 1;

  while (j--) {
    if (buffer[j].pts <= startPts) {
      break;
    }
  }

  // clamp remove range start to 0 index
  j = Math.max(j, 0);

  updatedBuffer.splice(j, i - j + 1);

  return updatedBuffer;
};

/**
 * VirtualSourceBuffers exist so that we can transmux non native formats
 * into a native format, but keep the same api as a native source buffer.
 * It creates a transmuxer, that works in its own thread (a web worker) and
 * that transmuxer muxes the data into a native format. VirtualSourceBuffer will
 * then send all of that data to the naive sourcebuffer so that it is
 * indestinguishable from a natively supported format.
 *
 * @param {HtmlMediaSource} mediaSource the parent mediaSource
 * @param {Array} codecs array of codecs that we will be dealing with
 * @class VirtualSourceBuffer
 * @extends video.js.EventTarget
 */
class VirtualSourceBuffer extends videojs.EventTarget {
  constructor(mediaSource, codecs) {
    super(videojs.EventTarget);
    this.timestampOffset_ = 0;
    this.pendingBuffers_ = [];
    this.bufferUpdating_ = false;

    this.mediaSource_ = mediaSource;
    this.codecs_ = codecs;
    this.audioCodec_ = null;
    this.videoCodec_ = null;
    this.audioDisabled_ = false;
    this.appendAudioInitSegment_ = true;
    this.gopBuffer_ = [];
    this.timeMapping_ = 0;
    this.safeAppend_ = videojs.browser.IE_VERSION >= 11;

    let options = {
      remux: false,
      alignGopsAtEnd: this.safeAppend_
    };

    this.codecs_.forEach((codec) => {
      if (isAudioCodec(codec)) {
        this.audioCodec_ = codec;
      } else if (isVideoCodec(codec)) {
        this.videoCodec_ = codec;
      }
    });

    // append muxed segments to their respective native buffers as
    // soon as they are available
    this.transmuxer_ = new TransmuxWorker();
    this.transmuxer_.postMessage({action: 'init', options });

    this.transmuxer_.onmessage = (event) => {
      if (event.data.action === 'data') {
        return this.data_(event);
      }

      if (event.data.action === 'done') {
        return this.done_(event);
      }

      if (event.data.action === 'gopInfo') {
        return this.appendGopInfo_(event);
      }
    };

    // this timestampOffset is a property with the side-effect of resetting
    // baseMediaDecodeTime in the transmuxer on the setter
    Object.defineProperty(this, 'timestampOffset', {
      get() {
        return this.timestampOffset_;
      },
      set(val) {
        if (typeof val === 'number' && val >= 0) {
          this.timestampOffset_ = val;
          this.appendAudioInitSegment_ = true;

          // reset gop buffer on timestampoffset as this signals a change in timeline
          this.gopBuffer_.length = 0;
          this.timeMapping_ = 0;

          // We have to tell the transmuxer to set the baseMediaDecodeTime to
          // the desired timestampOffset for the next segment
          this.transmuxer_.postMessage({
            action: 'setTimestampOffset',
            timestampOffset: val
          });
        }
      }
    });

    // setting the append window affects both source buffers
    Object.defineProperty(this, 'appendWindowStart', {
      get() {
        return (this.videoBuffer_ || this.audioBuffer_).appendWindowStart;
      },
      set(start) {
        if (this.videoBuffer_) {
          this.videoBuffer_.appendWindowStart = start;
        }
        if (this.audioBuffer_) {
          this.audioBuffer_.appendWindowStart = start;
        }
      }
    });

    // this buffer is "updating" if either of its native buffers are
    Object.defineProperty(this, 'updating', {
      get() {
        return !!(this.bufferUpdating_ ||
          (!this.audioDisabled_ && this.audioBuffer_ && this.audioBuffer_.updating) ||
          (this.videoBuffer_ && this.videoBuffer_.updating));
      }
    });

    // the buffered property is the intersection of the buffered
    // ranges of the native source buffers
    Object.defineProperty(this, 'buffered', {
      get() {
        let start = null;
        let end = null;
        let arity = 0;
        let extents = [];
        let ranges = [];

        // neither buffer has been created yet
        if (!this.videoBuffer_ && !this.audioBuffer_) {
          return videojs.createTimeRange();
        }

        // only one buffer is configured
        if (!this.videoBuffer_) {
          return this.audioBuffer_.buffered;
        }
        if (!this.audioBuffer_) {
          return this.videoBuffer_.buffered;
        }

        // both buffers are configured
        if (this.audioDisabled_) {
          return this.videoBuffer_.buffered;
        }

        // both buffers are empty
        if (this.videoBuffer_.buffered.length === 0 &&
            this.audioBuffer_.buffered.length === 0) {
          return videojs.createTimeRange();
        }

        // Handle the case where we have both buffers and create an
        // intersection of the two
        let videoBuffered = this.videoBuffer_.buffered;
        let audioBuffered = this.audioBuffer_.buffered;
        let count = videoBuffered.length;

        // A) Gather up all start and end times
        while (count--) {
          extents.push({time: videoBuffered.start(count), type: 'start'});
          extents.push({time: videoBuffered.end(count), type: 'end'});
        }
        count = audioBuffered.length;
        while (count--) {
          extents.push({time: audioBuffered.start(count), type: 'start'});
          extents.push({time: audioBuffered.end(count), type: 'end'});
        }
        // B) Sort them by time
        extents.sort(function(a, b) {
          return a.time - b.time;
        });

        // C) Go along one by one incrementing arity for start and decrementing
        //    arity for ends
        for (count = 0; count < extents.length; count++) {
          if (extents[count].type === 'start') {
            arity++;

            // D) If arity is ever incremented to 2 we are entering an
            //    overlapping range
            if (arity === 2) {
              start = extents[count].time;
            }
          } else if (extents[count].type === 'end') {
            arity--;

            // E) If arity is ever decremented to 1 we leaving an
            //    overlapping range
            if (arity === 1) {
              end = extents[count].time;
            }
          }

          // F) Record overlapping ranges
          if (start !== null && end !== null) {
            ranges.push([start, end]);
            start = null;
            end = null;
          }
        }

        return videojs.createTimeRanges(ranges);
      }
    });
  }

  /**
   * When we get a data event from the transmuxer
   * we call this function and handle the data that
   * was sent to us
   *
   * @private
   * @param {Event} event the data event from the transmuxer
   */
  data_(event) {
    let segment = event.data.segment;

    // Cast ArrayBuffer to TypedArray
    segment.data = new Uint8Array(
      segment.data,
      event.data.byteOffset,
      event.data.byteLength
    );

    segment.initSegment = new Uint8Array(
      segment.initSegment.data,
      segment.initSegment.byteOffset,
      segment.initSegment.byteLength
    );

    createTextTracksIfNecessary(this, this.mediaSource_, segment);

    // Add the segments to the pendingBuffers array
    this.pendingBuffers_.push(segment);
    return;
  }

  /**
   * When we get a done event from the transmuxer
   * we call this function and we process all
   * of the pending data that we have been saving in the
   * data_ function
   *
   * @private
   * @param {Event} event the done event from the transmuxer
   */
  done_(event) {
    // Don't process and append data if the mediaSource is closed
    if (this.mediaSource_.readyState === 'closed') {
      this.pendingBuffers_.length = 0;
      return;
    }

    // All buffers should have been flushed from the muxer
    // start processing anything we have received
    this.processPendingSegments_();
    return;
  }

  /**
   * Create our internal native audio/video source buffers and add
   * event handlers to them with the following conditions:
   * 1. they do not already exist on the mediaSource
   * 2. this VSB has a codec for them
   *
   * @private
   */
  createRealSourceBuffers_() {
    let types = ['audio', 'video'];

    types.forEach((type) => {
      // Don't create a SourceBuffer of this type if we don't have a
      // codec for it
      if (!this[`${type}Codec_`]) {
        return;
      }

      // Do nothing if a SourceBuffer of this type already exists
      if (this[`${type}Buffer_`]) {
        return;
      }

      let buffer = null;

      // If the mediasource already has a SourceBuffer for the codec
      // use that
      if (this.mediaSource_[`${type}Buffer_`]) {
        buffer = this.mediaSource_[`${type}Buffer_`];
        // In multiple audio track cases, the audio source buffer is disabled
        // on the main VirtualSourceBuffer by the HTMLMediaSource much earlier
        // than createRealSourceBuffers_ is called to create the second
        // VirtualSourceBuffer because that happens as a side-effect of
        // videojs-contrib-hls starting the audioSegmentLoader. As a result,
        // the audioBuffer is essentially "ownerless" and no one will toggle
        // the `updating` state back to false once the `updateend` event is received
        //
        // Setting `updating` to false manually will work around this
        // situation and allow work to continue
        buffer.updating = false;
      } else {
        const codecProperty = `${type}Codec_`;
        const mimeType = `${type}/mp4;codecs="${this[codecProperty]}"`;

        buffer = makeWrappedSourceBuffer(this.mediaSource_.nativeMediaSource_, mimeType);

        this.mediaSource_[`${type}Buffer_`] = buffer;
      }

      this[`${type}Buffer_`] = buffer;

      // Wire up the events to the SourceBuffer
      ['update', 'updatestart', 'updateend'].forEach((event) => {
        buffer.addEventListener(event, () => {
          // if audio is disabled
          if (type === 'audio' && this.audioDisabled_) {
            return;
          }

          if (event === 'updateend') {
            this[`${type}Buffer_`].updating = false;
          }

          let shouldTrigger = types.every((t) => {
            // skip checking audio's updating status if audio
            // is not enabled
            if (t === 'audio' && this.audioDisabled_) {
              return true;
            }
            // if the other type if updating we don't trigger
            if (type !== t &&
                this[`${t}Buffer_`] &&
                this[`${t}Buffer_`].updating) {
              return false;
            }
            return true;
          });

          if (shouldTrigger) {
            return this.trigger(event);
          }
        });
      });
    });
  }

  /**
   * Emulate the native mediasource function, but our function will
   * send all of the proposed segments to the transmuxer so that we
   * can transmux them before we append them to our internal
   * native source buffers in the correct format.
   *
   * @link https://developer.mozilla.org/en-US/docs/Web/API/SourceBuffer/appendBuffer
   * @param {Uint8Array} segment the segment to append to the buffer
   */
  appendBuffer(segment) {
    // Start the internal "updating" state
    this.bufferUpdating_ = true;

    if (this.audioBuffer_ && this.audioBuffer_.buffered.length) {
      let audioBuffered = this.audioBuffer_.buffered;

      this.transmuxer_.postMessage({
        action: 'setAudioAppendStart',
        appendStart: audioBuffered.end(audioBuffered.length - 1)
      });
    }

    if (this.videoBuffer_) {
      this.transmuxer_.postMessage({
        action: 'alignGopsWith',
        gopsToAlignWith: gopsSafeToAlignWith(this.gopBuffer_,
                                             this.mediaSource_.player_,
                                             this.timeMapping_)
      });
    }

    this.transmuxer_.postMessage({
      action: 'push',
      // Send the typed-array of data as an ArrayBuffer so that
      // it can be sent as a "Transferable" and avoid the costly
      // memory copy
      data: segment.buffer,

      // To recreate the original typed-array, we need information
      // about what portion of the ArrayBuffer it was a view into
      byteOffset: segment.byteOffset,
      byteLength: segment.byteLength
    },
    [segment.buffer]);
    this.transmuxer_.postMessage({action: 'flush'});
  }

  /**
   * Appends gop information (timing and byteLength) received by the transmuxer for the
   * gops appended in the last call to appendBuffer
   *
   * @param {Event} event
   *        The gopInfo event from the transmuxer
   * @param {Array} event.data.gopInfo
   *        List of gop info to append
   */
  appendGopInfo_(event) {
    this.gopBuffer_ = updateGopBuffer(this.gopBuffer_,
                                      event.data.gopInfo,
                                      this.safeAppend_);
  }

  /**
   * Emulate the native mediasource function and remove parts
   * of the buffer from any of our internal buffers that exist
   *
   * @link https://developer.mozilla.org/en-US/docs/Web/API/SourceBuffer/remove
   * @param {Double} start position to start the remove at
   * @param {Double} end position to end the remove at
   */
  remove(start, end) {
    if (this.videoBuffer_) {
      this.videoBuffer_.updating = true;
      this.videoBuffer_.remove(start, end);
      this.gopBuffer_ = removeGopBuffer(this.gopBuffer_, start, end, this.timeMapping_);
    }
    if (!this.audioDisabled_ && this.audioBuffer_) {
      this.audioBuffer_.updating = true;
      this.audioBuffer_.remove(start, end);
    }

    // Remove Metadata Cues (id3)
    removeCuesFromTrack(start, end, this.metadataTrack_);

    // Remove Any Captions
    if (this.inbandTextTracks_) {
      for (let track in this.inbandTextTracks_) {
        removeCuesFromTrack(start, end, this.inbandTextTracks_[track]);
      }
    }
  }

  /**
   * Process any segments that the muxer has output
   * Concatenate segments together based on type and append them into
   * their respective sourceBuffers
   *
   * @private
   */
  processPendingSegments_() {
    let sortedSegments = {
      video: {
        segments: [],
        bytes: 0
      },
      audio: {
        segments: [],
        bytes: 0
      },
      captions: [],
      metadata: []
    };

    // Sort segments into separate video/audio arrays and
    // keep track of their total byte lengths
    sortedSegments = this.pendingBuffers_.reduce(function(segmentObj, segment) {
      let type = segment.type;
      let data = segment.data;
      let initSegment = segment.initSegment;

      segmentObj[type].segments.push(data);
      segmentObj[type].bytes += data.byteLength;

      segmentObj[type].initSegment = initSegment;

      // Gather any captions into a single array
      if (segment.captions) {
        segmentObj.captions = segmentObj.captions.concat(segment.captions);
      }

      if (segment.info) {
        segmentObj[type].info = segment.info;
      }

      // Gather any metadata into a single array
      if (segment.metadata) {
        segmentObj.metadata = segmentObj.metadata.concat(segment.metadata);
      }

      return segmentObj;
    }, sortedSegments);

    // Create the real source buffers if they don't exist by now since we
    // finally are sure what tracks are contained in the source
    if (!this.videoBuffer_ && !this.audioBuffer_) {
      // Remove any codecs that may have been specified by default but
      // are no longer applicable now
      if (sortedSegments.video.bytes === 0) {
        this.videoCodec_ = null;
      }
      if (sortedSegments.audio.bytes === 0) {
        this.audioCodec_ = null;
      }

      this.createRealSourceBuffers_();
    }

    if (sortedSegments.audio.info) {
      this.mediaSource_.trigger({type: 'audioinfo', info: sortedSegments.audio.info});
    }
    if (sortedSegments.video.info) {
      this.mediaSource_.trigger({type: 'videoinfo', info: sortedSegments.video.info});
    }

    if (this.appendAudioInitSegment_) {
      if (!this.audioDisabled_ && this.audioBuffer_) {
        sortedSegments.audio.segments.unshift(sortedSegments.audio.initSegment);
        sortedSegments.audio.bytes += sortedSegments.audio.initSegment.byteLength;
      }
      this.appendAudioInitSegment_ = false;
    }

    let triggerUpdateend = false;

    // Merge multiple video and audio segments into one and append
    if (this.videoBuffer_ && sortedSegments.video.bytes) {
      sortedSegments.video.segments.unshift(sortedSegments.video.initSegment);
      sortedSegments.video.bytes += sortedSegments.video.initSegment.byteLength;
      this.concatAndAppendSegments_(sortedSegments.video, this.videoBuffer_);
      // TODO: are video tracks the only ones with text tracks?
      addTextTrackData(this, sortedSegments.captions, sortedSegments.metadata);
    } else if (this.videoBuffer_ && (this.audioDisabled_ || !this.audioBuffer_)) {
      // The transmuxer did not return any bytes of video, meaning it was all trimmed
      // for gop alignment. Since we have a video buffer and audio is disabled, updateend
      // will never be triggered by this source buffer, which will cause contrib-hls
      // to be stuck forever waiting for updateend. If audio is not disabled, updateend
      // will be triggered by the audio buffer, which will be sent upwards since the video
      // buffer will not be in an updating state.
      triggerUpdateend = true;
    }

    if (!this.audioDisabled_ && this.audioBuffer_) {
      this.concatAndAppendSegments_(sortedSegments.audio, this.audioBuffer_);
    }

    this.pendingBuffers_.length = 0;

    if (triggerUpdateend) {
      this.trigger('updateend');
    }

    // We are no longer in the internal "updating" state
    this.bufferUpdating_ = false;
  }

  /**
   * Combine all segments into a single Uint8Array and then append them
   * to the destination buffer
   *
   * @param {Object} segmentObj
   * @param {SourceBuffer} destinationBuffer native source buffer to append data to
   * @private
   */
  concatAndAppendSegments_(segmentObj, destinationBuffer) {
    let offset = 0;
    let tempBuffer;

    if (segmentObj.bytes) {
      tempBuffer = new Uint8Array(segmentObj.bytes);

      // Combine the individual segments into one large typed-array
      segmentObj.segments.forEach(function(segment) {
        tempBuffer.set(segment, offset);
        offset += segment.byteLength;
      });

      try {
        destinationBuffer.updating = true;
        destinationBuffer.appendBuffer(tempBuffer);
      } catch (error) {
        if (this.mediaSource_.player_) {
          this.mediaSource_.player_.error({
            code: -3,
            type: 'APPEND_BUFFER_ERR',
            message: error.message,
            originalError: error
          });
        }
      }
    }
  }

  /**
   * Emulate the native mediasource function. abort any soureBuffer
   * actions and throw out any un-appended data.
   *
   * @link https://developer.mozilla.org/en-US/docs/Web/API/SourceBuffer/abort
   */
  abort() {
    if (this.videoBuffer_) {
      this.videoBuffer_.abort();
    }
    if (!this.audioDisabled_ && this.audioBuffer_) {
      this.audioBuffer_.abort();
    }
    if (this.transmuxer_) {
      this.transmuxer_.postMessage({action: 'reset'});
    }
    this.pendingBuffers_.length = 0;
    this.bufferUpdating_ = false;
  }
}

/**
 * @file html-media-source.js
 */

/**
 * Our MediaSource implementation in HTML, mimics native
 * MediaSource where/if possible.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/API/MediaSource
 * @class HtmlMediaSource
 * @extends videojs.EventTarget
 */
class HtmlMediaSource extends videojs.EventTarget {
  constructor() {
    super();
    let property;

    this.nativeMediaSource_ = new window$1.MediaSource();
    // delegate to the native MediaSource's methods by default
    for (property in this.nativeMediaSource_) {
      if (!(property in HtmlMediaSource.prototype) &&
          typeof this.nativeMediaSource_[property] === 'function') {
        this[property] = this.nativeMediaSource_[property].bind(this.nativeMediaSource_);
      }
    }

    // emulate `duration` and `seekable` until seeking can be
    // handled uniformly for live streams
    // see https://github.com/w3c/media-source/issues/5
    this.duration_ = NaN;
    Object.defineProperty(this, 'duration', {
      get() {
        if (this.duration_ === Infinity) {
          return this.duration_;
        }
        return this.nativeMediaSource_.duration;
      },
      set(duration) {
        this.duration_ = duration;
        if (duration !== Infinity) {
          this.nativeMediaSource_.duration = duration;
          return;
        }
      }
    });
    Object.defineProperty(this, 'seekable', {
      get() {
        if (this.duration_ === Infinity) {
          return videojs.createTimeRanges([[0, this.nativeMediaSource_.duration]]);
        }
        return this.nativeMediaSource_.seekable;
      }
    });

    Object.defineProperty(this, 'readyState', {
      get() {
        return this.nativeMediaSource_.readyState;
      }
    });

    Object.defineProperty(this, 'activeSourceBuffers', {
      get() {
        return this.activeSourceBuffers_;
      }
    });

    // the list of virtual and native SourceBuffers created by this
    // MediaSource
    this.sourceBuffers = [];

    this.activeSourceBuffers_ = [];

    /**
     * update the list of active source buffers based upon various
     * imformation from HLS and video.js
     *
     * @private
     */
    this.updateActiveSourceBuffers_ = () => {
      // Retain the reference but empty the array
      this.activeSourceBuffers_.length = 0;

      // If there is only one source buffer, then it will always be active and audio will
      // be disabled based on the codec of the source buffer
      if (this.sourceBuffers.length === 1) {
        let sourceBuffer = this.sourceBuffers[0];

        sourceBuffer.appendAudioInitSegment_ = true;
        sourceBuffer.audioDisabled_ = !sourceBuffer.audioCodec_;
        this.activeSourceBuffers_.push(sourceBuffer);
        return;
      }

      // There are 2 source buffers, a combined (possibly video only) source buffer and
      // and an audio only source buffer.
      // By default, the audio in the combined virtual source buffer is enabled
      // and the audio-only source buffer (if it exists) is disabled.
      let disableCombined = false;
      let disableAudioOnly = true;

      // TODO: maybe we can store the sourcebuffers on the track objects?
      // safari may do something like this
      for (let i = 0; i < this.player_.audioTracks().length; i++) {
        let track = this.player_.audioTracks()[i];

        if (track.enabled && track.kind !== 'main') {
          // The enabled track is an alternate audio track so disable the audio in
          // the combined source buffer and enable the audio-only source buffer.
          disableCombined = true;
          disableAudioOnly = false;
          break;
        }
      }

      this.sourceBuffers.forEach((sourceBuffer, index) => {
        /* eslinst-disable */
        // TODO once codecs are required, we can switch to using the codecs to determine
        //      what stream is the video stream, rather than relying on videoTracks
        /* eslinst-enable */

        sourceBuffer.appendAudioInitSegment_ = true;

        if (sourceBuffer.videoCodec_ && sourceBuffer.audioCodec_) {
          // combined
          sourceBuffer.audioDisabled_ = disableCombined;
        } else if (sourceBuffer.videoCodec_ && !sourceBuffer.audioCodec_) {
          // If the "combined" source buffer is video only, then we do not want
          // disable the audio-only source buffer (this is mostly for demuxed
          // audio and video hls)
          sourceBuffer.audioDisabled_ = true;
          disableAudioOnly = false;
        } else if (!sourceBuffer.videoCodec_ && sourceBuffer.audioCodec_) {
          // audio only
          // In the case of audio only with alternate audio and disableAudioOnly is true
          // this means we want to disable the audio on the alternate audio sourcebuffer
          // but not the main "combined" source buffer. The "combined" source buffer is
          // always at index 0, so this ensures audio won't be disabled in both source
          // buffers.
          sourceBuffer.audioDisabled_ = index ? disableAudioOnly : !disableAudioOnly;
          if (sourceBuffer.audioDisabled_) {
            return;
          }
        }

        this.activeSourceBuffers_.push(sourceBuffer);
      });
    };

    this.onPlayerMediachange_ = () => {
      this.sourceBuffers.forEach((sourceBuffer) => {
        sourceBuffer.appendAudioInitSegment_ = true;
      });
    };

    this.onHlsReset_ = () => {
      this.sourceBuffers.forEach((sourceBuffer) => {
        if (sourceBuffer.transmuxer_) {
          sourceBuffer.transmuxer_.postMessage({action: 'resetCaptions'});
        }
      });
    };

    this.onHlsSegmentTimeMapping_ = (event) => {
      this.sourceBuffers.forEach(buffer => buffer.timeMapping_ = event.mapping);
    };

    // Re-emit MediaSource events on the polyfill
    [
      'sourceopen',
      'sourceclose',
      'sourceended'
    ].forEach(function(eventName) {
      this.nativeMediaSource_.addEventListener(eventName, this.trigger.bind(this));
    }, this);

    // capture the associated player when the MediaSource is
    // successfully attached
    this.on('sourceopen', (event) => {
      // Get the player this MediaSource is attached to
      let video = document.querySelector('[src="' + this.url_ + '"]');

      if (!video) {
        return;
      }

      this.player_ = videojs(video.parentNode);

      // hls-reset is fired by videojs.Hls on to the tech after the main SegmentLoader
      // resets its state and flushes the buffer
      this.player_.tech_.on('hls-reset', this.onHlsReset_);
      // hls-segment-time-mapping is fired by videojs.Hls on to the tech after the main
      // SegmentLoader inspects an MTS segment and has an accurate stream to display
      // time mapping
      this.player_.tech_.on('hls-segment-time-mapping', this.onHlsSegmentTimeMapping_);

      if (this.player_.audioTracks && this.player_.audioTracks()) {
        this.player_.audioTracks().on('change', this.updateActiveSourceBuffers_);
        this.player_.audioTracks().on('addtrack', this.updateActiveSourceBuffers_);
        this.player_.audioTracks().on('removetrack', this.updateActiveSourceBuffers_);
      }

      this.player_.on('mediachange', this.onPlayerMediachange_);
    });

    this.on('sourceended', (event) => {
      let duration = durationOfVideo(this.duration);

      for (let i = 0; i < this.sourceBuffers.length; i++) {
        let sourcebuffer = this.sourceBuffers[i];
        let cues = sourcebuffer.metadataTrack_ && sourcebuffer.metadataTrack_.cues;

        if (cues && cues.length) {
          cues[cues.length - 1].endTime = duration;
        }
      }
    });

    // explicitly terminate any WebWorkers that were created
    // by SourceHandlers
    this.on('sourceclose', function(event) {
      this.sourceBuffers.forEach(function(sourceBuffer) {
        if (sourceBuffer.transmuxer_) {
          sourceBuffer.transmuxer_.terminate();
        }
      });

      this.sourceBuffers.length = 0;
      if (!this.player_) {
        return;
      }

      if (this.player_.audioTracks && this.player_.audioTracks()) {
        this.player_.audioTracks().off('change', this.updateActiveSourceBuffers_);
        this.player_.audioTracks().off('addtrack', this.updateActiveSourceBuffers_);
        this.player_.audioTracks().off('removetrack', this.updateActiveSourceBuffers_);
      }

      // We can only change this if the player hasn't been disposed of yet
      // because `off` eventually tries to use the el_ property. If it has
      // been disposed of, then don't worry about it because there are no
      // event handlers left to unbind anyway
      if (this.player_.el_) {
        this.player_.off('mediachange', this.onPlayerMediachange_);
        this.player_.tech_.off('hls-reset', this.onHlsReset_);
        this.player_.tech_.off('hls-segment-time-mapping', this.onHlsSegmentTimeMapping_);
      }
    });
  }

  /**
   * Add a range that that can now be seeked to.
   *
   * @param {Double} start where to start the addition
   * @param {Double} end where to end the addition
   * @private
   */
  addSeekableRange_(start, end) {
    let error;

    if (this.duration !== Infinity) {
      error = new Error('MediaSource.addSeekableRange() can only be invoked ' +
                        'when the duration is Infinity');
      error.name = 'InvalidStateError';
      error.code = 11;
      throw error;
    }

    if (end > this.nativeMediaSource_.duration ||
        isNaN(this.nativeMediaSource_.duration)) {
      this.nativeMediaSource_.duration = end;
    }
  }

  /**
   * Add a source buffer to the media source.
   *
   * @link https://developer.mozilla.org/en-US/docs/Web/API/MediaSource/addSourceBuffer
   * @param {String} type the content-type of the content
   * @return {Object} the created source buffer
   */
  addSourceBuffer(type) {
    let buffer;
    let parsedType = parseContentType(type);

    // Create a VirtualSourceBuffer to transmux MPEG-2 transport
    // stream segments into fragmented MP4s
    if ((/^(video|audio)\/mp2t$/i).test(parsedType.type)) {
      let codecs = [];

      if (parsedType.parameters && parsedType.parameters.codecs) {
        codecs = parsedType.parameters.codecs.split(',');
        codecs = translateLegacyCodecs(codecs);
        codecs = codecs.filter((codec) => {
          return (isAudioCodec(codec) || isVideoCodec(codec));
        });
      }

      if (codecs.length === 0) {
        codecs = ['avc1.4d400d', 'mp4a.40.2'];
      }

      buffer = new VirtualSourceBuffer(this, codecs);

      if (this.sourceBuffers.length !== 0) {
        // If another VirtualSourceBuffer already exists, then we are creating a
        // SourceBuffer for an alternate audio track and therefore we know that
        // the source has both an audio and video track.
        // That means we should trigger the manual creation of the real
        // SourceBuffers instead of waiting for the transmuxer to return data
        this.sourceBuffers[0].createRealSourceBuffers_();
        buffer.createRealSourceBuffers_();

        // Automatically disable the audio on the first source buffer if
        // a second source buffer is ever created
        this.sourceBuffers[0].audioDisabled_ = true;
      }
    } else {
      // delegate to the native implementation
      buffer = this.nativeMediaSource_.addSourceBuffer(type);
    }

    this.sourceBuffers.push(buffer);
    return buffer;
  }
}

/**
 * @file videojs-contrib-media-sources.js
 */
let urlCount = 0;

// ------------
// Media Source
// ------------

// store references to the media sources so they can be connected
// to a video element (a swf object)
// TODO: can we store this somewhere local to this module?
videojs.mediaSources = {};

/**
 * Provide a method for a swf object to notify JS that a
 * media source is now open.
 *
 * @param {String} msObjectURL string referencing the MSE Object URL
 * @param {String} swfId the swf id
 */
const open = function(msObjectURL, swfId) {
  let mediaSource = videojs.mediaSources[msObjectURL];

  if (mediaSource) {
    mediaSource.trigger({type: 'sourceopen', swfId});
  } else {
    throw new Error('Media Source not found (Video.js)');
  }
};

/**
 * Check to see if the native MediaSource object exists and supports
 * an MP4 container with both H.264 video and AAC-LC audio.
 *
 * @return {Boolean} if  native media sources are supported
 */
const supportsNativeMediaSources = function() {
  return (!!window$1.MediaSource && !!window$1.MediaSource.isTypeSupported &&
    window$1.MediaSource.isTypeSupported('video/mp4;codecs="avc1.4d400d,mp4a.40.2"'));
};

/**
 * An emulation of the MediaSource API so that we can support
 * native and non-native functionality. returns an instance of
 * HtmlMediaSource.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/API/MediaSource/MediaSource
 */
const MediaSource = function() {
  this.MediaSource = {
    open,
    supportsNativeMediaSources
  };

  if (supportsNativeMediaSources()) {
    return new HtmlMediaSource();
  }

  throw new Error('Cannot use create a virtual MediaSource for this video');
};

MediaSource.open = open;
MediaSource.supportsNativeMediaSources = supportsNativeMediaSources;

/**
 * A wrapper around the native URL for our MSE object
 * implementation, this object is exposed under videojs.URL
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/API/URL/URL
 */
const URL$1 = {
  /**
   * A wrapper around the native createObjectURL for our objects.
   * This function maps a native or emulated mediaSource to a blob
   * url so that it can be loaded into video.js
   *
   * @link https://developer.mozilla.org/en-US/docs/Web/API/URL/createObjectURL
   * @param {MediaSource} object the object to create a blob url to
   */
  createObjectURL(object) {
    let objectUrlPrefix = 'blob:vjs-media-source/';
    let url;

    // use the native MediaSource to generate an object URL
    if (object instanceof HtmlMediaSource) {
      url = window$1.URL.createObjectURL(object.nativeMediaSource_);
      object.url_ = url;
      return url;
    }
    // if the object isn't an emulated MediaSource, delegate to the
    // native implementation
    if (!(object instanceof HtmlMediaSource)) {
      url = window$1.URL.createObjectURL(object);
      object.url_ = url;
      return url;
    }

    // build a URL that can be used to map back to the emulated
    // MediaSource
    url = objectUrlPrefix + urlCount;

    urlCount++;

    // setup the mapping back to object
    videojs.mediaSources[url] = object;

    return url;
  }
};

videojs.MediaSource = MediaSource;
videojs.URL = URL$1;

const { EventTarget: EventTarget$1, mergeOptions: mergeOptions$2 } = videojs;

/**
 * Returns a new master manifest that is the result of merging an updated master manifest
 * into the original version.
 *
 * @param {Object} oldMaster
 *        The old parsed mpd object
 * @param {Object} newMaster
 *        The updated parsed mpd object
 * @return {Object}
 *         A new object representing the original master manifest with the updated media
 *         playlists merged in
 */
const updateMaster$1 = (oldMaster, newMaster) => {
  let update = mergeOptions$2(oldMaster, {
    // These are top level properties that can be updated
    duration: newMaster.duration,
    minimumUpdatePeriod: newMaster.minimumUpdatePeriod
  });

  // First update the playlists in playlist list
  for (let i = 0; i < newMaster.playlists.length; i++) {
    const playlistUpdate = updateMaster(update, newMaster.playlists[i]);

    if (playlistUpdate) {
      update = playlistUpdate;
    }
  }

  // Then update media group playlists
  forEachMediaGroup(newMaster, (properties, type, group, label) => {
    if (properties.playlists && properties.playlists.length) {
      const uri = properties.playlists[0].uri;
      const playlistUpdate = updateMaster(update, properties.playlists[0]);

      if (playlistUpdate) {
        update = playlistUpdate;
        // update the playlist reference within media groups
        update.mediaGroups[type][group][label].playlists[0] = update.playlists[uri];
      }
    }
  });

  return update;
};

class DashPlaylistLoader extends EventTarget$1 {
  // DashPlaylistLoader must accept either a src url or a playlist because subsequent
  // playlist loader setups from media groups will expect to be able to pass a playlist
  // (since there aren't external URLs to media playlists with DASH)
  constructor(srcUrlOrPlaylist, hls, withCredentials, masterPlaylistLoader) {
    super();

    this.hls_ = hls;
    this.withCredentials = withCredentials;

    if (!srcUrlOrPlaylist) {
      throw new Error('A non-empty playlist URL or playlist is required');
    }

    // event naming?
    this.on('minimumUpdatePeriod', () => {
      this.refreshXml_();
    });

    // live playlist staleness timeout
    this.on('mediaupdatetimeout', () => {
      this.refreshMedia_();
    });

    // initialize the loader state
    if (typeof srcUrlOrPlaylist === 'string') {
      this.srcUrl = srcUrlOrPlaylist;
      this.state = 'HAVE_NOTHING';
      return;
    }

    this.masterPlaylistLoader_ = masterPlaylistLoader;

    this.state = 'HAVE_METADATA';
    this.started = true;
    // we only should have one playlist so select it
    this.media(srcUrlOrPlaylist);
    // trigger async to mimic behavior of HLS, where it must request a playlist
    window$1.setTimeout(() => {
      this.trigger('loadedmetadata');
    }, 0);
  }

  dispose() {
    this.stopRequest();
    window$1.clearTimeout(this.mediaUpdateTimeout);
  }

  stopRequest() {
    if (this.request) {
      const oldRequest = this.request;

      this.request = null;
      oldRequest.onreadystatechange = null;
      oldRequest.abort();
    }
  }

  media(playlist) {
    // getter
    if (!playlist) {
      return this.media_;
    }

    // setter
    if (this.state === 'HAVE_NOTHING') {
      throw new Error('Cannot switch media playlist from ' + this.state);
    }

    const startingState = this.state;

    // find the playlist object if the target playlist has been specified by URI
    if (typeof playlist === 'string') {
      if (!this.master.playlists[playlist]) {
        throw new Error('Unknown playlist URI: ' + playlist);
      }
      playlist = this.master.playlists[playlist];
    }

    const mediaChange = !this.media_ || playlist.uri !== this.media_.uri;

    this.state = 'HAVE_METADATA';

    // switching to the active playlist is a no-op
    if (!mediaChange) {
      return;
    }

    // switching from an already loaded playlist
    if (this.media_) {
      this.trigger('mediachanging');
    }

    this.media_ = playlist;

    this.refreshMedia_();

    // trigger media change if the active media has been updated
    if (startingState !== 'HAVE_MASTER') {
      this.trigger('mediachange');
    }
  }

  pause() {
    this.stopRequest();
    if (this.state === 'HAVE_NOTHING') {
      // If we pause the loader before any data has been retrieved, its as if we never
      // started, so reset to an unstarted state.
      this.started = false;
    }
  }

  load() {
    // because the playlists are internal to the manifest, load should either load the
    // main manifest, or do nothing but trigger an event
    if (!this.started) {
      this.start();
      return;
    }

    this.trigger('loadedplaylist');
  }

  /**
   * Parses the master xml string and updates playlist uri references
   *
   * @return {Object}
   *         The parsed mpd manifest object
   */
  parseMasterXml() {
    const master = parse(this.masterXml_, {
      manifestUri: this.srcUrl,
      clientOffset: this.clientOffset_
    });

    master.uri = this.srcUrl;

    // Set up phony URIs for the playlists since we won't have external URIs for DASH
    // but reference playlists by their URI throughout the project
    // TODO: Should we create the dummy uris in mpd-parser as well (leaning towards yes).
    for (let i = 0; i < master.playlists.length; i++) {
      const phonyUri = `placeholder-uri-${i}`;

      master.playlists[i].uri = phonyUri;
      // set up by URI references
      master.playlists[phonyUri] = master.playlists[i];
    }

    // set up phony URIs for the media group playlists since we won't have external
    // URIs for DASH but reference playlists by their URI throughout the project
    forEachMediaGroup(master, (properties, mediaType, groupKey, labelKey) => {
      if (properties.playlists && properties.playlists.length) {
        const phonyUri = `placeholder-uri-${mediaType}-${groupKey}-${labelKey}`;

        properties.playlists[0].uri = phonyUri;
        // setup URI references
        master.playlists[phonyUri] = properties.playlists[0];
      }
    });

    setupMediaPlaylists(master);
    resolveMediaGroupUris(master);

    return master;
  }

  start() {
    this.started = true;

    // request the specified URL
    this.request = this.hls_.xhr({
      uri: this.srcUrl,
      withCredentials: this.withCredentials
    }, (error, req) => {
      // disposed
      if (!this.request) {
        return;
      }

      // clear the loader's request reference
      this.request = null;

      if (error) {
        this.error = {
          status: req.status,
          message: 'DASH playlist request error at URL: ' + this.srcUrl,
          responseText: req.responseText,
          // MEDIA_ERR_NETWORK
          code: 2
        };
        if (this.state === 'HAVE_NOTHING') {
          this.started = false;
        }
        return this.trigger('error');
      }

      this.masterXml_ = req.responseText;

      if (req.responseHeaders && req.responseHeaders.date) {
        this.masterLoaded_ = Date.parse(req.responseHeaders.date);
      } else {
        this.masterLoaded_ = Date.now();
      }

      this.syncClientServerClock_(this.onClientServerClockSync_.bind(this));
    });
  }

  /**
   * Parses the master xml for UTCTiming node to sync the client clock to the server
   * clock. If the UTCTiming node requires a HEAD or GET request, that request is made.
   *
   * @param {Function} done
   *        Function to call when clock sync has completed
   */
  syncClientServerClock_(done) {
    const utcTiming = parseUTCTiming(this.masterXml_);

    // No UTCTiming element found in the mpd. Use Date header from mpd request as the
    // server clock
    if (utcTiming === null) {
      this.clientOffset_ = this.masterLoaded_ - Date.now();
      return done();
    }

    if (utcTiming.method === 'DIRECT') {
      this.clientOffset_ = utcTiming.value - Date.now();
      return done();
    }

    this.request = this.hls_.xhr({
      uri: resolveUrl(this.srcUrl, utcTiming.value),
      method: utcTiming.method,
      withCredentials: this.withCredentials
    }, (error, req) => {
      // disposed
      if (!this.request) {
        return;
      }

      if (error) {
        // sync request failed, fall back to using date header from mpd
        // TODO: log warning
        this.clientOffset_ = this.masterLoaded_ - Date.now();
        return done();
      }

      let serverTime;

      if (utcTiming.method === 'HEAD') {
        if (!req.responseHeaders || !req.responseHeaders.date) {
          // expected date header not preset, fall back to using date header from mpd
          // TODO: log warning
          serverTime = this.masterLoaded_;
        } else {
          serverTime = Date.parse(req.responseHeaders.date);
        }
      } else {
        serverTime = Date.parse(req.responseText);
      }

      this.clientOffset_ = serverTime - Date.now();

      done();
    });
  }

  /**
   * Handler for after client/server clock synchronization has happened. Sets up
   * xml refresh timer if specificed by the manifest.
   */
  onClientServerClockSync_() {
    this.master = this.parseMasterXml();

    this.state = 'HAVE_MASTER';

    this.trigger('loadedplaylist');

    if (!this.media_) {
      // no media playlist was specifically selected so start
      // from the first listed one
      this.media(this.master.playlists[0]);
    }
    // trigger loadedmetadata to resolve setup of media groups
    // trigger async to mimic behavior of HLS, where it must request a playlist
    window$1.setTimeout(() => {
      this.trigger('loadedmetadata');
    }, 0);

    // TODO: minimumUpdatePeriod can have a value of 0. Currently the manifest will not
    // be refreshed when this is the case. The inter-op guide says that when the
    // minimumUpdatePeriod is 0, the manifest should outline all currently available
    // segments, but future segments may require an update. I think a good solution
    // would be to update the manifest at the same rate that the media playlists
    // are "refreshed", i.e. every targetDuration.
    if (this.master.minimumUpdatePeriod) {
      window$1.setTimeout(() => {
        this.trigger('minimumUpdatePeriod');
      }, this.master.minimumUpdatePeriod);
    }
  }

  /**
   * Sends request to refresh the master xml and updates the parsed master manifest
   * TODO: Does the client offset need to be recalculated when the xml is refreshed?
   */
  refreshXml_() {
    this.request = this.hls_.xhr({
      uri: this.srcUrl,
      withCredentials: this.withCredentials
    }, (error, req) => {
      // disposed
      if (!this.request) {
        return;
      }

      // clear the loader's request reference
      this.request = null;

      if (error) {
        this.error = {
          status: req.status,
          message: 'DASH playlist request error at URL: ' + this.srcUrl,
          responseText: req.responseText,
          // MEDIA_ERR_NETWORK
          code: 2
        };
        if (this.state === 'HAVE_NOTHING') {
          this.started = false;
        }
        return this.trigger('error');
      }

      this.masterXml_ = req.responseText;

      const newMaster = this.parseMasterXml();

      this.master = updateMaster$1(this.master, newMaster);

      window$1.setTimeout(() => {
        this.trigger('minimumUpdatePeriod');
      }, this.master.minimumUpdatePeriod);
    });
  }

  /**
   * Refreshes the media playlist by re-parsing the master xml and updating playlist
   * references. If this is an alternate loader, the updated parsed manifest is retrieved
   * from the master loader.
   */
  refreshMedia_() {
    let oldMaster;
    let newMaster;

    if (this.masterPlaylistLoader_) {
      oldMaster = this.masterPlaylistLoader_.master;
      newMaster = this.masterPlaylistLoader_.parseMasterXml();
    } else {
      oldMaster = this.master;
      newMaster = this.parseMasterXml();
    }

    const updatedMaster = updateMaster$1(oldMaster, newMaster);

    if (updatedMaster) {
      if (this.masterPlaylistLoader_) {
        this.masterPlaylistLoader_.master = updatedMaster;
      } else {
        this.master = updatedMaster;
      }
      this.media_ = updatedMaster.playlists[this.media_.uri];
    } else {
      this.trigger('playlistunchanged');
    }

    if (!this.media().endList) {
      this.mediaUpdateTimeout = window$1.setTimeout(()=> {
        this.trigger('mediaupdatetimeout');
      }, refreshDelay(this.media(), !!updatedMaster));
    }

    this.trigger('loadedplaylist');
  }
}

const logger = (source) => {
  if (videojs.log.debug) {
    return videojs.log.debug.bind(videojs, 'VHS:', `${source} >`);
  }

  return function() {};
};

function noop() {}

/**
 * @file source-updater.js
 */

/**
 * A queue of callbacks to be serialized and applied when a
 * MediaSource and its associated SourceBuffers are not in the
 * updating state. It is used by the segment loader to update the
 * underlying SourceBuffers when new data is loaded, for instance.
 *
 * @class SourceUpdater
 * @param {MediaSource} mediaSource the MediaSource to create the
 * SourceBuffer from
 * @param {String} mimeType the desired MIME type of the underlying
 * SourceBuffer
 * @param {Object} sourceBufferEmitter an event emitter that fires when a source buffer is
 * added to the media source
 */
class SourceUpdater {
  constructor(mediaSource, mimeType, type, sourceBufferEmitter) {
    this.callbacks_ = [];
    this.pendingCallback_ = null;
    this.timestampOffset_ = 0;
    this.mediaSource = mediaSource;
    this.processedAppend_ = false;
    this.type_ = type;
    this.mimeType_ = mimeType;
    this.logger_ = logger(`SourceUpdater[${type}][${mimeType}]`);

    if (mediaSource.readyState === 'closed') {
      mediaSource.addEventListener(
        'sourceopen', this.createSourceBuffer_.bind(this, mimeType, sourceBufferEmitter));
    } else {
      this.createSourceBuffer_(mimeType, sourceBufferEmitter);
    }
  }

  createSourceBuffer_(mimeType, sourceBufferEmitter) {
    this.sourceBuffer_ = this.mediaSource.addSourceBuffer(mimeType);

    this.logger_('created SourceBuffer');

    if (sourceBufferEmitter) {
      sourceBufferEmitter.trigger('sourcebufferadded');

      if (this.mediaSource.sourceBuffers.length < 2) {
        // There's another source buffer we must wait for before we can start updating
        // our own (or else we can get into a bad state, i.e., appending video/audio data
        // before the other video/audio source buffer is available and leading to a video
        // or audio only buffer).
        sourceBufferEmitter.on('sourcebufferadded', () => {
          this.start_();
        });
        return;
      }
    }

    this.start_();
  }

  start_() {
    this.started_ = true;

    // run completion handlers and process callbacks as updateend
    // events fire
    this.onUpdateendCallback_ = () => {
      let pendingCallback = this.pendingCallback_;

      this.pendingCallback_ = null;

      this.logger_(`buffered [${printableRange(this.buffered())}]`);

      if (pendingCallback) {
        pendingCallback();
      }

      this.runCallback_();
    };

    this.sourceBuffer_.addEventListener('updateend', this.onUpdateendCallback_);

    this.runCallback_();
  }

  /**
   * Aborts the current segment and resets the segment parser.
   *
   * @param {Function} done function to call when done
   * @see http://w3c.github.io/media-source/#widl-SourceBuffer-abort-void
   */
  abort(done) {
    if (this.processedAppend_) {
      this.queueCallback_(() => {
        this.sourceBuffer_.abort();
      }, done);
    }
  }

  /**
   * Queue an update to append an ArrayBuffer.
   *
   * @param {ArrayBuffer} bytes
   * @param {Function} done the function to call when done
   * @see http://www.w3.org/TR/media-source/#widl-SourceBuffer-appendBuffer-void-ArrayBuffer-data
   */
  appendBuffer(bytes, done) {
    this.processedAppend_ = true;
    this.queueCallback_(() => {
      this.sourceBuffer_.appendBuffer(bytes);
    }, done);
  }

  /**
   * Indicates what TimeRanges are buffered in the managed SourceBuffer.
   *
   * @see http://www.w3.org/TR/media-source/#widl-SourceBuffer-buffered
   */
  buffered() {
    if (!this.sourceBuffer_) {
      return videojs.createTimeRanges();
    }
    return this.sourceBuffer_.buffered;
  }

  /**
   * Queue an update to remove a time range from the buffer.
   *
   * @param {Number} start where to start the removal
   * @param {Number} end where to end the removal
   * @see http://www.w3.org/TR/media-source/#widl-SourceBuffer-remove-void-double-start-unrestricted-double-end
   */
  remove(start, end) {
    if (this.processedAppend_) {
      this.queueCallback_(() => {
        this.logger_(`remove [${start} => ${end}]`);
        this.sourceBuffer_.remove(start, end);
      }, noop);
    }
  }

  /**
   * Whether the underlying sourceBuffer is updating or not
   *
   * @return {Boolean} the updating status of the SourceBuffer
   */
  updating() {
    return !this.sourceBuffer_ || this.sourceBuffer_.updating || this.pendingCallback_;
  }

  /**
   * Set/get the timestampoffset on the SourceBuffer
   *
   * @return {Number} the timestamp offset
   */
  timestampOffset(offset) {
    if (typeof offset !== 'undefined') {
      this.queueCallback_(() => {
        this.sourceBuffer_.timestampOffset = offset;
      });
      this.timestampOffset_ = offset;
    }
    return this.timestampOffset_;
  }

  /**
   * Queue a callback to run
   */
  queueCallback_(callback, done) {
    this.callbacks_.push([callback.bind(this), done]);
    this.runCallback_();
  }

  /**
   * Run a queued callback
   */
  runCallback_() {
    let callbacks;

    if (!this.updating() &&
        this.callbacks_.length &&
        this.started_) {
      callbacks = this.callbacks_.shift();
      this.pendingCallback_ = callbacks[1];
      callbacks[0]();
    }
  }

  /**
   * dispose of the source updater and the underlying sourceBuffer
   */
  dispose() {
    this.sourceBuffer_.removeEventListener('updateend', this.onUpdateendCallback_);
    if (this.sourceBuffer_ && this.mediaSource.readyState === 'open') {
      this.sourceBuffer_.abort();
    }
  }
}

var Config = {
  GOAL_BUFFER_LENGTH: 30,
  MAX_GOAL_BUFFER_LENGTH: 60,
  GOAL_BUFFER_LENGTH_RATE: 1,
  // A fudge factor to apply to advertised playlist bitrates to account for
  // temporary flucations in client bandwidth
  BANDWIDTH_VARIANCE: 1.2,
  // How much of the buffer must be filled before we consider upswitching
  BUFFER_LOW_WATER_LINE: 0,
  MAX_BUFFER_LOW_WATER_LINE: 30,
  BUFFER_LOW_WATER_LINE_RATE: 1
};

const REQUEST_ERRORS = {
  FAILURE: 2,
  TIMEOUT: -101,
  ABORTED: -102
};

/**
 * Turns segment byterange into a string suitable for use in
 * HTTP Range requests
 *
 * @param {Object} byterange - an object with two values defining the start and end
 *                             of a byte-range
 */
const byterangeStr = function(byterange) {
  let byterangeStart;
  let byterangeEnd;

  // `byterangeEnd` is one less than `offset + length` because the HTTP range
  // header uses inclusive ranges
  byterangeEnd = byterange.offset + byterange.length - 1;
  byterangeStart = byterange.offset;
  return 'bytes=' + byterangeStart + '-' + byterangeEnd;
};

/**
 * Defines headers for use in the xhr request for a particular segment.
 *
 * @param {Object} segment - a simplified copy of the segmentInfo object
 *                           from SegmentLoader
 */
const segmentXhrHeaders = function(segment) {
  let headers = {};

  if (segment.byterange) {
    headers.Range = byterangeStr(segment.byterange);
  }
  return headers;
};

/**
 * Abort all requests
 *
 * @param {Object} activeXhrs - an object that tracks all XHR requests
 */
const abortAll = (activeXhrs) => {
  activeXhrs.forEach((xhr) => {
    xhr.abort();
  });
};

/**
 * Gather important bandwidth stats once a request has completed
 *
 * @param {Object} request - the XHR request from which to gather stats
 */
const getRequestStats = (request) => {
  return {
    bandwidth: request.bandwidth,
    bytesReceived: request.bytesReceived || 0,
    roundTripTime: request.roundTripTime || 0
  };
};

/**
 * If possible gather bandwidth stats as a request is in
 * progress
 *
 * @param {Event} progressEvent - an event object from an XHR's progress event
 */
const getProgressStats = (progressEvent) => {
  const request = progressEvent.target;
  const roundTripTime = Date.now() - request.requestTime;
  const stats = {
    bandwidth: Infinity,
    bytesReceived: 0,
    roundTripTime: roundTripTime || 0
  };

  stats.bytesReceived = progressEvent.loaded;
  // This can result in Infinity if stats.roundTripTime is 0 but that is ok
  // because we should only use bandwidth stats on progress to determine when
  // abort a request early due to insufficient bandwidth
  stats.bandwidth = Math.floor((stats.bytesReceived / stats.roundTripTime) * 8 * 1000);

  return stats;
};

/**
 * Handle all error conditions in one place and return an object
 * with all the information
 *
 * @param {Error|null} error - if non-null signals an error occured with the XHR
 * @param {Object} request -  the XHR request that possibly generated the error
 */
const handleErrors = (error, request) => {
  if (request.timedout) {
    return {
      status: request.status,
      message: 'HLS request timed-out at URL: ' + request.uri,
      code: REQUEST_ERRORS.TIMEOUT,
      xhr: request
    };
  }

  if (request.aborted) {
    return {
      status: request.status,
      message: 'HLS request aborted at URL: ' + request.uri,
      code: REQUEST_ERRORS.ABORTED,
      xhr: request
    };
  }

  if (error) {
    return {
      status: request.status,
      message: 'HLS request errored at URL: ' + request.uri,
      code: REQUEST_ERRORS.FAILURE,
      xhr: request
    };
  }

  return null;
};

/**
 * Handle responses for key data and convert the key data to the correct format
 * for the decryption step later
 *
 * @param {Object} segment - a simplified copy of the segmentInfo object
 *                           from SegmentLoader
 * @param {Function} finishProcessingFn - a callback to execute to continue processing
 *                                        this request
 */
const handleKeyResponse = (segment, finishProcessingFn) => (error, request) => {
  const response = request.response;
  const errorObj = handleErrors(error, request);

  if (errorObj) {
    return finishProcessingFn(errorObj, segment);
  }

  if (response.byteLength !== 16) {
    return finishProcessingFn({
      status: request.status,
      message: 'Invalid HLS key at URL: ' + request.uri,
      code: REQUEST_ERRORS.FAILURE,
      xhr: request
    }, segment);
  }

  const view = new DataView(response);

  segment.key.bytes = new Uint32Array([
    view.getUint32(0),
    view.getUint32(4),
    view.getUint32(8),
    view.getUint32(12)
  ]);
  return finishProcessingFn(null, segment);
};

/**
 * Handle init-segment responses
 *
 * @param {Object} segment - a simplified copy of the segmentInfo object
 *                           from SegmentLoader
 * @param {Function} finishProcessingFn - a callback to execute to continue processing
 *                                        this request
 */
const handleInitSegmentResponse = (segment, finishProcessingFn) => (error, request) => {
  const response = request.response;
  const errorObj = handleErrors(error, request);

  if (errorObj) {
    return finishProcessingFn(errorObj, segment);
  }

  // stop processing if received empty content
  if (response.byteLength === 0) {
    return finishProcessingFn({
      status: request.status,
      message: 'Empty HLS segment content at URL: ' + request.uri,
      code: REQUEST_ERRORS.FAILURE,
      xhr: request
    }, segment);
  }

  segment.map.bytes = new Uint8Array(request.response);
  return finishProcessingFn(null, segment);
};

/**
 * Response handler for segment-requests being sure to set the correct
 * property depending on whether the segment is encryped or not
 * Also records and keeps track of stats that are used for ABR purposes
 *
 * @param {Object} segment - a simplified copy of the segmentInfo object
 *                           from SegmentLoader
 * @param {Function} finishProcessingFn - a callback to execute to continue processing
 *                                        this request
 */
const handleSegmentResponse = (segment, finishProcessingFn) => (error, request) => {
  const response = request.response;
  const errorObj = handleErrors(error, request);

  if (errorObj) {
    return finishProcessingFn(errorObj, segment);
  }

  // stop processing if received empty content
  if (response.byteLength === 0) {
    return finishProcessingFn({
      status: request.status,
      message: 'Empty HLS segment content at URL: ' + request.uri,
      code: REQUEST_ERRORS.FAILURE,
      xhr: request
    }, segment);
  }

  segment.stats = getRequestStats(request);

  if (segment.key) {
    segment.encryptedBytes = new Uint8Array(request.response);
  } else {
    segment.bytes = new Uint8Array(request.response);
  }

  return finishProcessingFn(null, segment);
};

/**
 * Decrypt the segment via the decryption web worker
 *
 * @param {WebWorker} decrypter - a WebWorker interface to AES-128 decryption routines
 * @param {Object} segment - a simplified copy of the segmentInfo object
 *                           from SegmentLoader
 * @param {Function} doneFn - a callback that is executed after decryption has completed
 */
const decryptSegment = (decrypter, segment, doneFn) => {
  const decryptionHandler = (event) => {
    if (event.data.source === segment.requestId) {
      decrypter.removeEventListener('message', decryptionHandler);
      const decrypted = event.data.decrypted;

      segment.bytes = new Uint8Array(decrypted.bytes,
                                     decrypted.byteOffset,
                                     decrypted.byteLength);
      return doneFn(null, segment);
    }
  };

  decrypter.addEventListener('message', decryptionHandler);

  // this is an encrypted segment
  // incrementally decrypt the segment
  decrypter.postMessage(createTransferableMessage({
    source: segment.requestId,
    encrypted: segment.encryptedBytes,
    key: segment.key.bytes,
    iv: segment.key.iv
  }), [
    segment.encryptedBytes.buffer,
    segment.key.bytes.buffer
  ]);
};

/**
 * The purpose of this function is to get the most pertinent error from the
 * array of errors.
 * For instance if a timeout and two aborts occur, then the aborts were
 * likely triggered by the timeout so return that error object.
 */
const getMostImportantError = (errors) => {
  return errors.reduce((prev, err) => {
    return err.code > prev.code ? err : prev;
  });
};

/**
 * This function waits for all XHRs to finish (with either success or failure)
 * before continueing processing via it's callback. The function gathers errors
 * from each request into a single errors array so that the error status for
 * each request can be examined later.
 *
 * @param {Object} activeXhrs - an object that tracks all XHR requests
 * @param {WebWorker} decrypter - a WebWorker interface to AES-128 decryption routines
 * @param {Function} doneFn - a callback that is executed after all resources have been
 *                            downloaded and any decryption completed
 */
const waitForCompletion = (activeXhrs, decrypter, doneFn) => {
  let errors = [];
  let count = 0;

  return (error, segment) => {
    if (error) {
      // If there are errors, we have to abort any outstanding requests
      abortAll(activeXhrs);
      errors.push(error);
    }
    count += 1;

    if (count === activeXhrs.length) {
      // Keep track of when *all* of the requests have completed
      segment.endOfAllRequests = Date.now();

      if (errors.length > 0) {
        const worstError = getMostImportantError(errors);

        return doneFn(worstError, segment);
      }
      if (segment.encryptedBytes) {
        return decryptSegment(decrypter, segment, doneFn);
      }
      // Otherwise, everything is ready just continue
      return doneFn(null, segment);
    }
  };
};

/**
 * Simple progress event callback handler that gathers some stats before
 * executing a provided callback with the `segment` object
 *
 * @param {Object} segment - a simplified copy of the segmentInfo object
 *                           from SegmentLoader
 * @param {Function} progressFn - a callback that is executed each time a progress event
 *                                is received
 * @param {Event} event - the progress event object from XMLHttpRequest
 */
const handleProgress = (segment, progressFn) => (event) => {
  segment.stats = videojs.mergeOptions(segment.stats, getProgressStats(event));

  // record the time that we receive the first byte of data
  if (!segment.stats.firstBytesReceivedAt && segment.stats.bytesReceived) {
    segment.stats.firstBytesReceivedAt = Date.now();
  }

  return progressFn(event, segment);
};

/**
 * Load all resources and does any processing necessary for a media-segment
 *
 * Features:
 *   decrypts the media-segment if it has a key uri and an iv
 *   aborts *all* requests if *any* one request fails
 *
 * The segment object, at minimum, has the following format:
 * {
 *   resolvedUri: String,
 *   [byterange]: {
 *     offset: Number,
 *     length: Number
 *   },
 *   [key]: {
 *     resolvedUri: String
 *     [byterange]: {
 *       offset: Number,
 *       length: Number
 *     },
 *     iv: {
 *       bytes: Uint32Array
 *     }
 *   },
 *   [map]: {
 *     resolvedUri: String,
 *     [byterange]: {
 *       offset: Number,
 *       length: Number
 *     },
 *     [bytes]: Uint8Array
 *   }
 * }
 * ...where [name] denotes optional properties
 *
 * @param {Function} xhr - an instance of the xhr wrapper in xhr.js
 * @param {Object} xhrOptions - the base options to provide to all xhr requests
 * @param {WebWorker} decryptionWorker - a WebWorker interface to AES-128
 *                                       decryption routines
 * @param {Object} segment - a simplified copy of the segmentInfo object
 *                           from SegmentLoader
 * @param {Function} progressFn - a callback that receives progress events from the main
 *                                segment's xhr request
 * @param {Function} doneFn - a callback that is executed only once all requests have
 *                            succeeded or failed
 * @returns {Function} a function that, when invoked, immediately aborts all
 *                     outstanding requests
 */
const mediaSegmentRequest = (xhr,
                                    xhrOptions,
                                    decryptionWorker,
                                    segment,
                                    progressFn,
                                    doneFn) => {
  const activeXhrs = [];
  const finishProcessingFn = waitForCompletion(activeXhrs, decryptionWorker, doneFn);

  // optionally, request the decryption key
  if (segment.key) {
    const keyRequestOptions = videojs.mergeOptions(xhrOptions, {
      uri: segment.key.resolvedUri,
      responseType: 'arraybuffer'
    });
    const keyRequestCallback = handleKeyResponse(segment, finishProcessingFn);
    const keyXhr = xhr(keyRequestOptions, keyRequestCallback);

    activeXhrs.push(keyXhr);
  }

  // optionally, request the associated media init segment
  if (segment.map &&
    !segment.map.bytes) {
    const initSegmentOptions = videojs.mergeOptions(xhrOptions, {
      uri: segment.map.resolvedUri,
      responseType: 'arraybuffer',
      headers: segmentXhrHeaders(segment.map)
    });
    const initSegmentRequestCallback = handleInitSegmentResponse(segment,
                                                                 finishProcessingFn);
    const initSegmentXhr = xhr(initSegmentOptions, initSegmentRequestCallback);

    activeXhrs.push(initSegmentXhr);
  }

  const segmentRequestOptions = videojs.mergeOptions(xhrOptions, {
    uri: segment.resolvedUri,
    responseType: 'arraybuffer',
    headers: segmentXhrHeaders(segment)
  });
  const segmentRequestCallback = handleSegmentResponse(segment, finishProcessingFn);
  const segmentXhr = xhr(segmentRequestOptions, segmentRequestCallback);

  segmentXhr.addEventListener('progress', handleProgress(segment, progressFn));
  activeXhrs.push(segmentXhr);

  return () => abortAll(activeXhrs);
};

/**
 * @file - codecs.js - Handles tasks regarding codec strings such as translating them to
 * codec strings, or translating codec strings into objects that can be examined.
 */

// Default codec parameters if none were provided for video and/or audio
const defaultCodecs = {
  videoCodec: 'avc1',
  videoObjectTypeIndicator: '.4d400d',
  // AAC-LC
  audioProfile: '2'
};

/**
 * Parses a codec string to retrieve the number of codecs specified,
 * the video codec and object type indicator, and the audio profile.
 */

const parseCodecs = function(codecs = '') {
  let result = {
    codecCount: 0
  };
  let parsed;

  result.codecCount = codecs.split(',').length;
  result.codecCount = result.codecCount || 2;

  // parse the video codec
  parsed = (/(^|\s|,)+(avc[13])([^ ,]*)/i).exec(codecs);
  if (parsed) {
    result.videoCodec = parsed[2];
    result.videoObjectTypeIndicator = parsed[3];
  }

  // parse the last field of the audio codec
  result.audioProfile =
    (/(^|\s|,)+mp4a.[0-9A-Fa-f]+\.([0-9A-Fa-f]+)/i).exec(codecs);
  result.audioProfile = result.audioProfile && result.audioProfile[2];

  return result;
};

/**
 * Replace codecs in the codec string with the old apple-style `avc1.<dd>.<dd>` to the
 * standard `avc1.<hhhhhh>`.
 *
 * @param codecString {String} the codec string
 * @return {String} the codec string with old apple-style codecs replaced
 *
 * @private
 */
const mapLegacyAvcCodecs = function(codecString) {
  return codecString.replace(/avc1\.(\d+)\.(\d+)/i, (match) => {
    return translateLegacyCodecs([match])[0];
  });
};

/**
 * Build a media mime-type string from a set of parameters
 * @param {String} type either 'audio' or 'video'
 * @param {String} container either 'mp2t' or 'mp4'
 * @param {Array} codecs an array of codec strings to add
 * @return {String} a valid media mime-type
 */
const makeMimeTypeString = function(type, container, codecs) {
  // The codecs array is filtered so that falsey values are
  // dropped and don't cause Array#join to create spurious
  // commas
  return `${type}/${container}; codecs="${codecs.filter(c=>!!c).join(', ')}"`;
};

/**
 * Returns the type container based on information in the playlist
 * @param {Playlist} media the current media playlist
 * @return {String} a valid media container type
 */
const getContainerType = function(media) {
  // An initialization segment means the media playlist is an iframe
  // playlist or is using the mp4 container. We don't currently
  // support iframe playlists, so assume this is signalling mp4
  // fragments.
  if (media.segments && media.segments.length && media.segments[0].map) {
    return 'mp4';
  }
  return 'mp2t';
};

/**
 * Returns a set of codec strings parsed from the playlist or the default
 * codec strings if no codecs were specified in the playlist
 * @param {Playlist} media the current media playlist
 * @return {Object} an object with the video and audio codecs
 */
const getCodecs = function(media) {
  // if the codecs were explicitly specified, use them instead of the
  // defaults
  let mediaAttributes = media.attributes || {};

  if (mediaAttributes.CODECS) {
    return parseCodecs(mediaAttributes.CODECS);
  }
  return defaultCodecs;
};

const audioProfileFromDefault = (master, audioGroupId) => {
  if (!master.mediaGroups.AUDIO || !audioGroupId) {
    return null;
  }

  const audioGroup = master.mediaGroups.AUDIO[audioGroupId];

  if (!audioGroup) {
    return null;
  }

  for (let name in audioGroup) {
    const audioType = audioGroup[name];

    if (audioType.default && audioType.playlists) {
      // codec should be the same for all playlists within the audio type
      return parseCodecs(audioType.playlists[0].attributes.CODECS).audioProfile;
    }
  }

  return null;
};

/**
 * Calculates the MIME type strings for a working configuration of
 * SourceBuffers to play variant streams in a master playlist. If
 * there is no possible working configuration, an empty array will be
 * returned.
 *
 * @param master {Object} the m3u8 object for the master playlist
 * @param media {Object} the m3u8 object for the variant playlist
 * @return {Array} the MIME type strings. If the array has more than
 * one entry, the first element should be applied to the video
 * SourceBuffer and the second to the audio SourceBuffer.
 *
 * @private
 */
const mimeTypesForPlaylist = function(master, media) {
  let containerType = getContainerType(media);
  let codecInfo = getCodecs(media);
  let mediaAttributes = media.attributes || {};
  // Default condition for a traditional HLS (no demuxed audio/video)
  let isMuxed = true;
  let isMaat = false;

  if (!media) {
    // Not enough information
    return [];
  }

  if (master.mediaGroups.AUDIO && mediaAttributes.AUDIO) {
    let audioGroup = master.mediaGroups.AUDIO[mediaAttributes.AUDIO];

    // Handle the case where we are in a multiple-audio track scenario
    if (audioGroup) {
      isMaat = true;
      // Start with the everything demuxed then...
      isMuxed = false;
      // ...check to see if any audio group tracks are muxed (ie. lacking a uri)
      for (let groupId in audioGroup) {
        // either a uri is present (if the case of HLS and an external playlist), or
        // playlists is present (in the case of DASH where we don't have external audio
        // playlists)
        if (!audioGroup[groupId].uri && !audioGroup[groupId].playlists) {
          isMuxed = true;
          break;
        }
      }
    }
  }

  // HLS with multiple-audio tracks must always get an audio codec.
  // Put another way, there is no way to have a video-only multiple-audio HLS!
  if (isMaat && !codecInfo.audioProfile) {
    if (!isMuxed) {
      // It is possible for codecs to be specified on the audio media group playlist but
      // not on the rendition playlist. This is mostly the case for DASH, where audio and
      // video are always separate (and separately specified).
      codecInfo.audioProfile = audioProfileFromDefault(master, mediaAttributes.AUDIO);
    }

    if (!codecInfo.audioProfile) {
      videojs.log.warn(
        'Multiple audio tracks present but no audio codec string is specified. ' +
        'Attempting to use the default audio codec (mp4a.40.2)');
      codecInfo.audioProfile = defaultCodecs.audioProfile;
    }
  }

  // Generate the final codec strings from the codec object generated above
  let codecStrings = {};

  if (codecInfo.videoCodec) {
    codecStrings.video = `${codecInfo.videoCodec}${codecInfo.videoObjectTypeIndicator}`;
  }

  if (codecInfo.audioProfile) {
    codecStrings.audio = `mp4a.40.${codecInfo.audioProfile}`;
  }

  // Finally, make and return an array with proper mime-types depending on
  // the configuration
  let justAudio = makeMimeTypeString('audio', containerType, [codecStrings.audio]);
  let justVideo = makeMimeTypeString('video', containerType, [codecStrings.video]);
  let bothVideoAudio = makeMimeTypeString('video', containerType, [
    codecStrings.video,
    codecStrings.audio
  ]);

  if (isMaat) {
    if (!isMuxed && codecStrings.video) {
      return [
        justVideo,
        justAudio
      ];
    }

    if (!isMuxed && !codecStrings.video) {
      // There is no muxed content and no video codec string, so this is an audio only
      // stream with alternate audio.
      return [
        justAudio,
        justAudio
      ];
    }

    // There exists the possiblity that this will return a `video/container`
    // mime-type for the first entry in the array even when there is only audio.
    // This doesn't appear to be a problem and simplifies the code.
    return [
      bothVideoAudio,
      justAudio
    ];
  }

  // If there is no video codec at all, always just return a single
  // audio/<container> mime-type
  if (!codecStrings.video) {
    return [
      justAudio
    ];
  }

  // When not using separate audio media groups, audio and video is
  // *always* muxed
  return [
    bothVideoAudio
  ];
};

// Utilities

/**
 * Returns the CSS value for the specified property on an element
 * using `getComputedStyle`. Firefox has a long-standing issue where
 * getComputedStyle() may return null when running in an iframe with
 * `display: none`.
 *
 * @see https://bugzilla.mozilla.org/show_bug.cgi?id=548397
 * @param {HTMLElement} el the htmlelement to work on
 * @param {string} the proprety to get the style for
 */
const safeGetComputedStyle = function(el, property) {
  let result;

  if (!el) {
    return '';
  }

  result = window$1.getComputedStyle(el);
  if (!result) {
    return '';
  }

  return result[property];
};

/**
 * Resuable stable sort function
 *
 * @param {Playlists} array
 * @param {Function} sortFn Different comparators
 * @function stableSort
 */
const stableSort = function(array, sortFn) {
  let newArray = array.slice();

  array.sort(function(left, right) {
    let cmp = sortFn(left, right);

    if (cmp === 0) {
      return newArray.indexOf(left) - newArray.indexOf(right);
    }
    return cmp;
  });
};

/**
 * A comparator function to sort two playlist object by bandwidth.
 *
 * @param {Object} left a media playlist object
 * @param {Object} right a media playlist object
 * @return {Number} Greater than zero if the bandwidth attribute of
 * left is greater than the corresponding attribute of right. Less
 * than zero if the bandwidth of right is greater than left and
 * exactly zero if the two are equal.
 */
const comparePlaylistBandwidth = function(left, right) {
  let leftBandwidth;
  let rightBandwidth;

  if (left.attributes.BANDWIDTH) {
    leftBandwidth = left.attributes.BANDWIDTH;
  }
  leftBandwidth = leftBandwidth || window$1.Number.MAX_VALUE;
  if (right.attributes.BANDWIDTH) {
    rightBandwidth = right.attributes.BANDWIDTH;
  }
  rightBandwidth = rightBandwidth || window$1.Number.MAX_VALUE;

  return leftBandwidth - rightBandwidth;
};

/**
 * A comparator function to sort two playlist object by resolution (width).
 * @param {Object} left a media playlist object
 * @param {Object} right a media playlist object
 * @return {Number} Greater than zero if the resolution.width attribute of
 * left is greater than the corresponding attribute of right. Less
 * than zero if the resolution.width of right is greater than left and
 * exactly zero if the two are equal.
 */
const comparePlaylistResolution = function(left, right) {
  let leftWidth;
  let rightWidth;

  if (left.attributes.RESOLUTION &&
      left.attributes.RESOLUTION.width) {
    leftWidth = left.attributes.RESOLUTION.width;
  }

  leftWidth = leftWidth || window$1.Number.MAX_VALUE;

  if (right.attributes.RESOLUTION &&
      right.attributes.RESOLUTION.width) {
    rightWidth = right.attributes.RESOLUTION.width;
  }

  rightWidth = rightWidth || window$1.Number.MAX_VALUE;

  // NOTE - Fallback to bandwidth sort as appropriate in cases where multiple renditions
  // have the same media dimensions/ resolution
  if (leftWidth === rightWidth &&
      left.attributes.BANDWIDTH &&
      right.attributes.BANDWIDTH) {
    return left.attributes.BANDWIDTH - right.attributes.BANDWIDTH;
  }
  return leftWidth - rightWidth;
};

/**
 * Chooses the appropriate media playlist based on bandwidth and player size
 *
 * @param {Object} master
 *        Object representation of the master manifest
 * @param {Number} playerBandwidth
 *        Current calculated bandwidth of the player
 * @param {Number} playerWidth
 *        Current width of the player element
 * @param {Number} playerHeight
 *        Current height of the player element
 * @return {Playlist} the highest bitrate playlist less than the
 * currently detected bandwidth, accounting for some amount of
 * bandwidth variance
 */
const simpleSelector = function(master,
                                       playerBandwidth,
                                       playerWidth,
                                       playerHeight) {
  // convert the playlists to an intermediary representation to make comparisons easier
  let sortedPlaylistReps = master.playlists.map((playlist) => {
    let width;
    let height;
    let bandwidth;

    width = playlist.attributes.RESOLUTION && playlist.attributes.RESOLUTION.width;
    height = playlist.attributes.RESOLUTION && playlist.attributes.RESOLUTION.height;
    bandwidth = playlist.attributes.BANDWIDTH;

    bandwidth = bandwidth || window$1.Number.MAX_VALUE;

    return {
      bandwidth,
      width,
      height,
      playlist
    };
  });

  stableSort(sortedPlaylistReps, (left, right) => left.bandwidth - right.bandwidth);

  // filter out any playlists that have been excluded due to
  // incompatible configurations
  sortedPlaylistReps = sortedPlaylistReps.filter(
    (rep) => !Playlist.isIncompatible(rep.playlist)
  );

  // filter out any playlists that have been disabled manually through the representations
  // api or blacklisted temporarily due to playback errors.
  let enabledPlaylistReps = sortedPlaylistReps.filter(
    (rep) => Playlist.isEnabled(rep.playlist)
  );

  if (!enabledPlaylistReps.length) {
    // if there are no enabled playlists, then they have all been blacklisted or disabled
    // by the user through the representations api. In this case, ignore blacklisting and
    // fallback to what the user wants by using playlists the user has not disabled.
    enabledPlaylistReps = sortedPlaylistReps.filter(
      (rep) => !Playlist.isDisabled(rep.playlist)
    );
  }

  // filter out any variant that has greater effective bitrate
  // than the current estimated bandwidth
  let bandwidthPlaylistReps = enabledPlaylistReps.filter(
    (rep) => rep.bandwidth * Config.BANDWIDTH_VARIANCE < playerBandwidth
  );

  let highestRemainingBandwidthRep =
    bandwidthPlaylistReps[bandwidthPlaylistReps.length - 1];

  // get all of the renditions with the same (highest) bandwidth
  // and then taking the very first element
  let bandwidthBestRep = bandwidthPlaylistReps.filter(
    (rep) => rep.bandwidth === highestRemainingBandwidthRep.bandwidth
  )[0];

  // filter out playlists without resolution information
  let haveResolution = bandwidthPlaylistReps.filter((rep) => rep.width && rep.height);

  // sort variants by resolution
  stableSort(haveResolution, (left, right) => left.width - right.width);

  // if we have the exact resolution as the player use it
  let resolutionBestRepList = haveResolution.filter(
    (rep) => rep.width === playerWidth && rep.height === playerHeight
  );

  highestRemainingBandwidthRep = resolutionBestRepList[resolutionBestRepList.length - 1];
  // ensure that we pick the highest bandwidth variant that have exact resolution
  let resolutionBestRep = resolutionBestRepList.filter(
    (rep) => rep.bandwidth === highestRemainingBandwidthRep.bandwidth
  )[0];

  let resolutionPlusOneList;
  let resolutionPlusOneSmallest;
  let resolutionPlusOneRep;

  // find the smallest variant that is larger than the player
  // if there is no match of exact resolution
  if (!resolutionBestRep) {
    resolutionPlusOneList = haveResolution.filter(
      (rep) => rep.width > playerWidth || rep.height > playerHeight
    );

    // find all the variants have the same smallest resolution
    resolutionPlusOneSmallest = resolutionPlusOneList.filter(
      (rep) => rep.width === resolutionPlusOneList[0].width &&
               rep.height === resolutionPlusOneList[0].height
    );

    // ensure that we also pick the highest bandwidth variant that
    // is just-larger-than the video player
    highestRemainingBandwidthRep =
      resolutionPlusOneSmallest[resolutionPlusOneSmallest.length - 1];
    resolutionPlusOneRep = resolutionPlusOneSmallest.filter(
      (rep) => rep.bandwidth === highestRemainingBandwidthRep.bandwidth
    )[0];
  }

  // fallback chain of variants
  let chosenRep = (
    resolutionPlusOneRep ||
    resolutionBestRep ||
    bandwidthBestRep ||
    enabledPlaylistReps[0] ||
    sortedPlaylistReps[0]
  );

  return chosenRep ? chosenRep.playlist : null;
};

// Playlist Selectors

/**
 * Chooses the appropriate media playlist based on the most recent
 * bandwidth estimate and the player size.
 *
 * Expects to be called within the context of an instance of HlsHandler
 *
 * @return {Playlist} the highest bitrate playlist less than the
 * currently detected bandwidth, accounting for some amount of
 * bandwidth variance
 */
const lastBandwidthSelector = function() {
  return simpleSelector(this.playlists.master,
                        this.systemBandwidth,
                        parseInt(safeGetComputedStyle(this.tech_.el(), 'width'), 10),
                        parseInt(safeGetComputedStyle(this.tech_.el(), 'height'), 10));
};

/**
 * Chooses the appropriate media playlist based on the potential to rebuffer
 *
 * @param {Object} settings
 *        Object of information required to use this selector
 * @param {Object} settings.master
 *        Object representation of the master manifest
 * @param {Number} settings.currentTime
 *        The current time of the player
 * @param {Number} settings.bandwidth
 *        Current measured bandwidth
 * @param {Number} settings.duration
 *        Duration of the media
 * @param {Number} settings.segmentDuration
 *        Segment duration to be used in round trip time calculations
 * @param {Number} settings.timeUntilRebuffer
 *        Time left in seconds until the player has to rebuffer
 * @param {Number} settings.currentTimeline
 *        The current timeline segments are being loaded from
 * @param {SyncController} settings.syncController
 *        SyncController for determining if we have a sync point for a given playlist
 * @return {Object|null}
 *         {Object} return.playlist
 *         The highest bandwidth playlist with the least amount of rebuffering
 *         {Number} return.rebufferingImpact
 *         The amount of time in seconds switching to this playlist will rebuffer. A
 *         negative value means that switching will cause zero rebuffering.
 */
const minRebufferMaxBandwidthSelector = function(settings) {
  const {
    master,
    currentTime,
    bandwidth,
    duration: duration$$1,
    segmentDuration,
    timeUntilRebuffer,
    currentTimeline,
    syncController
  } = settings;

  // filter out any playlists that have been excluded due to
  // incompatible configurations
  const compatiblePlaylists = master.playlists.filter(
    playlist => !Playlist.isIncompatible(playlist));

  // filter out any playlists that have been disabled manually through the representations
  // api or blacklisted temporarily due to playback errors.
  let enabledPlaylists = compatiblePlaylists.filter(Playlist.isEnabled);

  if (!enabledPlaylists.length) {
    // if there are no enabled playlists, then they have all been blacklisted or disabled
    // by the user through the representations api. In this case, ignore blacklisting and
    // fallback to what the user wants by using playlists the user has not disabled.
    enabledPlaylists = compatiblePlaylists.filter(
      playlist => !Playlist.isDisabled(playlist));
  }

  const bandwidthPlaylists =
    enabledPlaylists.filter(Playlist.hasAttribute.bind(null, 'BANDWIDTH'));

  const rebufferingEstimates = bandwidthPlaylists.map((playlist) => {
    const syncPoint = syncController.getSyncPoint(playlist,
                                                  duration$$1,
                                                  currentTimeline,
                                                  currentTime);
    // If there is no sync point for this playlist, switching to it will require a
    // sync request first. This will double the request time
    const numRequests = syncPoint ? 1 : 2;
    const requestTimeEstimate = Playlist.estimateSegmentRequestTime(segmentDuration,
                                                                    bandwidth,
                                                                    playlist);
    const rebufferingImpact = (requestTimeEstimate * numRequests) - timeUntilRebuffer;

    return {
      playlist,
      rebufferingImpact
    };
  });

  const noRebufferingPlaylists = rebufferingEstimates.filter(
    (estimate) => estimate.rebufferingImpact <= 0);

  // Sort by bandwidth DESC
  stableSort(noRebufferingPlaylists,
    (a, b) => comparePlaylistBandwidth(b.playlist, a.playlist));

  if (noRebufferingPlaylists.length) {
    return noRebufferingPlaylists[0];
  }

  stableSort(rebufferingEstimates, (a, b) => a.rebufferingImpact - b.rebufferingImpact);

  return rebufferingEstimates[0] || null;
};

/**
 * Chooses the appropriate media playlist, which in this case is the lowest bitrate
 * one with video.  If no renditions with video exist, return the lowest audio rendition.
 *
 * Expects to be called within the context of an instance of HlsHandler
 *
 * @return {Object|null}
 *         {Object} return.playlist
 *         The lowest bitrate playlist that contains a video codec.  If no such rendition
 *         exists pick the lowest audio rendition.
 */
const lowestBitrateCompatibleVariantSelector = function() {
  // filter out any playlists that have been excluded due to
  // incompatible configurations or playback errors
  const playlists = this.playlists.master.playlists.filter(Playlist.isEnabled);

  // Sort ascending by bitrate
  stableSort(playlists,
    (a, b) => comparePlaylistBandwidth(a, b));

  // Parse and assume that playlists with no video codec have no video
  // (this is not necessarily true, although it is generally true).
  //
  // If an entire manifest has no valid videos everything will get filtered
  // out.
  const playlistsWithVideo = playlists.filter(
    playlist => parseCodecs(playlist.attributes.CODECS).videoCodec
  );

  return playlistsWithVideo[0] || null;
};

/**
 * @file segment-loader.js
 */

// in ms
const CHECK_BUFFER_DELAY = 500;

/**
 * Determines if we should call endOfStream on the media source based
 * on the state of the buffer or if appened segment was the final
 * segment in the playlist.
 *
 * @param {Object} playlist a media playlist object
 * @param {Object} mediaSource the MediaSource object
 * @param {Number} segmentIndex the index of segment we last appended
 * @returns {Boolean} do we need to call endOfStream on the MediaSource
 */
const detectEndOfStream = function(playlist, mediaSource, segmentIndex) {
  if (!playlist || !mediaSource) {
    return false;
  }

  let segments = playlist.segments;

  // determine a few boolean values to help make the branch below easier
  // to read
  let appendedLastSegment = segmentIndex === segments.length;

  // if we've buffered to the end of the video, we need to call endOfStream
  // so that MediaSources can trigger the `ended` event when it runs out of
  // buffered data instead of waiting for me
  return playlist.endList &&
    mediaSource.readyState === 'open' &&
    appendedLastSegment;
};

const finite = (num) => typeof num === 'number' && isFinite(num);

const illegalMediaSwitch = (loaderType, startingMedia, newSegmentMedia) => {
  // Although these checks should most likely cover non 'main' types, for now it narrows
  // the scope of our checks.
  if (loaderType !== 'main' || !startingMedia || !newSegmentMedia) {
    return null;
  }

  if (!newSegmentMedia.containsAudio && !newSegmentMedia.containsVideo) {
    return 'Neither audio nor video found in segment.';
  }

  if (startingMedia.containsVideo && !newSegmentMedia.containsVideo) {
    return 'Only audio found in segment when we expected video.' +
      ' We can\'t switch to audio only from a stream that had video.' +
      ' To get rid of this message, please add codec information to the manifest.';
  }

  if (!startingMedia.containsVideo && newSegmentMedia.containsVideo) {
    return 'Video found in segment when we expected only audio.' +
      ' We can\'t switch to a stream with video from an audio only stream.' +
      ' To get rid of this message, please add codec information to the manifest.';
  }

  return null;
};

/**
 * Calculates a time value that is safe to remove from the back buffer without interupting
 * playback.
 *
 * @param {TimeRange} seekable
 *        The current seekable range
 * @param {Number} currentTime
 *        The current time of the player
 * @param {Number} targetDuration
 *        The target duration of the current playlist
 * @return {Number}
 *         Time that is safe to remove from the back buffer without interupting playback
 */
const safeBackBufferTrimTime = (seekable$$1, currentTime, targetDuration) => {
  let removeToTime;

  if (seekable$$1.length &&
      seekable$$1.start(0) > 0 &&
      seekable$$1.start(0) < currentTime) {
    // If we have a seekable range use that as the limit for what can be removed safely
    removeToTime = seekable$$1.start(0);
  } else {
    // otherwise remove anything older than 30 seconds before the current play head
    removeToTime = currentTime - 30;
  }

  // Don't allow removing from the buffer within target duration of current time
  // to avoid the possibility of removing the GOP currently being played which could
  // cause playback stalls.
  return Math.min(removeToTime, currentTime - targetDuration);
};

const segmentInfoString = (segmentInfo) => {
  const {
    segment: {
      start,
      end
    },
    playlist: {
      mediaSequence: seq,
      id,
      segments = []
    },
    mediaIndex: index,
    timeline
  } = segmentInfo;

  return [
    `appending [${index}] of [${seq}, ${seq + segments.length}] from playlist [${id}]`,
    `[${start} => ${end}] in timeline [${timeline}]`
  ].join(' ');
};

/**
 * An object that manages segment loading and appending.
 *
 * @class SegmentLoader
 * @param {Object} options required and optional options
 * @extends videojs.EventTarget
 */
class SegmentLoader extends videojs.EventTarget {
  constructor(settings, options = {}) {
    super();
    // check pre-conditions
    if (!settings) {
      throw new TypeError('Initialization settings are required');
    }
    if (typeof settings.currentTime !== 'function') {
      throw new TypeError('No currentTime getter specified');
    }
    if (!settings.mediaSource) {
      throw new TypeError('No MediaSource specified');
    }
    // public properties
    this.bandwidth = settings.bandwidth;
    this.throughput = {rate: 0, count: 0};
    this.roundTrip = NaN;
    this.resetStats_();
    this.mediaIndex = null;

    // private settings
    this.hasPlayed_ = settings.hasPlayed;
    this.currentTime_ = settings.currentTime;
    this.seekable_ = settings.seekable;
    this.seeking_ = settings.seeking;
    this.duration_ = settings.duration;
    this.mediaSource_ = settings.mediaSource;
    this.hls_ = settings.hls;
    this.loaderType_ = settings.loaderType;
    this.startingMedia_ = void 0;
    this.segmentMetadataTrack_ = settings.segmentMetadataTrack;
    this.goalBufferLength_ = settings.goalBufferLength;
    this.sourceType_ = settings.sourceType;
    this.state_ = 'INIT';

    // private instance variables
    this.checkBufferTimeout_ = null;
    this.error_ = void 0;
    this.currentTimeline_ = -1;
    this.pendingSegment_ = null;
    this.mimeType_ = null;
    this.sourceUpdater_ = null;
    this.xhrOptions_ = null;

    // Fragmented mp4 playback
    this.activeInitSegmentId_ = null;
    this.initSegments_ = {};

    this.decrypter_ = settings.decrypter;

    // Manages the tracking and generation of sync-points, mappings
    // between a time in the display time and a segment index within
    // a playlist
    this.syncController_ = settings.syncController;
    this.syncPoint_ = {
      segmentIndex: 0,
      time: 0
    };

    this.syncController_.on('syncinfoupdate', () => this.trigger('syncinfoupdate'));

    this.mediaSource_.addEventListener('sourceopen', () => this.ended_ = false);

    // ...for determining the fetch location
    this.fetchAtBuffer_ = false;

    this.logger_ = logger(`SegmentLoader[${this.loaderType_}]`);

    Object.defineProperty(this, 'state', {
      get() {
        return this.state_;
      },
      set(newState) {
        if (newState !== this.state_) {
          this.logger_(`${this.state_} -> ${newState}`);
          this.state_ = newState;
        }
      }
    });
  }

  /**
   * reset all of our media stats
   *
   * @private
   */
  resetStats_() {
    this.mediaBytesTransferred = 0;
    this.mediaRequests = 0;
    this.mediaRequestsAborted = 0;
    this.mediaRequestsTimedout = 0;
    this.mediaRequestsErrored = 0;
    this.mediaTransferDuration = 0;
    this.mediaSecondsLoaded = 0;
  }

  /**
   * dispose of the SegmentLoader and reset to the default state
   */
  dispose() {
    this.state = 'DISPOSED';
    this.pause();
    this.abort_();
    if (this.sourceUpdater_) {
      this.sourceUpdater_.dispose();
    }
    this.resetStats_();
  }

  /**
   * abort anything that is currently doing on with the SegmentLoader
   * and reset to a default state
   */
  abort() {
    if (this.state !== 'WAITING') {
      if (this.pendingSegment_) {
        this.pendingSegment_ = null;
      }
      return;
    }

    this.abort_();

    // We aborted the requests we were waiting on, so reset the loader's state to READY
    // since we are no longer "waiting" on any requests. XHR callback is not always run
    // when the request is aborted. This will prevent the loader from being stuck in the
    // WAITING state indefinitely.
    this.state = 'READY';

    // don't wait for buffer check timeouts to begin fetching the
    // next segment
    if (!this.paused()) {
      this.monitorBuffer_();
    }
  }

  /**
   * abort all pending xhr requests and null any pending segements
   *
   * @private
   */
  abort_() {
    if (this.pendingSegment_) {
      this.pendingSegment_.abortRequests();
    }

    // clear out the segment being processed
    this.pendingSegment_ = null;
  }

  /**
   * set an error on the segment loader and null out any pending segements
   *
   * @param {Error} error the error to set on the SegmentLoader
   * @return {Error} the error that was set or that is currently set
   */
  error(error) {
    if (typeof error !== 'undefined') {
      this.error_ = error;
    }

    this.pendingSegment_ = null;
    return this.error_;
  }

  endOfStream() {
    this.ended_ = true;
    this.pause();
    this.trigger('ended');
  }

  /**
   * Indicates which time ranges are buffered
   *
   * @return {TimeRange}
   *         TimeRange object representing the current buffered ranges
   */
  buffered_() {
    if (!this.sourceUpdater_) {
      return videojs.createTimeRanges();
    }

    return this.sourceUpdater_.buffered();
  }

  /**
   * Gets and sets init segment for the provided map
   *
   * @param {Object} map
   *        The map object representing the init segment to get or set
   * @param {Boolean=} set
   *        If true, the init segment for the provided map should be saved
   * @return {Object}
   *         map object for desired init segment
   */
  initSegment(map, set = false) {
    if (!map) {
      return null;
    }

    const id = initSegmentId(map);
    let storedMap = this.initSegments_[id];

    if (set && !storedMap && map.bytes) {
      this.initSegments_[id] = storedMap = {
        resolvedUri: map.resolvedUri,
        byterange: map.byterange,
        bytes: map.bytes
      };
    }

    return storedMap || map;
  }

  /**
   * Returns true if all configuration required for loading is present, otherwise false.
   *
   * @return {Boolean} True if the all configuration is ready for loading
   * @private
   */
  couldBeginLoading_() {
    return this.playlist_ &&
           // the source updater is created when init_ is called, so either having a
           // source updater or being in the INIT state with a mimeType is enough
           // to say we have all the needed configuration to start loading.
           (this.sourceUpdater_ || (this.mimeType_ && this.state === 'INIT')) &&
           !this.paused();
  }

  /**
   * load a playlist and start to fill the buffer
   */
  load() {
    // un-pause
    this.monitorBuffer_();

    // if we don't have a playlist yet, keep waiting for one to be
    // specified
    if (!this.playlist_) {
      return;
    }

    // not sure if this is the best place for this
    this.syncController_.setDateTimeMapping(this.playlist_);

    // if all the configuration is ready, initialize and begin loading
    if (this.state === 'INIT' && this.couldBeginLoading_()) {
      return this.init_();
    }

    // if we're in the middle of processing a segment already, don't
    // kick off an additional segment request
    if (!this.couldBeginLoading_() ||
        (this.state !== 'READY' &&
        this.state !== 'INIT')) {
      return;
    }

    this.state = 'READY';
  }

  /**
   * Once all the starting parameters have been specified, begin
   * operation. This method should only be invoked from the INIT
   * state.
   *
   * @private
   */
  init_() {
    this.state = 'READY';
    this.sourceUpdater_ = new SourceUpdater(this.mediaSource_,
                                            this.mimeType_,
                                            this.loaderType_,
                                            this.sourceBufferEmitter_);
    this.resetEverything();
    return this.monitorBuffer_();
  }

  /**
   * set a playlist on the segment loader
   *
   * @param {PlaylistLoader} media the playlist to set on the segment loader
   */
  playlist(newPlaylist, options = {}) {
    if (!newPlaylist) {
      return;
    }

    let oldPlaylist = this.playlist_;
    let segmentInfo = this.pendingSegment_;

    this.playlist_ = newPlaylist;
    this.xhrOptions_ = options;

    // when we haven't started playing yet, the start of a live playlist
    // is always our zero-time so force a sync update each time the playlist
    // is refreshed from the server
    if (!this.hasPlayed_()) {
      newPlaylist.syncInfo = {
        mediaSequence: newPlaylist.mediaSequence,
        time: 0
      };
    }

    const oldId = oldPlaylist ? oldPlaylist.id : null;

    this.logger_(`playlist update [${oldId} => ${newPlaylist.id}]`);

    // in VOD, this is always a rendition switch (or we updated our syncInfo above)
    // in LIVE, we always want to update with new playlists (including refreshes)
    this.trigger('syncinfoupdate');

    // if we were unpaused but waiting for a playlist, start
    // buffering now
    if (this.state === 'INIT' && this.couldBeginLoading_()) {
      return this.init_();
    }

    if (!oldPlaylist || oldPlaylist.uri !== newPlaylist.uri) {
      if (this.mediaIndex !== null) {
        // we must "resync" the segment loader when we switch renditions and
        // the segment loader is already synced to the previous rendition
        this.resyncLoader();
      }

      // the rest of this function depends on `oldPlaylist` being defined
      return;
    }

    // we reloaded the same playlist so we are in a live scenario
    // and we will likely need to adjust the mediaIndex
    let mediaSequenceDiff = newPlaylist.mediaSequence - oldPlaylist.mediaSequence;

    this.logger_(`live window shift [${mediaSequenceDiff}]`);

    // update the mediaIndex on the SegmentLoader
    // this is important because we can abort a request and this value must be
    // equal to the last appended mediaIndex
    if (this.mediaIndex !== null) {
      this.mediaIndex -= mediaSequenceDiff;
    }

    // update the mediaIndex on the SegmentInfo object
    // this is important because we will update this.mediaIndex with this value
    // in `handleUpdateEnd_` after the segment has been successfully appended
    if (segmentInfo) {
      segmentInfo.mediaIndex -= mediaSequenceDiff;

      // we need to update the referenced segment so that timing information is
      // saved for the new playlist's segment, however, if the segment fell off the
      // playlist, we can leave the old reference and just lose the timing info
      if (segmentInfo.mediaIndex >= 0) {
        segmentInfo.segment = newPlaylist.segments[segmentInfo.mediaIndex];
      }
    }

    this.syncController_.saveExpiredSegmentInfo(oldPlaylist, newPlaylist);
  }

  /**
   * Prevent the loader from fetching additional segments. If there
   * is a segment request outstanding, it will finish processing
   * before the loader halts. A segment loader can be unpaused by
   * calling load().
   */
  pause() {
    if (this.checkBufferTimeout_) {
      window$1.clearTimeout(this.checkBufferTimeout_);

      this.checkBufferTimeout_ = null;
    }
  }

  /**
   * Returns whether the segment loader is fetching additional
   * segments when given the opportunity. This property can be
   * modified through calls to pause() and load().
   */
  paused() {
    return this.checkBufferTimeout_ === null;
  }

  /**
   * create/set the following mimetype on the SourceBuffer through a
   * SourceUpdater
   *
   * @param {String} mimeType the mime type string to use
   * @param {Object} sourceBufferEmitter an event emitter that fires when a source buffer
   * is added to the media source
   */
  mimeType(mimeType, sourceBufferEmitter) {
    if (this.mimeType_) {
      return;
    }

    this.mimeType_ = mimeType;
    this.sourceBufferEmitter_ = sourceBufferEmitter;
    // if we were unpaused but waiting for a sourceUpdater, start
    // buffering now
    if (this.state === 'INIT' && this.couldBeginLoading_()) {
      this.init_();
    }
  }

  /**
   * Delete all the buffered data and reset the SegmentLoader
   */
  resetEverything() {
    this.ended_ = false;
    this.resetLoader();
    this.remove(0, this.duration_());
    this.trigger('reseteverything');
  }

  /**
   * Force the SegmentLoader to resync and start loading around the currentTime instead
   * of starting at the end of the buffer
   *
   * Useful for fast quality changes
   */
  resetLoader() {
    this.fetchAtBuffer_ = false;
    this.resyncLoader();
  }

  /**
   * Force the SegmentLoader to restart synchronization and make a conservative guess
   * before returning to the simple walk-forward method
   */
  resyncLoader() {
    this.mediaIndex = null;
    this.syncPoint_ = null;
    this.abort();
  }

  /**
   * Remove any data in the source buffer between start and end times
   * @param {Number} start - the start time of the region to remove from the buffer
   * @param {Number} end - the end time of the region to remove from the buffer
   */
  remove(start, end) {
    if (this.sourceUpdater_) {
      this.sourceUpdater_.remove(start, end);
    }
    removeCuesFromTrack(start, end, this.segmentMetadataTrack_);
  }

  /**
   * (re-)schedule monitorBufferTick_ to run as soon as possible
   *
   * @private
   */
  monitorBuffer_() {
    if (this.checkBufferTimeout_) {
      window$1.clearTimeout(this.checkBufferTimeout_);
    }

    this.checkBufferTimeout_ = window$1.setTimeout(this.monitorBufferTick_.bind(this), 1);
  }

  /**
   * As long as the SegmentLoader is in the READY state, periodically
   * invoke fillBuffer_().
   *
   * @private
   */
  monitorBufferTick_() {
    if (this.state === 'READY') {
      this.fillBuffer_();
    }

    if (this.checkBufferTimeout_) {
      window$1.clearTimeout(this.checkBufferTimeout_);
    }

    this.checkBufferTimeout_ = window$1.setTimeout(this.monitorBufferTick_.bind(this),
                                                 CHECK_BUFFER_DELAY);
  }

  /**
   * fill the buffer with segements unless the sourceBuffers are
   * currently updating
   *
   * Note: this function should only ever be called by monitorBuffer_
   * and never directly
   *
   * @private
   */
  fillBuffer_() {
    if (this.sourceUpdater_.updating()) {
      return;
    }

    if (!this.syncPoint_) {
      this.syncPoint_ = this.syncController_.getSyncPoint(this.playlist_,
                                                          this.duration_(),
                                                          this.currentTimeline_,
                                                          this.currentTime_());
    }

    // see if we need to begin loading immediately
    let segmentInfo = this.checkBuffer_(this.buffered_(),
                                        this.playlist_,
                                        this.mediaIndex,
                                        this.hasPlayed_(),
                                        this.currentTime_(),
                                        this.syncPoint_);

    if (!segmentInfo) {
      return;
    }

    let isEndOfStream = detectEndOfStream(this.playlist_,
                                          this.mediaSource_,
                                          segmentInfo.mediaIndex);

    if (isEndOfStream) {
      this.endOfStream();
      return;
    }

    if (segmentInfo.mediaIndex === this.playlist_.segments.length - 1 &&
        this.mediaSource_.readyState === 'ended' &&
        !this.seeking_()) {
      return;
    }

    // We will need to change timestampOffset of the sourceBuffer if either of
    // the following conditions are true:
    // - The segment.timeline !== this.currentTimeline
    //   (we are crossing a discontinuity somehow)
    // - The "timestampOffset" for the start of this segment is less than
    //   the currently set timestampOffset
    if (segmentInfo.timeline !== this.currentTimeline_ ||
        ((segmentInfo.startOfSegment !== null) &&
        segmentInfo.startOfSegment < this.sourceUpdater_.timestampOffset())) {
      this.syncController_.reset();
      segmentInfo.timestampOffset = segmentInfo.startOfSegment;
    }

    this.loadSegment_(segmentInfo);
  }

  /**
   * Determines what segment request should be made, given current playback
   * state.
   *
   * @param {TimeRanges} buffered - the state of the buffer
   * @param {Object} playlist - the playlist object to fetch segments from
   * @param {Number} mediaIndex - the previous mediaIndex fetched or null
   * @param {Boolean} hasPlayed - a flag indicating whether we have played or not
   * @param {Number} currentTime - the playback position in seconds
   * @param {Object} syncPoint - a segment info object that describes the
   * @returns {Object} a segment request object that describes the segment to load
   */
  checkBuffer_(buffered, playlist, mediaIndex, hasPlayed, currentTime, syncPoint) {
    let lastBufferedEnd = 0;
    let startOfSegment;

    if (buffered.length) {
      lastBufferedEnd = buffered.end(buffered.length - 1);
    }

    let bufferedTime = Math.max(0, lastBufferedEnd - currentTime);

    if (!playlist.segments.length) {
      return null;
    }

    // if there is plenty of content buffered, and the video has
    // been played before relax for awhile
    if (bufferedTime >= this.goalBufferLength_()) {
      return null;
    }

    // if the video has not yet played once, and we already have
    // one segment downloaded do nothing
    if (!hasPlayed && bufferedTime >= 1) {
      return null;
    }

    // When the syncPoint is null, there is no way of determining a good
    // conservative segment index to fetch from
    // The best thing to do here is to get the kind of sync-point data by
    // making a request
    if (syncPoint === null) {
      mediaIndex = this.getSyncSegmentCandidate_(playlist);
      return this.generateSegmentInfo_(playlist, mediaIndex, null, true);
    }

    // Under normal playback conditions fetching is a simple walk forward
    if (mediaIndex !== null) {
      let segment = playlist.segments[mediaIndex];

      if (segment && segment.end) {
        startOfSegment = segment.end;
      } else {
        startOfSegment = lastBufferedEnd;
      }
      return this.generateSegmentInfo_(playlist, mediaIndex + 1, startOfSegment, false);
    }

    // There is a sync-point but the lack of a mediaIndex indicates that
    // we need to make a good conservative guess about which segment to
    // fetch
    if (this.fetchAtBuffer_) {
      // Find the segment containing the end of the buffer
      let mediaSourceInfo = Playlist.getMediaInfoForTime(playlist,
                                                         lastBufferedEnd,
                                                         syncPoint.segmentIndex,
                                                         syncPoint.time);

      mediaIndex = mediaSourceInfo.mediaIndex;
      startOfSegment = mediaSourceInfo.startTime;
    } else {
      // Find the segment containing currentTime
      let mediaSourceInfo = Playlist.getMediaInfoForTime(playlist,
                                                         currentTime,
                                                         syncPoint.segmentIndex,
                                                         syncPoint.time);

      mediaIndex = mediaSourceInfo.mediaIndex;
      startOfSegment = mediaSourceInfo.startTime;
    }

    return this.generateSegmentInfo_(playlist, mediaIndex, startOfSegment, false);
  }

  /**
   * The segment loader has no recourse except to fetch a segment in the
   * current playlist and use the internal timestamps in that segment to
   * generate a syncPoint. This function returns a good candidate index
   * for that process.
   *
   * @param {Object} playlist - the playlist object to look for a
   * @returns {Number} An index of a segment from the playlist to load
   */
  getSyncSegmentCandidate_(playlist) {
    if (this.currentTimeline_ === -1) {
      return 0;
    }

    let segmentIndexArray = playlist.segments
      .map((s, i) => {
        return {
          timeline: s.timeline,
          segmentIndex: i
        };
      }).filter(s => s.timeline === this.currentTimeline_);

    if (segmentIndexArray.length) {
      return segmentIndexArray[Math.min(segmentIndexArray.length - 1, 1)].segmentIndex;
    }

    return Math.max(playlist.segments.length - 1, 0);
  }

  generateSegmentInfo_(playlist, mediaIndex, startOfSegment, isSyncRequest) {
    if (mediaIndex < 0 || mediaIndex >= playlist.segments.length) {
      return null;
    }

    let segment = playlist.segments[mediaIndex];

    return {
      requestId: 'segment-loader-' + Math.random(),
      // resolve the segment URL relative to the playlist
      uri: segment.resolvedUri,
      // the segment's mediaIndex at the time it was requested
      mediaIndex,
      // whether or not to update the SegmentLoader's state with this
      // segment's mediaIndex
      isSyncRequest,
      startOfSegment,
      // the segment's playlist
      playlist,
      // unencrypted bytes of the segment
      bytes: null,
      // when a key is defined for this segment, the encrypted bytes
      encryptedBytes: null,
      // The target timestampOffset for this segment when we append it
      // to the source buffer
      timestampOffset: null,
      // The timeline that the segment is in
      timeline: segment.timeline,
      // The expected duration of the segment in seconds
      duration: segment.duration,
      // retain the segment in case the playlist updates while doing an async process
      segment
    };
  }

  /**
   * Determines if the network has enough bandwidth to complete the current segment
   * request in a timely manner. If not, the request will be aborted early and bandwidth
   * updated to trigger a playlist switch.
   *
   * @param {Object} stats
   *        Object containing stats about the request timing and size
   * @return {Boolean} True if the request was aborted, false otherwise
   * @private
   */
  abortRequestEarly_(stats) {
    if (this.hls_.tech_.paused() ||
        // Don't abort if the current playlist is on the lowestEnabledRendition
        // TODO: Replace using timeout with a boolean indicating whether this playlist is
        //       the lowestEnabledRendition.
        !this.xhrOptions_.timeout ||
        // Don't abort if we have no bandwidth information to estimate segment sizes
        !(this.playlist_.attributes.BANDWIDTH)) {
      return false;
    }

    // Wait at least 1 second since the first byte of data has been received before
    // using the calculated bandwidth from the progress event to allow the bitrate
    // to stabilize
    if (Date.now() - (stats.firstBytesReceivedAt || Date.now()) < 1000) {
      return false;
    }

    const currentTime = this.currentTime_();
    const measuredBandwidth = stats.bandwidth;
    const segmentDuration = this.pendingSegment_.duration;

    const requestTimeRemaining =
      Playlist.estimateSegmentRequestTime(segmentDuration,
                                          measuredBandwidth,
                                          this.playlist_,
                                          stats.bytesReceived);

    // Subtract 1 from the timeUntilRebuffer so we still consider an early abort
    // if we are only left with less than 1 second when the request completes.
    // A negative timeUntilRebuffering indicates we are already rebuffering
    const timeUntilRebuffer$$1 = timeUntilRebuffer(this.buffered_(),
                                                 currentTime,
                                                 this.hls_.tech_.playbackRate()) - 1;

    // Only consider aborting early if the estimated time to finish the download
    // is larger than the estimated time until the player runs out of forward buffer
    if (requestTimeRemaining <= timeUntilRebuffer$$1) {
      return false;
    }

    const switchCandidate = minRebufferMaxBandwidthSelector({
      master: this.hls_.playlists.master,
      currentTime,
      bandwidth: measuredBandwidth,
      duration: this.duration_(),
      segmentDuration,
      timeUntilRebuffer: timeUntilRebuffer$$1,
      currentTimeline: this.currentTimeline_,
      syncController: this.syncController_
    });

    if (!switchCandidate) {
      return;
    }

    const rebufferingImpact = requestTimeRemaining - timeUntilRebuffer$$1;

    const timeSavedBySwitching = rebufferingImpact - switchCandidate.rebufferingImpact;

    let minimumTimeSaving = 0.5;

    // If we are already rebuffering, increase the amount of variance we add to the
    // potential round trip time of the new request so that we are not too aggressive
    // with switching to a playlist that might save us a fraction of a second.
    if (timeUntilRebuffer$$1 <= TIME_FUDGE_FACTOR) {
      minimumTimeSaving = 1;
    }

    if (!switchCandidate.playlist ||
        switchCandidate.playlist.uri === this.playlist_.uri ||
        timeSavedBySwitching < minimumTimeSaving) {
      return false;
    }

    // set the bandwidth to that of the desired playlist being sure to scale by
    // BANDWIDTH_VARIANCE and add one so the playlist selector does not exclude it
    // don't trigger a bandwidthupdate as the bandwidth is artifial
    this.bandwidth =
      switchCandidate.playlist.attributes.BANDWIDTH * Config.BANDWIDTH_VARIANCE + 1;
    this.abort();
    this.trigger('earlyabort');
    return true;
  }

  /**
   * XHR `progress` event handler
   *
   * @param {Event}
   *        The XHR `progress` event
   * @param {Object} simpleSegment
   *        A simplified segment object copy
   * @private
   */
  handleProgress_(event, simpleSegment) {
    if (!this.pendingSegment_ ||
        simpleSegment.requestId !== this.pendingSegment_.requestId ||
        this.abortRequestEarly_(simpleSegment.stats)) {
      return;
    }

    this.trigger('progress');
  }

  /**
   * load a specific segment from a request into the buffer
   *
   * @private
   */
  loadSegment_(segmentInfo) {
    this.state = 'WAITING';
    this.pendingSegment_ = segmentInfo;
    this.trimBackBuffer_(segmentInfo);

    segmentInfo.abortRequests = mediaSegmentRequest(this.hls_.xhr,
      this.xhrOptions_,
      this.decrypter_,
      this.createSimplifiedSegmentObj_(segmentInfo),
      // progress callback
      this.handleProgress_.bind(this),
      this.segmentRequestFinished_.bind(this));
  }

  /**
   * trim the back buffer so that we don't have too much data
   * in the source buffer
   *
   * @private
   *
   * @param {Object} segmentInfo - the current segment
   */
  trimBackBuffer_(segmentInfo) {
    const removeToTime = safeBackBufferTrimTime(this.seekable_(),
                                                this.currentTime_(),
                                                this.playlist_.targetDuration || 10);

    // Chrome has a hard limit of 150MB of
    // buffer and a very conservative "garbage collector"
    // We manually clear out the old buffer to ensure
    // we don't trigger the QuotaExceeded error
    // on the source buffer during subsequent appends

    if (removeToTime > 0) {
      this.remove(0, removeToTime);
    }
  }

  /**
   * created a simplified copy of the segment object with just the
   * information necessary to perform the XHR and decryption
   *
   * @private
   *
   * @param {Object} segmentInfo - the current segment
   * @returns {Object} a simplified segment object copy
   */
  createSimplifiedSegmentObj_(segmentInfo) {
    const segment = segmentInfo.segment;
    const simpleSegment = {
      resolvedUri: segment.resolvedUri,
      byterange: segment.byterange,
      requestId: segmentInfo.requestId
    };

    if (segment.key) {
      // if the media sequence is greater than 2^32, the IV will be incorrect
      // assuming 10s segments, that would be about 1300 years
      const iv = segment.key.iv || new Uint32Array([
        0, 0, 0, segmentInfo.mediaIndex + segmentInfo.playlist.mediaSequence
      ]);

      simpleSegment.key = {
        resolvedUri: segment.key.resolvedUri,
        iv
      };
    }

    if (segment.map) {
      simpleSegment.map = this.initSegment(segment.map);
    }

    return simpleSegment;
  }

  /**
   * Handle the callback from the segmentRequest function and set the
   * associated SegmentLoader state and errors if necessary
   *
   * @private
   */
  segmentRequestFinished_(error, simpleSegment) {
    // every request counts as a media request even if it has been aborted
    // or canceled due to a timeout
    this.mediaRequests += 1;

    if (simpleSegment.stats) {
      this.mediaBytesTransferred += simpleSegment.stats.bytesReceived;
      this.mediaTransferDuration += simpleSegment.stats.roundTripTime;
    }

    // The request was aborted and the SegmentLoader has already been reset
    if (!this.pendingSegment_) {
      this.mediaRequestsAborted += 1;
      return;
    }

    // the request was aborted and the SegmentLoader has already started
    // another request. this can happen when the timeout for an aborted
    // request triggers due to a limitation in the XHR library
    // do not count this as any sort of request or we risk double-counting
    if (simpleSegment.requestId !== this.pendingSegment_.requestId) {
      return;
    }

    // an error occurred from the active pendingSegment_ so reset everything
    if (error) {
      this.pendingSegment_ = null;
      this.state = 'READY';

      // the requests were aborted just record the aborted stat and exit
      // this is not a true error condition and nothing corrective needs
      // to be done
      if (error.code === REQUEST_ERRORS.ABORTED) {
        this.mediaRequestsAborted += 1;
        return;
      }

      this.pause();

      // the error is really just that at least one of the requests timed-out
      // set the bandwidth to a very low value and trigger an ABR switch to
      // take emergency action
      if (error.code === REQUEST_ERRORS.TIMEOUT) {
        this.mediaRequestsTimedout += 1;
        this.bandwidth = 1;
        this.roundTrip = NaN;
        this.trigger('bandwidthupdate');
        return;
      }

      // if control-flow has arrived here, then the error is real
      // emit an error event to blacklist the current playlist
      this.mediaRequestsErrored += 1;
      this.error(error);
      this.trigger('error');
      return;
    }

    // the response was a success so set any bandwidth stats the request
    // generated for ABR purposes
    this.bandwidth = simpleSegment.stats.bandwidth;
    this.roundTrip = simpleSegment.stats.roundTripTime;

    // if this request included an initialization segment, save that data
    // to the initSegment cache
    if (simpleSegment.map) {
      simpleSegment.map = this.initSegment(simpleSegment.map, true);
    }

    this.processSegmentResponse_(simpleSegment);
  }

  /**
   * Move any important data from the simplified segment object
   * back to the real segment object for future phases
   *
   * @private
   */
  processSegmentResponse_(simpleSegment) {
    const segmentInfo = this.pendingSegment_;

    segmentInfo.bytes = simpleSegment.bytes;
    if (simpleSegment.map) {
      segmentInfo.segment.map.bytes = simpleSegment.map.bytes;
    }

    segmentInfo.endOfAllRequests = simpleSegment.endOfAllRequests;
    this.handleSegment_();
  }

  /**
   * append a decrypted segement to the SourceBuffer through a SourceUpdater
   *
   * @private
   */
  handleSegment_() {
    if (!this.pendingSegment_) {
      this.state = 'READY';
      return;
    }

    const segmentInfo = this.pendingSegment_;
    const segment = segmentInfo.segment;
    const timingInfo = this.syncController_.probeSegmentInfo(segmentInfo);

    // When we have our first timing info, determine what media types this loader is
    // dealing with. Although we're maintaining extra state, it helps to preserve the
    // separation of segment loader from the actual source buffers.
    if (typeof this.startingMedia_ === 'undefined' &&
        timingInfo &&
        // Guard against cases where we're not getting timing info at all until we are
        // certain that all streams will provide it.
        (timingInfo.containsAudio || timingInfo.containsVideo)) {
      this.startingMedia_ = {
        containsAudio: timingInfo.containsAudio,
        containsVideo: timingInfo.containsVideo
      };
    }

    const illegalMediaSwitchError =
      illegalMediaSwitch(this.loaderType_, this.startingMedia_, timingInfo);

    if (illegalMediaSwitchError) {
      this.error({
        message: illegalMediaSwitchError,
        blacklistDuration: Infinity
      });
      this.trigger('error');
      return;
    }

    if (segmentInfo.isSyncRequest) {
      this.trigger('syncinfoupdate');
      this.pendingSegment_ = null;
      this.state = 'READY';
      return;
    }

    if (segmentInfo.timestampOffset !== null &&
        segmentInfo.timestampOffset !== this.sourceUpdater_.timestampOffset()) {
      this.sourceUpdater_.timestampOffset(segmentInfo.timestampOffset);
      // fired when a timestamp offset is set in HLS (can also identify discontinuities)
      this.trigger('timestampoffset');
    }

    const timelineMapping = this.syncController_.mappingForTimeline(segmentInfo.timeline);

    if (timelineMapping !== null) {
      this.trigger({
        type: 'segmenttimemapping',
        mapping: timelineMapping
      });
    }

    this.state = 'APPENDING';

    // if the media initialization segment is changing, append it
    // before the content segment
    if (segment.map) {
      const initId = initSegmentId(segment.map);

      if (!this.activeInitSegmentId_ ||
          this.activeInitSegmentId_ !== initId) {
        const initSegment = this.initSegment(segment.map);

        this.sourceUpdater_.appendBuffer(initSegment.bytes, () => {
          this.activeInitSegmentId_ = initId;
        });
      }
    }

    segmentInfo.byteLength = segmentInfo.bytes.byteLength;
    if (typeof segment.start === 'number' && typeof segment.end === 'number') {
      this.mediaSecondsLoaded += segment.end - segment.start;
    } else {
      this.mediaSecondsLoaded += segment.duration;
    }

    this.logger_(segmentInfoString(segmentInfo));

    this.sourceUpdater_.appendBuffer(segmentInfo.bytes,
                                     this.handleUpdateEnd_.bind(this));
  }

  /**
   * callback to run when appendBuffer is finished. detects if we are
   * in a good state to do things with the data we got, or if we need
   * to wait for more
   *
   * @private
   */
  handleUpdateEnd_() {
    if (!this.pendingSegment_) {
      this.state = 'READY';
      if (!this.paused()) {
        this.monitorBuffer_();
      }
      return;
    }

    const segmentInfo = this.pendingSegment_;
    const segment = segmentInfo.segment;
    const isWalkingForward = this.mediaIndex !== null;

    this.pendingSegment_ = null;
    this.recordThroughput_(segmentInfo);
    this.addSegmentMetadataCue_(segmentInfo);

    this.state = 'READY';

    this.mediaIndex = segmentInfo.mediaIndex;
    this.fetchAtBuffer_ = true;
    this.currentTimeline_ = segmentInfo.timeline;

    // We must update the syncinfo to recalculate the seekable range before
    // the following conditional otherwise it may consider this a bad "guess"
    // and attempt to resync when the post-update seekable window and live
    // point would mean that this was the perfect segment to fetch
    this.trigger('syncinfoupdate');

    // If we previously appended a segment that ends more than 3 targetDurations before
    // the currentTime_ that means that our conservative guess was too conservative.
    // In that case, reset the loader state so that we try to use any information gained
    // from the previous request to create a new, more accurate, sync-point.
    if (segment.end &&
        this.currentTime_() - segment.end > segmentInfo.playlist.targetDuration * 3) {
      this.resetEverything();
      return;
    }

    // Don't do a rendition switch unless we have enough time to get a sync segment
    // and conservatively guess
    if (isWalkingForward) {
      this.trigger('bandwidthupdate');
    }
    this.trigger('progress');

    // any time an update finishes and the last segment is in the
    // buffer, end the stream. this ensures the "ended" event will
    // fire if playback reaches that point.
    const isEndOfStream = detectEndOfStream(segmentInfo.playlist,
                                            this.mediaSource_,
                                            segmentInfo.mediaIndex + 1);

    if (isEndOfStream) {
      this.endOfStream();
    }

    if (!this.paused()) {
      this.monitorBuffer_();
    }
  }

  /**
   * Records the current throughput of the decrypt, transmux, and append
   * portion of the semgment pipeline. `throughput.rate` is a the cumulative
   * moving average of the throughput. `throughput.count` is the number of
   * data points in the average.
   *
   * @private
   * @param {Object} segmentInfo the object returned by loadSegment
   */
  recordThroughput_(segmentInfo) {
    const rate = this.throughput.rate;
    // Add one to the time to ensure that we don't accidentally attempt to divide
    // by zero in the case where the throughput is ridiculously high
    const segmentProcessingTime =
      Date.now() - segmentInfo.endOfAllRequests + 1;
    // Multiply by 8000 to convert from bytes/millisecond to bits/second
    const segmentProcessingThroughput =
      Math.floor((segmentInfo.byteLength / segmentProcessingTime) * 8 * 1000);

    // This is just a cumulative moving average calculation:
    //   newAvg = oldAvg + (sample - oldAvg) / (sampleCount + 1)
    this.throughput.rate +=
      (segmentProcessingThroughput - rate) / (++this.throughput.count);
  }

  /**
   * Adds a cue to the segment-metadata track with some metadata information about the
   * segment
   *
   * @private
   * @param {Object} segmentInfo
   *        the object returned by loadSegment
   * @method addSegmentMetadataCue_
   */
  addSegmentMetadataCue_(segmentInfo) {
    if (!this.segmentMetadataTrack_) {
      return;
    }

    const segment = segmentInfo.segment;
    const start = segment.start;
    const end = segment.end;

    // Do not try adding the cue if the start and end times are invalid.
    if (!finite(start) || !finite(end)) {
      return;
    }

    removeCuesFromTrack(start, end, this.segmentMetadataTrack_);

    const Cue = window$1.WebKitDataCue || window$1.VTTCue;
    const value = {
      bandwidth: segmentInfo.playlist.attributes.BANDWIDTH,
      resolution: segmentInfo.playlist.attributes.RESOLUTION,
      codecs: segmentInfo.playlist.attributes.CODECS,
      byteLength: segmentInfo.byteLength,
      uri: segmentInfo.uri,
      timeline: segmentInfo.timeline,
      playlist: segmentInfo.playlist.uri,
      start,
      end
    };
    const data = JSON.stringify(value);
    const cue = new Cue(start, end, data);

    // Attach the metadata to the value property of the cue to keep consistency between
    // the differences of WebKitDataCue in safari and VTTCue in other browsers
    cue.value = value;

    this.segmentMetadataTrack_.addCue(cue);
  }
}

/**
 * @file vtt-segment-loader.js
 */

const VTT_LINE_TERMINATORS =
  new Uint8Array('\n\n'.split('').map(char => char.charCodeAt(0)));

const uintToString = function(uintArray) {
  return String.fromCharCode.apply(null, uintArray);
};

/**
 * An object that manages segment loading and appending.
 *
 * @class VTTSegmentLoader
 * @param {Object} options required and optional options
 * @extends videojs.EventTarget
 */
class VTTSegmentLoader extends SegmentLoader {
  constructor(settings, options = {}) {
    super(settings, options);

    // SegmentLoader requires a MediaSource be specified or it will throw an error;
    // however, VTTSegmentLoader has no need of a media source, so delete the reference
    this.mediaSource_ = null;

    this.subtitlesTrack_ = null;
  }

  /**
   * Indicates which time ranges are buffered
   *
   * @return {TimeRange}
   *         TimeRange object representing the current buffered ranges
   */
  buffered_() {
    if (!this.subtitlesTrack_ || !this.subtitlesTrack_.cues.length) {
      return videojs.createTimeRanges();
    }

    const cues = this.subtitlesTrack_.cues;
    let start = cues[0].startTime;
    let end = cues[cues.length - 1].startTime;

    return videojs.createTimeRanges([[start, end]]);
  }

  /**
   * Gets and sets init segment for the provided map
   *
   * @param {Object} map
   *        The map object representing the init segment to get or set
   * @param {Boolean=} set
   *        If true, the init segment for the provided map should be saved
   * @return {Object}
   *         map object for desired init segment
   */
  initSegment(map, set = false) {
    if (!map) {
      return null;
    }

    const id = initSegmentId(map);
    let storedMap = this.initSegments_[id];

    if (set && !storedMap && map.bytes) {
      // append WebVTT line terminators to the media initialization segment if it exists
      // to follow the WebVTT spec (https://w3c.github.io/webvtt/#file-structure) that
      // requires two or more WebVTT line terminators between the WebVTT header and the
      // rest of the file
      const combinedByteLength = VTT_LINE_TERMINATORS.byteLength + map.bytes.byteLength;
      const combinedSegment = new Uint8Array(combinedByteLength);

      combinedSegment.set(map.bytes);
      combinedSegment.set(VTT_LINE_TERMINATORS, map.bytes.byteLength);

      this.initSegments_[id] = storedMap = {
        resolvedUri: map.resolvedUri,
        byterange: map.byterange,
        bytes: combinedSegment
      };
    }

    return storedMap || map;
  }

  /**
   * Returns true if all configuration required for loading is present, otherwise false.
   *
   * @return {Boolean} True if the all configuration is ready for loading
   * @private
   */
  couldBeginLoading_() {
    return this.playlist_ &&
           this.subtitlesTrack_ &&
           !this.paused();
  }

  /**
   * Once all the starting parameters have been specified, begin
   * operation. This method should only be invoked from the INIT
   * state.
   *
   * @private
   */
  init_() {
    this.state = 'READY';
    this.resetEverything();
    return this.monitorBuffer_();
  }

  /**
   * Set a subtitle track on the segment loader to add subtitles to
   *
   * @param {TextTrack=} track
   *        The text track to add loaded subtitles to
   * @return {TextTrack}
   *        Returns the subtitles track
   */
  track(track) {
    if (typeof track === 'undefined') {
      return this.subtitlesTrack_;
    }

    this.subtitlesTrack_ = track;

    // if we were unpaused but waiting for a sourceUpdater, start
    // buffering now
    if (this.state === 'INIT' && this.couldBeginLoading_()) {
      this.init_();
    }

    return this.subtitlesTrack_;
  }

  /**
   * Remove any data in the source buffer between start and end times
   * @param {Number} start - the start time of the region to remove from the buffer
   * @param {Number} end - the end time of the region to remove from the buffer
   */
  remove(start, end) {
    removeCuesFromTrack(start, end, this.subtitlesTrack_);
  }

  /**
   * fill the buffer with segements unless the sourceBuffers are
   * currently updating
   *
   * Note: this function should only ever be called by monitorBuffer_
   * and never directly
   *
   * @private
   */
  fillBuffer_() {
    if (!this.syncPoint_) {
      this.syncPoint_ = this.syncController_.getSyncPoint(this.playlist_,
                                                          this.duration_(),
                                                          this.currentTimeline_,
                                                          this.currentTime_());
    }

    // see if we need to begin loading immediately
    let segmentInfo = this.checkBuffer_(this.buffered_(),
                                        this.playlist_,
                                        this.mediaIndex,
                                        this.hasPlayed_(),
                                        this.currentTime_(),
                                        this.syncPoint_);

    segmentInfo = this.skipEmptySegments_(segmentInfo);

    if (!segmentInfo) {
      return;
    }

    if (this.syncController_.timestampOffsetForTimeline(segmentInfo.timeline) === null) {
      // We don't have the timestamp offset that we need to sync subtitles.
      // Rerun on a timestamp offset or user interaction.
      let checkTimestampOffset = () => {
        this.state = 'READY';
        if (!this.paused()) {
          // if not paused, queue a buffer check as soon as possible
          this.monitorBuffer_();
        }
      };

      this.syncController_.one('timestampoffset', checkTimestampOffset);
      this.state = 'WAITING_ON_TIMELINE';
      return;
    }

    this.loadSegment_(segmentInfo);
  }

  /**
   * Prevents the segment loader from requesting segments we know contain no subtitles
   * by walking forward until we find the next segment that we don't know whether it is
   * empty or not.
   *
   * @param {Object} segmentInfo
   *        a segment info object that describes the current segment
   * @return {Object}
   *         a segment info object that describes the current segment
   */
  skipEmptySegments_(segmentInfo) {
    while (segmentInfo && segmentInfo.segment.empty) {
      segmentInfo = this.generateSegmentInfo_(
        segmentInfo.playlist,
        segmentInfo.mediaIndex + 1,
        segmentInfo.startOfSegment + segmentInfo.duration,
        segmentInfo.isSyncRequest);
    }
    return segmentInfo;
  }

  /**
   * append a decrypted segement to the SourceBuffer through a SourceUpdater
   *
   * @private
   */
  handleSegment_() {
    if (!this.pendingSegment_ || !this.subtitlesTrack_) {
      this.state = 'READY';
      return;
    }

    this.state = 'APPENDING';

    let segmentInfo = this.pendingSegment_;
    let segment = segmentInfo.segment;

    // Make sure that vttjs has loaded, otherwise, wait till it finished loading
    if (typeof window$1.WebVTT !== 'function' &&
        this.subtitlesTrack_ &&
        this.subtitlesTrack_.tech_) {

      const loadHandler = () => {
        this.handleSegment_();
      };

      this.state = 'WAITING_ON_VTTJS';
      this.subtitlesTrack_.tech_.one('vttjsloaded', loadHandler);
      this.subtitlesTrack_.tech_.one('vttjserror', () => {
        this.subtitlesTrack_.tech_.off('vttjsloaded', loadHandler);
        this.error({
          message: 'Error loading vtt.js'
        });
        this.state = 'READY';
        this.pause();
        this.trigger('error');
      });

      return;
    }

    segment.requested = true;

    try {
      this.parseVTTCues_(segmentInfo);
    } catch (e) {
      this.error({
        message: e.message
      });
      this.state = 'READY';
      this.pause();
      return this.trigger('error');
    }

    this.updateTimeMapping_(segmentInfo,
                            this.syncController_.timelines[segmentInfo.timeline],
                            this.playlist_);

    if (segmentInfo.isSyncRequest) {
      this.trigger('syncinfoupdate');
      this.pendingSegment_ = null;
      this.state = 'READY';
      return;
    }

    segmentInfo.byteLength = segmentInfo.bytes.byteLength;

    this.mediaSecondsLoaded += segment.duration;

    if (segmentInfo.cues.length) {
     // remove any overlapping cues to prevent doubling
      this.remove(segmentInfo.cues[0].endTime,
                  segmentInfo.cues[segmentInfo.cues.length - 1].endTime);
    }

    segmentInfo.cues.forEach((cue) => {
      this.subtitlesTrack_.addCue(cue);
    });

    this.handleUpdateEnd_();
  }

  /**
   * Uses the WebVTT parser to parse the segment response
   *
   * @param {Object} segmentInfo
   *        a segment info object that describes the current segment
   * @private
   */
  parseVTTCues_(segmentInfo) {
    let decoder;
    let decodeBytesToString = false;

    if (typeof window$1.TextDecoder === 'function') {
      decoder = new window$1.TextDecoder('utf8');
    } else {
      decoder = window$1.WebVTT.StringDecoder();
      decodeBytesToString = true;
    }

    const parser = new window$1.WebVTT.Parser(window$1,
                                            window$1.vttjs,
                                            decoder);

    segmentInfo.cues = [];
    segmentInfo.timestampmap = { MPEGTS: 0, LOCAL: 0 };

    parser.oncue = segmentInfo.cues.push.bind(segmentInfo.cues);
    parser.ontimestampmap = (map) => segmentInfo.timestampmap = map;
    parser.onparsingerror = (error) => {
      videojs.log.warn('Error encountered when parsing cues: ' + error.message);
    };

    if (segmentInfo.segment.map) {
      let mapData = segmentInfo.segment.map.bytes;

      if (decodeBytesToString) {
        mapData = uintToString(mapData);
      }

      parser.parse(mapData);
    }

    let segmentData = segmentInfo.bytes;

    if (decodeBytesToString) {
      segmentData = uintToString(segmentData);
    }

    parser.parse(segmentData);
    parser.flush();
  }

  /**
   * Updates the start and end times of any cues parsed by the WebVTT parser using
   * the information parsed from the X-TIMESTAMP-MAP header and a TS to media time mapping
   * from the SyncController
   *
   * @param {Object} segmentInfo
   *        a segment info object that describes the current segment
   * @param {Object} mappingObj
   *        object containing a mapping from TS to media time
   * @param {Object} playlist
   *        the playlist object containing the segment
   * @private
   */
  updateTimeMapping_(segmentInfo, mappingObj, playlist) {
    const segment = segmentInfo.segment;

    if (!mappingObj) {
      // If the sync controller does not have a mapping of TS to Media Time for the
      // timeline, then we don't have enough information to update the cue
      // start/end times
      return;
    }

    if (!segmentInfo.cues.length) {
      // If there are no cues, we also do not have enough information to figure out
      // segment timing. Mark that the segment contains no cues so we don't re-request
      // an empty segment.
      segment.empty = true;
      return;
    }

    const timestampmap = segmentInfo.timestampmap;
    const diff = (timestampmap.MPEGTS / 90000) - timestampmap.LOCAL + mappingObj.mapping;

    segmentInfo.cues.forEach((cue) => {
      // First convert cue time to TS time using the timestamp-map provided within the vtt
      cue.startTime += diff;
      cue.endTime += diff;
    });

    if (!playlist.syncInfo) {
      const firstStart = segmentInfo.cues[0].startTime;
      const lastStart = segmentInfo.cues[segmentInfo.cues.length - 1].startTime;

      playlist.syncInfo = {
        mediaSequence: playlist.mediaSequence + segmentInfo.mediaIndex,
        time: Math.min(firstStart, lastStart - segment.duration)
      };
    }
  }
}

/**
 * @file ad-cue-tags.js
 */

/**
 * Searches for an ad cue that overlaps with the given mediaTime
 */
const findAdCue = function(track, mediaTime) {
  let cues = track.cues;

  for (let i = 0; i < cues.length; i++) {
    let cue = cues[i];

    if (mediaTime >= cue.adStartTime && mediaTime <= cue.adEndTime) {
      return cue;
    }
  }
  return null;
};

const updateAdCues = function(media, track, offset = 0) {
  if (!media.segments) {
    return;
  }

  let mediaTime = offset;
  let cue;

  for (let i = 0; i < media.segments.length; i++) {
    let segment = media.segments[i];

    if (!cue) {
      // Since the cues will span for at least the segment duration, adding a fudge
      // factor of half segment duration will prevent duplicate cues from being
      // created when timing info is not exact (e.g. cue start time initialized
      // at 10.006677, but next call mediaTime is 10.003332 )
      cue = findAdCue(track, mediaTime + (segment.duration / 2));
    }

    if (cue) {
      if ('cueIn' in segment) {
        // Found a CUE-IN so end the cue
        cue.endTime = mediaTime;
        cue.adEndTime = mediaTime;
        mediaTime += segment.duration;
        cue = null;
        continue;
      }

      if (mediaTime < cue.endTime) {
        // Already processed this mediaTime for this cue
        mediaTime += segment.duration;
        continue;
      }

      // otherwise extend cue until a CUE-IN is found
      cue.endTime += segment.duration;

    } else {
      if ('cueOut' in segment) {
        cue = new window$1.VTTCue(mediaTime,
                                mediaTime + segment.duration,
                                segment.cueOut);
        cue.adStartTime = mediaTime;
        // Assumes tag format to be
        // #EXT-X-CUE-OUT:30
        cue.adEndTime = mediaTime + parseFloat(segment.cueOut);
        track.addCue(cue);
      }

      if ('cueOutCont' in segment) {
        // Entered into the middle of an ad cue
        let adOffset;
        let adTotal;

        // Assumes tag formate to be
        // #EXT-X-CUE-OUT-CONT:10/30
        [adOffset, adTotal] = segment.cueOutCont.split('/').map(parseFloat);

        cue = new window$1.VTTCue(mediaTime,
                                mediaTime + segment.duration,
                                '');
        cue.adStartTime = mediaTime - adOffset;
        cue.adEndTime = cue.adStartTime + adTotal;
        track.addCue(cue);
      }
    }
    mediaTime += segment.duration;
  }
};

/**
 * @file sync-controller.js
 */

const tsprobe = tsInspector.inspect;

const syncPointStrategies = [
  // Stategy "VOD": Handle the VOD-case where the sync-point is *always*
  //                the equivalence display-time 0 === segment-index 0
  {
    name: 'VOD',
    run: (syncController, playlist, duration$$1, currentTimeline, currentTime) => {
      if (duration$$1 !== Infinity) {
        let syncPoint = {
          time: 0,
          segmentIndex: 0
        };

        return syncPoint;
      }
      return null;
    }
  },
  // Stategy "ProgramDateTime": We have a program-date-time tag in this playlist
  {
    name: 'ProgramDateTime',
    run: (syncController, playlist, duration$$1, currentTimeline, currentTime) => {
      if (!syncController.datetimeToDisplayTime) {
        return null;
      }

      let segments = playlist.segments || [];
      let syncPoint = null;
      let lastDistance = null;

      currentTime = currentTime || 0;

      for (let i = 0; i < segments.length; i++) {
        let segment = segments[i];

        if (segment.dateTimeObject) {
          let segmentTime = segment.dateTimeObject.getTime() / 1000;
          let segmentStart = segmentTime + syncController.datetimeToDisplayTime;
          let distance = Math.abs(currentTime - segmentStart);

          // Once the distance begins to increase, we have passed
          // currentTime and can stop looking for better candidates
          if (lastDistance !== null && lastDistance < distance) {
            break;
          }

          lastDistance = distance;
          syncPoint = {
            time: segmentStart,
            segmentIndex: i
          };
        }
      }
      return syncPoint;
    }
  },
  // Stategy "Segment": We have a known time mapping for a timeline and a
  //                    segment in the current timeline with timing data
  {
    name: 'Segment',
    run: (syncController, playlist, duration$$1, currentTimeline, currentTime) => {
      let segments = playlist.segments || [];
      let syncPoint = null;
      let lastDistance = null;

      currentTime = currentTime || 0;

      for (let i = 0; i < segments.length; i++) {
        let segment = segments[i];

        if (segment.timeline === currentTimeline &&
            typeof segment.start !== 'undefined') {
          let distance = Math.abs(currentTime - segment.start);

          // Once the distance begins to increase, we have passed
          // currentTime and can stop looking for better candidates
          if (lastDistance !== null && lastDistance < distance) {
            break;
          }

          if (!syncPoint || lastDistance === null || lastDistance >= distance) {
            lastDistance = distance;
            syncPoint = {
              time: segment.start,
              segmentIndex: i
            };
          }

        }
      }
      return syncPoint;
    }
  },
  // Stategy "Discontinuity": We have a discontinuity with a known
  //                          display-time
  {
    name: 'Discontinuity',
    run: (syncController, playlist, duration$$1, currentTimeline, currentTime) => {
      let syncPoint = null;

      currentTime = currentTime || 0;

      if (playlist.discontinuityStarts && playlist.discontinuityStarts.length) {
        let lastDistance = null;

        for (let i = 0; i < playlist.discontinuityStarts.length; i++) {
          let segmentIndex = playlist.discontinuityStarts[i];
          let discontinuity = playlist.discontinuitySequence + i + 1;
          let discontinuitySync = syncController.discontinuities[discontinuity];

          if (discontinuitySync) {
            let distance = Math.abs(currentTime - discontinuitySync.time);

            // Once the distance begins to increase, we have passed
            // currentTime and can stop looking for better candidates
            if (lastDistance !== null && lastDistance < distance) {
              break;
            }

            if (!syncPoint || lastDistance === null || lastDistance >= distance) {
              lastDistance = distance;
              syncPoint = {
                time: discontinuitySync.time,
                segmentIndex
              };
            }
          }
        }
      }
      return syncPoint;
    }
  },
  // Stategy "Playlist": We have a playlist with a known mapping of
  //                     segment index to display time
  {
    name: 'Playlist',
    run: (syncController, playlist, duration$$1, currentTimeline, currentTime) => {
      if (playlist.syncInfo) {
        let syncPoint = {
          time: playlist.syncInfo.time,
          segmentIndex: playlist.syncInfo.mediaSequence - playlist.mediaSequence
        };

        return syncPoint;
      }
      return null;
    }
  }
];

class SyncController extends videojs.EventTarget {
  constructor(options = {}) {
    super();
    // Segment Loader state variables...
    // ...for synching across variants
    this.inspectCache_ = undefined;

    // ...for synching across variants
    this.timelines = [];
    this.discontinuities = [];
    this.datetimeToDisplayTime = null;

    this.logger_ = logger('SyncController');
  }

  /**
   * Find a sync-point for the playlist specified
   *
   * A sync-point is defined as a known mapping from display-time to
   * a segment-index in the current playlist.
   *
   * @param {Playlist} playlist
   *        The playlist that needs a sync-point
   * @param {Number} duration
   *        Duration of the MediaSource (Infinite if playing a live source)
   * @param {Number} currentTimeline
   *        The last timeline from which a segment was loaded
   * @returns {Object}
   *          A sync-point object
   */
  getSyncPoint(playlist, duration$$1, currentTimeline, currentTime) {
    const syncPoints = this.runStrategies_(playlist,
                                           duration$$1,
                                           currentTimeline,
                                           currentTime);

    if (!syncPoints.length) {
      // Signal that we need to attempt to get a sync-point manually
      // by fetching a segment in the playlist and constructing
      // a sync-point from that information
      return null;
    }

    // Now find the sync-point that is closest to the currentTime because
    // that should result in the most accurate guess about which segment
    // to fetch
    return this.selectSyncPoint_(syncPoints, { key: 'time', value: currentTime });
  }

  /**
   * Calculate the amount of time that has expired off the playlist during playback
   *
   * @param {Playlist} playlist
   *        Playlist object to calculate expired from
   * @param {Number} duration
   *        Duration of the MediaSource (Infinity if playling a live source)
   * @returns {Number|null}
   *          The amount of time that has expired off the playlist during playback. Null
   *          if no sync-points for the playlist can be found.
   */
  getExpiredTime(playlist, duration$$1) {
    if (!playlist || !playlist.segments) {
      return null;
    }

    const syncPoints = this.runStrategies_(playlist,
                                           duration$$1,
                                           playlist.discontinuitySequence,
                                           0);

    // Without sync-points, there is not enough information to determine the expired time
    if (!syncPoints.length) {
      return null;
    }

    const syncPoint = this.selectSyncPoint_(syncPoints, {
      key: 'segmentIndex',
      value: 0
    });

    // If the sync-point is beyond the start of the playlist, we want to subtract the
    // duration from index 0 to syncPoint.segmentIndex instead of adding.
    if (syncPoint.segmentIndex > 0) {
      syncPoint.time *= -1;
    }

    return Math.abs(syncPoint.time + sumDurations(playlist, syncPoint.segmentIndex, 0));
  }

  /**
   * Runs each sync-point strategy and returns a list of sync-points returned by the
   * strategies
   *
   * @private
   * @param {Playlist} playlist
   *        The playlist that needs a sync-point
   * @param {Number} duration
   *        Duration of the MediaSource (Infinity if playing a live source)
   * @param {Number} currentTimeline
   *        The last timeline from which a segment was loaded
   * @returns {Array}
   *          A list of sync-point objects
   */
  runStrategies_(playlist, duration$$1, currentTimeline, currentTime) {
    let syncPoints = [];

    // Try to find a sync-point in by utilizing various strategies...
    for (let i = 0; i < syncPointStrategies.length; i++) {
      let strategy = syncPointStrategies[i];
      let syncPoint = strategy.run(this,
                                   playlist,
                                   duration$$1,
                                   currentTimeline,
                                   currentTime);

      if (syncPoint) {
        syncPoint.strategy = strategy.name;
        syncPoints.push({
          strategy: strategy.name,
          syncPoint
        });
      }
    }

    return syncPoints;
  }

  /**
   * Selects the sync-point nearest the specified target
   *
   * @private
   * @param {Array} syncPoints
   *        List of sync-points to select from
   * @param {Object} target
   *        Object specifying the property and value we are targeting
   * @param {String} target.key
   *        Specifies the property to target. Must be either 'time' or 'segmentIndex'
   * @param {Number} target.value
   *        The value to target for the specified key.
   * @returns {Object}
   *          The sync-point nearest the target
   */
  selectSyncPoint_(syncPoints, target) {
    let bestSyncPoint = syncPoints[0].syncPoint;
    let bestDistance = Math.abs(syncPoints[0].syncPoint[target.key] - target.value);
    let bestStrategy = syncPoints[0].strategy;

    for (let i = 1; i < syncPoints.length; i++) {
      let newDistance = Math.abs(syncPoints[i].syncPoint[target.key] - target.value);

      if (newDistance < bestDistance) {
        bestDistance = newDistance;
        bestSyncPoint = syncPoints[i].syncPoint;
        bestStrategy = syncPoints[i].strategy;
      }
    }

    this.logger_(`syncPoint for [${target.key}: ${target.value}] chosen with strategy` +
      ` [${bestStrategy}]: [time:${bestSyncPoint.time},` +
      ` segmentIndex:${bestSyncPoint.segmentIndex}]`);

    return bestSyncPoint;
  }

  /**
   * Save any meta-data present on the segments when segments leave
   * the live window to the playlist to allow for synchronization at the
   * playlist level later.
   *
   * @param {Playlist} oldPlaylist - The previous active playlist
   * @param {Playlist} newPlaylist - The updated and most current playlist
   */
  saveExpiredSegmentInfo(oldPlaylist, newPlaylist) {
    let mediaSequenceDiff = newPlaylist.mediaSequence - oldPlaylist.mediaSequence;

    // When a segment expires from the playlist and it has a start time
    // save that information as a possible sync-point reference in future
    for (let i = mediaSequenceDiff - 1; i >= 0; i--) {
      let lastRemovedSegment = oldPlaylist.segments[i];

      if (lastRemovedSegment && typeof lastRemovedSegment.start !== 'undefined') {
        newPlaylist.syncInfo = {
          mediaSequence: oldPlaylist.mediaSequence + i,
          time: lastRemovedSegment.start
        };
        this.logger_(`playlist refresh sync: [time:${newPlaylist.syncInfo.time},` +
          ` mediaSequence: ${newPlaylist.syncInfo.mediaSequence}]`);
        this.trigger('syncinfoupdate');
        break;
      }
    }
  }

  /**
   * Save the mapping from playlist's ProgramDateTime to display. This should
   * only ever happen once at the start of playback.
   *
   * @param {Playlist} playlist - The currently active playlist
   */
  setDateTimeMapping(playlist) {
    if (!this.datetimeToDisplayTime &&
        playlist.segments &&
        playlist.segments.length &&
        playlist.segments[0].dateTimeObject) {
      let playlistTimestamp = playlist.segments[0].dateTimeObject.getTime() / 1000;

      this.datetimeToDisplayTime = -playlistTimestamp;
    }
  }

  /**
   * Reset the state of the inspection cache when we do a rendition
   * switch
   */
  reset() {
    this.inspectCache_ = undefined;
  }

  /**
   * Probe or inspect a fmp4 or an mpeg2-ts segment to determine the start
   * and end of the segment in it's internal "media time". Used to generate
   * mappings from that internal "media time" to the display time that is
   * shown on the player.
   *
   * @param {SegmentInfo} segmentInfo - The current active request information
   */
  probeSegmentInfo(segmentInfo) {
    const segment = segmentInfo.segment;
    const playlist = segmentInfo.playlist;
    let timingInfo;

    if (segment.map) {
      timingInfo = this.probeMp4Segment_(segmentInfo);
    } else {
      timingInfo = this.probeTsSegment_(segmentInfo);
    }

    if (timingInfo) {
      if (this.calculateSegmentTimeMapping_(segmentInfo, timingInfo)) {
        this.saveDiscontinuitySyncInfo_(segmentInfo);

        // If the playlist does not have sync information yet, record that information
        // now with segment timing information
        if (!playlist.syncInfo) {
          playlist.syncInfo = {
            mediaSequence: playlist.mediaSequence + segmentInfo.mediaIndex,
            time: segment.start
          };
        }
      }
    }

    return timingInfo;
  }

  /**
   * Probe an fmp4 or an mpeg2-ts segment to determine the start of the segment
   * in it's internal "media time".
   *
   * @private
   * @param {SegmentInfo} segmentInfo - The current active request information
   * @return {object} The start and end time of the current segment in "media time"
   */
  probeMp4Segment_(segmentInfo) {
    let segment = segmentInfo.segment;
    let timescales = mp4probe.timescale(segment.map.bytes);
    let startTime = mp4probe.startTime(timescales, segmentInfo.bytes);

    if (segmentInfo.timestampOffset !== null) {
      segmentInfo.timestampOffset -= startTime;
    }

    return {
      start: startTime,
      end: startTime + segment.duration
    };
  }

  /**
   * Probe an mpeg2-ts segment to determine the start and end of the segment
   * in it's internal "media time".
   *
   * @private
   * @param {SegmentInfo} segmentInfo - The current active request information
   * @return {object} The start and end time of the current segment in "media time"
   */
  probeTsSegment_(segmentInfo) {
    let timeInfo = tsprobe(segmentInfo.bytes, this.inspectCache_);
    let segmentStartTime;
    let segmentEndTime;

    if (!timeInfo) {
      return null;
    }

    if (timeInfo.video && timeInfo.video.length === 2) {
      this.inspectCache_ = timeInfo.video[1].dts;
      segmentStartTime = timeInfo.video[0].dtsTime;
      segmentEndTime = timeInfo.video[1].dtsTime;
    } else if (timeInfo.audio && timeInfo.audio.length === 2) {
      this.inspectCache_ = timeInfo.audio[1].dts;
      segmentStartTime = timeInfo.audio[0].dtsTime;
      segmentEndTime = timeInfo.audio[1].dtsTime;
    }

    return {
      start: segmentStartTime,
      end: segmentEndTime,
      containsVideo: timeInfo.video && timeInfo.video.length === 2,
      containsAudio: timeInfo.audio && timeInfo.audio.length === 2
    };
  }

  timestampOffsetForTimeline(timeline) {
    if (typeof this.timelines[timeline] === 'undefined') {
      return null;
    }
    return this.timelines[timeline].time;
  }

  mappingForTimeline(timeline) {
    if (typeof this.timelines[timeline] === 'undefined') {
      return null;
    }
    return this.timelines[timeline].mapping;
  }

  /**
   * Use the "media time" for a segment to generate a mapping to "display time" and
   * save that display time to the segment.
   *
   * @private
   * @param {SegmentInfo} segmentInfo
   *        The current active request information
   * @param {object} timingInfo
   *        The start and end time of the current segment in "media time"
   * @returns {Boolean}
   *          Returns false if segment time mapping could not be calculated
   */
  calculateSegmentTimeMapping_(segmentInfo, timingInfo) {
    let segment = segmentInfo.segment;
    let mappingObj = this.timelines[segmentInfo.timeline];

    if (segmentInfo.timestampOffset !== null) {
      mappingObj = {
        time: segmentInfo.startOfSegment,
        mapping: segmentInfo.startOfSegment - timingInfo.start
      };
      this.timelines[segmentInfo.timeline] = mappingObj;
      this.trigger('timestampoffset');

      this.logger_(`time mapping for timeline ${segmentInfo.timeline}: ` +
        `[time: ${mappingObj.time}] [mapping: ${mappingObj.mapping}]`);

      segment.start = segmentInfo.startOfSegment;
      segment.end = timingInfo.end + mappingObj.mapping;
    } else if (mappingObj) {
      segment.start = timingInfo.start + mappingObj.mapping;
      segment.end = timingInfo.end + mappingObj.mapping;
    } else {
      return false;
    }

    return true;
  }

  /**
   * Each time we have discontinuity in the playlist, attempt to calculate the location
   * in display of the start of the discontinuity and save that. We also save an accuracy
   * value so that we save values with the most accuracy (closest to 0.)
   *
   * @private
   * @param {SegmentInfo} segmentInfo - The current active request information
   */
  saveDiscontinuitySyncInfo_(segmentInfo) {
    let playlist = segmentInfo.playlist;
    let segment = segmentInfo.segment;

    // If the current segment is a discontinuity then we know exactly where
    // the start of the range and it's accuracy is 0 (greater accuracy values
    // mean more approximation)
    if (segment.discontinuity) {
      this.discontinuities[segment.timeline] = {
        time: segment.start,
        accuracy: 0
      };
    } else if (playlist.discontinuityStarts && playlist.discontinuityStarts.length) {
      // Search for future discontinuities that we can provide better timing
      // information for and save that information for sync purposes
      for (let i = 0; i < playlist.discontinuityStarts.length; i++) {
        let segmentIndex = playlist.discontinuityStarts[i];
        let discontinuity = playlist.discontinuitySequence + i + 1;
        let mediaIndexDiff = segmentIndex - segmentInfo.mediaIndex;
        let accuracy = Math.abs(mediaIndexDiff);

        if (!this.discontinuities[discontinuity] ||
             this.discontinuities[discontinuity].accuracy > accuracy) {
          let time;

          if (mediaIndexDiff < 0) {
            time = segment.start - sumDurations(playlist,
                                                segmentInfo.mediaIndex,
                                                segmentIndex);
          } else {
            time = segment.end + sumDurations(playlist,
                                              segmentInfo.mediaIndex + 1,
                                              segmentIndex);
          }

          this.discontinuities[discontinuity] = {
            time,
            accuracy
          };
        }
      }
    }
  }
}

var Decrypter$1 = new shimWorker("./decrypter-worker.worker.js", function (window, document$$1) {
var self = this;
var decrypterWorker = (function () {

	var commonjsGlobal = typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};

	var win;

	if (typeof window !== "undefined") {
	    win = window;
	} else if (typeof commonjsGlobal !== "undefined") {
	    win = commonjsGlobal;
	} else if (typeof self !== "undefined") {
	    win = self;
	} else {
	    win = {};
	}

	var window_1 = win;

	/*
	 * pkcs7.pad
	 * https://github.com/brightcove/pkcs7
	 *
	 * Copyright (c) 2014 Brightcove
	 * Licensed under the apache2 license.
	 */

	/**
	 * Returns the subarray of a Uint8Array without PKCS#7 padding.
	 * @param padded {Uint8Array} unencrypted bytes that have been padded
	 * @return {Uint8Array} the unpadded bytes
	 * @see http://tools.ietf.org/html/rfc5652
	 */
	function unpad(padded) {
	  return padded.subarray(0, padded.byteLength - padded[padded.byteLength - 1]);
	}

	var classCallCheck = function classCallCheck(instance, Constructor) {
	  if (!(instance instanceof Constructor)) {
	    throw new TypeError("Cannot call a class as a function");
	  }
	};

	var createClass = function () {
	  function defineProperties(target, props) {
	    for (var i = 0; i < props.length; i++) {
	      var descriptor = props[i];
	      descriptor.enumerable = descriptor.enumerable || false;
	      descriptor.configurable = true;
	      if ("value" in descriptor) descriptor.writable = true;
	      Object.defineProperty(target, descriptor.key, descriptor);
	    }
	  }

	  return function (Constructor, protoProps, staticProps) {
	    if (protoProps) defineProperties(Constructor.prototype, protoProps);
	    if (staticProps) defineProperties(Constructor, staticProps);
	    return Constructor;
	  };
	}();

	var inherits = function inherits(subClass, superClass) {
	  if (typeof superClass !== "function" && superClass !== null) {
	    throw new TypeError("Super expression must either be null or a function, not " + typeof superClass);
	  }

	  subClass.prototype = Object.create(superClass && superClass.prototype, {
	    constructor: {
	      value: subClass,
	      enumerable: false,
	      writable: true,
	      configurable: true
	    }
	  });
	  if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
	};

	var possibleConstructorReturn = function possibleConstructorReturn(self, call) {
	  if (!self) {
	    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
	  }

	  return call && (typeof call === "object" || typeof call === "function") ? call : self;
	};

	/**
	 * @file aes.js
	 *
	 * This file contains an adaptation of the AES decryption algorithm
	 * from the Standford Javascript Cryptography Library. That work is
	 * covered by the following copyright and permissions notice:
	 *
	 * Copyright 2009-2010 Emily Stark, Mike Hamburg, Dan Boneh.
	 * All rights reserved.
	 *
	 * Redistribution and use in source and binary forms, with or without
	 * modification, are permitted provided that the following conditions are
	 * met:
	 *
	 * 1. Redistributions of source code must retain the above copyright
	 *    notice, this list of conditions and the following disclaimer.
	 *
	 * 2. Redistributions in binary form must reproduce the above
	 *    copyright notice, this list of conditions and the following
	 *    disclaimer in the documentation and/or other materials provided
	 *    with the distribution.
	 *
	 * THIS SOFTWARE IS PROVIDED BY THE AUTHORS ``AS IS'' AND ANY EXPRESS OR
	 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> OR CONTRIBUTORS BE
	 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
	 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
	 * BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
	 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
	 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
	 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	 *
	 * The views and conclusions contained in the software and documentation
	 * are those of the authors and should not be interpreted as representing
	 * official policies, either expressed or implied, of the authors.
	 */

	/**
	 * Expand the S-box tables.
	 *
	 * @private
	 */
	var precompute = function precompute() {
	  var tables = [[[], [], [], [], []], [[], [], [], [], []]];
	  var encTable = tables[0];
	  var decTable = tables[1];
	  var sbox = encTable[4];
	  var sboxInv = decTable[4];
	  var i = void 0;
	  var x = void 0;
	  var xInv = void 0;
	  var d = [];
	  var th = [];
	  var x2 = void 0;
	  var x4 = void 0;
	  var x8 = void 0;
	  var s = void 0;
	  var tEnc = void 0;
	  var tDec = void 0;

	  // Compute double and third tables
	  for (i = 0; i < 256; i++) {
	    th[(d[i] = i << 1 ^ (i >> 7) * 283) ^ i] = i;
	  }

	  for (x = xInv = 0; !sbox[x]; x ^= x2 || 1, xInv = th[xInv] || 1) {
	    // Compute sbox
	    s = xInv ^ xInv << 1 ^ xInv << 2 ^ xInv << 3 ^ xInv << 4;
	    s = s >> 8 ^ s & 255 ^ 99;
	    sbox[x] = s;
	    sboxInv[s] = x;

	    // Compute MixColumns
	    x8 = d[x4 = d[x2 = d[x]]];
	    tDec = x8 * 0x1010101 ^ x4 * 0x10001 ^ x2 * 0x101 ^ x * 0x1010100;
	    tEnc = d[s] * 0x101 ^ s * 0x1010100;

	    for (i = 0; i < 4; i++) {
	      encTable[i][x] = tEnc = tEnc << 24 ^ tEnc >>> 8;
	      decTable[i][s] = tDec = tDec << 24 ^ tDec >>> 8;
	    }
	  }

	  // Compactify. Considerable speedup on Firefox.
	  for (i = 0; i < 5; i++) {
	    encTable[i] = encTable[i].slice(0);
	    decTable[i] = decTable[i].slice(0);
	  }
	  return tables;
	};
	var aesTables = null;

	/**
	 * Schedule out an AES key for both encryption and decryption. This
	 * is a low-level class. Use a cipher mode to do bulk encryption.
	 *
	 * @class AES
	 * @param key {Array} The key as an array of 4, 6 or 8 words.
	 */

	var AES = function () {
	  function AES(key) {
	    classCallCheck(this, AES);

	    /**
	     * The expanded S-box and inverse S-box tables. These will be computed
	     * on the client so that we don't have to send them down the wire.
	     *
	     * There are two tables, _tables[0] is for encryption and
	     * _tables[1] is for decryption.
	     *
	     * The first 4 sub-tables are the expanded S-box with MixColumns. The
	     * last (_tables[01][4]) is the S-box itself.
	     *
	     * @private
	     */
	    // if we have yet to precompute the S-box tables
	    // do so now
	    if (!aesTables) {
	      aesTables = precompute();
	    }
	    // then make a copy of that object for use
	    this._tables = [[aesTables[0][0].slice(), aesTables[0][1].slice(), aesTables[0][2].slice(), aesTables[0][3].slice(), aesTables[0][4].slice()], [aesTables[1][0].slice(), aesTables[1][1].slice(), aesTables[1][2].slice(), aesTables[1][3].slice(), aesTables[1][4].slice()]];
	    var i = void 0;
	    var j = void 0;
	    var tmp = void 0;
	    var encKey = void 0;
	    var decKey = void 0;
	    var sbox = this._tables[0][4];
	    var decTable = this._tables[1];
	    var keyLen = key.length;
	    var rcon = 1;

	    if (keyLen !== 4 && keyLen !== 6 && keyLen !== 8) {
	      throw new Error('Invalid aes key size');
	    }

	    encKey = key.slice(0);
	    decKey = [];
	    this._key = [encKey, decKey];

	    // schedule encryption keys
	    for (i = keyLen; i < 4 * keyLen + 28; i++) {
	      tmp = encKey[i - 1];

	      // apply sbox
	      if (i % keyLen === 0 || keyLen === 8 && i % keyLen === 4) {
	        tmp = sbox[tmp >>> 24] << 24 ^ sbox[tmp >> 16 & 255] << 16 ^ sbox[tmp >> 8 & 255] << 8 ^ sbox[tmp & 255];

	        // shift rows and add rcon
	        if (i % keyLen === 0) {
	          tmp = tmp << 8 ^ tmp >>> 24 ^ rcon << 24;
	          rcon = rcon << 1 ^ (rcon >> 7) * 283;
	        }
	      }

	      encKey[i] = encKey[i - keyLen] ^ tmp;
	    }

	    // schedule decryption keys
	    for (j = 0; i; j++, i--) {
	      tmp = encKey[j & 3 ? i : i - 4];
	      if (i <= 4 || j < 4) {
	        decKey[j] = tmp;
	      } else {
	        decKey[j] = decTable[0][sbox[tmp >>> 24]] ^ decTable[1][sbox[tmp >> 16 & 255]] ^ decTable[2][sbox[tmp >> 8 & 255]] ^ decTable[3][sbox[tmp & 255]];
	      }
	    }
	  }

	  /**
	   * Decrypt 16 bytes, specified as four 32-bit words.
	   *
	   * @param {Number} encrypted0 the first word to decrypt
	   * @param {Number} encrypted1 the second word to decrypt
	   * @param {Number} encrypted2 the third word to decrypt
	   * @param {Number} encrypted3 the fourth word to decrypt
	   * @param {Int32Array} out the array to write the decrypted words
	   * into
	   * @param {Number} offset the offset into the output array to start
	   * writing results
	   * @return {Array} The plaintext.
	   */

	  AES.prototype.decrypt = function decrypt$$1(encrypted0, encrypted1, encrypted2, encrypted3, out, offset) {
	    var key = this._key[1];
	    // state variables a,b,c,d are loaded with pre-whitened data
	    var a = encrypted0 ^ key[0];
	    var b = encrypted3 ^ key[1];
	    var c = encrypted2 ^ key[2];
	    var d = encrypted1 ^ key[3];
	    var a2 = void 0;
	    var b2 = void 0;
	    var c2 = void 0;

	    // key.length === 2 ?
	    var nInnerRounds = key.length / 4 - 2;
	    var i = void 0;
	    var kIndex = 4;
	    var table = this._tables[1];

	    // load up the tables
	    var table0 = table[0];
	    var table1 = table[1];
	    var table2 = table[2];
	    var table3 = table[3];
	    var sbox = table[4];

	    // Inner rounds. Cribbed from OpenSSL.
	    for (i = 0; i < nInnerRounds; i++) {
	      a2 = table0[a >>> 24] ^ table1[b >> 16 & 255] ^ table2[c >> 8 & 255] ^ table3[d & 255] ^ key[kIndex];
	      b2 = table0[b >>> 24] ^ table1[c >> 16 & 255] ^ table2[d >> 8 & 255] ^ table3[a & 255] ^ key[kIndex + 1];
	      c2 = table0[c >>> 24] ^ table1[d >> 16 & 255] ^ table2[a >> 8 & 255] ^ table3[b & 255] ^ key[kIndex + 2];
	      d = table0[d >>> 24] ^ table1[a >> 16 & 255] ^ table2[b >> 8 & 255] ^ table3[c & 255] ^ key[kIndex + 3];
	      kIndex += 4;
	      a = a2;b = b2;c = c2;
	    }

	    // Last round.
	    for (i = 0; i < 4; i++) {
	      out[(3 & -i) + offset] = sbox[a >>> 24] << 24 ^ sbox[b >> 16 & 255] << 16 ^ sbox[c >> 8 & 255] << 8 ^ sbox[d & 255] ^ key[kIndex++];
	      a2 = a;a = b;b = c;c = d;d = a2;
	    }
	  };

	  return AES;
	}();

	/**
	 * @file stream.js
	 */
	/**
	 * A lightweight readable stream implemention that handles event dispatching.
	 *
	 * @class Stream
	 */
	var Stream = function () {
	  function Stream() {
	    classCallCheck(this, Stream);

	    this.listeners = {};
	  }

	  /**
	   * Add a listener for a specified event type.
	   *
	   * @param {String} type the event name
	   * @param {Function} listener the callback to be invoked when an event of
	   * the specified type occurs
	   */

	  Stream.prototype.on = function on(type, listener) {
	    if (!this.listeners[type]) {
	      this.listeners[type] = [];
	    }
	    this.listeners[type].push(listener);
	  };

	  /**
	   * Remove a listener for a specified event type.
	   *
	   * @param {String} type the event name
	   * @param {Function} listener  a function previously registered for this
	   * type of event through `on`
	   * @return {Boolean} if we could turn it off or not
	   */

	  Stream.prototype.off = function off(type, listener) {
	    if (!this.listeners[type]) {
	      return false;
	    }

	    var index = this.listeners[type].indexOf(listener);

	    this.listeners[type].splice(index, 1);
	    return index > -1;
	  };

	  /**
	   * Trigger an event of the specified type on this stream. Any additional
	   * arguments to this function are passed as parameters to event listeners.
	   *
	   * @param {String} type the event name
	   */

	  Stream.prototype.trigger = function trigger(type) {
	    var callbacks = this.listeners[type];

	    if (!callbacks) {
	      return;
	    }

	    // Slicing the arguments on every invocation of this method
	    // can add a significant amount of overhead. Avoid the
	    // intermediate object creation for the common case of a
	    // single callback argument
	    if (arguments.length === 2) {
	      var length = callbacks.length;

	      for (var i = 0; i < length; ++i) {
	        callbacks[i].call(this, arguments[1]);
	      }
	    } else {
	      var args = Array.prototype.slice.call(arguments, 1);
	      var _length = callbacks.length;

	      for (var _i = 0; _i < _length; ++_i) {
	        callbacks[_i].apply(this, args);
	      }
	    }
	  };

	  /**
	   * Destroys the stream and cleans up.
	   */

	  Stream.prototype.dispose = function dispose() {
	    this.listeners = {};
	  };
	  /**
	   * Forwards all `data` events on this stream to the destination stream. The
	   * destination stream should provide a method `push` to receive the data
	   * events as they arrive.
	   *
	   * @param {Stream} destination the stream that will receive all `data` events
	   * @see http://nodejs.org/api/stream.html#stream_readable_pipe_destination_options
	   */

	  Stream.prototype.pipe = function pipe(destination) {
	    this.on('data', function (data) {
	      destination.push(data);
	    });
	  };

	  return Stream;
	}();

	/**
	 * @file async-stream.js
	 */
	/**
	 * A wrapper around the Stream class to use setTiemout
	 * and run stream "jobs" Asynchronously
	 *
	 * @class AsyncStream
	 * @extends Stream
	 */

	var AsyncStream$$1 = function (_Stream) {
	  inherits(AsyncStream$$1, _Stream);

	  function AsyncStream$$1() {
	    classCallCheck(this, AsyncStream$$1);

	    var _this = possibleConstructorReturn(this, _Stream.call(this, Stream));

	    _this.jobs = [];
	    _this.delay = 1;
	    _this.timeout_ = null;
	    return _this;
	  }

	  /**
	   * process an async job
	   *
	   * @private
	   */

	  AsyncStream$$1.prototype.processJob_ = function processJob_() {
	    this.jobs.shift()();
	    if (this.jobs.length) {
	      this.timeout_ = setTimeout(this.processJob_.bind(this), this.delay);
	    } else {
	      this.timeout_ = null;
	    }
	  };

	  /**
	   * push a job into the stream
	   *
	   * @param {Function} job the job to push into the stream
	   */

	  AsyncStream$$1.prototype.push = function push(job) {
	    this.jobs.push(job);
	    if (!this.timeout_) {
	      this.timeout_ = setTimeout(this.processJob_.bind(this), this.delay);
	    }
	  };

	  return AsyncStream$$1;
	}(Stream);

	/**
	 * @file decrypter.js
	 *
	 * An asynchronous implementation of AES-128 CBC decryption with
	 * PKCS#7 padding.
	 */

	/**
	 * Convert network-order (big-endian) bytes into their little-endian
	 * representation.
	 */
	var ntoh = function ntoh(word) {
	  return word << 24 | (word & 0xff00) << 8 | (word & 0xff0000) >> 8 | word >>> 24;
	};

	/**
	 * Decrypt bytes using AES-128 with CBC and PKCS#7 padding.
	 *
	 * @param {Uint8Array} encrypted the encrypted bytes
	 * @param {Uint32Array} key the bytes of the decryption key
	 * @param {Uint32Array} initVector the initialization vector (IV) to
	 * use for the first round of CBC.
	 * @return {Uint8Array} the decrypted bytes
	 *
	 * @see http://en.wikipedia.org/wiki/Advanced_Encryption_Standard
	 * @see http://en.wikipedia.org/wiki/Block_cipher_mode_of_operation#Cipher_Block_Chaining_.28CBC.29
	 * @see https://tools.ietf.org/html/rfc2315
	 */
	var decrypt$$1 = function decrypt$$1(encrypted, key, initVector) {
	  // word-level access to the encrypted bytes
	  var encrypted32 = new Int32Array(encrypted.buffer, encrypted.byteOffset, encrypted.byteLength >> 2);

	  var decipher = new AES(Array.prototype.slice.call(key));

	  // byte and word-level access for the decrypted output
	  var decrypted = new Uint8Array(encrypted.byteLength);
	  var decrypted32 = new Int32Array(decrypted.buffer);

	  // temporary variables for working with the IV, encrypted, and
	  // decrypted data
	  var init0 = void 0;
	  var init1 = void 0;
	  var init2 = void 0;
	  var init3 = void 0;
	  var encrypted0 = void 0;
	  var encrypted1 = void 0;
	  var encrypted2 = void 0;
	  var encrypted3 = void 0;

	  // iteration variable
	  var wordIx = void 0;

	  // pull out the words of the IV to ensure we don't modify the
	  // passed-in reference and easier access
	  init0 = initVector[0];
	  init1 = initVector[1];
	  init2 = initVector[2];
	  init3 = initVector[3];

	  // decrypt four word sequences, applying cipher-block chaining (CBC)
	  // to each decrypted block
	  for (wordIx = 0; wordIx < encrypted32.length; wordIx += 4) {
	    // convert big-endian (network order) words into little-endian
	    // (javascript order)
	    encrypted0 = ntoh(encrypted32[wordIx]);
	    encrypted1 = ntoh(encrypted32[wordIx + 1]);
	    encrypted2 = ntoh(encrypted32[wordIx + 2]);
	    encrypted3 = ntoh(encrypted32[wordIx + 3]);

	    // decrypt the block
	    decipher.decrypt(encrypted0, encrypted1, encrypted2, encrypted3, decrypted32, wordIx);

	    // XOR with the IV, and restore network byte-order to obtain the
	    // plaintext
	    decrypted32[wordIx] = ntoh(decrypted32[wordIx] ^ init0);
	    decrypted32[wordIx + 1] = ntoh(decrypted32[wordIx + 1] ^ init1);
	    decrypted32[wordIx + 2] = ntoh(decrypted32[wordIx + 2] ^ init2);
	    decrypted32[wordIx + 3] = ntoh(decrypted32[wordIx + 3] ^ init3);

	    // setup the IV for the next round
	    init0 = encrypted0;
	    init1 = encrypted1;
	    init2 = encrypted2;
	    init3 = encrypted3;
	  }

	  return decrypted;
	};

	/**
	 * The `Decrypter` class that manages decryption of AES
	 * data through `AsyncStream` objects and the `decrypt`
	 * function
	 *
	 * @param {Uint8Array} encrypted the encrypted bytes
	 * @param {Uint32Array} key the bytes of the decryption key
	 * @param {Uint32Array} initVector the initialization vector (IV) to
	 * @param {Function} done the function to run when done
	 * @class Decrypter
	 */

	var Decrypter$$1 = function () {
	  function Decrypter$$1(encrypted, key, initVector, done) {
	    classCallCheck(this, Decrypter$$1);

	    var step = Decrypter$$1.STEP;
	    var encrypted32 = new Int32Array(encrypted.buffer);
	    var decrypted = new Uint8Array(encrypted.byteLength);
	    var i = 0;

	    this.asyncStream_ = new AsyncStream$$1();

	    // split up the encryption job and do the individual chunks asynchronously
	    this.asyncStream_.push(this.decryptChunk_(encrypted32.subarray(i, i + step), key, initVector, decrypted));
	    for (i = step; i < encrypted32.length; i += step) {
	      initVector = new Uint32Array([ntoh(encrypted32[i - 4]), ntoh(encrypted32[i - 3]), ntoh(encrypted32[i - 2]), ntoh(encrypted32[i - 1])]);
	      this.asyncStream_.push(this.decryptChunk_(encrypted32.subarray(i, i + step), key, initVector, decrypted));
	    }
	    // invoke the done() callback when everything is finished
	    this.asyncStream_.push(function () {
	      // remove pkcs#7 padding from the decrypted bytes
	      done(null, unpad(decrypted));
	    });
	  }

	  /**
	   * a getter for step the maximum number of bytes to process at one time
	   *
	   * @return {Number} the value of step 32000
	   */

	  /**
	   * @private
	   */
	  Decrypter$$1.prototype.decryptChunk_ = function decryptChunk_(encrypted, key, initVector, decrypted) {
	    return function () {
	      var bytes = decrypt$$1(encrypted, key, initVector);

	      decrypted.set(bytes, encrypted.byteOffset);
	    };
	  };

	  createClass(Decrypter$$1, null, [{
	    key: 'STEP',
	    get: function get$$1() {
	      // 4 * 8000;
	      return 32000;
	    }
	  }]);
	  return Decrypter$$1;
	}();

	/**
	 * @file bin-utils.js
	 */

	/**
	 * Creates an object for sending to a web worker modifying properties that are TypedArrays
	 * into a new object with seperated properties for the buffer, byteOffset, and byteLength.
	 *
	 * @param {Object} message
	 *        Object of properties and values to send to the web worker
	 * @return {Object}
	 *         Modified message with TypedArray values expanded
	 * @function createTransferableMessage
	 */
	var createTransferableMessage = function createTransferableMessage(message) {
	  var transferable = {};

	  Object.keys(message).forEach(function (key) {
	    var value = message[key];

	    if (ArrayBuffer.isView(value)) {
	      transferable[key] = {
	        bytes: value.buffer,
	        byteOffset: value.byteOffset,
	        byteLength: value.byteLength
	      };
	    } else {
	      transferable[key] = value;
	    }
	  });

	  return transferable;
	};

	/**
	 * Our web worker interface so that things can talk to aes-decrypter
	 * that will be running in a web worker. the scope is passed to this by
	 * webworkify.
	 *
	 * @param {Object} self
	 *        the scope for the web worker
	 */
	var DecrypterWorker = function DecrypterWorker(self) {
	  self.onmessage = function (event) {
	    var data = event.data;
	    var encrypted = new Uint8Array(data.encrypted.bytes, data.encrypted.byteOffset, data.encrypted.byteLength);
	    var key = new Uint32Array(data.key.bytes, data.key.byteOffset, data.key.byteLength / 4);
	    var iv = new Uint32Array(data.iv.bytes, data.iv.byteOffset, data.iv.byteLength / 4);

	    /* eslint-disable no-new, handle-callback-err */
	    new Decrypter$$1(encrypted, key, iv, function (err, bytes) {
	      window_1.postMessage(createTransferableMessage({
	        source: data.source,
	        decrypted: bytes
	      }), [bytes.buffer]);
	    });
	    /* eslint-enable */
	  };
	};

	var decrypterWorker = new DecrypterWorker(self);

	return decrypterWorker;

}());


});

/**
 * Convert the properties of an HLS track into an audioTrackKind.
 *
 * @private
 */
const audioTrackKind_ = (properties) => {
  let kind = properties.default ? 'main' : 'alternative';

  if (properties.characteristics &&
      properties.characteristics.indexOf('public.accessibility.describes-video') >= 0) {
    kind = 'main-desc';
  }

  return kind;
};

/**
 * Pause provided segment loader and playlist loader if active
 *
 * @param {SegmentLoader} segmentLoader
 *        SegmentLoader to pause
 * @param {Object} mediaType
 *        Active media type
 * @function stopLoaders
 */
const stopLoaders = (segmentLoader, mediaType) => {
  segmentLoader.abort();
  segmentLoader.pause();

  if (mediaType && mediaType.activePlaylistLoader) {
    mediaType.activePlaylistLoader.pause();
    mediaType.activePlaylistLoader = null;
  }
};

/**
 * Start loading provided segment loader and playlist loader
 *
 * @param {PlaylistLoader} playlistLoader
 *        PlaylistLoader to start loading
 * @param {Object} mediaType
 *        Active media type
 * @function startLoaders
 */
const startLoaders = (playlistLoader, mediaType) => {
  // Segment loader will be started after `loadedmetadata` or `loadedplaylist` from the
  // playlist loader
  mediaType.activePlaylistLoader = playlistLoader;
  playlistLoader.load();
};

/**
 * Returns a function to be called when the media group changes. It performs a
 * non-destructive (preserve the buffer) resync of the SegmentLoader. This is because a
 * change of group is merely a rendition switch of the same content at another encoding,
 * rather than a change of content, such as switching audio from English to Spanish.
 *
 * @param {String} type
 *        MediaGroup type
 * @param {Object} settings
 *        Object containing required information for media groups
 * @return {Function}
 *         Handler for a non-destructive resync of SegmentLoader when the active media
 *         group changes.
 * @function onGroupChanged
 */
const onGroupChanged = (type, settings) => () => {
  const {
    segmentLoaders: {
      [type]: segmentLoader,
      main: mainSegmentLoader
    },
    mediaTypes: { [type]: mediaType }
  } = settings;
  const activeTrack = mediaType.activeTrack();
  const activeGroup = mediaType.activeGroup(activeTrack);
  const previousActiveLoader = mediaType.activePlaylistLoader;

  stopLoaders(segmentLoader, mediaType);

  if (!activeGroup) {
    // there is no group active
    return;
  }

  if (!activeGroup.playlistLoader) {
    if (previousActiveLoader) {
      // The previous group had a playlist loader but the new active group does not
      // this means we are switching from demuxed to muxed audio. In this case we want to
      // do a destructive reset of the main segment loader and not restart the audio
      // loaders.
      mainSegmentLoader.resetEverything();
    }
    return;
  }

  // Non-destructive resync
  segmentLoader.resyncLoader();

  startLoaders(activeGroup.playlistLoader, mediaType);
};

/**
 * Returns a function to be called when the media track changes. It performs a
 * destructive reset of the SegmentLoader to ensure we start loading as close to
 * currentTime as possible.
 *
 * @param {String} type
 *        MediaGroup type
 * @param {Object} settings
 *        Object containing required information for media groups
 * @return {Function}
 *         Handler for a destructive reset of SegmentLoader when the active media
 *         track changes.
 * @function onTrackChanged
 */
const onTrackChanged = (type, settings) => () => {
  const {
    segmentLoaders: {
      [type]: segmentLoader,
      main: mainSegmentLoader
    },
    mediaTypes: { [type]: mediaType }
  } = settings;
  const activeTrack = mediaType.activeTrack();
  const activeGroup = mediaType.activeGroup(activeTrack);
  const previousActiveLoader = mediaType.activePlaylistLoader;

  stopLoaders(segmentLoader, mediaType);

  if (!activeGroup) {
    // there is no group active so we do not want to restart loaders
    return;
  }

  if (!activeGroup.playlistLoader) {
    // when switching from demuxed audio/video to muxed audio/video (noted by no playlist
    // loader for the audio group), we want to do a destructive reset of the main segment
    // loader and not restart the audio loaders
    mainSegmentLoader.resetEverything();
    return;
  }

  if (previousActiveLoader === activeGroup.playlistLoader) {
    // Nothing has actually changed. This can happen because track change events can fire
    // multiple times for a "single" change. One for enabling the new active track, and
    // one for disabling the track that was active
    startLoaders(activeGroup.playlistLoader, mediaType);
    return;
  }

  if (segmentLoader.track) {
    // For WebVTT, set the new text track in the segmentloader
    segmentLoader.track(activeTrack);
  }

  // destructive reset
  segmentLoader.resetEverything();

  startLoaders(activeGroup.playlistLoader, mediaType);
};

const onError = {
  /**
   * Returns a function to be called when a SegmentLoader or PlaylistLoader encounters
   * an error.
   *
   * @param {String} type
   *        MediaGroup type
   * @param {Object} settings
   *        Object containing required information for media groups
   * @return {Function}
   *         Error handler. Logs warning (or error if the playlist is blacklisted) to
   *         console and switches back to default audio track.
   * @function onError.AUDIO
   */
  AUDIO: (type, settings) => () => {
    const {
      segmentLoaders: { [type]: segmentLoader},
      mediaTypes: { [type]: mediaType },
      blacklistCurrentPlaylist
    } = settings;

    stopLoaders(segmentLoader, mediaType);

    // switch back to default audio track
    const activeTrack = mediaType.activeTrack();
    const activeGroup = mediaType.activeGroup();
    const id = (activeGroup.filter(group => group.default)[0] || activeGroup[0]).id;
    const defaultTrack = mediaType.tracks[id];

    if (activeTrack === defaultTrack) {
      // Default track encountered an error. All we can do now is blacklist the current
      // rendition and hope another will switch audio groups
      blacklistCurrentPlaylist({
        message: 'Problem encountered loading the default audio track.'
      });
      return;
    }

    videojs.log.warn('Problem encountered loading the alternate audio track.' +
                       'Switching back to default.');

    for (let trackId in mediaType.tracks) {
      mediaType.tracks[trackId].enabled = mediaType.tracks[trackId] === defaultTrack;
    }

    mediaType.onTrackChanged();
  },
  /**
   * Returns a function to be called when a SegmentLoader or PlaylistLoader encounters
   * an error.
   *
   * @param {String} type
   *        MediaGroup type
   * @param {Object} settings
   *        Object containing required information for media groups
   * @return {Function}
   *         Error handler. Logs warning to console and disables the active subtitle track
   * @function onError.SUBTITLES
   */
  SUBTITLES: (type, settings) => () => {
    const {
      segmentLoaders: { [type]: segmentLoader},
      mediaTypes: { [type]: mediaType }
    } = settings;

    videojs.log.warn('Problem encountered loading the subtitle track.' +
                     'Disabling subtitle track.');

    stopLoaders(segmentLoader, mediaType);

    const track = mediaType.activeTrack();

    if (track) {
      track.mode = 'disabled';
    }

    mediaType.onTrackChanged();
  }
};

const setupListeners = {
  /**
   * Setup event listeners for audio playlist loader
   *
   * @param {String} type
   *        MediaGroup type
   * @param {PlaylistLoader|null} playlistLoader
   *        PlaylistLoader to register listeners on
   * @param {Object} settings
   *        Object containing required information for media groups
   * @function setupListeners.AUDIO
   */
  AUDIO: (type, playlistLoader, settings) => {
    if (!playlistLoader) {
      // no playlist loader means audio will be muxed with the video
      return;
    }

    const {
      tech,
      requestOptions,
      segmentLoaders: { [type]: segmentLoader }
    } = settings;

    playlistLoader.on('loadedmetadata', () => {
      const media = playlistLoader.media();

      segmentLoader.playlist(media, requestOptions);

      // if the video is already playing, or if this isn't a live video and preload
      // permits, start downloading segments
      if (!tech.paused() || (media.endList && tech.preload() !== 'none')) {
        segmentLoader.load();
      }
    });

    playlistLoader.on('loadedplaylist', () => {
      segmentLoader.playlist(playlistLoader.media(), requestOptions);

      // If the player isn't paused, ensure that the segment loader is running
      if (!tech.paused()) {
        segmentLoader.load();
      }
    });

    playlistLoader.on('error', onError[type](type, settings));
  },
  /**
   * Setup event listeners for subtitle playlist loader
   *
   * @param {String} type
   *        MediaGroup type
   * @param {PlaylistLoader|null} playlistLoader
   *        PlaylistLoader to register listeners on
   * @param {Object} settings
   *        Object containing required information for media groups
   * @function setupListeners.SUBTITLES
   */
  SUBTITLES: (type, playlistLoader, settings) => {
    const {
      tech,
      requestOptions,
      segmentLoaders: { [type]: segmentLoader },
      mediaTypes: { [type]: mediaType }
    } = settings;

    playlistLoader.on('loadedmetadata', () => {
      const media = playlistLoader.media();

      segmentLoader.playlist(media, requestOptions);
      segmentLoader.track(mediaType.activeTrack());

      // if the video is already playing, or if this isn't a live video and preload
      // permits, start downloading segments
      if (!tech.paused() || (media.endList && tech.preload() !== 'none')) {
        segmentLoader.load();
      }
    });

    playlistLoader.on('loadedplaylist', () => {
      segmentLoader.playlist(playlistLoader.media(), requestOptions);

      // If the player isn't paused, ensure that the segment loader is running
      if (!tech.paused()) {
        segmentLoader.load();
      }
    });

    playlistLoader.on('error', onError[type](type, settings));
  }
};

const byGroupId = (type, groupId) => (playlist) => playlist.attributes[type] === groupId;

const byResolvedUri = (resolvedUri) => (playlist) => playlist.resolvedUri === resolvedUri;

const initialize = {
  /**
   * Setup PlaylistLoaders and AudioTracks for the audio groups
   *
   * @param {String} type
   *        MediaGroup type
   * @param {Object} settings
   *        Object containing required information for media groups
   * @function initialize.AUDIO
   */
  'AUDIO': (type, settings) => {
    const {
      hls,
      sourceType,
      segmentLoaders: { [type]: segmentLoader },
      requestOptions: { withCredentials },
      master: { mediaGroups, playlists },
      mediaTypes: {
        [type]: {
          groups,
          tracks
        }
      },
      masterPlaylistLoader
    } = settings;

    // force a default if we have none
    if (!mediaGroups[type] ||
        Object.keys(mediaGroups[type]).length === 0) {
      mediaGroups[type] = { main: { default: { default: true } } };
    }

    for (let groupId in mediaGroups[type]) {
      if (!groups[groupId]) {
        groups[groupId] = [];
      }

      // List of playlists that have an AUDIO attribute value matching the current
      // group ID
      const groupPlaylists = playlists.filter(byGroupId(type, groupId));

      for (let variantLabel in mediaGroups[type][groupId]) {
        let properties = mediaGroups[type][groupId][variantLabel];

        // List of playlists for the current group ID that have a matching uri with
        // this alternate audio variant
        const matchingPlaylists =
          groupPlaylists.filter(byResolvedUri(properties.resolvedUri));

        if (matchingPlaylists.length) {
          // If there is a playlist that has the same uri as this audio variant, assume
          // that the playlist is audio only. We delete the resolvedUri property here
          // to prevent a playlist loader from being created so that we don't have
          // both the main and audio segment loaders loading the same audio segments
          // from the same playlist.
          delete properties.resolvedUri;
        }

        let playlistLoader;

        if (properties.resolvedUri) {
          playlistLoader = new PlaylistLoader(properties.resolvedUri,
                                              hls,
                                              withCredentials);
        } else if (properties.playlists && sourceType === 'dash') {
          playlistLoader = new DashPlaylistLoader(properties.playlists[0],
                                                  hls,
                                                  withCredentials,
                                                  masterPlaylistLoader);
        } else {
          // no resolvedUri means the audio is muxed with the video when using this
          // audio track
          playlistLoader = null;
        }

        properties = videojs.mergeOptions({ id: variantLabel, playlistLoader },
                                          properties);

        setupListeners[type](type, properties.playlistLoader, settings);

        groups[groupId].push(properties);

        if (typeof tracks[variantLabel] === 'undefined') {
          const track = new videojs.AudioTrack({
            id: variantLabel,
            kind: audioTrackKind_(properties),
            enabled: false,
            language: properties.language,
            default: properties.default,
            label: variantLabel
          });

          tracks[variantLabel] = track;
        }
      }
    }

    // setup single error event handler for the segment loader
    segmentLoader.on('error', onError[type](type, settings));
  },
  /**
   * Setup PlaylistLoaders and TextTracks for the subtitle groups
   *
   * @param {String} type
   *        MediaGroup type
   * @param {Object} settings
   *        Object containing required information for media groups
   * @function initialize.SUBTITLES
   */
  'SUBTITLES': (type, settings) => {
    const {
      tech,
      hls,
      sourceType,
      segmentLoaders: { [type]: segmentLoader },
      requestOptions: { withCredentials },
      master: { mediaGroups },
      mediaTypes: {
        [type]: {
          groups,
          tracks
        }
      },
      masterPlaylistLoader
    } = settings;

    for (let groupId in mediaGroups[type]) {
      if (!groups[groupId]) {
        groups[groupId] = [];
      }

      for (let variantLabel in mediaGroups[type][groupId]) {
        if (mediaGroups[type][groupId][variantLabel].forced) {
          // Subtitle playlists with the forced attribute are not selectable in Safari.
          // According to Apple's HLS Authoring Specification:
          //   If content has forced subtitles and regular subtitles in a given language,
          //   the regular subtitles track in that language MUST contain both the forced
          //   subtitles and the regular subtitles for that language.
          // Because of this requirement and that Safari does not add forced subtitles,
          // forced subtitles are skipped here to maintain consistent experience across
          // all platforms
          continue;
        }

        let properties = mediaGroups[type][groupId][variantLabel];

        let playlistLoader;

        if (sourceType === 'hls') {
          playlistLoader =
            new PlaylistLoader(properties.resolvedUri, hls, withCredentials);
        } else if (sourceType === 'dash') {
          playlistLoader = new DashPlaylistLoader(properties.playlists[0],
                                                  hls,
                                                  withCredentials,
                                                  masterPlaylistLoader);
        }

        properties = videojs.mergeOptions({
          id: variantLabel,
          playlistLoader
        }, properties);

        setupListeners[type](type, properties.playlistLoader, settings);

        groups[groupId].push(properties);

        if (typeof tracks[variantLabel] === 'undefined') {
          const track = tech.addRemoteTextTrack({
            id: variantLabel,
            kind: 'subtitles',
            enabled: false,
            language: properties.language,
            label: variantLabel
          }, false).track;

          tracks[variantLabel] = track;
        }
      }
    }

    // setup single error event handler for the segment loader
    segmentLoader.on('error', onError[type](type, settings));
  },
  /**
   * Setup TextTracks for the closed-caption groups
   *
   * @param {String} type
   *        MediaGroup type
   * @param {Object} settings
   *        Object containing required information for media groups
   * @function initialize['CLOSED-CAPTIONS']
   */
  'CLOSED-CAPTIONS': (type, settings) => {
    const {
      tech,
      master: { mediaGroups },
      mediaTypes: {
        [type]: {
          groups,
          tracks
        }
      }
    } = settings;

    for (let groupId in mediaGroups[type]) {
      if (!groups[groupId]) {
        groups[groupId] = [];
      }

      for (let variantLabel in mediaGroups[type][groupId]) {
        let properties = mediaGroups[type][groupId][variantLabel];

        // We only support CEA608 captions for now, so ignore anything that
        // doesn't use a CCx INSTREAM-ID
        if (!properties.instreamId.match(/CC\d/)) {
          continue;
        }

        // No PlaylistLoader is required for Closed-Captions because the captions are
        // embedded within the video stream
        groups[groupId].push(videojs.mergeOptions({ id: variantLabel }, properties));

        if (typeof tracks[variantLabel] === 'undefined') {
          const track = tech.addRemoteTextTrack({
            id: properties.instreamId,
            kind: 'captions',
            enabled: false,
            language: properties.language,
            label: variantLabel
          }, false).track;

          tracks[variantLabel] = track;
        }
      }
    }
  }
};

/**
 * Returns a function used to get the active group of the provided type
 *
 * @param {String} type
 *        MediaGroup type
 * @param {Object} settings
 *        Object containing required information for media groups
 * @return {Function}
 *         Function that returns the active media group for the provided type. Takes an
 *         optional parameter {TextTrack} track. If no track is provided, a list of all
 *         variants in the group, otherwise the variant corresponding to the provided
 *         track is returned.
 * @function activeGroup
 */
const activeGroup = (type, settings) => (track) => {
  const {
    masterPlaylistLoader,
    mediaTypes: { [type]: { groups } }
  } = settings;

  const media = masterPlaylistLoader.media();

  if (!media) {
    return null;
  }

  let variants = null;

  if (media.attributes[type]) {
    variants = groups[media.attributes[type]];
  }

  variants = variants || groups.main;

  if (typeof track === 'undefined') {
    return variants;
  }

  if (track === null) {
    // An active track was specified so a corresponding group is expected. track === null
    // means no track is currently active so there is no corresponding group
    return null;
  }

  return variants.filter((props) => props.id === track.id)[0] || null;
};

const activeTrack = {
  /**
   * Returns a function used to get the active track of type provided
   *
   * @param {String} type
   *        MediaGroup type
   * @param {Object} settings
   *        Object containing required information for media groups
   * @return {Function}
   *         Function that returns the active media track for the provided type. Returns
   *         null if no track is active
   * @function activeTrack.AUDIO
   */
  AUDIO: (type, settings) => () => {
    const { mediaTypes: { [type]: { tracks } } } = settings;

    for (let id in tracks) {
      if (tracks[id].enabled) {
        return tracks[id];
      }
    }

    return null;
  },
  /**
   * Returns a function used to get the active track of type provided
   *
   * @param {String} type
   *        MediaGroup type
   * @param {Object} settings
   *        Object containing required information for media groups
   * @return {Function}
   *         Function that returns the active media track for the provided type. Returns
   *         null if no track is active
   * @function activeTrack.SUBTITLES
   */
  SUBTITLES: (type, settings) => () => {
    const { mediaTypes: { [type]: { tracks } } } = settings;

    for (let id in tracks) {
      if (tracks[id].mode === 'showing') {
        return tracks[id];
      }
    }

    return null;
  }
};

/**
 * Setup PlaylistLoaders and Tracks for media groups (Audio, Subtitles,
 * Closed-Captions) specified in the master manifest.
 *
 * @param {Object} settings
 *        Object containing required information for setting up the media groups
 * @param {SegmentLoader} settings.segmentLoaders.AUDIO
 *        Audio segment loader
 * @param {SegmentLoader} settings.segmentLoaders.SUBTITLES
 *        Subtitle segment loader
 * @param {SegmentLoader} settings.segmentLoaders.main
 *        Main segment loader
 * @param {Tech} settings.tech
 *        The tech of the player
 * @param {Object} settings.requestOptions
 *        XHR request options used by the segment loaders
 * @param {PlaylistLoader} settings.masterPlaylistLoader
 *        PlaylistLoader for the master source
 * @param {HlsHandler} settings.hls
 *        HLS SourceHandler
 * @param {Object} settings.master
 *        The parsed master manifest
 * @param {Object} settings.mediaTypes
 *        Object to store the loaders, tracks, and utility methods for each media type
 * @param {Function} settings.blacklistCurrentPlaylist
 *        Blacklists the current rendition and forces a rendition switch.
 * @function setupMediaGroups
 */
const setupMediaGroups = (settings) => {
  ['AUDIO', 'SUBTITLES', 'CLOSED-CAPTIONS'].forEach((type) => {
    initialize[type](type, settings);
  });

  const {
    mediaTypes,
    masterPlaylistLoader,
    tech,
    hls
  } = settings;

  // setup active group and track getters and change event handlers
  ['AUDIO', 'SUBTITLES'].forEach((type) => {
    mediaTypes[type].activeGroup = activeGroup(type, settings);
    mediaTypes[type].activeTrack = activeTrack[type](type, settings);
    mediaTypes[type].onGroupChanged = onGroupChanged(type, settings);
    mediaTypes[type].onTrackChanged = onTrackChanged(type, settings);
  });

  // DO NOT enable the default subtitle or caption track.
  // DO enable the default audio track
  const audioGroup = mediaTypes.AUDIO.activeGroup();
  const groupId = (audioGroup.filter(group => group.default)[0] || audioGroup[0]).id;

  mediaTypes.AUDIO.tracks[groupId].enabled = true;
  mediaTypes.AUDIO.onTrackChanged();

  masterPlaylistLoader.on('mediachange', () => {
    ['AUDIO', 'SUBTITLES'].forEach(type => mediaTypes[type].onGroupChanged());
  });

  // custom audio track change event handler for usage event
  const onAudioTrackChanged = () => {
    mediaTypes.AUDIO.onTrackChanged();
    tech.trigger({ type: 'usage', name: 'hls-audio-change' });
  };

  tech.audioTracks().addEventListener('change', onAudioTrackChanged);
  tech.remoteTextTracks().addEventListener('change',
    mediaTypes.SUBTITLES.onTrackChanged);

  hls.on('dispose', () => {
    tech.audioTracks().removeEventListener('change', onAudioTrackChanged);
    tech.remoteTextTracks().removeEventListener('change',
      mediaTypes.SUBTITLES.onTrackChanged);
  });

  // clear existing audio tracks and add the ones we just created
  tech.clearTracks('audio');

  for (let id in mediaTypes.AUDIO.tracks) {
    tech.audioTracks().addTrack(mediaTypes.AUDIO.tracks[id]);
  }
};

/**
 * Creates skeleton object used to store the loaders, tracks, and utility methods for each
 * media type
 *
 * @return {Object}
 *         Object to store the loaders, tracks, and utility methods for each media type
 * @function createMediaTypes
 */
const createMediaTypes = () => {
  const mediaTypes = {};

  ['AUDIO', 'SUBTITLES', 'CLOSED-CAPTIONS'].forEach((type) => {
    mediaTypes[type] = {
      groups: {},
      tracks: {},
      activePlaylistLoader: null,
      activeGroup: noop,
      activeTrack: noop,
      onGroupChanged: noop,
      onTrackChanged: noop
    };
  });

  return mediaTypes;
};

/**
 * @file master-playlist-controller.js
 */

const ABORT_EARLY_BLACKLIST_SECONDS = 60 * 2;

let Hls;

// SegmentLoader stats that need to have each loader's
// values summed to calculate the final value
const loaderStats = [
  'mediaRequests',
  'mediaRequestsAborted',
  'mediaRequestsTimedout',
  'mediaRequestsErrored',
  'mediaTransferDuration',
  'mediaBytesTransferred'
];
const sumLoaderStat = function(stat) {
  return this.audioSegmentLoader_[stat] +
         this.mainSegmentLoader_[stat];
};

/**
 * the master playlist controller controller all interactons
 * between playlists and segmentloaders. At this time this mainly
 * involves a master playlist and a series of audio playlists
 * if they are available
 *
 * @class MasterPlaylistController
 * @extends videojs.EventTarget
 */
class MasterPlaylistController extends videojs.EventTarget {
  constructor(options) {
    super();

    let {
      url,
      withCredentials,
      tech,
      bandwidth,
      externHls,
      useCueTags,
      blacklistDuration,
      enableLowInitialPlaylist,
      sourceType
    } = options;

    if (!url) {
      throw new Error('A non-empty playlist URL is required');
    }

    Hls = externHls;

    this.withCredentials = withCredentials;
    this.tech_ = tech;
    this.hls_ = tech.hls;
    this.sourceType_ = sourceType;
    this.useCueTags_ = useCueTags;
    this.blacklistDuration = blacklistDuration;
    this.enableLowInitialPlaylist = enableLowInitialPlaylist;
    if (this.useCueTags_) {
      this.cueTagsTrack_ = this.tech_.addTextTrack('metadata',
        'ad-cues');
      this.cueTagsTrack_.inBandMetadataTrackDispatchType = '';
    }

    this.requestOptions_ = {
      withCredentials: this.withCredentials,
      timeout: null
    };

    this.mediaTypes_ = createMediaTypes();

    this.mediaSource = new videojs.MediaSource();

    // load the media source into the player
    this.mediaSource.addEventListener('sourceopen', this.handleSourceOpen_.bind(this));

    this.seekable_ = videojs.createTimeRanges();
    this.hasPlayed_ = () => false;

    this.syncController_ = new SyncController(options);
    this.segmentMetadataTrack_ = tech.addRemoteTextTrack({
      kind: 'metadata',
      label: 'segment-metadata'
    }, false).track;

    this.decrypter_ = new Decrypter$1();

    const segmentLoaderSettings = {
      hls: this.hls_,
      mediaSource: this.mediaSource,
      currentTime: this.tech_.currentTime.bind(this.tech_),
      seekable: () => this.seekable(),
      seeking: () => this.tech_.seeking(),
      duration: () => this.mediaSource.duration,
      hasPlayed: () => this.hasPlayed_(),
      goalBufferLength: () => this.goalBufferLength(),
      bandwidth,
      syncController: this.syncController_,
      decrypter: this.decrypter_,
      sourceType: this.sourceType_
    };

    this.masterPlaylistLoader_ = this.sourceType_ === 'dash' ?
      new DashPlaylistLoader(url, this.hls_, this.withCredentials) :
      new PlaylistLoader(url, this.hls_, this.withCredentials);
    this.setupMasterPlaylistLoaderListeners_();

    // setup segment loaders
    // combined audio/video or just video when alternate audio track is selected
    this.mainSegmentLoader_ =
      new SegmentLoader(videojs.mergeOptions(segmentLoaderSettings, {
        segmentMetadataTrack: this.segmentMetadataTrack_,
        loaderType: 'main'
      }), options);

    // alternate audio track
    this.audioSegmentLoader_ =
      new SegmentLoader(videojs.mergeOptions(segmentLoaderSettings, {
        loaderType: 'audio'
      }), options);

    this.subtitleSegmentLoader_ =
      new VTTSegmentLoader(videojs.mergeOptions(segmentLoaderSettings, {
        loaderType: 'vtt'
      }), options);

    this.setupSegmentLoaderListeners_();

    // Create SegmentLoader stat-getters
    loaderStats.forEach((stat) => {
      this[stat + '_'] = sumLoaderStat.bind(this, stat);
    });

    this.logger_ = logger('MPC');

    this.masterPlaylistLoader_.load();
  }

  /**
   * Register event handlers on the master playlist loader. A helper
   * function for construction time.
   *
   * @private
   */
  setupMasterPlaylistLoaderListeners_() {
    this.masterPlaylistLoader_.on('loadedmetadata', () => {
      let media = this.masterPlaylistLoader_.media();
      let requestTimeout = (this.masterPlaylistLoader_.targetDuration * 1.5) * 1000;

      // If we don't have any more available playlists, we don't want to
      // timeout the request.
      if (isLowestEnabledRendition(
            this.masterPlaylistLoader_.master, this.masterPlaylistLoader_.media())) {
        this.requestOptions_.timeout = 0;
      } else {
        this.requestOptions_.timeout = requestTimeout;
      }

      // if this isn't a live video and preload permits, start
      // downloading segments
      if (media.endList && this.tech_.preload() !== 'none') {
        this.mainSegmentLoader_.playlist(media, this.requestOptions_);
        this.mainSegmentLoader_.load();
      }

      setupMediaGroups({
        sourceType: this.sourceType_,
        segmentLoaders: {
          AUDIO: this.audioSegmentLoader_,
          SUBTITLES: this.subtitleSegmentLoader_,
          main: this.mainSegmentLoader_
        },
        tech: this.tech_,
        requestOptions: this.requestOptions_,
        masterPlaylistLoader: this.masterPlaylistLoader_,
        hls: this.hls_,
        master: this.master(),
        mediaTypes: this.mediaTypes_,
        blacklistCurrentPlaylist: this.blacklistCurrentPlaylist.bind(this)
      });

      this.triggerPresenceUsage_(this.master(), media);

      try {
        this.setupSourceBuffers_();
      } catch (e) {
        videojs.log.warn('Failed to create SourceBuffers', e);
        return this.mediaSource.endOfStream('decode');
      }
      this.setupFirstPlay();

      this.trigger('selectedinitialmedia');
    });

    this.masterPlaylistLoader_.on('loadedplaylist', () => {
      let updatedPlaylist = this.masterPlaylistLoader_.media();

      if (!updatedPlaylist) {
        // blacklist any variants that are not supported by the browser before selecting
        // an initial media as the playlist selectors do not consider browser support
        this.excludeUnsupportedVariants_();

        let selectedMedia;

        if (this.enableLowInitialPlaylist) {
          selectedMedia = this.selectInitialPlaylist();
        }

        if (!selectedMedia) {
          selectedMedia = this.selectPlaylist();
        }

        this.initialMedia_ = selectedMedia;
        this.masterPlaylistLoader_.media(this.initialMedia_);
        return;
      }

      if (this.useCueTags_) {
        this.updateAdCues_(updatedPlaylist);
      }

      // TODO: Create a new event on the PlaylistLoader that signals
      // that the segments have changed in some way and use that to
      // update the SegmentLoader instead of doing it twice here and
      // on `mediachange`
      this.mainSegmentLoader_.playlist(updatedPlaylist, this.requestOptions_);
      this.updateDuration();

      // If the player isn't paused, ensure that the segment loader is running,
      // as it is possible that it was temporarily stopped while waiting for
      // a playlist (e.g., in case the playlist errored and we re-requested it).
      if (!this.tech_.paused()) {
        this.mainSegmentLoader_.load();
      }

      if (!updatedPlaylist.endList) {
        let addSeekableRange = () => {
          let seekable$$1 = this.seekable();

          if (seekable$$1.length !== 0) {
            this.mediaSource.addSeekableRange_(seekable$$1.start(0), seekable$$1.end(0));
          }
        };

        if (this.duration() !== Infinity) {
          let onDurationchange = () => {
            if (this.duration() === Infinity) {
              addSeekableRange();
            } else {
              this.tech_.one('durationchange', onDurationchange);
            }
          };

          this.tech_.one('durationchange', onDurationchange);
        } else {
          addSeekableRange();
        }
      }
    });

    this.masterPlaylistLoader_.on('error', () => {
      this.blacklistCurrentPlaylist(this.masterPlaylistLoader_.error);
    });

    this.masterPlaylistLoader_.on('mediachanging', () => {
      this.mainSegmentLoader_.abort();
      this.mainSegmentLoader_.pause();
    });

    this.masterPlaylistLoader_.on('mediachange', () => {
      let media = this.masterPlaylistLoader_.media();
      let requestTimeout = (this.masterPlaylistLoader_.targetDuration * 1.5) * 1000;

      // If we don't have any more available playlists, we don't want to
      // timeout the request.
      if (isLowestEnabledRendition(
            this.masterPlaylistLoader_.master, this.masterPlaylistLoader_.media())) {
        this.requestOptions_.timeout = 0;
      } else {
        this.requestOptions_.timeout = requestTimeout;
      }

      // TODO: Create a new event on the PlaylistLoader that signals
      // that the segments have changed in some way and use that to
      // update the SegmentLoader instead of doing it twice here and
      // on `loadedplaylist`
      this.mainSegmentLoader_.playlist(media, this.requestOptions_);
      this.mainSegmentLoader_.load();

      this.tech_.trigger({
        type: 'mediachange',
        bubbles: true
      });
    });

    this.masterPlaylistLoader_.on('playlistunchanged', () => {
      let updatedPlaylist = this.masterPlaylistLoader_.media();
      let playlistOutdated = this.stuckAtPlaylistEnd_(updatedPlaylist);

      if (playlistOutdated) {
        // Playlist has stopped updating and we're stuck at its end. Try to
        // blacklist it and switch to another playlist in the hope that that
        // one is updating (and give the player a chance to re-adjust to the
        // safe live point).
        this.blacklistCurrentPlaylist({
          message: 'Playlist no longer updating.'
        });
        // useful for monitoring QoS
        this.tech_.trigger('playliststuck');
      }
    });

    this.masterPlaylistLoader_.on('renditiondisabled', () => {
      this.tech_.trigger({type: 'usage', name: 'hls-rendition-disabled'});
    });
    this.masterPlaylistLoader_.on('renditionenabled', () => {
      this.tech_.trigger({type: 'usage', name: 'hls-rendition-enabled'});
    });
  }

  /**
   * A helper function for triggerring presence usage events once per source
   *
   * @private
   */
  triggerPresenceUsage_(master, media) {
    let mediaGroups = master.mediaGroups || {};
    let defaultDemuxed = true;
    let audioGroupKeys = Object.keys(mediaGroups.AUDIO);

    for (let mediaGroup in mediaGroups.AUDIO) {
      for (let label in mediaGroups.AUDIO[mediaGroup]) {
        let properties = mediaGroups.AUDIO[mediaGroup][label];

        if (!properties.uri) {
          defaultDemuxed = false;
        }
      }
    }

    if (defaultDemuxed) {
      this.tech_.trigger({type: 'usage', name: 'hls-demuxed'});
    }

    if (Object.keys(mediaGroups.SUBTITLES).length) {
      this.tech_.trigger({type: 'usage', name: 'hls-webvtt'});
    }

    if (Hls.Playlist.isAes(media)) {
      this.tech_.trigger({type: 'usage', name: 'hls-aes'});
    }

    if (Hls.Playlist.isFmp4(media)) {
      this.tech_.trigger({type: 'usage', name: 'hls-fmp4'});
    }

    if (audioGroupKeys.length &&
        Object.keys(mediaGroups.AUDIO[audioGroupKeys[0]]).length > 1) {
      this.tech_.trigger({type: 'usage', name: 'hls-alternate-audio'});
    }

    if (this.useCueTags_) {
      this.tech_.trigger({type: 'usage', name: 'hls-playlist-cue-tags'});
    }
  }
  /**
   * Register event handlers on the segment loaders. A helper function
   * for construction time.
   *
   * @private
   */
  setupSegmentLoaderListeners_() {
    this.mainSegmentLoader_.on('bandwidthupdate', () => {
      const nextPlaylist = this.selectPlaylist();
      const currentPlaylist = this.masterPlaylistLoader_.media();
      const buffered = this.tech_.buffered();
      const forwardBuffer = buffered.length ?
        buffered.end(buffered.length - 1) - this.tech_.currentTime() : 0;

      const bufferLowWaterLine = this.bufferLowWaterLine();

      // If the playlist is live, then we want to not take low water line into account.
      // This is because in LIVE, the player plays 3 segments from the end of the
      // playlist, and if `BUFFER_LOW_WATER_LINE` is greater than the duration availble
      // in those segments, a viewer will never experience a rendition upswitch.
      if (!currentPlaylist.endList ||
          // For the same reason as LIVE, we ignore the low water line when the VOD
          // duration is below the max potential low water line
          this.duration() < Config.MAX_BUFFER_LOW_WATER_LINE ||
          // we want to switch down to lower resolutions quickly to continue playback, but
          nextPlaylist.attributes.BANDWIDTH < currentPlaylist.attributes.BANDWIDTH ||
          // ensure we have some buffer before we switch up to prevent us running out of
          // buffer while loading a higher rendition.
          forwardBuffer >= bufferLowWaterLine) {
        this.masterPlaylistLoader_.media(nextPlaylist);
      }

      this.tech_.trigger('bandwidthupdate');
    });
    this.mainSegmentLoader_.on('progress', () => {
      this.trigger('progress');
    });

    this.mainSegmentLoader_.on('error', () => {
      this.blacklistCurrentPlaylist(this.mainSegmentLoader_.error());
    });

    this.mainSegmentLoader_.on('syncinfoupdate', () => {
      this.onSyncInfoUpdate_();
    });

    this.mainSegmentLoader_.on('timestampoffset', () => {
      this.tech_.trigger({type: 'usage', name: 'hls-timestamp-offset'});
    });
    this.audioSegmentLoader_.on('syncinfoupdate', () => {
      this.onSyncInfoUpdate_();
    });

    this.mainSegmentLoader_.on('ended', () => {
      this.onEndOfStream();
    });

    this.mainSegmentLoader_.on('earlyabort', () => {
      this.blacklistCurrentPlaylist({
        message: 'Aborted early because there isn\'t enough bandwidth to complete the ' +
          'request without rebuffering.'
      }, ABORT_EARLY_BLACKLIST_SECONDS);
    });

    this.mainSegmentLoader_.on('reseteverything', () => {
      // If playing an MTS stream, a videojs.MediaSource is listening for
      // hls-reset to reset caption parsing state in the transmuxer
      this.tech_.trigger('hls-reset');
    });

    this.mainSegmentLoader_.on('segmenttimemapping', (event) => {
      // If playing an MTS stream in html, a videojs.MediaSource is listening for
      // hls-segment-time-mapping update its internal mapping of stream to display time
      this.tech_.trigger({
        type: 'hls-segment-time-mapping',
        mapping: event.mapping
      });
    });

    this.audioSegmentLoader_.on('ended', () => {
      this.onEndOfStream();
    });
  }

  mediaSecondsLoaded_() {
    return Math.max(this.audioSegmentLoader_.mediaSecondsLoaded +
                    this.mainSegmentLoader_.mediaSecondsLoaded);
  }

  /**
   * Call load on our SegmentLoaders
   */
  load() {
    this.mainSegmentLoader_.load();
    if (this.mediaTypes_.AUDIO.activePlaylistLoader) {
      this.audioSegmentLoader_.load();
    }
    if (this.mediaTypes_.SUBTITLES.activePlaylistLoader) {
      this.subtitleSegmentLoader_.load();
    }
  }

  /**
   * Re-tune playback quality level for the current player
   * conditions. This method may perform destructive actions, like
   * removing already buffered content, to readjust the currently
   * active playlist quickly.
   *
   * @private
   */
  fastQualityChange_() {
    let media = this.selectPlaylist();

    if (media !== this.masterPlaylistLoader_.media()) {
      this.masterPlaylistLoader_.media(media);

      this.mainSegmentLoader_.resetLoader();
      // don't need to reset audio as it is reset when media changes
    }
  }

  /**
   * Begin playback.
   */
  play() {
    if (this.setupFirstPlay()) {
      return;
    }

    if (this.tech_.ended()) {
      this.tech_.setCurrentTime(0);
    }

    if (this.hasPlayed_()) {
      this.load();
    }

    let seekable$$1 = this.tech_.seekable();

    // if the viewer has paused and we fell out of the live window,
    // seek forward to the live point
    if (this.tech_.duration() === Infinity) {
      if (this.tech_.currentTime() < seekable$$1.start(0)) {
        return this.tech_.setCurrentTime(seekable$$1.end(seekable$$1.length - 1));
      }
    }
  }

  /**
   * Seek to the latest media position if this is a live video and the
   * player and video are loaded and initialized.
   */
  setupFirstPlay() {
    let media = this.masterPlaylistLoader_.media();

    // Check that everything is ready to begin buffering for the first call to play
    //  If 1) there is no active media
    //     2) the player is paused
    //     3) the first play has already been setup
    // then exit early
    if (!media || this.tech_.paused() || this.hasPlayed_()) {
      return false;
    }

    // when the video is a live stream
    if (!media.endList) {
      const seekable$$1 = this.seekable();

      if (!seekable$$1.length) {
        // without a seekable range, the player cannot seek to begin buffering at the live
        // point
        return false;
      }

      if (videojs.browser.IE_VERSION &&
          this.tech_.readyState() === 0) {
        // IE11 throws an InvalidStateError if you try to set currentTime while the
        // readyState is 0, so it must be delayed until the tech fires loadedmetadata.
        this.tech_.one('loadedmetadata', () => {
          this.trigger('firstplay');
          this.tech_.setCurrentTime(seekable$$1.end(0));
          this.hasPlayed_ = () => true;
        });

        return false;
      }

      // trigger firstplay to inform the source handler to ignore the next seek event
      this.trigger('firstplay');
      // seek to the live point
      this.tech_.setCurrentTime(seekable$$1.end(0));
    }

    this.hasPlayed_ = () => true;
    // we can begin loading now that everything is ready
    this.load();
    return true;
  }

  /**
   * handle the sourceopen event on the MediaSource
   *
   * @private
   */
  handleSourceOpen_() {
    // Only attempt to create the source buffer if none already exist.
    // handleSourceOpen is also called when we are "re-opening" a source buffer
    // after `endOfStream` has been called (in response to a seek for instance)
    try {
      this.setupSourceBuffers_();
    } catch (e) {
      videojs.log.warn('Failed to create Source Buffers', e);
      return this.mediaSource.endOfStream('decode');
    }

    // if autoplay is enabled, begin playback. This is duplicative of
    // code in video.js but is required because play() must be invoked
    // *after* the media source has opened.
    if (this.tech_.autoplay()) {
      const playPromise = this.tech_.play();

      // Catch/silence error when a pause interrupts a play request
      // on browsers which return a promise
      if (typeof playPromise !== 'undefined' && typeof playPromise.then === 'function') {
        playPromise.then(null, (e) => {});
      }
    }

    this.trigger('sourceopen');
  }

  /**
   * Calls endOfStream on the media source when all active stream types have called
   * endOfStream
   *
   * @param {string} streamType
   *        Stream type of the segment loader that called endOfStream
   * @private
   */
  onEndOfStream() {
    let isEndOfStream = this.mainSegmentLoader_.ended_;

    if (this.mediaTypes_.AUDIO.activePlaylistLoader) {
      // if the audio playlist loader exists, then alternate audio is active
      if (!this.mainSegmentLoader_.startingMedia_ ||
          this.mainSegmentLoader_.startingMedia_.containsVideo) {
        // if we do not know if the main segment loader contains video yet or if we
        // definitively know the main segment loader contains video, then we need to wait
        // for both main and audio segment loaders to call endOfStream
        isEndOfStream = isEndOfStream && this.audioSegmentLoader_.ended_;
      } else {
        // otherwise just rely on the audio loader
        isEndOfStream = this.audioSegmentLoader_.ended_;
      }
    }

    if (isEndOfStream) {
      this.mediaSource.endOfStream();
    }
  }

  /**
   * Check if a playlist has stopped being updated
   * @param {Object} playlist the media playlist object
   * @return {boolean} whether the playlist has stopped being updated or not
   */
  stuckAtPlaylistEnd_(playlist) {
    let seekable$$1 = this.seekable();

    if (!seekable$$1.length) {
      // playlist doesn't have enough information to determine whether we are stuck
      return false;
    }

    let expired =
      this.syncController_.getExpiredTime(playlist, this.mediaSource.duration);

    if (expired === null) {
      return false;
    }

    // does not use the safe live end to calculate playlist end, since we
    // don't want to say we are stuck while there is still content
    let absolutePlaylistEnd = Hls.Playlist.playlistEnd(playlist, expired);
    let currentTime = this.tech_.currentTime();
    let buffered = this.tech_.buffered();

    if (!buffered.length) {
      // return true if the playhead reached the absolute end of the playlist
      return absolutePlaylistEnd - currentTime <= SAFE_TIME_DELTA;
    }
    let bufferedEnd = buffered.end(buffered.length - 1);

    // return true if there is too little buffer left and buffer has reached absolute
    // end of playlist
    return bufferedEnd - currentTime <= SAFE_TIME_DELTA &&
           absolutePlaylistEnd - bufferedEnd <= SAFE_TIME_DELTA;
  }

  /**
   * Blacklists a playlist when an error occurs for a set amount of time
   * making it unavailable for selection by the rendition selection algorithm
   * and then forces a new playlist (rendition) selection.
   *
   * @param {Object=} error an optional error that may include the playlist
   * to blacklist
   * @param {Number=} blacklistDuration an optional number of seconds to blacklist the
   * playlist
   */
  blacklistCurrentPlaylist(error = {}, blacklistDuration) {
    let currentPlaylist;
    let nextPlaylist;

    // If the `error` was generated by the playlist loader, it will contain
    // the playlist we were trying to load (but failed) and that should be
    // blacklisted instead of the currently selected playlist which is likely
    // out-of-date in this scenario
    currentPlaylist = error.playlist || this.masterPlaylistLoader_.media();

    blacklistDuration = blacklistDuration ||
                        error.blacklistDuration ||
                        this.blacklistDuration;

    // If there is no current playlist, then an error occurred while we were
    // trying to load the master OR while we were disposing of the tech
    if (!currentPlaylist) {
      this.error = error;

      try {
        return this.mediaSource.endOfStream('network');
      } catch (e) {
        return this.trigger('error');
      }
    }

    let isFinalRendition =
      this.masterPlaylistLoader_.master.playlists.filter(isEnabled).length === 1;

    if (isFinalRendition) {
      // Never blacklisting this playlist because it's final rendition
      videojs.log.warn('Problem encountered with the current ' +
                       'HLS playlist. Trying again since it is the final playlist.');

      this.tech_.trigger('retryplaylist');
      return this.masterPlaylistLoader_.load(isFinalRendition);
    }
    // Blacklist this playlist
    currentPlaylist.excludeUntil = Date.now() + (blacklistDuration * 1000);
    this.tech_.trigger('blacklistplaylist');
    this.tech_.trigger({type: 'usage', name: 'hls-rendition-blacklisted'});

    // Select a new playlist
    nextPlaylist = this.selectPlaylist();
    videojs.log.warn('Problem encountered with the current HLS playlist.' +
                     (error.message ? ' ' + error.message : '') +
                     ' Switching to another playlist.');

    return this.masterPlaylistLoader_.media(nextPlaylist);
  }

  /**
   * Pause all segment loaders
   */
  pauseLoading() {
    this.mainSegmentLoader_.pause();
    if (this.mediaTypes_.AUDIO.activePlaylistLoader) {
      this.audioSegmentLoader_.pause();
    }
    if (this.mediaTypes_.SUBTITLES.activePlaylistLoader) {
      this.subtitleSegmentLoader_.pause();
    }
  }

  /**
   * set the current time on all segment loaders
   *
   * @param {TimeRange} currentTime the current time to set
   * @return {TimeRange} the current time
   */
  setCurrentTime(currentTime) {
    let buffered = findRange(this.tech_.buffered(), currentTime);

    if (!(this.masterPlaylistLoader_ && this.masterPlaylistLoader_.media())) {
      // return immediately if the metadata is not ready yet
      return 0;
    }

    // it's clearly an edge-case but don't thrown an error if asked to
    // seek within an empty playlist
    if (!this.masterPlaylistLoader_.media().segments) {
      return 0;
    }

    // In flash playback, the segment loaders should be reset on every seek, even
    // in buffer seeks. If the seek location is already buffered, continue buffering as
    // usual
    // TODO: redo this comment
    if (buffered && buffered.length) {
      return currentTime;
    }

    // cancel outstanding requests so we begin buffering at the new
    // location
    this.mainSegmentLoader_.resetEverything();
    this.mainSegmentLoader_.abort();
    if (this.mediaTypes_.AUDIO.activePlaylistLoader) {
      this.audioSegmentLoader_.resetEverything();
      this.audioSegmentLoader_.abort();
    }
    if (this.mediaTypes_.SUBTITLES.activePlaylistLoader) {
      this.subtitleSegmentLoader_.resetEverything();
      this.subtitleSegmentLoader_.abort();
    }

    // start segment loader loading in case they are paused
    this.load();
  }

  /**
   * get the current duration
   *
   * @return {TimeRange} the duration
   */
  duration() {
    if (!this.masterPlaylistLoader_) {
      return 0;
    }

    if (this.mediaSource) {
      return this.mediaSource.duration;
    }

    return Hls.Playlist.duration(this.masterPlaylistLoader_.media());
  }

  /**
   * check the seekable range
   *
   * @return {TimeRange} the seekable range
   */
  seekable() {
    return this.seekable_;
  }

  onSyncInfoUpdate_() {
    let mainSeekable;
    let audioSeekable;

    if (!this.masterPlaylistLoader_) {
      return;
    }

    let media = this.masterPlaylistLoader_.media();

    if (!media) {
      return;
    }

    let expired = this.syncController_.getExpiredTime(media, this.mediaSource.duration);

    if (expired === null) {
      // not enough information to update seekable
      return;
    }

    mainSeekable = Hls.Playlist.seekable(media, expired);

    if (mainSeekable.length === 0) {
      return;
    }

    if (this.mediaTypes_.AUDIO.activePlaylistLoader) {
      media = this.mediaTypes_.AUDIO.activePlaylistLoader.media();
      expired = this.syncController_.getExpiredTime(media, this.mediaSource.duration);

      if (expired === null) {
        return;
      }

      audioSeekable = Hls.Playlist.seekable(media, expired);

      if (audioSeekable.length === 0) {
        return;
      }
    }

    if (!audioSeekable) {
      // seekable has been calculated based on buffering video data so it
      // can be returned directly
      this.seekable_ = mainSeekable;
    } else if (audioSeekable.start(0) > mainSeekable.end(0) ||
               mainSeekable.start(0) > audioSeekable.end(0)) {
      // seekables are pretty far off, rely on main
      this.seekable_ = mainSeekable;
    } else {
      this.seekable_ = videojs.createTimeRanges([[
        (audioSeekable.start(0) > mainSeekable.start(0)) ? audioSeekable.start(0) :
                                                           mainSeekable.start(0),
        (audioSeekable.end(0) < mainSeekable.end(0)) ? audioSeekable.end(0) :
                                                       mainSeekable.end(0)
      ]]);
    }

    this.logger_(`seekable updated [${printableRange(this.seekable_)}]`);

    this.tech_.trigger('seekablechanged');
  }

  /**
   * Update the player duration
   */
  updateDuration() {
    let oldDuration = this.mediaSource.duration;
    let newDuration = Hls.Playlist.duration(this.masterPlaylistLoader_.media());
    let buffered = this.tech_.buffered();
    let setDuration = () => {
      this.mediaSource.duration = newDuration;
      this.tech_.trigger('durationchange');

      this.mediaSource.removeEventListener('sourceopen', setDuration);
    };

    if (buffered.length > 0) {
      newDuration = Math.max(newDuration, buffered.end(buffered.length - 1));
    }

    // if the duration has changed, invalidate the cached value
    if (oldDuration !== newDuration) {
      // update the duration
      if (this.mediaSource.readyState !== 'open') {
        this.mediaSource.addEventListener('sourceopen', setDuration);
      } else {
        setDuration();
      }
    }
  }

  /**
   * dispose of the MasterPlaylistController and everything
   * that it controls
   */
  dispose() {
    this.decrypter_.terminate();
    this.masterPlaylistLoader_.dispose();
    this.mainSegmentLoader_.dispose();

    ['AUDIO', 'SUBTITLES'].forEach((type) => {
      const groups = this.mediaTypes_[type].groups;

      for (let id in groups) {
        groups[id].forEach((group) => {
          if (group.playlistLoader) {
            group.playlistLoader.dispose();
          }
        });
      }
    });

    this.audioSegmentLoader_.dispose();
    this.subtitleSegmentLoader_.dispose();
  }

  /**
   * return the master playlist object if we have one
   *
   * @return {Object} the master playlist object that we parsed
   */
  master() {
    return this.masterPlaylistLoader_.master;
  }

  /**
   * return the currently selected playlist
   *
   * @return {Object} the currently selected playlist object that we parsed
   */
  media() {
    // playlist loader will not return media if it has not been fully loaded
    return this.masterPlaylistLoader_.media() || this.initialMedia_;
  }

  /**
   * setup our internal source buffers on our segment Loaders
   *
   * @private
   */
  setupSourceBuffers_() {
    let media = this.masterPlaylistLoader_.media();
    let mimeTypes;

    // wait until a media playlist is available and the Media Source is
    // attached
    if (!media || this.mediaSource.readyState !== 'open') {
      return;
    }

    mimeTypes = mimeTypesForPlaylist(this.masterPlaylistLoader_.master, media);
    if (mimeTypes.length < 1) {
      this.error =
        'No compatible SourceBuffer configuration for the variant stream:' +
        media.resolvedUri;
      return this.mediaSource.endOfStream('decode');
    }

    this.configureLoaderMimeTypes_(mimeTypes);
    // exclude any incompatible variant streams from future playlist
    // selection
    this.excludeIncompatibleVariants_(media);
  }

  configureLoaderMimeTypes_(mimeTypes) {
    // If the content is demuxed, we can't start appending segments to a source buffer
    // until both source buffers are set up, or else the browser may not let us add the
    // second source buffer (it will assume we are playing either audio only or video
    // only).
    const sourceBufferEmitter =
      // If there is more than one mime type
      mimeTypes.length > 1 &&
      // and the first mime type does not have muxed video and audio
      mimeTypes[0].indexOf(',') === -1 &&
      // and the two mime types are different (they can be the same in the case of audio
      // only with alternate audio)
      mimeTypes[0] !== mimeTypes[1] ?
        // then we want to wait on the second source buffer
        new videojs.EventTarget() :
        // otherwise there is no need to wait as the content is either audio only,
        // video only, or muxed content.
        null;

    this.mainSegmentLoader_.mimeType(mimeTypes[0], sourceBufferEmitter);
    if (mimeTypes[1]) {
      this.audioSegmentLoader_.mimeType(mimeTypes[1], sourceBufferEmitter);
    }
  }

  /**
   * Blacklists playlists with codecs that are unsupported by the browser.
   */
  excludeUnsupportedVariants_() {
    this.master().playlists.forEach(variant => {
      if (variant.attributes.CODECS &&
          window$1.MediaSource &&
          window$1.MediaSource.isTypeSupported &&
          !window$1.MediaSource.isTypeSupported(
            `video/mp4; codecs="${mapLegacyAvcCodecs(variant.attributes.CODECS)}"`)) {
        variant.excludeUntil = Infinity;
      }
    });
  }

  /**
   * Blacklist playlists that are known to be codec or
   * stream-incompatible with the SourceBuffer configuration. For
   * instance, Media Source Extensions would cause the video element to
   * stall waiting for video data if you switched from a variant with
   * video and audio to an audio-only one.
   *
   * @param {Object} media a media playlist compatible with the current
   * set of SourceBuffers. Variants in the current master playlist that
   * do not appear to have compatible codec or stream configurations
   * will be excluded from the default playlist selection algorithm
   * indefinitely.
   * @private
   */
  excludeIncompatibleVariants_(media) {
    let codecCount = 2;
    let videoCodec = null;
    let codecs;

    if (media.attributes.CODECS) {
      codecs = parseCodecs(media.attributes.CODECS);
      videoCodec = codecs.videoCodec;
      codecCount = codecs.codecCount;
    }

    this.master().playlists.forEach(function(variant) {
      let variantCodecs = {
        codecCount: 2,
        videoCodec: null
      };

      if (variant.attributes.CODECS) {
        variantCodecs = parseCodecs(variant.attributes.CODECS);
      }

      // if the streams differ in the presence or absence of audio or
      // video, they are incompatible
      if (variantCodecs.codecCount !== codecCount) {
        variant.excludeUntil = Infinity;
      }

      // if h.264 is specified on the current playlist, some flavor of
      // it must be specified on all compatible variants
      if (variantCodecs.videoCodec !== videoCodec) {
        variant.excludeUntil = Infinity;
      }
    });
  }

  updateAdCues_(media) {
    let offset = 0;
    let seekable$$1 = this.seekable();

    if (seekable$$1.length) {
      offset = seekable$$1.start(0);
    }

    updateAdCues(media, this.cueTagsTrack_, offset);
  }

  /**
   * Calculates the desired forward buffer length based on current time
   *
   * @return {Number} Desired forward buffer length in seconds
   */
  goalBufferLength() {
    const currentTime = this.tech_.currentTime();
    const initial = Config.GOAL_BUFFER_LENGTH;
    const rate = Config.GOAL_BUFFER_LENGTH_RATE;
    const max = Math.max(initial, Config.MAX_GOAL_BUFFER_LENGTH);

    return Math.min(initial + currentTime * rate, max);
  }

  /**
   * Calculates the desired buffer low water line based on current time
   *
   * @return {Number} Desired buffer low water line in seconds
   */
  bufferLowWaterLine() {
    const currentTime = this.tech_.currentTime();
    const initial = Config.BUFFER_LOW_WATER_LINE;
    const rate = Config.BUFFER_LOW_WATER_LINE_RATE;
    const max = Math.max(initial, Config.MAX_BUFFER_LOW_WATER_LINE);

    return Math.min(initial + currentTime * rate, max);
  }
}

/**
 * Returns a function that acts as the Enable/disable playlist function.
 *
 * @param {PlaylistLoader} loader - The master playlist loader
 * @param {String} playlistUri - uri of the playlist
 * @param {Function} changePlaylistFn - A function to be called after a
 * playlist's enabled-state has been changed. Will NOT be called if a
 * playlist's enabled-state is unchanged
 * @param {Boolean=} enable - Value to set the playlist enabled-state to
 * or if undefined returns the current enabled-state for the playlist
 * @return {Function} Function for setting/getting enabled
 */
const enableFunction = (loader, playlistUri, changePlaylistFn) => (enable) => {
  const playlist = loader.master.playlists[playlistUri];
  const incompatible = isIncompatible(playlist);
  const currentlyEnabled = isEnabled(playlist);

  if (typeof enable === 'undefined') {
    return currentlyEnabled;
  }

  if (enable) {
    delete playlist.disabled;
  } else {
    playlist.disabled = true;
  }

  if (enable !== currentlyEnabled && !incompatible) {
    // Ensure the outside world knows about our changes
    changePlaylistFn();
    if (enable) {
      loader.trigger('renditionenabled');
    } else {
      loader.trigger('renditiondisabled');
    }
  }
  return enable;
};

/**
 * The representation object encapsulates the publicly visible information
 * in a media playlist along with a setter/getter-type function (enabled)
 * for changing the enabled-state of a particular playlist entry
 *
 * @class Representation
 */
class Representation {
  constructor(hlsHandler, playlist, id) {
    // Get a reference to a bound version of fastQualityChange_
    let fastChangeFunction = hlsHandler
                              .masterPlaylistController_
                              .fastQualityChange_
                              .bind(hlsHandler.masterPlaylistController_);

    // some playlist attributes are optional
    if (playlist.attributes.RESOLUTION) {
      const resolution = playlist.attributes.RESOLUTION;

      this.width = resolution.width;
      this.height = resolution.height;
    }

    this.bandwidth = playlist.attributes.BANDWIDTH;

    // The id is simply the ordinality of the media playlist
    // within the master playlist
    this.id = id;

    // Partially-apply the enableFunction to create a playlist-
    // specific variant
    this.enabled = enableFunction(hlsHandler.playlists,
                                  playlist.uri,
                                  fastChangeFunction);
  }
}

/**
 * A mixin function that adds the `representations` api to an instance
 * of the HlsHandler class
 * @param {HlsHandler} hlsHandler - An instance of HlsHandler to add the
 * representation API into
 */
let renditionSelectionMixin = function(hlsHandler) {
  let playlists = hlsHandler.playlists;

  // Add a single API-specific function to the HlsHandler instance
  hlsHandler.representations = () => {
    return playlists
      .master
      .playlists
      .filter((media) => !isIncompatible(media))
      .map((e, i) => new Representation(hlsHandler, e, e.uri));
  };
};

/**
 * @file playback-watcher.js
 *
 * Playback starts, and now my watch begins. It shall not end until my death. I shall
 * take no wait, hold no uncleared timeouts, father no bad seeks. I shall wear no crowns
 * and win no glory. I shall live and die at my post. I am the corrector of the underflow.
 * I am the watcher of gaps. I am the shield that guards the realms of seekable. I pledge
 * my life and honor to the Playback Watch, for this Player and all the Players to come.
 */

// Set of events that reset the playback-watcher time check logic and clear the timeout
const timerCancelEvents = [
  'seeking',
  'seeked',
  'pause',
  'playing',
  'error'
];

/**
 * @class PlaybackWatcher
 */
class PlaybackWatcher {
  /**
   * Represents an PlaybackWatcher object.
   * @constructor
   * @param {object} options an object that includes the tech and settings
   */
  constructor(options) {
    this.tech_ = options.tech;
    this.seekable = options.seekable;

    this.consecutiveUpdates = 0;
    this.lastRecordedTime = null;
    this.timer_ = null;
    this.checkCurrentTimeTimeout_ = null;
    this.logger_ = logger('PlaybackWatcher');

    this.logger_('initialize');

    let canPlayHandler = () => this.monitorCurrentTime_();
    let waitingHandler = () => this.techWaiting_();
    let cancelTimerHandler = () => this.cancelTimer_();
    let fixesBadSeeksHandler = () => this.fixesBadSeeks_();

    this.tech_.on('seekablechanged', fixesBadSeeksHandler);
    this.tech_.on('waiting', waitingHandler);
    this.tech_.on(timerCancelEvents, cancelTimerHandler);
    this.tech_.on('canplay', canPlayHandler);

    // Define the dispose function to clean up our events
    this.dispose = () => {
      this.logger_('dispose');
      this.tech_.off('seekablechanged', fixesBadSeeksHandler);
      this.tech_.off('waiting', waitingHandler);
      this.tech_.off(timerCancelEvents, cancelTimerHandler);
      this.tech_.off('canplay', canPlayHandler);
      if (this.checkCurrentTimeTimeout_) {
        window$1.clearTimeout(this.checkCurrentTimeTimeout_);
      }
      this.cancelTimer_();
    };
  }

  /**
   * Periodically check current time to see if playback stopped
   *
   * @private
   */
  monitorCurrentTime_() {
    this.checkCurrentTime_();

    if (this.checkCurrentTimeTimeout_) {
      window$1.clearTimeout(this.checkCurrentTimeTimeout_);
    }

    // 42 = 24 fps // 250 is what Webkit uses // FF uses 15
    this.checkCurrentTimeTimeout_ =
      window$1.setTimeout(this.monitorCurrentTime_.bind(this), 250);
  }

  /**
   * The purpose of this function is to emulate the "waiting" event on
   * browsers that do not emit it when they are waiting for more
   * data to continue playback
   *
   * @private
   */
  checkCurrentTime_() {
    if (this.tech_.seeking() && this.fixesBadSeeks_()) {
      this.consecutiveUpdates = 0;
      this.lastRecordedTime = this.tech_.currentTime();
      return;
    }

    if (this.tech_.paused() || this.tech_.seeking()) {
      return;
    }

    let currentTime = this.tech_.currentTime();
    let buffered = this.tech_.buffered();

    if (this.lastRecordedTime === currentTime &&
        (!buffered.length ||
         currentTime + SAFE_TIME_DELTA >= buffered.end(buffered.length - 1))) {
      // If current time is at the end of the final buffered region, then any playback
      // stall is most likely caused by buffering in a low bandwidth environment. The tech
      // should fire a `waiting` event in this scenario, but due to browser and tech
      // inconsistencies. Calling `techWaiting_` here allows us to simulate
      // responding to a native `waiting` event when the tech fails to emit one.
      return this.techWaiting_();
    }

    if (this.consecutiveUpdates >= 5 &&
        currentTime === this.lastRecordedTime) {
      this.consecutiveUpdates++;
      this.waiting_();
    } else if (currentTime === this.lastRecordedTime) {
      this.consecutiveUpdates++;
    } else {
      this.consecutiveUpdates = 0;
      this.lastRecordedTime = currentTime;
    }
  }

  /**
   * Cancels any pending timers and resets the 'timeupdate' mechanism
   * designed to detect that we are stalled
   *
   * @private
   */
  cancelTimer_() {
    this.consecutiveUpdates = 0;

    if (this.timer_) {
      this.logger_('cancelTimer_');
      clearTimeout(this.timer_);
    }

    this.timer_ = null;
  }

  /**
   * Fixes situations where there's a bad seek
   *
   * @return {Boolean} whether an action was taken to fix the seek
   * @private
   */
  fixesBadSeeks_() {
    const seeking = this.tech_.seeking();
    const seekable = this.seekable();
    const currentTime = this.tech_.currentTime();
    let seekTo;

    if (seeking && this.afterSeekableWindow_(seekable, currentTime)) {
      const seekableEnd = seekable.end(seekable.length - 1);

      // sync to live point (if VOD, our seekable was updated and we're simply adjusting)
      seekTo = seekableEnd;
    }

    if (seeking && this.beforeSeekableWindow_(seekable, currentTime)) {
      const seekableStart = seekable.start(0);

      // sync to the beginning of the live window
      // provide a buffer of .1 seconds to handle rounding/imprecise numbers
      seekTo = seekableStart + SAFE_TIME_DELTA;
    }

    if (typeof seekTo !== 'undefined') {
      this.logger_(`Trying to seek outside of seekable at time ${currentTime} with ` +
                  `seekable range ${printableRange(seekable)}. Seeking to ` +
                  `${seekTo}.`);

      this.tech_.setCurrentTime(seekTo);
      return true;
    }

    return false;
  }

  /**
   * Handler for situations when we determine the player is waiting.
   *
   * @private
   */
  waiting_() {
    if (this.techWaiting_()) {
      return;
    }

    // All tech waiting checks failed. Use last resort correction
    let currentTime = this.tech_.currentTime();
    let buffered = this.tech_.buffered();
    let currentRange = findRange(buffered, currentTime);

    // Sometimes the player can stall for unknown reasons within a contiguous buffered
    // region with no indication that anything is amiss (seen in Firefox). Seeking to
    // currentTime is usually enough to kickstart the player. This checks that the player
    // is currently within a buffered region before attempting a corrective seek.
    // Chrome does not appear to continue `timeupdate` events after a `waiting` event
    // until there is ~ 3 seconds of forward buffer available. PlaybackWatcher should also
    // make sure there is ~3 seconds of forward buffer before taking any corrective action
    // to avoid triggering an `unknownwaiting` event when the network is slow.
    if (currentRange.length && currentTime + 3 <= currentRange.end(0)) {
      this.cancelTimer_();
      this.tech_.setCurrentTime(currentTime);

      this.logger_(`Stopped at ${currentTime} while inside a buffered region ` +
        `[${currentRange.start(0)} -> ${currentRange.end(0)}]. Attempting to resume ` +
        `playback by seeking to the current time.`);

      // unknown waiting corrections may be useful for monitoring QoS
      this.tech_.trigger({type: 'usage', name: 'hls-unknown-waiting'});
      return;
    }
  }

  /**
   * Handler for situations when the tech fires a `waiting` event
   *
   * @return {Boolean}
   *         True if an action (or none) was needed to correct the waiting. False if no
   *         checks passed
   * @private
   */
  techWaiting_() {
    let seekable = this.seekable();
    let currentTime = this.tech_.currentTime();

    if (this.tech_.seeking() && this.fixesBadSeeks_()) {
      // Tech is seeking or bad seek fixed, no action needed
      return true;
    }

    if (this.tech_.seeking() || this.timer_ !== null) {
      // Tech is seeking or already waiting on another action, no action needed
      return true;
    }

    if (this.beforeSeekableWindow_(seekable, currentTime)) {
      let livePoint = seekable.end(seekable.length - 1);

      this.logger_(`Fell out of live window at time ${currentTime}. Seeking to ` +
                   `live point (seekable end) ${livePoint}`);
      this.cancelTimer_();
      this.tech_.setCurrentTime(livePoint);

      // live window resyncs may be useful for monitoring QoS
      this.tech_.trigger({type: 'usage', name: 'hls-live-resync'});
      return true;
    }

    let buffered = this.tech_.buffered();
    let nextRange = findNextRange(buffered, currentTime);

    if (this.videoUnderflow_(nextRange, buffered, currentTime)) {
      // Even though the video underflowed and was stuck in a gap, the audio overplayed
      // the gap, leading currentTime into a buffered range. Seeking to currentTime
      // allows the video to catch up to the audio position without losing any audio
      // (only suffering ~3 seconds of frozen video and a pause in audio playback).
      this.cancelTimer_();
      this.tech_.setCurrentTime(currentTime);

      // video underflow may be useful for monitoring QoS
      this.tech_.trigger({type: 'usage', name: 'hls-video-underflow'});
      return true;
    }

    // check for gap
    if (nextRange.length > 0) {
      let difference = nextRange.start(0) - currentTime;

      this.logger_(
        `Stopped at ${currentTime}, setting timer for ${difference}, seeking ` +
        `to ${nextRange.start(0)}`);

      this.timer_ = setTimeout(this.skipTheGap_.bind(this),
                               difference * 1000,
                               currentTime);
      return true;
    }

    // All checks failed. Returning false to indicate failure to correct waiting
    return false;
  }

  afterSeekableWindow_(seekable, currentTime) {
    if (!seekable.length) {
      // we can't make a solid case if there's no seekable, default to false
      return false;
    }

    if (currentTime > seekable.end(seekable.length - 1) + SAFE_TIME_DELTA) {
      return true;
    }

    return false;
  }

  beforeSeekableWindow_(seekable, currentTime) {
    if (seekable.length &&
        // can't fall before 0 and 0 seekable start identifies VOD stream
        seekable.start(0) > 0 &&
        currentTime < seekable.start(0) - SAFE_TIME_DELTA) {
      return true;
    }

    return false;
  }

  videoUnderflow_(nextRange, buffered, currentTime) {
    if (nextRange.length === 0) {
      // Even if there is no available next range, there is still a possibility we are
      // stuck in a gap due to video underflow.
      let gap = this.gapFromVideoUnderflow_(buffered, currentTime);

      if (gap) {
        this.logger_(`Encountered a gap in video from ${gap.start} to ${gap.end}. ` +
                     `Seeking to current time ${currentTime}`);

        return true;
      }
    }

    return false;
  }

  /**
   * Timer callback. If playback still has not proceeded, then we seek
   * to the start of the next buffered region.
   *
   * @private
   */
  skipTheGap_(scheduledCurrentTime) {
    let buffered = this.tech_.buffered();
    let currentTime = this.tech_.currentTime();
    let nextRange = findNextRange(buffered, currentTime);

    this.cancelTimer_();

    if (nextRange.length === 0 ||
        currentTime !== scheduledCurrentTime) {
      return;
    }

    this.logger_('skipTheGap_:',
                 'currentTime:', currentTime,
                 'scheduled currentTime:', scheduledCurrentTime,
                 'nextRange start:', nextRange.start(0));

    // only seek if we still have not played
    this.tech_.setCurrentTime(nextRange.start(0) + TIME_FUDGE_FACTOR);

    this.tech_.trigger({type: 'usage', name: 'hls-gap-skip'});
  }

  gapFromVideoUnderflow_(buffered, currentTime) {
    // At least in Chrome, if there is a gap in the video buffer, the audio will continue
    // playing for ~3 seconds after the video gap starts. This is done to account for
    // video buffer underflow/underrun (note that this is not done when there is audio
    // buffer underflow/underrun -- in that case the video will stop as soon as it
    // encounters the gap, as audio stalls are more noticeable/jarring to a user than
    // video stalls). The player's time will reflect the playthrough of audio, so the
    // time will appear as if we are in a buffered region, even if we are stuck in a
    // "gap."
    //
    // Example:
    // video buffer:   0 => 10.1, 10.2 => 20
    // audio buffer:   0 => 20
    // overall buffer: 0 => 10.1, 10.2 => 20
    // current time: 13
    //
    // Chrome's video froze at 10 seconds, where the video buffer encountered the gap,
    // however, the audio continued playing until it reached ~3 seconds past the gap
    // (13 seconds), at which point it stops as well. Since current time is past the
    // gap, findNextRange will return no ranges.
    //
    // To check for this issue, we see if there is a gap that starts somewhere within
    // a 3 second range (3 seconds +/- 1 second) back from our current time.
    let gaps = findGaps(buffered);

    for (let i = 0; i < gaps.length; i++) {
      let start = gaps.start(i);
      let end = gaps.end(i);

      // gap is starts no more than 4 seconds back
      if (currentTime - start < 4 && currentTime - start > 2) {
        return {
          start,
          end
        };
      }
    }

    return null;
  }
}

const defaultOptions = {
  errorInterval: 30,
  getSource(next) {
    let tech = this.tech({ IWillNotUseThisInPlugins: true });
    let sourceObj = tech.currentSource_;

    return next(sourceObj);
  }
};

/**
 * Main entry point for the plugin
 *
 * @param {Player} player a reference to a videojs Player instance
 * @param {Object} [options] an object with plugin options
 * @private
 */
const initPlugin = function(player, options) {
  let lastCalled = 0;
  let seekTo = 0;
  let localOptions = videojs.mergeOptions(defaultOptions, options);

  player.ready(() => {
    player.trigger({type: 'usage', name: 'hls-error-reload-initialized'});
  });

  /**
   * Player modifications to perform that must wait until `loadedmetadata`
   * has been triggered
   *
   * @private
   */
  const loadedMetadataHandler = function() {
    if (seekTo) {
      player.currentTime(seekTo);
    }
  };

  /**
   * Set the source on the player element, play, and seek if necessary
   *
   * @param {Object} sourceObj An object specifying the source url and mime-type to play
   * @private
   */
  const setSource = function(sourceObj) {
    if (sourceObj === null || sourceObj === undefined) {
      return;
    }
    seekTo = (player.duration() !== Infinity && player.currentTime()) || 0;

    player.one('loadedmetadata', loadedMetadataHandler);

    player.src(sourceObj);
    player.trigger({type: 'usage', name: 'hls-error-reload'});
    player.play();
  };

  /**
   * Attempt to get a source from either the built-in getSource function
   * or a custom function provided via the options
   *
   * @private
   */
  const errorHandler = function() {
    // Do not attempt to reload the source if a source-reload occurred before
    // 'errorInterval' time has elapsed since the last source-reload
    if (Date.now() - lastCalled < localOptions.errorInterval * 1000) {
      player.trigger({type: 'usage', name: 'hls-error-reload-canceled'});
      return;
    }

    if (!localOptions.getSource ||
        typeof localOptions.getSource !== 'function') {
      videojs.log.error(
        'ERROR: reloadSourceOnError - The option getSource must be a function!');
      return;
    }
    lastCalled = Date.now();

    return localOptions.getSource.call(player, setSource);
  };

  /**
   * Unbind any event handlers that were bound by the plugin
   *
   * @private
   */
  const cleanupEvents = function() {
    player.off('loadedmetadata', loadedMetadataHandler);
    player.off('error', errorHandler);
    player.off('dispose', cleanupEvents);
  };

  /**
   * Cleanup before re-initializing the plugin
   *
   * @param {Object} [newOptions] an object with plugin options
   * @private
   */
  const reinitPlugin = function(newOptions) {
    cleanupEvents();
    initPlugin(player, newOptions);
  };

  player.on('error', errorHandler);
  player.on('dispose', cleanupEvents);

  // Overwrite the plugin function so that we can correctly cleanup before
  // initializing the plugin
  player.reloadSourceOnError = reinitPlugin;
};

/**
 * Reload the source when an error is detected as long as there
 * wasn't an error previously within the last 30 seconds
 *
 * @param {Object} [options] an object with plugin options
 */
const reloadSourceOnError = function(options) {
  initPlugin(this, options);
};

const version = "1.0.1";

/**
 * @file videojs-http-streaming.js
 *
 * The main file for the HLS project.
 * License: https://github.com/videojs/videojs-http-streaming/blob/master/LICENSE
 */

const Hls$1 = {
  PlaylistLoader,
  Playlist,
  Decrypter,
  AsyncStream,
  decrypt,
  utils,

  STANDARD_PLAYLIST_SELECTOR: lastBandwidthSelector,
  INITIAL_PLAYLIST_SELECTOR: lowestBitrateCompatibleVariantSelector,
  comparePlaylistBandwidth,
  comparePlaylistResolution,

  xhr: xhrFactory()
};

// 0.5 MB/s
const INITIAL_BANDWIDTH = 4194304;

// Define getter/setters for config properites
[
  'GOAL_BUFFER_LENGTH',
  'MAX_GOAL_BUFFER_LENGTH',
  'GOAL_BUFFER_LENGTH_RATE',
  'BUFFER_LOW_WATER_LINE',
  'MAX_BUFFER_LOW_WATER_LINE',
  'BUFFER_LOW_WATER_LINE_RATE',
  'BANDWIDTH_VARIANCE'
].forEach((prop) => {
  Object.defineProperty(Hls$1, prop, {
    get() {
      videojs.log.warn(`using Hls.${prop} is UNSAFE be sure you know what you are doing`);
      return Config[prop];
    },
    set(value) {
      videojs.log.warn(`using Hls.${prop} is UNSAFE be sure you know what you are doing`);

      if (typeof value !== 'number' || value < 0) {
        videojs.log.warn(`value of Hls.${prop} must be greater than or equal to 0`);
        return;
      }

      Config[prop] = value;
    }
  });
});

const simpleTypeFromSourceType = (type) => {
  const mpegurlRE = /^(audio|video|application)\/(x-|vnd\.apple\.)?mpegurl/i;

  if (mpegurlRE.test(type)) {
    return 'hls';
  }

  const dashRE = /^application\/dash\+xml/i;

  if (dashRE.test(type)) {
    return 'dash';
  }

  return null;
};

/**
 * Updates the selectedIndex of the QualityLevelList when a mediachange happens in hls.
 *
 * @param {QualityLevelList} qualityLevels The QualityLevelList to update.
 * @param {PlaylistLoader} playlistLoader PlaylistLoader containing the new media info.
 * @function handleHlsMediaChange
 */
const handleHlsMediaChange = function(qualityLevels, playlistLoader) {
  let newPlaylist = playlistLoader.media();
  let selectedIndex = -1;

  for (let i = 0; i < qualityLevels.length; i++) {
    if (qualityLevels[i].id === newPlaylist.uri) {
      selectedIndex = i;
      break;
    }
  }

  qualityLevels.selectedIndex_ = selectedIndex;
  qualityLevels.trigger({
    selectedIndex,
    type: 'change'
  });
};

/**
 * Adds quality levels to list once playlist metadata is available
 *
 * @param {QualityLevelList} qualityLevels The QualityLevelList to attach events to.
 * @param {Object} hls Hls object to listen to for media events.
 * @function handleHlsLoadedMetadata
 */
const handleHlsLoadedMetadata = function(qualityLevels, hls) {
  hls.representations().forEach((rep) => {
    qualityLevels.addQualityLevel(rep);
  });
  handleHlsMediaChange(qualityLevels, hls.playlists);
};

// HLS is a source handler, not a tech. Make sure attempts to use it
// as one do not cause exceptions.
Hls$1.canPlaySource = function() {
  return videojs.log.warn('HLS is no longer a tech. Please remove it from ' +
    'your player\'s techOrder.');
};

const emeKeySystems = (keySystemOptions, videoPlaylist, audioPlaylist) => {
  if (!keySystemOptions) {
    return keySystemOptions;
  }

  // upsert the content types based on the selected playlist
  const keySystemContentTypes = {};

  for (let keySystem in keySystemOptions) {
    keySystemContentTypes[keySystem] = {
      audioContentType: `audio/mp4; codecs="${audioPlaylist.attributes.CODECS}"`,
      videoContentType: `video/mp4; codecs="${videoPlaylist.attributes.CODECS}"`
    };

    if (videoPlaylist.contentProtection &&
        videoPlaylist.contentProtection[keySystem] &&
        videoPlaylist.contentProtection[keySystem].pssh) {
      keySystemContentTypes[keySystem].pssh =
        videoPlaylist.contentProtection[keySystem].pssh;
    }

    // videojs-contrib-eme accepts the option of specifying: 'com.some.cdm': 'url'
    // so we need to prevent overwriting the URL entirely
    if (typeof keySystemOptions[keySystem] === 'string') {
      keySystemContentTypes[keySystem].url = keySystemOptions[keySystem];
    }
  }

  return videojs.mergeOptions(keySystemOptions, keySystemContentTypes);
};

const setupEmeOptions = (hlsHandler) => {
  if (hlsHandler.options_.sourceType !== 'dash') {
    return;
  }
  const player = videojs.players[hlsHandler.tech_.options_.playerId];

  if (player.eme) {
    const sourceOptions = emeKeySystems(
      hlsHandler.source_.keySystems,
      hlsHandler.playlists.media(),
      hlsHandler.masterPlaylistController_.mediaTypes_.AUDIO.activePlaylistLoader.media()
    );

    if (sourceOptions) {
      player.currentSource().keySystems = sourceOptions;
    }
  }
};

/**
 * Whether the browser has built-in HLS support.
 */
Hls$1.supportsNativeHls = (function() {
  let video = document.createElement('video');

  // native HLS is definitely not supported if HTML5 video isn't
  if (!videojs.getTech('Html5').isSupported()) {
    return false;
  }

  // HLS manifests can go by many mime-types
  let canPlay = [
    // Apple santioned
    'application/vnd.apple.mpegurl',
    // Apple sanctioned for backwards compatibility
    'audio/mpegurl',
    // Very common
    'audio/x-mpegurl',
    // Very common
    'application/x-mpegurl',
    // Included for completeness
    'video/x-mpegurl',
    'video/mpegurl',
    'application/mpegurl'
  ];

  return canPlay.some(function(canItPlay) {
    return (/maybe|probably/i).test(video.canPlayType(canItPlay));
  });
}());

Hls$1.supportsNativeDash = (function() {
  if (!videojs.getTech('Html5').isSupported()) {
    return false;
  }

  return (/maybe|probably/i).test(
    document.createElement('video').canPlayType('application/dash+xml'));
}());

Hls$1.supportsTypeNatively = (type) => {
  if (type === 'hls') {
    return Hls$1.supportsNativeHls;
  }

  if (type === 'dash') {
    return Hls$1.supportsNativeDash;
  }

  return false;
};

/**
 * HLS is a source handler, not a tech. Make sure attempts to use it
 * as one do not cause exceptions.
 */
Hls$1.isSupported = function() {
  return videojs.log.warn('HLS is no longer a tech. Please remove it from ' +
    'your player\'s techOrder.');
};

const Component = videojs.getComponent('Component');

/**
 * The Hls Handler object, where we orchestrate all of the parts
 * of HLS to interact with video.js
 *
 * @class HlsHandler
 * @extends videojs.Component
 * @param {Object} source the soruce object
 * @param {Tech} tech the parent tech object
 * @param {Object} options optional and required options
 */
class HlsHandler extends Component {
  constructor(source, tech, options) {
    super(tech, options.hls);

    // tech.player() is deprecated but setup a reference to HLS for
    // backwards-compatibility
    if (tech.options_ && tech.options_.playerId) {
      let _player = videojs(tech.options_.playerId);

      if (!_player.hasOwnProperty('hls')) {
        Object.defineProperty(_player, 'hls', {
          get: () => {
            videojs.log.warn('player.hls is deprecated. Use player.tech_.hls instead.');
            tech.trigger({ type: 'usage', name: 'hls-player-access' });
            return this;
          }
        });
      }

      // Set up a reference to the HlsHandler from player.vhs. This allows users to start
      // migrating from player.tech_.hls... to player.vhs... for API access. Although this
      // isn't the most appropriate form of reference for video.js (since all APIs should
      // be provided through core video.js), it is a common pattern for plugins, and vhs
      // will act accordingly.
      _player.vhs = this;
      // deprecated, for backwards compatibility
      _player.dash = this;
    }

    this.tech_ = tech;
    this.source_ = source;
    this.stats = {};
    this.ignoreNextSeekingEvent_ = false;
    this.setOptions_();

    // overriding native HLS only works if audio tracks have been emulated
    // error early if we're misconfigured:
    if (this.options_.overrideNative &&
        (tech.featuresNativeVideoTracks || tech.featuresNativeAudioTracks)) {
      throw new Error('Overriding native HLS requires emulated tracks. ' +
        'See https://git.io/vMpjB');
    }

    // listen for fullscreenchange events for this player so that we
    // can adjust our quality selection quickly
    this.on(document, [
      'fullscreenchange', 'webkitfullscreenchange',
      'mozfullscreenchange', 'MSFullscreenChange'
    ], (event) => {
      let fullscreenElement = document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement;

      if (fullscreenElement && fullscreenElement.contains(this.tech_.el())) {
        this.masterPlaylistController_.fastQualityChange_();
      }
    });

    this.on(this.tech_, 'seeking', function() {
      if (this.ignoreNextSeekingEvent_) {
        this.ignoreNextSeekingEvent_ = false;
        return;
      }

      this.setCurrentTime(this.tech_.currentTime());
    });
    this.on(this.tech_, 'error', function() {
      if (this.masterPlaylistController_) {
        this.masterPlaylistController_.pauseLoading();
      }
    });

    this.on(this.tech_, 'play', this.play);
  }

  setOptions_() {
    // defaults
    this.options_.withCredentials = this.options_.withCredentials || false;

    if (typeof this.options_.blacklistDuration !== 'number') {
      this.options_.blacklistDuration = 5 * 60;
    }

    // start playlist selection at a reasonable bandwidth for
    // broadband internet (0.5 MB/s) or mobile (0.0625 MB/s)
    if (typeof this.options_.bandwidth !== 'number') {
      this.options_.bandwidth = INITIAL_BANDWIDTH;
    }

    // If the bandwidth number is unchanged from the initial setting
    // then this takes precedence over the enableLowInitialPlaylist option
    this.options_.enableLowInitialPlaylist =
      this.options_.enableLowInitialPlaylist &&
      this.options_.bandwidth === INITIAL_BANDWIDTH;

    // grab options passed to player.src
    ['withCredentials', 'bandwidth'].forEach((option) => {
      if (typeof this.source_[option] !== 'undefined') {
        this.options_[option] = this.source_[option];
      }
    });

    this.bandwidth = this.options_.bandwidth;
  }
  /**
   * called when player.src gets called, handle a new source
   *
   * @param {Object} src the source object to handle
   */
  src(src, type) {
    // do nothing if the src is falsey
    if (!src) {
      return;
    }
    this.setOptions_();
    // add master playlist controller options
    this.options_.url = this.source_.src;
    this.options_.tech = this.tech_;
    this.options_.externHls = Hls$1;
    this.options_.sourceType = simpleTypeFromSourceType(type);

    this.masterPlaylistController_ = new MasterPlaylistController(this.options_);
    this.playbackWatcher_ = new PlaybackWatcher(
      videojs.mergeOptions(this.options_, {
        seekable: () => this.seekable()
      }));

    this.masterPlaylistController_.on('error', () => {
      let player = videojs.players[this.tech_.options_.playerId];

      player.error(this.masterPlaylistController_.error);
    });

    // `this` in selectPlaylist should be the HlsHandler for backwards
    // compatibility with < v2
    this.masterPlaylistController_.selectPlaylist =
      this.selectPlaylist ?
        this.selectPlaylist.bind(this) : Hls$1.STANDARD_PLAYLIST_SELECTOR.bind(this);

    this.masterPlaylistController_.selectInitialPlaylist =
      Hls$1.INITIAL_PLAYLIST_SELECTOR.bind(this);

    // re-expose some internal objects for backwards compatibility with < v2
    this.playlists = this.masterPlaylistController_.masterPlaylistLoader_;
    this.mediaSource = this.masterPlaylistController_.mediaSource;

    // Proxy assignment of some properties to the master playlist
    // controller. Using a custom property for backwards compatibility
    // with < v2
    Object.defineProperties(this, {
      selectPlaylist: {
        get() {
          return this.masterPlaylistController_.selectPlaylist;
        },
        set(selectPlaylist) {
          this.masterPlaylistController_.selectPlaylist = selectPlaylist.bind(this);
        }
      },
      throughput: {
        get() {
          return this.masterPlaylistController_.mainSegmentLoader_.throughput.rate;
        },
        set(throughput) {
          this.masterPlaylistController_.mainSegmentLoader_.throughput.rate = throughput;
          // By setting `count` to 1 the throughput value becomes the starting value
          // for the cumulative average
          this.masterPlaylistController_.mainSegmentLoader_.throughput.count = 1;
        }
      },
      bandwidth: {
        get() {
          return this.masterPlaylistController_.mainSegmentLoader_.bandwidth;
        },
        set(bandwidth) {
          this.masterPlaylistController_.mainSegmentLoader_.bandwidth = bandwidth;
          // setting the bandwidth manually resets the throughput counter
          // `count` is set to zero that current value of `rate` isn't included
          // in the cumulative average
          this.masterPlaylistController_.mainSegmentLoader_.throughput = {
            rate: 0,
            count: 0
          };
        }
      },
      /**
       * `systemBandwidth` is a combination of two serial processes bit-rates. The first
       * is the network bitrate provided by `bandwidth` and the second is the bitrate of
       * the entire process after that - decryption, transmuxing, and appending - provided
       * by `throughput`.
       *
       * Since the two process are serial, the overall system bandwidth is given by:
       *   sysBandwidth = 1 / (1 / bandwidth + 1 / throughput)
       */
      systemBandwidth: {
        get() {
          let invBandwidth = 1 / (this.bandwidth || 1);
          let invThroughput;

          if (this.throughput > 0) {
            invThroughput = 1 / this.throughput;
          } else {
            invThroughput = 0;
          }

          let systemBitrate = Math.floor(1 / (invBandwidth + invThroughput));

          return systemBitrate;
        },
        set() {
          videojs.log.error('The "systemBandwidth" property is read-only');
        }
      }
    });

    Object.defineProperties(this.stats, {
      bandwidth: {
        get: () => this.bandwidth || 0,
        enumerable: true
      },
      mediaRequests: {
        get: () => this.masterPlaylistController_.mediaRequests_() || 0,
        enumerable: true
      },
      mediaRequestsAborted: {
        get: () => this.masterPlaylistController_.mediaRequestsAborted_() || 0,
        enumerable: true
      },
      mediaRequestsTimedout: {
        get: () => this.masterPlaylistController_.mediaRequestsTimedout_() || 0,
        enumerable: true
      },
      mediaRequestsErrored: {
        get: () => this.masterPlaylistController_.mediaRequestsErrored_() || 0,
        enumerable: true
      },
      mediaTransferDuration: {
        get: () => this.masterPlaylistController_.mediaTransferDuration_() || 0,
        enumerable: true
      },
      mediaBytesTransferred: {
        get: () => this.masterPlaylistController_.mediaBytesTransferred_() || 0,
        enumerable: true
      },
      mediaSecondsLoaded: {
        get: () => this.masterPlaylistController_.mediaSecondsLoaded_() || 0,
        enumerable: true
      },
      buffered: {
        get: () => timeRangesToArray(this.tech_.buffered()),
        enumerable: true
      },
      currentTime: {
        get: () => this.tech_.currentTime(),
        enumerable: true
      },
      currentSource: {
        get: () => this.tech_.currentSource_,
        enumerable: true
      },
      currentTech: {
        get: () => this.tech_.name_,
        enumerable: true
      },
      duration: {
        get: () => this.tech_.duration(),
        enumerable: true
      },
      master: {
        get: () => this.playlists.master,
        enumerable: true
      },
      playerDimensions: {
        get: () => this.tech_.currentDimensions(),
        enumerable: true
      },
      seekable: {
        get: () => timeRangesToArray(this.tech_.seekable()),
        enumerable: true
      },
      timestamp: {
        get: () => Date.now(),
        enumerable: true
      },
      videoPlaybackQuality: {
        get: () => this.tech_.getVideoPlaybackQuality(),
        enumerable: true
      }
    });

    this.tech_.one('canplay',
      this.masterPlaylistController_.setupFirstPlay.bind(this.masterPlaylistController_));

    this.masterPlaylistController_.on('selectedinitialmedia', () => {
      // Add the manual rendition mix-in to HlsHandler
      renditionSelectionMixin(this);
      setupEmeOptions(this);
    });

    // the bandwidth of the primary segment loader is our best
    // estimate of overall bandwidth
    this.on(this.masterPlaylistController_, 'progress', function() {
      this.tech_.trigger('progress');
    });

    // In the live case, we need to ignore the very first `seeking` event since
    // that will be the result of the seek-to-live behavior
    this.on(this.masterPlaylistController_, 'firstplay', function() {
      this.ignoreNextSeekingEvent_ = true;
    });

    this.tech_.ready(() => this.setupQualityLevels_());

    // do nothing if the tech has been disposed already
    // this can occur if someone sets the src in player.ready(), for instance
    if (!this.tech_.el()) {
      return;
    }

    this.tech_.src(videojs.URL.createObjectURL(
      this.masterPlaylistController_.mediaSource));
  }

  /**
   * Initializes the quality levels and sets listeners to update them.
   *
   * @method setupQualityLevels_
   * @private
   */
  setupQualityLevels_() {
    let player = videojs.players[this.tech_.options_.playerId];

    if (player && player.qualityLevels) {
      this.qualityLevels_ = player.qualityLevels();

      this.masterPlaylistController_.on('selectedinitialmedia', () => {
        handleHlsLoadedMetadata(this.qualityLevels_, this);
      });

      this.playlists.on('mediachange', () => {
        handleHlsMediaChange(this.qualityLevels_, this.playlists);
      });
    }
  }

  /**
   * Begin playing the video.
   */
  play() {
    this.masterPlaylistController_.play();
  }

  /**
   * a wrapper around the function in MasterPlaylistController
   */
  setCurrentTime(currentTime) {
    this.masterPlaylistController_.setCurrentTime(currentTime);
  }

  /**
   * a wrapper around the function in MasterPlaylistController
   */
  duration() {
    return this.masterPlaylistController_.duration();
  }

  /**
   * a wrapper around the function in MasterPlaylistController
   */
  seekable() {
    return this.masterPlaylistController_.seekable();
  }

  /**
   * Abort all outstanding work and cleanup.
   */
  dispose() {
    if (this.playbackWatcher_) {
      this.playbackWatcher_.dispose();
    }
    if (this.masterPlaylistController_) {
      this.masterPlaylistController_.dispose();
    }
    if (this.qualityLevels_) {
      this.qualityLevels_.dispose();
    }
    super.dispose();
  }
}

/**
 * The Source Handler object, which informs video.js what additional
 * MIME types are supported and sets up playback. It is registered
 * automatically to the appropriate tech based on the capabilities of
 * the browser it is running in. It is not necessary to use or modify
 * this object in normal usage.
 */
const HlsSourceHandler = {
  name: 'videojs-http-streaming',
  VERSION: version,
  canHandleSource(srcObj, options = {}) {
    let localOptions = videojs.mergeOptions(videojs.options, options);

    return HlsSourceHandler.canPlayType(srcObj.type, localOptions);
  },
  handleSource(source, tech, options = {}) {
    let localOptions = videojs.mergeOptions(videojs.options, options);

    tech.hls = new HlsHandler(source, tech, localOptions);
    tech.hls.xhr = xhrFactory();

    tech.hls.src(source.src, source.type);
    return tech.hls;
  },
  canPlayType(type, options = {}) {
    const { hls: { overrideNative } } = videojs.mergeOptions(videojs.options, options);
    const supportedType = simpleTypeFromSourceType(type);
    const canUseMsePlayback = supportedType &&
      (!Hls$1.supportsTypeNatively(supportedType) || overrideNative);

    return canUseMsePlayback ? 'maybe' : '';
  }
};

if (typeof videojs.MediaSource === 'undefined' ||
    typeof videojs.URL === 'undefined') {
  videojs.MediaSource = MediaSource;
  videojs.URL = URL$1;
}

// register source handlers with the appropriate techs
if (MediaSource.supportsNativeMediaSources()) {
  videojs.getTech('Html5').registerSourceHandler(HlsSourceHandler, 0);
}

videojs.HlsHandler = HlsHandler;
videojs.HlsSourceHandler = HlsSourceHandler;
videojs.Hls = Hls$1;
if (!videojs.use) {
  videojs.registerComponent('Hls', Hls$1);
}
videojs.options.hls = videojs.options.hls || {};

if (videojs.registerPlugin) {
  videojs.registerPlugin('reloadSourceOnError', reloadSourceOnError);
} else {
  videojs.plugin('reloadSourceOnError', reloadSourceOnError);
}

export { Hls$1 as Hls, HlsHandler, HlsSourceHandler, emeKeySystems, simpleTypeFromSourceType };
