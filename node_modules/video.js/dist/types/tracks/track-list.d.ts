export default TrackList;
/** @import Track from './track' */
/**
 * Common functionaliy between {@link TextTrackList}, {@link AudioTrackList}, and
 * {@link VideoTrackList}
 *
 * @extends EventTarget
 */
declare class TrackList extends EventTarget {
    /**
     * Create an instance of this class
     *
     * @param { Track[] } tracks
     *        A list of tracks to initialize the list with.
     *
     * @abstract
     */
    constructor(tracks?: Track[]);
    tracks_: any[];
    /**
     * Add a {@link Track} to the `TrackList`
     *
     * @param {Track} track
     *        The audio, video, or text track to add to the list.
     *
     * @fires TrackList#addtrack
     */
    addTrack(track: Track): void;
    /**
     * Remove a {@link Track} from the `TrackList`
     *
     * @param {Track} rtrack
     *        The audio, video, or text track to remove from the list.
     *
     * @fires TrackList#removetrack
     */
    removeTrack(rtrack: Track): void;
    /**
     * Get a Track from the TrackList by a tracks id
     *
     * @param {string} id - the id of the track to get
     * @method getTrackById
     * @return {Track}
     * @private
     */
    private getTrackById;
    /**
     * Triggered when a different track is selected/enabled.
     *
     * @event TrackList#change
     * @type {Event}
     */
    /**
     * Events that can be called with on + eventName. See {@link EventHandler}.
     *
     * @property {Object} TrackList#allowedEvents_
     * @protected
     */
    protected allowedEvents_: {
        change: string;
        addtrack: string;
        removetrack: string;
        labelchange: string;
    };
}
import EventTarget from '../event-target';
import type Track from './track';
//# sourceMappingURL=track-list.d.ts.map