export default TextTrackList;
/** @import TextTrack from './text-track' */
/**
 * The current list of {@link TextTrack} for a media file.
 *
 * @see [Spec]{@link https://html.spec.whatwg.org/multipage/embedded-content.html#texttracklist}
 * @extends TrackList
 */
declare class TextTrackList extends TrackList {
    /**
     * Add a {@link TextTrack} to the `TextTrackList`
     *
     * @param {TextTrack} track
     *        The text track to add to the list.
     *
     * @fires TrackList#addtrack
     */
    addTrack(track: TextTrack): void;
    queueChange_: () => void;
    triggerSelectedlanguagechange_: () => void;
    removeTrack(rtrack: any): void;
}
import TrackList from './track-list';
import type TextTrack from './text-track';
//# sourceMappingURL=text-track-list.d.ts.map